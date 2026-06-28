<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\ActivityType;
use App\Modules\CRM\Domain\Models\Activity;
use App\Modules\CRM\Infrastructure\Repositories\EloquentActivityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeActivity(array $attributes = []): Activity
    {
        return Activity::create(array_merge([
            'type'    => ActivityType::Call->value,
            'subject' => 'Test call',
        ], $attributes));
    }

    // ── UUID primary keys ─────────────────────────────────────────────────────

    #[Test]
    public function activity_primary_key_is_a_uuid(): void
    {
        $activity = $this->makeActivity();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $activity->getKey(),
        );
    }

    #[Test]
    public function each_activity_receives_a_unique_uuid(): void
    {
        $a = $this->makeActivity(['subject' => 'Call A']);
        $b = $this->makeActivity(['subject' => 'Call B']);

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    // ── Column persistence ────────────────────────────────────────────────────

    #[Test]
    public function activity_can_be_retrieved_by_uuid(): void
    {
        $saved = $this->makeActivity(['subject' => 'Lookup call']);
        $found = Activity::find($saved->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Lookup call', $found->subject);
    }

    #[Test]
    public function activity_type_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeActivity(['type' => ActivityType::Meeting->value]);

        $this->assertSame(ActivityType::Meeting, Activity::first()->type);
    }

    #[Test]
    public function activity_body_persists_correctly(): void
    {
        $this->makeActivity(['body' => 'Discussed Q3 renewal terms.']);

        $this->assertSame('Discussed Q3 renewal terms.', Activity::first()->body);
    }

    #[Test]
    public function occurred_at_persists_and_is_cast_to_datetime(): void
    {
        $this->makeActivity(['occurred_at' => '2026-06-01 10:00:00']);

        $found = Activity::first();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $found->occurred_at);
        $this->assertSame('2026-06-01 10:00:00', $found->occurred_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function occurred_at_is_null_when_not_provided(): void
    {
        $this->makeActivity();

        $this->assertNull(Activity::first()->occurred_at);
    }

    // ── Related-entity linkage ────────────────────────────────────────────────

    #[Test]
    public function activity_can_be_linked_to_a_lead(): void
    {
        $leadUuid = 'aaaaaaaa-0000-0000-0000-000000000001';
        $this->makeActivity(['lead_id' => $leadUuid]);

        $this->assertSame($leadUuid, Activity::first()->lead_id);
    }

    #[Test]
    public function activity_can_be_linked_to_a_company(): void
    {
        $companyUuid = 'bbbbbbbb-0000-0000-0000-000000000001';
        $this->makeActivity(['company_id' => $companyUuid]);

        $this->assertSame($companyUuid, Activity::first()->company_id);
    }

    #[Test]
    public function activity_can_be_linked_to_a_contact_person(): void
    {
        $contactUuid = 'cccccccc-0000-0000-0000-000000000001';
        $this->makeActivity(['contact_person_id' => $contactUuid]);

        $this->assertSame($contactUuid, Activity::first()->contact_person_id);
    }

    #[Test]
    public function activity_can_be_linked_to_an_opportunity(): void
    {
        $oppUuid = 'dddddddd-0000-0000-0000-000000000001';
        $this->makeActivity(['opportunity_id' => $oppUuid]);

        $this->assertSame($oppUuid, Activity::first()->opportunity_id);
    }

    #[Test]
    public function has_linked_record_returns_true_when_lead_is_set(): void
    {
        $this->makeActivity(['lead_id' => 'aaaaaaaa-0000-0000-0000-000000000001']);

        $this->assertTrue(Activity::first()->hasLinkedRecord());
    }

    #[Test]
    public function has_linked_record_returns_false_when_no_entity_is_linked(): void
    {
        $this->makeActivity();

        $this->assertFalse(Activity::first()->hasLinkedRecord());
    }

    // ── Append-only: soft-delete is not used for activities ───────────────────

    #[Test]
    public function multiple_activities_can_be_stored_independently(): void
    {
        $this->makeActivity(['type' => ActivityType::Call->value,    'subject' => 'First call']);
        $this->makeActivity(['type' => ActivityType::Email->value,   'subject' => 'Follow-up email']);
        $this->makeActivity(['type' => ActivityType::Meeting->value, 'subject' => 'Discovery meeting']);

        $this->assertCount(3, Activity::all());
    }

    // ── Index-backed queries ──────────────────────────────────────────────────

    #[Test]
    public function activities_can_be_filtered_by_type(): void
    {
        $this->makeActivity(['type' => ActivityType::Call->value,  'subject' => 'Call 1']);
        $this->makeActivity(['type' => ActivityType::Email->value, 'subject' => 'Email 1']);
        $this->makeActivity(['type' => ActivityType::Call->value,  'subject' => 'Call 2']);

        $calls = Activity::where('type', ActivityType::Call->value)->get();
        $this->assertCount(2, $calls);
    }

    #[Test]
    public function activities_can_be_filtered_by_lead(): void
    {
        $lead = 'aaaaaaaa-0000-0000-0000-000000000001';

        $this->makeActivity(['lead_id' => $lead, 'subject' => 'For lead']);
        $this->makeActivity(['subject' => 'No lead']);

        $this->assertCount(1, Activity::where('lead_id', $lead)->get());
    }

    #[Test]
    public function activities_can_be_filtered_by_opportunity(): void
    {
        $opp = 'eeeeeeee-0000-0000-0000-000000000001';

        $this->makeActivity(['opportunity_id' => $opp, 'subject' => 'Opp activity 1']);
        $this->makeActivity(['opportunity_id' => $opp, 'subject' => 'Opp activity 2']);
        $this->makeActivity(['subject' => 'No opp']);

        $this->assertCount(2, Activity::where('opportunity_id', $opp)->get());
    }

    // ── Eloquent repository round-trips ───────────────────────────────────────

    #[Test]
    public function eloquent_activity_repository_saves_and_finds_by_uuid(): void
    {
        $repo     = new EloquentActivityRepository();
        $activity = new Activity();
        $activity->forceFill([
            'type'    => ActivityType::Note->value,
            'subject' => 'Repo note',
        ]);
        $repo->save($activity);

        $found = $repo->findById($activity->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Repo note', $found->subject);
        $this->assertSame(ActivityType::Note, $found->type);
    }

    #[Test]
    public function repository_returns_null_for_nonexistent_uuid(): void
    {
        $repo = new EloquentActivityRepository();

        $this->assertNull($repo->findById('00000000-0000-0000-0000-000000000000'));
    }
}
