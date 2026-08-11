<?php

namespace App\Http\Controllers;

class TypeOfAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.lab.type-of-analysis.index');
    }
}
