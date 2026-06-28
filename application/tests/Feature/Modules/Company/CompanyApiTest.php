<?php

namespace Tests\Feature\Modules\Company;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Modules\Company\Domain\Enums\ContactRole;
use App\Modules\Company\Domain\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/companies ───────────────────────────────────────────────────

    #[Test]
    public function it_creates_a_company_and_returns_201(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'Coder Link KSA',
            'type' => 'customer',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'type', 'status',
                    'industry', 'phone', 'email',
                    'website', 'address', 'city', 'country', 'created_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Coder Link KSA')
            ->assertJsonPath('data.type', 'customer')
            ->assertJsonPath('data.status', 'new');
    }

    #[Test]
    public function it_persists_the_company_to_the_database(): void
    {
        $this->postJson('/api/companies', [
            'name' => 'Persisted Corp',
            'type' => 'partner',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Persisted Corp',
            'type' => 'partner',
        ]);
    }

    #[Test]
    public function it_returns_a_uuid_as_the_company_id(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'UUID Corp',
            'type' => 'vendor',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_accepts_all_optional_company_fields(): void
    {
        $response = $this->postJson('/api/companies', [
            'name'     => 'Full Corp',
            'type'     => 'customer',
            'status'   => 'active',
            'industry' => 'Technology',
            'phone'    => '+966501234567',
            'email'    => 'info@full.sa',
            'website'  => 'https://full.sa',
            'address'  => 'King Fahd Road',
            'city'     => 'Riyadh',
            'country'  => 'Saudi Arabia',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.industry', 'Technology')
            ->assertJsonPath('data.city', 'Riyadh');
    }

    #[Test]
    public function it_returns_422_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/companies', [
            'type' => 'customer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_returns_422_when_type_is_missing(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'No Type Corp',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function it_returns_422_when_type_is_invalid(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'Bad Type Corp',
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function it_returns_422_when_status_is_invalid(): void
    {
        $response = $this->postJson('/api/companies', [
            'name'   => 'Bad Status Corp',
            'type'   => 'customer',
            'status' => 'flying',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_returns_422_when_email_is_malformed(): void
    {
        $response = $this->postJson('/api/companies', [
            'name'  => 'Bad Email Corp',
            'type'  => 'customer',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_all_validation_errors_in_one_response(): void
    {
        $response = $this->postJson('/api/companies', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type']);
    }

    // ── POST /api/companies/{id}/contacts ─────────────────────────────────────

    #[Test]
    public function it_creates_a_contact_for_an_existing_company_and_returns_201(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $response = $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'first_name' => 'Ahmed',
            'last_name'  => 'Al-Rashid',
            'role'       => 'decision_maker',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'company_id', 'first_name', 'last_name',
                    'full_name', 'role', 'email', 'phone', 'is_primary', 'created_at',
                ],
            ])
            ->assertJsonPath('data.first_name', 'Ahmed')
            ->assertJsonPath('data.last_name', 'Al-Rashid')
            ->assertJsonPath('data.full_name', 'Ahmed Al-Rashid')
            ->assertJsonPath('data.role', 'decision_maker')
            ->assertJsonPath('data.company_id', $company->getKey());
    }

    #[Test]
    public function it_persists_the_contact_to_the_database(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'first_name' => 'Sara',
            'last_name'  => 'Al-Otaibi',
            'role'       => 'primary',
        ]);

        $this->assertDatabaseHas('contact_persons', [
            'first_name' => 'Sara',
            'last_name'  => 'Al-Otaibi',
            'company_id' => $company->getKey(),
        ]);
    }

    #[Test]
    public function it_returns_contact_uuid(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $response = $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'first_name' => 'Omar',
            'last_name'  => 'Hassan',
            'role'       => 'technical',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_returns_404_when_company_does_not_exist(): void
    {
        $response = $this->postJson('/api/companies/00000000-0000-0000-0000-000000000000/contacts', [
            'first_name' => 'Ghost',
            'last_name'  => 'User',
            'role'       => 'other',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'not found'));
    }

    #[Test]
    public function it_returns_422_when_contact_first_name_is_missing(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $response = $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'last_name' => 'Al-Rashid',
            'role'      => 'primary',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    #[Test]
    public function it_returns_422_when_contact_role_is_invalid(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $response = $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'first_name' => 'Ahmed',
            'last_name'  => 'Ali',
            'role'       => 'unknown_role',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    #[Test]
    public function it_sets_is_primary_flag_when_provided(): void
    {
        $company = Company::create([
            'name' => 'Host Corp',
            'type' => CompanyType::Customer->value,
        ]);

        $response = $this->postJson("/api/companies/{$company->getKey()}/contacts", [
            'first_name' => 'Primary',
            'last_name'  => 'Person',
            'role'       => 'primary',
            'is_primary' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_primary', true);
    }
}
