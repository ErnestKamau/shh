<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SubmissionRequestController extends Controller
{
    /**
     * Get parameters and pricing for a submission request.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParametersWithPricing(Request $request)
    {
        $submissionRequestId = $request->input('submission_request_id');
        $submissionFormInstanceId = $request->input('submission_form_instance_id');
        
        if (!$submissionRequestId && !$submissionFormInstanceId) {
            return response()->json([
                'error' => 'Missing submission_request_id or submission_form_instance_id',
                'parameters' => [],
                'pricelist' => []
            ], 400);
        }

        try {
            $parameters = [];
            $customerId = null;
            $sampleTypeId = null;

            if ($submissionRequestId) {
                $submissionRequest = SampleSubmissionRequest::with('batch')
                    ->find($submissionRequestId);

                if (!$submissionRequest) {
                    return response()->json([
                        'error' => 'Submission request not found',
                        'parameters' => [],
                        'pricelist' => []
                    ], 404);
                }

                $requestedAnalyses = $submissionRequest->requestedAnalyses()
                    ->select('analysis_key', 'analysis_label')
                    ->get();

                $customerId = (string) $submissionRequest->crm_customer_id;
                $sampleTypeId = $this->resolveSampleTypeFromSubmissionRequest($submissionRequest);

                foreach ($requestedAnalyses as $analysis) {
                    $analysisId = (string) $analysis->analysis_key;
                    $parameters[] = [
                        'analysis_id' => $analysisId,
                        'name' => $analysis->analysis_label,
                        'label' => $analysis->analysis_label,
                        'price' => 0,
                    ];
                }
            } else {
                $instance = SubmissionFormInstance::with([
                    'submissionForm.sampleTypes:id',
                    'values.element:id,name,label,element_type,mapping_field',
                ])->find((string) $submissionFormInstanceId);

                if (!$instance) {
                    return response()->json([
                        'error' => 'Submission form instance not found',
                        'parameters' => [],
                        'pricelist' => []
                    ], 404);
                }

                $customerId = (string) $instance->crm_customer_id;
                $sampleTypeId = $this->resolveSampleTypeFromFormInstance($instance);
                $parameters = $this->extractParametersFromFormInstance($instance);

                if (empty($parameters)) {
                    $linkedSubmissionRequest = $this->resolveLinkedSubmissionRequestFromFormInstance($instance);
                    if ($linkedSubmissionRequest) {
                        $requestedAnalyses = $linkedSubmissionRequest->requestedAnalyses()
                            ->select('analysis_key', 'analysis_label')
                            ->get();

                        foreach ($requestedAnalyses as $analysis) {
                            $analysisId = (string) $analysis->analysis_key;
                            $parameters[] = [
                                'analysis_id' => $analysisId,
                                'name' => $analysis->analysis_label,
                                'label' => $analysis->analysis_label,
                                'price' => 0,
                            ];
                        }

                        if (empty($customerId)) {
                            $customerId = (string) $linkedSubmissionRequest->crm_customer_id;
                        }
                        if (empty($sampleTypeId)) {
                            $sampleTypeId = $this->resolveSampleTypeFromSubmissionRequest($linkedSubmissionRequest);
                        }
                    }
                }
            }

            if (empty($parameters)) {
                return response()->json([
                    'parameters' => [],
                    'pricelist' => []
                ]);
            }

            $pricelist = $this->resolvePricelist($customerId);

            foreach ($parameters as &$parameter) {
                $parameter['price'] = $this->resolveParameterPrice(
                    $pricelist,
                    (string) ($parameter['analysis_id'] ?? ''),
                    $sampleTypeId
                );
            }
            unset($parameter);

            return response()->json([
                'parameters' => $parameters,
                'pricelist' => $pricelist ? [
                    'id' => $pricelist->id,
                    'name' => $pricelist->name ?? 'Default'
                ] : []
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching parameters for submission request: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Internal server error',
                'parameters' => [],
                'pricelist' => []
            ], 500);
        }
    }

    private function resolveSampleTypeFromSubmissionRequest(SampleSubmissionRequest $submissionRequest): ?string
    {
        if ($submissionRequest->batch && isset($submissionRequest->batch->sample_type_id)) {
            return (string) $submissionRequest->batch->sample_type_id;
        }

        if ($submissionRequest->batch) {
            $firstSample = SampleDetails::where('sample_header_id', $submissionRequest->batch->id)
                ->select('sample_point_id')
                ->first();

            if ($firstSample && isset($firstSample->sample_point_id)) {
                return (string) $firstSample->sample_point_id;
            }
        }

        return null;
    }

    private function resolveSampleTypeFromFormInstance(SubmissionFormInstance $instance): ?string
    {
        $sampleTypeToken = $instance->values
            ->filter(function ($value) {
                $element = $value->element;
                if (!$element) {
                    return false;
                }

                $elementType = (string) ($element->element_type ?? '');
                $mappingField = (string) ($element->mapping_field ?? '');

                return $elementType === 'sample_type_select' || $mappingField === 'sample_type_id';
            })
            ->flatMap(fn ($value) => $this->extractValueTokens((string) $value->value))
            ->first();

        if ($sampleTypeToken !== null && ctype_digit((string) $sampleTypeToken)) {
            return (string) $sampleTypeToken;
        }

        $fallbackSampleType = $instance->submissionForm?->sampleTypes?->first();
        if ($fallbackSampleType && isset($fallbackSampleType->id)) {
            return (string) $fallbackSampleType->id;
        }

        return null;
    }

    private function extractParametersFromFormInstance(SubmissionFormInstance $instance): array
    {
        $rows = $instance->values
            ->filter(function ($value) {
                $element = $value->element;
                if (!$element) {
                    return false;
                }

                $elementType = (string) ($element->element_type ?? '');
                $mappingField = (string) ($element->mapping_field ?? '');
                $elementName = Str::lower(trim((string) $element->name . ' ' . (string) $element->label));

                return $elementType === 'analysis_elements_select'
                    || in_array($mappingField, ['analysis_element_id', 'analyte_id'], true)
                    || Str::contains($elementName, ['test required', 'tests required', 'parameter', 'analysis']);
            })
            ->flatMap(fn ($value) => $this->extractValueTokens((string) $value->value))
            ->map(function ($token) {
                $token = trim((string) $token);
                if ($token === '') {
                    return null;
                }

                $analysisId = ctype_digit($token) ? (int) $token : 0;
                $label = $this->resolveParameterLabel($token);

                return [
                    'analysis_id' => (string) $token,
                    'name' => $label,
                    'label' => $label,
                    'price' => 0,
                ];
            })
            ->filter()
            ->unique('label')
            ->values()
            ->all();

        return $rows;
    }

    private function resolveParameterLabel(string $token): string
    {
        $analyte = DB::table('analytes')->where('id', $token)->value('name');
        if (!empty($analyte)) {
            return (string) $analyte;
        }

        $analysisElement = DB::table('analysis_elements')
            ->leftJoin('analytes', 'analytes.id', '=', 'analysis_elements.analyte_id')
            ->where('analysis_elements.id', $token)
            ->select('analysis_elements.method as analysis_element_name', 'analytes.name as analyte_name')
            ->first();

        if ($analysisElement) {
            return (string) ($analysisElement->analyte_name ?: $analysisElement->analysis_element_name ?: $token);
        }

        return $token;
    }

    private function extractValueTokens(string $rawValue): array
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return [];
        }

        $tokens = [];
        $decoded = json_decode($rawValue, true);

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $tokens = array_merge($tokens, array_map('trim', explode(',', $item)));
                    continue;
                }

                if (is_array($item)) {
                    foreach (['value', 'id', 'uuid'] as $key) {
                        if (!empty($item[$key]) && is_string($item[$key])) {
                            $tokens = array_merge($tokens, array_map('trim', explode(',', $item[$key])));
                            break;
                        }
                    }
                }
            }
        } else {
            $tokens = array_map('trim', explode(',', $rawValue));
        }

        return array_values(array_filter($tokens, static fn ($token) => $token !== ''));
    }

    private function resolveLinkedSubmissionRequestFromFormInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        $candidateIds = collect([
            $instance->portal_request_id,
            $instance->target_record_id,
        ])
            ->filter(fn ($value) => !empty($value))
            ->map(fn ($value) => (string) $value)
            ->values();

        if ($candidateIds->isEmpty()) {
            return null;
        }

        foreach ($candidateIds as $candidateId) {
            $query = SampleSubmissionRequest::query()->where('id', $candidateId);

            if (!empty($instance->crm_customer_id)) {
                $query->where('crm_customer_id', $instance->crm_customer_id);
            }

            $submissionRequest = $query->first();
            if ($submissionRequest) {
                return $submissionRequest;
            }
        }

        return null;
    }

    private function resolvePricelist(?string $customerId): ?Pricelist
    {
        if (!empty($customerId)) {
            $pricelistCustomer = PricelistCustomer::where('customer_id', $customerId)
                ->select('pricelist_id')
                ->first();

            if ($pricelistCustomer) {
                $pricelist = Pricelist::find($pricelistCustomer->pricelist_id);
                if ($pricelist) {
                    return $pricelist;
                }
            }
        }

        return Pricelist::where('active', 1)
            ->orderBy('id')
            ->first();
    }

    private function resolveParameterPrice(?Pricelist $pricelist, string $analysisId, ?string $sampleTypeId): float
    {
        if (!$pricelist || trim($analysisId) === '') {
            return 0.0;
        }

        $priceQuery = $pricelist->items()
            ->where(function ($query) use ($analysisId) {
                $query->where('analysis_id', $analysisId)
                    ->orWhere('analysis_element_id', $analysisId);
            })
            ->where('active', 1);

        if (!empty($sampleTypeId)) {
            $priceQuery->where('sample_type_id', $sampleTypeId);
        }

        $pricelistItem = $priceQuery->select('selling_price')->first();

        return $pricelistItem ? (float) $pricelistItem->selling_price : 0.0;
    }
}
