<?php

use App\Modules\Inventory\StockMovement\Controllers\StockMovementController;
use App\Modules\Inventory\StockOpname\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;

Route::get('stock-movements', [StockMovementController::class, 'index']);
Route::post('stock-movements', [StockMovementController::class, 'store']);
Route::get('stock-movements/{stockMovement}', [StockMovementController::class, 'show']);
Route::match(['put', 'patch'], 'stock-movements/{stockMovement}', [StockMovementController::class, 'update']);
Route::delete('stock-movements/{stockMovement}', [StockMovementController::class, 'destroy']);

Route::get('stock-opnames', [StockOpnameController::class, 'index']);
Route::post('stock-opnames', [StockOpnameController::class, 'store']);
Route::get('stock-opnames/{stockOpname}', [StockOpnameController::class, 'show']);
Route::match(['put', 'patch'], 'stock-opnames/{stockOpname}', [StockOpnameController::class, 'update']);
Route::delete('stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy']);

