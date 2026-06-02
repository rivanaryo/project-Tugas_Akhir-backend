<?php

namespace App\Modules\MasterData\Exports\Sheets;

use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VendorSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Vendor::all()->map(function ($vendor) {
            return [
                'vendor_name' => $vendor->vendor_name,
                'contact_person' => $vendor->contact_person ?? '-',
                'phone_number' => $vendor->phone_number ?? '-',
                'email' => $vendor->email ?? '-',
                'address' => $vendor->address ?? '-',
                'created_at' => $vendor->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Contact Person',
            'Phone Number',
            'Email',
            'Address',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Vendors';
    }
}
