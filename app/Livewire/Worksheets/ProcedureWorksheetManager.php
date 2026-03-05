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
        // Auto-select first worksheet ?
        $worksheets = $this->worksheetsForParam;
        if ($worksheets->count() > 0) {
            $this->selectWorksheet($worksheets->first()->id);
        }
    }

    public function selectWorksheet($worksheetId)
    {
        $this->selectedWorksheetId = $worksheetId;
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

        // Get samples for this analyte and worksheet
        $samples = CapturedResult::where('sample_header_id', $this->batchId)
            ->where('analyte_id', $this->activeTab)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->with('sample')
            ->get()
            ->pluck('sample.id')
            ->toArray();
            
        // Default select all
        $this->selectedSamples = $samples;

        // Load existing values
        $capturedResultIds = CapturedResult::where('sample_header_id', $this->batchId)
            ->where('analyte_id', $this->activeTab)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->pluck('id');

        $values = CapturedProcedureValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($values as $val) {
            $this->inputValues[$val->captured_result_id][$val->procedure_worksheet_step_id] = $val->value;
        }

        $configValues = CapturedProcedureConfigValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($configValues as $val) {
            $this->configFieldValues[$val->captured_result_id][$val->procedure_config_field_id] = $val->value;
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

         return CapturedResult::where('sample_header_id', $this->batchId)
            ->where('analyte_id', $this->activeTab)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->with(['sample', 'procedureWorksheet'])
            ->get();
    }
    
    public function getStepsProperty()
    {
        if (!$this->selectedWorksheetId) return collect();
        
        return ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
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
            foreach ($steps as $stepId => $value) {
                CapturedProcedureValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }

        foreach ($this->configFieldValues as $capturedResultId => $fields) {
            foreach ($fields as $fieldId => $value) {
                CapturedProcedureConfigValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'procedure_config_field_id' => $fieldId,
                    ],
                    [
                        'value' => $value,
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

        session()->flash('message', 'Worksheet values saved successfully.');
    }
}
