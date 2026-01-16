<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SerWorksheetStepsController extends Controller
{
    public function index()
    {
        return view('lab.ser-worksheet-steps.index');
    }
}
