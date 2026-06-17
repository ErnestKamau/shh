<?php

namespace App\Services\Monitoring;

use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateField;
use Illuminate\Support\Arr;

class MonitoringLogValueResolver
{
    /**
     * @return list<array{key: string, label: string, is_primary: bool}>
     */
    public function variableColumnsForTemplate(MonitoringTemplate $template): array
    {
        return $this->columnsOrderedByReadingSteps($template, onlyShownInLogs: true);
    }

    /**
     * All possible columns a template can produce (including those hidden from the operator capture table).
     *
     * @return list<array{key: string, label: string, is_primary: bool}>
     */
    protected function allVariableColumnsForTemplate(MonitoringTemplate $template): array
    {
        return $this->columnsOrderedByReadingSteps($template, onlyShownInLogs: false);
    }

    /**
     * Build columns in reading-step order (template field sort_order matches step_number).
     *
     * @return list<array{key: string, label: string, is_primary: bool}>
     */
    protected function columnsOrderedByReadingSteps(MonitoringTemplate $template, bool $onlyShownInLogs): array
    {
        $template->loadMissing(['fields.formulaRule']);

        $fields = $template->fields
            ->filter(fn (MonitoringTemplateField $field) => $field->field_key !== '__meta_scope_items')
            ->sortBy('sort_order')
            ->values();

        $columns = [];
        $seenKeys = [];

        foreach ($fields as $field) {
            if (in_array($field->field_type, ['metadata'], true)) {
                continue;
            }

            if ($onlyShownInLogs && ! $this->fieldShownInMonitoringLogs($field)) {
                continue;
            }

            $column = $this->columnFromTemplateField($field);

            if ($column === null) {
                continue;
            }

            $key = $column['key'];

            if (isset($seenKeys[$key])) {
                continue;
            }

            $seenKeys[$key] = true;
            $columns[] = $column;
        }

        if ($columns !== [] && ! collect($columns)->contains(fn (array $col) => $col['is_primary'])) {
            $columns[array_key_last($columns)]['is_primary'] = true;
        }

        return $columns;
    }

    protected function fieldShownInMonitoringLogs(MonitoringTemplateField $field): bool
    {
        $config = is_array($field->field_config) ? $field->field_config : [];

        if (! array_key_exists('show_in_monitoring_logs', $config)) {
            return true;
        }

        return (bool) $config['show_in_monitoring_logs'];
    }

    /**
     * @return array{key: string, label: string, is_primary: bool}|null
     */
    protected function columnFromTemplateField(MonitoringTemplateField $field): ?array
    {
        if ($field->field_type === 'formula') {
            $rule = $field->formulaRule;

            if ($rule === null || ! $rule->is_active || blank($rule->output_key)) {
                return null;
            }

            $key = (string) $rule->output_key;

            return [
                'key' => $key,
                'label' => $rule->name ?: $field->label,
                'is_primary' => $this->isPrimaryOutputKey($key),
            ];
        }

        $key = (string) $field->field_key;

        return [
            'key' => $key,
            'label' => $field->label,
            'is_primary' => $this->isPrimaryOutputKey($key),
        ];
    }

    /**
     * Columns the operator enters during capture (not formula outputs or auto-resolved variables).
     *
     * @return list<array{key: string, label: string, is_primary: bool}>
     */
    public function userInputColumnsForTemplate(MonitoringTemplate $template): array
    {
        return $this->partitionVariableColumns($template)['user'];
    }

    /**
     * Formula outputs, scope variables, and other system-computed values.
     *
     * @return list<array{key: string, label: string, is_primary: bool}>
     */
    public function derivedColumnsForTemplate(MonitoringTemplate $template): array
    {
        return $this->partitionVariableColumns($template)['derived'];
    }

    /**
     * @return array{user: list<array{key: string, label: string, is_primary: bool}>, derived: list<array{key: string, label: string, is_primary: bool}>}
     */
    protected function partitionVariableColumns(MonitoringTemplate $template): array
    {
        $formulaOutputKeys = $template->formulaRules
            ->where('is_active', true)
            ->pluck('output_key')
            ->filter()
            ->map(fn ($key) => (string) $key)
            ->all();

        $fieldsByKey = $template->fields->keyBy('field_key');

        $user = [];
        $derived = [];

        foreach ($this->variableColumnsForTemplate($template) as $column) {
            $key = $column['key'];
            $field = $fieldsByKey->get($key);

            if (in_array($key, $formulaOutputKeys, true)) {
                $derived[] = $column;

                continue;
            }

            if ($field !== null && $this->fieldIsDerivedForCapture($field)) {
                $derived[] = $column;

                continue;
            }

            $user[] = $column;
        }

        return ['user' => $user, 'derived' => $derived];
    }

