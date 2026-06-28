<?php

namespace Tests\Unit\Modules\Company\Application;

use App\Modules\Company\Application\Actions\CreateCompanyAction;
use App\Modules\Company\Application\DTOs\CreateCompanyData;
use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Modules\Company\Domain\Events\CompanyCreated;
use App\Modules\Company\Domain\Models\Company;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCompanyActionTest extends TestCase
{
    private CompanyRepository $repository;
    private CreateCompanyAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements CompanyRepository {
            public ?Company $saved = null;

            public function findById(int|string $id): ?Company { return null; }

            public function save(Company $company): void
            {
                $company->id  = 1;
                $this->saved  = $company;
            }
        };

        $this->action = new CreateCompanyAction($this->repository);
    }

    #[Test]
    public function it_creates_a_company_and_returns_it(): void
    {
        Event::fake();

        $data = new CreateCompanyData(
            name: 'Acme Corp',
            type: CompanyType::Customer,
        );

        $company = $this->action->execute($data);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertSame('Acme Corp', $company->name);
        $this->assertSame(CompanyType::Customer, $company->type);
        $this->assertSame(CompanyStatus::New, $company->status);
    }

    #[Test]
    public function it_persists_the_company_through_the_repository(): void
    {
        Event::fake();

        $data = new CreateCompanyData(
            name: 'Test Company',
            type: CompanyType::Lead,
        );

        $this->action->execute($data);

        $this->assertNotNull($this->repository->saved);
        $this->assertSame('Test Company', $this->repository->saved->name);
    }

    #[Test]
    public function it_fires_company_created_event(): void
    {
        Event::fake();

        $data = new CreateCompanyData(
            name: 'Event Corp',
            type: CompanyType::Customer,
        );

        $company = $this->action->execute($data);

        Event::assertDispatched(
            CompanyCreated::class,
            fn ($e) => $e->company === $company,
        );
    }

    #[Test]
    public function it_maps_all_optional_fields(): void
    {
        Event::fake();

        $data = new CreateCompanyData(
            name:       'Full Company',
            type:       CompanyType::Partner,
            status:     CompanyStatus::Active,
            industry:   'Technology',
            phone:      '+966501234567',
            email:      'info@full.sa',
            website:    'https://full.sa',
            address:    '123 King Fahd Road',
            city:       'Riyadh',
            country:    'Saudi Arabia',
            assignedTo: 7,
        );

        $company = $this->action->execute($data);

        $this->assertSame('Technology',        $company->industry);
        $this->assertSame('+966501234567',     $company->phone);
        $this->assertSame('info@full.sa',      $company->email);
        $this->assertSame('https://full.sa',   $company->website);
        $this->assertSame('123 King Fahd Road',$company->address);
        $this->assertSame('Riyadh',            $company->city);
        $this->assertSame('Saudi Arabia',      $company->country);
        $this->assertSame(7,                   $company->assigned_to);
    }
}
