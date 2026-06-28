<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Enums\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    #[Test]
    public function it_has_exactly_six_roles(): void
    {
        $this->assertCount(6, Role::cases());
    }

    #[Test]
    public function owner_and_admin_bypass_ownership(): void
    {
        $this->assertTrue(Role::Owner->bypassesOwnership());
        $this->assertTrue(Role::Admin->bypassesOwnership());
    }

    #[Test]
    public function other_roles_do_not_bypass_ownership(): void
    {
        $this->assertFalse(Role::SalesRepresentative->bypassesOwnership());
        $this->assertFalse(Role::AccountManager->bypassesOwnership());
        $this->assertFalse(Role::TechnicalSupport->bypassesOwnership());
        $this->assertFalse(Role::Viewer->bypassesOwnership());
    }

    #[Test]
    public function only_owner_is_super_admin(): void
    {
        $this->assertTrue(Role::Owner->isSuperAdmin());

        foreach ([Role::Admin, Role::SalesRepresentative, Role::AccountManager, Role::TechnicalSupport, Role::Viewer] as $role) {
            $this->assertFalse($role->isSuperAdmin(), "{$role->value} should not be super admin");
        }
    }

    #[Test]
    public function roles_can_be_created_from_string_value(): void
    {
        $this->assertSame(Role::Owner, Role::from('owner'));
        $this->assertSame(Role::Admin, Role::from('admin'));
        $this->assertSame(Role::SalesRepresentative, Role::from('sales_representative'));
        $this->assertSame(Role::AccountManager, Role::from('account_manager'));
        $this->assertSame(Role::TechnicalSupport, Role::from('technical_support'));
        $this->assertSame(Role::Viewer, Role::from('viewer'));
    }

    #[Test]
    public function each_role_has_a_human_readable_label(): void
    {
        $this->assertSame('Owner', Role::Owner->label());
        $this->assertSame('Admin', Role::Admin->label());
        $this->assertSame('Sales Representative', Role::SalesRepresentative->label());
        $this->assertSame('Account Manager', Role::AccountManager->label());
        $this->assertSame('Technical Support', Role::TechnicalSupport->label());
        $this->assertSame('Viewer', Role::Viewer->label());
    }
}
