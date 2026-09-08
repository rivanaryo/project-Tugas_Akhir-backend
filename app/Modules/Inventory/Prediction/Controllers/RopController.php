<?php

namespace App\Modules\Inventory\Prediction\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Prediction\Services\MlPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RopController extends Controller
{
    public function __construct(private readonly MlPredictionService $mlService)
    {
    }

    public function predict(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string|min:1',
            'warehouse_id' => 'nullable|string|min:1',
            'days_to_predict' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $days = (int) $request->input('days_to_predict', 7);

        $result = $this->mlService->predictRop($productId, $warehouseId, $days);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'ML service tidak tersedia atau mengalami error. Silakan coba lagi nanti.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prediksi ROP berhasil diambil',
            'data' => $result,
        ], 200);
    }

    public function modelMetrics(): JsonResponse
    {
        $metrics = $this->mlService->getModelMetrics();

        if ($metrics === null) {
            return response()->json([
                'success' => false,
                'message' => 'ML service tidak tersedia atau mengalami error. Silakan coba lagi nanti.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Model metrics berhasil diambil',
            'data' => $metrics,
        ], 200);
    }
}
