<?php

namespace App\Modules\Inventory\Exports\Sheets;

use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseOrderSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = PurchaseOrder::query()
            ->with(['vendor']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->get()->map(function ($po) {
            return [
                'po_number' => $po->po_number,
                'vendor_name' => $po->vendor?->vendor_name ?? '-',
                'po_date' => $po->po_date ? (is_string($po->po_date) ? date('d/m/Y', strtotime($po->po_date)) : $po->po_date->format('d/m/Y')) : '-',
                'status' => $this->formatStatus($po->status),
                'created_at' => $po->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Vendor Name',
            'PO Date',
            'Status',
            'Created At',
        ];
    }

    public function title(): string
    {
        return 'Purchase Order';
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
                7 => 'SHIPPED - Dikirim',
                8 => 'DELIVERED - Diterima',
                9 => 'DRAFT - Belum Diproses',
            ];
            return $numericMap[$status] ?? 'STATUS CODE: ' . $status;
        }

        $status = strtolower(trim((string) $status));

        $statusMap = [
            'draft' => 'DRAFT - Belum Diproses',
            'pending' => 'PENDING - Menunggu',
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
        ];

        return $statusMap[$status] ?? 'STATUS: ' . strtoupper($status);
    }
}
