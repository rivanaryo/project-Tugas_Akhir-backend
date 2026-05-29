<?php

namespace App\Modules\Sales\SalesOrder\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesOrder\DTOs\SalesOrderDTO;
use App\Modules\Sales\SalesOrder\Repositories\SalesOrderRepositories;
use App\Modules\Sales\SalesOrder\Request\StoreSalesOrderRequest;
use App\Modules\Sales\SalesOrder\Request\UpdateSalesOrderRequest;
use Illuminate\Http\JsonResponse;

class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesOrderRepositories $salesOrderRepositories,
    ) {
    }

    public function index(): JsonResponse
    {
        $salesOrders = $this->salesOrderRepositories->all()->load( 'customer');
        
        return response()->json([
            'success' => true,
            'message' => 'Sales orders retrieved successfully',
            'data' => $salesOrders,
        ]);
    }

    public function store(StoreSalesOrderRequest $request): JsonResponse
    {
        $salesOrderDTO = SalesOrderDTO::fromStoreRequest($request);
        $payload = array_merge($salesOrderDTO->toArray(), [
            'subtotal' => 0,
            'total_tax' => 0,
            'grand_total' => 0,
        ]);
        $salesOrder = $this->salesOrderRepositories->create($payload);
    
        return response()->json([
            'success' => true,
            'message' => 'Sales order created successfully',
            'data' => $salesOrder->load('items'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $salesOrder = $this->salesOrderRepositories->findById($id);
        if (!$salesOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales order retrieved successfully',
            'data' => $salesOrder->load('items'),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, string $id): JsonResponse
    {
        $salesOrderDTO = SalesOrderDTO::fromUpdateRequest($request, $id);
        $updatedSalesOrder = $this->salesOrderRepositories->update($id, $salesOrderDTO->toArray());

        if (!$updatedSalesOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales order updated successfully',
            'data' => $updatedSalesOrder->load('items'),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->salesOrderRepositories->delete($id);
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales order deleted successfully',
        ]);
    }

}
