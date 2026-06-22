<?php

use App\Modules\Inventory\StockMovement\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('stock-movements', [StockMovementController::class, 'index']);
Route::post('stock-movements', [StockMovementController::class, 'store']);
Route::get('stock-movements/{stockMovement}', [StockMovementController::class, 'show']);
Route::match(['put', 'patch'], 'stock-movements/{stockMovement}', [StockMovementController::class, 'update']);
Route::delete('stock-movements/{stockMovement}', [StockMovementController::class, 'destroy']);