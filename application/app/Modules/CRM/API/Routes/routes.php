<?php

use App\Modules\CRM\API\Controllers\ActivityController;
use App\Modules\CRM\API\Controllers\LeadController;
use App\Modules\CRM\API\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('leads', [LeadController::class, 'store']);
Route::post('leads/{leadId}/convert', [LeadController::class, 'convert']);

Route::post('activities', [ActivityController::class, 'store']);

Route::post('tasks', [TaskController::class, 'store']);
Route::post('tasks/{taskId}/complete', [TaskController::class, 'complete']);
