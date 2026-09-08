<?php

namespace App\Console\Commands;

use App\Modules\Inventory\Prediction\Services\MlPredictionService;
use Illuminate\Console\Command;

class PredictRopCommand extends Command
{
    protected $signature = 'ml:predict-rop
                            {product_id : UUID produk}
                            {warehouse_id? : UUID gudang (opsional)}
                            {--days=7 : Jumlah hari prediksi}';

    protected $description = 'Prediksi Reorder Point (ROP) via ML service';

    public function handle(MlPredictionService $mlService): int
    {
        $productId = $this->argument('product_id');
        $warehouseId = $this->argument('warehouse_id');
        $days = (int) $this->option('days');

        $this->info("Meminta prediksi ROP untuk product_id={$productId}, warehouse_id={$warehouseId}, days={$days}...");

        $result = $mlService->predictRop($productId, $warehouseId, $days);

        if ($result === null) {
            $this->error('Gagal mendapatkan prediksi dari ML service.');
            return self::FAILURE;
        }

        $this->info('Prediksi berhasil:');

        $rows = collect($result)
            ->map(fn ($value, $key) => [$key, is_bool($value) ? ($value ? 'true' : 'false') : $value])
            ->values()
            ->toArray();

        $this->table(['Key', 'Value'], $rows);

        return self::SUCCESS;
    }
}
