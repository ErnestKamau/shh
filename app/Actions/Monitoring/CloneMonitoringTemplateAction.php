<?php

namespace App\Actions\Monitoring;

use App\Models\Monitoring\MonitoringFormulaRule;
use App\Models\Monitoring\MonitoringReadingStep;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateConfiguredField;
use App\Models\Monitoring\MonitoringTemplateField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CloneMonitoringTemplateAction
{
    /**
     * Deep-clone a monitoring template including fields, reading steps,
     * formula rules, and configured fields. Captured logs are not copied.
     */
    public function execute(MonitoringTemplate $source): MonitoringTemplate
    {
        $source->loadMissing([
            'fields',
            'formulaRules',
            'readingSteps',
            'configuredFields',
        ]);

        return DB::transaction(function () use ($source): MonitoringTemplate {
            $userId = Auth::id();

            $clone = MonitoringTemplate::query()->create([
                'name' => $this->uniqueCopyName((string) $source->name, Auth::user()?->company_id ?? $source->company_id),
                'document_control_number' => $source->document_control_number,
                'version' => 1,
                'effective_date' => $source->effective_date,
                'review_date' => $source->review_date,
                'department' => $source->department,
                'monitoring_category' => $source->monitoring_category,
                'approval_workflow' => $source->approval_workflow,
                'status' => 'draft',
                'is_active' => true,
                'lab_id' => $source->lab_id,
                'parent_template_id' => $source->id,
                'company_id' => Auth::user()?->company_id ?? $source->company_id,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            /** @var array<string, string> $formulaIdMap */
            $formulaIdMap = [];
            foreach ($source->formulaRules as $rule) {
                $newRule = MonitoringFormulaRule::query()->create([
                    'template_id' => $clone->id,
                    'name' => $rule->name,
                    'output_key' => $rule->output_key,
                    'expression' => $rule->expression,
                    'pass_condition_expression' => $rule->pass_condition_expression,
                    'meta' => $rule->meta,
                    'is_active' => $rule->is_active,
                    'company_id' => $rule->company_id ?? $clone->company_id,
                ]);
                $formulaIdMap[(string) $rule->id] = (string) $newRule->id;
            }

            /** @var array<string, string> $readingStepIdMap */
            $readingStepIdMap = [];
            foreach ($source->readingSteps as $step) {
                $newStep = MonitoringReadingStep::query()->create([
                    'template_id' => $clone->id,
                    'step_number' => $step->step_number,
                    'variable_name' => $step->variable_name,
                    'step_type' => $step->step_type,
                    'expression' => $step->expression,
                    'derived_config' => $this->remapUuidMap(
                        is_array($step->derived_config) ? $step->derived_config : null,
                        array_merge($formulaIdMap, $readingStepIdMap)
                    ),
                    'label' => $step->label,
                    'description' => $step->description,
                    'lookup_config' => $step->lookup_config,
                    'analyte_id' => $step->analyte_id,
                    'variable_slug' => $step->variable_slug,
                    'show_in_monitoring_logs' => $step->show_in_monitoring_logs,
                    'input_config' => $this->remapUuidMap(
                        is_array($step->input_config) ? $step->input_config : null,
                        array_merge($formulaIdMap, $readingStepIdMap)
                    ),
                ]);
                $readingStepIdMap[(string) $step->id] = (string) $newStep->id;
            }

            // Second pass: rewrite configs that may reference other steps created later in the loop.
            foreach ($source->readingSteps as $step) {
                $newStepId = $readingStepIdMap[(string) $step->id] ?? null;
                if ($newStepId === null) {
                    continue;
                }

                $newStep = MonitoringReadingStep::query()->find($newStepId);
                if (! $newStep) {
                    continue;
                }

                $idMap = array_merge($formulaIdMap, $readingStepIdMap);
                $newStep->derived_config = $this->remapUuidMap(
                    is_array($step->derived_config) ? $step->derived_config : null,
                    $idMap
                );
                $newStep->input_config = $this->remapUuidMap(
                    is_array($step->input_config) ? $step->input_config : null,
                    $idMap
                );
                $newStep->save();
            }

            foreach ($source->fields as $field) {
                $fieldConfig = is_array($field->field_config) ? $field->field_config : null;
                $fieldConfig = $this->remapUuidMap(
                    $fieldConfig,
                    array_merge($formulaIdMap, $readingStepIdMap)
                );

                $formulaRuleId = $field->formula_rule_id
                    ? ($formulaIdMap[(string) $field->formula_rule_id] ?? null)
                    : null;

                MonitoringTemplateField::query()->create([
                    'template_id' => $clone->id,
                    'formula_rule_id' => $formulaRuleId,
                    'field_key' => $field->field_key,
                    'label' => $field->label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'is_readonly' => $field->is_readonly,
                    'sort_order' => $field->sort_order,
                    'field_config' => $fieldConfig,
                ]);
            }

            foreach ($source->configuredFields as $configuredField) {
                MonitoringTemplateConfiguredField::query()->create([
                    'template_id' => $clone->id,
                    'placement' => $configuredField->placement,
                    'label' => $configuredField->label,
                    'field_type' => $configuredField->field_type,
                    'order' => $configuredField->order,
                    'help_text' => $configuredField->help_text,
                    'model_tied_to' => $configuredField->model_tied_to,
                    'field_config' => $this->remapUuidMap(
                        is_array($configuredField->field_config) ? $configuredField->field_config : null,
                        array_merge($formulaIdMap, $readingStepIdMap)
                    ),
                    'is_required' => $configuredField->is_required,
                    'field_value_name' => $configuredField->field_value_name,
                ]);
            }

            return $clone->fresh([
                'fields',
                'formulaRules',
                'readingSteps',
                'configuredFields',
            ]) ?? $clone;
        });
    }

    protected function uniqueCopyName(string $sourceName, mixed $companyId): string
    {
        $base = trim($sourceName);
        if ($base === '') {
            $base = 'Monitoring Template';
        }

        $base = preg_replace('/ \(copy(?: \d+)?\)$/i', '', $base) ?: $base;
        $candidate = $base.' (Copy)';
        $suffix = 2;

        while (
            MonitoringTemplate::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->where('name', $candidate)
                ->exists()
        ) {
            $candidate = $base.' (Copy '.$suffix.')';
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Recursively replace known UUIDs inside nested config arrays.
     *
     * @param  array<string, mixed>|null  $config
     * @param  array<string, string>  $idMap
     * @return array<string, mixed>|null
     */
    protected function remapUuidMap(?array $config, array $idMap): ?array
    {
        if ($config === null) {
            return null;
        }

        if ($idMap === []) {
            return $config;
        }

        $walk = function ($value) use (&$walk, $idMap) {
            if (is_string($value) && isset($idMap[$value])) {
                return $idMap[$value];
            }

            if (is_array($value)) {
                $out = [];
                foreach ($value as $key => $item) {
                    $out[$key] = $walk($item);
                }

                return $out;
            }

            return $value;
        };

        return $walk($config);
    }
}
