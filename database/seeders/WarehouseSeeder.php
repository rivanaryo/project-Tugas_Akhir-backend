<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $warehouses = [
            [
                'id' => (string) Str::uuid(),
                'code' => 'UTM',
                'name' => 'Gudang Utama',
                'location' => 'Pusat - Yogyakarta',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'code' => 'BDG',
                'name' => 'Gudang Bandung',
                'location' => 'Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'code' => 'JKT',
                'name' => 'Gudang Jakarta',
                'location' => 'Jakarta',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('warehouses')->insert($warehouses);
    }
}
