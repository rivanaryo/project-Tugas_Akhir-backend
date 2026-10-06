<?php

namespace App\Modules\Inventory\Exports\Sheets;

use App\Modules\Inventory\GoodReceipt\Models\GoodReceipt;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GoodReceiptSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = GoodReceipt::query()
            ->with(['vendor', 'purchaseOrder']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($gr) {
            return [
                'gr_number' => $gr->gr_number,
                'vendor_name' => $gr->vendor?->vendor_name ?? '-',
                'po_number' => $gr->purchaseOrder?->po_number ?? '-',
                'receipt_date' => $gr->receipt_date ? (is_string($gr->receipt_date) ? date('d/m/Y', strtotime($gr->receipt_date)) : $gr->receipt_date->format('d/m/Y')) : '-',
                'status' => $this->formatStatus($gr->status),
                'created_at' => $gr->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'GR Number',
            'Vendor Name',
            'PO Number',
            'Receipt Date',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Good Receipt';
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
                7 => 'RECEIVED - Diterima',
                8 => 'INSPECTED - Diperiksa',
                9 => 'DRAFT - Belum Diproses',
            ];
            return $numericMap[$status] ?? 'STATUS CODE: ' . $status;
        }

        $status = strtolower(trim((string) $status));

        $statusMap = [
            'draft' => 'DRAFT - Belum Diproses',
            'pending' => 'PENDING - Menunggu',
            'received' => 'RECEIVED - Diterima',
            'inspected' => 'INSPECTED - Diperiksa',
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
