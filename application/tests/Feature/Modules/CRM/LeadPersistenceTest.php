<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Models\Lead;
use App\Modules\CRM\Infrastructure\Repositories\EloquentLeadRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeLead(array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'name'   => 'Test Lead',
            'source' => LeadSource::Website->value,
            'email'  => 'test@example.sa',
        ], $attributes));
    }

    // ── UUID primary keys ─────────────────────────────────────────────────────

    #[Test]
    public function lead_primary_key_is_a_uuid(): void
    {
        $lead = $this->makeLead();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $lead->getKey(),
        );
    }

    #[Test]
    public function each_lead_receives_a_unique_uuid(): void
    {
        $a = $this->makeLead(['name' => 'Lead A', 'email' => 'a@example.sa']);
        $b = $this->makeLead(['name' => 'Lead B', 'email' => 'b@example.sa']);

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    // ── Column persistence ────────────────────────────────────────────────────

    #[Test]
    public function lead_can_be_retrieved_by_uuid(): void
    {
        $saved = $this->makeLead(['name' => 'Lookup Lead']);
        $found = Lead::find($saved->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Lookup Lead', $found->name);
    }

    #[Test]
    public function lead_source_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeLead(['source' => LeadSource::Referral->value]);

        $this->assertSame(LeadSource::Referral, Lead::first()->source);
    }

    #[Test]
    public function lead_status_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeLead(['status' => LeadStatus::Contacted->value]);

        $this->assertSame(LeadStatus::Contacted, Lead::first()->status);
    }

    #[Test]
    public function lead_status_defaults_to_new_when_not_provided(): void
    {
        Lead::create([
            'name'   => 'Default Status Lead',
            'source' => LeadSource::Direct->value,
            'email'  => 'default@example.sa',
        ]);

        $this->assertSame(LeadStatus::New, Lead::first()->status);
    }

    #[Test]
    public function all_nullable_lead_fields_persist_correctly(): void
    {
        $this->makeLead([
            'phone'             => '+966501234567',
            'service_requested' => 'Website Development',
            'notes'             => 'Partner referral.',
            'lost_reason'       => null,
        ]);

        $found = Lead::first();
        $this->assertSame('+966501234567',       $found->phone);
        $this->assertSame('Website Development', $found->service_requested);
        $this->assertSame('Partner referral.',   $found->notes);
        $this->assertNull($found->lost_reason);
    }

    // ── Conversion timestamp ──────────────────────────────────────────────────

    #[Test]
    public function converted_at_is_null_by_default(): void
    {
        $lead = $this->makeLead();

        $this->assertNull($lead->converted_at);
    }

    #[Test]
    public function mark_converted_sets_converted_at_timestamp(): void
    {
        $lead = $this->makeLead();
        $lead->markConverted();
        $lead->save();

        $found = Lead::find($lead->getKey());
        $this->assertNotNull($found->converted_at);
        $this->assertSame(LeadStatus::Converted, $found->status);
    }

    #[Test]
    public function converted_at_is_cast_to_datetime_after_retrieval(): void
    {
        $lead = $this->makeLead();
        $lead->markConverted();
        $lead->save();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, Lead::first()->converted_at);
    }

    // ── Soft deletes ──────────────────────────────────────────────────────────

    #[Test]
    public function soft_deleting_a_lead_sets_deleted_at(): void
    {
        $lead = $this->makeLead();
        $lead->delete();

        $this->assertNotNull(Lead::withTrashed()->find($lead->getKey())->deleted_at);
    }

    #[Test]
    public function soft_deleted_lead_is_excluded_from_default_queries(): void
    {
        $lead = $this->makeLead();
        $lead->delete();

        $this->assertNull(Lead::find($lead->getKey()));
        $this->assertNotNull(Lead::withTrashed()->find($lead->getKey()));
    }

    // ── Index-backed queries ──────────────────────────────────────────────────

    #[Test]
    public function leads_can_be_filtered_by_status(): void
    {
        $this->makeLead(['name' => 'New 1',       'email' => 'n1@x.sa', 'status' => LeadStatus::New->value]);
        $this->makeLead(['name' => 'Contacted 1', 'email' => 'c1@x.sa', 'status' => LeadStatus::Contacted->value]);
        $this->makeLead(['name' => 'New 2',       'email' => 'n2@x.sa', 'status' => LeadStatus::New->value]);

        $newLeads = Lead::where('status', LeadStatus::New->value)->get();
        $this->assertCount(2, $newLeads);
    }

    #[Test]
    public function leads_can_be_filtered_by_source(): void
    {
        $this->makeLead(['name' => 'Web 1', 'email' => 'w1@x.sa', 'source' => LeadSource::Website->value]);
        $this->makeLead(['name' => 'Ref 1', 'email' => 'r1@x.sa', 'source' => LeadSource::Referral->value]);
        $this->makeLead(['name' => 'Web 2', 'email' => 'w2@x.sa', 'source' => LeadSource::Website->value]);

        $webLeads = Lead::where('source', LeadSource::Website->value)->get();
        $this->assertCount(2, $webLeads);
    }

    #[Test]
    public function leads_can_be_filtered_by_email(): void
    {
        $this->makeLead(['name' => 'A', 'email' => 'ali@example.sa']);
        $this->makeLead(['name' => 'B', 'email' => 'sara@example.sa']);

        $this->assertNotNull(Lead::where('email', 'ali@example.sa')->first());
    }

    // ── Eloquent repository round-trips ───────────────────────────────────────

    #[Test]
    public function eloquent_lead_repository_saves_and_finds_by_uuid(): void
    {
        $repo = new EloquentLeadRepository();
        $lead = new Lead();
        $lead->forceFill([
            'name'   => 'Repo Lead',
            'source' => LeadSource::Direct->value,
            'email'  => 'repo@example.sa',
        ]);
        $repo->save($lead);

        $found = $repo->findById($lead->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Repo Lead', $found->name);
        $this->assertSame(LeadSource::Direct, $found->source);
    }

    #[Test]
    public function repository_returns_null_for_nonexistent_uuid(): void
    {
        $repo = new EloquentLeadRepository();

        $this->assertNull($repo->findById('00000000-0000-0000-0000-000000000000'));
    }

    // ── has_contact_info helper ───────────────────────────────────────────────

    #[Test]
    public function has_contact_info_returns_true_when_email_is_set(): void
    {
        $lead = $this->makeLead(['email' => 'info@example.sa', 'phone' => null]);

        $this->assertTrue(Lead::find($lead->getKey())->hasContactInfo());
    }

    #[Test]
    public function has_contact_info_returns_true_when_phone_is_set(): void
    {
        Lead::create([
            'name'   => 'Phone Lead',
            'source' => LeadSource::Direct->value,
            'phone'  => '+966501111111',
        ]);

        $this->assertTrue(Lead::first()->hasContactInfo());
    }

    #[Test]
    public function has_contact_info_returns_false_when_neither_is_set(): void
    {
        Lead::create([
            'name'   => 'No Contact',
            'source' => LeadSource::Other->value,
        ]);

        $this->assertFalse(Lead::first()->hasContactInfo());
    }
}
