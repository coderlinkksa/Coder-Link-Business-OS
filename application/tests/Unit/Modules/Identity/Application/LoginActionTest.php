<?php

namespace Tests\Unit\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Identity\Application\Actions\LoginAction;
use App\Modules\Identity\Application\DTOs\LoginData;
use App\Modules\Identity\Domain\Contracts\AuthenticationService;
use App\Modules\Identity\Domain\Exceptions\AuthenticationFailedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    #[Test]
    public function it_returns_user_on_successful_login(): void
    {
        $user = new User(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com']);
        $data = new LoginData('test@example.com', 'password');

        $service = Mockery::mock(AuthenticationService::class);
        $service->shouldReceive('login')->once()->with($data)->andReturn($user);

        $action = new LoginAction($service);
        $result = $action->execute($data);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function it_propagates_authentication_failed_exception(): void
    {
        $data = new LoginData('wrong@example.com', 'badpassword');

        $service = Mockery::mock(AuthenticationService::class);
        $service->shouldReceive('login')->once()->andThrow(new AuthenticationFailedException());

        $this->expectException(AuthenticationFailedException::class);

        (new LoginAction($service))->execute($data);
    }
}
