<?php

namespace Tests\Unit\Modules\Company\Domain;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyStatusTest extends TestCase
{
    #[Test]
    public function it_has_exactly_four_statuses(): void
    {
        $this->assertCount(4, CompanyStatus::cases());
    }

    #[Test]
    public function statuses_can_be_created_from_string_value(): void
    {
        $this->assertSame(CompanyStatus::New,      CompanyStatus::from('new'));
        $this->assertSame(CompanyStatus::Active,   CompanyStatus::from('active'));
        $this->assertSame(CompanyStatus::Inactive, CompanyStatus::from('inactive'));
        $this->assertSame(CompanyStatus::Archived, CompanyStatus::from('archived'));
    }

    #[Test]
    public function each_status_has_a_human_readable_label(): void
    {
        $this->assertSame('New',      CompanyStatus::New->label());
        $this->assertSame('Active',   CompanyStatus::Active->label());
        $this->assertSame('Inactive', CompanyStatus::Inactive->label());
        $this->assertSame('Archived', CompanyStatus::Archived->label());
    }

    #[Test]
    public function only_active_status_reports_as_active(): void
    {
        $this->assertTrue(CompanyStatus::Active->isActive());
        $this->assertFalse(CompanyStatus::New->isActive());
        $this->assertFalse(CompanyStatus::Inactive->isActive());
        $this->assertFalse(CompanyStatus::Archived->isActive());
    }

    #[Test]
    public function new_company_can_transition_to_active_or_archived(): void
    {
        $this->assertTrue(CompanyStatus::New->canTransitionTo(CompanyStatus::Active));
        $this->assertTrue(CompanyStatus::New->canTransitionTo(CompanyStatus::Archived));
        $this->assertFalse(CompanyStatus::New->canTransitionTo(CompanyStatus::Inactive));
        $this->assertFalse(CompanyStatus::New->canTransitionTo(CompanyStatus::New));
    }

    #[Test]
    public function active_company_can_become_inactive_or_archived(): void
    {
        $this->assertTrue(CompanyStatus::Active->canTransitionTo(CompanyStatus::Inactive));
        $this->assertTrue(CompanyStatus::Active->canTransitionTo(CompanyStatus::Archived));
        $this->assertFalse(CompanyStatus::Active->canTransitionTo(CompanyStatus::New));
    }

    #[Test]
    public function archived_company_cannot_transition_to_any_status(): void
    {
        foreach (CompanyStatus::cases() as $status) {
            $this->assertFalse(
                CompanyStatus::Archived->canTransitionTo($status),
                "Archived should not transition to {$status->value}",
            );
        }
    }
}
