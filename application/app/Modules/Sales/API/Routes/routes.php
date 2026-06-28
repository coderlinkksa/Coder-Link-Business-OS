<?php

use App\Modules\Sales\API\Controllers\OpportunityController;
use Illuminate\Support\Facades\Route;

Route::post('opportunities', [OpportunityController::class, 'store']);
