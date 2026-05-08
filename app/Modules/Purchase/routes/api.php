<?php

use App\Modules\Purchase\PurchaseOrder\Controllers\PurchaseOrderController;
use App\Modules\Purchase\PurchaseOrder\Controllers\PurchaseOrderItemsController;
use App\Modules\Purchase\GoodReceipt\Controllers\GoodReceiptController;
use App\Modules\Purchase\GoodReceipt\Controllers\GoodReceiptItemsController;
use App\Modules\Purchase\VendorPayment\Controllers\VendorPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
Route::match(['put', 'patch'], 'purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

Route::get('purchase-order-items', [PurchaseOrderItemsController::class, 'index']);
Route::post('purchase-order-items', [PurchaseOrderItemsController::class, 'store']);
Route::get('purchase-order-items/{purchaseOrderItem}', [PurchaseOrderItemsController::class, 'show']);
Route::match(['put', 'patch'], 'purchase-order-items/{purchaseOrderItem}', [PurchaseOrderItemsController::class, 'update']);
Route::delete('purchase-order-items/{purchaseOrderItem}', [PurchaseOrderItemsController::class, 'destroy']);

Route::get('good-receipts', [GoodReceiptController::class, 'index']);
Route::get('good-receipts/{goodReceipt}', [GoodReceiptController::class, 'show']);
Route::post('good-receipts', [GoodReceiptController::class, 'store']);
Route::match(['put', 'patch'], 'good-receipts/{goodReceipt}', [GoodReceiptController::class, 'update']);
Route::delete('good-receipts/{goodReceipt}', [GoodReceiptController::class, 'destroy']);

Route::get('good-receipt-items', [GoodReceiptItemsController::class, 'index']);
Route::get('good-receipt-items/{goodReceiptItem}', [GoodReceiptItemsController::class, 'show']);
Route::match(['put', 'patch'], 'good-receipt-items/{goodReceiptItem}', [GoodReceiptItemsController::class, 'update']);
Route::delete('good-receipt-items/{goodReceiptItem}', [GoodReceiptItemsController::class, 'destroy']);
Route::post('good-receipt-items', [GoodReceiptItemsController::class, 'store']);

Route::get('vendor-payments', [VendorPaymentController::class, 'index']);
Route::post('vendor-payments', [VendorPaymentController::class, 'store']);
Route::get('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'show']);
Route::match(['put', 'patch'], 'vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'update']);
Route::delete('vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'destroy']);