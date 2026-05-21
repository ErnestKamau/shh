<?php

namespace App\Http\Controllers\GroupedWorksheets;

use App\Http\Controllers\Controller;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;

class GroupedWorksheetHolderController extends Controller
{
    public function manage()
    {
        return view('formulars.grouped-worksheets.manage');
    }

    public function edit(GroupedWorksheetHolder $groupedWorksheetHolder)
    {
        return view('formulars.grouped-worksheets.edit', [
            'holder' => $groupedWorksheetHolder,
        ]);
    }
}
