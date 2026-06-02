<?php

namespace App\Modules\Sales\Exports\Sheets;

use App\Modules\Sales\Invoice\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InvoiceSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = Invoice::query()
            ->with(['customer', 'salesOrder']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($invoice) {
            return [
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date ? (is_string($invoice->invoice_date) ? date('d/m/Y', strtotime($invoice->invoice_date)) : $invoice->invoice_date->format('d/m/Y')) : '-',
                'customer_name' => $invoice->customer?->customer_name ?? '-',
                'sales_order_number' => $invoice->salesOrder?->order_number ?? '-',
                'status' => $this->formatStatus($invoice->status),
                'created_at' => $invoice->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Invoice Date',
            'Customer Name',
            'Sales Order Number',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Invoice';
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
            'sent' => 'SENT - Terkirim',
            'viewed' => 'VIEWED - Dilihat',
            'partial' => 'PARTIAL - Dibayar Sebagian',
            'paid' => 'PAID - Lunas',
            'overdue' => 'OVERDUE - Jatuh Tempo',
            'cancelled' => 'CANCELLED - Dibatalkan',
            'canceled' => 'CANCELLED - Dibatalkan',
            'refunded' => 'REFUNDED - Dikembalikan',
            'approved' => 'APPROVED - Disetujui',
            'processing' => 'PROCESSING - Dalam Proses',
            'shipped' => 'SHIPPED - Dikirim',
            'delivered' => 'DELIVERED - Diterima',
            'completed' => 'COMPLETED - Selesai',
            'complete' => 'COMPLETED - Selesai',
            'rejected' => 'REJECTED - Ditolak',
            'preparing' => 'PREPARING - Persiapan',
            'ready' => 'READY - Siap Kirim',
            'in_transit' => 'IN TRANSIT - Transit',
            'returned' => 'RETURNED - Dikembalikan',
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
            'unpaid' => 'UNPAID - Belum Bayar',
        ];

        return $statusMap[$status] ?? 'STATUS: ' . strtoupper($status);
    }
}