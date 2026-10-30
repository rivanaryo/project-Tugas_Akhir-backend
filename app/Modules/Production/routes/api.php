<?php

use App\Modules\Production\FinishedGoodsReceipt\Controllers\FinishedGoodsReceiptController;
use App\Modules\Production\GoodReceipt\Controllers\GoodReceiptController;
use App\Modules\Production\GoodReceipt\Controllers\GoodReceiptItemsController;
use App\Modules\Production\PurchaseOrder\Controllers\PurchaseOrderController;
use App\Modules\Production\PurchaseOrder\Controllers\PurchaseOrderItemsController;
use App\Modules\Production\WorkOrder\Controllers\WorkOrderController;
use App\Modules\Production\MaterialChecklist\Controllers\MaterialChecklistController;
use Illuminate\Support\Facades\Route;

Route::apiResource('work-orders', WorkOrderController::class);
Route::post('work-orders/from-sales-order', [WorkOrderController::class, 'fromSalesOrder']);
Route::patch('work-orders/{workOrder}/stage', [WorkOrderController::class, 'updateStage']);

Route::get('material-checklist', [MaterialChecklistController::class, 'index']);

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

Route::get('finished-goods-receipts', [FinishedGoodsReceiptController::class, 'index']);
Route::post('finished-goods-receipts', [FinishedGoodsReceiptController::class, 'store']);
Route::get('finished-goods-receipts/{finishedGoodsReceipt}', [FinishedGoodsReceiptController::class, 'show']);
Route::match(['put', 'patch'], 'finished-goods-receipts/{finishedGoodsReceipt}', [FinishedGoodsReceiptController::class, 'update']);
Route::delete('finished-goods-receipts/{finishedGoodsReceipt}', [FinishedGoodsReceiptController::class, 'destroy']);
