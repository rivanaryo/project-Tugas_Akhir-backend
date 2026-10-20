<?php

use App\Modules\Rd\Sample\Controllers\SampleController;
use App\Modules\Rd\SampleApproval\Controllers\SampleApprovalController;
use App\Modules\Rd\TechPack\Controllers\TechPackController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tech-packs', TechPackController::class);
Route::patch('tech-packs/{techPack}/status', [TechPackController::class, 'updateStatus']);

Route::apiResource('samples', SampleController::class);
Route::patch('samples/{sample}/status', [SampleController::class, 'updateStatus']);

Route::apiResource('sample-approvals', SampleApprovalController::class);
Route::patch('sample-approvals/{sampleApproval}/status', [SampleApprovalController::class, 'updateStatus']);
