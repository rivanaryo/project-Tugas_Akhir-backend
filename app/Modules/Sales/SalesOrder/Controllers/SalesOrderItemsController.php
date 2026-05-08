<?php

namespace App\Modules\Sales\SalesOrder\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesOrder\DTOs\SalesOrderItemsDTO;
use App\Modules\Sales\SalesOrder\Repositories\SalesOrderItemsRepositories;
use App\Modules\Sales\SalesOrder\Request\StoreSalesOrderItemsRequest;
use App\Modules\Sales\SalesOrder\Request\UpdateSalesOrderItemsRequest;
use App\Modules\Sales\SalesOrder\Services\SalesOrderGrandTotalCalculator;
use Illuminate\Http\JsonResponse;

class SalesOrderItemsController extends Controller
{
    public function __construct(
        private readonly SalesOrderItemsRepositories $salesOrderItemsRepositories,
        private readonly SalesOrderGrandTotalCalculator $grandTotalCalculator
    ) {
    }

    public function index(string $salesOrderId): JsonResponse
    {
        $salesOrderItems = $this->salesOrderItemsRepositories->all(['sales_order_id' => $salesOrderId]);
        return response()->json([
            'success' => true,
            'message' => 'Sales order items retrieved successfully',
            'data' => $salesOrderItems,
        ]);
    }

    public function store(StoreSalesOrderItemsRequest $request, string $salesOrderId): JsonResponse
    {
        $salesOrderItemsDTO = SalesOrderItemsDTO::fromStoreRequest($request);
        $payload = array_merge($salesOrderItemsDTO->toArray(), ['sales_order_id' => $salesOrderId]);
        $salesOrderItem = $this->salesOrderItemsRepositories->create($payload);

        // Update order totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateOrderTotals($salesOrderId);

        return response()->json([
            'success' => true,
            'message' => 'Sales order item created successfully',
            'data' => $salesOrderItem,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $salesOrderItem = $this->salesOrderItemsRepositories->findById($id);
        if (!$salesOrderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales order item retrieved successfully',
            'data' => $salesOrderItem,
        ]);
    }

    public function update(UpdateSalesOrderItemsRequest $request, string $salesOrderId, string $id): JsonResponse
    {
        $salesOrderItemsDTO = SalesOrderItemsDTO::fromUpdateRequest($request);
        $updatedSalesOrderItem = $this->salesOrderItemsRepositories->update($id, $salesOrderItemsDTO->toArray());

        if (!$updatedSalesOrderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order item not found',
            ], 404);
        }

        // Update order totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateOrderTotals($salesOrderId);

        return response()->json([
            'success' => true,
            'message' => 'Sales order item updated successfully',
            'data' => $updatedSalesOrderItem,
        ]);
    }

    public function destroy(string $salesOrderId, string $id): JsonResponse
    {
        $deleted = $this->salesOrderItemsRepositories->delete($id);
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order item not found',
            ], 404);
        }

        // Update order totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateOrderTotals($salesOrderId);

        return response()->json([
            'success' => true,
            'message' => 'Sales order item deleted successfully',
        ]);
    }
}
