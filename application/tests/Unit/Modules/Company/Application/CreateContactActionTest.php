<?php

namespace Tests\Unit\Modules\Company\Application;

use App\Modules\Company\Application\Actions\CreateContactAction;
use App\Modules\Company\Application\DTOs\CreateContactData;
use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Modules\Company\Domain\Enums\ContactRole;
use App\Modules\Company\Domain\Events\ContactCreated;
use App\Modules\Company\Domain\Exceptions\CompanyNotFoundException;
use App\Modules\Company\Domain\Models\Company;
use App\Modules\Company\Domain\Models\ContactPerson;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateContactActionTest extends TestCase
{
    private function makeCompanyRepo(?Company $company): CompanyRepository
    {
        return new class ($company) implements CompanyRepository {
            public function __construct(private readonly ?Company $company) {}

            public function findById(int|string $id): ?Company { return $this->company; }

            public function save(Company $c): void {}
        };
    }

    private function makeContactRepo(): ContactRepository
    {
        return new class implements ContactRepository {
            public ?ContactPerson $saved = null;

            public function findById(int|string $id): ?ContactPerson { return null; }

            public function save(ContactPerson $contact): void
            {
                $contact->id  = 1;
                $this->saved  = $contact;
            }
        };
    }

    private function makeExistingCompany(): Company
    {
        $company     = new Company();
        $company->id = 42;
        $company->fill(['name' => 'Acme Corp', 'type' => CompanyType::Customer]);
        return $company;
    }

    #[Test]
    public function it_creates_a_contact_linked_to_the_company(): void
    {
        Event::fake();

        $contactRepo = $this->makeContactRepo();
        $action      = new CreateContactAction(
            $this->makeCompanyRepo($this->makeExistingCompany()),
            $contactRepo,
        );

        $data = new CreateContactData(
            companyId: 42,
            firstName: 'Ahmed',
            lastName:  'Al-Rashid',
            role:      ContactRole::DecisionMaker,
        );

        $contact = $action->execute($data);

        $this->assertInstanceOf(ContactPerson::class, $contact);
        $this->assertSame('Ahmed',    $contact->first_name);
        $this->assertSame('Al-Rashid', $contact->last_name);
        $this->assertSame(42,         $contact->company_id);
        $this->assertSame(ContactRole::DecisionMaker, $contact->role);
    }

    #[Test]
    public function it_persists_the_contact_through_the_repository(): void
    {
        Event::fake();

        $contactRepo = $this->makeContactRepo();
        $action      = new CreateContactAction(
            $this->makeCompanyRepo($this->makeExistingCompany()),
            $contactRepo,
        );

        $data = new CreateContactData(
            companyId: 42,
            firstName: 'Sara',
            lastName:  'Al-Otaibi',
            role:      ContactRole::Technical,
        );

        $action->execute($data);

        $this->assertNotNull($contactRepo->saved);
        $this->assertSame('Sara', $contactRepo->saved->first_name);
    }

    #[Test]
    public function it_fires_contact_created_event(): void
    {
        Event::fake();

        $action = new CreateContactAction(
            $this->makeCompanyRepo($this->makeExistingCompany()),
            $this->makeContactRepo(),
        );

        $data = new CreateContactData(
            companyId: 42,
            firstName: 'Omar',
            lastName:  'Hassan',
            role:      ContactRole::Primary,
        );

        $contact = $action->execute($data);

        Event::assertDispatched(
            ContactCreated::class,
            fn ($e) => $e->contact === $contact,
        );
    }

    #[Test]
    public function it_throws_when_company_does_not_exist(): void
    {
        Event::fake();

        $action = new CreateContactAction(
            $this->makeCompanyRepo(null),
            $this->makeContactRepo(),
        );

        $this->expectException(CompanyNotFoundException::class);

        $action->execute(new CreateContactData(
            companyId: 999,
            firstName: 'Ghost',
            lastName:  'User',
            role:      ContactRole::Other,
        ));
    }

    #[Test]
    public function it_sets_primary_flag_correctly(): void
    {
        Event::fake();

        $contactRepo = $this->makeContactRepo();
        $action      = new CreateContactAction(
            $this->makeCompanyRepo($this->makeExistingCompany()),
            $contactRepo,
        );

        $data = new CreateContactData(
            companyId: 42,
            firstName: 'Primary',
            lastName:  'Person',
            role:      ContactRole::Primary,
            isPrimary: true,
        );

        $action->execute($data);

        $this->assertTrue($contactRepo->saved->is_primary);
    }
}
