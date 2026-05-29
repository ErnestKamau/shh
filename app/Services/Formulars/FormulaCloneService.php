<?php

namespace App\Services\Formulars;

use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\FormulaStepTableColumn;
use App\Models\Formulars\FormulaStepTableStaticCell;
use App\Models\Formulars\FormulaStepTableStaticRow;
use App\Models\Formulars\FormulaVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormulaCloneService
{
    public function cloneFormula(Formula $source, string $name, ?string $description = null): Formula
    {
        return DB::transaction(function () use ($source, $name, $description): Formula {
            $sourceVersion = $this->resolveSourceVersion($source);

            $formula = Formula::create([
                'name' => $name,
                'description' => $description ?? $source->description,
                'is_active' => $source->is_active,
            ]);

            $targetVersion = FormulaVersion::create([
                'formula_id' => $formula->id,
                'version_number' => 1,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            if ($sourceVersion) {
                $this->copyVersionConfiguration($sourceVersion, $targetVersion);
            }

            return $formula->fresh(['formulaVersions']);
        });
    }

    public function copyVersionConfiguration(FormulaVersion $source, FormulaVersion $target): void
    {
        $this->copyVersionMetadata($source, $target);

        foreach ($source->formulaSteps()->orderBy('step_number')->get() as $step) {
            $newStep = FormulaStep::create([
                'formula_version_id' => $target->id,
                'step_number' => $step->step_number,
                'variable_name' => $step->variable_name,
                'step_type' => $step->step_type,
                'expression' => $step->expression,
                'label' => $step->label,
                'description' => $step->description,
                'lookup_config' => $step->lookup_config,
                'step_config' => $step->step_config,
                'table_mode' => $step->table_mode,
                'row_driver' => $step->row_driver,
                'row_driver_filters' => $step->row_driver_filters,
                'allow_manual_rows' => $step->allow_manual_rows,
                'analyte_id' => $step->analyte_id,
            ]);

            if ($step->isCustomTable()) {
                $this->copyFormulaStepTableDefinition($step, $newStep);
            }
        }

        foreach ($source->mandatoryFields()->orderBy('order')->get() as $field) {
            $payload = [
                'formula_version_id' => $target->id,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'order' => $field->order,
                'help_text' => $field->help_text,
                'model_tied_to' => $field->model_tied_to,
                'is_required' => $field->is_required,
                'field_value_name' => $field->field_value_name,
            ];

            if (Schema::hasColumn('formula_mandatory_fields', 'form_placement')) {
                $payload['form_placement'] = $field->form_placement;
            }

            if (Schema::hasColumn('formula_mandatory_fields', 'field_options')) {
                $payload['field_options'] = $field->field_options;
            }

            FormulaMandatoryField::create($payload);
        }
    }

    protected function resolveSourceVersion(Formula $source): ?FormulaVersion
    {
        $activeVersion = $source->formulaVersions()->where('is_active', true)->first();

        if ($activeVersion) {
            return $activeVersion;
        }

        return $source->formulaVersions()->orderByDesc('version_number')->first();
    }

    protected function copyVersionMetadata(FormulaVersion $source, FormulaVersion $target): void
    {
        $attributes = [];

        if (Schema::hasColumn('formula_versions', 'mandatory_fields_placement')) {
            $attributes['mandatory_fields_placement'] = $source->mandatory_fields_placement;
        }

        if (Schema::hasColumn('formula_versions', 'document_control_no')) {
            $attributes['document_control_no'] = $source->document_control_no;
        }

        if (Schema::hasColumn('formula_versions', 'document_control_revision_no')) {
            $attributes['document_control_revision_no'] = $source->document_control_revision_no;
        }

        if (Schema::hasColumn('formula_versions', 'document_control_issue_date')) {
            $attributes['document_control_issue_date'] = $source->document_control_issue_date;
        }

        if ($attributes !== []) {
            $target->update($attributes);
        }
    }

    protected function copyFormulaStepTableDefinition(FormulaStep $source, FormulaStep $target): void
    {
        if (! Schema::hasTable('formula_step_table_columns')) {
            return;
        }

        $columnIdMap = [];
        foreach (FormulaStepTableColumn::where('formula_step_id', $source->id)->get() as $column) {
            $newColumn = FormulaStepTableColumn::create([
                'formula_step_id' => $target->id,
                'label' => $column->label,
                'key' => $column->key,
                'column_type' => $column->column_type,
                'input_data_type' => $column->input_data_type,
                'expression' => $column->expression,
                'model_tied_to' => $column->model_tied_to,
                'dataset_config' => $column->dataset_config,
                'order' => $column->order,
                'is_required' => $column->is_required,
                'help_text' => $column->help_text,
            ]);
            $columnIdMap[$column->id] = $newColumn->id;
        }

        foreach (FormulaStepTableStaticRow::where('formula_step_id', $source->id)->with('cells')->get() as $staticRow) {
            $newRow = FormulaStepTableStaticRow::create([
                'formula_step_id' => $target->id,
                'order' => $staticRow->order,
                'label' => $staticRow->label,
            ]);

            foreach ($staticRow->cells as $cell) {
                $newColumnId = $columnIdMap[$cell->column_id] ?? null;
                if ($newColumnId) {
                    FormulaStepTableStaticCell::create([
                        'static_row_id' => $newRow->id,
                        'column_id' => $newColumnId,
                        'default_value' => $cell->default_value,
                    ]);
                }
            }
        }
    }
}
