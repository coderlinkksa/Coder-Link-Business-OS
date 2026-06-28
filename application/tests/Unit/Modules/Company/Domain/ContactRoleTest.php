<?php

namespace Tests\Unit\Modules\Company\Domain;

use App\Modules\Company\Domain\Enums\ContactRole;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactRoleTest extends TestCase
{
    #[Test]
    public function it_has_exactly_five_roles(): void
    {
        $this->assertCount(5, ContactRole::cases());
    }

    #[Test]
    public function roles_can_be_created_from_string_value(): void
    {
        $this->assertSame(ContactRole::Primary,       ContactRole::from('primary'));
        $this->assertSame(ContactRole::DecisionMaker, ContactRole::from('decision_maker'));
        $this->assertSame(ContactRole::Technical,     ContactRole::from('technical'));
        $this->assertSame(ContactRole::Billing,       ContactRole::from('billing'));
        $this->assertSame(ContactRole::Other,         ContactRole::from('other'));
    }

    #[Test]
    public function each_role_has_a_human_readable_label(): void
    {
        $this->assertSame('Primary Contact',  ContactRole::Primary->label());
        $this->assertSame('Decision Maker',   ContactRole::DecisionMaker->label());
        $this->assertSame('Technical Contact', ContactRole::Technical->label());
        $this->assertSame('Billing Contact',  ContactRole::Billing->label());
        $this->assertSame('Other',            ContactRole::Other->label());
    }
}
