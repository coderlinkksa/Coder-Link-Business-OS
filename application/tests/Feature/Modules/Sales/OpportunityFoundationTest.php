<?php

namespace Tests\Feature\Modules\Sales;

use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\CRM\Domain\Models\Lead;
use App\Modules\Sales\Application\Actions\CreateOpportunityAction;
use App\Modules\Sales\Application\DTOs\CreateOpportunityData;
use App\Modules\Sales\Domain\Contracts\OpportunityRepository;
use App\Modules\Sales\Domain\Enums\OpportunityStage;
use App\Modules\Sales\Domain\Events\OpportunityCreated;
use App\Modules\Sales\Domain\Models\Opportunity;
use App\Modules\Sales\Infrastructure\Listeners\CreateOpportunityOnLeadConversion;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for Sales opportunity creation and the CRM→Sales event bridge.
 * Uses in-memory repository doubles — no database required at this stage.
 */
class OpportunityFoundationTest extends TestCase
{
    private InMemoryOpportunityRepository $opportunityRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->opportunityRepo = new InMemoryOpportunityRepository();
        $this->app->instance(OpportunityRepository::class, $this->opportunityRepo);
    }

    // ── CreateOpportunityAction via container ─────────────────────────────────

    #[Test]
    public function create_opportunity_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateOpportunityAction::class);
        $this->assertInstanceOf(CreateOpportunityAction::class, $action);
    }

    #[Test]
    public function creating_an_opportunity_fires_opportunity_created_event(): void
    {
        Event::fake();

        $action      = $this->app->make(CreateOpportunityAction::class);
        $opportunity = $action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'E-Commerce Platform',
        ));

        Event::assertDispatched(
            OpportunityCreated::class,
            fn ($e) => $e->opportunity->title === 'E-Commerce Platform',
        );

        $this->assertSame(OpportunityStage::Qualification, $opportunity->stage);
    }

    #[Test]
    public function opportunity_is_stored_after_creation(): void
    {
        Event::fake();

        $action = $this->app->make(CreateOpportunityAction::class);
        $action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'Stored Opportunity',
        ));

        $this->assertCount(1, $this->opportunityRepo->all());
        $this->assertSame('Stored Opportunity', $this->opportunityRepo->all()[0]->title);
    }

    // ── Listener: CRM event → Sales Opportunity ───────────────────────────────

    #[Test]
    public function listener_creates_opportunity_when_lead_is_converted(): void
    {
        Event::fake([OpportunityCreated::class]);

        $lead         = new Lead();
        $lead->id     = 5;
        $lead->status = LeadStatus::Converted;
        $lead->fill([
            'name'   => 'Converted Lead',
            'source' => LeadSource::Referral,
            'email'  => 'lead@example.sa',
        ]);

        $conversionData = new ConvertLeadData(
            leadId:           5,
            companyId:        15,
            opportunityTitle: 'CRM Integration Project',
            contactPersonId:  3,
            valueMinorUnits:  10000000,
            probability:      75,
            assignedTo:       2,
        );

        $listener = $this->app->make(CreateOpportunityOnLeadConversion::class);
        $listener->handle(new LeadConvertedToOpportunity($lead, $conversionData));

        $this->assertCount(1, $this->opportunityRepo->all());

        $opportunity = $this->opportunityRepo->all()[0];
        $this->assertSame(15,                           $opportunity->company_id);
        $this->assertSame('CRM Integration Project',    $opportunity->title);
        $this->assertSame(5,                            $opportunity->lead_id);
        $this->assertSame(3,                            $opportunity->contact_person_id);
        $this->assertSame(10000000,                     $opportunity->value_minor_units);
        $this->assertSame(75,                           $opportunity->probability);
    }

    #[Test]
    public function listener_fires_opportunity_created_event(): void
    {
        Event::fake([OpportunityCreated::class]);

        $lead     = new Lead();
        $lead->id = 7;
        $lead->fill(['name' => 'Lead', 'source' => LeadSource::Direct, 'email' => 'x@x.sa']);

        $conversionData = new ConvertLeadData(
            leadId:           7,
            companyId:        20,
            opportunityTitle: 'Event Fired Test',
        );

        $listener = $this->app->make(CreateOpportunityOnLeadConversion::class);
        $listener->handle(new LeadConvertedToOpportunity($lead, $conversionData));

        Event::assertDispatched(
            OpportunityCreated::class,
            fn ($e) => $e->opportunity->title === 'Event Fired Test',
        );
    }

    #[Test]
    public function occurred_at_is_set_on_opportunity_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateOpportunityAction::class);
        $action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'Timestamped Opportunity',
        ));

        Event::assertDispatched(OpportunityCreated::class, function (OpportunityCreated $e) {
            return $e->occurredAt() instanceof \DateTimeImmutable;
        });
    }
}

// ── In-memory test double ─────────────────────────────────────────────────────

class InMemoryOpportunityRepository implements OpportunityRepository
{
    /** @var Opportunity[] */
    private array $store  = [];
    private int   $nextId = 1;

    public function findById(int $id): ?Opportunity
    {
        return $this->store[$id] ?? null;
    }

    public function save(Opportunity $opportunity): void
    {
        if (! isset($opportunity->id) || $opportunity->id === null) {
            $opportunity->id = $this->nextId++;
        }
        $this->store[$opportunity->id] = $opportunity;
    }

    /** @return Opportunity[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}
