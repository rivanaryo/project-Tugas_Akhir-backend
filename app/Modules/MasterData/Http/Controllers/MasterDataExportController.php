<?php

namespace App\Modules\MasterData\Http\Controllers;

use App\Modules\MasterData\Exports\MasterDataExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterDataExportController extends Controller
{
    public function export(Request $request): BinaryFileResponse
    {
        $fileName = 'master_data_' . now()->format('Ymd_His') . '.xlsx';

        return (new MasterDataExport())
            ->download($fileName);
    }
}
