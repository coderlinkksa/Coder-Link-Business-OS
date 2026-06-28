<?php

use App\Modules\CRM\API\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::post('leads', [LeadController::class, 'store']);
Route::post('leads/{leadId}/convert', [LeadController::class, 'convert']);
