<?php

namespace App\Services\Monitoring;

use App\LabSection;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MonitoringSectionChartService
{
    public function __construct(
        protected MonitoringLogValueResolver $valueResolver,
    ) {
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     actual: list<float>,
     *     optimum: list<float|null>,
     *     min: list<float|null>,
     *     max: list<float|null>,
     *     actual_um_high: list<float|null>,
     *     actual_um_low: list<float|null>,
     *     optimum_um_high: list<float|null>,
     *     optimum_um_low: list<float|null>,
     *     executed_at: list<string|null>,
     *     unit: string,
     *     hasData: bool,
     *     reference_mode: string,
     *     reference: array{min: ?float, max: ?float, optimum: ?float, constant: ?float},
     *     include_uncertainty_on_optimum: bool,
     *     period_label: string,
     *     range_fill_min: ?float,
     *     range_fill_max: ?float,
     *     optimum_level: ?float
     * }
     */
    public function build(
        LabSection $section,
        MonitoringTemplate $template,
        string $dateRange = '30',
        bool $includeUncertaintyOnOptimum = false,
    ): array {
        $reference = $this->referenceLevels($section);
        $referenceMode = $this->referenceMode($section);
        $unitName = $section->reportingUnit?->name ?? '';
        [$fromDate, $toDate, $periodLabel] = $this->resolveDateBounds($dateRange);

        $frequencyLabels = collect($section->normalizedReadingFrequencySchedule())
            ->mapWithKeys(fn (array $row): array => [
                (int) $row['frequency'] => filled($row['label']) ? (string) $row['label'] : 'Reading '.(int) $row['frequency'],
            ])
            ->all();

        $logs = MonitoringLog::query()
            ->with(['entries', 'calibrationSnapshots', 'template.fields.formulaRule', 'template.formulaRules'])
            ->where('template_id', $template->id)
            ->where('lab_id', $section->lab_id)
            ->where('monitoring_scope', 'environmental')
            ->where('log_date', '>=', $fromDate)
            ->when($toDate !== null, fn ($query) => $query->where('log_date', '<=', $toDate))
            ->orderBy('log_date')
            ->orderBy('executed_at')
            ->get()
            ->filter(fn (MonitoringLog $log) => $this->logBelongsToSection($log, $section, $template))
            ->sortBy(fn (MonitoringLog $log) => sprintf(
                '%s-%03d-%s',
                $log->log_date?->format('Y-m-d') ?? '',
                $log->resolvedFrequencySlot() ?? 999,
                optional($log->executed_at)->format('H:i:s') ?? '',
            ))
            ->values();

        $series = $this->buildSeriesFromLogs(
            $logs,
            $template,
            $section,
            $frequencyLabels,
            $reference,
            $referenceMode,
            $includeUncertaintyOnOptimum,
        );

        $optimumLevel = $this->resolvedOptimumLevel($reference, $referenceMode);

        return array_merge($series, [
            'unit' => $unitName,
            'hasData' => count($series['labels']) > 0,
            'reference_mode' => $referenceMode,
            'reference' => $reference,
            'include_uncertainty_on_optimum' => $includeUncertaintyOnOptimum,
            'period_label' => $periodLabel,
            'range_fill_min' => $referenceMode === 'range' ? $reference['min'] : null,
            'range_fill_max' => $referenceMode === 'range' ? $reference['max'] : null,
            'optimum_level' => $optimumLevel,
        ]);
    }

    /**
     * @param  Collection<int, MonitoringLog>  $logs
     * @param  array<int, string>  $frequencyLabels
     * @param  array{min: ?float, max: ?float, optimum: ?float, constant: ?float}  $reference
     * @return array{
     *     labels: list<string>,
     *     actual: list<float>,
     *     optimum: list<float|null>,
     *     min: list<float|null>,
     *     max: list<float|null>,
     *     actual_um_high: list<float|null>,
     *     actual_um_low: list<float|null>,
     *     optimum_um_high: list<float|null>,
     *     optimum_um_low: list<float|null>,
     *     min_um_low: list<float|null>,
     *     max_um_high: list<float|null>,
     *     executed_at: list<string|null>
     * }
     */
    public function buildSeriesFromLogs(
        Collection $logs,
        MonitoringTemplate $template,
        LabSection $section,
        array $frequencyLabels,
        array $reference,
        string $referenceMode,
        bool $includeUncertaintyOnOptimum,
    ): array {
        $labels = [];
        $actual = [];
        $optimum = [];
        $min = [];
        $max = [];
        $actualUmHigh = [];
        $actualUmLow = [];
        $optimumUmHigh = [];
        $optimumUmLow = [];
        $minUmLow = [];
        $maxUmHigh = [];
        $executedAt = [];

        $optimumLevel = $this->resolvedOptimumLevel($reference, $referenceMode);

        foreach ($logs as $log) {
            $numericVal = $this->valueResolver->finalNumericValueFromLog($log, $template);

            if ($numericVal === null) {
                continue;
            }

            $uom = $this->uncertaintyFromLog($log);

            $slot = $log->resolvedFrequencySlot();
            $freqLabel = $slot !== null ? ($frequencyLabels[$slot] ?? 'Reading '.$slot) : '';
            $dateLabel = $log->log_date?->format('M j') ?? optional($log->executed_at)->format('M j') ?? '';
            $labels[] = trim($dateLabel.($freqLabel !== '' ? ' '.$freqLabel : ''));

            $actual[] = $numericVal;
            $executedAt[] = $log->executed_at?->toIso8601String();

            if ($referenceMode === 'range') {
                $min[] = $reference['min'];
                $max[] = $reference['max'];
                $optimum[] = null;
            } else {
                $min[] = null;
                $max[] = null;
                $optimum[] = $optimumLevel;
            }

            if ($uom !== null) {
                $actualUmHigh[] = $numericVal + $uom;
                $actualUmLow[] = $numericVal - $uom;
            } else {
                $actualUmHigh[] = null;
                $actualUmLow[] = null;
            }

            if ($includeUncertaintyOnOptimum && $uom !== null) {
                if ($optimumLevel !== null) {
                    $optimumUmHigh[] = $optimumLevel + $uom;
                    $optimumUmLow[] = $optimumLevel - $uom;
                } else {
                    $optimumUmHigh[] = null;
                    $optimumUmLow[] = null;
                }

                if ($referenceMode === 'range' && $reference['min'] !== null && $reference['max'] !== null) {
                    $minUmLow[] = (float) $reference['min'] - $uom;
                    $maxUmHigh[] = (float) $reference['max'] + $uom;
                } else {
                    $minUmLow[] = null;
                    $maxUmHigh[] = null;
                }
            } else {
                $optimumUmHigh[] = null;
                $optimumUmLow[] = null;
                $minUmLow[] = null;
                $maxUmHigh[] = null;
            }
        }

        return [
            'labels' => $labels,
            'actual' => $actual,
            'optimum' => $optimum,
            'min' => $min,
            'max' => $max,
            'actual_um_high' => $actualUmHigh,
            'actual_um_low' => $actualUmLow,
            'optimum_um_high' => $optimumUmHigh,
            'optimum_um_low' => $optimumUmLow,
            'min_um_low' => $minUmLow,
            'max_um_high' => $maxUmHigh,
            'executed_at' => $executedAt,
        ];
    }

    protected function uncertaintyFromLog(MonitoringLog $log): ?float
    {
        $uom = $log->calibrationSnapshots->first()?->uncertainty_of_measure;

        if ($uom !== null && is_numeric($uom)) {
            return (float) $uom;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: ?string, 2: string}
     */
    protected function resolveDateBounds(string $dateRange): array
    {
        if ($dateRange === 'month') {
            return [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
                now()->format('F Y'),
            ];
        }

        $days = max(7, min(365, (int) $dateRange));

        return [
            now()->subDays($days)->toDateString(),
            null,
            'Last '.$days.' days',
        ];
    }

    protected function referenceMode(LabSection $section): string
    {
        if ($section->expected_value_type === 'range') {
            return 'range';
        }

        if ($section->expected_value_type === 'constant') {
            return 'constant';
        }

        if (filled($section->optimum_level) && is_numeric($section->optimum_level)) {
            return 'optimum';
        }

        if ($section->expected_min !== null && $section->expected_max !== null) {
            return 'range';
        }

        return 'optimum';
    }

    /**
     * @param  array{min: ?float, max: ?float, optimum: ?float, constant: ?float}  $reference
     */
    protected function resolvedOptimumLevel(array $reference, string $referenceMode): ?float
    {
        if ($referenceMode === 'constant') {
            return $reference['constant'];
        }

        if ($referenceMode === 'optimum') {
            return $reference['optimum'];
        }

        return $reference['optimum'];
    }

    protected function logBelongsToSection(MonitoringLog $log, LabSection $section, MonitoringTemplate $template): bool
    {
        $logSectionId = $log->resolvedLabSectionId();

        if ($logSectionId === (string) $section->id) {
            return true;
        }

        if ($logSectionId !== null) {
            return false;
        }

        $metaField = $template->fields->firstWhere('field_key', '__meta_scope_items');
        $sections = array_map(
            fn ($id) => (string) $id,
            (array) Arr::get($metaField?->field_config ?? [], 'sections', []),
        );

        return in_array((string) $section->id, $sections, true);
    }

    /**
     * @return array{min: ?float, max: ?float, optimum: ?float, constant: ?float}
     */
    protected function referenceLevels(LabSection $section): array
    {
        $constant = null;

        if ($section->expected_value_type === 'constant' && $section->expected_value !== null) {
            $constant = is_numeric($section->expected_value) ? (float) $section->expected_value : null;
        }

        $optimum = filled($section->optimum_level) && is_numeric($section->optimum_level)
            ? (float) $section->optimum_level
            : null;

        if ($optimum === null && $section->expected_min !== null && $section->expected_max !== null) {
            $optimum = ((float) $section->expected_min + (float) $section->expected_max) / 2;
        }

        return [
            'min' => $section->expected_min !== null ? (float) $section->expected_min : null,
            'max' => $section->expected_max !== null ? (float) $section->expected_max : null,
            'optimum' => $optimum,
            'constant' => $constant,
        ];
    }
}
