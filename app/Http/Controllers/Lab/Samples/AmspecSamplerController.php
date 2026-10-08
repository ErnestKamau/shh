<?php

namespace App\Http\Controllers\Lab\Samples;

use App\Http\Controllers\Controller;
use App\Models\AmspecSampler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Typeahead for the TRF "Submit & sign" sampler fields (Name + Employee ID).
 */
class AmspecSamplerController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $samplers = AmspecSampler::query()
            ->search($term)
            ->orderByDesc('use_count')
            ->orderByDesc('last_used_at')
            ->limit(8)
            ->get(['id', 'name', 'employee_id']);

        return response()->json($samplers);
    }
}
