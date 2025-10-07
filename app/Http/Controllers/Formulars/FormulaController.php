<?php

namespace App\Http\Controllers\Formulars;

use App\Http\Controllers\Controller;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaVersion;
use Illuminate\Http\Request;

class FormulaController extends Controller
{
    /**
     * Display the formulas index page.
     */
    public function index()
    {
        return view('formulars.index');
    }

    /**
     * Display the formula management page.
     */
    public function manage()
    {
        return view('formulars.manage');
    }

    /**
     * Display the formula steps editor.
     */
    public function steps(FormulaVersion $formulaVersion)
    {
        return view('formulars.steps', compact('formulaVersion'));
    }

    /**
     * Display the worksheet executor.
     */
    public function execute(FormulaVersion $formulaVersion, Request $request)
    {
        $executionMode = $request->get('mode', 'standalone');
        $sampleId = $request->get('sample_id');
        $batchId = $request->get('batch_id');

        return view('formulars.execute', compact('formulaVersion', 'executionMode', 'sampleId', 'batchId'));
    }

    /**
     * Display the worksheet history.
     */
    public function history()
    {
        return view('formulars.history');
    }

    /**
     * Display global variables management.
     */
    public function globalVariables()
    {
        return view('formulars.global-variables');
    }

    /**
     * Display lookup tables management.
     */
    public function lookupTables()
    {
        return view('formulars.lookup-tables');
    }
}
