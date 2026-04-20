<?php

namespace App\Http\Controllers\Lab\MethodValidation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataReviewAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.lab.method-validation.data-review-analysis');
    }
}

