<?php

namespace App\Modules\Sales\SalesQuotation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesQuotation\DTOs\SalesQuotationDTO;
use App\Modules\Sales\SalesQuotation\Repositories\SalesQuotationRepositories;
use App\Modules\Sales\SalesQuotation\Request\StoreSalesQuotationRequest;
use App\Modules\Sales\SalesQuotation\Request\UpdateSalesQuotationRequest;
use App\Modules\Sales\SalesQuotation\Services\SalesQuotationConverter;
use Illuminate\Http\JsonResponse;

class SalesQuotationController extends Controller
{
    public function __construct(
        private readonly SalesQuotationRepositories $salesQuotationRepositories,
        private readonly SalesQuotationConverter $salesQuotationConverter,
    ) {
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $salesQuotations = $this->salesQuotationRepositories->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Sales quotations retrieved successfully',
            'data' => $salesQuotations,
        ]);
    }

    public function store(StoreSalesQuotationRequest $request): JsonResponse
    {
        $salesQuotationDTO = SalesQuotationDTO::fromStoreRequest($request);
        $payload = array_merge($salesQuotationDTO->toArray(), [
            'subtotal' => 0,
            'total_tax' => 0,
            'grand_total' => 0,
        ]);
        $salesQuotation = $this->salesQuotationRepositories->create($payload);
    
        return response()->json([
            'success' => true,
            'message' => 'Sales quotation created successfully',
            'data' => $salesQuotation->load('items'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $salesQuotation = $this->salesQuotationRepositories->findById($id);
        if (!$salesQuotation) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation retrieved successfully',
            'data' => $salesQuotation->load('items'),
        ]);
    }

    public function update(UpdateSalesQuotationRequest $request, string $id): JsonResponse
    {
        $salesQuotationDTO = SalesQuotationDTO::fromUpdateRequest($request, $id);
        $updatedSalesQuotation = $this->salesQuotationRepositories->update($id, $salesQuotationDTO->toArray());

        if (!$updatedSalesQuotation) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation updated successfully',
            'data' => $updatedSalesQuotation->load('items'),
        ]);
    }

    public function convertToOrder(string $id): JsonResponse
    {
        $salesQuotation = $this->salesQuotationRepositories->findById($id);
        if (!$salesQuotation) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation not found',
            ], 404);
        }

        $salesOrder = $this->salesQuotationConverter->convert($salesQuotation);

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation converted to sales order successfully',
            'data' => $salesOrder->load(['items', 'salesQuotation']),
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->salesQuotationRepositories->delete($id);
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation deleted successfully',
        ]);
    }

}
