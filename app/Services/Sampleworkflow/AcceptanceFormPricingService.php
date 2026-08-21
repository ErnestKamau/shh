<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use App\QuotationDetails;
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
                'submissionForm.sampleTypeCategories:id,sample_type_category',
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
        if (! empty($customerId)) {
            $assigned = $this->resolveCustomerAssignedPricelist($customerId);
            if ($assigned !== null) {
                return $assigned;
            }

            // Master is only used when the customer is explicitly assigned to it
            // (via pricelist_customers). Do not fall back to any random active list.
            $master = $this->resolveActiveMasterPricelist();
            if ($master !== null && $this->customerIsAssignedToPricelist($customerId, (string) $master->id)) {
                return $master;
            }

            return null;
        }

        return $this->resolveActiveMasterPricelist();
    }

    /**
     * Active master pricelist (lab default catalogue).
     * Callers must still check customer assignment before using it for pricing.
     */
    public function resolveActiveMasterPricelist(): ?Pricelist
    {
        return Pricelist::query()
            ->where('active', 1)
            ->where('is_master', 1)
            ->first();
    }

    public function customerIsAssignedToPricelist(string $customerId, string $pricelistId): bool
    {
        if ($customerId === '' || $pricelistId === '') {
            return false;
        }

        return PricelistCustomer::query()
            ->where('customer_id', $customerId)
            ->where('pricelist_id', $pricelistId)
            ->exists();
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

        $pricelistCustomer = PricelistCustomer::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->select('pricelist_id')
            ->first();

        if (!$pricelistCustomer) {
            return null;
        }

        return Pricelist::find($pricelistCustomer->pricelist_id);
    }

    /**
     * @return list<Pricelist>
     */
    public function assignedPricelistsForCustomer(string $customerId): array
    {
        if ($customerId === '') {
            return [];
        }

        $pricelistIds = PricelistCustomer::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->pluck('pricelist_id')
            ->unique()
            ->values();

        if ($pricelistIds->isEmpty()) {
            return [];
        }

        $pricelistsById = Pricelist::query()
            ->whereIn('id', $pricelistIds)
            ->where('active', 1)
            ->get()
            ->keyBy('id');

        return $pricelistIds
            ->map(fn (string $id): ?Pricelist => $pricelistsById->get($id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Resolve a line price by searching preferred → assigned lists only.
     * Master is included only when the customer is assigned to that master.
     *
     * @return array{price: float, pricelist: ?Pricelist}
     */
    public function resolveLinePriceWithPricelist(
        ?string $customerId,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
        ?Pricelist $preferredPricelist = null,
    ): array {
        $candidates = $this->candidatePricelistsForPricing($customerId, $preferredPricelist);

        foreach ($candidates as $pricelist) {
            $price = $this->resolveLinePrice($pricelist, $sampleTypeId, $analysisTypeId, $analysisElementId);
            if ($price > 0) {
                return ['price' => $price, 'pricelist' => $pricelist];
            }
        }

        return ['price' => 0.0, 'pricelist' => $candidates[0] ?? null];
    }

    /**
     * Pricelist search order for line prices and packages:
     * preferred (if eligible) → customer assignments (active, by recency).
     * Master only when assigned to the customer — never an unassigned global fallback.
     *
     * @return list<Pricelist>
     */
    public function candidatePricelistsForPricing(
        ?string $customerId,
        ?Pricelist $preferredPricelist = null,
    ): array {
        $candidates = [];
        $eligible = $this->eligiblePricelistsForCustomer($customerId);
        $eligibleIds = collect($eligible)->map(fn (Pricelist $p): string => (string) $p->id)->all();

        if ($preferredPricelist !== null && in_array((string) $preferredPricelist->id, $eligibleIds, true)) {
            $candidates[] = $preferredPricelist;
        }

        foreach ($eligible as $pricelist) {
            if ($preferredPricelist !== null && $pricelist->id === $preferredPricelist->id) {
                continue;
            }
            $candidates[] = $pricelist;
        }

        return $candidates;
    }

    /**
     * Active pricelists the customer may use on quotation prep.
     * Includes master only when assigned via pricelist_customers.
     *
     * @return list<Pricelist>
     */
    public function eligiblePricelistsForCustomer(?string $customerId): array
    {
        if ($customerId === null || $customerId === '') {
            $master = $this->resolveActiveMasterPricelist();

            return $master !== null ? [$master] : [];
        }

        return $this->assignedPricelistsForCustomer($customerId);
    }

    /**
     * Chooser payload for prep UI when multiple eligible lists exist.
     *
     * @return array{
     *     needs_choice: bool,
     *     selected_pricelist_id: ?string,
     *     pricelists: list<array<string, mixed>>
     * }
     */
    public function buildPricelistChooserPayload(?string $customerId, ?string $selectedPricelistId = null): array
    {
        $eligible = $this->eligiblePricelistsForCustomer($customerId);
        $cards = [];

        foreach ($eligible as $pricelist) {
            $pricelist->loadMissing(['items.packageElements', 'currency']);
            $items = [];
            foreach ($pricelist->items as $item) {
                $elementIds = $item->coveredElementIds();
                if ($elementIds === [] && ! empty($item->analysis_element_id)) {
                    $elementIds = [(string) $item->analysis_element_id];
                }
                $items[] = [
                    'id' => (string) $item->id,
                    'sample_type_id' => (string) ($item->sample_type_id ?? ''),
                    'analysis_type_id' => (string) ($item->analysis_id ?? ''),
                    'selling_price' => (float) $item->selling_price,
                    'is_package' => (bool) ($item->is_package ?? false),
                    'pricing_mode' => (bool) ($item->is_package ?? false) ? 'per_package' : 'per_test',
                    'element_ids' => $elementIds,
                    'element_count' => count($elementIds),
                    'vat' => (bool) ($item->vat ?? false),
                ];
            }

            $cards[] = [
                'id' => (string) $pricelist->id,
                'code' => (string) ($pricelist->code ?? ''),
                'description' => (string) ($pricelist->description ?? ''),
                'is_master' => (bool) ($pricelist->is_master ?? false),
                'currency' => (string) ($pricelist->currency?->code ?? ''),
                'item_count' => count($items),
                'package_count' => collect($items)->where('is_package', true)->count(),
                'per_test_count' => collect($items)->where('is_package', false)->count(),
                'items' => $items,
            ];
        }

        $selected = $selectedPricelistId;
        if ($selected !== null && $selected !== '') {
            $ids = collect($cards)->pluck('id')->all();
            if (! in_array($selected, $ids, true)) {
                $selected = $cards[0]['id'] ?? null;
            }
        } else {
            $selected = $cards[0]['id'] ?? null;
        }

        return [
            'needs_choice' => count($cards) > 1,
            'selected_pricelist_id' => $selected,
            'pricelists' => $cards,
        ];
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
                if (! empty($line['is_package'])) {
                    return true;
                }

                if (! empty($line['analysis_element_id'])) {
                    return true;
                }

                $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

                return $analysisTypeId === ''
                    || ! in_array($analysisTypeId, $analysisTypesWithElements, true);
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
        $item = $this->findMatchingLineItem($pricelist, $sampleTypeId, $analysisTypeId, $analysisElementId);

        return $item !== null ? (float) $item->selling_price : 0.0;
    }

    /**
     * Same match order as resolveLinePrice (including analyte fallback for TRF UUID mismatches).
     */
    public function findMatchingLineItem(
        ?Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
    ): ?PricelistItem {
        if ($pricelist === null) {
            return null;
        }

        $query = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->where('is_package', false);

        if (! empty($sampleTypeId)) {
            $query->where('sample_type_id', $sampleTypeId);
        }

        if (! empty($analysisElementId)) {
            $item = (clone $query)->where('analysis_element_id', $analysisElementId)->first();
            if ($item !== null) {
                return $item;
            }

            $item = PricelistItem::query()
                ->where('pricelist_id', $pricelist->id)
                ->where('active', 1)
                ->where('is_package', false)
                ->where('analysis_element_id', $analysisElementId)
                ->first();
            if ($item !== null) {
                return $item;
            }

            // TRF/catalog often keeps a different element UUID than the pricelist row for the
            // same analyte (re-imports, Food vs Food & Feed duplicates). Match by analyte.
            $analyteItem = $this->findMatchingLineItemByAnalyte(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $analysisElementId,
            );
            if ($analyteItem !== null) {
                return $analyteItem;
            }
        }

        if ($analysisTypeId !== '') {
            return (clone $query)
                ->where('analysis_id', $analysisTypeId)
                ->whereNull('analysis_element_id')
                ->first();
        }

        return null;
    }

    /**
     * Find a pricelist item when the line's analysis_element_id is not on the pricelist,
     * but another element for the same analyte (id / name / code) is.
     */
    private function findMatchingLineItemByAnalyte(
        Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        string $analysisElementId,
    ): ?PricelistItem {
        $element = AnalysisElements::query()
            ->with('analyte:id,name,code')
            ->find($analysisElementId);

        if ($element === null) {
            return null;
        }

        $analyteId = trim((string) ($element->analyte_id ?? ''));
        $analyteName = strtolower(trim((string) ($element->analyte?->name ?? '')));
        $analyteCode = strtolower(trim((string) ($element->analyte?->code ?? '')));

        if ($analyteId === '' && $analyteName === '' && $analyteCode === '') {
            return null;
        }

        $baseQuery = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->where('is_package', false)
            ->whereNotNull('analysis_element_id');

        $scoped = (clone $baseQuery);
        if (! empty($sampleTypeId)) {
            $scoped->where('sample_type_id', $sampleTypeId);
        }
        if ($analysisTypeId !== '') {
            $scoped->where('analysis_id', $analysisTypeId);
        }

        foreach ([$scoped, $baseQuery] as $query) {
            if ($analyteId !== '') {
                $item = (clone $query)
                    ->whereHas('analysisElement', fn ($elementQuery) => $elementQuery->where('analyte_id', $analyteId))
                    ->first();
                if ($item !== null) {
                    return $item;
                }
            }

            if ($analyteName !== '' || $analyteCode !== '') {
                $item = (clone $query)
                    ->whereHas('analysisElement.analyte', function ($analyteQuery) use ($analyteName, $analyteCode): void {
                        $analyteQuery->where(function ($match) use ($analyteName, $analyteCode): void {
                            if ($analyteName !== '') {
                                $match->whereRaw('LOWER(name) = ?', [$analyteName]);
                            }
                            if ($analyteCode !== '') {
                                $match->orWhereRaw('LOWER(code) = ?', [$analyteCode]);
                            }
                        });
                    })
                    ->first();
                if ($item !== null) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Find an analysis-type package covering the requested elements, searching pricelists
     * in the same order as resolveLinePriceWithPricelist (preferred → assigned by recency → master).
     * A package applies when every element it covers is present in the requested set.
     *
     * @param  list<string>  $requestedElementIds
     * @return ?array{item: PricelistItem, pricelist: Pricelist, covered_element_ids: list<string>}
     */
    public function resolvePackageForGroup(
        ?string $customerId,
        ?string $sampleTypeId,
        string $analysisTypeId,
        array $requestedElementIds,
        ?Pricelist $preferredPricelist = null,
    ): ?array {
        $requestedElementIds = array_values(array_unique(array_filter(array_map(
            fn ($id): string => (string) $id,
            $requestedElementIds
        ))));

        if ($analysisTypeId === '' || $requestedElementIds === []) {
            return null;
        }

        foreach ($this->candidatePricelistsForPricing($customerId, $preferredPricelist) as $pricelist) {
            $match = $this->matchPackageInPricelist($pricelist, $sampleTypeId, $analysisTypeId, $requestedElementIds);
            if ($match !== null) {
                return [
                    'item' => $match['item'],
                    'pricelist' => $pricelist,
                    'covered_element_ids' => $match['covered_element_ids'],
                ];
            }
        }

        return null;
    }

    /**
     * Locate an active package for sample type + analysis type without requiring
     * the caller to already know which elements are covered.
     *
     * @return ?array{item: PricelistItem, pricelist: Pricelist, covered_element_ids: list<string>}
     */
    public function findPackageForAnalysisType(
        ?string $customerId,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?Pricelist $preferredPricelist = null,
    ): ?array {
        if ($analysisTypeId === '') {
            return null;
        }

        foreach ($this->candidatePricelistsForPricing($customerId, $preferredPricelist) as $pricelist) {
            $match = $this->firstPackageInPricelist($pricelist, $sampleTypeId, $analysisTypeId);
            if ($match !== null) {
                return [
                    'item' => $match['item'],
                    'pricelist' => $pricelist,
                    'covered_element_ids' => $match['covered_element_ids'],
                ];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $requestedElementIds
     * @return ?array{item: PricelistItem, covered_element_ids: list<string>}
     */
    private function matchPackageInPricelist(
        Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        array $requestedElementIds,
    ): ?array {
        $packages = $this->packagesForAnalysisType($pricelist, $sampleTypeId, $analysisTypeId);

        foreach ($packages as $package) {
            $coveredElementIds = $package->coveredElementIds();

            if ($coveredElementIds === []) {
                continue;
            }

            if (array_diff($coveredElementIds, $requestedElementIds) === []) {
                return [
                    'item' => $package,
                    'covered_element_ids' => $coveredElementIds,
                ];
            }
        }

        return null;
    }

    /**
     * @return ?array{item: PricelistItem, covered_element_ids: list<string>}
     */
    private function firstPackageInPricelist(
        Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
    ): ?array {
        foreach ($this->packagesForAnalysisType($pricelist, $sampleTypeId, $analysisTypeId) as $package) {
            $coveredElementIds = $package->coveredElementIds();

            if ($coveredElementIds === []) {
                continue;
            }

            return [
                'item' => $package,
                'covered_element_ids' => $coveredElementIds,
            ];
        }

        return null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, PricelistItem>
     */
    private function packagesForAnalysisType(
        Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
    ): Collection {
        $query = PricelistItem::query()
            ->with('packageElements')
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->where('is_package', true)
            ->where('analysis_id', $analysisTypeId);

        $packages = (! empty($sampleTypeId))
            ? (clone $query)->where('sample_type_id', $sampleTypeId)->get()
            : collect();

        if ($packages->isEmpty()) {
            $packages = $query->get();
        }

        return $packages;
    }

    /**
     * Collapse per-parameter quotation lines into a single package line when the request
     * covers all elements in an assigned analysis-type package. Uncovered extras keep
     * their per-parameter pricing.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyPackagePricingToLines(
        array $lines,
        ?string $customerId,
        ?Pricelist $preferredPricelist = null,
    ): array {
        if ($lines === []) {
            return [];
        }

        $groups = [];
        foreach ($lines as $index => $line) {
            if (! empty($line['is_package'])) {
                continue;
            }

            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            if ($elementId === '' || $analysisTypeId === '') {
                continue;
            }

            $key = implode('::', [
                (string) ($line['acceptance_config_key'] ?? ''),
                (string) ($line['sample_type_id'] ?? ''),
                $analysisTypeId,
            ]);
            $groups[$key][] = $index;
        }

        $removedIndexes = [];
        $packageLineByFirstIndex = [];

        foreach ($groups as $indexes) {
            $firstLine = $lines[$indexes[0]];
            $sampleTypeId = (string) ($firstLine['sample_type_id'] ?? '');
            $analysisTypeId = (string) ($firstLine['analysis_type_id'] ?? '');

            $requestedElementIds = array_map(
                fn (int $index): string => (string) ($lines[$index]['analysis_element_id'] ?? ''),
                $indexes
            );

            $match = $this->resolvePackageForGroup(
                $customerId,
                $sampleTypeId !== '' ? $sampleTypeId : null,
                $analysisTypeId,
                $requestedElementIds,
                $preferredPricelist,
            );

            if ($match === null) {
                continue;
            }

            $coveredElementIds = $match['covered_element_ids'];
            $coveredIndexes = array_values(array_filter(
                $indexes,
                fn (int $index): bool => in_array((string) ($lines[$index]['analysis_element_id'] ?? ''), $coveredElementIds, true)
            ));

            if ($coveredIndexes === []) {
                continue;
            }

            $quantity = max(array_map(
                fn (int $index): int => max(1, (int) ($lines[$index]['physical_sample_count'] ?? $lines[$index]['quantity'] ?? 1)),
                $coveredIndexes
            ));

            $packageLineByFirstIndex[min($coveredIndexes)] = $this->makePackageLine(
                $firstLine,
                $match['item'],
                $coveredElementIds,
                $quantity,
            );

            foreach ($coveredIndexes as $index) {
                $removedIndexes[$index] = true;
            }
        }

        if ($packageLineByFirstIndex === []) {
            return $lines;
        }

        $result = [];
        foreach ($lines as $index => $line) {
            if (isset($packageLineByFirstIndex[$index])) {
                $result[] = $packageLineByFirstIndex[$index];
            }

            if (isset($removedIndexes[$index])) {
                continue;
            }

            $result[] = $line;
        }

        foreach ($result as $index => &$line) {
            $line['line_no'] = $index + 1;
            if (array_key_exists('sort_order', $line)) {
                $line['sort_order'] = $index;
            }
        }
        unset($line);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $templateLine
     * @param  list<string>  $coveredElementIds
     * @return array<string, mixed>
     */
    private function makePackageLine(
        array $templateLine,
        PricelistItem $packageItem,
        array $coveredElementIds,
        int $quantity,
    ): array {
        $elementLabels = AnalysisElements::query()
            ->with('analyte:id,name,code')
            ->whereIn('id', $coveredElementIds)
            ->orderBy('level')
            ->get()
            ->map(fn (AnalysisElements $element): string => (string) ($element->analyte?->name ?? $element->name ?? 'Parameter'))
            ->values()
            ->all();

        $analysisTypeName = (string) ($templateLine['analysis_type_name'] ?? '');
        if ($analysisTypeName === '') {
            $analysisTypeId = (string) ($templateLine['analysis_type_id'] ?? '');
            $analysisTypeName = $analysisTypeId !== ''
                ? (string) (AnalysisType::find($analysisTypeId)?->name ?? 'Analysis')
                : 'Analysis';
        }

        $taxPercent = $packageItem->vat
            ? app(\App\Services\Billing\QuotationLineTaxResolver::class)->activeTaxRegimePercent()
            : 0.0;

        $line = $templateLine;
        $line['analysis_element_id'] = null;
        $line['is_package'] = true;
        $line['package_pricelist_item_id'] = (string) $packageItem->id;
        $line['package_element_ids'] = array_values($coveredElementIds);
        $line['package_element_labels'] = $elementLabels;
        $line['parameter_label'] = $analysisTypeName.' package ('.count($coveredElementIds).' parameters)';
        $line['physical_sample_count'] = $quantity;
        $line['quantity'] = $quantity;
        $line['unit_price'] = (float) $packageItem->selling_price;
        if (array_key_exists('unit_amount', $line)) {
            $line['unit_amount'] = (float) $packageItem->selling_price;
        }
        $line['tax'] = $taxPercent;
        $line['vat_from_pricelist'] = true;
        $line['vat_manual'] = false;
        $line['subcontracted'] = false;

        return $line;
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
            ->with(['sampleType:id,name', 'analysisType:id,name', 'analysisElement.analyte:id,name,code']);

        if ($sampleTypeId) {
            $query->where('sample_type_id', $sampleTypeId);
        }

        if ($analysisTypeId) {
            $query->where('analysis_id', $analysisTypeId);
        }

        return $query->get()->map(function (PricelistItem $item) {
            $analyte = $item->analysisElement?->analyte;
            $code = trim((string) ($analyte?->code ?? ''));
            $label = $analyte?->name
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
                'code' => $code,
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

        $form = $instance->submissionForm;
        $fallbackSampleType = $form !== null
            ? app(\App\Services\SubmissionForm\PortalTestRequestFormSampleTypeResolver::class)->resolveForForm($form)->first()
            : null;
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
            ->with(['analyte:id,name,code'])
            ->orderBy('level')
            ->get()
            ->map(function (AnalysisElements $element) use ($pricelist, $resolvedSampleTypeId, $analysisType) {
                $code = trim((string) ($element->analyte?->code ?? ''));
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
                    'code' => $code,
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

        // Reuse quotation detail expansion so package lines keep human labels and
        // package_element_ids instead of stuffing UUID CSV into parameter_label.
        $inlineLines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromQuotationHeader($quotation);

        $lines = [];
        foreach ($inlineLines as $index => $row) {
            $sampleTypeId = trim((string) ($row['sample_type_id'] ?? ''));
            $analysisTypeId = trim((string) ($row['analysis_type_id'] ?? ''));
            $label = trim((string) ($row['parameter_label'] ?? ''));
            if ($label === '' || $this->looksLikeElementIdList($label)) {
                $analysisTypeName = (string) ($row['analysis_type_name'] ?? '');
                if ($analysisTypeName === '' && $analysisTypeId !== '') {
                    $analysisTypeName = (string) (AnalysisType::find($analysisTypeId)?->name ?? 'Analysis');
                }
                $packageCount = count($row['package_element_ids'] ?? []);
                $label = ! empty($row['is_package'])
                    ? ($analysisTypeName !== '' ? $analysisTypeName : 'Analysis').' package'.($packageCount > 0 ? ' ('.$packageCount.' parameters)' : '')
                    : ($analysisTypeName !== '' ? $analysisTypeName : 'Parameter');
            }

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId !== '' ? $sampleTypeId : null,
                'sample_type_name' => (string) ($row['sample_type_name'] ?? ($sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '')),
                'analysis_type_id' => $analysisTypeId !== '' ? $analysisTypeId : null,
                'analysis_type_name' => (string) ($row['analysis_type_name'] ?? ''),
                'analysis_element_id' => $row['analysis_element_id'] ?? null,
                'parameter_label' => Str::limit($label, 255, ''),
                'unit_amount' => (float) ($row['unit_price'] ?? $row['unit_amount'] ?? 0),
                'number_of_samples' => max(1, (int) ($row['physical_sample_count'] ?? $row['quantity'] ?? 1)),
                'is_approved' => true,
                'sort_order' => $index,
                'subcontracted' => (bool) ($row['subcontracted'] ?? false),
                'accredited' => ! (bool) ($row['subcontracted'] ?? false),
                'is_package' => (bool) ($row['is_package'] ?? false),
                'package_element_ids' => array_values(array_filter(array_map(
                    'strval',
                    is_array($row['package_element_ids'] ?? null) ? $row['package_element_ids'] : []
                ))),
                'package_element_labels' => array_values(array_filter(array_map(
                    'strval',
                    is_array($row['package_element_labels'] ?? null) ? $row['package_element_labels'] : []
                ))),
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

    private function looksLikeElementIdList(string $value): bool
    {
        if ($value === '' || ! str_contains($value, ',')) {
            return false;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value))));

        return $parts !== [] && collect($parts)->every(fn (string $part): bool => Str::isUuid($part));
    }
}
