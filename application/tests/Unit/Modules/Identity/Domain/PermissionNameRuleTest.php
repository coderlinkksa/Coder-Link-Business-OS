<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Rules\PermissionNameRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionNameRuleTest extends TestCase
{
    #[Test]
    #[DataProvider('validPermissions')]
    public function it_accepts_valid_permission_names(string $permission): void
    {
        $this->assertTrue(PermissionNameRule::validate($permission));
    }

    public static function validPermissions(): array
    {
        return [
            ['crm.view'],
            ['crm.view-own'],
            ['crm.create'],
            ['crm.update-own'],
            ['billing.configure'],
            ['admin.export'],
            ['identity.configure'],
            ['ai.create'],
            ['notification.view'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPermissions')]
    public function it_rejects_invalid_permission_names(string $permission): void
    {
        $this->assertFalse(PermissionNameRule::validate($permission));
    }

    public static function invalidPermissions(): array
    {
        return [
            ['']                       ,   // empty
            ['crm']                    ,   // missing action
            ['.view']                  ,   // missing category
            ['crm.']                   ,   // missing action after dot
            ['CRM.view']               ,   // uppercase category
            ['crm.View']               ,   // uppercase action
            ['unknown.view']           ,   // invalid category
            ['crm.unknown']            ,   // invalid action
            ['crm.view.extra']         ,   // too many segments (action part has extra dot)
            ['crm view']               ,   // space instead of dot
        ];
    }
}
