<?php

namespace App\Modules\Sales\Exports;

use App\Modules\Sales\Exports\Sheets\InvoiceSheet;
use App\Modules\Sales\Exports\Sheets\SalesDeliverySheet;
use App\Modules\Sales\Exports\Sheets\SalesOrderSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPenjualanExport implements WithMultipleSheets
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
            new SalesOrderSheet($this->startDate, $this->endDate),
            new SalesDeliverySheet($this->startDate, $this->endDate),
            new InvoiceSheet($this->startDate, $this->endDate),
        ];
    }
}
