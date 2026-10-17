<?php

use App\Modules\Finance\VendorPayment\Controllers\VendorPaymentController;
use App\Modules\Finance\CustomerPayment\Controllers\CustomerPaymentController;
use App\Modules\Finance\Account\Controllers\FinanceAccountController;
use Illuminate\Support\Facades\Route;

Route::get('vendor-payments', [VendorPaymentController::class, 'index']);
Route::post('vendor-payments', [VendorPaymentController::class, 'store']);
Route::get('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'show']);
Route::match(['put', 'patch'], 'vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'update']);
Route::delete('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'destroy']);

Route::get('customer-payments', [CustomerPaymentController::class, 'index']);
Route::post('customer-payments', [CustomerPaymentController::class, 'store']);
Route::get('customer-payments/{customerPayment}', [CustomerPaymentController::class, 'show']);
Route::delete('customer-payments/{customerPayment}', [CustomerPaymentController::class, 'destroy']);

Route::get('accounts', [FinanceAccountController::class, 'index']);
