<?php

use App\Modules\Rd\TechPack\Controllers\TechPackController;
use App\Modules\Rd\Sample\Controllers\SampleController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tech-packs', TechPackController::class);
Route::patch('tech-packs/{techPack}/status', [TechPackController::class, 'updateStatus']);

Route::apiResource('samples', SampleController::class);
Route::patch('samples/{sample}/status', [SampleController::class, 'updateStatus']);
