<?php

namespace App\Modules\MasterData\Exports;

use App\Modules\MasterData\Exports\Sheets\CategorySheet;
use App\Modules\MasterData\Exports\Sheets\CustomerSheet;
use App\Modules\MasterData\Exports\Sheets\ProductSheet;
use App\Modules\MasterData\Exports\Sheets\UnitSheet;
use App\Modules\MasterData\Exports\Sheets\VendorSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterDataExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new CustomerSheet(),
            new VendorSheet(),
            new ProductSheet(),
            new CategorySheet(),
            new UnitSheet(),
        ];
    }
}
