<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RegistryApprovedRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('layouts.registry.approved.index');
    }
}
