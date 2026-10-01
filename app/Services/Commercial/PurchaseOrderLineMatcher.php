<?php

namespace App\Services\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\Commercial\CustomerPurchaseOrderLine;

/**
 * Picks the PO line that covers a demand item, using stable keys
 * (sample type + package / analysis types) rather than quotation detail ids,
 * so quotation revisions do not orphan PO lines.
 *
 * Preference order:
 *  1. analysis set equal to the demand's, then the smallest superset, then lines with no analysis set (any analysis);
 *  2. a line for the same sample type before a line with no sample type;
 *  3. a line with remaining quantity before an exhausted one;
 *  4. lowest line number.
 */
final class PurchaseOrderLineMatcher
{
    private const WILDCARD_ANALYSIS_SCORE = 1000;

    /**
     * @param  iterable<int, CustomerPurchaseOrderLine>  $lines
     */
    public function match(iterable $lines, PurchaseOrderDemandItem $demand): ?CustomerPurchaseOrderLine
    {
        $best = null;
        $bestRank = null;

        foreach ($lines as $line) {
            $rank = $this->rank($line, $demand);
            if ($rank === null) {
                continue;
            }

            if ($bestRank === null || $rank < $bestRank) {
                $best = $line;
                $bestRank = $rank;
            }
        }

        return $best;
    }

    /**
     * @return list<int>|null Lexicographically comparable rank, or null when the line cannot cover the demand.
     */
    private function rank(CustomerPurchaseOrderLine $line, PurchaseOrderDemandItem $demand): ?array
    {
        $lineSampleType = trim((string) ($line->sample_type_id ?? ''));
        $demandSampleType = trim((string) ($demand->sampleTypeId ?? ''));

        if ($lineSampleType !== '' && $demandSampleType !== '' && $lineSampleType !== $demandSampleType) {
            return null;
        }

        $analysisScore = $this->analysisScore($line->analysisTypeIdList(), $demand->analysisTypeIds);
        if ($analysisScore === null) {
            return null;
        }

        $sampleTypeScore = ($lineSampleType !== '' && $lineSampleType === $demandSampleType) ? 0 : 1;
        $exhaustedScore = (int) $line->remaining_qty > 0 ? 0 : 1;

        return [$analysisScore, $sampleTypeScore, $exhaustedScore, (int) $line->line_no];
    }

    /**
     * @param  list<string>  $lineIds
     * @param  list<string>  $demandIds
     */
    private function analysisScore(array $lineIds, array $demandIds): ?int
    {
        if ($lineIds === []) {
            return self::WILDCARD_ANALYSIS_SCORE;
        }

        if ($demandIds === []) {
            return null;
        }

        if (array_diff($demandIds, $lineIds) !== []) {
            return null;
        }

        return count($lineIds) - count($demandIds);
    }
}
