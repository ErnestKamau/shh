<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\SampleSubmissionRequest;

/**
 * Turns requested samples into PO demand, grouped by sample type + analysis type with sample
 * counts summed. When the PO prices a requested parameter per test (and has no package for that
 * analysis type), that parameter becomes its own demand item so each test draws on its own line.
 */
final class PurchaseOrderDemandBuilder
{
    public function __construct(private readonly PurchaseOrderLineMatcher $matcher = new PurchaseOrderLineMatcher) {}

    /**
     * @param  iterable<int, CustomerPurchaseOrderLine>|null  $lines  The PO's lines; null keeps one item per analysis type.
     * @return list<PurchaseOrderDemandItem>
     */
    public function fromEnquiry(SampleSubmissionRequest $enquiry, ?iterable $lines = null): array
    {
        $rows = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];

        if ($rows !== []) {
            return $this->fromRows($rows, $lines);
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

        return $this->fromRows(array_values($grouped), $lines);
    }

    /**
     * @param  iterable<int, array<string, mixed>>  $rows  Each row: sample_type_id, analysis_type_id, number_of_samples (or quantity);
     *                                                     optionally attributes.analysis_type_ids / attributes.analysis_element_ids or analysis_element_id.
     * @param  iterable<int, CustomerPurchaseOrderLine>|null  $lines
     * @return list<PurchaseOrderDemandItem>
     */
    public function fromRows(iterable $rows, ?iterable $lines = null): array
    {
        $lines = $lines !== null ? $this->listOf($lines) : null;
        $parsed = [];
        $multiTypeElementIds = [];

        foreach ($rows as $row) {
            $quantity = (int) ($row['number_of_samples'] ?? $row['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $elementIds = $this->rowElementIds($row);
            $analysisTypeIds = $this->rowAnalysisTypeIds($row);
            $parsed[] = [
                'sample_type_id' => $this->idOrNull($row['sample_type_id'] ?? null),
                'analysis_type_ids' => $analysisTypeIds,
                'element_ids' => $elementIds,
                'quantity' => $quantity,
            ];

            if (count($analysisTypeIds) > 1) {
                array_push($multiTypeElementIds, ...$elementIds);
            }
        }

        $analysisTypeByElement = $lines !== null ? $this->analysisTypesForElements($multiTypeElementIds) : [];
        $totals = [];

        foreach ($parsed as $row) {
            $analysisTypeIds = $row['analysis_type_ids'] !== [] ? $row['analysis_type_ids'] : [null];

            foreach ($analysisTypeIds as $analysisTypeId) {
                $elementIds = array_values(array_filter(
                    $row['element_ids'],
                    fn (string $elementId): bool => count($analysisTypeIds) === 1
                        || ($analysisTypeByElement[$elementId] ?? null) === $analysisTypeId,
                ));

                foreach ($this->unitsFor($row['sample_type_id'], $analysisTypeId, $elementIds, $lines) as $unit) {
                    $totals[$unit['key']] ??= $unit + ['quantity' => 0];
                    $totals[$unit['key']]['quantity'] += $row['quantity'];
                }
            }
        }

        $items = [];
        foreach ($totals as $key => $total) {
            $items[] = new PurchaseOrderDemandItem(
                $key,
                $total['sample_type_id'],
                $total['analysis_type_id'] !== null ? [$total['analysis_type_id']] : [],
                $total['quantity'],
                $total['element_ids'],
            );
        }

        return $items;
    }

    /**
     * Demand units one sample needs for one analysis type: a single analysis-type unit, or one unit per
     * parameter the PO prices per test. Parameters without a per-test line stay on the analysis-type unit.
     *
     * @param  list<string>  $elementIds
     * @param  list<CustomerPurchaseOrderLine>|null  $lines
     * @return list<array{key: string, sample_type_id: ?string, analysis_type_id: ?string, element_ids: list<string>}>
     */
    public function unitsFor(?string $sampleTypeId, ?string $analysisTypeId, array $elementIds, ?array $lines): array
    {
        $analysisUnit = [
            'key' => self::keyFor($sampleTypeId, $analysisTypeId),
            'sample_type_id' => $sampleTypeId,
            'analysis_type_id' => $analysisTypeId,
            'element_ids' => [],
        ];

        if ($lines === null || $elementIds === [] || $this->hasPackageFor($lines, $sampleTypeId, $analysisTypeId)) {
            return [$analysisUnit];
        }

        $units = [];
        $withoutPerTestLine = [];

        foreach (array_values(array_unique($elementIds)) as $elementId) {
            $perTest = new PurchaseOrderDemandItem(
                self::keyFor($sampleTypeId, $analysisTypeId, $elementId),
                $sampleTypeId,
                $analysisTypeId !== null ? [$analysisTypeId] : [],
                1,
                [$elementId],
            );

            if ($this->matcher->match($lines, $perTest) === null) {
                $withoutPerTestLine[] = $elementId;

                continue;
            }

            $units[] = [
                'key' => $perTest->key,
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'element_ids' => [$elementId],
            ];
        }

        if ($withoutPerTestLine !== [] || $units === []) {
            $units[] = $analysisUnit;
        }

        return $units;
    }

    public static function keyFor(?string $sampleTypeId, ?string $analysisTypeId, ?string $analysisElementId = null): string
    {
        $key = ($sampleTypeId ?? '-').'|'.($analysisTypeId ?? '-');

        return $analysisElementId !== null ? $key.'|'.$analysisElementId : $key;
    }

    /**
     * @param  list<CustomerPurchaseOrderLine>  $lines
     */
    private function hasPackageFor(array $lines, ?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        $packages = array_values(array_filter($lines, static fn (CustomerPurchaseOrderLine $line): bool => (bool) $line->is_package));

        return $packages !== [] && $this->matcher->match($packages, new PurchaseOrderDemandItem(
            self::keyFor($sampleTypeId, $analysisTypeId),
            $sampleTypeId,
            $analysisTypeId !== null ? [$analysisTypeId] : [],
            1,
        )) !== null;
    }

    /**
     * @param  list<string>  $elementIds
     * @return array<string, string> element id => analysis type id
     */
    private function analysisTypesForElements(array $elementIds): array
    {
        $elementIds = array_values(array_unique($elementIds));
        if ($elementIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $elementIds)
            ->pluck('analysis_type_id', 'id')
            ->mapWithKeys(fn ($analysisTypeId, $id): array => [(string) $id => (string) $analysisTypeId])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function rowAnalysisTypeIds(array $row): array
    {
        $fromAttributes = $row['attributes']['analysis_type_ids'] ?? null;
        $ids = is_array($fromAttributes) && $fromAttributes !== []
            ? $fromAttributes
            : [$row['analysis_type_id'] ?? null];

        return $this->idList($ids);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function rowElementIds(array $row): array
    {
        $fromAttributes = $row['attributes']['analysis_element_ids'] ?? null;
        $ids = is_array($fromAttributes) && $fromAttributes !== []
            ? $fromAttributes
            : explode(',', (string) ($row['analysis_element_id'] ?? ''));

        return $this->idList($ids);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return list<string>
     */
    private function idList(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn (mixed $id): string => (string) $this->idOrNull($id), $ids),
            static fn (string $id): bool => $id !== '',
        )));
    }

    /**
     * @param  iterable<int, CustomerPurchaseOrderLine>  $lines
     * @return list<CustomerPurchaseOrderLine>
     */
    private function listOf(iterable $lines): array
    {
        $list = [];
        foreach ($lines as $line) {
            $list[] = $line;
        }

        return $list;
    }

    private function idOrNull(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
