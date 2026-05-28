<?php

namespace App\Livewire\LogEntryWorksheets;

use App\Enums\LogEntryWorksheet\LogEntryRowDriver;
use App\Livewire\LogEntryWorksheets\LogEntryWorksheetEditor;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField;
use App\Services\LogEntryWorksheets\LogEntryMandatoryFieldOptionsResolver;
use Illuminate\Support\Collection;
use Livewire\Component;

class LogEntryWorksheetPreview extends Component
{
    public LogEntryWorksheet $worksheet;

    /** @var \Illuminate\Support\Collection<int, \App\Models\LogEntryWorksheets\LogEntryWorksheetColumn> */
    public Collection $columns;

    /** @var \Illuminate\Support\Collection<int, \App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField> */
    public Collection $mandatoryFields;

    public function mount(LogEntryWorksheet $worksheet): void
    {
        $this->worksheet = $worksheet;
        $this->columns = $worksheet->columns()->orderBy('order')->get();
        $this->mandatoryFields = $worksheet->mandatoryFields()->orderBy('order')->get();
    }

    public function mandatoryFieldUsesSelectList(LogEntryWorksheetMandatoryField $field): bool
    {
        return app(LogEntryMandatoryFieldOptionsResolver::class)->mandatoryFieldUsesSelectList($field);
    }

    public function getMandatoryFieldOptions(LogEntryWorksheetMandatoryField $field): Collection
    {
        return app(LogEntryMandatoryFieldOptionsResolver::class)->optionsForField($field);
    }

    public function columnSourceSummary(LogEntryWorksheetColumn $column): string
    {
        if ($column->column_type === 'derived') {
            return 'Derived from expression';
        }

        if ($column->column_type === 'dataset') {
            return LogEntryWorksheetEditor::formatDatasetSummary($column->dataset_config);
        }

        return ucfirst($column->input_data_type ?? 'text');
    }

    public function render()
    {
        return view('livewire.log-entry-worksheets.log-entry-worksheet-preview', [
            'rowDriverLabel' => LogEntryRowDriver::tryFrom($this->worksheet->row_driver)?->label()
                ?? str_replace('_', ' ', $this->worksheet->row_driver),
            'mandatoryFieldTypes' => LogEntryWorksheetMandatoryField::getFieldTypes(),
        ]);
    }
}
