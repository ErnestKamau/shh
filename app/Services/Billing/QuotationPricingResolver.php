<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;

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

    public function resolvePricelist(?string $customerId): ?Pricelist
    {
        return $this->acceptanceFormPricingService->resolvePricelist($customerId);
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

        $pricelist = $this->resolvePricelist($header->crm_customer_id);
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
     * @param  list<string>  $elementIds
     * @return array{unit_price: float, tax: float, source: string, hint: string, is_package: bool, pricing_mode: string, max_tat: int|null}
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
                'hint' => 'Select tests in Parameters to load pricelist total.',
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
            $unitPrice = round((float) $item->selling_price, 2);
            $tax = $item->vat
                ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                : 0.0;

            return [
                'unit_price' => $unitPrice,
                'tax' => round((float) $tax, 2),
                'source' => 'package',
                'hint' => sprintf(
                    'Package price for %d parameter(s): %s',
                    count($package['covered_element_ids']),
                    number_format($unitPrice, 2)
                ),
                'is_package' => true,
                'pricing_mode' => $pricingMode === self::PRICING_MODE_AUTO ? self::PRICING_MODE_PER_PACKAGE : $pricingMode,
                'max_tat' => $maxTat,
            ];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';

        $total = 0.0;
        $taxes = [];
        foreach ($elementIds as $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
            $total += $this->suggestPrefillUnitPrice(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId,
            );
            $taxes[] = $this->quotationLineTaxResolver->resolveLineTaxPercent(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId,
            );
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
        ];
    }

    /**
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
    ): array {
        $pricingMode = $this->normalizePricingMode($pricingMode);
        $suggestion = $this->suggestManualLinePricing(
            $header,
            $sampleTypeId,
            $analysisTypeIdsCsv,
            $elementIds,
            $pricingMode,
        );

        $usePackage = (bool) ($suggestion['is_package'] ?? false);
        $resolvedUnitPrice = $unitPrice > 0 ? $unitPrice : (float) $suggestion['unit_price'];
        $resolvedTax = $tax > 0 ? $tax : (float) $suggestion['tax'];
        $showLoqList = array_values(array_filter(array_map('trim', explode(',', $showLoqAnalytes))));
        $showMuList = array_values(array_filter(array_map('trim', explode(',', $showMuAnalytes))));

        if ($elementIds === []) {
            return [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
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

        if ($usePackage) {
            $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
            $primaryAnalysisTypeId = $analysisTypeIds[0] ?? '';
            $allIdsCsv = implode(',', $elementIds);

            return [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                'unit_price' => $resolvedUnitPrice,
                'tax' => $resolvedTax,
                'part_no' => $primaryAnalysisTypeId !== '' ? $primaryAnalysisTypeId : $analysisTypeIdsCsv,
                'accredited_analytes' => $accreditedAnalytes !== '' ? $accreditedAnalytes : $allIdsCsv,
                'subcontracted_analytes' => $subcontractedAnalytes,
                'default_analytes' => $defaultAnalytes !== '' ? $defaultAnalytes : $allIdsCsv,
                'sub_acc_analytes' => $subAccAnalytes,
                'is_package' => true,
                'loq' => '',
                'mu_percent' => '',
                'show_loq_analytes' => implode(',', array_values(array_intersect($showLoqList, $elementIds))),
                'show_mu_analytes' => implode(',', array_values(array_intersect($showMuList, $elementIds))),
                'test_method' => '',
                'tat' => $suggestion['max_tat'],
                'description' => sprintf('Package (%d parameters)', count($elementIds)),
            ]];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';
        $accreditedList = array_values(array_filter(array_map('trim', explode(',', $accreditedAnalytes))));
        $subcontractedList = array_values(array_filter(array_map('trim', explode(',', $subcontractedAnalytes))));
        $defaultList = array_values(array_filter(array_map('trim', explode(',', $defaultAnalytes))));
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

            $rows[] = [
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                'unit_price' => $rowPrice,
                'tax' => $rowTax,
                'part_no' => $analysisTypeId,
                'accredited_analytes' => in_array($elementId, $accreditedList, true) ? $elementId : '',
                'subcontracted_analytes' => in_array($elementId, $subcontractedList, true) ? $elementId : '',
                'default_analytes' => in_array($elementId, $defaultList, true) ? $elementId : $elementId,
                'sub_acc_analytes' => in_array($elementId, $subAccList, true) ? $elementId : '',
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
     * @return array{item: \App\Models\Billing\PricelistItem, covered_element_ids: list<string>}|null
     */
    private function findPackageMatch(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
    ): ?array {
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $pricelist = $this->resolvePricelist($header->crm_customer_id);

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
