<?php

use App\Modules\Inventory\Prediction\Controllers\RopController;
use Illuminate\Support\Facades\Route;

Route::post('/predict', [RopController::class, 'predict']);
Route::get('/model-metrics', [RopController::class, 'modelMetrics']);
