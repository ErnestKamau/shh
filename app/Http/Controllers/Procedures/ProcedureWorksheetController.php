<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Procedures\ProcedureWorksheet;
use Illuminate\Http\Request;

class ProcedureWorksheetController extends Controller
{
    public function manage()
    {
        return view('procedures.manage');
    }

    public function edit(ProcedureWorksheet $procedureWorksheet)
    {
        return view('procedures.edit', compact('procedureWorksheet'));
    }
}
