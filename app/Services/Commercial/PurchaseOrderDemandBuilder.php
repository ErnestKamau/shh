<?php

namespace App\Services\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\SampleSubmissionRequest;

/**
 * Turns requested samples into PO demand. One item = one sample of one analysis type
 * (package) for a sample type, so rows are grouped by sample type + analysis type and
 * their sample counts summed.
 */
final class PurchaseOrderDemandBuilder
{
    /**
     * @return list<PurchaseOrderDemandItem>
     */
    public function fromEnquiry(SampleSubmissionRequest $enquiry): array
    {
        $rows = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];

        if ($rows !== []) {
            return $this->fromRows($rows);
        }

        $enquiry->loadMissing('requestedAnalyses');

        $grouped = [];
        foreach ($enquiry->requestedAnalyses as $analysis) {
            $sampleTypeId = $this->idOrNull($analysis->sample_type_id ?? $enquiry->sample_type_id);
            $analysisTypeId = $this->idOrNull($analysis->analysis_type_id ?? $enquiry->matrix_id);
            $key = self::keyFor($sampleTypeId, $analysisTypeId);
            $quantity = max(1, (int) ($analysis->number_of_samples ?? $enquiry->number_of_samples ?? 1));

            $grouped[$key] = [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'number_of_samples' => max($grouped[$key]['number_of_samples'] ?? 0, $quantity),
            ];
        }

        return $this->fromRows(array_values($grouped));
    }

    /**
     * @param  iterable<int, array<string, mixed>>  $rows  Each row: sample_type_id, analysis_type_id, number_of_samples (or quantity).
     * @return list<PurchaseOrderDemandItem>
     */
    public function fromRows(iterable $rows): array
    {
        $totals = [];

        foreach ($rows as $row) {
            $quantity = (int) ($row['number_of_samples'] ?? $row['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $sampleTypeId = $this->idOrNull($row['sample_type_id'] ?? null);
            $analysisTypeId = $this->idOrNull($row['analysis_type_id'] ?? null);
            $key = self::keyFor($sampleTypeId, $analysisTypeId);

            $totals[$key] ??= ['sample_type_id' => $sampleTypeId, 'analysis_type_id' => $analysisTypeId, 'quantity' => 0];
            $totals[$key]['quantity'] += $quantity;
        }

        $items = [];
        foreach ($totals as $key => $total) {
            $items[] = new PurchaseOrderDemandItem(
                $key,
                $total['sample_type_id'],
                $total['analysis_type_id'] !== null ? [$total['analysis_type_id']] : [],
                $total['quantity'],
            );
        }

        return $items;
    }

    public static function keyFor(?string $sampleTypeId, ?string $analysisTypeId): string
    {
        return ($sampleTypeId ?? '-').'|'.($analysisTypeId ?? '-');
    }

    private function idOrNull(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
