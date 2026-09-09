<?php

use App\Http\Controllers\API\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->controller(DashboardController::class)->group(function() {
    Route::get('machine/{machineCode}', 'machinePerformance');
});

Route::controller(DashboardController::class)->group(function() {
    Route::get('production-orders', 'productionOrders');
    Route::get('production-results', 'productionResults');
});
