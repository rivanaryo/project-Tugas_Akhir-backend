<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = DB::table('products')->get();
        $mProducts = DB::table('m_products')->pluck('id', 'product_name')->toArray();
        $warehouses = DB::table('warehouses')->pluck('id')->toArray();

        $baseDemands = [];
        $productWarehouses = [];
        $mProductIds = [];

        foreach ($products as $product) {
            $baseDemands[$product->id] = random_int(10, 20);
            $productWarehouses[$product->id] = $warehouses[array_rand($warehouses)];
            $mProductIds[$product->id] = $mProducts[$product->name] ?? null;
        }

        $startDate = Carbon::today()->subDays(365);
        $now = now();

        $rows = [];
        $counter = 1;

        for ($day = 0; $day < 365; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateString = $date->toDateString();
            $dayOfWeek = (int) $date->format('w'); // 0 = Minggu, 6 = Sabtu
            $dayOfMonth = (int) $date->format('j');

            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
            $isPayday = ($dayOfMonth >= 25 || $dayOfMonth <= 2);

            foreach ($products as $product) {
                $base = $baseDemands[$product->id];
                $factor = 1.0;

                if ($isWeekend) {
                    $factor *= random_int(140, 150) / 100;
                }

                if ($isPayday) {
                    $factor *= 1.3;
                }

                $noise = random_int(85, 115) / 100;
                $quantity = (int) round($base * $factor * $noise);

                if ($quantity < 1) {
                    $quantity = 1;
                }

                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'reference_number' => 'SO-' . $date->format('Ymd') . '-' . str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
                    'product_id' => $product->id,
                    'warehouse_id' => $productWarehouses[$product->id],
                    'm_products_id' => $mProductIds[$product->id],
                    'm_warehouses_id' => $productWarehouses[$product->id],
                    'type' => 'Out',
                    'quantity' => $quantity,
                    'reference' => 'Sales Order',
                    'date' => $dateString,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $counter++;

                // Restock setiap hari Senin
                if ($dayOfWeek === 1) {
                    $restock = random_int(50, 100);

                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'reference_number' => 'PO-' . $date->format('Ymd') . '-' . str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
                        'product_id' => $product->id,
                        'warehouse_id' => $productWarehouses[$product->id],
                        'm_products_id' => $mProductIds[$product->id],
                        'm_warehouses_id' => $productWarehouses[$product->id],
                        'type' => 'In',
                        'quantity' => $restock,
                        'reference' => 'Purchase Order',
                        'date' => $dateString,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $counter++;
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('stock_movements')->insert($chunk);
        }
    }
}