    protected function fieldIsDerivedForCapture(MonitoringTemplateField $field): bool
    {
        if (in_array($field->field_type, ['metadata', 'formula'], true)) {
            return true;
        }

        if ($field->is_readonly) {
            return true;
        }

        $config = $field->field_config ?? [];
        if (isset($config['step_type']) && $config['step_type'] === 'input') {
            return false;
        }

        if (filled(Arr::get($config, 'variable_slug'))) {
            return true;
        }

        return false;
    }

    public function primaryOutputKeyForTemplate(MonitoringTemplate $template): ?string
    {
        $columns = $this->allVariableColumnsForTemplate($template);

        foreach ($columns as $column) {
            if ($column['is_primary']) {
                return $column['key'];
            }
        }

        return $columns[0]['key'] ?? null;
    }

    public function finalOutputKeyForTemplate(MonitoringTemplate $template): ?string
    {
        $template->loadMissing(['fields.formulaRule', 'formulaRules']);

        foreach ($template->fields as $field) {
            if ($field->field_key === '__meta_scope_items') {
                continue;
            }

            if (str_contains(strtolower((string) $field->field_key), 'final')) {
                return (string) $field->field_key;
            }
        }

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $outputKey = (string) ($rule->output_key ?? '');

            if ($outputKey !== '' && str_contains(strtolower($outputKey), 'final')) {
                return $outputKey;
            }
        }

        return $this->primaryOutputKeyForTemplate($template);
    }

    public function finalNumericValueFromLog(MonitoringLog $log, MonitoringTemplate $template): ?float
    {
        $finalKey = $this->finalOutputKeyForTemplate($template);

        if ($finalKey === null) {
            return null;
        }

        $value = $this->valueFromLogByKey($log, $finalKey);

        if ($value !== null && is_numeric($value)) {
            return (float) $value;
        }

        return $this->numericValueFromLog($log, $finalKey);
    }

    public function numericValueFromLog(MonitoringLog $log, ?string $preferredKey = null): ?float
    {
        if ($preferredKey !== null) {
            $value = $this->valueFromLogByKey($log, $preferredKey);
            if ($value !== null && is_numeric($value)) {
                return (float) $value;
            }
        }

        foreach ($log->entries as $entry) {
            if ($this->isPrimaryOutputKey($entry->field_key)) {
                $value = $entry->computed_value ?? $entry->raw_value;
                if (is_numeric($value)) {
                    return (float) $value;
                }
            }
        }

        foreach ($log->entries as $entry) {
            if ($entry->field_key === '__meta_scope_items') {
                continue;
            }

            $value = $entry->computed_value ?? $entry->raw_value;
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        $formulaVars = Arr::get($log->payload ?? [], 'formula_variables', []);
        if (is_array($formulaVars)) {
            foreach ($formulaVars as $key => $value) {
                if ($this->isPrimaryOutputKey((string) $key) && is_numeric($value)) {
                    return (float) $value;
                }
            }

            foreach ($formulaVars as $value) {
                if (is_numeric($value)) {
                    return (float) $value;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, string|null>
     */
    public function variableValuesFromLog(MonitoringLog $log, array $columnKeys): array
    {
        $values = [];

        foreach ($columnKeys as $key) {
            $values[$key] = $this->stringifyForDisplay($this->valueFromLogByKey($log, $key));
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    public function captureInputsFromLog(MonitoringLog $log): array
    {
        $inputs = Arr::get($log->payload ?? [], 'inputs', []);

        if (! is_array($inputs)) {
            $inputs = [];
        }

        foreach ($log->entries as $entry) {
            if ($entry->field_key === '__meta_scope_items') {
                continue;
            }

            if ($entry->raw_value !== null && $entry->raw_value !== '') {
                $inputs[$entry->field_key] = $entry->raw_value;

                continue;
            }

            if ($entry->computed_value !== null && $entry->computed_value !== '') {
                $inputs[$entry->field_key] = $entry->computed_value;
            }
        }

        if (filled($log->equipment_id)) {
            $inputs['equipment_id'] = (string) $log->equipment_id;
        }

        return $inputs;
    }

    public function valueFromLogByKey(MonitoringLog $log, string $key): mixed
    {
        foreach ($log->entries as $entry) {
            if ($entry->field_key === $key) {
                return $entry->computed_value ?? $entry->raw_value;
            }
        }

        $inputs = Arr::get($log->payload ?? [], 'inputs', []);
        if (is_array($inputs) && array_key_exists($key, $inputs)) {
            return $inputs[$key];
        }

        $formulaVars = Arr::get($log->payload ?? [], 'formula_variables', []);
        if (is_array($formulaVars) && array_key_exists($key, $formulaVars)) {
            return $formulaVars[$key];
        }

        return null;
    }

    protected function isPrimaryOutputKey(string $key): bool
    {
        $lower = strtolower($key);

        return str_contains($lower, 'final')
            || str_contains($lower, 'result')
            || str_contains($lower, 'value');
    }

    protected function stringifyForDisplay(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value);
    }
}
