<?php

use App\Modules\MasterData\DataProduk\Controllers\CategoriesController;
use App\Modules\MasterData\DataProduk\Controllers\ProductController;
use App\Modules\MasterData\DataProduk\Controllers\UnitsController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'index']);
Route::post('products', [ProductController::class, 'store']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update']);
Route::delete('products/{product}', [ProductController::class, 'destroy']);

Route::get('categories', [CategoriesController::class, 'index']);
Route::post('categories', [CategoriesController::class, 'store']);
Route::get('categories/{category}', [CategoriesController::class, 'show']);
Route::match(['put', 'patch'], 'categories/{category}', [CategoriesController::class, 'update']);
Route::delete('categories/{category}', [CategoriesController::class, 'destroy']);

Route::get('units', [UnitsController::class, 'index']);
Route::post('units', [UnitsController::class, 'store']);
Route::get('units/{unit}', [UnitsController::class, 'show']);
Route::match(['put', 'patch'], 'units/{unit}', [UnitsController::class, 'update']);
Route::delete('units/{unit}', [UnitsController::class, 'destroy']);
