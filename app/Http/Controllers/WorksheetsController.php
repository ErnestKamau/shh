<?php

namespace App\Http\Controllers;

use App\SampleHeader;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorksheetsController extends Controller
{
    public function index(int $batchId): View
    {
        $batch = SampleHeader::findOrFail($batchId);
        
        return view('worksheets.index', [
            'batch' => $batch,
        ]);
    }
}
