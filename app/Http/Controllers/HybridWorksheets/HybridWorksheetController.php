<?php

namespace App\Http\Controllers\HybridWorksheets;

use App\Http\Controllers\Controller;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\HybridWorksheets\HybridWorksheetVersion;

class HybridWorksheetController extends Controller
{
    public function manage()
    {
        return view('formulars.hybrid-worksheets.manage');
    }

    public function edit(HybridWorksheet $hybridWorksheet, HybridWorksheetVersion $hybridWorksheetVersion)
    {
        if ($hybridWorksheetVersion->hybrid_worksheet_id !== $hybridWorksheet->id) {
            abort(404);
        }

        $hybridWorksheetVersion->load(['hybridWorksheet', 'blocks']);

        return view('formulars.hybrid-worksheets.edit', [
            'hybridWorksheet' => $hybridWorksheet,
            'version' => $hybridWorksheetVersion,
        ]);
    }
}
