<?php

namespace Tests\Unit\Modules\Sales\Application;

use App\Modules\Sales\Application\Actions\CreateOpportunityAction;
use App\Modules\Sales\Application\DTOs\CreateOpportunityData;
use App\Modules\Sales\Domain\Contracts\OpportunityRepository;
use App\Modules\Sales\Domain\Enums\OpportunityStage;
use App\Modules\Sales\Domain\Events\OpportunityCreated;
use App\Modules\Sales\Domain\Models\Opportunity;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateOpportunityActionTest extends TestCase
{
    private OpportunityRepository $repository;
    private CreateOpportunityAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements OpportunityRepository {
            public ?Opportunity $saved = null;

            public function findById(int|string $id): ?Opportunity { return null; }

            public function save(Opportunity $opportunity): void
            {
                $opportunity->id  = 1;
                $this->saved      = $opportunity;
            }
        };

        $this->action = new CreateOpportunityAction($this->repository);
    }

    #[Test]
    public function it_creates_an_opportunity_in_qualification_stage_by_default(): void
    {
        Event::fake();

        $opportunity = $this->action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'CRM Platform Build',
        ));

        $this->assertInstanceOf(Opportunity::class, $opportunity);
        $this->assertSame('CRM Platform Build', $opportunity->title);
        $this->assertSame(OpportunityStage::Qualification, $opportunity->stage);
        $this->assertSame(10, $opportunity->company_id);
    }

    #[Test]
    public function it_fires_opportunity_created_event(): void
    {
        Event::fake();

        $opportunity = $this->action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'E-commerce Portal',
        ));

        Event::assertDispatched(
            OpportunityCreated::class,
            fn ($e) => $e->opportunity === $opportunity,
        );
    }

    #[Test]
    public function it_persists_the_opportunity_through_the_repository(): void
    {
        Event::fake();

        $this->action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'Stored Opportunity',
        ));

        $this->assertNotNull($this->repository->saved);
        $this->assertSame('Stored Opportunity', $this->repository->saved->title);
    }

    #[Test]
    public function it_maps_all_optional_fields(): void
    {
        Event::fake();

        $opportunity = $this->action->execute(new CreateOpportunityData(
            companyId:         10,
            title:             'Full Opportunity',
            stage:             OpportunityStage::DiscoveryMeeting,
            leadId:            3,
            contactPersonId:   7,
            valueMinorUnits:   5000000,
            probability:       60,
            expectedCloseDate: '2026-09-30',
            assignedTo:        2,
            notes:             'High-priority deal.',
        ));

        $this->assertSame(OpportunityStage::DiscoveryMeeting, $opportunity->stage);
        $this->assertSame(3,          $opportunity->lead_id);
        $this->assertSame(7,          $opportunity->contact_person_id);
        $this->assertSame(5000000,    $opportunity->value_minor_units);
        $this->assertSame(60,         $opportunity->probability);
        $this->assertSame(2,          $opportunity->assigned_to);
        $this->assertSame('High-priority deal.', $opportunity->notes);
    }

    #[Test]
    public function new_opportunity_is_open(): void
    {
        Event::fake();

        $opportunity = $this->action->execute(new CreateOpportunityData(
            companyId: 10,
            title:     'Open Opportunity',
        ));

        $this->assertTrue($opportunity->isOpen());
    }
}
