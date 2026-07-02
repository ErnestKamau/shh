<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Models\Billing\Pricelist;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;

class QuotationPricingResolver
{
    public function __construct(
        private readonly AcceptanceFormPricingService $acceptanceFormPricingService,
        private readonly QuotationLineTaxResolver $quotationLineTaxResolver,
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
     * Sum of per-test pricelist prices for the selected elements.
     *
     * @param  list<string>  $elementIds
     */
    public function suggestLineUnitPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
    ): float {
        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';
        $total = 0.0;

        if ($elementIds !== []) {
            foreach ($elementIds as $elementId) {
                $element = AnalysisElements::query()->find($elementId);
                if (! $element) {
                    continue;
                }

                $total += $this->suggestPrefillUnitPrice(
                    $pricelist,
                    $sampleTypeId,
                    (string) ($element->analysis_type_id ?: $defaultAnalysisTypeId),
                    (string) $elementId,
                );
            }

            return $total;
        }

        foreach ($analysisTypeIds as $analysisTypeId) {
            $elements = AnalysisElements::query()
                ->where('analysis_type_id', $analysisTypeId)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            foreach ($elements as $elementId) {
                $total += $this->suggestPrefillUnitPrice(
                    $pricelist,
                    $sampleTypeId,
                    $analysisTypeId,
                    $elementId,
                );
            }
        }

        return $total;
    }

    /**
     * @param  list<string>  $elementIds
     * @return array{unit_price: float, tax: float, source: string, hint: string}
     */
    public function suggestManualLinePricing(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds,
    ): array {
        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';

        if ($elementIds === []) {
            return [
                'unit_price' => 0.0,
                'tax' => 0.0,
                'source' => 'none',
                'hint' => 'Select tests in Description to load pricelist total.',
            ];
        }

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
        ];
    }

    /**
     * One quotation detail row per selected test (element).
     *
     * @param  list<string>  $elementIds
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
    ): array {
        if ($elementIds === []) {
            return [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice > 0 ? $unitPrice : $this->suggestLineUnitPrice($header, $sampleTypeId, $analysisTypeIdsCsv, []),
                'tax' => $tax,
                'part_no' => $analysisTypeIdsCsv,
                'accredited_analytes' => $accreditedAnalytes,
                'subcontracted_analytes' => $subcontractedAnalytes,
                'default_analytes' => $defaultAnalytes,
                'sub_acc_analytes' => $subAccAnalytes,
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
        $usePerTestPricelist = $unitPrice <= 0 || abs($unitPrice - $pricelistTotal) < 0.02;

        $rows = [];
        $allocatedOverride = 0.0;
        $lastIndex = count($elementIds) - 1;

        foreach ($elementIds as $index => $elementId) {
            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);

            if ($usePerTestPricelist) {
                $rowPrice = $pricelistPrices[$elementId];
                $rowTax = $tax > 0
                    ? $tax
                    : $this->quotationLineTaxResolver->resolveLineTaxPercent(
                        $pricelist,
                        $sampleTypeId,
                        $analysisTypeId,
                        $elementId,
                    );
            } elseif ($pricelistTotal > 0) {
                if ($index === $lastIndex) {
                    $rowPrice = round($unitPrice - $allocatedOverride, 2);
                } else {
                    $rowPrice = round($unitPrice * ($pricelistPrices[$elementId] / $pricelistTotal), 2);
                    $allocatedOverride += $rowPrice;
                }
                $rowTax = $tax;
            } else {
                $pricePerRow = round($unitPrice / count($elementIds), 2);
                $remainder = round($unitPrice - ($pricePerRow * count($elementIds)), 2);
                $rowPrice = $pricePerRow + ($index === 0 ? $remainder : 0.0);
                $rowTax = $tax;
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
}
