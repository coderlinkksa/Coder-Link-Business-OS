<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Enums\Permission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    #[Test]
    public function each_permission_value_follows_the_naming_convention(): void
    {
        foreach (Permission::cases() as $permission) {
            $this->assertMatchesRegularExpression(
                '/^[a-z]+\.[a-z]+(-[a-z]+)*$/',
                $permission->value,
                "Permission {$permission->value} does not follow {category}.{action} convention",
            );
        }
    }

    #[Test]
    public function category_and_action_helpers_return_correct_parts(): void
    {
        $this->assertSame('crm', Permission::CrmViewOwn->category());
        $this->assertSame('view-own', Permission::CrmViewOwn->action());

        $this->assertSame('billing', Permission::BillingConfigure->category());
        $this->assertSame('configure', Permission::BillingConfigure->action());

        $this->assertSame('admin', Permission::AdminExport->category());
        $this->assertSame('export', Permission::AdminExport->action());
    }

    #[Test]
    public function permissions_can_be_created_from_string_value(): void
    {
        $this->assertSame(Permission::CrmCreate, Permission::from('crm.create'));
        $this->assertSame(Permission::BillingView, Permission::from('billing.view'));
        $this->assertSame(Permission::AdminConfigure, Permission::from('admin.configure'));
    }
}
