<?php

use App\Modules\Sales\Http\Controllers\SalesExportController;
use App\Modules\Sales\Invoice\Controllers\InvoiceController;
use App\Modules\Sales\Invoice\Controllers\InvoiceItemController;
use App\Modules\Sales\SalesDelivery\Controllers\SalesDeliveryController;
use App\Modules\Sales\SalesDelivery\Controllers\SalesDeliveryItemsController;
use App\Modules\Sales\SalesOrder\Controllers\SalesOrderController;
use App\Modules\Sales\SalesOrder\Controllers\SalesOrderItemsController;
use Illuminate\Support\Facades\Route;

Route::get('sales-orders', [SalesOrderController::class, 'index']);
Route::post('sales-orders', [SalesOrderController::class, 'store']);
Route::get('sales-orders/{id}', [SalesOrderController::class, 'show']);
Route::get('sales-orders/{id}/balance', [SalesOrderController::class, 'balance']);
Route::match(['put', 'patch'], 'sales-orders/{id}', [SalesOrderController::class, 'update']);
Route::delete('sales-orders/{id}', [SalesOrderController::class, 'destroy']);

// Sales Order Items routes
Route::get('sales-orders/{salesOrderId}/items', [SalesOrderItemsController::class, 'index']);
Route::post('sales-orders/{salesOrderId}/items', [SalesOrderItemsController::class, 'store']);
Route::get('sales-orders/{salesOrderId}/items/{id}', [SalesOrderItemsController::class, 'show']);
Route::match(['put', 'patch'], 'sales-orders/{salesOrderId}/items/{id}', [SalesOrderItemsController::class, 'update']);
Route::delete('sales-orders/{salesOrderId}/items/{id}', [SalesOrderItemsController::class, 'destroy']);


// Sales Delivery routes
Route::get('sales-deliveries', [SalesDeliveryController::class, 'index']);
Route::post('sales-deliveries', [SalesDeliveryController::class, 'store']);
Route::get('sales-deliveries/{id}', [SalesDeliveryController::class, 'show']);
Route::match(['put', 'patch'], 'sales-deliveries/{id}', [SalesDeliveryController::class, 'update']);
Route::delete('sales-deliveries/{id}', [SalesDeliveryController::class, 'destroy']);

// Sales Delivery Items routes
Route::get('sales-deliveries/{salesDeliveryId}/items', [SalesDeliveryItemsController::class, 'index']);
Route::post('sales-deliveries/{salesDeliveryId}/items', [SalesDeliveryItemsController::class, 'store']);
Route::get('sales-deliveries/{salesDeliveryId}/items/{id}', [SalesDeliveryItemsController::class, 'show']);
Route::match(['put', 'patch'], 'sales-deliveries/{salesDeliveryId}/items/{id}', [SalesDeliveryItemsController::class, 'update']);
Route::delete('sales-deliveries/{salesDeliveryId}/items/{id}', [SalesDeliveryItemsController::class, 'destroy']);

// Invoice routes
Route::get('invoices', [InvoiceController::class, 'index']);
Route::post('invoices', [InvoiceController::class, 'store']);
Route::get('invoices/{id}', [InvoiceController::class, 'show']);
Route::get('invoices/{id}/balance', [InvoiceController::class, 'balance']);
Route::match(['put', 'patch'], 'invoices/{id}', [InvoiceController::class, 'update']);
Route::delete('invoices/{id}', [InvoiceController::class, 'destroy']);

// Invoice Items routes
Route::get('invoices/{invoiceId}/items', [InvoiceItemController::class, 'index']);
Route::post('invoices/{invoiceId}/items', [InvoiceItemController::class, 'store']);
Route::get('invoices/{invoiceId}/items/{id}', [InvoiceItemController::class, 'show']); 
Route::match(['put', 'patch'], 'invoices/{invoiceId}/items/{id}', [InvoiceItemController::class, 'update']);
Route::delete('invoices/{invoiceId}/items/{id}', [InvoiceItemController::class, 'destroy']);

// Export routes
Route::get('export', [SalesExportController::class, 'export']);
