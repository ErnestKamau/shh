<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestFormInstance;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use App\SampleDetails;
use App\SampleType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcceptanceFormPricingService
{
    /**
     * @return array{
     *     lines: list<array<string, mixed>>,
     *     customer_id: ?string,
     *     sample_type_id: ?string,
     *     pricelist: ?Pricelist,
     *     customer_name: string,
     *     request_date: ?string,
     *     number_of_samples: int,
     *     mode_of_work: string,
     *     date_of_sampling: ?string
     * }
     */
    public function buildPrefillFromSelection(?string $submissionRequestId, ?string $submissionFormInstanceId): array
    {
        $enquiry = $this->resolveEnquiryForPrefill($submissionRequestId, $submissionFormInstanceId);
        if ($enquiry !== null) {
            $quotationPrefill = $this->buildPrefillFromAcceptedQuotation($enquiry);
            if ($quotationPrefill !== null) {
                return $quotationPrefill;
            }
        }

        $parameters = [];
        $customerId = null;
        $sampleTypeId = null;
        $customerName = '';
        $requestDate = null;
        $numberOfSamples = 1;
        $modeOfWork = 'Normal';
        $dateOfSampling = null;

        if ($submissionRequestId) {
            $submissionRequest = SampleSubmissionRequest::with(['batch', 'customer'])->find($submissionRequestId);
            if (!$submissionRequest) {
                return $this->emptyPrefill();
            }

            $customerId = (string) $submissionRequest->crm_customer_id;
            $customerName = (string) ($submissionRequest->customer?->name ?? '');
            $sampleTypeId = $this->resolveSampleTypeFromSubmissionRequest($submissionRequest);
            $requestDate = optional($submissionRequest->created_at)->format('Y-m-d');
            $dateOfSampling = optional($submissionRequest->date_of_seizure)->format('Y-m-d');

            foreach ($submissionRequest->requestedAnalyses()->select('analysis_key', 'analysis_label')->get() as $analysis) {
                $parameters[] = $this->tokenToLineSeed((string) $analysis->analysis_key, (string) $analysis->analysis_label, $sampleTypeId);
            }

            if ($parameters === []) {
                $instance = $submissionRequest->resolveLinkedFormInstance();
                if ($instance !== null) {
                    $lineService = app(SubmissionRequestSampleLineService::class);
                    $instanceLines = $lineService->linesForInstance($instance);
                    $parameters = $this->parameterSeedsFromInstanceLines($instanceLines, $sampleTypeId);
                    if ($parameters !== []) {
                        $numberOfSamples = max(1, count($instanceLines));
                    }
                }
            } elseif ((int) $submissionRequest->number_of_samples > 0) {
                $numberOfSamples = max(1, (int) $submissionRequest->number_of_samples);
            }
        } elseif ($submissionFormInstanceId) {
            $instance = SubmissionFormInstance::with([
                'crmCustomer',
                'submissionForm.sampleTypes:id,name',
                'values.element:id,name,label,element_type,mapping_field',
                'batches',
            ])->find((string) $submissionFormInstanceId);

            if (!$instance) {
                return $this->emptyPrefill();
            }

            $customerId = (string) $instance->crm_customer_id;
            $customerName = (string) ($instance->crmCustomer?->name ?? '');
            $sampleTypeId = $this->resolveSampleTypeFromFormInstance($instance);
            $requestDate = optional($instance->submitted_at ?? $instance->created_at)->format('Y-m-d');
            $modeOfWork = strtolower((string) ($instance->priority ?? '')) === 'express' ? 'Express' : 'Normal';
            $sampleLineSeeds = app(SubmissionRequestSampleLineService::class)->seedsForAcceptancePrefill($instance);
            if ($sampleLineSeeds !== []) {
                $numberOfSamples = max(1, count($sampleLineSeeds));
                foreach ($sampleLineSeeds as $seed) {
                    $parameters[] = array_merge(
                        $this->tokenToLineSeed(
                            (string) ($seed['analysis_element_id'] ?? $seed['analysis_type_id'] ?? ''),
                            (string) ($seed['parameter_label'] ?? 'Parameter'),
                            $seed['sample_type_id'] ?? $sampleTypeId
                        ),
                        [
                            'sample_type_id' => $seed['sample_type_id'] ?? $sampleTypeId,
                            'analysis_type_id' => (string) ($seed['analysis_type_id'] ?? ''),
                            'analysis_element_id' => $seed['analysis_element_id'] ?? null,
                            'parameter_label' => (string) ($seed['parameter_label'] ?? 'Parameter'),
                        ]
                    );
                }
            } else {
                $rawParameters = $this->extractParametersFromFormInstance($instance);
                foreach ($rawParameters as $param) {
                    $parameters[] = $this->tokenToLineSeed(
                        (string) ($param['analysis_id'] ?? ''),
                        (string) ($param['label'] ?? ''),
                        $sampleTypeId
                    );
                }
            }

            if (empty($parameters)) {
                $linkedSubmissionRequest = $this->resolveLinkedSubmissionRequestFromFormInstance($instance);
                if ($linkedSubmissionRequest) {
                    if (empty($customerId)) {
                        $customerId = (string) $linkedSubmissionRequest->crm_customer_id;
                    }
                    if (empty($sampleTypeId)) {
                        $sampleTypeId = $this->resolveSampleTypeFromSubmissionRequest($linkedSubmissionRequest);
                    }
                    foreach ($linkedSubmissionRequest->requestedAnalyses()->select('analysis_key', 'analysis_label')->get() as $analysis) {
                        $parameters[] = $this->tokenToLineSeed(
                            (string) $analysis->analysis_key,
                            (string) $analysis->analysis_label,
                            $sampleTypeId
                        );
                    }
                }
            }
        } else {
            return $this->emptyPrefill();
        }

        $pricelist = $this->resolvePricelist($customerId);
        $lines = [];

        foreach (array_values($parameters) as $index => $seed) {
            $unitAmount = $this->resolveLinePrice(
                $pricelist,
                (string) ($seed['sample_type_id'] ?? $sampleTypeId),
                (string) ($seed['analysis_type_id'] ?? ''),
                $seed['analysis_element_id'] ?? null
            );

            $lines[] = array_merge($seed, [
                'line_no' => $index + 1,
                'sort_order' => $index,
                'unit_amount' => $unitAmount,
                'number_of_samples' => 1,
                'is_approved' => true,
            ]);
        }

        $lines = $this->expandAnalysisTypeOnlyLinesToParameters($lines, $customerId, $pricelist);
        $lines = $this->deduplicateRedundantAnalysisTypeLines($lines);

        return [
            'lines' => $lines,
            'customer_id' => $customerId,
            'sample_type_id' => $sampleTypeId,
            'pricelist' => $pricelist,
            'customer_name' => $customerName,
            'request_date' => $requestDate,
            'number_of_samples' => $numberOfSamples,
            'mode_of_work' => $modeOfWork,
            'date_of_sampling' => $dateOfSampling,
        ];
    }

    public function resolvePricelist(?string $customerId): ?Pricelist
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

        return Pricelist::query()
            ->where('active', 1)
            ->where('is_master', 1)
            ->first()
            ?? Pricelist::query()->where('active', 1)->orderBy('id')->first();
    }

    /**
     * Returns true only when the customer has an explicit pricelist assignment.
     * Does NOT fall back to the global active pricelist.
     */
    public function hasCustomerAssignedPricelist(string $customerId): bool
    {
        return $this->resolveCustomerAssignedPricelist($customerId) !== null;
    }

    /**
     * Resolves the pricelist explicitly assigned to a customer.
     * Returns null when no assignment exists — no global fallback.
     */
    public function resolveCustomerAssignedPricelist(?string $customerId): ?Pricelist
    {
        if (empty($customerId)) {
            return null;
        }

        $pricelistCustomer = PricelistCustomer::where('customer_id', $customerId)
            ->select('pricelist_id')
            ->first();

        if (!$pricelistCustomer) {
            return null;
        }

        return Pricelist::find($pricelistCustomer->pricelist_id);
    }

    /**
     * Remove analysis-type-only rows when element-level rows exist for the same analysis type.
     * The analysis type is already represented by the grouped header in the acceptance form UI.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function deduplicateRedundantAnalysisTypeLines(array $lines): array
    {
        $analysisTypesWithElements = collect($lines)
            ->filter(fn (array $line) => !empty($line['analysis_element_id']))
            ->pluck('analysis_type_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->all();

        $filtered = array_values(array_filter(
            $lines,
            function (array $line) use ($analysisTypesWithElements): bool {
                if (!empty($line['analysis_element_id'])) {
                    return true;
                }

                $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

                return $analysisTypeId === ''
                    || !in_array($analysisTypeId, $analysisTypesWithElements, true);
            }
        ));

        foreach ($filtered as $index => &$line) {
            $line['line_no'] = $index + 1;
            $line['sort_order'] = $index;
        }
        unset($line);

        return $filtered;
    }

    /**
     * When a request only specifies sample type + analysis type, expand to all parameters (elements) for that analysis.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function expandAnalysisTypeOnlyLinesToParameters(
        array $lines,
        ?string $customerId,
        ?Pricelist $pricelist = null
    ): array {
        if ($lines === []) {
            return [];
        }

        $pricelist ??= $this->resolvePricelist($customerId);
        $expanded = [];
        $expandedKeys = [];

        foreach ($lines as $line) {
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

            if (!empty($line['analysis_element_id']) || $analysisTypeId === '') {
                $expanded[] = $line;

                continue;
            }

            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
            $expandKey = $sampleTypeId . '::' . $analysisTypeId;

            if (isset($expandedKeys[$expandKey])) {
                continue;
            }

            $parameters = $this->parametersForAddLineSelection(
                (string) ($customerId ?? ''),
                $sampleTypeId ?: null,
                $analysisTypeId
            );

            if ($parameters === []) {
                $expanded[] = $line;

                continue;
            }

            $expandedKeys[$expandKey] = true;

            foreach ($parameters as $parameter) {
                $resolvedSampleTypeId = (string) ($parameter['sample_type_id'] ?? $sampleTypeId);
                $resolvedAnalysisTypeId = (string) ($parameter['analysis_type_id'] ?? $analysisTypeId);
                $elementId = $parameter['analysis_element_id'] ?? null;

                $expanded[] = array_merge($line, [
                    'sample_type_id' => $resolvedSampleTypeId !== '' ? $resolvedSampleTypeId : null,
                    'analysis_type_id' => $resolvedAnalysisTypeId,
                    'analysis_element_id' => $elementId,
                    'parameter_label' => (string) ($parameter['label'] ?? $line['parameter_label'] ?? 'Parameter'),
                    'unit_amount' => (float) ($parameter['unit_amount'] ?? $this->resolveLinePrice(
                        $pricelist,
                        $resolvedSampleTypeId ?: null,
                        $resolvedAnalysisTypeId,
                        $elementId
                    )),
                    'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    'is_approved' => (bool) ($line['is_approved'] ?? true),
                ]);
            }
        }

        foreach ($expanded as $index => &$line) {
            $line['line_no'] = $index + 1;
            $line['sort_order'] = $index;
        }
        unset($line);

        return $expanded;
    }

    public function resolveLinePrice(
        ?Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null
    ): float {
        if (!$pricelist) {
            return 0.0;
        }

        $query = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1);

        if (!empty($sampleTypeId)) {
            $query->where('sample_type_id', $sampleTypeId);
        }

        if (!empty($analysisElementId)) {
            $item = (clone $query)->where('analysis_element_id', $analysisElementId)->first();
            if ($item) {
                return (float) $item->selling_price;
            }
        }

        if ($analysisTypeId !== '') {
            $item = (clone $query)->where('analysis_id', $analysisTypeId)->first();
            if ($item) {
                return (float) $item->selling_price;
            }
        }

        return 0.0;
    }

    /**
     * @return list<array{id: string, label: string, analysis_type_id: string, sample_type_id: ?string}>
     */
    public function pricelistParametersForCustomer(
        string $customerId,
        ?string $sampleTypeId = null,
        ?string $analysisTypeId = null
    ): array {
        $pricelist = $this->resolvePricelist($customerId);
        if (!$pricelist) {
            return [];
        }

        $query = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->with(['sampleType:id,name', 'analysisType:id,name', 'analysisElement.analyte:id,name']);

        if ($sampleTypeId) {
            $query->where('sample_type_id', $sampleTypeId);
        }

        if ($analysisTypeId) {
            $query->where('analysis_id', $analysisTypeId);
        }

        return $query->get()->map(function (PricelistItem $item) {
            $label = $item->analysisElement?->analyte?->name
                ?? $item->analysisType?->name
                ?? 'Parameter';

            return [
                'id' => (string) ($item->analysis_element_id ?: $item->analysis_id),
                'pricelist_item_id' => (string) $item->id,
                'sample_type_id' => $item->sample_type_id ? (string) $item->sample_type_id : null,
                'sample_type_name' => $item->sampleType?->name,
                'analysis_type_id' => $item->analysis_id ? (string) $item->analysis_id : null,
                'analysis_type_name' => $item->analysisType?->name,
                'analysis_element_id' => $item->analysis_element_id ? (string) $item->analysis_element_id : null,
                'label' => $label,
                'unit_amount' => (float) $item->selling_price,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function pricelistSampleTypesForCustomer(string $customerId): array
    {
        $pricelist = $this->resolvePricelist($customerId);
        if (!$pricelist) {
            return [];
        }

        $typeIds = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->whereNotNull('sample_type_id')
            ->distinct()
            ->pluck('sample_type_id');

        return SampleType::query()
            ->whereIn('id', $typeIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (SampleType $type) => ['id' => (string) $type->id, 'name' => (string) $type->name])
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function pricelistAnalysisTypesForCustomer(string $customerId, ?string $sampleTypeId = null): array
    {
        $pricelist = $this->resolvePricelist($customerId);
        if (!$pricelist) {
            return [];
        }

        $query = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->whereNotNull('analysis_id');

        if ($sampleTypeId) {
            $query->where('sample_type_id', $sampleTypeId);
        }

        $analysisIds = $query->distinct()->pluck('analysis_id');

        return AnalysisType::query()
            ->whereIn('id', $analysisIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AnalysisType $type) => ['id' => (string) $type->id, 'name' => (string) $type->name])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenToLineSeed(string $token, string $label, ?string $defaultSampleTypeId): array
    {
        $token = trim($token);
        $resolved = $this->resolveIdsFromToken($token);

        return [
            'sample_type_id' => $resolved['sample_type_id'] ?? $defaultSampleTypeId,
            'analysis_type_id' => $resolved['analysis_type_id'],
            'analysis_element_id' => $resolved['analysis_element_id'],
            'parameter_label' => $label !== '' ? $label : $this->resolveParameterLabel($token),
        ];
    }

    /**
     * @return array{analysis_type_id: ?string, analysis_element_id: ?string, sample_type_id: ?string}
     */
    private function resolveIdsFromToken(string $token): array
    {
        if ($token === '') {
            return ['analysis_type_id' => null, 'analysis_element_id' => null, 'sample_type_id' => null];
        }

        if (Str::isUuid($token)) {
            $element = AnalysisElements::query()->find($token);
            if ($element) {
                $analysisType = AnalysisType::query()->find($element->analysis_type_id);

                return [
                    'analysis_type_id' => (string) $element->analysis_type_id,
                    'analysis_element_id' => (string) $element->id,
                    'sample_type_id' => $analysisType?->sample_type_id ? (string) $analysisType->sample_type_id : null,
                ];
            }

            $analysisType = AnalysisType::query()->find($token);
            if ($analysisType) {
                return [
                    'analysis_type_id' => (string) $analysisType->id,
                    'analysis_element_id' => null,
                    'sample_type_id' => $analysisType->sample_type_id ? (string) $analysisType->sample_type_id : null,
                ];
            }
        }

        $elementByName = AnalysisElements::query()
            ->whereHas('analyte', fn ($query) => $query->where('name', $token))
            ->first();

        if ($elementByName) {
            $analysisType = AnalysisType::query()->find($elementByName->analysis_type_id);

            return [
                'analysis_type_id' => (string) $elementByName->analysis_type_id,
                'analysis_element_id' => (string) $elementByName->id,
                'sample_type_id' => $analysisType?->sample_type_id ? (string) $analysisType->sample_type_id : null,
            ];
        }

        $analysisTypeByName = AnalysisType::query()->where('name', $token)->first();
        if ($analysisTypeByName) {
            return [
                'analysis_type_id' => (string) $analysisTypeByName->id,
                'analysis_element_id' => null,
                'sample_type_id' => $analysisTypeByName->sample_type_id ? (string) $analysisTypeByName->sample_type_id : null,
            ];
        }

        return [
            'analysis_type_id' => null,
            'analysis_element_id' => null,
            'sample_type_id' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPrefill(): array
    {
        return [
            'lines' => [],
            'customer_id' => null,
            'sample_type_id' => null,
            'pricelist' => null,
            'customer_name' => '',
            'request_date' => null,
            'number_of_samples' => 1,
            'mode_of_work' => 'Normal',
            'date_of_sampling' => null,
        ];
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

        if ($sampleTypeToken !== null && (ctype_digit((string) $sampleTypeToken) || Str::isUuid((string) $sampleTypeToken))) {
            return (string) $sampleTypeToken;
        }

        $fallbackSampleType = $instance->submissionForm?->sampleTypes?->first();
        if ($fallbackSampleType && isset($fallbackSampleType->id)) {
            return (string) $fallbackSampleType->id;
        }

        return null;
    }

    /**
     * @return list<array{analysis_id: string, name: string, label: string}>
     */
    private function extractParametersFromFormInstance(SubmissionFormInstance $instance): array
    {
        return $instance->values
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

                $label = $this->resolveParameterLabel($token);

                return [
                    'analysis_id' => $token,
                    'name' => $label,
                    'label' => $label,
                ];
            })
            ->filter()
            ->unique('label')
            ->values()
            ->all();
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

        $analysisType = AnalysisType::query()->find($token);

        return $analysisType?->name ?? $token;
    }

    /**
     * @return list<string>
     */
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

    /**
     * Legacy flat parameters for existing workflow-board JS.
     *
     * @return list<array<string, mixed>>
     */
    public function legacyParametersWithPricing(?string $submissionRequestId, ?string $submissionFormInstanceId): array
    {
        $prefill = $this->buildPrefillFromSelection($submissionRequestId, $submissionFormInstanceId);

        return collect($prefill['lines'])->map(function (array $line) {
            return [
                'analysis_id' => (string) ($line['analysis_type_id'] ?? $line['analysis_element_id'] ?? ''),
                'name' => (string) $line['parameter_label'],
                'label' => (string) $line['parameter_label'],
                'price' => (float) ($line['unit_amount'] ?? 0),
            ];
        })->values()->all();
    }

    /**
     * Sample types available when adding a line: customer pricelist plus types already on the table.
     *
     * @param  list<array<string, mixed>>  $existingLines
     * @return list<array{id: string, name: string}>
     */
    public function sampleTypesForAddLinePicker(string $customerId, array $existingLines): array
    {
        $fromLines = collect($existingLines)
            ->filter(fn (array $line) => !empty($line['sample_type_id']))
            ->map(fn (array $line) => [
                'id' => (string) $line['sample_type_id'],
                'name' => trim((string) ($line['sample_type_name'] ?? '')) !== ''
                    ? (string) $line['sample_type_name']
                    : (string) (optional(SampleType::find($line['sample_type_id']))->name ?? 'Sample type'),
            ])
            ->unique('id')
            ->values()
            ->all();

        return $this->mergeTypePickerOptions(
            $this->pricelistSampleTypesForCustomer($customerId),
            $fromLines
        );
    }

    /**
     * All active sample types for unrestricted add-line picker.
     *
     * @return list<array{id: string, name: string}>
     */
    public function allSampleTypesForPicker(): array
    {
        return SampleType::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (SampleType $type) => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
            ])
            ->all();
    }

    /**
     * All active analysis types for unrestricted add-line picker.
     *
     * @return list<array{id: string, name: string}>
     */
    public function allAnalysisTypesForPicker(?string $sampleTypeId = null): array
    {
        $query = AnalysisType::query()->orderBy('name');

        if ($sampleTypeId !== null && $sampleTypeId !== '') {
            $query->where('sample_type_id', $sampleTypeId);
        }

        return $query
            ->get(['id', 'name'])
            ->map(fn (AnalysisType $type) => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
            ])
            ->all();
    }

    /**
     * Analysis types for add-line picker: pricelist plus types already on the table for the sample type.
     *
     * @param  list<array<string, mixed>>  $existingLines
     * @return list<array{id: string, name: string}>
     */
    public function analysisTypesForAddLinePicker(
        string $customerId,
        ?string $sampleTypeId,
        array $existingLines
    ): array {
        $fromLines = collect($existingLines)
            ->filter(fn (array $line) => !empty($line['analysis_type_id'])
                && (string) ($line['sample_type_id'] ?? '') === (string) ($sampleTypeId ?? ''))
            ->map(fn (array $line) => [
                'id' => (string) $line['analysis_type_id'],
                'name' => trim((string) ($line['analysis_type_name'] ?? '')) !== ''
                    ? (string) $line['analysis_type_name']
                    : (string) (optional(AnalysisType::find($line['analysis_type_id']))->name ?? 'Analysis type'),
            ])
            ->unique('id')
            ->values()
            ->all();

        return $this->mergeTypePickerOptions(
            $this->pricelistAnalysisTypesForCustomer($customerId, $sampleTypeId),
            $fromLines
        );
    }

    /**
     * Pricelist parameters for an analysis selection, falling back to configured analysis elements.
     *
     * @return list<array<string, mixed>>
     */
    public function parametersForAddLineSelection(
        string $customerId,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): array {
        $fromPricelist = $this->pricelistParametersForCustomer($customerId, $sampleTypeId, $analysisTypeId);
        $withElements = array_values(array_filter(
            $fromPricelist,
            fn (array $param) => !empty($param['analysis_element_id'])
        ));

        if ($withElements !== []) {
            return $withElements;
        }

        return $this->analysisElementsAsParameterOptions($customerId, $sampleTypeId, $analysisTypeId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function analysisElementsAsParameterOptions(
        string $customerId,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): array {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return [];
        }

        $analysisType = AnalysisType::query()->find($analysisTypeId);
        if ($analysisType === null) {
            return [];
        }

        $pricelist = $this->resolvePricelist($customerId);
        $resolvedSampleTypeId = $sampleTypeId
            ?? ($analysisType->sample_type_id ? (string) $analysisType->sample_type_id : null);

        return AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->with(['analyte:id,name'])
            ->orderBy('level')
            ->get()
            ->map(function (AnalysisElements $element) use ($pricelist, $resolvedSampleTypeId, $analysisType) {
                $label = $element->analyte?->name
                    ?? ($element->method !== '' ? (string) $element->method : null)
                    ?? 'Parameter';

                return [
                    'id' => (string) $element->id,
                    'pricelist_item_id' => null,
                    'sample_type_id' => $resolvedSampleTypeId,
                    'sample_type_name' => $resolvedSampleTypeId
                        ? optional(SampleType::find($resolvedSampleTypeId))->name
                        : null,
                    'analysis_type_id' => (string) $analysisType->id,
                    'analysis_type_name' => (string) $analysisType->name,
                    'analysis_element_id' => (string) $element->id,
                    'label' => $label,
                    'unit_amount' => $this->resolveLinePrice(
                        $pricelist,
                        $resolvedSampleTypeId,
                        (string) $analysisType->id,
                        (string) $element->id
                    ),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array{id: string, name: string}>  $primary
     * @param  list<array{id: string, name: string}>  $secondary
     * @return list<array{id: string, name: string}>
     */
    private function mergeTypePickerOptions(array $primary, array $secondary): array
    {
        return collect($primary)
            ->merge($secondary)
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function parameterSeedsFromInstanceLines(array $lines, ?string $defaultSampleTypeId): array
    {
        $parameters = [];

        foreach ($lines as $line) {
            if (empty($line['analysis_type_id']) && empty($line['analysis_element_id'])) {
                continue;
            }

            $parameters[] = array_merge(
                $this->tokenToLineSeed(
                    (string) ($line['analysis_element_id'] ?? $line['analysis_type_id'] ?? ''),
                    (string) ($line['parameter_label'] ?? 'Parameter'),
                    $line['sample_type_id'] ?? $defaultSampleTypeId
                ),
                [
                    'sample_type_id' => $line['sample_type_id'] ?? $defaultSampleTypeId,
                    'analysis_type_id' => (string) ($line['analysis_type_id'] ?? ''),
                    'analysis_element_id' => $line['analysis_element_id'] ?? null,
                    'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                ]
            );
        }

        return $parameters;
    }

    /**
     * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 4
     *
     * @return list<array<string, mixed>>
     */
    private function parameterSeedsFromTrfi(TestRequestFormInstance $trfi, ?string $defaultSampleTypeId): array
    {
        return $this->parameterSeedsFromInstanceLines(
            app(SubmissionRequestSampleLineService::class)->linesForTrfi($trfi),
            $defaultSampleTypeId,
        );
    }

    private function resolveEnquiryForPrefill(?string $submissionRequestId, ?string $submissionFormInstanceId): ?SampleSubmissionRequest
    {
        if ($submissionRequestId) {
            return SampleSubmissionRequest::query()->find($submissionRequestId);
        }

        if ($submissionFormInstanceId) {
            return SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $submissionFormInstanceId)
                ->first();
        }

        return null;
    }

    /**
     * @return array{
     *     lines: list<array<string, mixed>>,
     *     customer_id: ?string,
     *     sample_type_id: ?string,
     *     pricelist: ?Pricelist,
     *     customer_name: string,
     *     request_date: ?string,
     *     number_of_samples: int,
     *     mode_of_work: string,
     *     date_of_sampling: ?string
     * }|null
     */
    private function buildPrefillFromAcceptedQuotation(SampleSubmissionRequest $enquiry): ?array
    {
        $readinessService = app(EnquiryReceptionReadinessService::class);
        if (! $readinessService->isEligibleForPhysicalReceive($enquiry) && $enquiry->quotation_accepted_at === null) {
            return null;
        }

        $quotation = $readinessService->resolveAcceptedQuotation($enquiry);
        if ($quotation === null) {
            return null;
        }

        $quotation->loadMissing('details');
        if ($quotation->details->isEmpty()) {
            return null;
        }

        $enquiry->loadMissing(['customer', 'submissionFormInstance']);
        $customerId = (string) $enquiry->crm_customer_id;
        $pricelist = $this->resolvePricelist($customerId);
        $lines = [];

        foreach ($quotation->details as $index => $detail) {
            $sampleTypeId = (string) ($detail->sample_type ?? $enquiry->sample_type_id ?? '');
            $analysisTypeId = (string) ($detail->part_no ?? '');
            $elementId = trim((string) ($detail->accredited_analytes ?? ''));
            $subcontractedIds = array_filter(array_map(
                'trim',
                explode(',', (string) ($detail->subcontracted_analytes ?? ''))
            ));

            $label = 'Parameter';
            if ($elementId !== '') {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                if ($element !== null) {
                    $label = (string) ($element->analyte->name ?? $element->name ?? $label);
                }
            } elseif ($analysisTypeId !== '') {
                $label = (string) (AnalysisType::find($analysisTypeId)?->name ?? 'Analysis');
            }

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId !== '' ? $sampleTypeId : null,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId !== '' ? $analysisTypeId : null,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => $label,
                'unit_amount' => (float) ($detail->unit_price ?? 0),
                'number_of_samples' => max(1, (int) ($detail->quantity ?? 1)),
                'is_approved' => true,
                'sort_order' => $index,
                'subcontracted' => $elementId !== '' && in_array($elementId, $subcontractedIds, true),
                'accredited' => $elementId !== '' && ! in_array($elementId, $subcontractedIds, true),
            ];
        }

        $lines = $this->deduplicateRedundantAnalysisTypeLines($lines);

        $instance = $enquiry->submissionFormInstance;
        $modeOfWork = strtolower((string) ($enquiry->mode_of_service_priority ?? $instance?->priority ?? '')) === 'express'
            ? 'Express'
            : 'Normal';

        return [
            'lines' => $lines,
            'customer_id' => $customerId,
            'sample_type_id' => $lines[0]['sample_type_id'] ?? null,
            'pricelist' => $pricelist,
            'customer_name' => (string) ($enquiry->customer?->name ?? ''),
            'request_date' => optional($enquiry->created_at)->format('Y-m-d'),
            'number_of_samples' => max(1, (int) ($enquiry->number_of_samples ?? 1)),
            'mode_of_work' => $modeOfWork,
            'date_of_sampling' => optional($enquiry->date_of_seizure)->format('Y-m-d'),
            'quotation_locked' => true,
        ];
    }
}
