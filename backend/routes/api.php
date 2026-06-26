<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/filters', [DashboardController::class, 'filters']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::patch('/payments/bulk-act', [PaymentController::class, 'bulkUpdateAct']);
    Route::patch('/payments/{payment}/act', [PaymentController::class, 'updateAct']);
});
