<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Modules\Purchase\Exports\LaporanPembelianExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseExportController extends Controller
{
    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $fileName = 'laporan_pembelian_' . now()->format('Ymd_His') . '.xlsx';

        return (new LaporanPembelianExport($startDate, $endDate))
            ->download($fileName);
    }
}
