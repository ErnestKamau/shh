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
        private readonly AcceptanceFormPricingService $acceptanceFormPricingService
    ) {}

    public function resolvePricelist(?string $customerId): ?Pricelist
    {
        return $this->acceptanceFormPricingService->resolvePricelist($customerId);
    }

    /**
     * @return array{unit_price: float, source: string, invoicable_item_id: ?string}
     */
    public function resolveElementPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
        ?float $manualLinePrice = null,
        bool $singleElementOnLine = false
    ): array {
        if ($manualLinePrice !== null && $singleElementOnLine && $manualLinePrice > 0) {
            return [
                'unit_price' => $manualLinePrice,
                'source' => 'manual',
                'invoicable_item_id' => null,
            ];
        }

        $pricelist = $this->resolvePricelist($header->crm_customer_id);
        $pricelistPrice = $this->acceptanceFormPricingService->resolveLinePrice(
            $pricelist,
            $sampleTypeId,
            $analysisTypeId,
            $analysisElementId
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
     * Suggest a line unit price from selected analytes (sum of element prices).
     *
     * @param  list<string>  $elementIds
     */
    public function suggestLineUnitPrice(
        QuotationHeader $header,
        ?string $sampleTypeId,
        string $analysisTypeIdsCsv,
        array $elementIds
    ): float {
        $analysisTypeIds = array_filter(explode(',', $analysisTypeIdsCsv));
        $total = 0.0;

        if ($elementIds !== []) {
            foreach ($elementIds as $elementId) {
                $element = AnalysisElements::find($elementId);
                if (! $element) {
                    continue;
                }

                $resolved = $this->resolveElementPrice(
                    $header,
                    $sampleTypeId,
                    (string) $element->analysis_type_id,
                    (string) $elementId
                );
                $total += $resolved['unit_price'];
            }

            return $total;
        }

        foreach ($analysisTypeIds as $analysisTypeId) {
            $resolved = $this->resolveElementPrice(
                $header,
                $sampleTypeId,
                (string) $analysisTypeId
            );
            $total += $resolved['unit_price'];
        }

        return $total;
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
