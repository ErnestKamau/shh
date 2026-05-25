<?php

namespace App\Livewire\Equipment\Concerns;

use App\Models\Equipments\Logbook\EquipmentLogbookColumn;
use App\Models\Equipments\Logbook\EquipmentLogbookEntryValue;
use App\Services\Formulars\FormulaEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatabaseSchemaService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait InteractsWithEquipmentLogbookColumns
{
    /** @var array<int, array<string, mixed>> */
    public array $logbookColumns = [];

    public bool $showLogbookColumnModal = false;

    public bool $showDeleteLogbookColumnModal = false;

    public ?EquipmentLogbookColumn $editingLogbookColumn = null;

    public ?EquipmentLogbookColumn $deletingLogbookColumn = null;

    public string $columnLabel = '';

    public string $columnKey = '';

    public string $columnType = 'input';

    public string $columnInputDataType = 'string';

    public string $columnExpression = '';

    public int $columnOrder = 1;

    public bool $columnIsRequired = false;

    public string $columnHelpText = '';

    public string $columnDatasetSourceTable = '';

    public string $columnDatasetSourceColumn = '';

    public ?string $expressionValidationMessage = null;

    public function loadLogbookColumns(): void
    {
        $this->logbookColumns = EquipmentLogbookColumn::query()
            ->where('equipment_id', $this->equipmentId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function showCreateLogbookColumnModal(): void
    {
        $this->resetLogbookColumnForm();
        $this->columnOrder = count($this->logbookColumns) + 1;
        $this->showLogbookColumnModal = true;
    }

    public function showEditLogbookColumnModal(string $columnId): void
    {
        $column = EquipmentLogbookColumn::query()
            ->where('equipment_id', $this->equipmentId)
            ->findOrFail($columnId);

        $this->editingLogbookColumn = $column;
        $this->columnLabel = $column->label;
        $this->columnKey = $column->key;
        $this->columnType = $column->column_type;
        $this->columnInputDataType = $column->input_data_type;
        $this->columnExpression = $column->expression ?? '';
        $this->columnOrder = $column->order;
        $this->columnIsRequired = $column->is_required;
        $this->columnHelpText = $column->help_text ?? '';
        $config = $column->dataset_config;
        $this->columnDatasetSourceTable = $config['source_table'] ?? '';
        $this->columnDatasetSourceColumn = $config['source_display_column'] ?? '';
        $this->showLogbookColumnModal = true;
    }

    public function saveLogbookColumn(): void
    {
        $this->validate($this->logbookColumnRules());
        $this->validateLogbookDerivedExpression();

        $uniqueRule = 'unique:equipment_logbook_columns,key,NULL,id,equipment_id,'.$this->equipmentId;
        if ($this->editingLogbookColumn) {
            $uniqueRule = 'unique:equipment_logbook_columns,key,'.$this->editingLogbookColumn->id.',id,equipment_id,'.$this->equipmentId;
        }
        $this->validate(['columnKey' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/', $uniqueRule]]);

        $payload = [
            'equipment_id' => $this->equipmentId,
            'label' => $this->columnLabel,
            'key' => $this->columnKey,
            'column_type' => $this->columnType,
            'input_data_type' => $this->columnType === 'input' ? $this->columnInputDataType : 'string',
            'expression' => $this->columnType === 'derived' ? $this->columnExpression : null,
            'dataset_config' => $this->columnType === 'dataset' ? $this->buildLogbookColumnDatasetConfig() : null,
            'order' => $this->columnOrder,
            'is_required' => $this->columnIsRequired,
            'help_text' => $this->columnHelpText ?: null,
        ];

        if ($this->editingLogbookColumn) {
            $this->editingLogbookColumn->update($payload);
            session()->flash('logbook_message', 'Column updated.');
        } else {
            EquipmentLogbookColumn::create($payload);
            session()->flash('logbook_message', 'Column created.');
        }

        $this->showLogbookColumnModal = false;
        $this->resetLogbookColumnForm();
        $this->loadLogbookColumns();
        $this->resetLogCaptureForm();
    }

    public function showDeleteLogbookColumnModalInit(string $columnId): void
    {
        $this->deletingLogbookColumn = EquipmentLogbookColumn::query()
            ->where('equipment_id', $this->equipmentId)
            ->findOrFail($columnId);
        $this->showDeleteLogbookColumnModal = true;
    }

    public function deleteLogbookColumn(): void
    {
        if (! $this->deletingLogbookColumn) {
            return;
        }

        EquipmentLogbookEntryValue::query()
            ->where('column_id', $this->deletingLogbookColumn->id)
            ->delete();

        $this->deletingLogbookColumn->delete();
        $this->showDeleteLogbookColumnModal = false;
        $this->deletingLogbookColumn = null;
        $this->loadLogbookColumns();
        $this->resetLogCaptureForm();
        session()->flash('logbook_message', 'Column deleted.');
    }

    public function updatedColumnLabel(): void
    {
        if ($this->columnKey === '' && $this->columnLabel !== '') {
            $this->columnKey = Str::snake(Str::ascii($this->columnLabel));
        }
    }

    public function updatedColumnType(): void
    {
        if ($this->columnType !== 'dataset') {
            $this->columnDatasetSourceTable = '';
            $this->columnDatasetSourceColumn = '';
        }
        if ($this->columnType !== 'derived') {
            $this->columnExpression = '';
        }
    }

    public function updatedColumnDatasetSourceTable(): void
    {
        $this->columnDatasetSourceColumn = '';
    }

    public function validateLogbookExpressionPreview(): void
    {
        $this->expressionValidationMessage = null;
        if ($this->columnType !== 'derived' || $this->columnExpression === '') {
            return;
        }

        $priorKeys = collect($this->logbookColumns)
            ->when($this->editingLogbookColumn, fn ($c) => $c->filter(fn ($col) => ($col['id'] ?? '') !== $this->editingLogbookColumn->id))
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->columnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->columnExpression, $priorKeys);
        $this->expressionValidationMessage = $result['valid']
            ? 'Expression is valid.'
            : $result['message'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildLogbookColumnDatasetConfig(): array
    {
        return [
            'source_table' => $this->columnDatasetSourceTable,
            'display_mode' => 'direct',
            'source_display_column' => $this->columnDatasetSourceColumn,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function logbookColumnRules(): array
    {
        return [
            'columnLabel' => 'required|string|max:255',
            'columnType' => 'required|in:input,derived,dataset',
            'columnInputDataType' => 'required_if:columnType,input|in:string,number,date,boolean,textarea',
            'columnExpression' => 'nullable|required_if:columnType,derived|string',
            'columnDatasetSourceTable' => 'nullable|required_if:columnType,dataset|string',
            'columnDatasetSourceColumn' => 'nullable|required_if:columnType,dataset|string',
            'columnOrder' => 'required|integer|min:1',
            'columnIsRequired' => 'boolean',
            'columnHelpText' => 'nullable|string',
        ];
    }

    protected function validateLogbookDerivedExpression(): void
    {
        if ($this->columnType !== 'derived') {
            return;
        }

        $priorKeys = collect($this->logbookColumns)
            ->when($this->editingLogbookColumn, fn ($c) => $c->filter(fn ($col) => ($col['id'] ?? '') !== $this->editingLogbookColumn->id))
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->columnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->columnExpression, $priorKeys);
        if (! $result['valid']) {
            throw ValidationException::withMessages([
                'columnExpression' => [$result['message']],
            ]);
        }
    }

    protected function resetLogbookColumnForm(): void
    {
        $this->editingLogbookColumn = null;
        $this->columnLabel = '';
        $this->columnKey = '';
        $this->columnType = 'input';
        $this->columnInputDataType = 'string';
        $this->columnExpression = '';
        $this->columnOrder = 1;
        $this->columnIsRequired = false;
        $this->columnHelpText = '';
        $this->columnDatasetSourceTable = '';
        $this->columnDatasetSourceColumn = '';
        $this->expressionValidationMessage = null;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function logbookSchemaTableOptions(): array
    {
        return app(LogEntryDatabaseSchemaService::class)->tableOptions();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function logbookSchemaColumnOptions(): array
    {
        if ($this->columnDatasetSourceTable === '') {
            return [];
        }

        return app(LogEntryDatabaseSchemaService::class)->columnOptions($this->columnDatasetSourceTable);
    }
}
