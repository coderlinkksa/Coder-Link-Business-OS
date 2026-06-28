<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Application\Actions\CreateActivityAction;
use App\Modules\CRM\Application\DTOs\CreateActivityData;
use App\Modules\CRM\Domain\Contracts\ActivityRepository;
use App\Modules\CRM\Domain\Enums\ActivityType;
use App\Modules\CRM\Domain\Events\ActivityCreated;
use App\Modules\CRM\Domain\Models\Activity;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for CRM Activity creation.
 * Uses an in-memory repository — no database required at this stage.
 * Activities are append-only: no update or delete action is tested.
 */
class ActivityFoundationTest extends TestCase
{
    private InMemoryActivityRepository $activityRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityRepo = new InMemoryActivityRepository();
        $this->app->instance(ActivityRepository::class, $this->activityRepo);
    }

    #[Test]
    public function create_activity_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateActivityAction::class);
        $this->assertInstanceOf(CreateActivityAction::class, $action);
    }

    #[Test]
    public function creating_an_activity_fires_activity_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateActivityAction::class);

        $activity = $action->execute(new CreateActivityData(
            type:    ActivityType::Call,
            subject: 'Called prospect to qualify',
            leadId:  1,
        ));

        Event::assertDispatched(
            ActivityCreated::class,
            fn ($e) => $e->activity->subject === 'Called prospect to qualify',
        );

        $this->assertSame(ActivityType::Call, $activity->type);
    }

    #[Test]
    public function activity_is_stored_in_repository_after_creation(): void
    {
        Event::fake();

        $action = $this->app->make(CreateActivityAction::class);

        $action->execute(new CreateActivityData(
            type:    ActivityType::Meeting,
            subject: 'Discovery meeting held',
            leadId:  2,
        ));

        $this->assertCount(1, $this->activityRepo->all());
        $this->assertSame('Discovery meeting held', $this->activityRepo->all()[0]->subject);
    }

    #[Test]
    public function multiple_activities_can_be_logged_independently(): void
    {
        Event::fake();

        $action = $this->app->make(CreateActivityAction::class);

        $action->execute(new CreateActivityData(
            type:    ActivityType::Call,
            subject: 'First call',
            leadId:  1,
        ));

        $action->execute(new CreateActivityData(
            type:    ActivityType::Email,
            subject: 'Follow-up email',
            leadId:  1,
        ));

        $action->execute(new CreateActivityData(
            type:    ActivityType::Note,
            subject: 'Internal note added',
            leadId:  1,
        ));

        $this->assertCount(3, $this->activityRepo->all());

        Event::assertDispatchedTimes(ActivityCreated::class, 3);
    }

    #[Test]
    public function activity_linked_to_opportunity_is_stored_correctly(): void
    {
        Event::fake();

        $action = $this->app->make(CreateActivityAction::class);

        $activity = $action->execute(new CreateActivityData(
            type:          ActivityType::Meeting,
            subject:       'Negotiation meeting',
            opportunityId: 7,
        ));

        $this->assertSame(7,  $activity->opportunity_id);
        $this->assertNull($activity->lead_id);
    }

    #[Test]
    public function occurred_at_is_set_on_activity_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateActivityAction::class);
        $action->execute(new CreateActivityData(
            type:    ActivityType::Note,
            subject: 'Timestamped note',
            leadId:  1,
        ));

        Event::assertDispatched(ActivityCreated::class, function (ActivityCreated $e) {
            return $e->occurredAt() instanceof \DateTimeImmutable;
        });
    }
}

// ── In-memory test double ─────────────────────────────────────────────────────

class InMemoryActivityRepository implements ActivityRepository
{
    /** @var Activity[] */
    private array $store  = [];
    private int   $nextId = 1;

    public function findById(int $id): ?Activity
    {
        return $this->store[$id] ?? null;
    }

    public function save(Activity $activity): void
    {
        if (! isset($activity->id) || $activity->id === null) {
            $activity->id = $this->nextId++;
        }
        $this->store[$activity->id] = $activity;
    }

    /** @return Activity[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}
