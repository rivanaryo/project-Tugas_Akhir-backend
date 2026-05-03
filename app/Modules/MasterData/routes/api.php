<?php

use App\Modules\MasterData\DataProduk\Controllers\CategoriesController;
use App\Modules\MasterData\DataProduk\Controllers\ProductController;
use App\Modules\MasterData\DataProduk\Controllers\UnitsController;
use App\Modules\MasterData\ManagementVendor\Controllers\VendorController;
use App\Modules\MasterData\ManagementCustomers\Controllers\CustomerController;
use App\Modules\MasterData\ManagementAccount\Controllers\AccountController;
use App\Modules\MasterData\ManagementPricelist\Controllers\PricelistController;
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

Route::get('vendors', [VendorController::class, 'index']);
Route::post('vendors', [VendorController::class, 'store']);
Route::get('vendors/{vendor}', [VendorController::class, 'show']);
Route::match(['put', 'patch'], 'vendors/{vendor}', [VendorController::class, 'update']);
Route::delete('vendors/{vendor}', [VendorController::class, 'destroy']);

Route::get('customers', [CustomerController::class, 'index']);
Route::post('customers', [CustomerController::class, 'store']);
Route::get('customers/{customer}', [CustomerController::class, 'show']);
Route::match(['put', 'patch'], 'customers/{customer}', [CustomerController::class, 'update']);
Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

Route::get('accounts', [AccountController::class, 'index']);
Route::post('accounts', [AccountController::class, 'store']);
Route::get('accounts/{account}', [AccountController::class, 'show']);
Route::match(['put', 'patch'], 'accounts/{account}', [AccountController::class, 'update']);
Route::delete('accounts/{account}', [AccountController::class, 'destroy']);

Route::get('pricelists', [PricelistController::class, 'index']);
Route::post('pricelists', [PricelistController::class, 'store']);
Route::get('pricelists/{pricelist}', [PricelistController::class, 'show']);
Route::match(['put', 'patch'], 'pricelists/{pricelist}', [PricelistController::class, 'update']);
Route::delete('pricelists/{pricelist}', [PricelistController::class, 'destroy']);

