<?php

namespace Tests\Unit\Modules\CRM\Application;

use App\Modules\CRM\Application\Actions\CreateLeadAction;
use App\Modules\CRM\Application\DTOs\CreateLeadData;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Events\LeadCreated;
use App\Modules\CRM\Domain\Models\Lead;
use App\Shared\Exceptions\ValidationException;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateLeadActionTest extends TestCase
{
    private LeadRepository $repository;
    private CreateLeadAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements LeadRepository {
            public ?Lead $saved = null;

            public function findById(int|string $id): ?Lead { return null; }

            public function save(Lead $lead): void
            {
                $lead->id    = 1;
                $this->saved = $lead;
            }
        };

        $this->action = new CreateLeadAction($this->repository);
    }

    #[Test]
    public function it_creates_a_lead_with_new_status(): void
    {
        Event::fake();

        $lead = $this->action->execute(new CreateLeadData(
            name:   'Ali Al-Qahtani',
            source: LeadSource::Website,
            email:  'ali@example.sa',
        ));

        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertSame('Ali Al-Qahtani', $lead->name);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame(LeadSource::Website, $lead->source);
    }

    #[Test]
    public function it_fires_lead_created_event(): void
    {
        Event::fake();

        $lead = $this->action->execute(new CreateLeadData(
            name:   'Sara Hassan',
            source: LeadSource::Referral,
            phone:  '+966501234567',
        ));

        Event::assertDispatched(
            LeadCreated::class,
            fn ($e) => $e->lead === $lead,
        );
    }

    #[Test]
    public function it_persists_the_lead_through_the_repository(): void
    {
        Event::fake();

        $this->action->execute(new CreateLeadData(
            name:   'Test Lead',
            source: LeadSource::Direct,
            email:  'test@example.com',
        ));

        $this->assertNotNull($this->repository->saved);
        $this->assertSame('Test Lead', $this->repository->saved->name);
    }

    #[Test]
    public function it_rejects_a_lead_with_no_email_and_no_phone(): void
    {
        Event::fake();

        $this->expectException(ValidationException::class);

        $this->action->execute(new CreateLeadData(
            name:   'No Contact Info',
            source: LeadSource::Other,
        ));
    }

    #[Test]
    public function it_accepts_a_lead_with_only_an_email(): void
    {
        Event::fake();

        $lead = $this->action->execute(new CreateLeadData(
            name:   'Email Only',
            source: LeadSource::Website,
            email:  'emailonly@example.sa',
        ));

        $this->assertSame('emailonly@example.sa', $lead->email);
        $this->assertNull($lead->phone);
    }

    #[Test]
    public function it_accepts_a_lead_with_only_a_phone(): void
    {
        Event::fake();

        $lead = $this->action->execute(new CreateLeadData(
            name:   'Phone Only',
            source: LeadSource::Direct,
            phone:  '+966501111111',
        ));

        $this->assertSame('+966501111111', $lead->phone);
        $this->assertNull($lead->email);
    }

    #[Test]
    public function it_maps_optional_fields_correctly(): void
    {
        Event::fake();

        $lead = $this->action->execute(new CreateLeadData(
            name:             'Full Lead',
            source:           LeadSource::Referral,
            email:            'full@example.sa',
            phone:            '+966509999999',
            serviceRequested: 'Website Development',
            companyId:        5,
            contactPersonId:  3,
            assignedTo:       2,
            notes:            'Came via partner referral.',
        ));

        $this->assertSame('Website Development', $lead->service_requested);
        $this->assertSame(5,                     $lead->company_id);
        $this->assertSame(3,                     $lead->contact_person_id);
        $this->assertSame(2,                     $lead->assigned_to);
        $this->assertSame('Came via partner referral.', $lead->notes);
    }
}
