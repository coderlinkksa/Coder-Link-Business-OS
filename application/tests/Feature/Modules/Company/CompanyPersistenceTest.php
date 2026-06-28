<?php

namespace Tests\Feature\Modules\Company;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Modules\Company\Domain\Enums\ContactRole;
use App\Modules\Company\Domain\Models\Company;
use App\Modules\Company\Domain\Models\ContactPerson;
use App\Modules\Company\Infrastructure\Repositories\EloquentCompanyRepository;
use App\Modules\Company\Infrastructure\Repositories\EloquentContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeCompany(array $attributes = []): Company
    {
        return Company::create(array_merge([
            'name' => 'Test Company',
            'type' => CompanyType::Customer->value,
        ], $attributes));
    }

    private function makeContact(Company $company, array $attributes = []): ContactPerson
    {
        return ContactPerson::create(array_merge([
            'company_id' => $company->getKey(),
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'role'       => ContactRole::Primary->value,
        ], $attributes));
    }

    // ── UUID primary keys ─────────────────────────────────────────────────────

    #[Test]
    public function company_primary_key_is_a_uuid(): void
    {
        $company = $this->makeCompany();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $company->getKey(),
        );
    }

    #[Test]
    public function contact_person_primary_key_is_a_uuid(): void
    {
        $contact = $this->makeContact($this->makeCompany());

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $contact->getKey(),
        );
    }

    #[Test]
    public function each_company_receives_a_unique_uuid(): void
    {
        $a = $this->makeCompany(['name' => 'Alpha Corp']);
        $b = $this->makeCompany(['name' => 'Beta Corp']);

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    // ── Company column persistence ────────────────────────────────────────────

    #[Test]
    public function company_can_be_retrieved_by_uuid(): void
    {
        $saved = $this->makeCompany(['name' => 'Lookup Corp']);
        $found = Company::find($saved->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Lookup Corp', $found->name);
    }

    #[Test]
    public function company_type_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeCompany(['type' => CompanyType::Partner->value]);

        $this->assertSame(CompanyType::Partner, Company::first()->type);
    }

    #[Test]
    public function company_status_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeCompany(['status' => CompanyStatus::Inactive->value]);

        $this->assertSame(CompanyStatus::Inactive, Company::first()->status);
    }

    #[Test]
    public function company_status_defaults_to_new_when_not_provided(): void
    {
        // Create without status — DB default should apply.
        Company::create(['name' => 'Default Status Co', 'type' => CompanyType::Lead->value]);

        $this->assertSame(CompanyStatus::New, Company::first()->status);
    }

    #[Test]
    public function all_nullable_company_fields_persist_correctly(): void
    {
        $this->makeCompany([
            'industry' => 'Technology',
            'phone'    => '+966-11-000-0000',
            'email'    => 'info@example.sa',
            'website'  => 'https://example.sa',
            'address'  => 'King Fahd Road',
            'city'     => 'Riyadh',
            'country'  => 'SA',
        ]);

        $found = Company::first();
        $this->assertSame('Technology',        $found->industry);
        $this->assertSame('+966-11-000-0000',  $found->phone);
        $this->assertSame('info@example.sa',   $found->email);
        $this->assertSame('https://example.sa', $found->website);
        $this->assertSame('King Fahd Road',    $found->address);
        $this->assertSame('Riyadh',            $found->city);
        $this->assertSame('SA',                $found->country);
    }

    #[Test]
    public function full_address_helper_works_after_persistence(): void
    {
        $this->makeCompany([
            'address' => 'King Fahd Road',
            'city'    => 'Riyadh',
            'country' => 'SA',
        ]);

        $this->assertSame('King Fahd Road, Riyadh, SA', Company::first()->fullAddress());
    }

    #[Test]
    public function is_active_helper_reflects_persisted_status(): void
    {
        $active   = $this->makeCompany(['name' => 'Active Co',   'status' => CompanyStatus::Active->value]);
        $inactive = $this->makeCompany(['name' => 'Inactive Co', 'status' => CompanyStatus::Inactive->value]);

        $this->assertTrue(Company::find($active->getKey())->isActive());
        $this->assertFalse(Company::find($inactive->getKey())->isActive());
    }

    // ── Soft deletes ──────────────────────────────────────────────────────────

    #[Test]
    public function soft_deleting_a_company_sets_deleted_at(): void
    {
        $company = $this->makeCompany();
        $company->delete();

        $this->assertNotNull(Company::withTrashed()->find($company->getKey())->deleted_at);
    }

    #[Test]
    public function soft_deleted_company_is_excluded_from_default_queries(): void
    {
        $company = $this->makeCompany();
        $company->delete();

        $this->assertNull(Company::find($company->getKey()));
        $this->assertNotNull(Company::withTrashed()->find($company->getKey()));
    }

    // ── Company → ContactPerson relationship ──────────────────────────────────

    #[Test]
    public function company_can_have_multiple_contact_persons(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['first_name' => 'Alice']);
        $this->makeContact($company, ['first_name' => 'Bob']);

        $this->assertCount(2, $company->contacts()->get());
    }

    #[Test]
    public function company_with_no_contacts_returns_empty_collection(): void
    {
        $company = $this->makeCompany();

        $this->assertCount(0, $company->contacts()->get());
    }

    #[Test]
    public function primary_contact_helper_returns_the_flagged_contact(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['first_name' => 'Secondary', 'is_primary' => false]);
        $this->makeContact($company, ['first_name' => 'Primary',   'is_primary' => true]);

        $primary = $company->primaryContact();
        $this->assertNotNull($primary);
        $this->assertSame('Primary', $primary->first_name);
    }

    #[Test]
    public function primary_contact_returns_null_when_none_is_flagged(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['is_primary' => false]);

        $this->assertNull($company->primaryContact());
    }

    // ── ContactPerson column persistence ──────────────────────────────────────

    #[Test]
    public function contact_role_is_cast_to_enum_after_retrieval(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['role' => ContactRole::DecisionMaker->value]);

        $this->assertSame(ContactRole::DecisionMaker, ContactPerson::first()->role);
    }

    #[Test]
    public function is_primary_is_cast_to_boolean_after_retrieval(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['is_primary' => true]);

        $this->assertTrue(ContactPerson::first()->is_primary);
    }

    #[Test]
    public function contact_full_name_helper_works_after_persistence(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['first_name' => 'Ahmad', 'last_name' => 'Al-Rashid']);

        $this->assertSame('Ahmad Al-Rashid', ContactPerson::first()->fullName());
    }

    // ── ContactPerson → Company relationship ──────────────────────────────────

    #[Test]
    public function contact_belongs_to_its_company(): void
    {
        $company = $this->makeCompany(['name' => 'Parent Corp']);
        $this->makeContact($company);

        $found = ContactPerson::first();
        $this->assertSame($company->getKey(), $found->company->getKey());
        $this->assertSame('Parent Corp', $found->company->name);
    }

    #[Test]
    public function contacts_from_different_companies_do_not_cross(): void
    {
        $companyA = $this->makeCompany(['name' => 'Company A']);
        $companyB = $this->makeCompany(['name' => 'Company B']);

        $this->makeContact($companyA, ['first_name' => 'Alice']);
        $this->makeContact($companyB, ['first_name' => 'Bob']);

        $this->assertCount(1, $companyA->contacts()->get());
        $this->assertSame('Alice', $companyA->contacts()->first()->first_name);

        $this->assertCount(1, $companyB->contacts()->get());
        $this->assertSame('Bob', $companyB->contacts()->first()->first_name);
    }

    // ── Eloquent repository round-trips ───────────────────────────────────────

    #[Test]
    public function eloquent_company_repository_saves_and_finds_by_uuid(): void
    {
        $repo    = new EloquentCompanyRepository();
        $company = new Company();
        $company->forceFill([
            'name' => 'Repo Corp',
            'type' => CompanyType::Vendor->value,
        ]);
        $repo->save($company);

        $found = $repo->findById($company->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Repo Corp', $found->name);
        $this->assertSame(CompanyType::Vendor, $found->type);
    }

    #[Test]
    public function eloquent_contact_repository_saves_and_finds_by_uuid(): void
    {
        $companyRepo = new EloquentCompanyRepository();
        $contactRepo = new EloquentContactRepository();

        $company = new Company();
        $company->forceFill(['name' => 'Host Corp', 'type' => CompanyType::Customer->value]);
        $companyRepo->save($company);

        $contact = new ContactPerson();
        $contact->forceFill([
            'company_id' => $company->getKey(),
            'first_name' => 'Sara',
            'last_name'  => 'Ahmed',
            'role'       => ContactRole::Technical->value,
        ]);
        $contactRepo->save($contact);

        $found = $contactRepo->findById($contact->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Sara', $found->first_name);
        $this->assertSame(ContactRole::Technical, $found->role);
    }

    #[Test]
    public function repository_returns_null_for_nonexistent_uuid(): void
    {
        $repo = new EloquentCompanyRepository();

        $this->assertNull($repo->findById('00000000-0000-0000-0000-000000000000'));
    }

    // ── Index-backed queries ──────────────────────────────────────────────────

    #[Test]
    public function companies_can_be_filtered_by_type(): void
    {
        $this->makeCompany(['name' => 'Customer A', 'type' => CompanyType::Customer->value]);
        $this->makeCompany(['name' => 'Partner A',  'type' => CompanyType::Partner->value]);
        $this->makeCompany(['name' => 'Customer B', 'type' => CompanyType::Customer->value]);

        $customers = Company::where('type', CompanyType::Customer->value)->get();
        $this->assertCount(2, $customers);
    }

    #[Test]
    public function companies_can_be_filtered_by_status(): void
    {
        $this->makeCompany(['name' => 'Active 1',   'status' => CompanyStatus::Active->value]);
        $this->makeCompany(['name' => 'Inactive 1', 'status' => CompanyStatus::Inactive->value]);
        $this->makeCompany(['name' => 'Active 2',   'status' => CompanyStatus::Active->value]);

        $active = Company::where('status', CompanyStatus::Active->value)->get();
        $this->assertCount(2, $active);
    }

    #[Test]
    public function contacts_can_be_filtered_by_is_primary(): void
    {
        $company = $this->makeCompany();
        $this->makeContact($company, ['first_name' => 'Primary',     'is_primary' => true]);
        $this->makeContact($company, ['first_name' => 'Not Primary', 'is_primary' => false]);

        $primary = ContactPerson::where('is_primary', true)->get();
        $this->assertCount(1, $primary);
        $this->assertSame('Primary', $primary->first()->first_name);
    }
}
