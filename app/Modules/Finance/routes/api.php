<?php

use App\Modules\Finance\VendorPayment\Controllers\VendorPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('vendor-payments', [VendorPaymentController::class, 'index']);
Route::post('vendor-payments', [VendorPaymentController::class, 'store']);
Route::get('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'show']);
Route::match(['put', 'patch'], 'vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'update']);
Route::delete('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'destroy']);
