<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\ActivityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityApiTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/activities ──────────────────────────────────────────────────

    #[Test]
    public function it_creates_an_activity_and_returns_201(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'    => 'call',
            'subject' => 'Introductory call with prospect',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'type', 'subject', 'body',
                    'occurred_at', 'lead_id', 'company_id',
                    'contact_person_id', 'opportunity_id', 'created_at',
                ],
            ])
            ->assertJsonPath('data.type', 'call')
            ->assertJsonPath('data.subject', 'Introductory call with prospect');
    }

    #[Test]
    public function it_persists_the_activity_to_the_database(): void
    {
        $this->postJson('/api/activities', [
            'type'    => 'email',
            'subject' => 'Follow-up email sent',
        ]);

        $this->assertDatabaseHas('activities', [
            'type'    => 'email',
            'subject' => 'Follow-up email sent',
        ]);
    }

    #[Test]
    public function it_returns_a_uuid_as_the_activity_id(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'    => 'note',
            'subject' => 'Internal note',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_accepts_all_optional_fields(): void
    {
        $leadUuid = 'aaaaaaaa-0000-0000-0000-000000000001';
        $oppUuid  = 'bbbbbbbb-0000-0000-0000-000000000001';

        $response = $this->postJson('/api/activities', [
            'type'           => 'meeting',
            'subject'        => 'Discovery meeting',
            'body'           => 'Discussed product requirements in detail.',
            'occurred_at'    => '2026-06-01 10:00:00',
            'lead_id'        => $leadUuid,
            'opportunity_id' => $oppUuid,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'Discussed product requirements in detail.')
            ->assertJsonPath('data.lead_id', $leadUuid)
            ->assertJsonPath('data.opportunity_id', $oppUuid);

        $this->assertNotNull($response->json('data.occurred_at'));
    }

    #[Test]
    public function it_sets_occurred_at_automatically_when_not_provided(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'    => 'note',
            'subject' => 'Auto timestamp note',
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.occurred_at'));
    }

    #[Test]
    public function it_can_link_to_a_company(): void
    {
        $companyUuid = 'cccccccc-0000-0000-0000-000000000001';

        $response = $this->postJson('/api/activities', [
            'type'       => 'call',
            'subject'    => 'Company call',
            'company_id' => $companyUuid,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.company_id', $companyUuid);
    }

    #[Test]
    public function it_can_link_to_a_contact_person(): void
    {
        $contactUuid = 'dddddddd-0000-0000-0000-000000000001';

        $response = $this->postJson('/api/activities', [
            'type'              => 'email',
            'subject'           => 'Email to contact',
            'contact_person_id' => $contactUuid,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.contact_person_id', $contactUuid);
    }

    #[Test]
    public function it_returns_422_when_type_is_missing(): void
    {
        $response = $this->postJson('/api/activities', [
            'subject' => 'No type activity',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function it_returns_422_when_type_is_invalid(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'    => 'telepathy',
            'subject' => 'Invalid type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function it_returns_422_when_subject_is_missing(): void
    {
        $response = $this->postJson('/api/activities', [
            'type' => 'call',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }

    #[Test]
    public function it_returns_422_when_lead_id_is_not_a_uuid(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'    => 'call',
            'subject' => 'Bad lead ID',
            'lead_id' => 'not-a-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lead_id']);
    }

    #[Test]
    public function it_returns_422_when_occurred_at_format_is_wrong(): void
    {
        $response = $this->postJson('/api/activities', [
            'type'        => 'call',
            'subject'     => 'Bad timestamp',
            'occurred_at' => '2026-06-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['occurred_at']);
    }
}
