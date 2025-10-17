<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaStep;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\SampleAnalysisDates;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FormulaWorksheet extends Component
{
    public SampleHeader $batch;
    public Formula $formula;
    public $capturedResults = [];
    public $worksheetData = [];
    public $formulaSteps = [];
    public $mandatoryFields = [];
    public $sharedMandatoryData = [];
    public $equipments = [];
    public $users = [];
    public $methods = [];
    
    // Messages
    public $message = '';
    public $messageType = '';

    public function mount(SampleHeader $batch, Formula $formula): void
    {
        $this->batch = $batch;
        $this->formula = $formula;
        $this->loadDatasets();
        $this->loadData();
    }

    public function loadData(): void
    {
        // Get captured results for this batch and formula
        $this->capturedResults = CapturedResult::where('sample_header_id', $this->batch->id)
            ->where('formular_id', $this->formula->id)
            ->with(['sample.sample_point', 'analysisElement.analyte'])
            ->get();

        // Get formula steps (input, derived, dataset, lookup)
        $this->formulaSteps = FormulaStep::where('formula_version_id', $this->formula->activeVersion->id)
            ->orderBy('step_number')
            ->get();

        // Get mandatory fields
        $this->mandatoryFields = FormulaMandatoryField::where('formula_version_id', $this->formula->activeVersion->id)
            ->orderBy('order')
            ->get();

        // Load existing worksheet data
        $this->loadWorksheetData();
    }

    // New method to load datasets for mandatory fields
    protected function loadDatasets(): void
    {
        $this->equipments = \App\Models\Equipments\Equipment::where('active', 1)->get();
        $this->users = User::where('active', 1)->get();
        $this->methods = \App\AnalysisMethod::all();
    }

    // New method to get dataset options based on model type
    public function getDatasetOptions(string $modelType)
    {
        return match($modelType) {
            'equipments' => $this->equipments,
            'users' => $this->users,
            'methods' => $this->methods,
            default => collect([])
        };
    }

    public function loadWorksheetData(): void
    {
        foreach ($this->capturedResults as $captured) {
            $existing = SampleCapturedWorksheetFormula::where('captured_result_id', $captured->id)
                ->with(['stepData', 'mandatoryData'])
                ->first();

            if ($existing) {
                $this->worksheetData[$captured->id] = [
                    'id' => $existing->id,
                    'date' => $existing->date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'time_in' => $existing->time_in?->format('H:i') ?? now()->format('H:i'),
                    'done_by_user_id' => $existing->done_by_user_id ?? Auth::id(),
                    'time_out' => $existing->time_out?->format('H:i') ?? '',
                    'read_by_user_id' => $existing->read_by_user_id,
                    'read_date' => $existing->read_date?->format('Y-m-d') ?? '',
                    'final_result' => $existing->final_result ?? '',
                    'steps' => $existing->stepData ? $existing->stepData->pluck('step_value', 'formula_step_id')->toArray() : [],
                    'mandatory' => $existing->mandatoryData ? $existing->mandatoryData->pluck('field_value', 'formula_mandatory_field_id')->toArray() : [],
                ];
            } else {
                $this->worksheetData[$captured->id] = [
                    'id' => null,
                    'date' => now()->format('Y-m-d'),
                    'time_in' => now()->format('H:i'),
                    'done_by_user_id' => Auth::id(),
                    'time_out' => '',
                    'read_by_user_id' => null,
                    'read_date' => '',
                    'final_result' => '',
                    'steps' => [],
                    'mandatory' => [],
                ];
            }
        }

        // Load shared mandatory data (from first captured result if exists)
        $firstCaptured = $this->capturedResults->first();
        if ($firstCaptured) {
            $existing = SampleCapturedWorksheetFormula::where('captured_result_id', $firstCaptured->id)->first();
            if ($existing && $existing->mandatoryData) {
                /** @var \Illuminate\Database\Eloquent\Collection $mandatoryData */
                $mandatoryData = $existing->mandatoryData;
                if ($mandatoryData instanceof \Illuminate\Database\Eloquent\Collection) {
                    $this->sharedMandatoryData = $mandatoryData->pluck('field_value', 'formula_mandatory_field_id')->toArray();
                }
            }
        }
    }

    public function saveWorksheet(int $capturedResultId): void
    {
        try {
            DB::beginTransaction();

            $data = $this->worksheetData[$capturedResultId] ?? null;
            if (!$data) {
                throw new \Exception('Worksheet data not found');
            }

            $captured = CapturedResult::findOrFail($capturedResultId);

            // Create or update worksheet
            $worksheet = SampleCapturedWorksheetFormula::updateOrCreate(
                ['captured_result_id' => $capturedResultId],
                [
                    'sample_header_id' => $this->batch->id,
                    'sample_detail_id' => $captured->sample_detail_id,
                    'formular_id' => $this->formula->id,
                    'date' => $data['date'],
                    'lab_no' => $this->batch->batch_code,
                    'sample_details' => $captured->sample->sample_code . ' - ' . ($captured->analysisElement->analyte->name ?? '') . ' - ' . ($captured->sample->sample_point->name ?? ''),
                    'time_in' => $data['time_in'],
                    'done_by_user_id' => $data['done_by_user_id'],
                    'time_out' => $data['time_out'] ?: null,
                    'read_by_user_id' => $data['read_by_user_id'] ?: null,
                    'read_date' => $data['read_date'] ?: null,
                    'final_result' => $data['final_result'] ?: null,
                ]
            );

            // Save step data
            if (isset($data['steps'])) {
                foreach ($data['steps'] as $stepId => $value) {
                    $worksheet->stepData()->updateOrCreate(
                        ['formula_step_id' => $stepId],
                        ['step_value' => $value]
                    );
                }
            }

            // Save shared mandatory data to this worksheet
            foreach ($this->sharedMandatoryData as $fieldId => $value) {
                $worksheet->mandatoryData()->updateOrCreate(
                    ['formula_mandatory_field_id' => $fieldId],
                    ['field_value' => $value]
                );
            }

            // Update captured result with final result and operator
            if ($data['final_result']) {
                $captured->result = $data['final_result'];
                
                // Set operator_id (becomes analyst_id in tat_captured via observer)
                // Priority: done_by_user_id, then current user
                $captured->operator_id = $data['done_by_user_id'] ?? Auth::id();
                
                // Ensure start_date_analysis exists before save triggers observer
                SampleAnalysisDates::firstOrCreate(
                    [
                        'sample_detail_id' => $captured->sample_detail_id,
                        'sample_header_id' => $captured->sample_header_id,
                    ],
                    [
                        'start_analysis_date' => $data['date'] ?? now()->format('Y-m-d'),
                    ]
                );
                
                $captured->save();
            }

            DB::commit();
            $this->setMessage('Worksheet auto-saved successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error saving worksheet: ' . $e->getMessage(), 'error');
        }
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    // Add real-time calculation with Livewire events
    public function updated($propertyName)
    {
        Log::info("Livewire updated() called for property: {$propertyName}");
        
        // Check if it's a step input field
        if (strpos($propertyName, 'worksheetData.') === 0 && strpos($propertyName, '.steps.') !== false) {
            preg_match('/worksheetData\\.(\\d+)\\.steps/', $propertyName, $matches);
            if (isset($matches[1])) {
                $capturedResultId = (int)$matches[1];
                Log::info("Triggering calculation for captured result ID: {$capturedResultId}");
                $this->calculateFormulaResult($capturedResultId);
            }
        }
    }

    // Enhanced method for real-time calculation
    protected function calculateFormulaResult(int $capturedResultId): void
    {
        try {
            $data = $this->worksheetData[$capturedResultId] ?? null;
            if (!$data || !isset($data['steps'])) {
                Log::warning("No worksheet data found for captured result ID: {$capturedResultId}");
                return;
            }
            
            // Check if all input steps have values
            /** @var \Illuminate\Database\Eloquent\Collection $inputSteps */
            $inputSteps = $this->formulaSteps->where('step_type', 'input');
            $allInputsFilled = true;
            $filledInputs = [];
            
            foreach ($inputSteps as $step) {
                $stepData = $data['steps'] ?? [];
                $value = $stepData[$step->id] ?? null;
                
                if ($value === '' || $value === null) {
                    $allInputsFilled = false;
                } else {
                    $filledInputs[$step->variable_name] = $value;
                }
            }
            
            // Log calculation attempt
            Log::info("Calculating formula for captured result {$capturedResultId}. All inputs filled: " . ($allInputsFilled ? 'Yes' : 'No') . ". Filled inputs: " . json_encode($filledInputs));
            
            if (!$allInputsFilled) {
                Log::info("Not all input steps have values, skipping calculation for captured result ID: {$capturedResultId}");
                return;
            }
            
            // Use FormulaEvaluator to calculate
            $evaluator = app(\App\Services\Formulars\FormulaEvaluator::class);
            $result = $evaluator->execute($this->formula->activeVersion, $filledInputs, null, $this->batch->id);
            
            Log::info("Formula calculation result for captured result {$capturedResultId}: " . json_encode($result));
            
            // Update derived, lookup, and final result
            foreach ($this->formulaSteps as $step) {
                if ($step->step_type === 'derived' || $step->step_type === 'lookup') {
                    $calculatedValue = $result['variables'][$step->variable_name] ?? '';
                    $this->worksheetData[$capturedResultId]['steps'][$step->id] = $calculatedValue;
                    Log::info("Updated {$step->step_type} step '{$step->variable_name}' with value: '{$calculatedValue}'");
                }
            }
            
            $finalResult = $result['execution_data']['final_result'] ?? '';
            $this->worksheetData[$capturedResultId]['final_result'] = $finalResult;
            Log::info("Updated final result for captured result {$capturedResultId}: '{$finalResult}'");
            
            // Force Livewire to refresh the component from the server
            $this->js('$wire.$refresh()');
            
        } catch (\Exception $e) {
            Log::error('Formula calculation error for captured result ' . $capturedResultId . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    // New method for auto-save on field blur
    public function autoSaveRow(int $capturedResultId): void
    {
        try {
            $this->saveWorksheet($capturedResultId);
        } catch (\Exception $e) {
            Log::error('Auto-save error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.worksheets.formula-worksheet', [
            'users' => User::where('active', 1)->get(),
        ]);
    }
}
