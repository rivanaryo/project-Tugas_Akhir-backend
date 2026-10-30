<?php

namespace App\Modules\Sales\SalesQuotation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\SalesQuotation\DTOs\SalesQuotationItemsDTO;
use App\Modules\Sales\SalesQuotation\Repositories\SalesQuotationItemsRepositories;
use App\Modules\Sales\SalesQuotation\Request\StoreSalesQuotationItemsRequest;
use App\Modules\Sales\SalesQuotation\Request\UpdateSalesQuotationItemsRequest;
use App\Modules\Sales\SalesQuotation\Services\SalesQuotationGrandTotalCalculator;
use Illuminate\Http\JsonResponse;

class SalesQuotationItemsController extends Controller
{
    public function __construct(
        private readonly SalesQuotationItemsRepositories $salesQuotationItemsRepositories,
        private readonly SalesQuotationGrandTotalCalculator $grandTotalCalculator
    ) {
    }

    public function index(string $salesQuotationId): JsonResponse
    {
        $salesQuotationItems = $this->salesQuotationItemsRepositories->all(['sales_quotation_id' => $salesQuotationId])->load('product');
        return response()->json([
            'success' => true,
            'message' => 'Sales quotation items retrieved successfully',
            'data' => $salesQuotationItems,
        ]);
    }

    public function store(StoreSalesQuotationItemsRequest $request, string $salesQuotationId): JsonResponse
    {
        $salesQuotationItemsDTO = SalesQuotationItemsDTO::fromStoreRequest($request);
        $payload = array_merge($salesQuotationItemsDTO->toArray(), ['sales_quotation_id' => $salesQuotationId]);
        $salesQuotationItem = $this->salesQuotationItemsRepositories->create($payload);

        // Update quotation totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateQuotationTotals($salesQuotationId);

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation item created successfully',
            'data' => $salesQuotationItem,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $salesQuotationItem = $this->salesQuotationItemsRepositories->findById($id);
        if (!$salesQuotationItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation item retrieved successfully',
            'data' => $salesQuotationItem,
        ]);
    }

    public function update(UpdateSalesQuotationItemsRequest $request, string $salesQuotationId, string $id): JsonResponse
    {
        $salesQuotationItemsDTO = SalesQuotationItemsDTO::fromUpdateRequest($request);
        $updatedSalesQuotationItem = $this->salesQuotationItemsRepositories->update($id, $salesQuotationItemsDTO->toArray());

        if (!$updatedSalesQuotationItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation item not found',
            ], 404);
        }

        // Update quotation totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateQuotationTotals($salesQuotationId);

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation item updated successfully',
            'data' => $updatedSalesQuotationItem,
        ]);
    }

    public function destroy(string $salesQuotationId, string $id): JsonResponse
    {
        $deleted = $this->salesQuotationItemsRepositories->delete($id);
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Sales quotation item not found',
            ], 404);
        }

        // Update quotation totals (subtotal, total_tax, grand_total)
        $this->grandTotalCalculator->updateQuotationTotals($salesQuotationId);

        return response()->json([
            'success' => true,
            'message' => 'Sales quotation item deleted successfully',
        ]);
    }
}
