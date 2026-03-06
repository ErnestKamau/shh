<?php

namespace App\Livewire\Worksheets;

use Livewire\Component;
use App\CapturedResult;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\CapturedProcedureValue;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\CapturedProcedureConfigValue;
use App\Models\Procedures\ProcedureTestKitColumn;
use App\Models\Procedures\ProcedureTestKitRow;
use App\Models\Procedures\ProcedureTestKitValue;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProcedureWorksheetManager extends Component
{
    public $batchId;
    public $activeTab = null; // This will hold the active Analyte ID
    public $selectedWorksheetId = null;
    public $selectedSamples = []; // Array of sample_detail_ids
    public $inputValues = []; // [captured_result_id => [step_id => value]]
    public $configFieldValues = []; // [captured_result_id => [config_field_id => value]]
    public $testKitRows = []; // [row_id => ['row_index' => n]]
    public $testKitData = []; // [row_id => [column_id => value]]

    /**
     * External samples support (from other batches).
     *
     * $externalCapturedResultIds tracks which extra captured_result rows
     * from other batches should be included in this worksheet.
     */
    public array $externalCapturedResultIds = [];
    public array $externalSearchResults = [];
    /** @var array<int, array{id: int, batch_code: string, sample_code: string}> Items chosen in the multiselect (before "Add Selected"). */
    public array $externalSelectionItems = [];
    public string $externalSearch = '';
    public bool $showExternalPanel = false;
    public bool $showExternalDropdown = false;

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->initActiveTab();
    }

    public function initActiveTab()
    {
        $params = $this->paramsWithWorksheets;
        if ($params->isNotEmpty() && !$this->activeTab) {
            $this->activeTab = $params->first()->id;
        }
    }

    public function getParamsWithWorksheetsProperty()
    {
        // Get analytes that have captured results with check for procedure_worksheet_id
        return CapturedResult::where('sample_header_id', $this->batchId)
            ->whereNotNull('procedure_worksheet_id')
            ->with('my_analyte')
            ->get()
            ->pluck('my_analyte')
            ->unique('id')
            ->values();
    }

    public function getWorksheetsForParamProperty()
    {
        if (!$this->activeTab) return collect();

        // Get unique procedure worksheets for the selected analyte in this batch
        $worksheetIds = CapturedResult::where('sample_header_id', $this->batchId)
            ->where('analyte_id', $this->activeTab)
            ->whereNotNull('procedure_worksheet_id')
            ->pluck('procedure_worksheet_id')
            ->unique();

        return ProcedureWorksheet::whereIn('id', $worksheetIds)->get();
    }

    public function updatedActiveTab()
    {
        $this->selectedWorksheetId = null;
        $this->selectedSamples = [];
        $this->externalCapturedResultIds = [];
        $this->externalSearchResults = [];
        $this->externalSelectionItems = [];
        $this->externalSearch = '';
        $this->showExternalPanel = false;
        $this->showExternalDropdown = false;
        // Auto-select first worksheet ?
        $worksheets = $this->worksheetsForParam;
        if ($worksheets->count() > 0) {
            $this->selectWorksheet($worksheets->first()->id);
        }
    }

    public function selectWorksheet($worksheetId)
    {
        $this->selectedWorksheetId = $worksheetId;
        $this->externalCapturedResultIds = [];
        $this->externalSearchResults = [];
        $this->externalSelectionItems = [];
        $this->externalSearch = '';
        $this->showExternalPanel = false;
        $this->showExternalDropdown = false;
        $this->loadSamples();
    }

    public function loadSamples()
    {
        if (!$this->activeTab || !$this->selectedWorksheetId) {
            $this->selectedSamples = [];
            $this->testKitRows = [];
            $this->testKitData = [];
            return;
        }

        // Base query for captured results for this analyte and worksheet
        $query = CapturedResult::query()
            ->where('analyte_id', $this->activeTab)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId);

        // Always include current batch, optionally include explicitly selected external captured_results
        if (! empty($this->externalCapturedResultIds)) {
            $query->where(function ($inner) {
                $inner->where('sample_header_id', $this->batchId)
                    ->orWhereIn('id', $this->externalCapturedResultIds);
            });
        } else {
            $query->where('sample_header_id', $this->batchId);
        }

        $capturedResults = $query->with('sample')->get();

        // Get samples for this analyte and worksheet (current + external)
        $samples = $capturedResults
            ->pluck('sample.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
            
        // Default select all
        $this->selectedSamples = $samples;

        // Load existing values for all included captured results
        $capturedResultIds = $capturedResults->pluck('id');
        $steps = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();
        $configFields = ProcedureConfigField::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();

        $values = CapturedProcedureValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($values as $val) {
            $this->inputValues[$val->captured_result_id][$val->procedure_worksheet_step_id] = $val->value;
        }

        // Ensure every displayed captured_result has inputValues keys for each step (so Livewire binds and sends on save)
        foreach ($capturedResults as $cr) {
            foreach ($steps as $step) {
                if (! isset($this->inputValues[$cr->id][$step->id])) {
                    $this->inputValues[$cr->id][$step->id] = '';
                }
            }
        }

        $configValues = CapturedProcedureConfigValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($configValues as $val) {
            $this->configFieldValues[$val->captured_result_id][$val->procedure_config_field_id] = $val->value;
        }

        // Ensure every displayed captured_result has configFieldValues keys for each config field
        foreach ($capturedResults as $cr) {
            foreach ($configFields as $field) {
                if (! isset($this->configFieldValues[$cr->id][$field->id])) {
                    $this->configFieldValues[$cr->id][$field->id] = '';
                }
            }
        }

        // Load test kit rows and values for this worksheet (per procedure scope)
        $rows = ProcedureTestKitRow::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('row_index')
            ->get();

        $this->testKitRows = $rows->mapWithKeys(function ($row) {
            return [$row->id => ['row_index' => $row->row_index]];
        })->toArray();

        $values = ProcedureTestKitValue::whereIn('procedure_test_kit_row_id', $rows->pluck('id'))->get();

        $this->testKitData = [];
        foreach ($values as $val) {
            $this->testKitData[$val->procedure_test_kit_row_id][$val->procedure_test_kit_column_id] = $val->value;
        }
    }

    public function getAnalysisSamplesProperty()
    {
        if (!$this->activeTab || !$this->selectedWorksheetId) return collect();

        $query = CapturedResult::query()
            ->where('analyte_id', $this->activeTab)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId);

        if (! empty($this->externalCapturedResultIds)) {
            $query->where(function ($inner) {
                $inner->where('sample_header_id', $this->batchId)
                    ->orWhereIn('id', $this->externalCapturedResultIds);
            });
        } else {
            $query->where('sample_header_id', $this->batchId);
        }

        return $query->with(['sample', 'procedureWorksheet'])->get();
    }
    
    public function getStepsProperty()
    {
        if (!$this->selectedWorksheetId) return collect();

        return ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    public function getConfigFieldsProperty()
    {
        if (!$this->selectedWorksheetId) {
            return collect();
        }

        return ProcedureConfigField::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();
    }

    public function getTestKitColumnsProperty()
    {
        if (!$this->selectedWorksheetId) {
            return collect();
        }

        return ProcedureTestKitColumn::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Toggle visibility of the external samples selection panel.
     */
    public function toggleExternalPanel(): void
    {
        $this->showExternalPanel = ! $this->showExternalPanel;

        if ($this->showExternalPanel) {
            $this->showExternalDropdown = true;
            $this->performExternalSearch();
        } else {
            $this->externalSearch = '';
            $this->externalSearchResults = [];
            $this->externalSelectionItems = [];
            $this->showExternalDropdown = false;
        }
    }

    /**
     * React to external search term changes.
     */
    public function updatedExternalSearch(): void
    {
        $this->performExternalSearch();
    }

    /**
     * Search for eligible external samples (other batches)
     * that share the same analyte and procedure worksheet.
     */
    protected function performExternalSearch(): void
    {
        $this->externalSearchResults = [];

        if (! $this->activeTab || ! $this->selectedWorksheetId) {
            return;
        }

        $term = trim($this->externalSearch);
        $alreadySelectedIds = array_merge(
            $this->externalCapturedResultIds,
            array_column($this->externalSelectionItems, 'id')
        );
        $alreadySelectedIds = array_values(array_unique(array_map('intval', $alreadySelectedIds)));

        $query = DB::table('captured_results as cr')
            ->join('sample_details as sd', 'sd.id', '=', 'cr.sample_detail_id')
            ->join('sample_headers as sh', 'sh.id', '=', 'cr.sample_header_id')
            ->where('cr.analyte_id', $this->activeTab)
            ->where('cr.procedure_worksheet_id', $this->selectedWorksheetId)
            ->where('cr.sample_header_id', '!=', $this->batchId)
            ->when(! empty($alreadySelectedIds), function ($q) use ($alreadySelectedIds) {
                $q->whereNotIn('cr.id', $alreadySelectedIds);
            })
            ->when($term !== '', function ($q) use ($term) {
                $like = '%' . $term . '%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('sd.sample_code', 'like', $like)
                        ->orWhere('sh.batch_code', 'like', $like);
                });
            })
            ->select([
                'cr.id as captured_result_id',
                'sd.sample_code',
                'sh.batch_code',
            ])
            ->orderBy('sh.batch_code')
            ->limit(20)
            ->get();

        $this->externalSearchResults = $query->map(function ($row) {
            return [
                'captured_result_id' => $row->captured_result_id,
                'sample_code' => $row->sample_code,
                'batch_code' => $row->batch_code,
            ];
        })->toArray();
    }

    /**
     * Add one external sample to the multiselect selection (before "Add Selected").
     * Looks up batch_code and sample_code from current search results.
     */
    public function addExternalSampleToSelection(int $id): void
    {
        $id = (int) $id;
        foreach ($this->externalSelectionItems as $item) {
            if ((int) $item['id'] === $id) {
                return;
            }
        }
        foreach ($this->externalSearchResults as $row) {
            if ((int) $row['captured_result_id'] === $id) {
                $this->externalSelectionItems[] = [
                    'id' => $id,
                    'batch_code' => $row['batch_code'] ?? '',
                    'sample_code' => $row['sample_code'] ?? '',
                ];
                return;
            }
        }
    }

    /**
     * Remove one external sample from the multiselect selection.
     */
    public function removeExternalSampleFromSelection(int $id): void
    {
        $id = (int) $id;
        $this->externalSelectionItems = array_values(array_filter(
            $this->externalSelectionItems,
            fn ($item) => (int) $item['id'] !== $id
        ));
    }

    /**
     * Add the currently selected external samples (from multiselect) into the worksheet.
     */
    public function addSelectedExternalSamples(): void
    {
        if (empty($this->externalSelectionItems)) {
            return;
        }

        $ids = array_map(fn ($item) => (int) $item['id'], $this->externalSelectionItems);
        $this->externalCapturedResultIds = array_values(array_unique(array_merge(
            $this->externalCapturedResultIds,
            $ids
        )));

        $this->externalSelectionItems = [];
        $this->performExternalSearch();

        // Reload samples and existing values with the newly added external results
        $this->loadSamples();
    }

    public function render()
    {
        return view('livewire.worksheets.procedure-worksheet-manager', [
            'configFields' => $this->getConfigFieldsProperty(),
            'testKitColumns' => $this->getTestKitColumnsProperty(),
        ]);
    }
    
    public function save()
    {
        $this->validate([
            'inputValues.*.*' => 'nullable|string',
            'configFieldValues.*.*' => 'nullable|string',
            'testKitData.*.*' => 'nullable|string',
        ]);

        foreach ($this->inputValues as $capturedResultId => $steps) {
            if (! is_array($steps)) {
                continue;
            }
            $capturedResultId = (int) $capturedResultId;
            foreach ($steps as $stepId => $value) {
                $stepId = (int) $stepId;
                CapturedProcedureValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'value' => $value ?? '',
                    ]
                );
            }
        }

        foreach ($this->configFieldValues as $capturedResultId => $fields) {
            if (! is_array($fields)) {
                continue;
            }
            $capturedResultId = (int) $capturedResultId;
            foreach ($fields as $fieldId => $value) {
                $fieldId = (int) $fieldId;
                CapturedProcedureConfigValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'procedure_config_field_id' => $fieldId,
                    ],
                    [
                        'value' => $value ?? '',
                    ]
                );
            }
        }

        // Save test kit values (per procedure, shared across batches)
        foreach ($this->testKitRows as $rowId => $rowMeta) {
            $row = ProcedureTestKitRow::updateOrCreate(
                [
                    'id' => $rowId,
                ],
                [
                    'procedure_worksheet_id' => $this->selectedWorksheetId,
                    'row_index' => $rowMeta['row_index'] ?? 1,
                ]
            );

            if (!isset($this->testKitData[$row->id])) {
                continue;
            }

            foreach ($this->testKitData[$row->id] as $columnId => $value) {
                ProcedureTestKitValue::updateOrCreate(
                    [
                        'procedure_test_kit_row_id' => $row->id,
                        'procedure_test_kit_column_id' => $columnId,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }

        $this->generateWorksheetPdf();

        session()->flash('message', 'Worksheet values saved successfully.');
    }

    protected function generateWorksheetPdf(): void
    {
        if (!$this->batchId || !$this->selectedWorksheetId) {
            return;
        }
        $batch = SampleHeader::find($this->batchId);
        $worksheet = ProcedureWorksheet::find($this->selectedWorksheetId);
        if ($batch && $worksheet) {
            app(\App\Services\ProcedureWorksheetPdfService::class)->generateAndAttach($batch, $worksheet);
        }
    }
}
