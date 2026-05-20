<?php

namespace App\Http\Controllers\Sampleworkflow;

use App\Http\Controllers\Controller;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcceptanceFormController extends Controller
{
    public function __construct(
        private readonly AcceptanceFormPricingService $pricingService
    ) {}

    public function prefill(Request $request): JsonResponse
    {
        $submissionRequestId = $request->input('submission_request_id');
        $submissionFormInstanceId = $request->input('submission_form_instance_id');

        if (!$submissionRequestId && !$submissionFormInstanceId) {
            return response()->json(['message' => 'submission_request_id or submission_form_instance_id is required.'], 422);
        }

        $prefill = $this->pricingService->buildPrefillFromSelection(
            $submissionRequestId ? (string) $submissionRequestId : null,
            $submissionFormInstanceId ? (string) $submissionFormInstanceId : null
        );

        $pricelist = $prefill['pricelist'];

        return response()->json([
            'header' => [
                'customer_name' => $prefill['customer_name'],
                'request_date' => $prefill['request_date'],
                'number_of_samples' => $prefill['number_of_samples'],
                'mode_of_work' => $prefill['mode_of_work'],
                'date_of_sampling' => $prefill['date_of_sampling'],
                'crm_customer_id' => $prefill['customer_id'],
            ],
            'lines' => $prefill['lines'],
            'pricelist' => $pricelist ? [
                'id' => $pricelist->id,
                'name' => $pricelist->name ?? 'Default',
                'currency_id' => $pricelist->currency_id,
            ] : null,
        ]);
    }
}
