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
    
    // Lookup table override management
    public $showLookupTableModal = false;
    public $selectedLookupStepId = null;
    public $currentCapturedResultId = null;
    public $compatibleLookupTables = [];
    public $selectedReplacementLookupTableId = null;
    
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
                // Load lookup overrides from step data
                $lookupOverrides = [];
                if ($existing->stepData) {
                    foreach ($existing->stepData as $stepData) {
                        if ($stepData->overridden_lookup_table_id) {
                            $lookupOverrides[$stepData->formula_step_id] = $stepData->overridden_lookup_table_id;
                        }
                    }
                }
                
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
                    'lookup_overrides' => $lookupOverrides,
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
                    'lookup_overrides' => [],
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
                    $stepDataRecord = $worksheet->stepData()->updateOrCreate(
                        ['formula_step_id' => $stepId],
                        ['step_value' => $value]
                    );
                    
                    // Save lookup override if exists for this step
                    if (isset($data['lookup_overrides'][$stepId])) {
                        $stepDataRecord->overridden_lookup_table_id = $data['lookup_overrides'][$stepId];
                        $stepDataRecord->save();
                    } else {
                        // Clear override if it was removed
                        if ($stepDataRecord->overridden_lookup_table_id) {
                            $stepDataRecord->overridden_lookup_table_id = null;
                            $stepDataRecord->save();
                        }
                    }
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
                
                // Populate from analysis element configuration if not already set
                if ($captured->analysisElement) {
                    if (!$captured->reporting_unit_id) {
                        $captured->reporting_unit_id = $captured->analysisElement->reporting_unit;
                    }
                    if (!$captured->method_id) {
                        $captured->method_id = $captured->analysisElement->method;
                    }
                }
                
                // remark left null - will be updated later by user
                
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
        
        // Check if lookup table override changed
        if (strpos($propertyName, 'worksheetData.') === 0 && strpos($propertyName, '.lookup_overrides.') !== false) {
            preg_match('/worksheetData\\.(\\d+)\\.lookup_overrides/', $propertyName, $matches);
            if (isset($matches[1])) {
                $capturedResultId = (int)$matches[1];
                Log::info("Lookup override changed for captured result ID: {$capturedResultId}");
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
            
            // Get lookup overrides for this worksheet row
            $lookupOverrides = $data['lookup_overrides'] ?? [];
            
            // Use FormulaEvaluator to calculate
            $evaluator = app(\App\Services\Formulars\FormulaEvaluator::class);
            $result = $evaluator->execute($this->formula->activeVersion, $filledInputs, null, $this->batch->id, $lookupOverrides);
            
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

    /**
     * Open modal to change lookup table for a specific step.
     */
    public function openChangeLookupModal(int $capturedResultId, int $stepId): void
    {
        try {
            $this->currentCapturedResultId = $capturedResultId;
            $this->selectedLookupStepId = $stepId;
            
            // Get the formula step to find current lookup table
            $step = FormulaStep::find($stepId);
            if (!$step || !$step->isLookup()) {
                $this->setMessage('Invalid lookup step', 'error');
                return;
            }
            
            $lookupConfig = $step->lookup_config;
            if (!$lookupConfig || !isset($lookupConfig['lookup_table_id'])) {
                $this->setMessage('Lookup configuration not found', 'error');
                return;
            }
            
            // Get current lookup table
            $currentLookupTable = \App\Models\Formulars\LookupTable::find($lookupConfig['lookup_table_id']);
            if (!$currentLookupTable) {
                $this->setMessage('Current lookup table not found', 'error');
                return;
            }
            
            // Get compatible lookup tables
            $this->compatibleLookupTables = $currentLookupTable->getCompatibleTables()->toArray();
            
            if (empty($this->compatibleLookupTables)) {
                $this->setMessage('No compatible lookup tables found for this step', 'warning');
                return;
            }
            
            // Set current override if exists
            $currentOverride = $this->worksheetData[$capturedResultId]['lookup_overrides'][$stepId] ?? null;
            $this->selectedReplacementLookupTableId = $currentOverride ?? $lookupConfig['lookup_table_id'];
            
            $this->showLookupTableModal = true;
        } catch (\Exception $e) {
            Log::error('Error opening lookup modal: ' . $e->getMessage());
            $this->setMessage('Error loading compatible lookup tables', 'error');
        }
    }

    /**
     * Change the lookup table for a specific step.
     */
    public function changeLookupTable(): void
    {
        try {
            if (!$this->currentCapturedResultId || !$this->selectedLookupStepId || !$this->selectedReplacementLookupTableId) {
                $this->setMessage('Missing required information', 'error');
                return;
            }
            
            // Get the formula step
            $step = FormulaStep::find($this->selectedLookupStepId);
            if (!$step || !$step->isLookup()) {
                $this->setMessage('Invalid lookup step', 'error');
                return;
            }
            
            // Get the new lookup table
            $newLookupTable = \App\Models\Formulars\LookupTable::find($this->selectedReplacementLookupTableId);
            if (!$newLookupTable) {
                $this->setMessage('Selected lookup table not found', 'error');
                return;
            }
            
            // Verify it's active
            if (!$newLookupTable->is_active) {
                $this->setMessage('Lookup table must be active to use', 'error');
                return;
            }
            
            // Get original lookup table and verify compatibility
            $lookupConfig = $step->lookup_config;
            $originalLookupTable = \App\Models\Formulars\LookupTable::find($lookupConfig['lookup_table_id']);
            
            // If selecting the original, remove the override
            if ($this->selectedReplacementLookupTableId == $lookupConfig['lookup_table_id']) {
                $this->resetLookupTable($this->currentCapturedResultId, $this->selectedLookupStepId);
                return;
            }
            
            if ($originalLookupTable && !$originalLookupTable->isCompatibleWith($newLookupTable)) {
                $this->setMessage('Selected lookup table is not compatible with this formula step', 'error');
                return;
            }
            
            // Set the override
            if (!isset($this->worksheetData[$this->currentCapturedResultId]['lookup_overrides'])) {
                $this->worksheetData[$this->currentCapturedResultId]['lookup_overrides'] = [];
            }
            $this->worksheetData[$this->currentCapturedResultId]['lookup_overrides'][$this->selectedLookupStepId] = $this->selectedReplacementLookupTableId;
            
            // Log the change
            Log::info("Lookup table override applied", [
                'captured_result_id' => $this->currentCapturedResultId,
                'step_id' => $this->selectedLookupStepId,
                'original_lookup_table_id' => $lookupConfig['lookup_table_id'],
                'new_lookup_table_id' => $this->selectedReplacementLookupTableId,
            ]);
            
            // Recalculate the formula
            $this->calculateFormulaResult($this->currentCapturedResultId);
            
            // Auto-save the change
            $this->autoSaveRow($this->currentCapturedResultId);
            
            $this->setMessage('Lookup table changed successfully. This change only affects this worksheet.', 'success');
            $this->closeLookupModal();
        } catch (\Exception $e) {
            Log::error('Error changing lookup table: ' . $e->getMessage());
            $this->setMessage('Error changing lookup table: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Reset lookup table to formula default.
     */
    public function resetLookupTable(?int $capturedResultId = null, ?int $stepId = null): void
    {
        try {
            $capturedId = $capturedResultId ?? $this->currentCapturedResultId;
            $stepIdToReset = $stepId ?? $this->selectedLookupStepId;
            
            if (!$capturedId || !$stepIdToReset) {
                $this->setMessage('Missing required information', 'error');
                return;
            }
            
            // Remove the override
            if (isset($this->worksheetData[$capturedId]['lookup_overrides'][$stepIdToReset])) {
                unset($this->worksheetData[$capturedId]['lookup_overrides'][$stepIdToReset]);
            }
            
            // Log the reset
            Log::info("Lookup table override removed", [
                'captured_result_id' => $capturedId,
                'step_id' => $stepIdToReset,
            ]);
            
            // Recalculate the formula
            $this->calculateFormulaResult($capturedId);
            
            // Auto-save the change
            $this->autoSaveRow($capturedId);
            
            $this->setMessage('Lookup table reset to formula default', 'success');
            $this->closeLookupModal();
        } catch (\Exception $e) {
            Log::error('Error resetting lookup table: ' . $e->getMessage());
            $this->setMessage('Error resetting lookup table: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Close the lookup table modal.
     */
    public function closeLookupModal(): void
    {
        $this->showLookupTableModal = false;
        $this->selectedLookupStepId = null;
        $this->currentCapturedResultId = null;
        $this->compatibleLookupTables = [];
        $this->selectedReplacementLookupTableId = null;
    }

    /**
     * Get the original lookup table for a step.
     */
    public function getOriginalLookupTable(int $stepId): ?\App\Models\Formulars\LookupTable
    {
        $step = FormulaStep::find($stepId);
        if (!$step || !$step->isLookup()) {
            return null;
        }
        
        $lookupConfig = $step->lookup_config;
        if (!$lookupConfig || !isset($lookupConfig['lookup_table_id'])) {
            return null;
        }
        
        return \App\Models\Formulars\LookupTable::find($lookupConfig['lookup_table_id']);
    }

    /**
     * Get the current (possibly overridden) lookup table for a step in a specific worksheet row.
     */
    public function getCurrentLookupTable(int $capturedResultId, int $stepId): ?\App\Models\Formulars\LookupTable
    {
        $override = $this->worksheetData[$capturedResultId]['lookup_overrides'][$stepId] ?? null;
        
        if ($override) {
            return \App\Models\Formulars\LookupTable::find($override);
        }
        
        return $this->getOriginalLookupTable($stepId);
    }

    public function render()
    {
        return view('livewire.worksheets.formula-worksheet', [
            'users' => User::where('active', 1)->get(),
        ]);
    }
}
