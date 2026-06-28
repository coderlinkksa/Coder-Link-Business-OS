<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Application\DTOs\LoginData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginDataTest extends TestCase
{
    #[Test]
    public function it_stores_credentials(): void
    {
        $dto = new LoginData('admin@example.com', 'secret', remember: true);

        $this->assertSame('admin@example.com', $dto->email);
        $this->assertSame('secret', $dto->password);
        $this->assertTrue($dto->remember);
    }

    #[Test]
    public function remember_defaults_to_false(): void
    {
        $dto = new LoginData('admin@example.com', 'secret');

        $this->assertFalse($dto->remember);
    }
}
