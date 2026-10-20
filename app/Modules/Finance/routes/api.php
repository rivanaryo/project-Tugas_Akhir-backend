<?php

use App\Modules\Finance\Account\Controllers\FinanceAccountController;
use App\Modules\Finance\CustomerPayment\Controllers\CustomerPaymentController;
use App\Modules\Finance\Ledger\Controllers\AccountAdjustmentController;
use App\Modules\Finance\Ledger\Controllers\AccountTransferController;
use App\Modules\Finance\Ledger\Controllers\FinanceAccountSummaryController;
use App\Modules\Finance\Ledger\Controllers\FinanceAccountTransactionController;
use App\Modules\Finance\Ledger\Controllers\FinanceTransactionVoidController;
use App\Modules\Finance\VendorPayment\Controllers\VendorPaymentController;
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
Route::get('accounts/summary', FinanceAccountSummaryController::class);
Route::get('accounts/{account}/transactions', FinanceAccountTransactionController::class);

Route::post('account-adjustments', [AccountAdjustmentController::class, 'store']);
Route::post('account-transfers', [AccountTransferController::class, 'store']);
Route::post('transactions/{transaction}/void', FinanceTransactionVoidController::class);
