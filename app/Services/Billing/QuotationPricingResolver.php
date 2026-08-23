<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;

/**
 * Quotation line commercial pricing (Amspec UAE prep model).
 *
 * Formula (package / sample block):
 *   Total price = No. of samples × Unit price
 *
 * It is NOT:
 *   - number of tests × unit price
 *   - sum of a separate unit price per test
 *
 * So one sample package (e.g. Potable Water) can list 20+ tests for scope
 * (LOQ, MU, method, accreditation) and still bill a single unit price once
 * for that sample package. PDF/prep UI lists every test under the block;
 * commercial columns (qty, unit price, total) belong to the package, not each test.
 *
 * PRICING_MODE_PER_TEST is the explicit exception that splits one prep line
 * into per-test billed rows. AUTO / PER_PACKAGE keep one commercial line.
 */
class QuotationPricingResolver
{
    public const PRICING_MODE_AUTO = 'auto';

    public const PRICING_MODE_PER_TEST = 'per_test';

    public const PRICING_MODE_PER_PACKAGE = 'per_package';

    public function __construct(
        private readonly AcceptanceFormPricingService $acceptanceFormPricingService,
        private readonly QuotationLineTaxResolver $quotationLineTaxResolver,
        private readonly UncertaintyBudgetResolver $uncertaintyBudgetResolver,
    ) {}

    public function resolvePricelist(?string $customerId, ?QuotationHeader $header = null): ?Pricelist
    {
        if ($header !== null && ! empty($header->pricelist_id)) {
            $selected = Pricelist::query()->find($header->pricelist_id);
            if ($selected !== null) {
                $eligibleIds = collect(
                    $this->acceptanceFormPricingService->eligiblePricelistsForCustomer($customerId)
                )->map(fn (Pricelist $p): string => (string) $p->id)->all();

                if (in_array((string) $selected->id, $eligibleIds, true)) {
                    return $selected;
                }
            }
        }

        $assigned = $this->acceptanceFormPricingService->resolveCustomerAssignedPricelist($customerId);
        if ($assigned !== null) {
            return $assigned;
        }

        // No unassigned master / random active fallback — customer must be assigned.
        $eligible = $this->acceptanceFormPricingService->eligiblePricelistsForCustomer($customerId);

        return $eligible[0] ?? null;
    }

