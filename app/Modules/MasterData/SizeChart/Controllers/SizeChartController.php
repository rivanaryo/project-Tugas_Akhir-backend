<?php

namespace App\Modules\MasterData\SizeChart\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\SizeChart\Models\SizeChart;
use App\Modules\MasterData\SizeChart\Request\StoreSizeChartRequest;
use App\Modules\MasterData\SizeChart\Request\UpdateSizeChartRequest;
use App\Modules\MasterData\SizeChart\Resources\SizeChartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SizeChartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SizeChart::with(['product', 'techPack'])->latest();

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($size = $request->get('size')) {
            $query->where('size', $size);
        }

        if ($search = $request->get('q')) {
            $query->whereHas('product', fn ($q) => $q->where('product_name', 'like', "%{$search}%"));
        }

        $perPage = (int) $request->get('per_page', 15);
        $sizeCharts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar size chart berhasil diambil',
            'data' => SizeChartResource::collection($sizeCharts),
        ]);
    }

    public function store(StoreSizeChartRequest $request): JsonResponse
    {
        $sizeChart = SizeChart::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Size chart berhasil dibuat',
            'data' => new SizeChartResource($sizeChart->load(['product', 'techPack'])),
        ], 201);
    }

    public function show(SizeChart $sizeChart): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail size chart berhasil diambil',
            'data' => new SizeChartResource($sizeChart->load(['product', 'techPack'])),
        ]);
    }

    public function update(UpdateSizeChartRequest $request, SizeChart $sizeChart): JsonResponse
    {
        $sizeChart->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Size chart berhasil diperbarui',
            'data' => new SizeChartResource($sizeChart->load(['product', 'techPack'])),
        ]);
    }

    public function destroy(SizeChart $sizeChart): JsonResponse
    {
        $sizeChart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Size chart berhasil dihapus',
        ]);
    }
}
