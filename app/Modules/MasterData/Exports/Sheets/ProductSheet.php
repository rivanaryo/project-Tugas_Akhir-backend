<?php

namespace App\Modules\MasterData\Exports\Sheets;

use App\Modules\MasterData\DataProduk\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Product::with(['category', 'unit'])->get()->map(function ($product) {
            return [
                'product_name' => $product->product_name,
                'category' => $product->category?->category_name ?? '-',
                'unit' => $product->unit?->unit_name ?? '-',
                'status' => $product->is_active ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif',
                'created_at' => $product->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category',
            'Unit',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Products';
    }
}
