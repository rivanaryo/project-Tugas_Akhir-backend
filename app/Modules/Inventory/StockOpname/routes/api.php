<?php

use App\Modules\Inventory\StockOpname\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;

Route::get('stock-opnames', [StockOpnameController::class, 'index']);
Route::post('stock-opnames', [StockOpnameController::class, 'store']);
Route::get('stock-opnames/{stockOpname}', [StockOpnameController::class, 'show']);
Route::match(['put', 'patch'], 'stock-opnames/{stockOpname}', [StockOpnameController::class, 'update']);
Route::delete('stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy']);