    /**
     * @return array{unit_price: float, source: string, invoicable_item_id: ?string}
     */
    public function resolveLineUnitPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
        ?float $storedUnitPrice = null,
        bool $preferStored = true,
    ): array {
        if ($preferStored && $storedUnitPrice !== null && $storedUnitPrice > 0) {
            return [
                'unit_price' => $storedUnitPrice,
                'source' => 'manual',
                'invoicable_item_id' => null,
            ];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id, $header);
        $pricelistPrice = $this->acceptanceFormPricingService->resolveLinePrice(
            $pricelist,
            $sampleTypeId,
            $analysisTypeId,
            $analysisElementId,
        );

        if ($pricelistPrice > 0) {
            return [
                'unit_price' => $pricelistPrice,
                'source' => 'pricelist',
                'invoicable_item_id' => null,
            ];
        }

        $invoicable = getPriceForAnalysisType($analysisTypeId, $header->currency_id);
        if ($invoicable && ($invoicable['unit_price'] ?? 0) > 0) {
            return [
                'unit_price' => (float) $invoicable['unit_price'],
                'source' => 'invoicable',
                'invoicable_item_id' => $invoicable['invoicable_item_id'] ?? null,
            ];
        }

        return [
            'unit_price' => 0.0,
            'source' => 'none',
            'invoicable_item_id' => null,
        ];
    }

    /**
     * @return array{unit_price: float, source: string, invoicable_item_id: ?string}
     */
    public function resolveElementPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
        ?float $storedUnitPrice = null,
    ): array {
        return $this->resolveLineUnitPrice(
            $header,
            $sampleTypeId,
            $analysisTypeId,
            $analysisElementId,
            $storedUnitPrice,
            true,
        );
    }

    public function suggestPrefillUnitPrice(
        ?Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId,
    ): float {
        return $this->acceptanceFormPricingService->resolveLinePrice(
            $pricelist,
            $sampleTypeId,
            $analysisTypeId,
            $analysisElementId,
        );
    }

    /**
     * @param  list<string>  $elementIds
     */
    public function suggestLineUnitPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
    ): float {
        $result = $this->suggestManualLinePricing(
            $header,
            $sampleTypeId,
            $analysisTypeIdsCsv,
            $elementIds,
            self::PRICING_MODE_PER_TEST,
        );

        return (float) $result['unit_price'];
    }

    /**
     * Resolve package parameter defaults for quotation prep when analysis type(s) are selected.
     * Unions covered elements across selected analysis types that have packages on the customer pricelist.
     *
     * @return array{
     *     found: bool,
     *     element_ids: list<string>,
     *     accredited_ids: list<string>,
     *     default_ids: list<string>,
     *     parameters: list<array{id: string, label: string, accredited: bool}>,
     *     unit_price: float,
     *     tax: float,
     *     source: string,
     *     hint: string,
     *     is_package: bool,
     *     pricing_mode: string,
     *     max_tat: int|null
     * }
     */
    public function resolvePackageDefaults(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
    ): array {
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $pricelist = $this->resolvePricelist($header->crm_customer_id, $header);

        $elementIds = [];
        $foundAny = false;

        foreach ($analysisTypeIds as $analysisTypeId) {
            $match = $this->acceptanceFormPricingService->findPackageForAnalysisType(
                $header->crm_customer_id,
                $sampleTypeId,
                $analysisTypeId,
                $pricelist,
            );

            if ($match === null) {
                continue;
            }

            $foundAny = true;
            foreach ($match['covered_element_ids'] as $elementId) {
                $elementIds[] = (string) $elementId;
            }
        }

        $elementIds = array_values(array_unique($elementIds));

        if (! $foundAny || $elementIds === []) {
            return [
                'found' => false,
                'element_ids' => [],
                'accredited_ids' => [],
                'default_ids' => [],
                'parameters' => [],
                'unit_price' => 0.0,
                'tax' => 0.0,
                'source' => 'none',
                'hint' => 'No package on the customer pricelist for the selected analysis type.',
                'is_package' => false,
                'pricing_mode' => self::PRICING_MODE_AUTO,
                'max_tat' => null,
            ];
        }

        $suggestion = $this->suggestManualLinePricing(
            $header,
            $sampleTypeId,
            $analysisTypeIdsCsv,
            $elementIds,
            self::PRICING_MODE_AUTO,
        );

        $accreditedIds = $this->accreditedElementIds($elementIds);
        $defaultIds = array_values(array_diff($elementIds, $accreditedIds));

        return [
            'found' => true,
            'element_ids' => $elementIds,
            'accredited_ids' => $accreditedIds,
            'default_ids' => $defaultIds,
            'parameters' => $this->parameterDisplayRows($elementIds, $accreditedIds),
            'unit_price' => (float) $suggestion['unit_price'],
            'tax' => (float) $suggestion['tax'],
            'source' => (string) $suggestion['source'],
            'hint' => (string) $suggestion['hint'],
            'is_package' => (bool) $suggestion['is_package'],
            'pricing_mode' => (string) $suggestion['pricing_mode'],
            'max_tat' => $suggestion['max_tat'],
        ];
    }

    /**
     * @param  list<string>  $elementIds
     * @param  list<string>  $accreditedIds
     * @return list<array{id: string, label: string, accredited: bool}>
     */
    private function parameterDisplayRows(array $elementIds, array $accreditedIds): array
    {
        if ($elementIds === []) {
            return [];
        }

        $accreditedSet = array_fill_keys($accreditedIds, true);
        $elements = AnalysisElements::query()
            ->with('analyte:id,name,code')
            ->whereIn('id', $elementIds)
            ->get()
            ->keyBy(fn (AnalysisElements $element): string => (string) $element->id);

        $rows = [];
        foreach ($elementIds as $elementId) {
            $element = $elements->get($elementId);
            $label = trim((string) ($element?->analyte?->name ?? $element?->parametername ?? $element?->method ?? ''));
            if ($label === '') {
                $label = $elementId;
            }

            $rows[] = [
                'id' => $elementId,
                'label' => $label,
                'accredited' => isset($accreditedSet[$elementId]),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<string>  $elementIds
     * @return list<string>
     */
    private function accreditedElementIds(array $elementIds): array
    {
        if ($elementIds === []) {
            return [];
        }

        $elements = AnalysisElements::query()
            ->whereIn('id', $elementIds)
            ->get(['id', 'non_accredited'])
            ->keyBy(fn (AnalysisElements $element): string => (string) $element->id);

        $accredited = [];
        foreach ($elementIds as $elementId) {
            $element = $elements->get($elementId);
            $isNonAccredited = $element !== null && (
                (int) $element->non_accredited === 1
                || $element->non_accredited === true
                || $element->non_accredited === 'true'
            );

            if (! $isNonAccredited) {
                $accredited[] = $elementId;
            }
        }

        return $accredited;
    }

    /**
     * Keep accredited / subcontracted / default analyte CSVs mutually exclusive.
     *
     * @param  list<string>  $elementIds
     * @return array{accredited: string, default: string, subcontracted: string, sub_acc: string}
     */
    private function exclusiveParameterCsvs(
        array $elementIds,
        string $accreditedAnalytes,
        string $subcontractedAnalytes,
        string $defaultAnalytes,
        string $subAccAnalytes,
    ): array {
        $split = static function (string $csv): array {
            return array_values(array_filter(array_map('trim', explode(',', $csv)), static fn (string $id): bool => $id !== ''));
        };

        $accredited = $split($accreditedAnalytes);
        $subcontracted = $split($subcontractedAnalytes);
        $subAcc = $split($subAccAnalytes);
        $defaultSubmitted = $split($defaultAnalytes);

        if (
            $accredited === []
            && $defaultSubmitted === []
            && $subcontracted === []
            && $subAcc === []
            && $elementIds !== []
        ) {
            $accredited = $this->accreditedElementIds($elementIds);
        }

        $claimed = array_values(array_unique(array_merge($accredited, $subcontracted, $subAcc)));
        $default = array_values(array_diff($elementIds, $claimed));

        return [
            'accredited' => implode(',', $accredited),
            'default' => implode(',', $default),
            'subcontracted' => implode(',', $subcontracted),
            'sub_acc' => implode(',', $subAcc),
        ];
    }

    /**
     * @param  list<string>  $elementIds
     * @return array{
     *     unit_price: float,
     *     tax: float,
     *     source: string,
     *     hint: string,
     *     is_package: bool,
     *     pricing_mode: string,
     *     max_tat: int|null,
     *     element_prices?: array<string, array{unit_price: float, tax: float}>
     * }
     */
    public function suggestManualLinePricing(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
        string $pricingMode = self::PRICING_MODE_AUTO,
    ): array {
        $pricingMode = $this->normalizePricingMode($pricingMode);
        $maxTat = $this->maxTatForElements($elementIds, $analysisTypeIdsCsv);

        if ($elementIds === []) {
            return [
                'unit_price' => 0.0,
                'tax' => 0.0,
                'source' => 'none',
                'hint' => 'Select tests in Description to load pricelist total.',
                'is_package' => false,
                'pricing_mode' => $pricingMode,
                'max_tat' => $maxTat,
            ];
        }

        $package = null;
        if ($pricingMode !== self::PRICING_MODE_PER_TEST) {
            $package = $this->findPackageMatch($header, $sampleTypeId, $analysisTypeIdsCsv, $elementIds);
        }

        if ($pricingMode === self::PRICING_MODE_PER_PACKAGE && $package === null) {
            return [
                'unit_price' => 0.0,
                'tax' => 0.0,
                'source' => 'none',
                'hint' => 'No matching package on the customer pricelist for the selected parameters.',
                'is_package' => false,
                'pricing_mode' => $pricingMode,
                'max_tat' => $maxTat,
            ];
        }

        if ($package !== null && ($pricingMode === self::PRICING_MODE_AUTO || $pricingMode === self::PRICING_MODE_PER_PACKAGE)) {
            $item = $package['item'];
            $covered = $package['covered_element_ids'];
            $packagePrice = (float) $item->selling_price;
            $extras = array_values(array_diff($elementIds, $covered));
            $pricelist = $package['pricelist'] ?? $this->resolvePricelist($header->crm_customer_id, $header);
            $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
            $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';

            $extraTotal = 0.0;
            if ($pricingMode === self::PRICING_MODE_AUTO) {
                foreach ($extras as $extraId) {
                    $element = AnalysisElements::query()->find($extraId);
                    $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
                    $extraTotal += $this->suggestPrefillUnitPrice(
                        $pricelist,
                        $sampleTypeId,
                        $analysisTypeId,
                        (string) $extraId,
                    );
                }
            }

            $tax = $item->vat
                ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                : 0.0;

            $total = round($packagePrice + $extraTotal, 2);
            $isPurePackage = $extras === [] || $pricingMode === self::PRICING_MODE_PER_PACKAGE;
            $hint = $isPurePackage || $extras === []
                ? sprintf(
                    'Package price for %d parameter(s): %s',
                    count($covered),
                    number_format($packagePrice, 2)
                )
                : sprintf(
                    'Package (%d params) + %d extra test(s): %s',
                    count($covered),
                    count($extras),
                    number_format($total, 2)
                );

            return [
                'unit_price' => $isPurePackage ? round($packagePrice, 2) : $total,
                'tax' => round((float) $tax, 2),
                'source' => $extras === [] ? 'package' : 'pricelist_package',
                'hint' => $hint,
                'is_package' => $extras === [],
                'pricing_mode' => $pricingMode === self::PRICING_MODE_AUTO
                    ? ($extras === [] ? self::PRICING_MODE_PER_PACKAGE : self::PRICING_MODE_PER_TEST)
                    : $pricingMode,
                'max_tat' => $maxTat,
            ];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id, $header);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';

        $total = 0.0;
        $taxes = [];
        /** @var array<string, array{unit_price: float, tax: float}> $elementPrices */
        $elementPrices = [];
        foreach ($elementIds as $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
            $rowPrice = round($this->suggestPrefillUnitPrice(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId,
            ), 2);
            $rowTax = round($this->quotationLineTaxResolver->resolveLineTaxPercent(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId,
            ), 2);
            $total += $rowPrice;
            $taxes[] = $rowTax;
            $elementPrices[(string) $elementId] = [
                'unit_price' => $rowPrice,
                'tax' => $rowTax,
            ];
        }

        $displayTax = $taxes !== [] ? max($taxes) : 0.0;

        return [
            'unit_price' => round($total, 2),
            'tax' => round($displayTax, 2),
            'source' => $pricelist !== null && $total > 0 ? 'pricelist' : 'none',
            'hint' => $total > 0
                ? sprintf('Pricelist total for %d test(s): %s', count($elementIds), number_format($total, 2))
                : 'No pricelist prices for selected tests.',
            'is_package' => false,
            'pricing_mode' => $pricingMode === self::PRICING_MODE_AUTO ? self::PRICING_MODE_PER_TEST : $pricingMode,
            'max_tat' => $maxTat,
            'element_prices' => $elementPrices,
        ];
    }

    /**
     * Build commercial quotation_details row payload(s) from one prep UI line.
     *
     * Amspec UAE package math (default AUTO / PER_PACKAGE):
     *   Total price = No. of samples × Unit price   (ONCE for the sample package)
     * Individual selected tests stay on that same line for LOQ / MU / method / PDF
     * scope — they do NOT each receive a separate billed unit price.
     *
     * Only PRICING_MODE_PER_TEST splits into one billed row per analysis element.
     *
     * @param  list<string>  $elementIds
     * @param  array<string, string>  $loqOverrides  element_id => loq string
     * @return list<array<string, mixed>>
     */
    public function normalizeManualDetailRows(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
        int $quantity,
        float $unitPrice,
        float $tax,
        string $accreditedAnalytes,
        string $subcontractedAnalytes,
        string $defaultAnalytes,
        string $subAccAnalytes,
        string $pricingMode = self::PRICING_MODE_AUTO,
        array $loqOverrides = [],
        string $showLoqAnalytes = '',
        string $showMuAnalytes = '',
        string $quantityRequired = '',
    ): array {
        unset($tax);
        $pricingMode = $this->normalizePricingMode($pricingMode);
        $quantityRequired = trim($quantityRequired);
        $suggestion = $this->suggestManualLinePricing(
            $header,
            $sampleTypeId,
            $analysisTypeIdsCsv,
            $elementIds,
            $pricingMode,
        );

        $resolvedUnitPrice = $unitPrice > 0 ? $unitPrice : (float) $suggestion['unit_price'];
        $resolvedTax = (float) $suggestion['tax'];
        $showLoqList = array_values(array_filter(array_map('trim', explode(',', $showLoqAnalytes))));
        $showMuList = array_values(array_filter(array_map('trim', explode(',', $showMuAnalytes))));

        if ($elementIds === []) {
            return [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                // Free-text package note (e.g. "Per Sample Swab") — not a price multiplier.
                'quantity_required' => $quantityRequired !== '' ? $quantityRequired : null,
                'unit_price' => $resolvedUnitPrice,
                'tax' => $resolvedTax,
                'part_no' => $analysisTypeIdsCsv,
                'accredited_analytes' => $accreditedAnalytes,
                'subcontracted_analytes' => $subcontractedAnalytes,
                'default_analytes' => $defaultAnalytes,
                'sub_acc_analytes' => $subAccAnalytes,
                'is_package' => false,
                'loq' => '',
                'mu_percent' => '',
                'show_loq_analytes' => $showLoqAnalytes,
                'show_mu_analytes' => $showMuAnalytes,
                'test_method' => '',
                'tat' => $suggestion['max_tat'],
                'description' => '',
            ]];
        }

        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        if ($analysisTypeIds === []) {
            $analysisTypeIds = AnalysisElements::query()
                ->whereIn('id', $elementIds)
                ->pluck('analysis_type_id')
                ->map(fn ($id) => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';
        $partNoCsv = implode(',', $analysisTypeIds);

        // Default Amspec path: one commercial line for the whole sample package.
        if ($pricingMode !== self::PRICING_MODE_PER_TEST) {
            $packageMatch = $this->findPackageMatch($header, $sampleTypeId, $partNoCsv, $elementIds);
            $isPackage = $packageMatch !== null
                && (
                    (bool) ($suggestion['is_package'] ?? false)
                    || $pricingMode === self::PRICING_MODE_PER_PACKAGE
                    || $unitPrice <= 0
                    || abs($unitPrice - (float) $packageMatch['item']->selling_price) < 0.02
                );

            if ($isPackage && $unitPrice <= 0) {
                $resolvedUnitPrice = (float) $packageMatch['item']->selling_price;
            }

            $csvs = $this->exclusiveParameterCsvs(
                $elementIds,
                $accreditedAnalytes,
                $subcontractedAnalytes,
                $defaultAnalytes,
                $subAccAnalytes,
            );

            $analysisTypeName = $defaultAnalysisTypeId !== ''
                ? (string) (AnalysisType::find($defaultAnalysisTypeId)?->name ?? 'Analysis')
                : 'Analysis';

            $packageTax = $resolvedTax;
            if ($isPackage && $packageMatch !== null) {
                $packageTax = $packageMatch['item']->vat
                    ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                    : 0.0;
            }

            // Aggregate LOQ/MU display hints for PDF toggles; per-test metrics stay on elements.
            $loqParts = [];
            $muParts = [];
            $methodParts = [];
            foreach ($elementIds as $elementId) {
                $element = AnalysisElements::query()->find($elementId);
                $metrics = $element
                    ? $this->uncertaintyBudgetResolver->resolveLabMetricsForElement($element)
                    : ['loq' => '', 'mu_percent' => '', 'test_method' => ''];
                $loqVal = array_key_exists($elementId, $loqOverrides)
                    ? trim((string) $loqOverrides[$elementId])
                    : (string) ($metrics['loq'] ?? '');
                if ($loqVal !== '') {
                    $loqParts[] = $loqVal;
                }
                $muVal = (string) ($metrics['mu_percent'] ?? '');
                if ($muVal !== '') {
                    $muParts[] = $muVal;
                }
                $methodVal = (string) ($metrics['test_method'] ?? '');
                if ($methodVal !== '') {
                    $methodParts[] = $methodVal;
                }
            }

            return [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                // Total = quantity (no. of samples) × this unit_price — once for the package.
                'quantity_required' => $quantityRequired !== '' ? $quantityRequired : null,
                'unit_price' => $resolvedUnitPrice,
                'tax' => $packageTax,
                'part_no' => $partNoCsv !== '' ? $partNoCsv : $analysisTypeIdsCsv,
                'accredited_analytes' => $csvs['accredited'],
                'subcontracted_analytes' => $csvs['subcontracted'],
                'default_analytes' => $csvs['default'],
                'sub_acc_analytes' => $csvs['sub_acc'],
                'is_package' => $isPackage,
                'loq' => implode('; ', array_values(array_unique($loqParts))),
                'mu_percent' => implode('; ', array_values(array_unique($muParts))),
                'show_loq_analytes' => implode(',', array_values(array_intersect($showLoqList, $elementIds))),
                'show_mu_analytes' => implode(',', array_values(array_intersect($showMuList, $elementIds))),
                'test_method' => implode('; ', array_values(array_unique($methodParts))),
                'tat' => $suggestion['max_tat'],
                'description' => $isPackage
                    ? $analysisTypeName.' package ('.count($elementIds).' parameters)'
                    : '',
            ]];
        }

        // Explicit per-test billing only (not the Amspec package default).
        $pricelist = $this->resolvePricelist($header->crm_customer_id, $header);
        $accreditedList = array_values(array_filter(array_map('trim', explode(',', $accreditedAnalytes))));
        $subcontractedList = array_values(array_filter(array_map('trim', explode(',', $subcontractedAnalytes))));
        $subAccList = array_values(array_filter(array_map('trim', explode(',', $subAccAnalytes))));

        $pricelistPrices = [];
        $pricelistTotal = 0.0;
        foreach ($elementIds as $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
            $price = $this->suggestPrefillUnitPrice(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId,
            );
            $pricelistPrices[$elementId] = $price;
            $pricelistTotal += $price;
        }
        $pricelistTotal = round($pricelistTotal, 2);
        $usePerTestPricelist = $resolvedUnitPrice <= 0 || abs($resolvedUnitPrice - $pricelistTotal) < 0.02;

        $rows = [];
        $allocatedOverride = 0.0;
        $lastIndex = count($elementIds) - 1;

        foreach ($elementIds as $index => $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
            $metrics = $element
                ? $this->uncertaintyBudgetResolver->resolveLabMetricsForElement($element)
                : ['loq' => '', 'mu_percent' => '', 'test_method' => ''];

            $loq = array_key_exists($elementId, $loqOverrides)
                ? trim((string) $loqOverrides[$elementId])
                : (string) ($metrics['loq'] ?? '');

            if ($usePerTestPricelist) {
                $rowPrice = $pricelistPrices[$elementId];
                $rowTax = $resolvedTax > 0
                    ? $resolvedTax
                    : $this->quotationLineTaxResolver->resolveLineTaxPercent(
                        $pricelist,
                        $sampleTypeId,
                        $analysisTypeId,
                        $elementId,
                    );
            } elseif ($pricelistTotal > 0) {
                if ($index === $lastIndex) {
                    $rowPrice = round($resolvedUnitPrice - $allocatedOverride, 2);
                } else {
                    $rowPrice = round($resolvedUnitPrice * ($pricelistPrices[$elementId] / $pricelistTotal), 2);
                    $allocatedOverride += $rowPrice;
                }
                $rowTax = $resolvedTax;
            } else {
                $pricePerRow = round($resolvedUnitPrice / count($elementIds), 2);
                $remainder = round($resolvedUnitPrice - ($pricePerRow * count($elementIds)), 2);
                $rowPrice = $pricePerRow + ($index === 0 ? $remainder : 0.0);
                $rowTax = $resolvedTax;
            }

            $isAcc = in_array($elementId, $accreditedList, true);
            $isSub = in_array($elementId, $subcontractedList, true);
            $isBoth = in_array($elementId, $subAccList, true);

            $rows[] = [
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                'quantity_required' => $quantityRequired !== '' ? $quantityRequired : null,
                'unit_price' => $rowPrice,
                'tax' => $rowTax,
                'part_no' => $analysisTypeId,
                'accredited_analytes' => ($isAcc && ! $isSub && ! $isBoth) ? $elementId : '',
                'subcontracted_analytes' => ($isSub && ! $isAcc && ! $isBoth) ? $elementId : '',
                'default_analytes' => (! $isAcc && ! $isSub && ! $isBoth) ? $elementId : '',
                'sub_acc_analytes' => $isBoth ? $elementId : '',
                'is_package' => false,
                'loq' => $loq,
                'mu_percent' => (string) ($metrics['mu_percent'] ?? ''),
                'show_loq_analytes' => in_array($elementId, $showLoqList, true) ? $elementId : '',
                'show_mu_analytes' => in_array($elementId, $showMuList, true) ? $elementId : '',
                'test_method' => (string) ($metrics['test_method'] ?? ''),
                'tat' => $this->elementTat($element, $analysisTypeId),
                'description' => '',
            ];
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    public function collectElementIdsFromDetail(QuotationDetails $detail): array
    {
        $ids = [];

        foreach (['default_analytes', 'accredited_analytes', 'subcontracted_analytes', 'sub_acc_analytes'] as $field) {
            $raw = $detail->{$field};
            if (! $raw) {
                continue;
            }

            foreach (explode(',', $raw) as $id) {
                $id = trim($id);
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    public function persistInvoicableItemOnDetail(QuotationDetails $detail, ?string $analysisTypeId): void
    {
        if (! $analysisTypeId) {
            return;
        }

        $priceData = getPriceForAnalysisType($analysisTypeId);
        if ($priceData && ! empty($priceData['invoicable_item_id'])) {
            $detail->invoicable_item_id = $priceData['invoicable_item_id'];
        }
    }

    /**
     * @param  list<string>  $elementIds
     * @return array{item: \App\Models\Billing\PricelistItem, covered_element_ids: list<string>, pricelist?: ?Pricelist}|null
     */
    private function findPackageMatch(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
    ): ?array {
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $pricelist = $this->resolvePricelist($header->crm_customer_id, $header);

        foreach ($analysisTypeIds as $analysisTypeId) {
            $match = $this->acceptanceFormPricingService->resolvePackageForGroup(
                $header->crm_customer_id,
                $sampleTypeId,
                $analysisTypeId,
                $elementIds,
                $pricelist,
            );
            if ($match !== null) {
                return $match;
            }
        }

        if ($analysisTypeIds === [] && $elementIds !== []) {
            $element = AnalysisElements::query()->find($elementIds[0]);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? '');
            if ($analysisTypeId !== '') {
                return $this->acceptanceFormPricingService->resolvePackageForGroup(
                    $header->crm_customer_id,
                    $sampleTypeId,
                    $analysisTypeId,
                    $elementIds,
                    $pricelist,
                );
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $elementIds
     */
    public function maxTatForElements(array $elementIds, string $analysisTypeIdsCsv = ''): ?int
    {
        $max = null;

        foreach ($elementIds as $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? '');
            $tat = $this->elementTat($element, $analysisTypeId);
            if ($tat === null) {
                continue;
            }
            $max = $max === null ? $tat : max($max, $tat);
        }

        if ($max !== null) {
            return $max;
        }

        foreach (array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))) as $analysisTypeId) {
            $type = AnalysisType::query()->find($analysisTypeId);
            if ($type && is_numeric($type->reporting_time) && (int) $type->reporting_time > 0) {
                $tat = (int) $type->reporting_time;
                $max = $max === null ? $tat : max($max, $tat);
            }
        }

        return $max;
    }

    private function elementTat(?AnalysisElements $element, string $analysisTypeId = ''): ?int
    {
        if ($element && is_numeric($element->reporting_time) && (int) $element->reporting_time > 0) {
            return (int) $element->reporting_time;
        }

        $typeId = $analysisTypeId !== '' ? $analysisTypeId : (string) ($element?->analysis_type_id ?? '');
        if ($typeId === '') {
            return null;
        }

        $type = AnalysisType::query()->find($typeId);
        if ($type && is_numeric($type->reporting_time) && (int) $type->reporting_time > 0) {
            return (int) $type->reporting_time;
        }

        return null;
    }

    private function normalizePricingMode(string $mode): string
    {
        $mode = trim($mode);

        return in_array($mode, [
            self::PRICING_MODE_AUTO,
            self::PRICING_MODE_PER_TEST,
            self::PRICING_MODE_PER_PACKAGE,
        ], true) ? $mode : self::PRICING_MODE_AUTO;
    }
}
