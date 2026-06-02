<?php

namespace App\Modules\MasterData\Exports\Sheets;

use App\Modules\MasterData\DataProduk\References\Models\Units;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Units::all()->map(function ($unit) {
            return [
                'unit_code' => $unit->unit_code,
                'unit_name' => $unit->unit_name,
                'status' => $unit->is_active ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif',
                'created_at' => $unit->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Unit Code',
            'Unit Name',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Units';
    }
}
