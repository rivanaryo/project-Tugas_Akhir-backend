<?php

namespace App\Modules\Sales\Exports\Sheets;

use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesOrderSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = SalesOrder::query()
            ->with(['customer']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($order) {
            return [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->customer_name ?? '-',
                'order_date' => $order->order_date ? (is_string($order->order_date) ? date('d/m/Y', strtotime($order->order_date)) : $order->order_date->format('d/m/Y')) : '-',
                'status' => $this->formatStatus($order->status),
                'subtotal' => number_format($order->subtotal, 2),
                'total_tax' => number_format($order->total_tax, 2),
                'grand_total' => number_format($order->grand_total, 2),
                'created_at' => $order->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer Name',
            'Order Date',
            'Status',
            'Subtotal (Rp)',
            'Tax (Rp)',
            'Grand Total (Rp)',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Sales Order';
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

        $statusMap = [
            'draft' => 'DRAFT - Belum Diproses',
            'pending' => 'PENDING - Menunggu Approval',
            'approved' => 'APPROVED - Disetujui',
            'processing' => 'PROCESSING - Dalam Proses',
            'shipped' => 'SHIPPED - Dikirim',
            'delivered' => 'DELIVERED - Diterima',
            'completed' => 'COMPLETED - Selesai',
            'complete' => 'COMPLETED - Selesai',
            'cancelled' => 'CANCELLED - Dibatalkan',
            'canceled' => 'CANCELLED - Dibatalkan',
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
        ];

        return $statusMap[$status] ?? 'STATUS: ' . strtoupper($status);
    }
}