<?php

namespace App\Services\Monitoring;

use App\LabSection;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringTemplate;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class MonitoringSectionLogMatrixService
{
    public function __construct(
        protected MonitoringLogValueResolver $valueResolver,
    ) {
    }

    /**
     * @return array{
     *     frequency_columns: list<array{slot: int, label: string}>,
     *     variable_columns: list<array{key: string, label: string, is_primary: bool}>,
     *     rows: list<array{date: string, date_label: string, cells: array<int|string, array{log: mixed, remark: ?string, user: ?string, variables: array<string, ?string>}>}>
     * }
     */
    public function build(
        LabSection $section,
        MonitoringTemplate $template,
        int $days = 30,
    ): array {
        $frequencyColumns = $this->frequencyColumns($section);
        $variableColumns = $this->valueResolver->variableColumnsForTemplate($template);
        $userInputColumns = $this->valueResolver->userInputColumnsForTemplate($template);
        $derivedColumns = $this->valueResolver->derivedColumnsForTemplate($template);
        $variableKeys = array_column($variableColumns, 'key');

        $fromDate = now()->subDays($days)->toDateString();

        $logs = MonitoringLog::query()
            ->with(['entries', 'executedBy', 'calibrationSnapshots', 'template.fields.formulaRule'])
            ->where('template_id', $template->id)
            ->where('lab_id', $section->lab_id)
            ->where('monitoring_scope', 'environmental')
            ->where('log_date', '>=', $fromDate)
            ->orderByDesc('log_date')
            ->orderByDesc('executed_at')
            ->get()
            ->filter(fn (MonitoringLog $log) => $this->logBelongsToSection($log, $section, $template))
            ->values();

        $today = now()->toDateString();
        $nextCapture = $this->nextCaptureSlot($section, $template, $logs);

        $logsByDate = $logs->groupBy(fn (MonitoringLog $log) => $log->log_date?->format('Y-m-d') ?? '');

        $rows = [];

        foreach ($logsByDate as $date => $dateLogs) {
            if ($date === '') {
                continue;
            }

            $cells = [];

            foreach ($frequencyColumns as $column) {
                $slot = $column['slot'];
                $cellLog = $dateLogs->first(fn (MonitoringLog $log) => $log->resolvedFrequencySlot() === $slot);

                $cells[$slot] = $this->buildCell($cellLog, $variableKeys, $date, $today, $slot, $nextCapture);
            }

            $unslotted = $dateLogs->filter(fn (MonitoringLog $log) => $log->resolvedFrequencySlot() === null);
            if ($unslotted->isNotEmpty()) {
                $cells['unslotted'] = $this->buildCell(
                    $unslotted->first(),
                    $variableKeys,
                    $date,
                    $today,
                    0,
                    $nextCapture,
                );
            }

            $rows[] = [
                'date' => $date,
                'date_label' => $date === $today ? 'Today' : Carbon::parse($date)->format('M j, Y'),
                'is_today' => $date === $today,
                'cells' => $cells,
            ];
        }

        if (! collect($rows)->contains(fn (array $row) => ($row['date'] ?? '') === $today)) {
            $cells = [];
            foreach ($frequencyColumns as $column) {
                $slot = $column['slot'];
                $cells[$slot] = $this->buildCell(null, $variableKeys, $today, $today, $slot, $nextCapture);
            }

            array_unshift($rows, [
                'date' => $today,
                'date_label' => 'Today',
                'is_today' => true,
                'cells' => $cells,
            ]);
        }

        return [
            'frequency_columns' => $frequencyColumns,
            'variable_columns' => $variableColumns,
            'user_input_columns' => $userInputColumns,
            'display_columns' => $variableColumns,
            'derived_columns' => $derivedColumns,
            'has_unslotted' => $logs->contains(fn (MonitoringLog $log) => $log->resolvedFrequencySlot() === null),
            'next_capture' => $nextCapture,
            'today' => $today,
            'rows' => $rows,
        ];
    }

    /**
     * Next frequency slot to capture today (by schedule order / entry order).
     *
     * @return array{slot: int, label: string}|null
     */
    public function nextCaptureSlot(LabSection $section, MonitoringTemplate $template, ?\Illuminate\Support\Collection $logs = null): ?array
    {
        $schedule = $this->frequencyColumns($section);

        if ($schedule === []) {
            return null;
        }

        $today = now()->toDateString();

        if ($logs === null) {
            $logs = MonitoringLog::query()
                ->where('template_id', $template->id)
                ->where('lab_id', $section->lab_id)
                ->where('monitoring_scope', 'environmental')
                ->whereDate('log_date', $today)
                ->get()
                ->filter(fn (MonitoringLog $log) => $this->logBelongsToSection($log, $section, $template));
        }

        $capturedSlots = $logs
            ->filter(fn (MonitoringLog $log) => $log->log_date?->format('Y-m-d') === $today)
            ->map(fn (MonitoringLog $log) => $log->resolvedFrequencySlot())
            ->filter()
            ->unique()
            ->values();

        foreach ($schedule as $column) {
            if (! $capturedSlots->contains($column['slot'])) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return list<array{slot: int, label: string}>
     */
    protected function frequencyColumns(LabSection $section): array
    {
        return collect($section->normalizedReadingFrequencySchedule())
            ->map(fn (array $row): array => [
                'slot' => (int) $row['frequency'],
                'label' => filled($row['label']) ? $row['label'] : 'Reading '.(int) $row['frequency'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $variableKeys
     * @return array{filled: bool, remark: ?string, user: ?string, variables: array<string, ?string>, status: ?string}
     */
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
     * @param  array{slot: int, label: string}|null  $nextCapture
     * @return array<string, mixed>
     */
    protected function buildCell(
        ?MonitoringLog $log,
        array $variableKeys,
        string $rowDate,
        string $today,
        int $slot,
        ?array $nextCapture,
    ): array {
        $isToday = $rowDate === $today;
        $nextSlot = $nextCapture['slot'] ?? null;

        if ($log === null) {
            return [
                'filled' => false,
                'remark' => null,
                'user' => null,
                'variables' => array_fill_keys($variableKeys, null),
                'status' => null,
                'is_capture' => $isToday && $nextSlot !== null && $slot === $nextSlot,
                'is_locked' => $isToday && $nextSlot !== null && $slot > $nextSlot,
                'is_waiting' => $isToday && $nextSlot !== null && $slot > $nextSlot,
            ];
        }

        $variables = $this->valueResolver->variableValuesFromLog($log, $variableKeys);

        return [
            'filled' => true,
            'log_id' => $log->id,
            'template_id' => $log->template_id,
            'frequency_slot' => $log->resolvedFrequencySlot(),
            'remark' => $log->resolvedRemark(),
            'user' => $log->executedBy?->name,
            'variables' => $variables,
            'status' => $log->status,
            'verdict' => $this->resolveVerdict($log, $variables),
            'is_capture' => false,
            'is_locked' => false,
            'is_waiting' => false,
        ];
    }

    /**
     * @param  array<string, string|null>  $variables
     */
    protected function resolveVerdict(MonitoringLog $log, array $variables): ?string
    {
        foreach ($variables as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $keyNeedle = strtolower((string) $key);
            $normalized = strtolower(trim((string) $value));

            if (! str_contains($keyNeedle, 'remark') && ! str_contains($keyNeedle, 'status') && ! str_contains($keyNeedle, 'result')) {
                continue;
            }

            if (in_array($normalized, ['pass', 'passed'], true)) {
                return 'pass';
            }

            if (in_array($normalized, ['fail', 'failed'], true)) {
                return 'fail';
            }
        }

        $remark = strtolower(trim((string) ($log->resolvedRemark() ?? '')));

        if (in_array($remark, ['pass', 'passed'], true)) {
            return 'pass';
        }

        if (in_array($remark, ['fail', 'failed'], true)) {
            return 'fail';
        }

        $overall = strtoupper(trim((string) ($log->overall_result ?? '')));

        if (in_array($overall, ['FAIL', 'FAILED', 'OUT OF RANGE', 'CRITICAL'], true)) {
            return 'fail';
        }

        if (in_array($overall, ['PASS', 'PASSED', 'IN RANGE', 'WARNING'], true)) {
            return 'pass';
        }

        return null;
    }
}
