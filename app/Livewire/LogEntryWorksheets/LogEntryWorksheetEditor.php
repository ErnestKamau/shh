<?php

namespace App\Livewire\LogEntryWorksheets;

use App\Enums\LogEntryWorksheet\LogEntryColumnType;
use App\Enums\LogEntryWorksheet\LogEntryRowDriver;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Services\Formulars\FormulaEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatabaseSchemaService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LogEntryWorksheetEditor extends Component
{
    use AppliesCaseInsensitiveSearch;

    public LogEntryWorksheet $worksheet;

    public string $activeTab = 'general';

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public string $document_control_no = '';

    public string $revision = '';

    public ?string $issue_date = null;

    public string $row_driver = 'captured_result';

    public string $mandatory_fields_placement = 'top';

    public bool $allow_manual_rows = true;

    /** @var array<int, array<string, mixed>> */
    public array $columns = [];

    public string $columnSearch = '';

    /** @var array<int, array<string, mixed>> */
    public array $mandatoryFields = [];

    public string $fieldSearch = '';

    public bool $showCreateColumnModal = false;

    public bool $showEditColumnModal = false;

    public ?LogEntryWorksheetColumn $editingColumn = null;

    public string $columnLabel = '';

    public string $columnKey = '';

    public string $columnType = 'input';

    public string $columnInputDataType = 'string';

    public string $columnExpression = '';

    public int $columnOrder = 1;

    public bool $columnIsRequired = false;

    public string $columnHelpText = '';

    public string $columnDatasetSourceTable = '';

    public string $columnDatasetDisplayMode = 'direct';

    public string $columnDatasetSourceColumn = '';

    public string $columnDatasetFkColumn = '';

    public string $columnDatasetReferencedTable = '';

    public string $columnDatasetReferencedKeyColumn = 'id';

    public string $columnDatasetReferencedDisplayColumn = '';

    public bool $showColumnDatasetSourceTableDropdown = false;

    public string $columnDatasetSourceTableSearch = '';

    public string $selectedColumnDatasetSourceTableLabel = '';

    public bool $showColumnDatasetSourceColumnDropdown = false;

    public string $columnDatasetSourceColumnSearch = '';

    public string $selectedColumnDatasetSourceColumnLabel = '';

    public bool $showColumnDatasetFkDropdown = false;

    public string $columnDatasetFkSearch = '';

    public string $selectedColumnDatasetFkLabel = '';

    public bool $showColumnDatasetReferencedDisplayDropdown = false;

    public string $columnDatasetReferencedDisplayColumnSearch = '';

    public string $selectedColumnDatasetReferencedDisplayLabel = '';

    public bool $showDeleteColumnModal = false;

    public ?LogEntryWorksheetColumn $deletingColumn = null;

    public bool $showCreateFieldModal = false;

    public bool $showEditFieldModal = false;

    public ?LogEntryWorksheetMandatoryField $editingField = null;

    public string $fieldLabel = '';

    public string $fieldType = 'input';

    public int $fieldOrder = 1;

    public string $fieldHelpText = '';

    public bool $fieldIsRequired = true;

    public string $fieldValueName = '';

    /** @var array<int, string> */
    public array $fieldChoiceOptions = [];

    public string $fieldNewChoice = '';

    public bool $fieldDefaultCurrentDate = false;

    public bool $fieldDefaultAuthenticatedUser = false;

    public string $fieldDatasetSourceTable = '';

    public string $fieldDatasetDisplayMode = 'direct';

    public string $fieldDatasetSourceColumn = '';

    public string $fieldDatasetFkColumn = '';

    public string $fieldDatasetReferencedTable = '';

    public string $fieldDatasetReferencedKeyColumn = 'id';

    public string $fieldDatasetReferencedDisplayColumn = '';

    public bool $showDeleteFieldModal = false;

    public ?LogEntryWorksheetMandatoryField $deletingField = null;

    public ?string $expressionValidationMessage = null;

    public function mount(LogEntryWorksheet $worksheet): void
    {
        $this->worksheet = $worksheet;
        $this->loadWorksheet();
        $this->loadColumns();
        $this->loadMandatoryFields();
    }

    public function render()
    {
        $schema = app(LogEntryDatabaseSchemaService::class);

        return view('livewire.log-entry-worksheets.log-entry-worksheet-editor', [
            'rowDriverOptions' => LogEntryRowDriver::options(),
            'columnTypeOptions' => LogEntryColumnType::options(),
            'inputDataTypeOptions' => [
                'string' => 'Text',
                'number' => 'Number',
                'date' => 'Date',
                'boolean' => 'Yes/No',
                'textarea' => 'Long text',
            ],
            'mandatoryFieldTypes' => LogEntryWorksheetMandatoryField::getFieldTypes(),
            'schemaTableOptions' => $schema->tableOptions(),
            'filteredColumnSchemaTableOptions' => $this->filteredColumnSchemaTableOptions($schema->tableOptions()),
            'columnSourceColumns' => $this->columnDatasetSourceTable !== ''
                ? $schema->columnOptions($this->columnDatasetSourceTable)
                : [],
            'filteredColumnSourceColumnOptions' => $this->filteredColumnSourceColumnOptions(
                $this->columnDatasetSourceTable !== ''
                    ? $schema->columnOptions($this->columnDatasetSourceTable)
                    : []
            ),
            'columnForeignKeys' => $this->columnDatasetSourceTable !== ''
                ? $schema->foreignKeyOptions($this->columnDatasetSourceTable)
                : [],
            'filteredColumnForeignKeyOptions' => $this->filteredColumnForeignKeyOptions(
                $this->columnDatasetSourceTable !== ''
                    ? $schema->foreignKeyOptions($this->columnDatasetSourceTable)
                    : []
            ),
            'columnReferencedColumns' => $this->columnDatasetReferencedTable !== ''
                ? $schema->columnOptions($this->columnDatasetReferencedTable)
                : [],
            'filteredColumnReferencedDisplayOptions' => $this->filteredColumnReferencedDisplayOptions(
                $this->columnDatasetReferencedTable !== ''
                    ? $schema->columnOptions($this->columnDatasetReferencedTable)
                    : []
            ),
            'fieldSourceColumns' => $this->fieldDatasetSourceTable !== ''
                ? $schema->columnOptions($this->fieldDatasetSourceTable)
                : [],
            'fieldForeignKeys' => $this->fieldDatasetSourceTable !== ''
                ? $schema->foreignKeyOptions($this->fieldDatasetSourceTable)
                : [],
            'fieldReferencedColumns' => $this->fieldDatasetReferencedTable !== ''
                ? $schema->columnOptions($this->fieldDatasetReferencedTable)
                : [],
            'showFieldDefaultCurrentDate' => $this->showFieldDefaultCurrentDateOption(),
            'showFieldDefaultAuthenticatedUser' => $this->showFieldDefaultAuthenticatedUserOption(),
        ]);
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'row_driver' => 'required|in:'.implode(',', array_column(LogEntryRowDriver::cases(), 'value')),
            'mandatory_fields_placement' => 'required|in:top,bottom',
            'allow_manual_rows' => 'boolean',
            'issue_date' => 'nullable|date',
        ]);

        $this->worksheet->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'document_control_no' => $this->document_control_no ?: null,
            'revision' => $this->revision ?: null,
            'issue_date' => $this->issue_date ?: null,
            'row_driver' => $this->row_driver,
            'row_driver_filters' => null,
            'mandatory_fields_placement' => $this->mandatory_fields_placement,
            'allow_manual_rows' => $this->allow_manual_rows,
        ]);

        $this->worksheet->refresh();
        session()->flash('message', 'Worksheet settings saved.');
    }

    protected function loadWorksheet(): void
    {
        $w = $this->worksheet;
        $this->name = $w->name;
        $this->description = $w->description ?? '';
        $this->is_active = $w->is_active;
        $this->document_control_no = $w->document_control_no ?? '';
        $this->revision = $w->revision ?? '';
        $this->issue_date = $w->issue_date?->format('Y-m-d');
        $this->row_driver = $w->row_driver;
        $this->mandatory_fields_placement = $w->mandatory_fields_placement;
        $this->allow_manual_rows = $w->allow_manual_rows;
    }

    public function loadColumns(): void
    {
        $query = LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->worksheet->id)
            ->orderBy('order');

        if ($this->columnSearch !== '') {
            $this->applyCaseInsensitiveSearch($query, ['label', 'key'], (string) $this->columnSearch);
        }

        $this->columns = $query->get()->toArray();
    }

    public function loadMandatoryFields(): void
    {
        $query = LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->worksheet->id)
            ->orderBy('order');

        if ($this->fieldSearch !== '') {
            $this->applyCaseInsensitiveSearch($query, ['label', 'field_value_name'], (string) $this->fieldSearch);
        }

        $this->mandatoryFields = $query->get()->toArray();
    }

    public function showCreateColumnModalInit(): void
    {
        $this->resetColumnForm();
        $this->columnOrder = count($this->columns) + 1;
        $this->activeTab = 'columns';
        $this->showCreateColumnModal = true;
    }

    public function showEditColumnModalInit(string $columnId): void
    {
        $column = LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->worksheet->id)->findOrFail($columnId);
        $this->editingColumn = $column;
        $this->columnLabel = $column->label;
        $this->columnKey = $column->key;
        $this->columnType = $column->column_type;
        $this->columnInputDataType = $column->input_data_type;
        $this->columnExpression = $column->expression ?? '';
        $this->columnOrder = $column->order;
        $this->columnIsRequired = $column->is_required;
        $this->columnHelpText = $column->help_text ?? '';
        $this->loadColumnDatasetFromConfig($column->dataset_config);
        $this->activeTab = 'columns';
        $this->showEditColumnModal = true;
    }

    public function createColumn(): void
    {
        $this->validate($this->columnRules());
        $this->validateDerivedExpression();

        LogEntryWorksheetColumn::create([
            'log_entry_worksheet_id' => $this->worksheet->id,
            'label' => $this->columnLabel,
            'key' => $this->columnKey,
            'column_type' => $this->columnType,
            'input_data_type' => $this->columnType === 'input' ? $this->columnInputDataType : 'string',
            'expression' => $this->columnType === 'derived' ? $this->columnExpression : null,
            'model_tied_to' => null,
            'dataset_config' => $this->columnType === 'dataset' ? $this->buildColumnDatasetConfig() : null,
            'order' => $this->columnOrder,
            'is_required' => $this->columnIsRequired,
            'help_text' => $this->columnHelpText ?: null,
        ]);

        $this->showCreateColumnModal = false;
        $this->resetColumnForm();
        $this->loadColumns();
        session()->flash('message', 'Column created.');
    }

    public function updateColumn(): void
    {
        $this->validate($this->columnRules());
        $this->validateDerivedExpression();

        if (! $this->editingColumn) {
            return;
        }

        $this->editingColumn->update([
            'label' => $this->columnLabel,
            'key' => $this->columnKey,
            'column_type' => $this->columnType,
            'input_data_type' => $this->columnType === 'input' ? $this->columnInputDataType : 'string',
            'expression' => $this->columnType === 'derived' ? $this->columnExpression : null,
            'model_tied_to' => null,
            'dataset_config' => $this->columnType === 'dataset' ? $this->buildColumnDatasetConfig() : null,
            'order' => $this->columnOrder,
            'is_required' => $this->columnIsRequired,
            'help_text' => $this->columnHelpText ?: null,
        ]);

        $this->showEditColumnModal = false;
        $this->resetColumnForm();
        $this->loadColumns();
        session()->flash('message', 'Column updated.');
    }

    public function updatedColumnDatasetSourceTable(): void
    {
        $this->columnDatasetSourceColumn = '';
        $this->columnDatasetFkColumn = '';
        $this->columnDatasetReferencedTable = '';
        $this->columnDatasetReferencedDisplayColumn = '';
        $this->selectedColumnDatasetSourceColumnLabel = '';
        $this->columnDatasetSourceColumnSearch = '';
        $this->showColumnDatasetSourceColumnDropdown = false;
        $this->selectedColumnDatasetFkLabel = '';
        $this->columnDatasetFkSearch = '';
        $this->showColumnDatasetFkDropdown = false;
        $this->selectedColumnDatasetReferencedDisplayLabel = '';
        $this->columnDatasetReferencedDisplayColumnSearch = '';
        $this->showColumnDatasetReferencedDisplayDropdown = false;
    }

    public function updatedColumnDatasetSourceTableSearch(): void
    {
        $this->showColumnDatasetSourceTableDropdown = true;
    }

    public function selectColumnDatasetSourceTable(string $table): void
    {
        $this->columnDatasetSourceTable = $table;
        $this->syncColumnDatasetSourceTableLabel();
        $this->showColumnDatasetSourceTableDropdown = false;
        $this->columnDatasetSourceTableSearch = '';
        $this->updatedColumnDatasetSourceTable();
    }

    public function clearColumnDatasetSourceTable(): void
    {
        $this->columnDatasetSourceTable = '';
        $this->selectedColumnDatasetSourceTableLabel = '';
        $this->columnDatasetSourceTableSearch = '';
        $this->showColumnDatasetSourceTableDropdown = false;
        $this->updatedColumnDatasetSourceTable();
    }

    public function updatedColumnDatasetSourceColumnSearch(): void
    {
        $this->showColumnDatasetSourceColumnDropdown = true;
    }

    public function selectColumnDatasetSourceColumn(string $column): void
    {
        $this->columnDatasetSourceColumn = $column;
        $this->syncColumnDatasetSourceColumnLabel();
        $this->showColumnDatasetSourceColumnDropdown = false;
        $this->columnDatasetSourceColumnSearch = '';
    }

    public function clearColumnDatasetSourceColumn(): void
    {
        $this->columnDatasetSourceColumn = '';
        $this->selectedColumnDatasetSourceColumnLabel = '';
        $this->columnDatasetSourceColumnSearch = '';
        $this->showColumnDatasetSourceColumnDropdown = false;
    }

    public function updatedColumnDatasetFkSearch(): void
    {
        $this->showColumnDatasetFkDropdown = true;
    }

    public function selectColumnDatasetFkColumn(string $column): void
    {
        $this->columnDatasetFkColumn = $column;
        $this->syncColumnDatasetFkLabel();
        $this->showColumnDatasetFkDropdown = false;
        $this->columnDatasetFkSearch = '';
        $this->applyColumnDatasetFkSelection();
    }

    public function clearColumnDatasetFkColumn(): void
    {
        $this->columnDatasetFkColumn = '';
        $this->selectedColumnDatasetFkLabel = '';
        $this->columnDatasetFkSearch = '';
        $this->showColumnDatasetFkDropdown = false;
        $this->columnDatasetReferencedTable = '';
        $this->columnDatasetReferencedDisplayColumn = '';
        $this->selectedColumnDatasetReferencedDisplayLabel = '';
        $this->columnDatasetReferencedDisplayColumnSearch = '';
        $this->showColumnDatasetReferencedDisplayDropdown = false;
    }

    public function updatedColumnDatasetFkColumn(): void
    {
        $this->applyColumnDatasetFkSelection();
    }

    public function updatedColumnDatasetReferencedDisplayColumnSearch(): void
    {
        $this->showColumnDatasetReferencedDisplayDropdown = true;
    }

    public function selectColumnDatasetReferencedDisplayColumn(string $column): void
    {
        $this->columnDatasetReferencedDisplayColumn = $column;
        $this->syncColumnDatasetReferencedDisplayLabel();
        $this->showColumnDatasetReferencedDisplayDropdown = false;
        $this->columnDatasetReferencedDisplayColumnSearch = '';
    }

    public function clearColumnDatasetReferencedDisplayColumn(): void
    {
        $this->columnDatasetReferencedDisplayColumn = '';
        $this->selectedColumnDatasetReferencedDisplayLabel = '';
        $this->columnDatasetReferencedDisplayColumnSearch = '';
        $this->showColumnDatasetReferencedDisplayDropdown = false;
    }

    public function updatedColumnDatasetDisplayMode(): void
    {
        if ($this->columnDatasetDisplayMode === 'direct') {
            $this->columnDatasetFkColumn = '';
            $this->columnDatasetReferencedTable = '';
            $this->columnDatasetReferencedDisplayColumn = '';
            $this->selectedColumnDatasetFkLabel = '';
            $this->selectedColumnDatasetReferencedDisplayLabel = '';
            $this->columnDatasetFkSearch = '';
            $this->columnDatasetReferencedDisplayColumnSearch = '';
        } else {
            $this->columnDatasetSourceColumn = '';
            $this->selectedColumnDatasetSourceColumnLabel = '';
            $this->columnDatasetSourceColumnSearch = '';
        }
    }

    public function showDeleteColumnModalInit(string $columnId): void
    {
        $this->deletingColumn = LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->worksheet->id)->findOrFail($columnId);
        $this->showDeleteColumnModal = true;
    }

    public function deleteColumn(): void
    {
        $this->deletingColumn?->delete();
        $this->showDeleteColumnModal = false;
        $this->deletingColumn = null;
        $this->loadColumns();
        session()->flash('message', 'Column deleted.');
    }

    public function updateColumnOrder(array $columnIds): void
    {
        LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->worksheet->id)->update(['order' => 9999]);

        foreach ($columnIds as $index => $columnId) {
            LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->worksheet->id)
                ->where('id', $columnId)
                ->update(['order' => $index + 1]);
        }

        $this->loadColumns();
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
            $this->resetColumnDatasetFields();
        }
        if ($this->columnType !== 'derived') {
            $this->columnExpression = '';
        }
    }

    public function validateExpressionPreview(): void
    {
        $this->expressionValidationMessage = null;
        if ($this->columnType !== 'derived' || $this->columnExpression === '') {
            return;
        }

        $priorKeys = collect($this->columns)
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->columnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->columnExpression, $priorKeys);
        $this->expressionValidationMessage = $result['valid']
            ? 'Expression is valid.'
            : $result['message'];
    }

    public function showCreateFieldModalInit(): void
    {
        $this->resetFieldForm();
        $this->fieldOrder = count($this->mandatoryFields) + 1;
        $this->activeTab = 'mandatory';
        $this->showCreateFieldModal = true;
    }

    public function showEditFieldModalInit(string $fieldId): void
    {
        $field = LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->worksheet->id)->findOrFail($fieldId);
        $this->editingField = $field;
        $this->fieldLabel = $field->label;
        $this->fieldType = $this->resolveFieldTypeForForm($field);
        $this->fieldOrder = $field->order;
        $this->fieldHelpText = $field->help_text ?? '';
        $this->fieldIsRequired = $field->is_required;
        $this->fieldValueName = $field->field_value_name;
        $this->fieldDefaultCurrentDate = $field->default_current_date;
        $this->fieldDefaultAuthenticatedUser = $field->default_authenticated_user;
        $options = $field->field_options['options'] ?? [];
        $this->fieldChoiceOptions = is_array($options) ? array_values($options) : [];
        $this->fieldNewChoice = '';
        $this->loadFieldDatasetFromConfig($field->dataset_config);
        $this->activeTab = 'mandatory';
        $this->showEditFieldModal = true;
    }

    public function addFieldChoice(): void
    {
        $value = trim($this->fieldNewChoice);
        if ($value === '') {
            $this->addError('fieldNewChoice', 'Enter a choice before adding.');

            return;
        }

        foreach ($this->fieldChoiceOptions as $existing) {
            if (strcasecmp(trim($existing), $value) === 0) {
                $this->addError('fieldNewChoice', 'This choice already exists.');

                return;
            }
        }

        $this->fieldChoiceOptions[] = $value;
        $this->fieldNewChoice = '';
        $this->resetErrorBag('fieldNewChoice');
    }

    public function removeFieldChoice(int $index): void
    {
        if (! array_key_exists($index, $this->fieldChoiceOptions)) {
            return;
        }

        unset($this->fieldChoiceOptions[$index]);
        $this->fieldChoiceOptions = array_values($this->fieldChoiceOptions);
    }

    public function createMandatoryField(): void
    {
        $this->validate($this->mandatoryFieldRules());
        $this->validateChoiceOptionsForCheckboxRadio();

        LogEntryWorksheetMandatoryField::create($this->buildMandatoryFieldPayload());

        $this->showCreateFieldModal = false;
        $this->resetFieldForm();
        $this->loadMandatoryFields();
        session()->flash('message', 'Mandatory field created.');
    }

    public function updateMandatoryField(): void
    {
        $this->validate($this->mandatoryFieldRules());
        $this->validateChoiceOptionsForCheckboxRadio();

        if (! $this->editingField) {
            return;
        }

        $this->editingField->update($this->buildMandatoryFieldPayload());

        $this->showEditFieldModal = false;
        $this->resetFieldForm();
        $this->loadMandatoryFields();
        session()->flash('message', 'Mandatory field updated.');
    }

    public function updatedFieldType(): void
    {
        if ($this->fieldType !== 'dataset_related') {
            $this->resetFieldDatasetFields();
        }

        if (! in_array($this->fieldType, ['date', 'datetime'], true)) {
            $this->fieldDefaultCurrentDate = false;
        }

        if ($this->fieldType !== 'users') {
            $this->fieldDefaultAuthenticatedUser = false;
        }

        if (! in_array($this->fieldType, ['checkbox', 'radio'], true)) {
            $this->fieldChoiceOptions = [];
            $this->fieldNewChoice = '';
        }
    }

    public function updatedFieldDatasetSourceTable(): void
    {
        $this->fieldDatasetSourceColumn = '';
        $this->fieldDatasetFkColumn = '';
        $this->fieldDatasetReferencedTable = '';
        $this->fieldDatasetReferencedDisplayColumn = '';
        $this->fieldDefaultCurrentDate = false;
        $this->fieldDefaultAuthenticatedUser = false;
    }

    public function updatedFieldDatasetFkColumn(): void
    {
        if ($this->fieldDatasetFkColumn === '') {
            return;
        }

        $fks = app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($this->fieldDatasetSourceTable);
        $match = collect($fks)->firstWhere('column', $this->fieldDatasetFkColumn);
        if ($match) {
            $this->fieldDatasetReferencedTable = $match['referenced_table'];
            $this->fieldDatasetReferencedKeyColumn = $match['referenced_column'];
            $this->fieldDatasetReferencedDisplayColumn = '';
        }
    }

    public function showDeleteFieldModalInit(string $fieldId): void
    {
        $this->deletingField = LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->worksheet->id)->findOrFail($fieldId);
        $this->showDeleteFieldModal = true;
    }

    public function deleteMandatoryField(): void
    {
        $this->deletingField?->delete();
        $this->showDeleteFieldModal = false;
        $this->deletingField = null;
        $this->loadMandatoryFields();
        session()->flash('message', 'Mandatory field deleted.');
    }

    public function updateFieldOrder(array $fieldIds): void
    {
        LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->worksheet->id)->update(['order' => 9999]);

        foreach ($fieldIds as $index => $fieldId) {
            LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->worksheet->id)
                ->where('id', $fieldId)
                ->update(['order' => $index + 1]);
        }

        $this->loadMandatoryFields();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildColumnDatasetConfig(): array
    {
        return [
            'source_table' => $this->columnDatasetSourceTable,
            'display_mode' => $this->columnDatasetDisplayMode,
            'source_display_column' => $this->columnDatasetDisplayMode === 'direct'
                ? $this->columnDatasetSourceColumn
                : null,
            'foreign_key_column' => $this->columnDatasetDisplayMode === 'foreign_key'
                ? $this->columnDatasetFkColumn
                : null,
            'referenced_table' => $this->columnDatasetDisplayMode === 'foreign_key'
                ? $this->columnDatasetReferencedTable
                : null,
            'referenced_key_column' => $this->columnDatasetReferencedKeyColumn ?: 'id',
            'referenced_display_column' => $this->columnDatasetDisplayMode === 'foreign_key'
                ? $this->columnDatasetReferencedDisplayColumn
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    protected function loadColumnDatasetFromConfig(?array $config): void
    {
        $this->resetColumnDatasetFields();
        if (! $config) {
            return;
        }

        $this->columnDatasetSourceTable = $config['source_table'] ?? '';
        $this->columnDatasetDisplayMode = $config['display_mode'] ?? 'direct';
        $this->columnDatasetSourceColumn = $config['source_display_column'] ?? '';
        $this->columnDatasetFkColumn = $config['foreign_key_column'] ?? '';
        $this->columnDatasetReferencedTable = $config['referenced_table'] ?? '';
        $this->columnDatasetReferencedKeyColumn = $config['referenced_key_column'] ?? 'id';
        $this->columnDatasetReferencedDisplayColumn = $config['referenced_display_column'] ?? '';
        $this->syncColumnDatasetSourceTableLabel();
        $this->syncColumnDatasetSourceColumnLabel();
        $this->syncColumnDatasetFkLabel();
        $this->syncColumnDatasetReferencedDisplayLabel();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMandatoryFieldPayload(): array
    {
        $options = null;
        if (in_array($this->fieldType, ['checkbox', 'radio'], true)) {
            $options = [
                'options' => $this->normalizedFieldChoiceOptions(),
            ];
        }

        return [
            'log_entry_worksheet_id' => $this->worksheet->id,
            'label' => $this->fieldLabel,
            'field_type' => $this->fieldType,
            'order' => $this->fieldOrder,
            'help_text' => $this->fieldHelpText ?: null,
            'model_tied_to' => $this->resolveMandatoryFieldModelTiedTo(),
            'is_required' => $this->fieldIsRequired,
            'field_value_name' => $this->fieldValueName,
            'field_options' => $options,
            'dataset_config' => $this->fieldType === 'dataset_related' ? $this->buildFieldDatasetConfig() : null,
            'default_current_date' => $this->fieldDefaultCurrentDate,
            'default_authenticated_user' => $this->fieldDefaultAuthenticatedUser,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFieldDatasetConfig(): array
    {
        return [
            'source_table' => $this->fieldDatasetSourceTable,
            'display_mode' => $this->fieldDatasetDisplayMode,
            'source_display_column' => $this->fieldDatasetDisplayMode === 'direct'
                ? $this->fieldDatasetSourceColumn
                : null,
            'foreign_key_column' => $this->fieldDatasetDisplayMode === 'foreign_key'
                ? $this->fieldDatasetFkColumn
                : null,
            'referenced_table' => $this->fieldDatasetDisplayMode === 'foreign_key'
                ? $this->fieldDatasetReferencedTable
                : null,
            'referenced_key_column' => $this->fieldDatasetReferencedKeyColumn ?: 'id',
            'referenced_display_column' => $this->fieldDatasetDisplayMode === 'foreign_key'
                ? $this->fieldDatasetReferencedDisplayColumn
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    protected function loadFieldDatasetFromConfig(?array $config): void
    {
        $this->resetFieldDatasetFields();
        if (! $config) {
            return;
        }

        $this->fieldDatasetSourceTable = $config['source_table'] ?? '';
        $this->fieldDatasetDisplayMode = $config['display_mode'] ?? 'direct';
        $this->fieldDatasetSourceColumn = $config['source_display_column'] ?? '';
        $this->fieldDatasetFkColumn = $config['foreign_key_column'] ?? '';
        $this->fieldDatasetReferencedTable = $config['referenced_table'] ?? '';
        $this->fieldDatasetReferencedKeyColumn = $config['referenced_key_column'] ?? 'id';
        $this->fieldDatasetReferencedDisplayColumn = $config['referenced_display_column'] ?? '';
    }

    protected function showFieldDefaultCurrentDateOption(): bool
    {
        if ($this->fieldType !== 'dataset_related') {
            return in_array($this->fieldType, ['date', 'datetime'], true);
        }

        $schema = app(LogEntryDatabaseSchemaService::class);

        if ($this->fieldDatasetDisplayMode === 'direct' && $this->fieldDatasetSourceColumn) {
            return $schema->isDateColumn($this->fieldDatasetSourceTable, $this->fieldDatasetSourceColumn);
        }

        if ($this->fieldDatasetDisplayMode === 'foreign_key' && $this->fieldDatasetReferencedDisplayColumn) {
            return $schema->isDateColumn($this->fieldDatasetReferencedTable, $this->fieldDatasetReferencedDisplayColumn);
        }

        return false;
    }

    protected function showFieldDefaultAuthenticatedUserOption(): bool
    {
        if ($this->fieldType === 'users') {
            return true;
        }

        if ($this->fieldType !== 'dataset_related') {
            return false;
        }

        $schema = app(LogEntryDatabaseSchemaService::class);

        if ($schema->isUsersTable($this->fieldDatasetSourceTable)) {
            return true;
        }

        return $schema->isUsersTable($this->fieldDatasetReferencedTable);
    }

    protected function resolveMandatoryFieldModelTiedTo(): ?string
    {
        if (LogEntryWorksheetMandatoryField::isPresetLookupType($this->fieldType)) {
            return $this->fieldType;
        }

        return null;
    }

    protected function resolveFieldTypeForForm(LogEntryWorksheetMandatoryField $field): string
    {
        if (LogEntryWorksheetMandatoryField::isPresetLookupType($field->field_type)) {
            return $field->field_type;
        }

        if (
            $field->field_type === 'dataset_related'
            && $field->model_tied_to
            && LogEntryWorksheetMandatoryField::isPresetLookupType($field->model_tied_to)
        ) {
            return $field->model_tied_to;
        }

        return $field->field_type;
    }

    /**
     * @return array<string, mixed>
     */
    protected function columnRules(): array
    {
        return [
            'columnLabel' => 'required|string|max:255',
            'columnKey' => 'required|string|max:255|regex:/^[a-z][a-z0-9_]*$/',
            'columnType' => 'required|in:input,derived,dataset',
            'columnInputDataType' => 'required_if:columnType,input|in:string,number,date,boolean,textarea',
            'columnExpression' => 'nullable|required_if:columnType,derived|string',
            'columnDatasetSourceTable' => 'nullable|required_if:columnType,dataset|string',
            'columnDatasetDisplayMode' => 'nullable|in:direct,foreign_key',
            'columnOrder' => 'required|integer|min:1',
            'columnIsRequired' => 'boolean',
            'columnHelpText' => 'nullable|string',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mandatoryFieldRules(): array
    {
        return [
            'fieldLabel' => 'required|string|max:255',
            'fieldType' => 'required|in:'.implode(',', array_keys(LogEntryWorksheetMandatoryField::getFieldTypes())),
            'fieldValueName' => 'required|string|max:255',
            'fieldHelpText' => 'nullable|string',
            'fieldIsRequired' => 'boolean',
            'fieldOrder' => 'required|integer|min:1',
            'fieldChoiceOptions' => 'nullable|array',
            'fieldChoiceOptions.*' => 'nullable|string|max:255',
            'fieldNewChoice' => 'nullable|string|max:255',
            'fieldDatasetSourceTable' => 'nullable|required_if:fieldType,dataset_related|string',
        ];
    }

    protected function validateChoiceOptionsForCheckboxRadio(): void
    {
        if (! in_array($this->fieldType, ['checkbox', 'radio'], true)) {
            return;
        }

        if ($this->normalizedFieldChoiceOptions() === []) {
            throw ValidationException::withMessages([
                'fieldChoiceOptions' => ['Add at least one choice.'],
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function normalizedFieldChoiceOptions(): array
    {
        return array_values(array_filter(
            array_map('trim', $this->fieldChoiceOptions),
            fn (string $option): bool => $option !== '',
        ));
    }

    protected function validateDerivedExpression(): void
    {
        if ($this->columnType !== 'derived') {
            return;
        }

        $priorKeys = collect($this->columns)
            ->when($this->editingColumn, fn ($c) => $c->filter(fn ($col) => $col['id'] !== $this->editingColumn->id))
            ->filter(fn ($c) => ($c['order'] ?? 0) < $this->columnOrder)
            ->pluck('key')
            ->mapWithKeys(fn ($k) => [$k => 0])
            ->all();

        $result = app(FormulaEvaluator::class)->validateExpression($this->columnExpression, $priorKeys);
        if (! $result['valid']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'columnExpression' => [$result['message']],
            ]);
        }
    }

    protected function resetColumnForm(): void
    {
        $this->editingColumn = null;
        $this->columnLabel = '';
        $this->columnKey = '';
        $this->columnType = 'input';
        $this->columnInputDataType = 'string';
        $this->columnExpression = '';
        $this->columnOrder = 1;
        $this->columnIsRequired = false;
        $this->columnHelpText = '';
        $this->resetColumnDatasetFields();
        $this->expressionValidationMessage = null;
    }

    protected function resetColumnDatasetFields(): void
    {
        $this->columnDatasetSourceTable = '';
        $this->columnDatasetDisplayMode = 'direct';
        $this->columnDatasetSourceColumn = '';
        $this->columnDatasetFkColumn = '';
        $this->columnDatasetReferencedTable = '';
        $this->columnDatasetReferencedKeyColumn = 'id';
        $this->columnDatasetReferencedDisplayColumn = '';
        $this->resetColumnDatasetTagSelectUi();
    }

    protected function resetColumnDatasetTagSelectUi(): void
    {
        $this->showColumnDatasetSourceTableDropdown = false;
        $this->columnDatasetSourceTableSearch = '';
        $this->selectedColumnDatasetSourceTableLabel = '';
        $this->showColumnDatasetSourceColumnDropdown = false;
        $this->columnDatasetSourceColumnSearch = '';
        $this->selectedColumnDatasetSourceColumnLabel = '';
        $this->showColumnDatasetFkDropdown = false;
        $this->columnDatasetFkSearch = '';
        $this->selectedColumnDatasetFkLabel = '';
        $this->showColumnDatasetReferencedDisplayDropdown = false;
        $this->columnDatasetReferencedDisplayColumnSearch = '';
        $this->selectedColumnDatasetReferencedDisplayLabel = '';
    }

    protected function applyColumnDatasetFkSelection(): void
    {
        if ($this->columnDatasetFkColumn === '') {
            $this->columnDatasetReferencedTable = '';
            $this->columnDatasetReferencedDisplayColumn = '';
            $this->selectedColumnDatasetReferencedDisplayLabel = '';

            return;
        }

        $fks = app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($this->columnDatasetSourceTable);
        $match = collect($fks)->firstWhere('column', $this->columnDatasetFkColumn);
        if ($match) {
            $this->columnDatasetReferencedTable = $match['referenced_table'];
            $this->columnDatasetReferencedKeyColumn = $match['referenced_column'];
            $this->columnDatasetReferencedDisplayColumn = '';
            $this->selectedColumnDatasetReferencedDisplayLabel = '';
            $this->columnDatasetReferencedDisplayColumnSearch = '';
        }
    }

    protected function syncColumnDatasetSourceTableLabel(): void
    {
        if ($this->columnDatasetSourceTable === '') {
            $this->selectedColumnDatasetSourceTableLabel = '';

            return;
        }

        foreach (app(LogEntryDatabaseSchemaService::class)->tableOptions() as $opt) {
            if (($opt['value'] ?? '') === $this->columnDatasetSourceTable) {
                $this->selectedColumnDatasetSourceTableLabel = ($opt['label'] ?? $opt['value']).' ('.$opt['value'].')';

                return;
            }
        }

        $this->selectedColumnDatasetSourceTableLabel = $this->columnDatasetSourceTable;
    }

    protected function syncColumnDatasetSourceColumnLabel(): void
    {
        $this->selectedColumnDatasetSourceColumnLabel = $this->resolveSchemaOptionLabel(
            $this->columnDatasetSourceColumn,
            $this->columnDatasetSourceTable !== ''
                ? app(LogEntryDatabaseSchemaService::class)->columnOptions($this->columnDatasetSourceTable)
                : []
        );
    }

    protected function syncColumnDatasetFkLabel(): void
    {
        if ($this->columnDatasetFkColumn === '') {
            $this->selectedColumnDatasetFkLabel = '';

            return;
        }

        $fks = $this->columnDatasetSourceTable !== ''
            ? app(LogEntryDatabaseSchemaService::class)->foreignKeyOptions($this->columnDatasetSourceTable)
            : [];
        $match = collect($fks)->firstWhere('column', $this->columnDatasetFkColumn);
        $this->selectedColumnDatasetFkLabel = $match['label'] ?? $this->columnDatasetFkColumn;
    }

    protected function syncColumnDatasetReferencedDisplayLabel(): void
    {
        $this->selectedColumnDatasetReferencedDisplayLabel = $this->resolveSchemaOptionLabel(
            $this->columnDatasetReferencedDisplayColumn,
            $this->columnDatasetReferencedTable !== ''
                ? app(LogEntryDatabaseSchemaService::class)->columnOptions($this->columnDatasetReferencedTable)
                : []
        );
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     */
    protected function resolveSchemaOptionLabel(string $value, array $options): string
    {
        if ($value === '') {
            return '';
        }

        foreach ($options as $opt) {
            if (($opt['value'] ?? '') === $value) {
                return ($opt['label'] ?? $value).' ('.$value.')';
            }
        }

        return $value;
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     * @return array<int, array{value: string, label: string}>
     */
    protected function filteredColumnSourceColumnOptions(array $options): array
    {
        return $this->filterSchemaOptions($options, $this->columnDatasetSourceColumnSearch);
    }

    /**
     * @param  array<int, array{column: string, referenced_table: string, referenced_column: string, label: string}>  $foreignKeys
     * @return array<int, array{value: string, label: string}>
     */
    protected function filteredColumnForeignKeyOptions(array $foreignKeys): array
    {
        $options = array_map(fn (array $fk): array => [
            'value' => $fk['column'],
            'label' => $fk['label'],
        ], $foreignKeys);

        return $this->filterSchemaOptions($options, $this->columnDatasetFkSearch);
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     * @return array<int, array{value: string, label: string}>
     */
    protected function filteredColumnReferencedDisplayOptions(array $options): array
    {
        return $this->filterSchemaOptions($options, $this->columnDatasetReferencedDisplayColumnSearch);
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     * @return array<int, array{value: string, label: string}>
     */
    protected function filterSchemaOptions(array $options, string $search): array
    {
        $search = strtolower(trim($search));

        if ($search === '') {
            return $options;
        }

        return array_values(array_filter($options, function (array $opt) use ($search): bool {
            $label = strtolower((string) ($opt['label'] ?? ''));
            $value = strtolower((string) ($opt['value'] ?? ''));

            return str_contains($label, $search) || str_contains($value, $search);
        }));
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     * @return array<int, array{value: string, label: string}>
     */
    protected function filteredColumnSchemaTableOptions(array $options): array
    {
        $search = strtolower(trim($this->columnDatasetSourceTableSearch));

        if ($search === '') {
            return $options;
        }

        return array_values(array_filter($options, function (array $opt) use ($search): bool {
            $label = strtolower((string) ($opt['label'] ?? ''));
            $value = strtolower((string) ($opt['value'] ?? ''));

            return str_contains($label, $search) || str_contains($value, $search);
        }));
    }

    protected function resetFieldForm(): void
    {
        $this->editingField = null;
        $this->fieldLabel = '';
        $this->fieldType = 'input';
        $this->fieldOrder = 1;
        $this->fieldHelpText = '';
        $this->fieldIsRequired = true;
        $this->fieldValueName = '';
        $this->fieldChoiceOptions = [];
        $this->fieldNewChoice = '';
        $this->fieldDefaultCurrentDate = false;
        $this->fieldDefaultAuthenticatedUser = false;
        $this->resetFieldDatasetFields();
    }

    protected function resetFieldDatasetFields(): void
    {
        $this->fieldDatasetSourceTable = '';
        $this->fieldDatasetDisplayMode = 'direct';
        $this->fieldDatasetSourceColumn = '';
        $this->fieldDatasetFkColumn = '';
        $this->fieldDatasetReferencedTable = '';
        $this->fieldDatasetReferencedKeyColumn = 'id';
        $this->fieldDatasetReferencedDisplayColumn = '';
    }

    public static function formatDatasetSummary(?array $config): string
    {
        if (! $config || empty($config['source_table'])) {
            return '—';
        }

        if (($config['display_mode'] ?? '') === 'foreign_key') {
            return sprintf(
                '%s.%s → %s.%s',
                $config['source_table'],
                $config['foreign_key_column'] ?? '?',
                $config['referenced_table'] ?? '?',
                $config['referenced_display_column'] ?? '?'
            );
        }

        return sprintf(
            '%s.%s',
            $config['source_table'],
            $config['source_display_column'] ?? '?'
        );
    }
}
