<?php

namespace App\Services\Qc;

use App\Models\QcModule\Data\QcResults;
use App\Models\QcModule\QCProcessedResults;

class QcStatisticsService
{
    /**
     * @param  array<int, float|int|string>  $values
     * @return array{mean: float, median: float|null, rSD: float, rCV: float|null, percent_rCV: float|null}|null
     */
    public function calculateRobustCv(array $values): ?array
    {
        $numeric = $this->normalizeNumericValues($values);

        if ($numeric === []) {
            return null;
        }

        $median = $this->calculateMedian($numeric);
        if ($median === null) {
            return null;
        }

        if ($median == 0.0) {
            return [
                'mean' => $this->calculateMean($numeric),
                'median' => $median,
                'rSD' => 0.0,
                'rCV' => null,
                'percent_rCV' => null,
            ];
        }

        $rSd = $this->calculateRobustSd($numeric) ?? 0.0;
        $rCv = $rSd / $median;

        return [
            'mean' => $this->calculateMean($numeric),
            'median' => $median,
            'rSD' => $rSd,
            'rCV' => $rCv,
            'percent_rCV' => $rCv * 100,
        ];
    }

    /**
     * Apply robust stats to processed-result rows and mark linked QC results as processed.
     *
     * @param  array<int, string>  $analyteProcessedIds
     */
    public function processAnalyteGroups(array $analyteProcessedIds): int
    {
        $ids = array_values(array_unique(array_filter($analyteProcessedIds)));

        if ($ids === []) {
            return 0;
        }

        $processed = 0;

        $groups = QCProcessedResults::query()->whereIn('id', $ids)->get();

        foreach ($groups as $group) {
            $rawResults = QcResults::query()
                ->where('analyte_processed_id', $group->id)
                ->pluck('result')
                ->all();

            $statistics = $this->calculateRobustCv($rawResults);
            if ($statistics === null) {
                continue;
            }

            $group->robust_standard_deviation = $statistics['rSD'];
            $group->robust_median = $statistics['median'];
            $group->robust_mean = $statistics['mean'];
            $group->robust_cv = $statistics['rCV'];
            $group->robust_cv_percentage = $statistics['percent_rCV'];
            $group->save();
            $processed++;
        }

        QcResults::query()
            ->whereIn('analyte_processed_id', $ids)
            ->update(['is_qc_processed' => 1]);

        return $processed;
    }

    /**
     * Process every QC result group that still has is_qc_processed = 0.
     */
    public function processAllUnprocessed(): int
    {
        $ids = QcResults::query()
            ->where('is_qc_processed', 0)
            ->pluck('analyte_processed_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->processAnalyteGroups($ids);
    }

    /**
     * @param  array<int, float|int|string>  $values
     * @return array<int, float>
     */
    public function normalizeNumericValues(array $values): array
    {
        return collect($values)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, float>  $values
     */
    public function calculateMean(array $values): float
    {
        if ($values === []) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }

    /**
     * @param  array<int, float>  $values
     */
    public function calculateMedian(array $values): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        sort($values);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    /**
     * @param  array<int, float>  $values
     */
    public function calculateRobustSd(array $values): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        if ($count === 1) {
            return 0.0;
        }

        $median = $this->calculateMedian($values);
        if ($median === null) {
            return null;
        }

        $deviations = array_map(fn ($value) => abs($value - $median), $values);
        $mad = $this->calculateMedian($deviations);

        return $mad !== null ? $mad * 1.4826 : null;
    }
}
