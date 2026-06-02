<?php

namespace App\Modules\Sales\Exports\Sheets;

use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesDeliverySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(): Collection
    {
        $query = SalesDelivery::query()
            ->with(['customer', 'salesOrder']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($delivery) {
            return [
                'delivery_number' => $delivery->delivery_number,
                'customer_name' => $delivery->customer?->customer_name ?? '-',
                'sales_order_number' => $delivery->salesOrder?->order_number ?? '-',
                'delivery_date' => $delivery->delivery_date ? (is_string($delivery->delivery_date) ? date('d/m/Y', strtotime($delivery->delivery_date)) : $delivery->delivery_date->format('d/m/Y')) : '-',
                'status' => $this->formatStatus($delivery->status),
                'created_at' => $delivery->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Delivery Number',
            'Customer Name',
            'Sales Order Number',
            'Delivery Date',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Sales Delivery';
    }

    private function formatStatus($status): string
    {
        // Handle boolean
        if (is_bool($status)) {
            return $status ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif';
        }

        // Handle numeric values (integers)
        if (is_numeric($status) && !is_string($status)) {
            $numericMap = [
                0 => 'INACTIVE - Tidak Aktif',
                1 => 'ACTIVE - Aktif',
                2 => 'PENDING - Menunggu',
                3 => 'PROCESSING - Dalam Proses',
                4 => 'COMPLETED - Selesai',
                5 => 'CANCELLED - Dibatalkan',
                6 => 'REJECTED - Ditolak',
                7 => 'SHIPPED - Dikirim',
                8 => 'DELIVERED - Diterima',
                9 => 'DRAFT - Belum Diproses',
            ];
            return $numericMap[$status] ?? 'STATUS CODE: ' . $status;
        }

        // Convert to string and clean
        $status = strtolower(trim((string) $status));

        // Common status for all sales modules
        $statusMap = [
            'draft' => 'DRAFT - Belum Diproses',
            'pending' => 'PENDING - Menunggu',
            'preparing' => 'PREPARING - Persiapan',
            'ready' => 'READY - Siap Kirim',
            'shipped' => 'SHIPPED - Dalam Pengiriman',
            'in_transit' => 'IN TRANSIT - Transit',
            'delivered' => 'DELIVERED - Diterima',
            'completed' => 'COMPLETED - Selesai',
            'complete' => 'COMPLETED - Selesai',
            'cancelled' => 'CANCELLED - Dibatalkan',
            'canceled' => 'CANCELLED - Dibatalkan',
            'returned' => 'RETURNED - Dikembalikan',
            'approved' => 'APPROVED - Disetujui',
            'processing' => 'PROCESSING - Dalam Proses',
            'rejected' => 'REJECTED - Ditolak',
            'false' => 'INACTIVE - Tidak Aktif',
            'true' => 'ACTIVE - Aktif',
            '0' => 'INACTIVE - Tidak Aktif',
            '1' => 'ACTIVE - Aktif',
            '2' => 'PENDING - Menunggu',
            '3' => 'PROCESSING - Dalam Proses',
            '4' => 'COMPLETED - Selesai',
            '5' => 'CANCELLED - Dibatalkan',
            '' => 'EMPTY - Kosong',
            'open' => 'OPEN - Terbuka',
            'close' => 'CLOSED - Ditutup',
            'paid' => 'PAID - Lunas',
            'unpaid' => 'UNPAID - Belum Bayar',
            'partial' => 'PARTIAL - Sebagian',
            'sent' => 'SENT - Terkirim',
            'viewed' => 'VIEWED - Dilihat',
        ];

        return $statusMap[$status] ?? 'STATUS: ' . strtoupper($status);
    }
}