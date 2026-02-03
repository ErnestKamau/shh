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
    protected $listeners = [
        'triggerPostResults' => 'openPostResultsModal',
        'closeAllDropdowns' => 'closeAllDropdowns'
    ];

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

    // Post Results Modal & Status
    public $showPostResultsModal = false;
    public $samplesWithStandards = [];
    public $postingInProgress = false;
    public $currentStep = 0;
    public $totalSteps = 3;
    public $stepMessages = [
        1 => 'Confirming standards and preparing data...',
        2 => 'Posting results with reporting symbols...',
        3 => 'Recalculating remarks based on standards...'
    ];
    public $currentStepMessage = '';
    public $processedCount = 0;
    public $totalCount = 0;

    // Standards modal data
    public $availableStandards = [];
    public $mainStandardSearch = [];
    public $secondaryStandardSearch = [];
    public $showMainStandardDropdown = [];
    public $showSecondaryStandardDropdown = [];
    public $filteredMainStandards = [];
    public $filteredSecondaryStandards = [];
    public $useLookupAsStandard = []; // [sample_id => true/false]
    public $lookupStandardInfo = null; // Info about the lookup table to use

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
        return match ($modelType) {
            'equipments' => $this->equipments,
            'users' => $this->users,
            'methods' => $this->methods,
            default => collect([])
        };
    }

    public function getUsedLookupTables()
    {
        $lookupTables = [];
        $lookupSteps = $this->formulaSteps->where('step_type', 'lookup');

        foreach ($lookupSteps as $step) {
            if ($step->lookup_config && isset($step->lookup_config['lookup_table_id'])) {
                $lookupTableId = $step->lookup_config['lookup_table_id'];
                if (!isset($lookupTables[$lookupTableId])) {
                    $lookupTable = \App\Models\Formulars\LookupTable::find($lookupTableId);
                    if ($lookupTable) {
                        $lookupTables[$lookupTableId] = $lookupTable;
                    }
                }
            }
        }

        return collect($lookupTables);
    }

    /**
     * Get worksheet with posting metadata
     */
    public function getWorksheetWithPostingInfo()
    {
        return SampleCapturedWorksheetFormula::where('sample_header_id', $this->batch->id)
            ->where('formular_id', $this->formula->id)
            ->with('postedBy')
            ->first();
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
                    'time_in' => $existing->time_in?->format('H:i') ?? null,
                    'done_by_user_id' => $existing->done_by_user_id ?? Auth::id(),
                    'time_out' => $existing->time_out?->format('H:i') ?? null,
                    'read_by_user_id' => $existing->read_by_user_id ?? Auth::id(),
                    'read_date' => $existing->read_date?->format('Y-m-d') ?? null,
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
                    'time_out' => null,
                    'read_by_user_id' => Auth::id(),
                    'read_date' => now()->format('Y-m-d'),
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
                // Properly save with lab section tracking and earliest date calculation
                $analysis_date = SampleAnalysisDates::where('sample_header_id', $captured->sample_header_id)
                    ->where('sample_detail_id', $captured->sample_detail_id)
                    ->first() ?? new SampleAnalysisDates();

                $currentDate = $data['date'] ?? now()->format('Y-m-d');

                if (isset($analysis_date->id)) {
                    // Update existing - merge lab section dates
                    $prev_dates = $analysis_date->analysis_dates ? json_decode($analysis_date->analysis_dates, true) : [];
                    if ($captured->lab_section_id) {
                        $prev_dates[$captured->lab_section_id] = $currentDate;
                    }

                    // Calculate earliest date across all lab sections
                    $start_date = '';
                    foreach ($prev_dates as $key => $val) {
                        if ($start_date == '') {
                            $start_date = $val;
                        } else {
                            $start_date = $val < $start_date ? $val : $start_date;
                        }
                    }
                    $analysis_date->start_analysis_date = $start_date;
                    $analysis_date->analysis_dates = json_encode($prev_dates);
                } else {
                    // Create new
                    $prev_dates = [];
                    if ($captured->lab_section_id) {
                        $prev_dates[$captured->lab_section_id] = $currentDate;
                    }
                    $analysis_date->sample_header_id = $captured->sample_header_id;
                    $analysis_date->sample_detail_id = $captured->sample_detail_id;
                    $analysis_date->start_analysis_date = $currentDate;
                    $analysis_date->analysis_dates = json_encode($prev_dates);
                }

                $analysis_date->save();

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
                $capturedResultId = (int) $matches[1];
                Log::info("Triggering calculation for captured result ID: {$capturedResultId}");
                $this->calculateFormulaResult($capturedResultId);
            }
        }

        // Check if lookup table override changed
        if (strpos($propertyName, 'worksheetData.') === 0 && strpos($propertyName, '.lookup_overrides.') !== false) {
            preg_match('/worksheetData\\.(\\d+)\\.lookup_overrides/', $propertyName, $matches);
            if (isset($matches[1])) {
                $capturedResultId = (int) $matches[1];
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
     * Auto-save mandatory field when user leaves the field
     */
    public function autoSaveMandatoryField(int $fieldId): void
    {
        try {
            $this->saveMandatoryFields();
        } catch (\Exception $e) {
            Log::error('Auto-save mandatory field error: ' . $e->getMessage());
        }
    }

    /**
     * Save mandatory fields to all captured results in this worksheet
     */
    public function saveMandatoryFields(): void
    {
        try {
            DB::beginTransaction();

            // Save shared mandatory data to all captured results in this worksheet
            foreach ($this->capturedResults as $captured) {
                $worksheet = SampleCapturedWorksheetFormula::where('captured_result_id', $captured->id)->first();

                if ($worksheet) {
                    foreach ($this->sharedMandatoryData as $fieldId => $value) {
                        $worksheet->mandatoryData()->updateOrCreate(
                            ['formula_mandatory_field_id' => $fieldId],
                            ['field_value' => $value]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save mandatory fields error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Open Post Results Modal with standards confirmation
     */
    public function openPostResultsModal(): void
    {
        try {
            // Load available standards for dropdowns
            $this->availableStandards = \App\Standards::where('status', 1)
                ->orderBy('name')
                ->get();

            // Get unique samples with analysis types
            $samples = [];

            foreach ($this->capturedResults as $captured) {
                $sample = $captured->sample;
                if (!isset($samples[$sample->id])) {
                    // Get unique analysis types for this sample
                    $analysisTypeNames = $this->capturedResults
                        ->where('sample_detail_id', $sample->id)
                        ->map(function ($cr) {
                            return $cr->analysisElement->analysis_type->name ?? null;
                        })
                        ->unique()
                        ->filter()
                        ->implode(', ');

                    $samples[$sample->id] = [
                        'id' => $sample->id,
                        'sample_code' => $sample->sample_code,
                        'analysis_types' => $analysisTypeNames ?: 'N/A',
                        'main_standard' => $sample->main_standard,
                        'secondary_standard' => $sample->secondary_standard,
                        'third_standard_id' => $sample->third_standard_id,
                    ];
                }
            }

            // Find lookup table marked as standard (last step with is_standard = 1)
            $this->lookupStandardInfo = $this->getStandardLookupTable();

            $this->samplesWithStandards = array_values($samples);
            $this->showPostResultsModal = true;
            $this->currentStep = 0;
            $this->postingInProgress = false;
        } catch (\Exception $e) {
            Log::error('Error opening post results modal: ' . $e->getMessage());
            $this->setMessage('Error loading standards information', 'error');
        }
    }

    /**
     * Get standard lookup table from formula
     */
    private function getStandardLookupTable(): ?array
    {
        $lookupSteps = $this->formulaSteps
            ->where('step_type', 'lookup')
            ->sortByDesc('step_number');

        foreach ($lookupSteps as $step) {
            if ($step->lookup_config && isset($step->lookup_config['lookup_table_id'])) {
                $lookupTable = \App\Models\Formulars\LookupTable::find($step->lookup_config['lookup_table_id']);

                if ($lookupTable && $lookupTable->is_standard) {
                    return [
                        'id' => $lookupTable->id,
                        'name' => $lookupTable->name,
                        'step_id' => $step->id,
                        'variable_name' => $step->variable_name,
                        'value_interpretation_column' => $lookupTable->value_interpretation_column,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Update sample standard (persists to database)
     */
    public function updateSampleStandard(int $sampleId, string $standardType, $standardId): void
    {
        try {
            $sample = \App\SampleDetails::find($sampleId);
            if (!$sample) {
                return;
            }

            // Update the appropriate standard field
            if ($standardType === 'main') {
                $sample->main_standard = $standardId ?: null;
            } elseif ($standardType === 'secondary') {
                $sample->secondary_standard = $standardId ?: null;
            } elseif ($standardType === 'third') {
                $sample->third_standard_id = $standardId ?: null;
            }

            $sample->save();

            // Update the samplesWithStandards array
            foreach ($this->samplesWithStandards as $key => $s) {
                if ($s['id'] == $sampleId) {
                    $this->samplesWithStandards[$key][$standardType . '_standard'] = $standardId;
                    break;
                }
            }

            // Close the dropdown
            if ($standardType === 'main') {
                $this->showMainStandardDropdown[$sampleId] = false;
            } else {
                $this->showSecondaryStandardDropdown[$sampleId] = false;
            }

        } catch (\Exception $e) {
            Log::error('Error updating sample standard: ' . $e->getMessage());
        }
    }

    public function searchMainStandards(int $sampleId): void
    {
        $this->showMainStandardDropdown[$sampleId] = true;

        $searchTerm = $this->mainStandardSearch[$sampleId] ?? '';

        if (empty($searchTerm)) {
            $this->filteredMainStandards[$sampleId] = $this->availableStandards;
        } else {
            $this->filteredMainStandards[$sampleId] = $this->availableStandards->filter(function ($standard) use ($searchTerm) {
                return stripos($standard->name, $searchTerm) !== false ||
                    stripos($standard->code, $searchTerm) !== false;
            });
        }


        // Dispatch event for positioning
        $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'main']);
    }

    public function searchSecondaryStandards(int $sampleId): void
    {
        $this->showSecondaryStandardDropdown[$sampleId] = true;

        $searchTerm = $this->secondaryStandardSearch[$sampleId] ?? '';

        if (empty($searchTerm)) {
            $this->filteredSecondaryStandards[$sampleId] = $this->availableStandards;
        } else {
            $this->filteredSecondaryStandards[$sampleId] = $this->availableStandards->filter(function ($standard) use ($searchTerm) {
                return stripos($standard->name, $searchTerm) !== false ||
                    stripos($standard->code, $searchTerm) !== false;
            });
        }

        // Dispatch event for positioning
        $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'secondary']);
    }

    public function getSelectedStandardName($standardId): string
    {
        if (!$standardId) {
            return '';
        }

        $standard = $this->availableStandards->firstWhere('id', $standardId);
        return $standard ? $standard->name . ' (' . $standard->code . ')' : '';
    }

    /**
     * Toggle main standard dropdown for a sample
     */
    public function toggleMainStandardDropdown(int $sampleId): void
    {
        $this->showMainStandardDropdown[$sampleId] = !($this->showMainStandardDropdown[$sampleId] ?? false);

        if ($this->showMainStandardDropdown[$sampleId]) {
            $this->searchMainStandards($sampleId);
            $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'main']);
        }

    }

    /**
     * Toggle secondary standard dropdown for a sample
     */
    public function toggleSecondaryStandardDropdown(int $sampleId): void
    {
        $this->showSecondaryStandardDropdown[$sampleId] = !($this->showSecondaryStandardDropdown[$sampleId] ?? false);

        if ($this->showSecondaryStandardDropdown[$sampleId]) {
            $this->searchSecondaryStandards($sampleId);
            $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'secondary']);
        }
    }

    /**
     * Close all dropdowns
     */
    public function closeAllDropdowns(): void
    {
        $this->showMainStandardDropdown = [];
        $this->showSecondaryStandardDropdown = [];
    }

    /**
     * Post Results with Progress Tracking
     */
    public function postResults(): void
    {
        try {
            $this->postingInProgress = true;
            $this->totalCount = $this->capturedResults->count();
            $this->processedCount = 0;

            // Step 1: Prepare and confirm standards
            $this->currentStep = 1;
            $this->currentStepMessage = $this->stepMessages[1];
            $this->dispatch('stepUpdated');
            sleep(1); // Brief pause for user to see progress

            DB::beginTransaction();

            // Step 2: Post results with reporting symbols
            $this->currentStep = 2;
            $this->currentStepMessage = $this->stepMessages[2];
            $this->dispatch('stepUpdated');

            $updatedCount = 0;

            foreach ($this->capturedResults as $captured) {
                $wsData = $this->worksheetData[$captured->id] ?? null;

                if (!$wsData || !isset($wsData['final_result']) || $wsData['final_result'] === '') {
                    continue;
                }

                $sample = $captured->sample;

                // Extract reporting symbol and numeric value
                $finalResult = $wsData['final_result'];
                $reportingSymbol = '';
                $numericResult = $finalResult;

                // Check for reporting symbols (<=, >=, <, >)
                if (preg_match('/^(<=|>=|<|>)\s*(.+)$/', trim($finalResult), $matches)) {
                    $reportingSymbol = $matches[1];
                    $numericResult = trim($matches[2]);
                }

                // Update captured result with final result and symbol
                $captured->result = $numericResult;
                $captured->result_reporting_symbol = $reportingSymbol;
                $captured->operator_id = $wsData['done_by_user_id'] ?? Auth::id();

                // Populate other fields if not set
                if ($captured->analysisElement) {
                    if (!$captured->reporting_unit_id) {
                        $captured->reporting_unit_id = $captured->analysisElement->reporting_unit;
                    }
                    if (!$captured->method_id) {
                        $captured->method_id = $captured->analysisElement->method;
                    }
                }

                // Ensure analysis dates exist with proper lab section tracking
                $analysis_date = SampleAnalysisDates::where('sample_header_id', $captured->sample_header_id)
                    ->where('sample_detail_id', $captured->sample_detail_id)
                    ->first() ?? new SampleAnalysisDates();

                $currentDate = $wsData['date'] ?? now()->format('Y-m-d');

                if (isset($analysis_date->id)) {
                    // Update existing - merge lab section dates
                    $prev_dates = $analysis_date->analysis_dates ? json_decode($analysis_date->analysis_dates, true) : [];
                    if ($captured->lab_section_id) {
                        $prev_dates[$captured->lab_section_id] = $currentDate;
                    }

                    // Calculate earliest date across all lab sections
                    $start_date = '';
                    foreach ($prev_dates as $key => $val) {
                        if ($start_date == '') {
                            $start_date = $val;
                        } else {
                            $start_date = $val < $start_date ? $val : $start_date;
                        }
                    }
                    $analysis_date->start_analysis_date = $start_date;
                    $analysis_date->analysis_dates = json_encode($prev_dates);
                } else {
                    // Create new
                    $prev_dates = [];
                    if ($captured->lab_section_id) {
                        $prev_dates[$captured->lab_section_id] = $currentDate;
                    }
                    $analysis_date->sample_header_id = $captured->sample_header_id;
                    $analysis_date->sample_detail_id = $captured->sample_detail_id;
                    $analysis_date->start_analysis_date = $currentDate;
                    $analysis_date->analysis_dates = json_encode($prev_dates);
                }

                $analysis_date->save();

                $captured->save();
                $this->processedCount++;
            }

            // Step 3: Recalculate remarks
            $this->currentStep = 3;
            $this->currentStepMessage = $this->stepMessages[3];
            $this->dispatch('stepUpdated');
            $this->processedCount = 0;

            foreach ($this->capturedResults as $captured) {
                $wsData = $this->worksheetData[$captured->id] ?? null;

                if (!$wsData || !isset($wsData['final_result']) || $wsData['final_result'] === '') {
                    continue;
                }

                $sample = $captured->sample;
                $finalResult = $wsData['final_result'];
                $reportingSymbol = '';
                $numericResult = $finalResult;

                if (preg_match('/^(<=|>=|<|>)\s*(.+)$/', trim($finalResult), $matches)) {
                    $reportingSymbol = $matches[1];
                    $numericResult = trim($matches[2]);
                }

                // Check if user opted to use lookup table as standard
                if (
                    isset($this->useLookupAsStandard[$sample->id]) &&
                    $this->useLookupAsStandard[$sample->id] &&
                    $this->lookupStandardInfo
                ) {

                    // Use lookup table interpretation as remark
                    $lookupStepId = $this->lookupStandardInfo['step_id'];

                    // Get the lookup result from worksheet data
                    // The lookup step stores both value and interpretation
                    $lookupValue = $wsData['steps'][$lookupStepId] ?? '';

                    // For lookup tables with value_interpretation_column, 
                    // the interpretation is what we use as the remark
                    $remark = $lookupValue ?: '-';
                } else {
                    // Use standard-based calculation
                    $remark = $this->calculateRemark($captured, $sample, $numericResult, $reportingSymbol);
                }

                $captured->remark = $remark;
                $captured->save();

                $this->processedCount++;
                $updatedCount++;
            }

            // Update worksheet posting metadata
            $worksheet = SampleCapturedWorksheetFormula::where('sample_header_id', $this->batch->id)
                ->where('formular_id', $this->formula->id)
                ->first();

            if ($worksheet) {
                $worksheet->posted_at = now();
                $worksheet->posted_by_user_id = Auth::id();
                $worksheet->save();
            }

            DB::commit();

            $this->showPostResultsModal = false;
            $this->postingInProgress = false;
            $this->setMessage("Successfully posted {$updatedCount} results to captured results!", 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->postingInProgress = false;
            Log::error('Error posting results: ' . $e->getMessage());
            $this->setMessage('Error posting results: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Calculate remark based on standards
     */
    private function calculateRemark($captured, $sample, $result, $reportingSymbol): string
    {
        $remarkArr = [];

        // Get standards for the sample
        $mainStandard = $sample->main_standard ? \App\Standards::find($sample->main_standard) : null;
        $secStandard = $sample->secondary_standard ? \App\Standards::find($sample->secondary_standard) : null;
        $thirdStandard = $sample->third_standard_id ? \App\Standards::find($sample->third_standard_id) : null;

        $analyte = \App\Analyte::find($captured->analyte_id);

        // Calculate remark for each standard
        if ($mainStandard && $analyte) {
            $mainRemark = $this->getResultRemark($mainStandard, $analyte, $result, $reportingSymbol);
            if ($mainRemark !== '') {
                $remarkArr[] = $mainRemark;
            }
        }

        if ($secStandard && $analyte) {
            $secRemark = $this->getResultRemark($secStandard, $analyte, $result, $reportingSymbol);
            if ($secRemark !== '') {
                $remarkArr[] = $secRemark;
            }
        }

        if ($thirdStandard && $analyte) {
            $thirdRemark = $this->getResultRemark($thirdStandard, $analyte, $result, $reportingSymbol);
            if ($thirdRemark !== '') {
                $remarkArr[] = $thirdRemark;
            }
        }

        // Return final remark based on logic - same as SampleWorkFlowController
        if (in_array('FAIL', $remarkArr)) {
            return 'FAIL';
        } elseif (in_array('PASS', $remarkArr)) {
            return 'PASS';
        } else {
            return '-';
        }
    }

    /**
     * Get result remark for a specific standard - mirrors SampleWorkFlowController logic
     */
    private function getResultRemark($standard, $analyte, $result, $reportingSymbol): string
    {
        if (!isset($standard->id) || !isset($analyte->id)) {
            return '-';
        }

        $analyteGuide = \App\StandardAnalytes::where('analyte_id', $analyte->id)
            ->where('standard_id', $standard->id)
            ->first();

        if (!isset($analyteGuide->standard_value_type)) {
            return '-';
        }

        // Range-based standard
        if ($analyteGuide->standard_value_type == 'is_range') {
            if (is_numeric($result) && $analyteGuide->low <= $result && $result <= $analyteGuide->high) {
                return 'PASS';
            } else {
                return 'FAIL';
            }
        }

        // Value-based standard
        $standardValue = \App\StandardValue::find($analyteGuide->standard_value_id);

        if (!is_numeric($result)) {
            // Non-numeric result handling
            if (strtoupper($result) == 'ND' && isset($standardValue->code)) {
                if (in_array(strtoupper($standardValue->code), ['NS']))
                    return '-';
                if (in_array(strtoupper($standardValue->code), ['NIL', 'ND']))
                    return 'PASS';
            }
            if (strtoupper($result) == 'ABSENT' && isset($standardValue->code) && strtoupper($standardValue->code) == 'ABSENT') {
                return 'PASS';
            }
            if (strtoupper($result) == 'PRESENT' && isset($standardValue->code) && strtoupper($standardValue->code) == 'ABSENT') {
                return 'FAIL';
            }
            return '-';
        }

        // Numeric result with standard value
        $resultValue = floatval($result);
        $standardIsValue = floatval($analyteGuide->standard_is_value);

        if ($analyteGuide->standard_is_value == '' || $analyteGuide->standard_is_value == null) {
            if (isset($standardValue->code) && strtoupper($standardValue->code) == 'NS') {
                return '-';
            }
            return '-';
        }

        // Apply reporting symbol logic
        $valueType = $analyteGuide->value_type; // Max, Min, less_than, greater_than

        if (trim($reportingSymbol) == '>') {
            if ($valueType == 'Max' || !$valueType) {
                return $resultValue < $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'Min') {
                return $resultValue >= $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'less_than') {
                return $resultValue < $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'greater_than') {
                return $resultValue > $standardIsValue ? 'PASS' : 'FAIL';
            }
        } elseif (trim($reportingSymbol) == '<') {
            if ($valueType == 'Max' || !$valueType) {
                return $resultValue <= $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'Min') {
                return $resultValue > $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'less_than') {
                return $resultValue < $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'greater_than') {
                return $resultValue > $standardIsValue ? 'PASS' : 'FAIL';
            }
        } else {
            // No reporting symbol
            if ($valueType == 'Max' || !$valueType) {
                return $resultValue <= $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'Min') {
                return $resultValue >= $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'less_than') {
                return $resultValue < $standardIsValue ? 'PASS' : 'FAIL';
            }
            if ($valueType == 'greater_than') {
                return $resultValue > $standardIsValue ? 'PASS' : 'FAIL';
            }
        }

        return '-';
    }

    public function closePostResultsModal(): void
    {
        $this->showPostResultsModal = false;
        $this->postingInProgress = false;
        $this->currentStep = 0;
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
