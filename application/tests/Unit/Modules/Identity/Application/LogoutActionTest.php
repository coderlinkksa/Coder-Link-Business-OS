<?php

namespace Tests\Unit\Modules\Identity\Application;

use App\Modules\Identity\Application\Actions\LogoutAction;
use App\Modules\Identity\Domain\Contracts\AuthenticationService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutActionTest extends TestCase
{
    #[Test]
    public function it_delegates_logout_to_the_auth_service(): void
    {
        $service = Mockery::mock(AuthenticationService::class);
        $service->shouldReceive('logout')->once();

        (new LogoutAction($service))->execute();
    }
}
