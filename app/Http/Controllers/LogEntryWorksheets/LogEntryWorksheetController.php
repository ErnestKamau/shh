<?php

namespace App\Http\Controllers\LogEntryWorksheets;

use App\Http\Controllers\Controller;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;

class LogEntryWorksheetController extends Controller
{
    public function manage()
    {
        return view('formulars.log-entry-worksheets.manage');
    }

    public function edit(LogEntryWorksheet $logEntryWorksheet)
    {
        return view('formulars.log-entry-worksheets.edit', [
            'worksheet' => $logEntryWorksheet,
        ]);
    }
}
