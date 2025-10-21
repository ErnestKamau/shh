<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SampleCondition;
use Illuminate\Support\Facades\Validator;

class SampleConditionController extends Controller
{
    /**
     * Display a listing of sample conditions.
     */
    public function index()
    {
        $conditions = SampleCondition::where('active', 1)
            ->with('sampleType:id,name')
            ->select('id', 'name', 'sample_type_id')
            ->orderBy('name')
            ->get();

        return response()->json($conditions);
    }

    /**
     * Store a newly created sample condition.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sample_type_id' => 'required|exists:sample_types,id',
            'short_name' => 'nullable|string|max:255',
            'reporting_time' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $condition = SampleCondition::create([
            'name' => $request->name,
            'sample_type_id' => $request->sample_type_id,
            'short_name' => $request->short_name,
            'reporting_time' => $request->reporting_time,
            'active' => 1,
        ]);

        return response()->json($condition, 201);
    }
}