<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
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
        return $this->acceptanceFormPricingService->resolveCustomerAssignedPricelist($customerId)
            ?? $this->acceptanceFormPricingService->resolvePricelist($customerId);
    }

    /**
     * @return array{unit_price: float, source: string}
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
            ];
        }

        return [
            'unit_price' => 0.0,
            'source' => 'none',
        ];
    }

    /**
     * @return array{unit_price: float, source: string}
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
        $suggestion = $this->suggestManualLinePricing(
            $header,
            $sampleTypeId,
            $analysisTypeIdsCsv,
            $elementIds,
        );

        return (float) $suggestion['unit_price'];
    }

    /**
     * @param  list<string>  $elementIds
     * @return array{unit_price: float, tax: float, source: string, hint: string, is_package: bool}
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
                'is_package' => false,
            ];
        }

        $packageMatch = $this->acceptanceFormPricingService->resolvePackageForGroup(
            (string) $header->crm_customer_id,
            $sampleTypeId,
            $defaultAnalysisTypeId,
            $elementIds,
            $pricelist,
        );

        if ($packageMatch !== null) {
            $packageItem = $packageMatch['item'];
            $covered = $packageMatch['covered_element_ids'];
            $packagePrice = (float) $packageItem->selling_price;
            $extras = array_values(array_diff($elementIds, $covered));
            $extraTotal = 0.0;
            foreach ($extras as $extraId) {
                $element = AnalysisElements::query()->find($extraId);
                $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
                $extraTotal += $this->suggestPrefillUnitPrice(
                    $packageMatch['pricelist'] ?? $pricelist,
                    $sampleTypeId,
                    $analysisTypeId,
                    (string) $extraId,
                );
            }

            $tax = $packageItem->vat
                ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                : 0.0;

            $total = round($packagePrice + $extraTotal, 2);
            $hint = $extras === []
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
                'unit_price' => $total,
                'tax' => round($tax, 2),
                'source' => 'pricelist_package',
                'hint' => $hint,
                'is_package' => $extras === [],
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
            'is_package' => false,
        ];
    }

    /**
     * Prefer a single package detail row when selected tests fully cover a pricelist package.
     * Extra uncovered tests remain as individual rows. Manual overrides still split across tests.
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
                'is_package' => false,
                'description' => '',
            ]];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', $analysisTypeIdsCsv))));
        $defaultAnalysisTypeId = $analysisTypeIds[0] ?? '';
        $accreditedList = array_values(array_filter(array_map('trim', explode(',', $accreditedAnalytes))));
        $subcontractedList = array_values(array_filter(array_map('trim', explode(',', $subcontractedAnalytes))));
        $defaultList = array_values(array_filter(array_map('trim', explode(',', $defaultAnalytes))));
        $subAccList = array_values(array_filter(array_map('trim', explode(',', $subAccAnalytes))));

        $packageMatch = $this->acceptanceFormPricingService->resolvePackageForGroup(
            (string) $header->crm_customer_id,
            $sampleTypeId,
            $defaultAnalysisTypeId,
            $elementIds,
            $pricelist,
        );

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
        $packagePrice = $packageMatch !== null ? (float) $packageMatch['item']->selling_price : 0.0;
        $usePackage = $packageMatch !== null
            && ($unitPrice <= 0 || abs($unitPrice - $packagePrice) < 0.02 || abs($unitPrice - $pricelistTotal) < 0.02);

        if ($usePackage && $packageMatch !== null) {
            $coveredElementIds = $packageMatch['covered_element_ids'];
            $extras = array_values(array_diff($elementIds, $coveredElementIds));
            $analysisTypeName = $defaultAnalysisTypeId !== ''
                ? (string) (AnalysisType::find($defaultAnalysisTypeId)?->name ?? 'Analysis')
                : 'Analysis';
            $packageTax = $tax > 0
                ? $tax
                : ($packageMatch['item']->vat
                    ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                    : 0.0);

            $rows = [[
                'sample_type' => $sampleTypeId,
                'quantity' => $quantity,
                'unit_price' => $packagePrice,
                'tax' => $packageTax,
                'part_no' => $defaultAnalysisTypeId,
                'accredited_analytes' => implode(',', $coveredElementIds),
                'subcontracted_analytes' => '',
                'default_analytes' => implode(',', $coveredElementIds),
                'sub_acc_analytes' => '',
                'is_package' => true,
                'description' => $analysisTypeName.' package ('.count($coveredElementIds).' parameters)',
            ]];

            foreach ($extras as $elementId) {
                $element = AnalysisElements::query()->find($elementId);
                $analysisTypeId = (string) ($element?->analysis_type_id ?? $defaultAnalysisTypeId);
                $rows[] = [
                    'sample_type' => $sampleTypeId,
                    'quantity' => $quantity,
                    'unit_price' => $pricelistPrices[$elementId] ?? 0.0,
                    'tax' => $tax > 0
                        ? $tax
                        : $this->quotationLineTaxResolver->resolveLineTaxPercent(
                            $pricelist,
                            $sampleTypeId,
                            $analysisTypeId,
                            $elementId,
                        ),
                    'part_no' => $analysisTypeId,
                    'accredited_analytes' => in_array($elementId, $accreditedList, true) ? $elementId : '',
                    'subcontracted_analytes' => in_array($elementId, $subcontractedList, true) ? $elementId : '',
                    'default_analytes' => in_array($elementId, $defaultList, true) ? $elementId : $elementId,
                    'sub_acc_analytes' => in_array($elementId, $subAccList, true) ? $elementId : '',
                    'is_package' => false,
                    'description' => '',
                ];
            }

            return $rows;
        }

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
                'is_package' => false,
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
}
