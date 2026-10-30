<?php

use App\Modules\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/kpi', [DashboardController::class, 'kpi']);
Route::get('/pipeline', [DashboardController::class, 'pipeline']);
