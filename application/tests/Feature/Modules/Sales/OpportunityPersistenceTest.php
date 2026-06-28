<?php

namespace Tests\Feature\Modules\Sales;

use App\Modules\Sales\Domain\Enums\OpportunityStage;
use App\Modules\Sales\Domain\Models\Opportunity;
use App\Modules\Sales\Infrastructure\Repositories\EloquentOpportunityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeOpportunity(array $attributes = []): Opportunity
    {
        return Opportunity::create(array_merge([
            'company_id' => 'aaaaaaaa-0000-0000-0000-000000000001',
            'title'      => 'Test Opportunity',
            'stage'      => OpportunityStage::Qualification->value,
        ], $attributes));
    }

    // ── UUID primary keys ─────────────────────────────────────────────────────

    #[Test]
    public function opportunity_primary_key_is_a_uuid(): void
    {
        $opp = $this->makeOpportunity();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $opp->getKey(),
        );
    }

    #[Test]
    public function each_opportunity_receives_a_unique_uuid(): void
    {
        $a = $this->makeOpportunity(['title' => 'Opp A']);
        $b = $this->makeOpportunity(['title' => 'Opp B']);

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    // ── Column persistence ────────────────────────────────────────────────────

    #[Test]
    public function opportunity_can_be_retrieved_by_uuid(): void
    {
        $saved = $this->makeOpportunity(['title' => 'Lookup Opp']);
        $found = Opportunity::find($saved->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Lookup Opp', $found->title);
    }

    #[Test]
    public function opportunity_stage_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeOpportunity(['stage' => OpportunityStage::ProposalSent->value]);

        $this->assertSame(OpportunityStage::ProposalSent, Opportunity::first()->stage);
    }

    #[Test]
    public function opportunity_stage_defaults_to_qualification(): void
    {
        Opportunity::create([
            'company_id' => 'aaaaaaaa-0000-0000-0000-000000000001',
            'title'      => 'Default Stage Opp',
        ]);

        $this->assertSame(OpportunityStage::Qualification, Opportunity::first()->stage);
    }

    #[Test]
    public function expected_close_date_is_cast_to_date_after_retrieval(): void
    {
        $this->makeOpportunity(['expected_close_date' => '2026-12-31']);

        $found = Opportunity::first();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $found->expected_close_date);
        $this->assertSame('2026-12-31', $found->expected_close_date->format('Y-m-d'));
    }

    #[Test]
    public function expected_close_date_is_null_when_not_provided(): void
    {
        $this->makeOpportunity();

        $this->assertNull(Opportunity::first()->expected_close_date);
    }

    #[Test]
    public function value_minor_units_persists_correctly(): void
    {
        $this->makeOpportunity(['value_minor_units' => 7500000]);

        $this->assertSame(7500000, Opportunity::first()->value_minor_units);
    }

    #[Test]
    public function probability_persists_correctly(): void
    {
        $this->makeOpportunity(['probability' => 75]);

        $this->assertSame(75, Opportunity::first()->probability);
    }

    #[Test]
    public function all_nullable_fields_persist_correctly(): void
    {
        $leadUuid    = 'bbbbbbbb-0000-0000-0000-000000000001';
        $contactUuid = 'cccccccc-0000-0000-0000-000000000001';
        $assignedUuid = 'dddddddd-0000-0000-0000-000000000001';

        $this->makeOpportunity([
            'lead_id'            => $leadUuid,
            'contact_person_id'  => $contactUuid,
            'value_minor_units'  => 5000000,
            'probability'        => 60,
            'expected_close_date' => '2026-09-30',
            'assigned_to'        => $assignedUuid,
            'notes'              => 'High-priority deal.',
        ]);

        $found = Opportunity::first();
        $this->assertSame($leadUuid,            $found->lead_id);
        $this->assertSame($contactUuid,         $found->contact_person_id);
        $this->assertSame(5000000,              $found->value_minor_units);
        $this->assertSame(60,                   $found->probability);
        $this->assertSame('High-priority deal.', $found->notes);
    }

    // ── is_open helper ────────────────────────────────────────────────────────

    #[Test]
    public function is_open_returns_true_for_non_terminal_stages(): void
    {
        foreach ([
            OpportunityStage::Qualification,
            OpportunityStage::DiscoveryMeeting,
            OpportunityStage::RequirementsGathering,
            OpportunityStage::SolutionDesign,
            OpportunityStage::ProposalSent,
            OpportunityStage::Negotiation,
        ] as $i => $stage) {
            $opp = $this->makeOpportunity([
                'title' => "Open $i",
                'stage' => $stage->value,
            ]);
            $this->assertTrue(Opportunity::find($opp->getKey())->isOpen(), "Expected isOpen() for {$stage->value}");
        }
    }

    #[Test]
    public function is_open_returns_false_for_won_stage(): void
    {
        $opp = $this->makeOpportunity(['stage' => OpportunityStage::Won->value]);

        $this->assertFalse(Opportunity::find($opp->getKey())->isOpen());
    }

    #[Test]
    public function is_open_returns_false_for_lost_stage(): void
    {
        $opp = $this->makeOpportunity(['stage' => OpportunityStage::Lost->value]);

        $this->assertFalse(Opportunity::find($opp->getKey())->isOpen());
    }

    // ── Soft deletes ──────────────────────────────────────────────────────────

    #[Test]
    public function soft_deleting_an_opportunity_sets_deleted_at(): void
    {
        $opp = $this->makeOpportunity();
        $opp->delete();

        $this->assertNotNull(Opportunity::withTrashed()->find($opp->getKey())->deleted_at);
    }

    #[Test]
    public function soft_deleted_opportunity_is_excluded_from_default_queries(): void
    {
        $opp = $this->makeOpportunity();
        $opp->delete();

        $this->assertNull(Opportunity::find($opp->getKey()));
        $this->assertNotNull(Opportunity::withTrashed()->find($opp->getKey()));
    }

    // ── Index-backed queries ──────────────────────────────────────────────────

    #[Test]
    public function opportunities_can_be_filtered_by_stage(): void
    {
        $this->makeOpportunity(['title' => 'Qual 1',     'stage' => OpportunityStage::Qualification->value]);
        $this->makeOpportunity(['title' => 'Proposal 1', 'stage' => OpportunityStage::ProposalSent->value]);
        $this->makeOpportunity(['title' => 'Qual 2',     'stage' => OpportunityStage::Qualification->value]);

        $inQual = Opportunity::where('stage', OpportunityStage::Qualification->value)->get();
        $this->assertCount(2, $inQual);
    }

    #[Test]
    public function opportunities_can_be_filtered_by_company(): void
    {
        $companyA = 'aaaaaaaa-0000-0000-0000-000000000001';
        $companyB = 'aaaaaaaa-0000-0000-0000-000000000002';

        $this->makeOpportunity(['title' => 'A1', 'company_id' => $companyA]);
        $this->makeOpportunity(['title' => 'A2', 'company_id' => $companyA]);
        $this->makeOpportunity(['title' => 'B1', 'company_id' => $companyB]);

        $this->assertCount(2, Opportunity::where('company_id', $companyA)->get());
        $this->assertCount(1, Opportunity::where('company_id', $companyB)->get());
    }

    #[Test]
    public function opportunities_can_be_filtered_by_source_lead(): void
    {
        $leadUuid = 'bbbbbbbb-0000-0000-0000-000000000001';

        $this->makeOpportunity(['title' => 'From Lead',    'lead_id' => $leadUuid]);
        $this->makeOpportunity(['title' => 'No Lead']);

        $fromLead = Opportunity::where('lead_id', $leadUuid)->get();
        $this->assertCount(1, $fromLead);
        $this->assertSame('From Lead', $fromLead->first()->title);
    }

    // ── Eloquent repository round-trips ───────────────────────────────────────

    #[Test]
    public function eloquent_opportunity_repository_saves_and_finds_by_uuid(): void
    {
        $repo = new EloquentOpportunityRepository();
        $opp  = new Opportunity();
        $opp->forceFill([
            'company_id' => 'aaaaaaaa-0000-0000-0000-000000000001',
            'title'      => 'Repo Opportunity',
            'stage'      => OpportunityStage::Negotiation->value,
        ]);
        $repo->save($opp);

        $found = $repo->findById($opp->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Repo Opportunity', $found->title);
        $this->assertSame(OpportunityStage::Negotiation, $found->stage);
    }

    #[Test]
    public function repository_returns_null_for_nonexistent_uuid(): void
    {
        $repo = new EloquentOpportunityRepository();

        $this->assertNull($repo->findById('00000000-0000-0000-0000-000000000000'));
    }
}
