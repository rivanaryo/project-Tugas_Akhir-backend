<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $woLimit = (int) $request->get('active_work_orders_limit', 10);

        $kpi = $this->service->getKpi();
        $pipeline = $this->service->getPipeline($page, $perPage);
        $activeWorkOrders = $this->service->getActiveWorkOrders($woLimit);

        return response()->json([
            'success' => true,
            'data' => [
                'kpi' => $kpi,
                'pipeline' => $pipeline,
                'active_work_orders' => $activeWorkOrders,
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
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        return response()->json([
            'success' => true,
            'data' => $this->service->getPipeline($page, $perPage),
        ]);
    }
}