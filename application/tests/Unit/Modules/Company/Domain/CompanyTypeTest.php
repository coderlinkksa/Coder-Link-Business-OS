<?php

namespace Tests\Unit\Modules\Company\Domain;

use App\Modules\Company\Domain\Enums\CompanyType;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyTypeTest extends TestCase
{
    #[Test]
    public function it_has_exactly_four_types(): void
    {
        $this->assertCount(4, CompanyType::cases());
    }

    #[Test]
    public function types_can_be_created_from_string_value(): void
    {
        $this->assertSame(CompanyType::Lead,     CompanyType::from('lead'));
        $this->assertSame(CompanyType::Customer, CompanyType::from('customer'));
        $this->assertSame(CompanyType::Partner,  CompanyType::from('partner'));
        $this->assertSame(CompanyType::Vendor,   CompanyType::from('vendor'));
    }

    #[Test]
    public function each_type_has_a_human_readable_label(): void
    {
        $this->assertSame('Lead',     CompanyType::Lead->label());
        $this->assertSame('Customer', CompanyType::Customer->label());
        $this->assertSame('Partner',  CompanyType::Partner->label());
        $this->assertSame('Vendor',   CompanyType::Vendor->label());
    }

    #[Test]
    public function customer_and_partner_are_revenue_bearing(): void
    {
        $this->assertTrue(CompanyType::Customer->isRevenueBearing());
        $this->assertTrue(CompanyType::Partner->isRevenueBearing());
    }

    #[Test]
    public function lead_and_vendor_are_not_revenue_bearing(): void
    {
        $this->assertFalse(CompanyType::Lead->isRevenueBearing());
        $this->assertFalse(CompanyType::Vendor->isRevenueBearing());
    }
}
