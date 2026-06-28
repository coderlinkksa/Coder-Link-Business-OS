<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    // Module service providers
    App\Modules\Identity\IdentityServiceProvider::class,
    App\Modules\Company\CompanyServiceProvider::class,
    App\Modules\CRM\CRMServiceProvider::class,
    App\Modules\Sales\SalesServiceProvider::class,
    App\Modules\Proposal\ProposalServiceProvider::class,
    App\Modules\Contract\ContractServiceProvider::class,
    App\Modules\Project\ProjectServiceProvider::class,
    App\Modules\Billing\BillingServiceProvider::class,
    App\Modules\Support\SupportServiceProvider::class,
    App\Modules\Notification\NotificationServiceProvider::class,
    App\Modules\Integration\IntegrationServiceProvider::class,
    App\Modules\AI\AIServiceProvider::class,
    App\Modules\Admin\AdminServiceProvider::class,
];
