<?php

use App\Modules\Company\API\Controllers\CompanyController;
use App\Modules\Company\API\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::post('companies', [CompanyController::class, 'store']);
Route::post('companies/{companyId}/contacts', [ContactController::class, 'store']);
