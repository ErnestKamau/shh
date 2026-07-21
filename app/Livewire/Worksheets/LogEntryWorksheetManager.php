<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetCellValue;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetInstance;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetMandatoryData;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use App\SampleHeader;
use App\Services\LogEntryWorksheets\LogEntryColumnEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatasetResolverService;
use App\Services\LogEntryWorksheets\LogEntryMandatoryFieldOptionsResolver;
use App\Services\LogEntryWorksheets\LogEntryRowGeneratorService;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use App\Services\Worksheets\WorksheetMetaResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LogEntryWorksheetManager extends Component
{
    public SampleHeader $batch;

    public ?string $selectedWorksheetId = null;

    /** @var array<string, string> mandatory_field_id => value */
    public array $sharedMandatoryData = [];

    /** @var array<string, array<string, string>> row_id => [ column_key => value ] */
    public array $tableData = [];

    public bool $showResetConfirm = false;

    public function mount(SampleHeader $batch, ?string $worksheetId = null): void
    {
        $this->batch = $batch;
        $this->selectedWorksheetId = $worksheetId;
        $this->bootstrapSelection();
    }

    public function render()
    {
        $capturedResults = $this->scopedCapturedResultModels();
        $metaResolver = app(WorksheetMetaResolver::class);

        return view('livewire.worksheets.log-entry-worksheet-manager', [
            'worksheets' => $this->availableWorksheets,
            'selectedWorksheet' => $this->selectedWorksheet,
            'instance' => $this->currentInstance,
            'columns' => $this->worksheetColumns,
            'mandatoryFields' => $this->mandatoryFields,
            'orderedRows' => $this->orderedRows,
            'worksheetMetaSummary' => $metaResolver->summaryForMany($capturedResults),
            'worksheetMetaRows' => $metaResolver->forMany($capturedResults),
        ]);
    }

    protected function scopedCapturedResultModels(): Collection
    {
        $query = CapturedResult::where('sample_header_id', $this->batch->id)
            ->when($this->selectedWorksheetId, fn ($q) => $q->where('log_entry_worksheet_id', $this->selectedWorksheetId));
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());

        return $query->with(WorksheetMetaResolver::EAGER)->get();
    }

    public function getAvailableWorksheetsProperty(): Collection
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $this->batch->id)
            ->where('has_log_entry_worksheet', true)
            ->whereNotNull('log_entry_worksheet_id');
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());
        $ids = $query->distinct()->pluck('log_entry_worksheet_id');

        if ($this->selectedWorksheetId !== null && $this->selectedWorksheetId !== '') {
            $ids = $ids->push($this->selectedWorksheetId)->unique()->values();
        }

        return LogEntryWorksheet::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedWorksheetProperty(): ?LogEntryWorksheet
    {
        if (! $this->selectedWorksheetId) {
            return null;
        }

        return LogEntryWorksheet::find($this->selectedWorksheetId);
    }

    public function getCurrentInstanceProperty(): ?SampleLogEntryWorksheetInstance
    {
        $worksheet = $this->selectedWorksheet;
        if (! $worksheet) {
            return null;
        }

        return app(LogEntryRowGeneratorService::class)->firstOrCreateInstance($this->batch, $worksheet);
    }

    public function getWorksheetColumnsProperty(): Collection
    {
        if (! $this->selectedWorksheetId) {
            return collect();
        }

        return LogEntryWorksheetColumn::where('log_entry_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();
    }

    public function getMandatoryFieldsProperty(): Collection
    {
        if (! $this->selectedWorksheetId) {
            return collect();
        }

        return LogEntryWorksheetMandatoryField::where('log_entry_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();
    }

    /**
     * @return array<int, array{row: SampleLogEntryWorksheetRow, cells: array<string, string|null>}>
     */
    public function getOrderedRowsProperty(): array
    {
        $instance = $this->currentInstance;
        if (! $instance) {
            return [];
        }

        $rows = SampleLogEntryWorksheetRow::where('instance_id', $instance->id)
            ->orderBy('row_index')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'row' => $row,
                'cells' => $this->tableData[$row->id] ?? [],
            ];
        }

        return $out;
    }

    public function selectWorksheet(string $worksheetId): void
    {
        $this->selectedWorksheetId = $worksheetId;
        $this->loadCaptureState();
    }

    public function regenerateAutoRows(): void
    {
        $instance = $this->currentInstance;
        $worksheet = $this->selectedWorksheet;
        if (! $instance || ! $worksheet) {
            return;
        }

        app(LogEntryRowGeneratorService::class)->syncAutoRows($instance, $this->batch);
        $this->loadCaptureState();
        session()->flash('log_entry_message', 'Auto rows synced.');
    }

    public function addManualRow(): void
    {
        $worksheet = $this->selectedWorksheet;
        $instance = $this->currentInstance;
        if (! $worksheet || ! $instance || ! $worksheet->allow_manual_rows) {
            return;
        }

        app(LogEntryRowGeneratorService::class)->addManualRow($instance);
        $this->loadCaptureState();
    }

    public function removeManualRow(string $rowId): void
    {
        $row = SampleLogEntryWorksheetRow::find($rowId);
        if (! $row || $row->row_source !== 'manual') {
            return;
        }

        SampleLogEntryWorksheetCellValue::where('row_id', $rowId)->delete();
        $row->delete();
        unset($this->tableData[$rowId]);
        $this->loadCaptureState();
    }

    public function updatedTableData($value, string $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        [$rowId, $columnKey] = $parts;
        $this->persistCell($rowId, $columnKey, (string) ($value ?? ''));
        $this->reevaluateDerivedForRow($rowId);
    }

    public function autoSaveMandatoryField(string $fieldId): void
    {
        $this->saveMandatoryFields();
    }

    public function toggleMandatoryCheckboxOption(string $fieldId, string $option): void
    {
        $current = json_decode((string) ($this->sharedMandatoryData[$fieldId] ?? '[]'), true);
        if (! is_array($current)) {
            $current = [];
        }

        if (in_array($option, $current, true)) {
            $current = array_values(array_filter($current, fn ($o) => $o !== $option));
        } else {
            $current[] = $option;
        }

        $this->sharedMandatoryData[$fieldId] = json_encode($current);
        $this->saveMandatoryFields();
    }

    public function isMandatoryCheckboxSelected(string $fieldId, string $option): bool
    {
        $current = json_decode((string) ($this->sharedMandatoryData[$fieldId] ?? '[]'), true);

        return is_array($current) && in_array($option, $current, true);
    }

    public function saveWorksheet(): void
    {
        $access = app(LabSectionResultAccess::class);
        if (! $access->hasLabSectionAssignment(Auth::user())) {
            session()->flash('log_entry_error', $access->denyEditMessage(Auth::user()));

            return;
        }

        $this->saveMandatoryFields();
        $this->persistAllCells();
        session()->flash('log_entry_message', 'Worksheet saved.');
    }

    public function postWorksheet(): void
    {
        $access = app(LabSectionResultAccess::class);
        if (! $access->hasLabSectionAssignment(Auth::user())) {
            session()->flash('log_entry_error', $access->denyEditMessage(Auth::user()));

            return;
        }

        $this->validateBeforePost();
        $this->saveWorksheet();

        $instance = $this->currentInstance;
        if ($instance) {
            $instance->update(['status' => 'posted']);
        }

        session()->flash('log_entry_message', 'Worksheet posted.');
    }

    public function getMandatoryDatasetOptions(LogEntryWorksheetMandatoryField $field): Collection
    {
        if ($field->dataset_config) {
            return app(LogEntryDatasetResolverService::class)->dropdownOptions($field->dataset_config);
        }

        $modelTiedTo = LogEntryWorksheetMandatoryField::isPresetLookupType($field->field_type)
            ? $field->field_type
            : ($field->model_tied_to ?? '');

        if (in_array($modelTiedTo, ['sample_details', 'captured_results'], true)) {
            return $this->getBatchScopedDatasetOptions($modelTiedTo);
        }

        return app(LogEntryMandatoryFieldOptionsResolver::class)->optionsForModel($modelTiedTo);
    }

    public function mandatoryFieldUsesSelectList(LogEntryWorksheetMandatoryField $field): bool
    {
        return app(LogEntryMandatoryFieldOptionsResolver::class)->mandatoryFieldUsesSelectList($field);
    }

    protected function getBatchScopedDatasetOptions(string $modelTiedTo): Collection
    {
        return match ($modelTiedTo) {
            'sample_details' => \App\SampleDetails::where('sample_header_id', $this->batch->id)
                ->orderBy('sample_code')
                ->get()
                ->map(fn ($s) => (object) ['id' => (string) $s->id, 'label' => $s->sample_code]),
            'captured_results' => $this->scopedCapturedResultsForLogEntryDataset(),
            default => collect(),
        };
    }

    protected function scopedCapturedResultsForLogEntryDataset(): Collection
    {
        $query = CapturedResult::where('sample_header_id', $this->batch->id)
            ->when($this->selectedWorksheetId, fn ($q) => $q->where('log_entry_worksheet_id', $this->selectedWorksheetId));
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());

        return $query
            ->with('analyte')
            ->get()
            ->map(fn ($cr) => (object) [
                'id' => (string) $cr->analyte_id,
                'label' => $cr->analyte?->name ?? $cr->sample_detail_code,
            ]);
    }

    protected function bootstrapSelection(): void
    {
        if ($this->selectedWorksheetId) {
            $this->loadCaptureState();

            return;
        }

        $first = $this->availableWorksheets->first();
        if ($first) {
            $this->selectedWorksheetId = (string) $first->id;
            $this->loadCaptureState();
        }
    }

    protected function loadCaptureState(): void
    {
        $instance = $this->currentInstance;
        $worksheet = $this->selectedWorksheet;
        if (! $instance || ! $worksheet) {
            $this->tableData = [];
            $this->sharedMandatoryData = [];

            return;
        }

        app(LogEntryRowGeneratorService::class)->syncAutoRows($instance, $this->batch);

        $this->loadMandatoryData($instance);
        $this->loadTableData($instance);
        $this->prefillDatasetColumns($instance);
    }

    protected function loadMandatoryData(SampleLogEntryWorksheetInstance $instance): void
    {
        $this->sharedMandatoryData = [];
        foreach ($this->mandatoryFields as $field) {
            $record = SampleLogEntryWorksheetMandatoryData::where('instance_id', $instance->id)
                ->where('mandatory_field_id', $field->id)
                ->first();

            $value = $record?->field_value ?? '';

            if ($value === '' && $field->default_current_date && in_array($field->field_type, ['date', 'datetime'], true)) {
                $value = $field->field_type === 'date'
                    ? now()->format('Y-m-d')
                    : now()->format('Y-m-d\TH:i');
            }

            if ($value === '' && $field->default_authenticated_user && auth()->check()) {
                $value = (string) auth()->id();
            }

            $this->sharedMandatoryData[$field->id] = $value;
        }
    }

    protected function loadTableData(SampleLogEntryWorksheetInstance $instance): void
    {
        $columnsById = $this->worksheetColumns->keyBy('id');
        $this->tableData = [];

        $rows = SampleLogEntryWorksheetRow::where('instance_id', $instance->id)->get();
        foreach ($rows as $row) {
            $cells = [];
            $values = SampleLogEntryWorksheetCellValue::where('row_id', $row->id)->get();
            foreach ($values as $val) {
                $col = $columnsById->get($val->column_id);
                if ($col) {
                    $cells[$col->key] = $val->value;
                }
            }
            $this->tableData[$row->id] = $cells;
        }
    }

    protected function prefillDatasetColumns(SampleLogEntryWorksheetInstance $instance): void
    {
        $columns = $this->worksheetColumns->where('column_type', 'dataset');
        if ($columns->isEmpty()) {
            return;
        }

        $resolver = app(LogEntryDatasetResolverService::class);
        $rows = SampleLogEntryWorksheetRow::where('instance_id', $instance->id)->get();

        foreach ($rows as $row) {
            if (! isset($this->tableData[$row->id])) {
                $this->tableData[$row->id] = [];
            }

            foreach ($columns as $column) {
                $resolved = $resolver->resolveDisplayValue($column->dataset_config, $row);
                if ($resolved !== null && $resolved !== '') {
                    $this->tableData[$row->id][$column->key] = $resolved;
                    $this->persistCell($row->id, $column->key, $resolved);
                }
            }
        }
    }

    protected function persistCell(string $rowId, string $columnKey, string $value): void
    {
        $column = $this->worksheetColumns->firstWhere('key', $columnKey);
        if (! $column || $column->column_type === 'derived') {
            return;
        }

        SampleLogEntryWorksheetCellValue::updateOrCreate(
            [
                'row_id' => $rowId,
                'column_id' => $column->id,
            ],
            ['value' => $value],
        );
    }

    protected function persistAllCells(): void
    {
        foreach ($this->tableData as $rowId => $cells) {
            foreach ($cells as $key => $value) {
                $this->persistCell($rowId, $key, (string) ($value ?? ''));
            }
        }
    }

    protected function reevaluateDerivedForRow(string $rowId): void
    {
        $row = SampleLogEntryWorksheetRow::find($rowId);
        if (! $row) {
            return;
        }

        $derived = app(LogEntryColumnEvaluator::class)->evaluateRow(
            $this->worksheetColumns,
            $row,
            $this->tableData[$rowId] ?? [],
        );

        foreach ($this->worksheetColumns->where('column_type', 'derived') as $column) {
            if (array_key_exists($column->key, $derived)) {
                $this->tableData[$rowId][$column->key] = $derived[$column->key];
                SampleLogEntryWorksheetCellValue::updateOrCreate(
                    ['row_id' => $rowId, 'column_id' => $column->id],
                    ['value' => $derived[$column->key]],
                );
            }
        }
    }

    protected function saveMandatoryFields(): void
    {
        $instance = $this->currentInstance;
        if (! $instance) {
            return;
        }

        foreach ($this->sharedMandatoryData as $fieldId => $value) {
            SampleLogEntryWorksheetMandatoryData::updateOrCreate(
                [
                    'instance_id' => $instance->id,
                    'mandatory_field_id' => $fieldId,
                ],
                ['field_value' => $value],
            );
        }
    }

    protected function validateBeforePost(): void
    {
        $errors = [];

        foreach ($this->mandatoryFields as $field) {
            if (! $field->is_required) {
                continue;
            }

            $value = $this->sharedMandatoryData[$field->id] ?? '';

            if ($field->field_type === 'checkbox') {
                $decoded = json_decode((string) $value, true);
                if (! is_array($decoded) || count($decoded) === 0) {
                    $errors['sharedMandatoryData.'.$field->id] = "{$field->label} requires at least one selection.";
                }

                continue;
            }

            if (empty(trim((string) $value))) {
                $errors['sharedMandatoryData.'.$field->id] = "{$field->label} is required.";
            }
        }

        foreach ($this->orderedRows as $entry) {
            $row = $entry['row'];
            foreach ($this->worksheetColumns as $column) {
                if (! $column->is_required || $column->column_type === 'derived') {
                    continue;
                }
                $val = $this->tableData[$row->id][$column->key] ?? '';
                if (trim((string) $val) === '') {
                    $errors['tableData.'.$row->id.'.'.$column->key] = "{$column->label} is required on row {$row->row_index}.";
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
