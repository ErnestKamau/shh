<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Country;
use Illuminate\Support\Facades\Cache;

class CountryController extends Controller
{
    /**
     * Display a listing of active countries.
     */
    public function index()
    {
        $countries = Cache::remember('api_countries_list', 3600, function() {
            return Country::where('status', 1)
                ->select('id', 'name', 'iso_code_2 as code')
                ->orderBy('name')
                ->get();
        });

        return response()->json($countries);
    }
}

