<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use Illuminate\Support\Facades\DB;

final class QuotationPricelistPopulateService
{
    public function __construct(
        private readonly QuotationPricingResolver $quotationPricingResolver,
        private readonly QuotationLineTaxResolver $quotationLineTaxResolver,
        private readonly QuotationLabSectionScope $quotationLabSectionScope,
    ) {}

    /**
     * Append quotation lines from an eligible pricelist (never replaces existing lines).
     *
     * @return array{created: int, billing_mode: string}
     */
    public function appendFromPricelist(QuotationHeader $header, Pricelist $pricelist): array
    {
        $billingMode = $this->resolveBillingMode($pricelist);
        $pricingMode = $billingMode === Pricelist::BILLING_MODE_PER_TEST
            ? QuotationPricingResolver::PRICING_MODE_PER_TEST
            : QuotationPricingResolver::PRICING_MODE_PER_PACKAGE;

        $pricelist->loadMissing(['items.packageElements', 'items.analysisElement']);

        $items = $pricelist->items
            ->filter(fn (PricelistItem $item): bool => (bool) ($item->active ?? true))
            ->sortBy(fn (PricelistItem $item): array => [
                mb_strtolower((string) ($item->sampleType?->name ?? '')),
                (int) ($item->level ?? 0),
            ])
            ->values();

        $created = 0;

        DB::transaction(function () use ($header, $items, $pricingMode, &$created): void {
            foreach ($items as $item) {
                $rows = $this->buildRowsForItem($header, $item, $pricingMode);
                foreach ($rows as $rowPayload) {
                    $this->persistDetailRow($header, $rowPayload);
                    $created++;
                }
            }
        });

        return [
            'created' => $created,
            'billing_mode' => $billingMode,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRowsForItem(
        QuotationHeader $header,
        PricelistItem $item,
        string $pricingMode,
    ): array {
        $sampleTypeId = (string) ($item->sample_type_id ?? '');
        if ($sampleTypeId === '') {
            return [];
        }

        if ($pricingMode === QuotationPricingResolver::PRICING_MODE_PER_TEST) {
            $elementId = (string) ($item->analysis_element_id ?? '');
            if ($elementId === '') {
                return [];
            }

            $element = AnalysisElements::query()->find($elementId);
            $analysisTypeId = (string) ($element?->analysis_type_id ?? $item->analysis_id ?? '');
            $tax = (bool) ($item->vat ?? false)
                ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
                : 0.0;

            return $this->quotationPricingResolver->normalizeManualDetailRows(
                $header,
                $sampleTypeId,
                $analysisTypeId,
                [$elementId],
                1,
                (float) $item->selling_price,
                $tax,
                '',
                '',
                $elementId,
                '',
                QuotationPricingResolver::PRICING_MODE_PER_TEST,
            );
        }

        $elementIds = $item->coveredElementIds();
        if ($elementIds === [] && ! empty($item->analysis_element_id)) {
            $elementIds = [(string) $item->analysis_element_id];
        }
        if ($elementIds === []) {
            return [];
        }

        $analysisTypeIds = AnalysisElements::query()
            ->whereIn('id', $elementIds)
            ->pluck('analysis_type_id')
            ->map(fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $partNo = $analysisTypeIds !== []
            ? implode(',', $analysisTypeIds)
            : (string) ($item->analysis_id ?? '');

        try {
            $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysisTypeIds);
            $this->quotationLabSectionScope->assertElementsAllowed($header, $elementIds);
        } catch (\RuntimeException) {
            return [];
        }

        $tax = (bool) ($item->vat ?? false)
            ? $this->quotationLineTaxResolver->activeTaxRegimePercent()
            : 0.0;

        return $this->quotationPricingResolver->normalizeManualDetailRows(
            $header,
            $sampleTypeId,
            $partNo,
            $elementIds,
            1,
            (float) $item->selling_price,
            $tax,
            '',
            '',
            implode(',', $elementIds),
            '',
            QuotationPricingResolver::PRICING_MODE_PER_PACKAGE,
        );
    }

    /**
     * @param  array<string, mixed>  $rowPayload
     */
    private function persistDetailRow(QuotationHeader $header, array $rowPayload): void
    {
        $detail = new QuotationDetails();
        $detail->sample_type = $rowPayload['sample_type'];
        $detail->unit_price = $rowPayload['unit_price'];
        $detail->tax = $rowPayload['tax'];
        $detail->part_no = $rowPayload['part_no'];
        $detail->quantity = $rowPayload['quantity'];
        $detail->quantity_required = $rowPayload['quantity_required'] ?? null;
        $detail->accredited_analytes = $rowPayload['accredited_analytes'];
        $detail->subcontracted_analytes = $rowPayload['subcontracted_analytes'] ?? '';
        $detail->default_analytes = $rowPayload['default_analytes'];
        $detail->sub_acc_analytes = $rowPayload['sub_acc_analytes'];
        $detail->is_package = (bool) ($rowPayload['is_package'] ?? false);
        $detail->loq = (string) ($rowPayload['loq'] ?? '');
        $detail->mu_percent = (string) ($rowPayload['mu_percent'] ?? '');
        $detail->show_loq_analytes = null;
        $detail->show_mu_analytes = null;
        $detail->test_method = (string) ($rowPayload['test_method'] ?? '');
        $detail->tat = $rowPayload['tat'] ?? null;
        $detail->description = (string) ($rowPayload['description'] ?? '');
        $detail->quotation_header_id = $header->id;

        $analysisTypeIds = array_filter(explode(',', (string) $rowPayload['part_no']));
        $this->quotationPricingResolver->persistInvoicableItemOnDetail(
            $detail,
            $analysisTypeIds[0] ?? null,
        );
        $detail->save();

        QuotationDetailAnalysisSplit::syncForDetail(
            (string) $detail->id,
            $analysisTypeIds,
        );
    }

    private function resolveBillingMode(Pricelist $pricelist): string
    {
        $mode = strtolower(trim((string) ($pricelist->billing_mode ?? '')));
        if (in_array($mode, [Pricelist::BILLING_MODE_PACKAGE, Pricelist::BILLING_MODE_PER_TEST], true)) {
            return $mode;
        }

        $hasPackage = $pricelist->items->contains(fn (PricelistItem $item): bool => (bool) ($item->is_package ?? false));

        return $hasPackage ? Pricelist::BILLING_MODE_PACKAGE : Pricelist::BILLING_MODE_PER_TEST;
    }
}
