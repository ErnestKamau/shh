<?php

namespace App\Http\Controllers;

class ParameterGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.lab.parameter-groups.index');
    }
}
