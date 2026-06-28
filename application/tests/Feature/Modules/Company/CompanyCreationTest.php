<?php

namespace Tests\Feature\Modules\Company;

use App\Modules\Company\Application\Actions\CreateCompanyAction;
use App\Modules\Company\Application\Actions\CreateContactAction;
use App\Modules\Company\Application\DTOs\CreateCompanyData;
use App\Modules\Company\Application\DTOs\CreateContactData;
use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Modules\Company\Domain\Enums\ContactRole;
use App\Modules\Company\Domain\Events\CompanyCreated;
use App\Modules\Company\Domain\Events\ContactCreated;
use App\Modules\Company\Domain\Exceptions\CompanyNotFoundException;
use App\Modules\Company\Domain\Models\Company;
use App\Modules\Company\Domain\Models\ContactPerson;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the Company module creation flow.
 * Uses in-memory repository doubles — no database required at this stage.
 */
class CompanyCreationTest extends TestCase
{
    private InMemoryCompanyRepository $companyRepo;
    private InMemoryContactRepository $contactRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyRepo = new InMemoryCompanyRepository();
        $this->contactRepo = new InMemoryContactRepository();

        $this->app->instance(CompanyRepository::class, $this->companyRepo);
        $this->app->instance(ContactRepository::class, $this->contactRepo);
    }

    // ── CreateCompanyAction via container ─────────────────────────────────────

    #[Test]
    public function create_company_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateCompanyAction::class);
        $this->assertInstanceOf(CreateCompanyAction::class, $action);
    }

    #[Test]
    public function creating_a_company_fires_company_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateCompanyAction::class);

        $company = $action->execute(new CreateCompanyData(
            name: 'Coder Link Client Co.',
            type: CompanyType::Customer,
        ));

        Event::assertDispatched(
            CompanyCreated::class,
            fn ($e) => $e->company->name === 'Coder Link Client Co.',
        );

        $this->assertSame(CompanyStatus::New, $company->status);
    }

    #[Test]
    public function company_created_event_carries_the_saved_company(): void
    {
        Event::fake();

        $action = $this->app->make(CreateCompanyAction::class);

        $action->execute(new CreateCompanyData(
            name:    'Tech Partners Ltd',
            type:    CompanyType::Partner,
            city:    'Riyadh',
            country: 'Saudi Arabia',
        ));

        Event::assertDispatched(CompanyCreated::class, function (CompanyCreated $e) {
            return $e->company->name    === 'Tech Partners Ltd'
                && $e->company->city    === 'Riyadh'
                && $e->company->country === 'Saudi Arabia';
        });
    }

    #[Test]
    public function company_is_stored_in_repository_after_creation(): void
    {
        Event::fake();

        $action = $this->app->make(CreateCompanyAction::class);

        $action->execute(new CreateCompanyData(
            name: 'Stored Company',
            type: CompanyType::Lead,
        ));

        $this->assertCount(1, $this->companyRepo->all());
        $this->assertSame('Stored Company', $this->companyRepo->all()[0]->name);
    }

    // ── CreateContactAction via container ─────────────────────────────────────

    #[Test]
    public function create_contact_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateContactAction::class);
        $this->assertInstanceOf(CreateContactAction::class, $action);
    }

    #[Test]
    public function creating_a_contact_fires_contact_created_event(): void
    {
        Event::fake();

        $companyAction = $this->app->make(CreateCompanyAction::class);
        $company       = $companyAction->execute(new CreateCompanyData(
            name: 'Host Company',
            type: CompanyType::Customer,
        ));

        $contactAction = $this->app->make(CreateContactAction::class);
        $contact       = $contactAction->execute(new CreateContactData(
            companyId: $company->id,
            firstName: 'Fatima',
            lastName:  'Al-Zahrani',
            role:      ContactRole::DecisionMaker,
        ));

        Event::assertDispatched(
            ContactCreated::class,
            fn ($e) => $e->contact->first_name === 'Fatima',
        );

        $this->assertSame($company->id, $contact->company_id);
    }

    #[Test]
    public function creating_a_contact_for_missing_company_throws(): void
    {
        Event::fake();

        $action = $this->app->make(CreateContactAction::class);

        $this->expectException(CompanyNotFoundException::class);

        $action->execute(new CreateContactData(
            companyId: 9999,
            firstName: 'Ghost',
            lastName:  'Person',
            role:      ContactRole::Other,
        ));
    }

    #[Test]
    public function contact_is_stored_in_repository_after_creation(): void
    {
        Event::fake();

        $companyAction = $this->app->make(CreateCompanyAction::class);
        $company       = $companyAction->execute(new CreateCompanyData(
            name: 'Company With Contact',
            type: CompanyType::Customer,
        ));

        $contactAction = $this->app->make(CreateContactAction::class);
        $contactAction->execute(new CreateContactData(
            companyId: $company->id,
            firstName: 'Khalid',
            lastName:  'Al-Saud',
            role:      ContactRole::Primary,
            isPrimary: true,
        ));

        $this->assertCount(1, $this->contactRepo->all());
        $this->assertSame('Khalid', $this->contactRepo->all()[0]->first_name);
        $this->assertTrue($this->contactRepo->all()[0]->is_primary);
    }

    #[Test]
    public function occurred_at_is_set_on_company_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateCompanyAction::class);
        $action->execute(new CreateCompanyData(
            name: 'Timestamped Co',
            type: CompanyType::Customer,
        ));

        Event::assertDispatched(CompanyCreated::class, function (CompanyCreated $e) {
            return $e->occurredAt() instanceof \DateTimeImmutable;
        });
    }
}

// ── In-memory test doubles ────────────────────────────────────────────────────

class InMemoryCompanyRepository implements CompanyRepository
{
    /** @var Company[] */
    private array $store = [];
    private int   $nextId = 1;

    public function findById(int|string $id): ?Company
    {
        foreach ($this->store as $company) {
            if ((string) $company->id === (string) $id) {
                return $company;
            }
        }
        return null;
    }

    public function save(Company $company): void
    {
        if (! isset($company->id) || $company->id === null) {
            $company->id = $this->nextId++;
        }
        $this->store[$company->id] = $company;
    }

    /** @return Company[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}

class InMemoryContactRepository implements ContactRepository
{
    /** @var ContactPerson[] */
    private array $store = [];
    private int   $nextId = 1;

    public function findById(int|string $id): ?ContactPerson
    {
        foreach ($this->store as $contact) {
            if ((string) $contact->id === (string) $id) {
                return $contact;
            }
        }
        return null;
    }

    public function save(ContactPerson $contact): void
    {
        if (! isset($contact->id) || $contact->id === null) {
            $contact->id = $this->nextId++;
        }
        $this->store[$contact->id] = $contact;
    }

    /** @return ContactPerson[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}
