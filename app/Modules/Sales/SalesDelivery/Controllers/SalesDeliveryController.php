<?php

namespace App\Modules\Sales\SalesDelivery\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesDelivery\DTOs\SalesDeliveryDTO;
use App\Modules\Sales\SalesDelivery\Repositories\SalesDeliveryRepositories;
use App\Modules\Sales\SalesDelivery\Request\StoreSalesDeliveryRequest;
use App\Modules\Sales\SalesDelivery\Request\UpdateSalesDeliveryRequest;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class SalesDeliveryController extends Controller
{
    public function __construct(
        private readonly SalesDeliveryRepositories $repo,
    ) {
    }

    public function index(Request $request) : JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $data = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Sales deliveries retrieved successfully',
            'data' => $data,
        ]);
    }

    public function store(StoreSalesDeliveryRequest $request) : JsonResponse
    {
        $salesDeliveryDTO = SalesDeliveryDTO::fromStoreRequest($request);
        $salesDelivery = $this->repo->create($salesDeliveryDTO->toArray());
    
        return response()->json([
            'success' => true,
            'message' => 'Sales delivery created successfully',
            'data' => $salesDelivery->load('items'),
        ], 201);
    }

    public function show(string $id) : JsonResponse
    {
        $salesDelivery = $this->repo->findById($id)->load(['items.salesOrderItem.product', 'salesOrder', 'customer']);
        if (!$salesDelivery) {
            return response()->json([
                'success' => false,
                'message' => 'Sales delivery not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales delivery retrieved successfully',
            'data' => $salesDelivery,
        ]);
    }

    public function update(UpdateSalesDeliveryRequest $request, string $id) : JsonResponse
    {
        $salesDeliveryDTO = SalesDeliveryDTO::fromUpdateRequest($request, $id);
        $salesDelivery = $this->repo->update($id, $salesDeliveryDTO->toArray());
    
        if (!$salesDelivery) {
            return response()->json([
                'success' => false,
                'message' => 'Sales delivery not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales delivery updated successfully',
            'data' => $salesDelivery->load('items'),
        ]);
    }

    public function destroy(string $id) : JsonResponse
    {
        $deleted = $this->repo->delete($id);
    
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales delivery not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales delivery deleted successfully',
        ]);
    }
    
}