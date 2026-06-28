<?php

namespace Tests\Unit\Modules\CRM\Application;

use App\Modules\CRM\Application\Actions\ConvertLeadToOpportunityAction;
use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\CRM\Domain\Exceptions\LeadAlreadyConvertedException;
use App\Modules\CRM\Domain\Exceptions\LeadNotFoundException;
use App\Modules\CRM\Domain\Models\Lead;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConvertLeadToOpportunityActionTest extends TestCase
{
    private function makeRepo(?Lead $lead): LeadRepository
    {
        return new class ($lead) implements LeadRepository {
            public ?Lead $saved = null;

            public function __construct(private readonly ?Lead $lead) {}

            public function findById(int|string $id): ?Lead { return $this->lead; }

            public function save(Lead $lead): void { $this->saved = $lead; }
        };
    }

    private function makeQualifiedLead(int $id = 1): Lead
    {
        $lead = new Lead();
        $lead->id = $id;
        $lead->fill([
            'name'   => 'Ali Al-Qahtani',
            'source' => LeadSource::Website,
            'status' => LeadStatus::Qualified,
            'email'  => 'ali@example.sa',
        ]);
        return $lead;
    }

    private function makeConversionData(int $leadId = 1): ConvertLeadData
    {
        return new ConvertLeadData(
            leadId:           $leadId,
            companyId:        10,
            opportunityTitle: 'Website Redesign Project',
        );
    }

    #[Test]
    public function it_marks_the_lead_as_converted(): void
    {
        Event::fake();

        $lead   = $this->makeQualifiedLead();
        $action = new ConvertLeadToOpportunityAction($this->makeRepo($lead));

        $result = $action->execute($this->makeConversionData());

        $this->assertSame(LeadStatus::Converted, $result->status);
    }

    #[Test]
    public function it_fires_lead_converted_to_opportunity_event(): void
    {
        Event::fake();

        $lead   = $this->makeQualifiedLead();
        $action = new ConvertLeadToOpportunityAction($this->makeRepo($lead));

        $action->execute($this->makeConversionData());

        Event::assertDispatched(
            LeadConvertedToOpportunity::class,
            fn ($e) => $e->lead->id === 1
                    && $e->conversionData->companyId === 10
                    && $e->conversionData->opportunityTitle === 'Website Redesign Project',
        );
    }

    #[Test]
    public function it_persists_the_updated_lead_status(): void
    {
        Event::fake();

        $lead = $this->makeQualifiedLead();
        $repo = $this->makeRepo($lead);
        (new ConvertLeadToOpportunityAction($repo))->execute($this->makeConversionData());

        $this->assertSame(LeadStatus::Converted, $repo->saved->status);
    }

    #[Test]
    public function it_throws_when_lead_does_not_exist(): void
    {
        Event::fake();

        $action = new ConvertLeadToOpportunityAction($this->makeRepo(null));

        $this->expectException(LeadNotFoundException::class);

        $action->execute($this->makeConversionData(999));
    }

    #[Test]
    public function it_throws_when_lead_is_already_converted(): void
    {
        Event::fake();

        $lead         = $this->makeQualifiedLead();
        $lead->status = LeadStatus::Converted;

        $action = new ConvertLeadToOpportunityAction($this->makeRepo($lead));

        $this->expectException(LeadAlreadyConvertedException::class);

        $action->execute($this->makeConversionData());
    }
}
