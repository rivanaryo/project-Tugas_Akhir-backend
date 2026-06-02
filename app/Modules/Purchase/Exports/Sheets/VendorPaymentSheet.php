<?php

namespace App\Modules\Purchase\Exports\Sheets;

use App\Modules\Purchase\VendorPayment\Models\VendorPayment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VendorPaymentSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = VendorPayment::query()
            ->with(['vendors']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($payment) {
            return [
                'payment_number' => $payment->payment_number,
                'vendor_name' => $payment->vendors?->vendor_name ?? '-',
                'vendor_invoice_number' => $payment->vendor_invoice_number ?? '-',
                'payment_type' => strtoupper($payment->payment_type ?? '-'),
                'payment_date' => $payment->payment_date ? (is_string($payment->payment_date) ? date('d/m/Y', strtotime($payment->payment_date)) : $payment->payment_date->format('d/m/Y')) : '-',
                'total_paid' => number_format($payment->total_paid ?? 0, 2),
                'tax_amount' => number_format($payment->tax_amount ?? 0, 2),
                'status' => $this->formatStatus($payment->status),
                'created_at' => $payment->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Payment Number',
            'Vendor Name',
            'Vendor Invoice',
            'Payment Type',
            'Payment Date',
            'Total Paid (Rp)',
            'Tax Amount (Rp)',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Vendor Payment';
    }

    private function formatStatus($status): string
    {
        if (is_bool($status)) {
            return $status ? 'ACTIVE - Aktif' : 'INACTIVE - Tidak Aktif';
        }

        if (is_numeric($status) && !is_string($status)) {
            $numericMap = [
                0 => 'INACTIVE - Tidak Aktif',
                1 => 'ACTIVE - Aktif',
                2 => 'PENDING - Menunggu',
                3 => 'PROCESSING - Dalam Proses',
                4 => 'COMPLETED - Selesai',
                5 => 'CANCELLED - Dibatalkan',
                6 => 'REJECTED - Ditolak',
                7 => 'PAID - Lunas',
                8 => 'PARTIAL - Sebagian',
                9 => 'DRAFT - Belum Diproses',
            ];
            return $numericMap[$status] ?? 'STATUS CODE: ' . $status;
        }

        $status = strtolower(trim((string) $status));

        $statusMap = [
            'draft' => 'DRAFT - Belum Diproses',
            'pending' => 'PENDING - Menunggu',
            'paid' => 'PAID - Lunas',
            'partial' => 'PARTIAL - Dibayar Sebagian',
            'approved' => 'APPROVED - Disetujui',
            'processing' => 'PROCESSING - Dalam Proses',
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
        ];

        return $statusMap[$status] ?? 'STATUS: ' . strtoupper($status);
    }
}
