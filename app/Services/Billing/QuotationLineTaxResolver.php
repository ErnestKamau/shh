<?php

namespace App\Services\Billing;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\TaxRegime;

final class QuotationLineTaxResolver
{
    public function activeTaxRegimePercent(): float
    {
        $regime = TaxRegime::query()->where('active', 1)->first();

        return $regime !== null ? (float) $regime->value : 0.0;
    }

    public function resolveLineTaxPercent(
        ?Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId = null,
    ): float {
        if ($pricelist === null) {
            return 0.0;
        }

        $item = $this->findMatchingPricelistItem(
            $pricelist,
            $sampleTypeId,
            $analysisTypeId,
            $analysisElementId,
        );

        if ($item === null || ! (bool) $item->vat) {
            return 0.0;
        }

        return $this->activeTaxRegimePercent();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyTaxToLines(?Pricelist $pricelist, array $lines, bool $overwrite = true): array
    {
        return array_map(function (array $line) use ($pricelist, $overwrite): array {
            if (! $overwrite && isset($line['tax']) && (float) $line['tax'] > 0) {
                return $line;
            }

            $line['tax'] = $this->resolveLineTaxPercent(
                $pricelist,
                isset($line['sample_type_id']) ? (string) $line['sample_type_id'] : null,
                (string) ($line['analysis_type_id'] ?? ''),
                ! empty($line['analysis_element_id']) ? (string) $line['analysis_element_id'] : null,
            );

            return $line;
        }, $lines);
    }

    private function findMatchingPricelistItem(
        Pricelist $pricelist,
        ?string $sampleTypeId,
        string $analysisTypeId,
        ?string $analysisElementId,
    ): ?PricelistItem {
        $query = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1);

        if ($sampleTypeId !== null && $sampleTypeId !== '') {
            $query->where('sample_type_id', $sampleTypeId);
        }

        if ($analysisElementId !== null && $analysisElementId !== '') {
            $item = (clone $query)->where('analysis_element_id', $analysisElementId)->first();
            if ($item !== null) {
                return $item;
            }

            $item = PricelistItem::query()
                ->where('pricelist_id', $pricelist->id)
                ->where('active', 1)
                ->where('analysis_element_id', $analysisElementId)
                ->first();
            if ($item !== null) {
                return $item;
            }
        }

        if ($analysisTypeId !== '') {
            return (clone $query)->where('analysis_id', $analysisTypeId)->first();
        }

        return null;
    }
}
