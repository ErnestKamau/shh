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
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $condition = SampleCondition::create([
            'name' => $request->name,
            'active' => $request->active ?? 1,
        ]);

        return response()->json($condition, 201);
    }
}