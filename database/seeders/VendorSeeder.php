<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $vendors = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'PT Sumber Jaya',
                'contact_person' => 'Budi Santoso',
                'phone_number' => '081234567890',
                'email' => 'budi@sumberjaya.co.id',
                'address' => 'Jl. Sudirman No. 1, Jakarta',
                'lead_time_days' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'CV Mitra Sukses',
                'contact_person' => 'Dewi Lestari',
                'phone_number' => '082345678901',
                'email' => 'dewi@mitrasukses.co.id',
                'address' => 'Jl. Asia Afrika No. 2, Bandung',
                'lead_time_days' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'PT Megah Sentosa',
                'contact_person' => 'Ahmad Fauzi',
                'phone_number' => '083456789012',
                'email' => 'ahmad@megahsentosa.co.id',
                'address' => 'Jl. Pahlawan No. 3, Surabaya',
                'lead_time_days' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('vendors')->insert($vendors);

        $mVendorRows = array_map(fn (array $vendor) => [
            'id' => $vendor['id'],
            'vendor_name' => $vendor['name'],
            'contact_person' => $vendor['contact_person'],
            'phone_number' => $vendor['phone_number'],
            'email' => $vendor['email'],
            'address' => $vendor['address'],
            'is_active' => $vendor['is_active'],
            'created_at' => $vendor['created_at'],
            'updated_at' => $vendor['updated_at'],
        ], $vendors);

        DB::table('m_vendor')->insert($mVendorRows);
    }
}
