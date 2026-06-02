<?php

namespace App\Modules\MasterData\Exports\Sheets;

use App\Modules\MasterData\DataProduk\References\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategorySheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Category::all()->map(function ($category) {
            return [
                'category_name' => $category->category_name,
                'status' => $category->is_active ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif',
                'created_at' => $category->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Category Name',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Categories';
    }
}
