<?php

namespace App\Modules\Sales\SalesDelivery\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesDelivery\DTOs\SalesDeliveryItemsDTO;
use App\Modules\Sales\SalesDelivery\Request\StoreSalesDeliveryItemsRequest;
use App\Modules\Sales\SalesDelivery\Request\UpdateSalesDeliveryItemsRequest;
use App\Modules\Sales\SalesDelivery\Repositories\SalesDeliveryItemsRepositories;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesDeliveryItemsController extends Controller
{
    public function __construct(
        private readonly SalesDeliveryItemsRepositories $repo,
    ) {
    }

    public function index (string $salesDeliveryId, Request $request) : JsonResponse
    {
        $per_page = (int) $request->get('per_page', 15);
        $filters = array_merge($request->all(), ['sales_delivery_id' => $salesDeliveryId]);
        $items = $this->repo->paginate($filters, $per_page);
        return response()->json([
            'success' => true,
            'message' => 'Sales delivery items retrieved successfully',
            'data' => $items,
        ]);
    }

    public function show(string $id) : JsonResponse
    {
        $salesDeliveryItems = $this->repo->findById($id)->load(['salesOrderItem.product', 'salesDelivery.salesOrder', 'salesDelivery.customer']);
        if (!$salesDeliveryItems) {
            return response()->json([
                'success' => false,
                'message' => 'Sales delivery item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales delivery item retrieved successfully',
            'data' => $salesDeliveryItems,
        ]);
    }

    public function store(StoreSalesDeliveryItemsRequest $request) : JsonResponse
    {
        $salesDeliveryItemsDTO = SalesDeliveryItemsDTO::fromStoreRequest($request);
        $salesDeliveryItems = $this->repo->create($salesDeliveryItemsDTO->toArray());
    
        return response()->json([
            'success' => true,
            'message' => 'Sales delivery item created successfully',
            'data' => $salesDeliveryItems,
        ], 201);
    }

    public function update(UpdateSalesDeliveryItemsRequest $request, string $id) : JsonResponse
    {
        $salesDeliveryItemsDTO = SalesDeliveryItemsDTO::fromUpdateRequest($request);
        $salesDeliveryItems = $this->repo->update($id, $salesDeliveryItemsDTO->toArray());
    
        return response()->json([
            'success' => true,
            'message' => 'Sales delivery item updated successfully',
            'data' => $salesDeliveryItems,
        ]);
    }

    public function destroy(string $id) : JsonResponse
    {
        $deleted = $this->repo->delete($id);
    
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales delivery item not found',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Sales delivery item deleted successfully',
        ]);
    }

}
