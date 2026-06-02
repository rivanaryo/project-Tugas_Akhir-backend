<?php

namespace App\Modules\Purchase\Exports;

use App\Modules\Purchase\Exports\Sheets\GoodReceiptSheet;
use App\Modules\Purchase\Exports\Sheets\PurchaseOrderSheet;
use App\Modules\Purchase\Exports\Sheets\VendorPaymentSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPembelianExport implements WithMultipleSheets
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new PurchaseOrderSheet($this->startDate, $this->endDate),
            new GoodReceiptSheet($this->startDate, $this->endDate),
            new VendorPaymentSheet($this->startDate, $this->endDate),
        ];
    }
}
