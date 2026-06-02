<?php

namespace App\Modules\MasterData\Exports\Sheets;

use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomerSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Customer::all()->map(function ($customer) {
            return [
                'customer_name' => $customer->customer_name,
                'contact_person' => $customer->contact_person ?? '-',
                'phone_number' => $customer->phone_number ?? '-',
                'email' => $customer->email ?? '-',
                'address' => $customer->address ?? '-',
                'status' => $customer->is_active ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif',
                'created_at' => $customer->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Contact Person',
            'Phone Number',
            'Email',
            'Address',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Customers';
    }
}
