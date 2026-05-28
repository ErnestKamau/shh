<?php

namespace App\Livewire\Equipment;

use App\Enums\LogEntryWorksheet\LogEntryColumnType;
use App\Livewire\Equipment\Concerns\InteractsWithEquipmentLogbookColumns;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\Logbook\EquipmentLogbookColumn;
use App\Models\Equipments\Logbook\EquipmentLogbookEntry;
use App\Models\Equipments\Logbook\EquipmentLogbookEntryValue;
use App\Services\Equipment\EquipmentLogbookEvaluator;
use App\Services\LogEntryWorksheets\LogEntryDatasetResolverService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class EquipmentLogbookPanel extends Component
{
    use InteractsWithEquipmentLogbookColumns;
    use WithPagination;

    public string $equipmentId;

    /** @var array<string, string> column_key => value */
    public array $captureValues = [];

    public string $captureLoggedAt = '';

    public string $captureNotes = '';

    public bool $showCaptureForm = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(string $equipmentId): void
    {
        $this->equipmentId = $equipmentId;
        $this->captureLoggedAt = now()->format('Y-m-d\TH:i');
        $this->loadLogbookColumns();
        $this->resetLogCaptureForm();
    }

    public function getEquipmentProperty(): Equipment
    {
        return Equipment::findOrFail($this->equipmentId);
    }

    public function getConfiguredColumnsProperty(): Collection
    {
        return EquipmentLogbookColumn::query()
            ->where('equipment_id', $this->equipmentId)
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        $entries = EquipmentLogbookEntry::query()
            ->where('equipment_id', $this->equipmentId)
            ->with(['values.column', 'loggedByUser'])
            ->orderByDesc('logged_at')
            ->paginate(15);

        return view('livewire.equipment.equipment-logbook-panel', [
            'equipment' => $this->equipment,
            'entries' => $entries,
            'configuredColumns' => $this->configuredColumns,
            'columnTypeOptions' => LogEntryColumnType::options(),
            'inputDataTypeOptions' => [
                'string' => 'Text',
                'number' => 'Number',
                'date' => 'Date',
                'boolean' => 'Checkbox (Yes/No)',
                'textarea' => 'Long text',
            ],
            'schemaTableOptions' => $this->logbookSchemaTableOptions(),
            'schemaColumnOptions' => $this->logbookSchemaColumnOptions(),
            'datasetOptionsByColumn' => $this->datasetOptionsByColumn(),
        ]);
    }

    public function toggleCaptureForm(): void
    {
        $this->showCaptureForm = ! $this->showCaptureForm;
        if ($this->showCaptureForm) {
            $this->resetLogCaptureForm();
        }
    }

    public function resetLogCaptureForm(): void
    {
        $this->captureValues = [];
        foreach ($this->configuredColumns as $column) {
            if ($column->column_type === 'derived') {
                continue;
            }
            $this->captureValues[$column->key] = $column->input_data_type === 'boolean' ? false : '';
        }
        $this->captureLoggedAt = now()->format('Y-m-d\TH:i');
        $this->captureNotes = '';
    }

    public function saveLogbookEntry(): void
    {
        $columns = $this->configuredColumns;
        if ($columns->isEmpty()) {
            throw ValidationException::withMessages([
                'capture' => ['Configure at least one logbook column before capturing logs.'],
            ]);
        }

        $rules = [
            'captureLoggedAt' => 'required|date',
            'captureNotes' => 'nullable|string|max:2000',
        ];

        foreach ($columns as $column) {
            if ($column->column_type === 'derived') {
                continue;
            }
            if ($column->is_required) {
                $rules['captureValues.'.$column->key] = 'required';
            }
        }

        $this->validate($rules);

        $valuesByKey = [];
        foreach ($columns as $column) {
            if ($column->column_type === 'derived') {
                continue;
            }
            $raw = $this->captureValues[$column->key] ?? '';
            if ($column->input_data_type === 'boolean') {
                $valuesByKey[$column->key] = filter_var($raw, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
            } else {
                $valuesByKey[$column->key] = $raw !== '' ? (string) $raw : null;
            }
        }

        $evaluated = app(EquipmentLogbookEvaluator::class)->evaluateDerived($columns, $valuesByKey);

        DB::transaction(function () use ($columns, $evaluated) {
            $entry = EquipmentLogbookEntry::create([
                'equipment_id' => $this->equipmentId,
                'logged_at' => $this->captureLoggedAt,
                'logged_by' => Auth::id(),
                'notes' => $this->captureNotes ?: null,
            ]);

            foreach ($columns as $column) {
                $value = $evaluated[$column->key] ?? null;
                EquipmentLogbookEntryValue::create([
                    'entry_id' => $entry->id,
                    'column_id' => $column->id,
                    'value' => $value,
                ]);
            }
        });

        $this->showCaptureForm = false;
        $this->resetLogCaptureForm();
        $this->resetPage();
        session()->flash('logbook_message', 'Log entry saved.');
    }

    public function deleteLogbookEntry(string $entryId): void
    {
        $entry = EquipmentLogbookEntry::query()
            ->where('equipment_id', $this->equipmentId)
            ->findOrFail($entryId);

        $entry->values()->delete();
        $entry->delete();
        session()->flash('logbook_message', 'Log entry deleted.');
    }

    public function formatCellDisplay(EquipmentLogbookEntryValue $cellValue): string
    {
        $column = $cellValue->column;
        if (! $column) {
            return $cellValue->value ?? '—';
        }

        if ($column->column_type === 'dataset' && $column->dataset_config) {
            $options = app(LogEntryDatasetResolverService::class)->dropdownOptions($column->dataset_config);
            $match = $options->firstWhere('id', (string) $cellValue->value);

            return $match?->label ?? ($cellValue->value ?? '—');
        }

        if ($column->input_data_type === 'boolean') {
            return in_array($cellValue->value, ['1', 'true', 'yes', 'on'], true) ? 'Yes' : 'No';
        }

        return $cellValue->value ?? '—';
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, object{id: string, label: string}>>
     */
    protected function datasetOptionsByColumn(): array
    {
        $out = [];
        foreach ($this->configuredColumns as $column) {
            if ($column->column_type === 'dataset' && $column->dataset_config) {
                $out[$column->key] = app(LogEntryDatasetResolverService::class)
                    ->dropdownOptions($column->dataset_config);
            }
        }

        return $out;
    }
}
