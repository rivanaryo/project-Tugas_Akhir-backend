<?php

use App\Modules\Inventory\GoodReceipt\Controllers\GoodReceiptController;
use App\Modules\Inventory\GoodReceipt\Controllers\GoodReceiptItemsController;
use App\Modules\Inventory\PurchaseOrder\Controllers\PurchaseOrderController;
use App\Modules\Inventory\PurchaseOrder\Controllers\PurchaseOrderItemsController;
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

// Export routes
Route::get('export', [\App\Modules\Inventory\Http\Controllers\PurchaseExportController::class, 'export']);

