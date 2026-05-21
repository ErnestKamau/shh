<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SampleSubmissionRequest;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Models\SubmissionFormInstance;
use App\Models\System\SystemConfiguration;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Zone;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SubmissionRequestController extends Controller
{
    public function __construct(
        private readonly AcceptanceFormPricingService $pricingService
    ) {}

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
            $parameters = $this->pricingService->legacyParametersWithPricing(
                $submissionRequestId ? (string) $submissionRequestId : null,
                $submissionFormInstanceId ? (string) $submissionFormInstanceId : null
            );

            if ($submissionRequestId && empty($parameters)) {
                return response()->json([
                    'error' => 'Submission request not found',
                    'parameters' => [],
                    'pricelist' => [],
                ], 404);
            }

            if ($submissionFormInstanceId && empty($parameters)) {
                $instanceExists = SubmissionFormInstance::query()->where('id', $submissionFormInstanceId)->exists();
                if (!$instanceExists) {
                    return response()->json([
                        'error' => 'Submission form instance not found',
                        'parameters' => [],
                        'pricelist' => [],
                    ], 404);
                }
            }

            $prefill = $this->pricingService->buildPrefillFromSelection(
                $submissionRequestId ? (string) $submissionRequestId : null,
                $submissionFormInstanceId ? (string) $submissionFormInstanceId : null
            );
            $pricelist = $prefill['pricelist'];

            return response()->json([
                'parameters' => $parameters,
                'pricelist' => $pricelist ? [
                    'id' => $pricelist->id,
                    'name' => $pricelist->name ?? 'Default',
                ] : [],
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
        $token = trim($token);

        if ($token === '' || (!ctype_digit($token) && !Str::isUuid($token))) {
            return $token;
        }

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
        $analysisId = trim($analysisId);

        if (!$pricelist || $analysisId === '' || !ctype_digit($analysisId)) {
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

    /**
     * Preview the batch code that would be generated for a portal form instance,
     * along with customer details for prefilling the lab acceptance form.
     * Does NOT persist anything.
     */
    public function previewBatchCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $formInstanceId = $request->input('submission_form_instance_id');
        $batchCode = $request->input('batch_code');

        // If a batch_code is supplied, just return it (already created batch)
        if ($batchCode) {
            $batch = SampleHeader::where('batch_code', $batchCode)->first();
            return response()->json([
                'lab_no' => $batchCode,
                'customer_name' => $batch?->client?->name ?? '',
                'email' => $batch?->client?->email ?? '',
                'tel' => $batch?->client?->telephone1 ?? '',
                'address' => $batch?->client?->postal_address ?? '',
            ]);
        }

        if (!$formInstanceId) {
            return response()->json(['lab_no' => '', 'customer_name' => '', 'email' => '', 'tel' => '', 'address' => ''], 200);
        }

        $formInstance = SubmissionFormInstance::query()
            ->with(['crmCustomer', 'submissionForm.sampleTypes', 'batches'])
            ->find((string) $formInstanceId);

        if (!$formInstance) {
            return response()->json(['lab_no' => '', 'customer_name' => '', 'email' => '', 'tel' => '', 'address' => ''], 200);
        }

        // If a batch already exists, return its code
        if ($formInstance->batches->isNotEmpty()) {
            $existingBatch = $formInstance->batches->first();
            return response()->json([
                'lab_no' => (string) $existingBatch->batch_code,
                'customer_name' => $formInstance->crmCustomer?->name ?? '',
                'email' => $formInstance->crmCustomer?->email ?? '',
                'tel' => $formInstance->crmCustomer?->telephone1 ?? '',
                'address' => $formInstance->crmCustomer?->postal_address ?? '',
            ]);
        }

        $customer = $formInstance->crmCustomer;
        if (!$customer) {
            return response()->json(['lab_no' => '', 'customer_name' => '', 'email' => '', 'tel' => '', 'address' => ''], 200);
        }

        $zoneCode = $this->resolveZoneCodeForPreview($formInstance) ?? 'XX';
        $previewCode = $this->generateZoneYearPreviewCode($zoneCode);

        return response()->json([
            'lab_no' => $previewCode,
            'customer_name' => $customer->name ?? '',
            'email' => $customer->email ?? '',
            'tel' => $customer->telephone1 ?? '',
            'address' => $customer->postal_address ?? '',
        ]);
    }

    private function resolveZoneCodeForPreview(SubmissionFormInstance $instance): ?string
    {
        $linkedRequest = $this->resolveLinkedSubmissionRequestFromFormInstance($instance);
        if ($linkedRequest) {
            $zoneCode = $this->normalizeZoneCode($linkedRequest->getAttribute('zone_code'))
                ?? $this->resolveZoneCodeFromZoneId($linkedRequest->getAttribute('zone_id'))
                ?? $this->normalizeZoneCode($linkedRequest->getAttribute('zone'));

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        $directZoneCode = $this->normalizeZoneCode($instance->getAttribute('zone_code'))
            ?? $this->resolveZoneCodeFromZoneId($instance->getAttribute('zone_id'))
            ?? $this->resolveZoneCodeFromFormValues($instance)
            ?? $this->resolveZoneCodeFromSubmittingUser($instance);

        return $directZoneCode;
    }

    private function resolveZoneCodeFromFormValues(SubmissionFormInstance $instance): ?string
    {
        $values = $instance->values()->with('element')->get();

        foreach ($values as $value) {
            $element = $value->element;
            if (! $element) {
                continue;
            }

            $name = Str::lower(trim(((string) $element->name) . ' ' . ((string) $element->label) . ' ' . ((string) $element->mapping_field)));
            if (! Str::contains($name, ['zone', 'submission zone'])) {
                continue;
            }

            $zoneCode = $this->normalizeZoneCode($value->value)
                ?? $this->resolveZoneCodeFromZoneId($value->value);

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        return null;
    }

    private function resolveZoneCodeFromSubmittingUser(SubmissionFormInstance $instance): ?string
    {
        if (empty($instance->submitted_by)) {
            return null;
        }

        $user = User::query()->select('id', 'zone_id')->find((string) $instance->submitted_by);
        if (! $user) {
            return null;
        }

        return $this->resolveZoneCodeFromZoneId($user->zone_id);
    }

    private function resolveZoneCodeFromZoneId($zoneId): ?string
    {
        if (empty($zoneId)) {
            return null;
        }

        $zone = Zone::query()->select('id', 'key')->find((string) $zoneId);
        if (! $zone) {
            return null;
        }

        return $this->normalizeZoneCode($zone->key);
    }

    private function normalizeZoneCode($zone): ?string
    {
        $value = strtoupper(trim((string) $zone));
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^A-Z0-9]/', '', $value);
        return $normalized !== '' ? $normalized : null;
    }

    private function generateZoneYearPreviewCode(string $zoneCode): string
    {
        $yy = date('y');
        $prefix = strtoupper($zoneCode) . $yy . '-';

        $existingCodes = SampleHeader::query()
            ->where('batch_code', 'like', $prefix . '%')
            ->pluck('batch_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            $code = (string) $code;
            if (!str_starts_with($code, $prefix)) {
                continue;
            }

            $suffix = substr($code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        return $prefix . sprintf('%05d', $maxSeq + 1);
    }
}
