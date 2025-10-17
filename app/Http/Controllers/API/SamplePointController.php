<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\SamplePoint;
use Illuminate\Support\Facades\Validator;

class SamplePointController extends Controller
{
    /**
     * Display a listing of sample points.
     */
    public function index()
    {
        $points = SamplePoint::where('active', 1)
            ->with('crmCompanyUnit:id,name')
            ->select('id', 'name', 'crm_company_unit_id')
            ->orderBy('name')
            ->get();

        return response()->json($points);
    }

    /**
     * Store a newly created sample point.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'crm_company_unit_id' => 'required|exists:crm_company_units,id',
            'gps' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $point = SamplePoint::create([
            'name' => $request->name,
            'crm_company_unit_id' => $request->crm_company_unit_id,
            'gps' => $request->gps,
            'active' => 1,
        ]);

        return response()->json($point, 201);
    }
}