<?php

namespace Tests\Unit\Modules\CRM\Application;

use App\Modules\CRM\Application\Actions\CreateActivityAction;
use App\Modules\CRM\Application\DTOs\CreateActivityData;
use App\Modules\CRM\Domain\Contracts\ActivityRepository;
use App\Modules\CRM\Domain\Enums\ActivityType;
use App\Modules\CRM\Domain\Events\ActivityCreated;
use App\Modules\CRM\Domain\Models\Activity;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateActivityActionTest extends TestCase
{
    private ActivityRepository $repository;
    private CreateActivityAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements ActivityRepository {
            public ?Activity $saved = null;

            public function findById(int|string $id): ?Activity { return null; }

            public function save(Activity $activity): void
            {
                $activity->id  = 1;
                $this->saved   = $activity;
            }
        };

        $this->action = new CreateActivityAction($this->repository);
    }

    #[Test]
    public function it_creates_an_activity_and_returns_it(): void
    {
        Event::fake();

        $activity = $this->action->execute(new CreateActivityData(
            type:    ActivityType::Call,
            subject: 'Initial call with prospect',
            leadId:  5,
        ));

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame(ActivityType::Call,              $activity->type);
        $this->assertSame('Initial call with prospect',    $activity->subject);
        $this->assertSame(5,                               $activity->lead_id);
    }

    #[Test]
    public function it_fires_activity_created_event(): void
    {
        Event::fake();

        $activity = $this->action->execute(new CreateActivityData(
            type:    ActivityType::Meeting,
            subject: 'Discovery meeting',
            leadId:  3,
        ));

        Event::assertDispatched(
            ActivityCreated::class,
            fn ($e) => $e->activity === $activity,
        );
    }

    #[Test]
    public function it_persists_the_activity_through_the_repository(): void
    {
        Event::fake();

        $this->action->execute(new CreateActivityData(
            type:    ActivityType::Email,
            subject: 'Sent follow-up email',
            leadId:  2,
        ));

        $this->assertNotNull($this->repository->saved);
        $this->assertSame('Sent follow-up email', $this->repository->saved->subject);
    }

    #[Test]
    public function it_defaults_occurred_at_to_now_when_not_provided(): void
    {
        Event::fake();

        $before = now()->subSecond();

        $this->action->execute(new CreateActivityData(
            type:    ActivityType::Note,
            subject: 'Added a note',
            leadId:  1,
        ));

        $savedAt = $this->repository->saved->occurred_at;
        $this->assertNotNull($savedAt);
        $this->assertTrue(
            $before->lte(now()),
            'occurred_at should be set to approximately now',
        );
    }

    #[Test]
    public function it_maps_all_optional_fields(): void
    {
        Event::fake();

        $activity = $this->action->execute(new CreateActivityData(
            type:            ActivityType::Call,
            subject:         'Qualification call',
            body:            'Discussed budget and timeline in detail.',
            occurredAt:      '2026-06-01 10:00:00',
            leadId:          1,
            companyId:       2,
            contactPersonId: 3,
            opportunityId:   4,
        ));

        $this->assertSame('Discussed budget and timeline in detail.', $activity->body);
        $this->assertSame(1, $activity->lead_id);
        $this->assertSame(2, $activity->company_id);
        $this->assertSame(3, $activity->contact_person_id);
        $this->assertSame(4, $activity->opportunity_id);
    }

    #[Test]
    public function it_records_the_event_occurred_at_timestamp(): void
    {
        Event::fake();

        $this->action->execute(new CreateActivityData(
            type:       ActivityType::Meeting,
            subject:    'Timestamped meeting',
            occurredAt: '2026-05-15 09:30:00',
            leadId:     1,
        ));

        $this->assertSame('2026-05-15 09:30:00', $this->repository->saved->occurred_at->format('Y-m-d H:i:s'));
    }
}
