<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'active_work_orders_limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 10);
        $woLimit = (int) ($validated['active_work_orders_limit'] ?? 4);

        $receivables = $this->service->getReceivableSummary();
        $workOrders = $this->service->getActiveWorkOrders($woLimit);

        return response()->json([
            'success' => true,
            'data' => [
                'kpi' => $this->service->getKpi($receivables),
                'pipeline_summary' => $receivables,
                'pipeline' => $this->service->getPipeline($page, $perPage),
                'active_work_orders' => $workOrders['items'],
                'active_work_orders_total' => $workOrders['total'],
            ],
        ]);
    }

    public function kpi(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getKpi(),
        ]);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->getPipeline(
                (int) ($validated['page'] ?? 1),
                (int) ($validated['per_page'] ?? 10),
            ),
        ]);
    }
}
