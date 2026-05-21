<?php

namespace App\Http\Controllers\Lab;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Http\Controllers\Controller;
use App\StandardAnalytes;
use Illuminate\Http\JsonResponse;

class SolutionPreparationAjaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function analysisTypes(string $sampleTypeId): JsonResponse
    {
        $types = AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($types);
    }

    public function analytes(string $analysisTypeId): JsonResponse
    {
        $elements = AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->with('analyte:id,name,code')
            ->get();

        $analytes = $elements->map(fn ($el) => [
            'id' => $el->analyte_id,
            'name' => $el->analyte?->name ?? $el->analyte_id,
            'code' => $el->analyte?->code,
            'element_id' => $el->id,
        ])->unique('id')->values();

        return response()->json($analytes);
    }

    public function standardLimits(string $analyteId, string $standardId): JsonResponse
    {
        $row = StandardAnalytes::query()
            ->where('analyte_id', $analyteId)
            ->where('standard_id', $standardId)
            ->first();

        return response()->json([
            'standard_limit' => $row?->low ?? $row?->standard_limit ?? null,
            'standard_value' => $row?->standard_is_value ?? $row?->standard_value ?? null,
        ]);
    }
}
