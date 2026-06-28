<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Models\Lead;
use App\Modules\Sales\Domain\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/leads ───────────────────────────────────────────────────────

    #[Test]
    public function it_creates_a_lead_and_returns_201(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'Ali Al-Qahtani',
            'source' => 'website',
            'email'  => 'ali@example.sa',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'source', 'status',
                    'email', 'phone', 'service_requested',
                    'company_id', 'contact_person_id',
                    'notes', 'converted_at', 'created_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Ali Al-Qahtani')
            ->assertJsonPath('data.source', 'website')
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.converted_at', null);
    }

    #[Test]
    public function it_persists_the_lead_to_the_database(): void
    {
        $this->postJson('/api/leads', [
            'name'   => 'Persisted Lead',
            'source' => 'referral',
            'email'  => 'persisted@example.sa',
        ]);

        $this->assertDatabaseHas('leads', [
            'name'   => 'Persisted Lead',
            'source' => 'referral',
            'status' => 'new',
        ]);
    }

    #[Test]
    public function it_returns_a_uuid_as_the_lead_id(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'UUID Lead',
            'source' => 'direct',
            'phone'  => '+966501234567',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_accepts_a_lead_with_only_a_phone(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'Phone Only Lead',
            'source' => 'direct',
            'phone'  => '+966509999999',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.phone', '+966509999999')
            ->assertJsonPath('data.email', null);
    }

    #[Test]
    public function it_accepts_all_optional_lead_fields(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'              => 'Full Lead',
            'source'            => 'referral',
            'email'             => 'full@example.sa',
            'phone'             => '+966501111111',
            'service_requested' => 'Website Development',
            'notes'             => 'High-priority prospect.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.service_requested', 'Website Development')
            ->assertJsonPath('data.notes', 'High-priority prospect.');
    }

    #[Test]
    public function it_returns_422_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/leads', [
            'source' => 'website',
            'email'  => 'test@example.sa',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_returns_422_when_source_is_missing(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'  => 'No Source',
            'email' => 'test@example.sa',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source']);
    }

    #[Test]
    public function it_returns_422_when_source_is_invalid(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'Bad Source Lead',
            'source' => 'pigeon_post',
            'email'  => 'test@example.sa',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source']);
    }

    #[Test]
    public function it_returns_422_when_neither_email_nor_phone_is_provided(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'No Contact Info',
            'source' => 'website',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_422_when_email_is_malformed(): void
    {
        $response = $this->postJson('/api/leads', [
            'name'   => 'Bad Email Lead',
            'source' => 'website',
            'email'  => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ── POST /api/leads/{leadId}/convert ──────────────────────────────────────

    #[Test]
    public function it_converts_a_lead_and_returns_200_with_updated_lead(): void
    {
        $lead = Lead::create([
            'name'   => 'Qualified Lead',
            'source' => LeadSource::Referral->value,
            'status' => LeadStatus::Qualified->value,
            'email'  => 'qualified@example.sa',
        ]);

        $companyUuid = 'aaaaaaaa-0000-0000-0000-000000000001';

        $response = $this->postJson("/api/leads/{$lead->getKey()}/convert", [
            'company_id'        => $companyUuid,
            'opportunity_title' => 'Website Redesign Project',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'converted')
            ->assertJsonPath('data.id', $lead->getKey());

        $this->assertNotNull($response->json('data.converted_at'));
    }

    #[Test]
    public function it_creates_an_opportunity_via_listener_on_conversion(): void
    {
        $lead = Lead::create([
            'name'   => 'Qualifiable Lead',
            'source' => LeadSource::Website->value,
            'status' => LeadStatus::Qualified->value,
            'email'  => 'qualifiable@example.sa',
        ]);

        $companyUuid = 'aaaaaaaa-0000-0000-0000-000000000001';

        $this->postJson("/api/leads/{$lead->getKey()}/convert", [
            'company_id'        => $companyUuid,
            'opportunity_title' => 'New Platform Build',
            'value_minor_units' => 5000000,
            'probability'       => 75,
        ]);

        $this->assertDatabaseHas('opportunities', [
            'title'           => 'New Platform Build',
            'company_id'      => $companyUuid,
            'lead_id'         => $lead->getKey(),
            'value_minor_units' => 5000000,
        ]);
    }

    #[Test]
    public function it_returns_404_when_lead_does_not_exist_on_conversion(): void
    {
        $response = $this->postJson('/api/leads/00000000-0000-0000-0000-000000000000/convert', [
            'company_id'        => 'aaaaaaaa-0000-0000-0000-000000000001',
            'opportunity_title' => 'Ghost Project',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_409_when_lead_is_already_converted(): void
    {
        $lead = Lead::create([
            'name'   => 'Already Converted',
            'source' => LeadSource::Direct->value,
            'status' => LeadStatus::Converted->value,
            'email'  => 'done@example.sa',
        ]);

        $response = $this->postJson("/api/leads/{$lead->getKey()}/convert", [
            'company_id'        => 'aaaaaaaa-0000-0000-0000-000000000001',
            'opportunity_title' => 'Duplicate Conversion',
        ]);

        $response->assertStatus(409);
    }

    #[Test]
    public function it_returns_422_when_conversion_company_id_is_missing(): void
    {
        $lead = Lead::create([
            'name'   => 'Qualified Lead',
            'source' => LeadSource::Website->value,
            'status' => LeadStatus::Qualified->value,
            'email'  => 'q@example.sa',
        ]);

        $response = $this->postJson("/api/leads/{$lead->getKey()}/convert", [
            'opportunity_title' => 'Missing Company',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    #[Test]
    public function it_returns_422_when_opportunity_title_is_missing(): void
    {
        $lead = Lead::create([
            'name'   => 'Qualified Lead',
            'source' => LeadSource::Website->value,
            'status' => LeadStatus::Qualified->value,
            'email'  => 'q2@example.sa',
        ]);

        $response = $this->postJson("/api/leads/{$lead->getKey()}/convert", [
            'company_id' => 'aaaaaaaa-0000-0000-0000-000000000001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opportunity_title']);
    }
}
