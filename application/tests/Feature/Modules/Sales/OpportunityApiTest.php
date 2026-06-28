<?php

namespace Tests\Feature\Modules\Sales;

use App\Modules\Sales\Domain\Enums\OpportunityStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityApiTest extends TestCase
{
    use RefreshDatabase;

    private string $companyUuid = 'aaaaaaaa-0000-0000-0000-000000000001';

    // ── POST /api/opportunities ───────────────────────────────────────────────

    #[Test]
    public function it_creates_an_opportunity_and_returns_201(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'ERP Implementation',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'company_id', 'lead_id', 'contact_person_id',
                    'title', 'stage', 'value_minor_units', 'probability',
                    'expected_close_date', 'loss_reason', 'notes', 'created_at',
                ],
            ])
            ->assertJsonPath('data.title', 'ERP Implementation')
            ->assertJsonPath('data.company_id', $this->companyUuid)
            ->assertJsonPath('data.stage', 'qualification');
    }

    #[Test]
    public function it_persists_the_opportunity_to_the_database(): void
    {
        $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'Persisted Opportunity',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'title'      => 'Persisted Opportunity',
            'company_id' => $this->companyUuid,
            'stage'      => 'qualification',
        ]);
    }

    #[Test]
    public function it_returns_a_uuid_as_the_opportunity_id(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'UUID Opportunity',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_accepts_an_explicit_stage(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'Stage Test',
            'stage'      => 'proposal_sent',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.stage', 'proposal_sent');
    }

    #[Test]
    public function it_accepts_all_optional_opportunity_fields(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id'          => $this->companyUuid,
            'title'               => 'Full Opportunity',
            'stage'               => 'negotiation',
            'value_minor_units'   => 7500000,
            'probability'         => 80,
            'expected_close_date' => '2026-12-31',
            'notes'               => 'Strategic account.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.value_minor_units', 7500000)
            ->assertJsonPath('data.probability', 80)
            ->assertJsonPath('data.expected_close_date', '2026-12-31')
            ->assertJsonPath('data.notes', 'Strategic account.');
    }

    #[Test]
    public function it_returns_422_when_company_id_is_missing(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'title' => 'No Company',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    #[Test]
    public function it_returns_422_when_company_id_is_not_a_uuid(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => 'not-a-uuid',
            'title'      => 'Bad Company ID',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    #[Test]
    public function it_returns_422_when_title_is_missing(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function it_returns_422_when_stage_is_invalid(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'Bad Stage',
            'stage'      => 'moon_landing',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    #[Test]
    public function it_returns_422_when_probability_is_out_of_range(): void
    {
        $response = $this->postJson('/api/opportunities', [
            'company_id'  => $this->companyUuid,
            'title'       => 'Bad Probability',
            'probability' => 150,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['probability']);
    }

    #[Test]
    public function it_defaults_stage_to_qualification_when_not_provided(): void
    {
        $this->postJson('/api/opportunities', [
            'company_id' => $this->companyUuid,
            'title'      => 'Default Stage',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'title' => 'Default Stage',
            'stage' => 'qualification',
        ]);
    }
}
