<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MlPredictionService
{
    public function predictRop(string $productId, ?string $warehouseId = null, int $days = 7): ?array
    {
        $url = trim(config('ml.base_url'), '/') . config('ml.predict_rop_endpoint');

        try {
            $response = Http::timeout((int) config('ml.timeout', 10))
                ->post($url, [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'days_to_predict' => $days,
                ]);

            if ($response->failed()) {
                Log::error('ML predict-rop request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::error('ML predict-rop returned invalid JSON', ['body' => $response->body()]);
                return null;
            }

            $this->savePrediction($productId, $warehouseId, $days, $data);
            return $data;
        } catch (Exception $e) {
            Log::error('ML predict-rop exception: ' . $e->getMessage(), [
                'url' => $url,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'days' => $days,
            ]);
            return null;
        }
    }

    public function getModelMetrics(): ?array
    {
        $url = trim(config('ml.base_url'), '/') . config('ml.model_metrics_endpoint');

        try {
            $response = Http::timeout((int) config('ml.timeout', 10))
                ->get($url);

            if ($response->failed()) {
                Log::error('ML model metrics request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::error('ML model metrics returned invalid JSON', ['body' => $response->body()]);
                return null;
            }

            return $data;
        } catch (Exception $e) {
            Log::error('ML model metrics exception: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    private function savePrediction(string $productId, ?string $warehouseId, int $days, array $data): void
    {
        DB::table('ml_predictions')->insert([
            'id' => (string) Str::uuid(),
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'predicted_demand_total' => $data['predicted_demand_total'] ?? 0,
            'reorder_point' => $data['reorder_point'] ?? 0,
            'current_stock' => $data['current_stock'] ?? 0,
            'reorder_recommended' => $data['reorder_recommended'] ?? false,
            'suggested_order_quantity' => $data['suggested_order_quantity'] ?? 0,
            'forecast_days' => $days,
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
