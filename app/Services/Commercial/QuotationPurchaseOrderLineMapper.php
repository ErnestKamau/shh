<?php

namespace App\Services\Commercial;

use App\QuotationDetails;
use App\QuotationHeader;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

/**
 * Turns quotation lines into PO line drafts: same sample type / analysis keys,
 * gross (tax-inclusive) unit price, quantity as quoted.
 */
final class QuotationPurchaseOrderLineMapper
{
    /**
     * Complete, non-superseded quotations a PO can be raised against.
     *
     * @return EloquentCollection<int, QuotationHeader>
     */
    public function quotationsForCustomer(string $customerId): EloquentCollection
    {
        return QuotationHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('status', QuotationApprovalService::HEADER_STATUS_COMPLETE)
            ->whereNull('superseded_by_quotation_header_id')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'quote_number', 'currency_id', 'created_at', 'crm_customer_id']);
    }

    /**
     * @return list<array{
     *     quotation_detail_id: string,
     *     description: string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_ids: list<string>,
     *     is_package: bool,
     *     ordered_qty: int,
     *     unit_price_gross: float,
     *     quoted_unit_price_gross: float,
     *     notify_remaining_qty: ?int
     * }>
     */
    public function draftsForQuotation(QuotationHeader $quotation): array
    {
        $quotation->loadMissing('details.sampletype');

        return $quotation->details
            ->map(fn (QuotationDetails $detail): array => $this->draftFromDetail($detail))
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     quotation_detail_id: string,
     *     description: string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_ids: list<string>,
     *     is_package: bool,
     *     ordered_qty: int,
     *     unit_price_gross: float,
     *     quoted_unit_price_gross: float,
     *     notify_remaining_qty: ?int
     * }
     */
    public function draftFromDetail(QuotationDetails $detail): array
    {
        $gross = $this->grossUnitPrice($detail);
        $sampleTypeId = $this->sampleTypeId($detail);

        return [
            'quotation_detail_id' => (string) $detail->id,
            'description' => $this->description($detail),
            'sample_type_id' => $sampleTypeId,
            'sample_type_name' => $sampleTypeId !== null ? ($detail->sampletype?->name ?? null) : null,
            'analysis_type_ids' => $this->analysisTypeIds($detail),
            'is_package' => (bool) $detail->is_package,
            'ordered_qty' => $this->quantity($detail),
            'unit_price_gross' => $gross,
            'quoted_unit_price_gross' => $gross,
            'notify_remaining_qty' => null,
        ];
    }

    public function quantity(QuotationDetails $detail): int
    {
        return max(1, (int) round((float) ($detail->quantity ?? 1)));
    }

    public function grossUnitPrice(QuotationDetails $detail): float
    {
        $net = (float) ($detail->unit_price ?? 0);
        $taxPercent = max(0.0, (float) ($detail->tax ?? 0));

        return round($net * (1 + $taxPercent / 100), 2);
    }

    /**
     * @return list<string>
     */
    public function analysisTypeIds(QuotationDetails $detail): array
    {
        return collect(explode(',', (string) ($detail->part_no ?? '')))
            ->map(fn (string $id): string => trim($id))
            ->filter(fn (string $id): bool => Str::isUuid($id))
            ->unique()
            ->values()
            ->all();
    }

    public function sampleTypeId(QuotationDetails $detail): ?string
    {
        $sampleTypeId = trim((string) ($detail->sample_type ?? ''));

        return Str::isUuid($sampleTypeId) ? $sampleTypeId : null;
    }

    public function description(QuotationDetails $detail): string
    {
        $description = trim((string) ($detail->item_name ?: $detail->description ?: 'Quotation line'));

        return Str::limit($description, 497);
    }
}
