<?php

namespace App\Http\Controllers\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\RiskManagement\Risk;
use Illuminate\Http\Request;

class RiskDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.risk.dashboard');
    }
}


