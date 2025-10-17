<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SampleType;

class SampleTypeController extends Controller
{
    /**
     * Display a listing of sample types.
     */
    public function index()
    {
        $types = SampleType::where('active', 1)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }
}