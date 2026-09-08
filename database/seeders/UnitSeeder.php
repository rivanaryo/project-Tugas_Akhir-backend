<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('units')->insert([
            'id' => (string) Str::uuid(),
            'unit_code' => 'PCS',
            'unit_name' => 'Pieces',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
