<?php

namespace App\Livewire\Formulars\Concerns;

use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\FormulaStepTableColumn;
use App\Models\Formulars\FormulaStepTableStaticCell;
use App\Models\Formulars\FormulaStepTableStaticRow;
use App\Services\Formulars\FormulaEvaluator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait InteractsWithFormulaStepTableConfiguration
{
    protected function tableEditorNotify(string $message, string $type = 'success'): void
    {
        if (method_exists($this, 'setMessage')) {
            $this->setMessage($message, $type === 'success' ? 'success' : 'error');
        }
    }

    public function toggleCustomTableStepExpanded(string $stepId): void
    {
        if (in_array($stepId, $this->expandedCustomTableStepIds, true)) {
            $this->expandedCustomTableStepIds = array_values(array_filter(
                $this->expandedCustomTableStepIds,
                fn (string $id): bool => $id !== $stepId
            ));
        } else {
            $this->expandedCustomTableStepIds[] = $stepId;
        }
    }

    public function isCustomTableStepExpanded(string $stepId): bool
    {
        return in_array($stepId, $this->expandedCustomTableStepIds, true);
    }

    public function openConfigureTableModal(string $stepId): void
    {
        if (! $this->customStepTablesAvailable()) {
            $this->tableEditorNotify('Run migrations to enable custom step tables.', 'warning');

            return;
        }

        $step = FormulaStep::where('formula_version_id', $this->formulaVersion->id)->findOrFail($stepId);
        if (! $step->isCustomTable()) {
            $this->tableEditorNotify('This step is not a custom table step. Edit the step and set type to Custom Table.', 'error');

            return;
        }

        $this->configuringStepId = $stepId;
        $this->configureTableWizardStep = 1;
        $this->loadStepTableColumns();
        $this->loadStepStaticRows();
        $this->showConfigureTableModal = true;
    }

    public function closeConfigureTableModal(): void
    {
        $this->showConfigureTableModal = false;
        $this->configuringStepId = null;
        $this->configureTableWizardStep = 1;
        $this->resetStepColumnForm();
    }

    public function configureTableWizardGoTo(int $step): void
    {
        if ($step < 1 || $step > 3) {
            return;
        }

        if ($step >= 2 && count($this->stepTableColumns) === 0) {
            $this->tableEditorNotify('Add at least one column before continuing.', 'warning');

            return;
        }

        if ($step >= 2) {
            $this->loadStepStaticRows();
            $this->closeStepColumnModal();
        }

        $this->configureTableWizardStep = $step;
    }

    public function configureTableWizardNext(): void
    {
        if ($this->configureTableWizardStep >= 3) {
            $this->closeConfigureTableModal();

            return;
        }

        $this->configureTableWizardGoTo($this->configureTableWizardStep + 1);
    }

    public function configureTableWizardBack(): void
    {
        if ($this->configureTableWizardStep <= 1) {
            return;
        }

        $this->configureTableWizardGoTo($this->configureTableWizardStep - 1);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnIsStaticConfigurable(array $column): bool
    {
        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];

        return ($config['column_mode'] ?? '') === 'fixed';
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnDisplayTypeLabel(array $column): string
    {
        if ($this->stepTableColumnIsStaticConfigurable($column)) {
            return 'Static';
        }

        $columnType = (string) ($column['column_type'] ?? 'input');
        if ($columnType === 'derived') {
            return 'Derived';
        }
        if ($columnType === 'dataset') {
            return 'Dataset';
        }

        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];
        $inputKey = (string) ($config['input_data_type'] ?? $column['input_data_type'] ?? 'string');

        $labels = [
            'string' => 'Text',
            'number' => 'Number',
            'date' => 'Date',
            'time' => 'Time',
            'datetime' => 'Date & time',
            'boolean' => 'Yes/No',
            'textarea' => 'Long text',
            'radio' => 'Radio',
            'checkbox' => 'Checkbox',
            'select' => 'Dropdown',
        ];

        return $labels[$inputKey] ?? ucfirst($inputKey);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public function stepTableColumnDisplayTypeIcon(array $column): string
    {
        if ($this->stepTableColumnIsStaticConfigurable($column)) {
            return 'mdi-table-edit';
        }

        $columnType = (string) ($column['column_type'] ?? 'input');
        if ($columnType === 'derived') {
            return 'mdi-function';
        }
        if ($columnType === 'dataset') {
            return 'mdi-database-outline';
        }

        $config = is_array($column['dataset_config'] ?? null) ? $column['dataset_config'] : [];
        $inputKey = (string) ($config['input_data_type'] ?? $column['input_data_type'] ?? 'string');

        return match ($inputKey) {
            'number' => 'mdi-numeric',
            'date', 'datetime' => 'mdi-calendar',
            'time' => 'mdi-clock-outline',
            'boolean' => 'mdi-toggle-switch-outline',
            'textarea' => 'mdi-text-box-outline',
            'radio' => 'mdi-radiobox-marked',
            'checkbox' => 'mdi-checkbox-multiple-marked-outline',
            'select' => 'mdi-form-dropdown',
            default => 'mdi-form-textbox-outline',
        };
    }

    public function loadStepTableColumns(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            $this->stepTableColumns = [];

            return;
        }

        $this->stepTableColumns = FormulaStepTableColumn::where('formula_step_id', $this->configuringStepId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function loadStepStaticRows(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            $this->stepStaticRows = [];
            $this->stepStaticCellValues = [];

            return;
        }

        $rows = FormulaStepTableStaticRow::where('formula_step_id', $this->configuringStepId)
            ->with('cells')
            ->orderBy('order')
            ->get();

        $this->stepStaticRows = $rows->map(fn ($r) => [
            'id' => $r->id,
            'order' => $r->order,
            'label' => $r->label,
        ])->all();

        $this->stepStaticCellValues = [];
        foreach ($rows as $row) {
            foreach ($row->cells as $cell) {
                $this->stepStaticCellValues[$row->id][$cell->column_id] = $cell->default_value ?? '';
            }
        }
    }

    public function showCreateStepColumnModalInit(): void
    {
        $this->resetStepColumnForm();
        $this->stepColumnOrder = count($this->stepTableColumns) + 1;
        $this->showCreateStepColumnModal = true;
        $this->showEditStepColumnModal = false;
    }

    public function showEditStepColumnModalInit(string $columnId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $column = FormulaStepTableColumn::where('formula_step_id', $this->configuringStepId)
            ->findOrFail($columnId);

        $this->editingStepColumn = $column;
        $this->stepColumnLabel = $column->label;
        $this->stepColumnKey = $column->key;
        $this->stepColumnType = $column->column_type;
        $this->stepColumnInputDataType = $column->input_data_type;
        $this->stepColumnExpression = $column->expression ?? '';
        $this->stepColumnModelTiedTo = $column->model_tied_to ?? '';
        $this->stepColumnOrder = $column->order;
        $this->stepColumnIsRequired = $column->is_required;
        $this->stepColumnHelpText = $column->help_text ?? '';
        $config = is_array($column->dataset_config) ? $column->dataset_config : [];
        if (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'fixed')) {
            $this->stepColumnType = 'static';
        } elseif (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'static')) {
            $this->stepColumnType = 'input';
            $this->stepColumnInputDataType = (string) ($config['input_data_type'] ?? 'radio');
            $this->stepColumnStaticOptions = implode(
                PHP_EOL,
                array_map('strval', is_array($config['static_options'] ?? null) ? $config['static_options'] : [])
            );
        } elseif (($column->column_type ?? '') === 'input' && (($config['column_mode'] ?? '') === 'choices')) {
            $this->stepColumnType = 'input';
            $this->stepColumnInputDataType = (string) ($config['input_data_type'] ?? 'radio');
            $this->stepColumnStaticOptions = implode(
                PHP_EOL,
                array_map('strval', is_array($config['static_options'] ?? null) ? $config['static_options'] : [])
            );
        }
        $this->showCreateStepColumnModal = false;
        $this->showEditStepColumnModal = true;
    }

    public function createStepColumn(): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $this->validate($this->stepColumnRules());
        $this->validateStepColumnExpression();

        [$storageColumnType, $storageInputType, $datasetConfig] = $this->stepColumnStoragePayload();
        if ($this->getErrorBag()->has('stepColumnStaticOptions')) {
            return;
        }

        FormulaStepTableColumn::create([
            'formula_step_id' => $this->configuringStepId,
            'label' => $this->stepColumnLabel,
            'key' => $this->stepColumnKey,
            'column_type' => $storageColumnType,
            'input_data_type' => $storageInputType,
            'expression' => $this->stepColumnType === 'derived' ? $this->stepColumnExpression : null,
            'model_tied_to' => $this->stepColumnType === 'dataset' ? $this->stepColumnModelTiedTo : null,
            'dataset_config' => $datasetConfig,
            'order' => $this->stepColumnOrder,
            'is_required' => $this->stepColumnIsRequired,
            'help_text' => $this->stepColumnHelpText ?: null,
        ]);

        $this->showCreateStepColumnModal = false;
        $this->resetStepColumnForm();
        $this->loadStepTableColumns();
        $this->tableEditorNotify('Table column saved.', 'success');
    }

    public function updateStepColumn(): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        $this->validate($this->stepColumnRules());
        $this->validateStepColumnExpression();

        if (! $this->editingStepColumn) {
            return;
        }

        [$storageColumnType, $storageInputType, $datasetConfig] = $this->stepColumnStoragePayload();
        if ($this->getErrorBag()->has('stepColumnStaticOptions')) {
            return;
        }

        $this->editingStepColumn->update([
            'label' => $this->stepColumnLabel,
            'key' => $this->stepColumnKey,
            'column_type' => $storageColumnType,
            'input_data_type' => $storageInputType,
            'expression' => $this->stepColumnType === 'derived' ? $this->stepColumnExpression : null,
            'model_tied_to' => $this->stepColumnType === 'dataset' ? $this->stepColumnModelTiedTo : null,
            'dataset_config' => $datasetConfig,
            'order' => $this->stepColumnOrder,
            'is_required' => $this->stepColumnIsRequired,
            'help_text' => $this->stepColumnHelpText ?: null,
        ]);

        $this->showEditStepColumnModal = false;
        $this->resetStepColumnForm();
        $this->loadStepTableColumns();
        $this->tableEditorNotify('Table column updated.', 'success');
    }

    public function deleteStepColumn(string $columnId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        FormulaStepTableColumn::where('formula_step_id', $this->configuringStepId)
            ->where('id', $columnId)
            ->delete();

        $this->loadStepTableColumns();
        $this->loadStepStaticRows();
        $this->tableEditorNotify('Table column deleted.', 'success');
    }

    public function addStaticRow(): void
    {
        if (! $this->configuringStepId || ! $this->customStepTablesAvailable()) {
            return;
        }

        $maxOrder = (int) FormulaStepTableStaticRow::where('formula_step_id', $this->configuringStepId)->max('order');
        $row = FormulaStepTableStaticRow::create([
            'formula_step_id' => $this->configuringStepId,
            'order' => $maxOrder + 1,
            'label' => trim($this->newStaticRowLabel) !== '' ? trim($this->newStaticRowLabel) : 'Row '.($maxOrder + 1),
        ]);

        foreach (FormulaStepTableColumn::where('formula_step_id', $this->configuringStepId)->get() as $column) {
            FormulaStepTableStaticCell::create([
                'static_row_id' => $row->id,
                'column_id' => $column->id,
                'default_value' => null,
            ]);
        }

        $this->loadStepStaticRows();
        $this->closeInlineStaticRowForm();
    }

    public function showInlineStaticRowFormInit(): void
    {
        $this->newStaticRowLabel = '';
        $this->showInlineStaticRowForm = true;
    }

    public function closeInlineStaticRowForm(): void
    {
        $this->newStaticRowLabel = '';
        $this->showInlineStaticRowForm = false;
    }

    public function saveStaticRowLabel(string $rowId, ?string $label): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        FormulaStepTableStaticRow::where('id', $rowId)
            ->where('formula_step_id', $this->configuringStepId)
            ->update(['label' => $label !== null ? trim($label) : null]);

        $this->loadStepStaticRows();
    }

    public function removeStaticRow(string $rowId): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        FormulaStepTableStaticCell::where('static_row_id', $rowId)->delete();
        FormulaStepTableStaticRow::where('id', $rowId)
            ->where('formula_step_id', $this->configuringStepId)
            ->delete();

        $this->loadStepStaticRows();
    }

    public function saveStaticCell(string $rowId, string $columnId, ?string $value): void
    {
        if (! $this->customStepTablesAvailable()) {
            return;
        }

        FormulaStepTableStaticCell::updateOrCreate(
            [
                'static_row_id' => $rowId,
                'column_id' => $columnId,
            ],
            ['default_value' => $value],
        );
    }

    public function updatedStepColumnLabel(): void
    {
        if ($this->stepColumnKey === '' && $this->stepColumnLabel !== '') {
            $this->stepColumnKey = Str::snake(Str::ascii($this->stepColumnLabel));
        }
    }

    public function updatedStepColumnType(): void
    {
        if ($this->stepColumnType !== 'dataset') {
            $this->stepColumnModelTiedTo = '';
        }
        if ($this->stepColumnType !== 'derived') {
            $this->stepColumnExpression = '';
        }
        if ($this->stepColumnType !== 'static') {
            $this->stepColumnChoiceControl = 'radio';
            if (! in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true)) {
                $this->stepColumnStaticOptions = '';
            }
        }
    }

    public function updatedStepColumnInputDataType(): void
    {
        if (! in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true) && $this->stepColumnType !== 'static') {
            $this->stepColumnStaticOptions = '';
        }
    }

    public function validateStepColumnExpressionPreview(): void
    {
        $this->stepColumnValidationMessage = null;
        if ($this->stepColumnType !== 'derived' || $this->stepColumnExpression === '') {
            return;
        }

        $priorKeys = collect($this->stepTableColumns)
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->stepColumnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->stepColumnExpression, $priorKeys);
        $this->stepColumnValidationMessage = $result['valid']
            ? 'Expression is valid.'
            : $result['message'];
    }

    public function closeStepColumnModal(): void
    {
        $this->showCreateStepColumnModal = false;
        $this->showEditStepColumnModal = false;
        $this->resetStepColumnForm();
    }

    protected function validateStepColumnExpression(): void
    {
        if ($this->stepColumnType !== 'derived' || $this->stepColumnExpression === '') {
            return;
        }

        $priorKeys = collect($this->stepTableColumns)
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->stepColumnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->stepColumnExpression, $priorKeys);
        if (! $result['valid']) {
            $this->addError('stepColumnExpression', $result['message']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function stepColumnRules(): array
    {
        return [
            'stepColumnLabel' => 'required|string|max:255',
            'stepColumnKey' => 'required|string|max:255|regex:/^[a-z][a-z0-9_]*$/',
            'stepColumnType' => 'required|in:input,derived,dataset,static',
            'stepColumnInputDataType' => 'required_if:stepColumnType,input|in:string,number,date,time,datetime,boolean,textarea,radio,checkbox,select',
            'stepColumnExpression' => 'nullable|required_if:stepColumnType,derived|string',
            'stepColumnModelTiedTo' => 'nullable|required_if:stepColumnType,dataset|string',
            'stepColumnChoiceControl' => 'nullable|in:radio,checkbox,select',
            'stepColumnStaticOptions' => 'nullable|string',
            'stepColumnOrder' => 'required|integer|min:1',
            'stepColumnIsRequired' => 'boolean',
            'stepColumnHelpText' => 'nullable|string',
        ];
    }

    protected function resetStepColumnForm(): void
    {
        $this->editingStepColumn = null;
        $this->stepColumnLabel = '';
        $this->stepColumnKey = '';
        $this->stepColumnType = 'input';
        $this->stepColumnInputDataType = 'string';
        $this->stepColumnExpression = '';
        $this->stepColumnModelTiedTo = '';
        $this->stepColumnOrder = 1;
        $this->stepColumnIsRequired = false;
        $this->stepColumnHelpText = '';
        $this->stepColumnChoiceControl = 'radio';
        $this->stepColumnStaticOptions = '';
        $this->stepColumnValidationMessage = null;
        $this->showInlineStaticRowForm = false;
        $this->newStaticRowLabel = '';
    }

    /**
     * @return array{0: string, 1: string, 2: array<string, mixed>|null}
     */
    protected function stepColumnStoragePayload(): array
    {
        if ($this->stepColumnType === 'static') {
            return [
                'input',
                'string',
                [
                    'column_mode' => 'fixed',
                ],
            ];
        }

        $inputType = $this->stepColumnType === 'input'
            ? (in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true) ? 'string' : $this->stepColumnInputDataType)
            : 'string';

        $datasetConfig = null;
        if ($this->stepColumnType === 'input' && in_array($this->stepColumnInputDataType, ['radio', 'checkbox', 'select'], true)) {
            $options = collect(preg_split('/\r\n|\r|\n/', $this->stepColumnStaticOptions) ?: [])
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($options) === 0) {
                $this->addError('stepColumnStaticOptions', 'Add at least one option for this input type.');

                return ['input', 'string', null];
            }

            $datasetConfig = [
                'column_mode' => 'choices',
                'choice_control' => $this->stepColumnInputDataType,
                'static_options' => $options,
                'input_data_type' => $this->stepColumnInputDataType,
            ];
        }

        return [$this->stepColumnType, $inputType, $datasetConfig];
    }


    public function copyFormulaStepTableDefinition(FormulaStep $source, FormulaStep $target): void
    {
        if (! $this->customStepTablesAvailable()) {
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

    protected function customStepTablesAvailable(): bool
    {
        return Schema::hasTable('formula_step_table_columns')
            && Schema::hasTable('formula_step_table_static_rows')
            && Schema::hasTable('formula_step_table_static_cells');
    }
}
