<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;

class RegistryDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.registry.dashboard');
    }
}
