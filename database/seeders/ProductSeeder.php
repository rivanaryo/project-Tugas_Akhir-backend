<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = DB::table('categories')->pluck('id')->toArray();
        $vendors = DB::table('vendors')->pluck('id')->toArray();

        $products = [
            ['sku' => 'BR-001', 'name' => 'Beras 5kg', 'current_stock' => 150, 'safety_stock' => 15, 'cost_price' => 50000.00],
            ['sku' => 'MK-002', 'name' => 'Minyak Goreng 1L', 'current_stock' => 200, 'safety_stock' => 20, 'cost_price' => 18000.00],
            ['sku' => 'GP-003', 'name' => 'Gula Pasir 1kg', 'current_stock' => 180, 'safety_stock' => 18, 'cost_price' => 15000.00],
            ['sku' => 'MI-004', 'name' => 'Mie Instan Box 40 pcs', 'current_stock' => 120, 'safety_stock' => 12, 'cost_price' => 35000.00],
            ['sku' => 'KP-005', 'name' => 'Kopi Sachet Box 12 pcs', 'current_stock' => 100, 'safety_stock' => 10, 'cost_price' => 12000.00],
            ['sku' => 'SR-006', 'name' => 'Sarden Kaleng 155g', 'current_stock' => 90, 'safety_stock' => 15, 'cost_price' => 9000.00],
            ['sku' => 'TT-007', 'name' => 'Teh Celup 25 bags', 'current_stock' => 160, 'safety_stock' => 16, 'cost_price' => 8000.00],
            ['sku' => 'SG-008', 'name' => 'Sabun Mandi 85g', 'current_stock' => 140, 'safety_stock' => 14, 'cost_price' => 6000.00],
        ];

        $now = now();
        $rows = [];

        foreach ($products as $index => $product) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'sku' => $product['sku'],
                'name' => $product['name'],
                'category_id' => $categories[$index % count($categories)],
                'vendor_id' => $vendors[$index % count($vendors)],
                'current_stock' => $product['current_stock'],
                'safety_stock' => $product['safety_stock'],
                'cost_price' => $product['cost_price'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('products')->insert($rows);

        $productIds = DB::table('products')->pluck('id', 'name')->toArray();
        $unitId = DB::table('units')->value('id');
        $warehouseId = DB::table('warehouses')->value('id');
        $mProductRows = [];

        foreach ($products as $index => $product) {
            $mProductRows[] = [
                'id' => (string) Str::uuid(),
                'product_name' => $product['name'],
                'ml_product_id' => $productIds[$product['name']] ?? null,
                'category_id' => $categories[$index % count($categories)],
                'warehouse_id' => $warehouseId,
                'unit_id' => $unitId,
                'stock_level' => $product['current_stock'],
                'min_stock_level' => $product['safety_stock'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('m_products')->insert($mProductRows);
    }
}
