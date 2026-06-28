<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Application\Actions\ConvertLeadToOpportunityAction;
use App\Modules\CRM\Application\Actions\CreateLeadAction;
use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Application\DTOs\CreateLeadData;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\CRM\Domain\Events\LeadCreated;
use App\Modules\CRM\Domain\Exceptions\LeadAlreadyConvertedException;
use App\Modules\CRM\Domain\Models\Lead;
use App\Shared\Exceptions\ValidationException;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for CRM lead creation and conversion.
 * Uses an in-memory repository — no database required at this stage.
 */
class LeadFoundationTest extends TestCase
{
    private InMemoryLeadRepository $leadRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->leadRepo = new InMemoryLeadRepository();
        $this->app->instance(LeadRepository::class, $this->leadRepo);
    }

    // ── CreateLeadAction via container ────────────────────────────────────────

    #[Test]
    public function create_lead_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateLeadAction::class);
        $this->assertInstanceOf(CreateLeadAction::class, $action);
    }

    #[Test]
    public function creating_a_lead_fires_lead_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateLeadAction::class);

        $lead = $action->execute(new CreateLeadData(
            name:   'Faisal Al-Ghamdi',
            source: LeadSource::Website,
            email:  'faisal@example.sa',
        ));

        Event::assertDispatched(
            LeadCreated::class,
            fn ($e) => $e->lead->name === 'Faisal Al-Ghamdi',
        );

        $this->assertSame(LeadStatus::New, $lead->status);
    }

    #[Test]
    public function creating_a_lead_without_contact_info_throws_validation_exception(): void
    {
        Event::fake();

        $action = $this->app->make(CreateLeadAction::class);

        $this->expectException(ValidationException::class);

        $action->execute(new CreateLeadData(
            name:   'No Contact',
            source: LeadSource::Other,
        ));
    }

    #[Test]
    public function lead_is_stored_in_repository_after_creation(): void
    {
        Event::fake();

        $action = $this->app->make(CreateLeadAction::class);

        $action->execute(new CreateLeadData(
            name:   'Stored Lead',
            source: LeadSource::Direct,
            phone:  '+966509999999',
        ));

        $this->assertCount(1, $this->leadRepo->all());
        $this->assertSame('Stored Lead', $this->leadRepo->all()[0]->name);
    }

    // ── ConvertLeadToOpportunityAction via container ──────────────────────────

    #[Test]
    public function convert_action_resolves_from_container(): void
    {
        $action = $this->app->make(ConvertLeadToOpportunityAction::class);
        $this->assertInstanceOf(ConvertLeadToOpportunityAction::class, $action);
    }

    #[Test]
    public function converting_a_qualified_lead_fires_lead_converted_event(): void
    {
        Event::fake();

        // First create a lead.
        $createAction = $this->app->make(CreateLeadAction::class);
        $lead         = $createAction->execute(new CreateLeadData(
            name:   'Convert Me',
            source: LeadSource::Referral,
            email:  'convert@example.sa',
        ));

        // Manually advance status so conversion guard passes.
        $lead->status = LeadStatus::Qualified;
        $this->leadRepo->save($lead);

        $convertAction = $this->app->make(ConvertLeadToOpportunityAction::class);
        $converted     = $convertAction->execute(new ConvertLeadData(
            leadId:           $lead->id,
            companyId:        20,
            opportunityTitle: 'CRM System Build',
        ));

        $this->assertSame(LeadStatus::Converted, $converted->status);

        Event::assertDispatched(
            LeadConvertedToOpportunity::class,
            fn ($e) => $e->lead->id === $lead->id
                    && $e->conversionData->companyId === 20,
        );
    }

    #[Test]
    public function converting_an_already_converted_lead_throws(): void
    {
        Event::fake();

        $createAction = $this->app->make(CreateLeadAction::class);
        $lead         = $createAction->execute(new CreateLeadData(
            name:   'Already Converted',
            source: LeadSource::Direct,
            phone:  '+966500000001',
        ));

        $lead->status = LeadStatus::Converted;
        $this->leadRepo->save($lead);

        $convertAction = $this->app->make(ConvertLeadToOpportunityAction::class);

        $this->expectException(LeadAlreadyConvertedException::class);

        $convertAction->execute(new ConvertLeadData(
            leadId:           $lead->id,
            companyId:        20,
            opportunityTitle: 'Should Fail',
        ));
    }

    #[Test]
    public function occurred_at_is_set_on_lead_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateLeadAction::class);
        $action->execute(new CreateLeadData(
            name:   'Timestamped Lead',
            source: LeadSource::Website,
            email:  'ts@example.sa',
        ));

        Event::assertDispatched(LeadCreated::class, function (LeadCreated $e) {
            return $e->occurredAt() instanceof \DateTimeImmutable;
        });
    }
}

// ── In-memory test double ─────────────────────────────────────────────────────

class InMemoryLeadRepository implements LeadRepository
{
    /** @var Lead[] */
    private array $store  = [];
    private int   $nextId = 1;

    public function findById(int $id): ?Lead
    {
        return $this->store[$id] ?? null;
    }

    public function save(Lead $lead): void
    {
        if (! isset($lead->id) || $lead->id === null) {
            $lead->id = $this->nextId++;
        }
        $this->store[$lead->id] = $lead;
    }

    /** @return Lead[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}
