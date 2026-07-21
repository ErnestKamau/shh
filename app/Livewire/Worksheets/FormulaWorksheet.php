<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\FormulaStepTableColumn;
use App\Models\Formulars\SampleFormulaStepTableCellValue;
use App\Models\Formulars\SampleFormulaStepTableInstance;
use App\Models\Formulars\SampleFormulaStepTableRow;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\Services\Formulars\FormulaStepCheckboxOptionsResolver;
use App\Services\Formulars\FormulaStepTableRowGeneratorService;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use App\Services\Worksheets\WorksheetMetaResolver;
use App\SampleAnalysisDates;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FormulaWorksheet extends Component
{
    protected $listeners = [
        'triggerFormulaPostResults' => 'handleTriggerPostResults',
        'closeAllDropdowns' => 'closeAllDropdowns'
    ];

    public SampleHeader $batch;
    public Formula $formula;
    public $capturedResults = [];
    public $worksheetData = [];
    public $formulaSteps = [];
    public $mandatoryFields = [];

    public $sharedMandatoryData = [];
    public string $viewMode = 'form';

    /**
     * Worksheet-level metadata shared across all samples.
     *
     * @var array{date: string, lab_no: string|null, time_in: string|null, done_by_user_id: string|null, time_out: string|null, read_by_user_id: string|null, read_date: string|null}
     */
    public array $sharedWorksheetMeta = [];

    /**
     * Shared input/dataset step values (step_id => value) entered once for the whole worksheet run.
     *
     * @var array<string, string>
     */
    public array $sharedInputStepValues = [];

    /**
     * Shared derived/lookup step values (step_id => value) — computed from sharedInputStepValues.
     *
     * @var array<string, string>
     */
    public array $sharedDerivedStepValues = [];

    /** @var array<string, list<string>> */
    public array $sharedCheckboxStepData = [];

    /**
     * @var array<string, array{wells: array<string, array{kind: string, value: string}>}>
     */
    public array $sharedPcrPlateMapData = [];

    public ?string $activePcrStepId = null;

    public ?string $activePcrWell = null;

    public string $pcrPopoverKind = 'sample';

    public string $pcrPopoverValue = '';

    public string $pcrPopoverCustomSample = '';

    public bool $pcrPopoverUseCustomSample = false;

    /** @var array<string, \Illuminate\Support\Collection<int, FormulaStepTableColumn>> */
    public array $formulaStepTableColumnsByStep = [];

    /** @var array<string, list<array{row: SampleFormulaStepTableRow, sample_detail_id: ?string}>> */
    public array $formulaStepTableRowsByStep = [];

    /** @var array<string, array<string, array<string, mixed>>> */
    public array $formulaStepTableData = [];

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

    /** True when the user has no lab section assignment (view-all, edit-none). */
    public bool $worksheetsReadOnly = false;

    /** True when rows were filtered to the user's assigned lab section(s). */
    public bool $worksheetsSectionFiltered = false;

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

    // Post results UI (analysis dates + preview rows)
    public ?string $startAnalysisDate = null;
    public ?string $endAnalysisDate = null;
    public array $postResultsPreviewRows = [];

    // Lookup modal context (for better UX when none compatible)
    public ?array $currentLookupTableInfo = null;

    /** When set, load all batch captured results assigned to this grouped pipeline (not formular_id). */
    public ?string $groupedWorksheetHolderId = null;

    public function mount(SampleHeader $batch, Formula $formula, ?string $groupedWorksheetHolderId = null): void
    {
        $this->batch = $batch;
        $this->formula = $formula;
        $this->groupedWorksheetHolderId = $groupedWorksheetHolderId !== null && $groupedWorksheetHolderId !== ''
            ? $groupedWorksheetHolderId
            : null;
        $this->loadDatasets();
        $this->loadData();
    }

    public function loadData(): void
    {
        $access = app(LabSectionResultAccess::class);
        $user = Auth::user();
        $this->worksheetsReadOnly = ! $access->hasLabSectionAssignment($user);
        $this->worksheetsSectionFiltered = $access->hasLabSectionAssignment($user);

        $this->capturedResults = $this->loadCapturedResultsForWorksheet();

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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CapturedResult>
     */
    protected function loadCapturedResultsForWorksheet(): \Illuminate\Database\Eloquent\Collection
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $this->batch->id)
            ->with(['sample.sample_point', 'analysisElement.analyte']);

        if ($this->groupedWorksheetHolderId) {
            $query
                ->where('grouped_worksheet_holder_id', $this->groupedWorksheetHolderId)
                ->where('has_grouped_worksheet', true);
        } else {
            $query->where('formular_id', $this->formula->id);
        }

        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());

        return $query
            ->orderBy('analysis_type_order')
            ->orderBy('parameters_order')
            ->orderBy('sample_detail_code')
            ->get();
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
                    'lab_no' => $existing->lab_no ?? $this->batch->batch_code,
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
                    'lab_no' => $this->batch->batch_code,
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
            $existing = SampleCapturedWorksheetFormula::where('captured_result_id', $firstCaptured->id)
                ->with('stepData')
                ->first();
            if ($existing && $existing->mandatoryData) {
                /** @var \Illuminate\Database\Eloquent\Collection $mandatoryData */
                $mandatoryData = $existing->mandatoryData;
                if ($mandatoryData instanceof \Illuminate\Database\Eloquent\Collection) {
                    $this->sharedMandatoryData = $mandatoryData->pluck('field_value', 'formula_mandatory_field_id')->toArray();
                }
            }

            if ($existing && $existing->stepData) {
                $this->sharedCheckboxStepData = [];
                foreach ($this->formulaSteps->where('step_type', 'checkbox') as $checkboxStep) {
                    $raw = $existing->stepData->firstWhere('formula_step_id', $checkboxStep->id)?->step_value;
                    if ($raw !== null && $raw !== '') {
                        $decoded = json_decode((string) $raw, true);
                        $this->sharedCheckboxStepData[$checkboxStep->id] = is_array($decoded)
                            ? array_values(array_map('strval', $decoded))
                            : [];
                    } else {
                        $this->sharedCheckboxStepData[$checkboxStep->id] = [];
                    }
                }

                $this->sharedPcrPlateMapData = [];
                foreach ($this->formulaSteps->where('step_type', 'pcr_plate_map') as $pcrStep) {
                    $raw = $existing->stepData->firstWhere('formula_step_id', $pcrStep->id)?->step_value;
                    $wells = [];
                    if ($raw !== null && $raw !== '') {
                        $decoded = json_decode((string) $raw, true);
                        if (is_array($decoded) && isset($decoded['wells']) && is_array($decoded['wells'])) {
                            $wells = $decoded['wells'];
                        }
                    }
                    $wells = $this->mergePcrPlateWellsWithPreset($pcrStep, $wells);
                    $this->sharedPcrPlateMapData[$pcrStep->id] = ['wells' => $wells];
                }
            }

            $this->initializeSharedPcrPlateMapData();
        }

        $this->loadFormulaStepTableCapture();

        // Load shared worksheet-level state from the first captured result
        $this->bootstrapSharedWorksheetState();

        // Pre-populate steps and mandatory values for all captured results to prevent Livewire binding errors in tabular mode
        foreach ($this->capturedResults as $captured) {
            $crId = (string) $captured->id;
            if (! isset($this->worksheetData[$crId]['steps'])) {
                $this->worksheetData[$crId]['steps'] = [];
            }
            if (! isset($this->worksheetData[$crId]['mandatory'])) {
                $this->worksheetData[$crId]['mandatory'] = [];
            }

            foreach ($this->formulaSteps as $step) {
                if (! isset($this->worksheetData[$crId]['steps'][(string) $step->id])) {
                    $this->worksheetData[$crId]['steps'][(string) $step->id] = $this->sharedInputStepValues[(string) $step->id] ?? '';
                }
            }

            foreach ($this->mandatoryFields as $field) {
                if (! isset($this->worksheetData[$crId]['mandatory'][(string) $field->id])) {
                    $this->worksheetData[$crId]['mandatory'][(string) $field->id] = $this->sharedMandatoryData[(string) $field->id] ?? '';
                }
            }
        }
    }

    /**
     * Populate sharedWorksheetMeta and sharedInputStepValues from the first captured result's
     * saved worksheet (or sensible defaults).
     */
    protected function bootstrapSharedWorksheetState(): void
    {
        $first = $this->capturedResults->first();
        $meta = $first ? ($this->worksheetData[$first->id] ?? []) : [];

        $this->sharedWorksheetMeta = [
            'date'             => $meta['date']             ?? now()->format('Y-m-d'),
            'lab_no'           => $meta['lab_no']           ?? $this->batch->batch_code,
            'time_in'          => $meta['time_in']          ?? now()->format('H:i'),
            'done_by_user_id'  => $meta['done_by_user_id']  ?? (string) (Auth::id() ?? ''),
            'time_out'         => $meta['time_out']         ?? null,
            'read_by_user_id'  => $meta['read_by_user_id']  ?? (string) (Auth::id() ?? ''),
            'read_date'        => $meta['read_date']        ?? now()->format('Y-m-d'),
        ];

        $sharedTypes = ['input', 'dataset'];
        foreach ($this->formulaSteps->whereIn('step_type', $sharedTypes) as $step) {
            $this->sharedInputStepValues[(string) $step->id] =
                (string) ($meta['steps'][(string) $step->id] ?? '');
        }

        $this->recalculateSharedDerivedValues();
    }

    /**
     * Run the formula evaluator with sharedInputStepValues + sharedCheckboxStepData and store
     * derived/lookup values into sharedDerivedStepValues.
     * Per-sample final results are still stored per worksheetData entry.
     */
    protected function recalculateSharedDerivedValues(): void
    {
        $inputs = $this->buildSharedEvaluatorInputs();

        if ($inputs === []) {
            return;
        }

        try {
            $evaluator = app(\App\Services\Formulars\FormulaEvaluator::class);
            $firstCr = $this->capturedResults->first();
            $result = $evaluator->execute(
                $this->formula->activeVersion,
                $inputs,
                $firstCr?->sample_detail_id,
                $this->batch->id,
                [],
            );

            foreach ($this->formulaSteps as $step) {
                if (in_array($step->step_type, ['derived', 'lookup'], true)) {
                    $this->sharedDerivedStepValues[(string) $step->id] =
                        (string) ($result['variables'][$step->variable_name] ?? '');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Shared derived recalculation error: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSharedEvaluatorInputs(): array
    {
        $inputs = [];

        foreach ($this->formulaSteps->whereIn('step_type', ['input', 'dataset']) as $step) {
            $val = $this->sharedInputStepValues[(string) $step->id] ?? '';
            if ($val !== '') {
                $inputs[$step->variable_name] = $val;
            }
        }

        foreach ($this->formulaSteps->where('step_type', 'checkbox') as $step) {
            $selected = $this->sharedCheckboxStepData[(string) $step->id] ?? [];
            $inputs[$step->variable_name] = $this->checkboxValueForEvaluator($step, $selected);
        }

        return $inputs;
    }

    /**
     * @param  list<string>  $selected
     */
    protected function checkboxValueForEvaluator(FormulaStep $step, array $selected): string
    {
        if ($selected === []) {
            return '';
        }

        $stored = (string) $selected[0];
        $options = app(FormulaStepCheckboxOptionsResolver::class)->optionsForStep($step);
        $match = $options->first(fn ($option) => (string) $option->id === $stored);

        return $match ? (string) $match->label : $stored;
    }

    /**
     * Sync sharedWorksheetMeta + sharedInputStepValues into each captured result's worksheetData
     * entry, recalculate per-sample final results, then persist everything.
     */
    public function saveWorksheetLevel(): void
    {
        try {
            $access = app(LabSectionResultAccess::class);
            if (! $access->hasLabSectionAssignment(Auth::user())) {
                $this->setMessage($access->denyEditMessage(Auth::user()), 'error');
                return;
            }

            DB::beginTransaction();

            foreach ($this->capturedResults as $captured) {
                $crId = (string) $captured->id;

                // Merge shared meta
                if (! isset($this->worksheetData[$crId])) {
                    $this->worksheetData[$crId] = [
                        'id' => null, 'steps' => [], 'mandatory' => [], 'lookup_overrides' => [],
                        'final_result' => '',
                    ];
                }

                $this->worksheetData[$crId]['date']            = $this->sharedWorksheetMeta['date'];
                $this->worksheetData[$crId]['lab_no']          = $this->sharedWorksheetMeta['lab_no'] ?: $this->batch->batch_code;
                $this->worksheetData[$crId]['time_in']         = $this->sharedWorksheetMeta['time_in'];
                $this->worksheetData[$crId]['done_by_user_id'] = $this->sharedWorksheetMeta['done_by_user_id'];
                $this->worksheetData[$crId]['time_out']        = $this->sharedWorksheetMeta['time_out'];
                $this->worksheetData[$crId]['read_by_user_id'] = $this->sharedWorksheetMeta['read_by_user_id'];
                $this->worksheetData[$crId]['read_date']       = $this->sharedWorksheetMeta['read_date'];

                // Merge shared input/dataset steps
                foreach ($this->sharedInputStepValues as $stepId => $value) {
                    $this->worksheetData[$crId]['steps'][$stepId] = $value;
                }

                // Merge shared derived/lookup results
                foreach ($this->sharedDerivedStepValues as $stepId => $value) {
                    $this->worksheetData[$crId]['steps'][$stepId] = $value;
                }

                // Run per-sample calculation for final result
                $this->calculateFormulaResult($crId);

                $this->saveWorksheet($crId);
            }

            DB::commit();
            $this->setMessage('Worksheet saved for all samples.', 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('saveWorksheetLevel error: ' . $e->getMessage());
            $this->setMessage('Save failed: ' . $e->getMessage(), 'error');
        }
    }

    public function saveWorksheet(string $capturedResultId): void
    {
        try {
            DB::beginTransaction();

            $data = $this->worksheetData[$capturedResultId] ?? null;
            if (!$data) {
                throw new \Exception('Worksheet data not found');
            }

            $captured = CapturedResult::with(['sample.sample_point', 'analysisElement.analyte'])
                ->findOrFail($capturedResultId);

            $access = app(LabSectionResultAccess::class);
            if (! $access->canEditCapturedResult(Auth::user(), $captured)) {
                throw new \Exception($access->denyEditMessage(Auth::user()));
            }

            // Create or update worksheet
            $worksheet = SampleCapturedWorksheetFormula::updateOrCreate(
                ['captured_result_id' => $capturedResultId],
                array_merge($this->defaultWorksheetAttributes($captured), [
                    'date' => $data['date'],
                    'lab_no' => $data['lab_no'] ?: $this->batch->batch_code,
                    'time_in' => $data['time_in'],
                    'done_by_user_id' => $data['done_by_user_id'],
                    'time_out' => $data['time_out'] ?: null,
                    'read_by_user_id' => $data['read_by_user_id'] ?: null,
                    'read_date' => $data['read_date'] ?: null,
                    'final_result' => $data['final_result'] ?: null,
                ])
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

            // Save mandatory data
            $mandatoryValues = (isset($data['mandatory']) && !empty($data['mandatory'])) 
                ? $data['mandatory'] 
                : $this->sharedMandatoryData;
            foreach ($mandatoryValues as $fieldId => $value) {
                $worksheet->mandatoryData()->updateOrCreate(
                    ['formula_mandatory_field_id' => $fieldId],
                    ['field_value' => $value]
                );
            }

            foreach ($this->sharedCheckboxStepData as $stepId => $selected) {
                $worksheet->stepData()->updateOrCreate(
                    ['formula_step_id' => $stepId],
                    ['step_value' => json_encode(is_array($selected) ? array_values($selected) : []) ?: '[]']
                );
            }

            // Update captured result with final result and operator
            if ($data['final_result']) {
                $captured->result = $data['final_result'];

                $captured->assignAnalyst($data['done_by_user_id'] ?? Auth::id());

                if ($captured->analysisElement) {
                    $captured->applyAnalysisElementDefaults();
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
                    $decodedDates = $analysis_date->analysis_dates ? json_decode($analysis_date->analysis_dates, true) : [];
                    $prev_dates = [];
                    if (is_array($decodedDates)) {
                        foreach ($decodedDates as $sectionId => $sectionValue) {
                            if (is_array($sectionValue)) {
                                $prev_dates[$sectionId] = [
                                    'start_date' => $sectionValue['start_date'] ?? $sectionValue['start'] ?? null,
                                    'end_date' => $sectionValue['end_date'] ?? $sectionValue['end'] ?? null,
                                ];
                                continue;
                            }

                            $prev_dates[$sectionId] = [
                                'start_date' => $sectionValue,
                                'end_date' => null,
                            ];
                        }
                    }

                    if ($captured->lab_section_id) {
                        $sectionId = (string) $captured->lab_section_id;
                        $prev_dates[$sectionId] = [
                            'start_date' => $currentDate,
                            'end_date' => $prev_dates[$sectionId]['end_date'] ?? null,
                        ];
                    }

                    // Calculate earliest date across all lab sections
                    $start_date = '';
                    foreach ($prev_dates as $key => $val) {
                        $sectionStartDate = is_array($val) ? ($val['start_date'] ?? '') : (string) $val;
                        if ($sectionStartDate === '') {
                            continue;
                        }

                        if ($start_date == '') {
                            $start_date = $sectionStartDate;
                        } else {
                            $start_date = $sectionStartDate < $start_date ? $sectionStartDate : $start_date;
                        }
                    }
                    $analysis_date->start_analysis_date = $start_date;
                    $analysis_date->analysis_dates = json_encode($prev_dates);
                } else {
                    // Create new
                    $prev_dates = [];
                    if ($captured->lab_section_id) {
                        $prev_dates[(string) $captured->lab_section_id] = [
                            'start_date' => $currentDate,
                            'end_date' => null,
                        ];
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
    public function updated(string $propertyName, mixed $value = null): void
    {
        Log::info("Livewire updated() called for property: {$propertyName}");

        // Persist custom table cell changes to the database.
        // Property path: formulaStepTableData.{stepId}.{rowId}.{colKey}
        if (str_starts_with($propertyName, 'formulaStepTableData.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) === 4) {
                [, $stepId, $rowId, $columnKey] = $parts;
                $cellValue = $this->formulaStepTableData[$stepId][$rowId][$columnKey] ?? $value;
                $this->persistFormulaStepTableCell($stepId, $rowId, $columnKey, $cellValue);
            }
        }

        // Shared worksheet-level inputs changed → recalculate derived values immediately
        if (str_starts_with($propertyName, 'sharedInputStepValues.')) {
            $this->recalculateSharedDerivedValues();
        }

        // Check if it's a step input field
        if (strpos($propertyName, 'worksheetData.') === 0 && strpos($propertyName, '.steps.') !== false) {
            preg_match('/worksheetData\\.([\\w-]+)\\.steps/', $propertyName, $matches);
            if (isset($matches[1])) {
                $capturedResultId = (string) $matches[1];
                Log::info("Triggering calculation for captured result ID: {$capturedResultId}");
                $this->calculateFormulaResult($capturedResultId);
            }
        }

        // Check if lookup table override changed
        if (strpos($propertyName, 'worksheetData.') === 0 && strpos($propertyName, '.lookup_overrides.') !== false) {
            preg_match('/worksheetData\\.([\\w-]+)\\.lookup_overrides/', $propertyName, $matches);
            if (isset($matches[1])) {
                $capturedResultId = (string) $matches[1];
                Log::info("Lookup override changed for captured result ID: {$capturedResultId}");
                $this->calculateFormulaResult($capturedResultId);
            }
        }
    }

    // Enhanced method for real-time calculation
    protected function calculateFormulaResult(string $capturedResultId): void
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
                $value = $stepData[$step->id] ?? $this->sharedInputStepValues[(string) $step->id] ?? null;

                if ($value === '' || $value === null) {
                    $allInputsFilled = false;
                } else {
                    $filledInputs[$step->variable_name] = $value;
                }
            }

            foreach ($this->formulaSteps->where('step_type', 'checkbox') as $step) {
                if ($this->viewMode === 'table') {
                    $selected = $data['steps'][(string) $step->id] ?? [];
                    if (is_string($selected)) {
                        $selected = json_decode($selected, true) ?: [];
                    }
                } else {
                    $selected = $this->sharedCheckboxStepData[(string) $step->id] ?? [];
                }
                $filledInputs[$step->variable_name] = $this->checkboxValueForEvaluator($step, $selected);
            }

            // Log calculation attempt
            Log::info("Calculating formula for captured result {$capturedResultId}. All inputs filled: " . ($allInputsFilled ? 'Yes' : 'No') . ". Filled inputs: " . json_encode($filledInputs));

            if (!$allInputsFilled) {
                Log::info("Not all input steps have values, skipping calculation for captured result ID: {$capturedResultId}");
                return;
            }

            // Get lookup overrides for this worksheet row
            $lookupOverrides = $data['lookup_overrides'] ?? [];

            $capturedResult = $this->capturedResults->firstWhere('id', $capturedResultId);
            $sampleId = $capturedResult?->sample_detail_id;

            $evaluator = app(\App\Services\Formulars\FormulaEvaluator::class);
            $result = $evaluator->execute($this->formula->activeVersion, $filledInputs, $sampleId, $this->batch->id, $lookupOverrides);

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

        } catch (\Exception $e) {
            Log::error('Formula calculation error for captured result ' . $capturedResultId . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    // New method for auto-save on field blur
    public function autoSaveRow(string $capturedResultId): void
    {
        try {
            $this->saveWorksheet($capturedResultId);
            $this->skipRender();
        } catch (\Exception $e) {
            Log::error('Auto-save error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-save mandatory field when user leaves the field
     */
    public function autoSaveMandatoryField(string $fieldId): void
    {
        try {
            $this->saveMandatoryFields();
            $this->skipRender();
        } catch (\Exception $e) {
            Log::error('Auto-save mandatory field error: ' . $e->getMessage());
        }
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

    public function updatedSharedCheckboxStepData(): void
    {
        try {
            $this->saveSharedCheckboxSteps();
            $this->skipRender();
        } catch (\Exception $e) {
            Log::error('Auto-save checkbox step error: ' . $e->getMessage());
        }
    }

    public function toggleSharedCheckboxOption(string $stepId, string $optionId): void
    {
        $current = $this->sharedCheckboxStepData[$stepId] ?? [];
        if (in_array($optionId, $current, true)) {
            $current = array_values(array_filter($current, fn ($v) => $v !== $optionId));
        } else {
            $current[] = $optionId;
        }
        $this->sharedCheckboxStepData[$stepId] = $current;
        $this->saveSharedCheckboxSteps();
        $this->recalculateSharedDerivedValues();
    }

    protected function resolveCapturedResult(string $capturedResultId): CapturedResult
    {
        $captured = collect($this->capturedResults)->firstWhere('id', $capturedResultId);

        if ($captured instanceof CapturedResult) {
            return $captured;
        }

        return CapturedResult::with(['sample.sample_point', 'analysisElement.analyte'])
            ->findOrFail($capturedResultId);
    }

    protected function sampleDetailsLabel(CapturedResult $captured): string
    {
        $captured->loadMissing(['sample.sample_point', 'analysisElement.analyte']);

        return $captured->sample->sample_code
            .' - '
            .($captured->analysisElement->analyte->name ?? '')
            .' - '
            .($captured->sample->sample_point->name ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultWorksheetAttributes(CapturedResult $captured): array
    {
        return [
            'sample_header_id' => $this->batch->id,
            'sample_detail_id' => $captured->sample_detail_id,
            'formular_id' => $this->formula->id,
            'lab_no' => $this->sharedWorksheetMeta['lab_no'] ?? $this->batch->batch_code,
            'date' => $this->sharedWorksheetMeta['date'] ?? now()->format('Y-m-d'),
            'sample_details' => $this->sampleDetailsLabel($captured),
            'done_by_user_id' => $this->sharedWorksheetMeta['done_by_user_id'] ?? Auth::id(),
        ];
    }

    protected function firstOrCreateWorksheetForCaptured(CapturedResult $captured): SampleCapturedWorksheetFormula
    {
        return SampleCapturedWorksheetFormula::firstOrCreate(
            ['captured_result_id' => $captured->id],
            $this->defaultWorksheetAttributes($captured)
        );
    }

    protected function saveSharedCheckboxSteps(): void
    {
        foreach ($this->capturedResults as $captured) {
            $worksheet = $this->firstOrCreateWorksheetForCaptured($captured);

            foreach ($this->sharedCheckboxStepData as $stepId => $selected) {
                $worksheet->stepData()->updateOrCreate(
                    ['formula_step_id' => $stepId],
                    ['step_value' => json_encode(is_array($selected) ? array_values($selected) : []) ?: '[]']
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getBatchSampleCodesProperty(): array
    {
        return array_map(
            fn (array $option): string => $option['code'],
            $this->batchSamplePcrOptions,
        );
    }

    /**
     * Batch samples available for PCR well assignment.
     *
     * @return list<array{code: string, sample_id_file: ?string, label: string}>
     */
    public function getBatchSamplePcrOptionsProperty(): array
    {
        $options = [];
        $seen = [];

        foreach ($this->capturedResults as $captured) {
            $sample = $captured->sample;
            if (! $sample) {
                continue;
            }

            $code = trim((string) ($sample->sample_code ?? ''));
            if ($code === '' || isset($seen[$code])) {
                continue;
            }

            $seen[$code] = true;
            $sampleIdFile = trim((string) ($sample->sample_id_file ?? ''));
            $label = $sampleIdFile !== '' ? "{$code} — {$sampleIdFile}" : $code;

            $options[] = [
                'code' => $code,
                'sample_id_file' => $sampleIdFile !== '' ? $sampleIdFile : null,
                'label' => $label,
            ];
        }

        return $options;
    }

    protected function pcrWellValueForSampleCode(string $sampleCode): string
    {
        foreach ($this->batchSamplePcrOptions as $option) {
            if ($option['code'] === $sampleCode) {
                return $option['sample_id_file'] ?? $sampleCode;
            }
        }

        return $sampleCode;
    }

    protected function sampleCodeForPcrWellValue(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach ($this->batchSamplePcrOptions as $option) {
            if ($option['sample_id_file'] === $value || $option['code'] === $value) {
                return $option['code'];
            }
        }

        return null;
    }

    public function getSelectedPcrSampleFileIdProperty(): ?string
    {
        if ($this->pcrPopoverKind !== 'sample' || $this->pcrPopoverUseCustomSample || trim($this->pcrPopoverValue) === '') {
            return null;
        }

        $fileId = $this->pcrWellValueForSampleCode(trim($this->pcrPopoverValue));

        return $fileId !== trim($this->pcrPopoverValue) ? $fileId : null;
    }

    /**
     * @return array<string, array{kind: string, value: string}>
     */
    public function pcrWellsForStep(string $stepId): array
    {
        return $this->sharedPcrPlateMapData[$stepId]['wells'] ?? [];
    }

    /**
     * @param  array<string, array{kind: string, value: string}>  $wells
     * @return array<string, array{kind: string, value: string}>
     */
    protected function mergePcrPlateWellsWithPreset(FormulaStep $pcrStep, array $wells): array
    {
        $preset = $pcrStep->pcrPlateConfig()['preset_wells'] ?? [];
        if (! is_array($preset) || $preset === []) {
            return $wells;
        }

        $merged = $preset;
        foreach ($wells as $well => $assignment) {
            if (is_array($assignment)) {
                $merged[$well] = $assignment;
            }
        }

        return $merged;
    }

    protected function initializeSharedPcrPlateMapData(): void
    {
        foreach ($this->formulaSteps->where('step_type', 'pcr_plate_map') as $pcrStep) {
            if (isset($this->sharedPcrPlateMapData[$pcrStep->id])) {
                continue;
            }

            $preset = $pcrStep->pcrPlateConfig()['preset_wells'] ?? [];
            $this->sharedPcrPlateMapData[$pcrStep->id] = [
                'wells' => is_array($preset) ? $preset : [],
            ];
        }
    }

    public function openPcrWellEditor(string $stepId, string $well): void
    {
        $this->activePcrStepId = $stepId;
        $this->activePcrWell = $well;
        $assignment = $this->pcrWellsForStep($stepId)[$well] ?? null;

        if (is_array($assignment)) {
            $this->pcrPopoverKind = (string) ($assignment['kind'] ?? 'sample');
            $storedValue = (string) ($assignment['value'] ?? '');

            if ($this->pcrPopoverKind === 'sample') {
                $matchedCode = $this->sampleCodeForPcrWellValue($storedValue);
                if ($matchedCode !== null && in_array($matchedCode, $this->batchSampleCodes, true)) {
                    $this->pcrPopoverValue = $matchedCode;
                    $this->pcrPopoverUseCustomSample = false;
                    $this->pcrPopoverCustomSample = '';
                } else {
                    $this->pcrPopoverValue = '';
                    $this->pcrPopoverUseCustomSample = true;
                    $this->pcrPopoverCustomSample = $storedValue;
                }
            } else {
                $this->pcrPopoverValue = $storedValue;
                $this->pcrPopoverUseCustomSample = false;
                $this->pcrPopoverCustomSample = '';
            }
        } else {
            $this->pcrPopoverKind = 'sample';
            $this->pcrPopoverValue = '';
            $this->pcrPopoverCustomSample = '';
            $this->pcrPopoverUseCustomSample = false;
        }
    }

    public function closePcrWellEditor(): void
    {
        $this->activePcrStepId = null;
        $this->activePcrWell = null;
    }

    public function setPcrPopoverKind(string $kind): void
    {
        if (! in_array($kind, ['sample', 'std', 'control', 'buffer'], true)) {
            return;
        }
        $this->pcrPopoverKind = $kind;
        $this->pcrPopoverValue = '';
        $this->pcrPopoverCustomSample = '';
        $this->pcrPopoverUseCustomSample = false;
    }

    public function applyPcrWellEditor(): void
    {
        if ($this->activePcrStepId === null || $this->activePcrWell === null) {
            return;
        }

        $value = $this->pcrPopoverKind === 'sample' && $this->pcrPopoverUseCustomSample
            ? trim($this->pcrPopoverCustomSample)
            : trim($this->pcrPopoverValue);

        if ($value === '') {
            $this->setMessage('Enter or select a value for this well.', 'error');

            return;
        }

        if ($this->pcrPopoverKind === 'sample' && ! $this->pcrPopoverUseCustomSample) {
            $value = $this->pcrWellValueForSampleCode($value);
        }

        if (! isset($this->sharedPcrPlateMapData[$this->activePcrStepId])) {
            $this->sharedPcrPlateMapData[$this->activePcrStepId] = ['wells' => []];
        }

        $well = $this->activePcrWell;

        $this->sharedPcrPlateMapData[$this->activePcrStepId]['wells'][$well] = [
            'kind' => $this->pcrPopoverKind,
            'value' => $value,
        ];

        $this->saveSharedPcrPlateMapSteps();
        $this->closePcrWellEditor();
        $this->setMessage('Well '.$well.' updated.', 'success');
    }

    public function clearActivePcrWell(): void
    {
        if ($this->activePcrStepId === null || $this->activePcrWell === null) {
            return;
        }

        if (isset($this->sharedPcrPlateMapData[$this->activePcrStepId]['wells'][$this->activePcrWell])) {
            unset($this->sharedPcrPlateMapData[$this->activePcrStepId]['wells'][$this->activePcrWell]);
            $this->saveSharedPcrPlateMapSteps();
        }

        $well = $this->activePcrWell;
        $this->closePcrWellEditor();
        $this->setMessage('Well '.$well.' cleared.', 'success');
    }

    public function quickAssignPcrWell(string $value): void
    {
        $this->pcrPopoverValue = $value;
        $this->pcrPopoverUseCustomSample = false;
        $this->applyPcrWellEditor();
    }

    protected function saveSharedPcrPlateMapSteps(): void
    {
        foreach ($this->capturedResults as $captured) {
            $worksheet = $this->firstOrCreateWorksheetForCaptured($captured);

            foreach ($this->sharedPcrPlateMapData as $stepId => $data) {
                $wells = $data['wells'] ?? [];
                $worksheet->stepData()->updateOrCreate(
                    ['formula_step_id' => $stepId],
                    ['step_value' => json_encode(['wells' => $wells]) ?: '{"wells":[]}']
                );
            }
        }
    }

    protected function loadFormulaStepTableCapture(): void
    {
        $this->formulaStepTableData = [];
        $this->formulaStepTableColumnsByStep = [];
        $this->formulaStepTableRowsByStep = [];

        $tableSteps = $this->formulaSteps->where('step_type', 'custom_table');
        if ($tableSteps->isEmpty() || $this->capturedResults->isEmpty()) {
            return;
        }

        $service = app(FormulaStepTableRowGeneratorService::class);
        $worksheet = $this->sharedFormulaTableWorksheet();

        foreach ($tableSteps as $step) {
            if (! isset($this->formulaStepTableColumnsByStep[$step->id])) {
                $this->formulaStepTableColumnsByStep[$step->id] = FormulaStepTableColumn::where('formula_step_id', $step->id)
                    ->orderBy('order')
                    ->get();
            }

            $instance = $service->firstOrCreateInstance($worksheet, $step);
            $service->syncRowsForFormulaWorksheet($instance, $this->batch, $step, $this->capturedResults);

            $columns = $this->formulaStepTableColumnsByStep[$step->id] ?? collect();
            $columnsById = $columns->keyBy('id');

            $rows = SampleFormulaStepTableRow::where('instance_id', $instance->id)
                ->orderBy('row_index')
                ->get();

            $rowEntries = [];
            foreach ($rows as $row) {
                $cells = [];
                $values = SampleFormulaStepTableCellValue::where('row_id', $row->id)->get();
                foreach ($values as $val) {
                    $col = $columnsById->get($val->column_id);
                    if ($col) {
                        $cells[$col->key] = $this->normalizeFormulaStepTableCellForDisplay($col, $val->value);
                    }
                }

                if (! isset($this->formulaStepTableData[$step->id])) {
                    $this->formulaStepTableData[$step->id] = [];
                }
                $this->formulaStepTableData[$step->id][$row->id] = $cells;

                $rowEntries[] = [
                    'row' => $row,
                    'sample_detail_id' => $this->sampleDetailIdForTableRow($row),
                ];
            }

            $this->formulaStepTableRowsByStep[$step->id] = $rowEntries;
        }
    }

    protected function sharedFormulaTableWorksheet(): SampleCapturedWorksheetFormula
    {
        $captured = $this->capturedResults->first();
        if (! $captured instanceof CapturedResult) {
            throw new \RuntimeException('No captured results available for formula table worksheet.');
        }

        return $this->firstOrCreateWorksheetForCaptured($captured);
    }

    protected function sampleDetailIdForTableRow(SampleFormulaStepTableRow $row): ?string
    {
        if ($row->driver_type === SampleDetails::class && $row->driver_id) {
            return (string) $row->driver_id;
        }

        if ($row->driver_type === CapturedResult::class && $row->driver_id) {
            return CapturedResult::query()
                ->whereKey($row->driver_id)
                ->value('sample_detail_id');
        }

        return null;
    }

    public function persistFormulaStepTableCell(
        string $stepId,
        string $rowId,
        string $columnKey,
        mixed $value,
    ): void {
        $step = FormulaStep::find($stepId);
        if (! $step) {
            return;
        }

        $column = FormulaStepTableColumn::where('formula_step_id', $stepId)->where('key', $columnKey)->first();
        if (! $column) {
            return;
        }

        $stored = is_array($value) ? json_encode($value) : (string) ($value ?? '');

        SampleFormulaStepTableCellValue::updateOrCreate(
            [
                'row_id' => $rowId,
                'column_id' => $column->id,
            ],
            ['value' => $stored],
        );

        if (! isset($this->formulaStepTableData[$stepId][$rowId])) {
            $this->formulaStepTableData[$stepId][$rowId] = [];
        }
        $this->formulaStepTableData[$stepId][$rowId][$columnKey] = $value;
    }

    public function syncFormulaStepTableRows(string $stepId): void
    {
        $step = FormulaStep::find($stepId);
        if (! $step || ! $step->isCustomTable()) {
            return;
        }

        $worksheet = $this->sharedFormulaTableWorksheet();
        $service = app(FormulaStepTableRowGeneratorService::class);
        $instance = $service->firstOrCreateInstance($worksheet, $step);
        $service->syncRowsForFormulaWorksheet($instance, $this->batch, $step, $this->capturedResults);
        $this->loadFormulaStepTableCapture();
    }

    public function addFormulaStepTableManualRow(string $stepId): void
    {
        $step = FormulaStep::find($stepId);
        if (! $step || ! $step->allow_manual_rows) {
            return;
        }

        $worksheet = $this->sharedFormulaTableWorksheet();
        $instance = app(FormulaStepTableRowGeneratorService::class)->firstOrCreateInstance($worksheet, $step);
        app(FormulaStepTableRowGeneratorService::class)->addManualRow($instance, $step);
        $this->loadFormulaStepTableCapture();
    }

    protected function normalizeFormulaStepTableCellForDisplay(FormulaStepTableColumn $column, mixed $value): mixed
    {
        $config = is_array($column->dataset_config) ? $column->dataset_config : [];
        if (in_array($config['choice_control'] ?? '', ['checkbox'], true) || $column->input_data_type === 'boolean') {
            if (is_string($value) && $value !== '' && $value[0] === '[') {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : $value;
            }
        }

        return $value ?? '';
    }

    public function checkboxOptionsForStep(FormulaStep $step): \Illuminate\Support\Collection
    {
        return app(FormulaStepCheckboxOptionsResolver::class)->optionsForStep($step);
    }

    public function resolveCustomTableDatasetValue(
        FormulaStepTableColumn $column,
        CapturedResult $captured,
    ): string {
        return $this->resolveCustomTableDatasetValueForSample($column, $captured->sample_detail_id);
    }

    public function resolveCustomTableDatasetValueForSample(
        FormulaStepTableColumn $column,
        ?string $sampleDetailId,
    ): string {
        if (($column->column_type ?? '') !== 'dataset' || ($column->model_tied_to ?? '') !== 'samples') {
            return '';
        }

        $config = is_array($column->dataset_config) ? $column->dataset_config : [];
        $sourceTable = (string) ($config['source_table'] ?? 'sample_details');
        $displayMode = (string) ($config['display_mode'] ?? 'direct');
        if ($sourceTable === '' || ! $sampleDetailId) {
            return '';
        }

        try {
            $sourceRecord = DB::table($sourceTable)
                ->where('id', $sampleDetailId)
                ->first();

            if (! $sourceRecord) {
                return '';
            }

            if ($displayMode === 'foreign_key') {
                $fkColumn = (string) ($config['foreign_key_column'] ?? '');
                $refTable = (string) ($config['referenced_table'] ?? '');
                $refKey = (string) ($config['referenced_key_column'] ?? 'id');
                $refDisplay = (string) ($config['referenced_display_column'] ?? '');
                if ($fkColumn === '' || $refTable === '' || $refDisplay === '') {
                    return '';
                }

                $fkValue = data_get($sourceRecord, $fkColumn);
                if ($fkValue === null || $fkValue === '') {
                    return '';
                }

                $refRecord = DB::table($refTable)
                    ->where($refKey, $fkValue)
                    ->first();

                return (string) (data_get($refRecord, $refDisplay) ?? '');
            }

            $sourceDisplayColumn = (string) ($config['source_display_column'] ?? '');
            if ($sourceDisplayColumn === '') {
                return '';
            }

            return (string) (data_get($sourceRecord, $sourceDisplayColumn) ?? '');
        } catch (\Throwable $e) {
            Log::warning('Failed resolving custom table dataset value: '.$e->getMessage(), [
                'column_id' => $column->id,
                'captured_result_id' => $captured->id,
            ]);

            return '';
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
    public function handleTriggerPostResults(?string $formulaId = null): void
    {
        if ($formulaId !== null && (string) $this->formula->id !== (string) $formulaId) {
            return;
        }

        $this->openPostResultsModal();
    }

    public function openPostResultsModal(): void
    {
        try {
            $access = app(LabSectionResultAccess::class);
            if (! $access->hasLabSectionAssignment(Auth::user())) {
                $this->setMessage($access->denyEditMessage(Auth::user()), 'error');
                return;
            }

            // Load available standards for dropdowns
            $this->availableStandards = \App\Standards::where('status', 1)
                ->orderBy('name')
                ->get();

            // Get unique samples with analysis types
            $samples = [];
            $this->useLookupAsStandard = [];
            $this->showMainStandardDropdown = [];
            $this->showSecondaryStandardDropdown = [];
            $this->mainStandardSearch = [];
            $this->secondaryStandardSearch = [];
            $this->filteredMainStandards = [];
            $this->filteredSecondaryStandards = [];

            foreach ($this->capturedResults as $captured) {
                $sample = $captured->sample;
                if (! $sample) {
                    continue;
                }

                $sampleId = (string) $sample->id;
                if (! isset($samples[$sampleId])) {
                    $analysisTypeNames = $this->capturedResults
                        ->where('sample_detail_id', $sample->id)
                        ->map(function ($cr) {
                            return $cr->analysisElement->analysis_type->name ?? null;
                        })
                        ->unique()
                        ->filter()
                        ->implode(', ');

                    $samples[$sampleId] = [
                        'id' => $sampleId,
                        'sample_code' => $sample->sample_code,
                        'analysis_types' => $analysisTypeNames ?: 'N/A',
                        'main_standard' => $sample->main_standard ? (string) $sample->main_standard : null,
                        'secondary_standard' => $sample->secondary_standard ? (string) $sample->secondary_standard : null,
                        'third_standard_id' => $sample->third_standard_id ? (string) $sample->third_standard_id : null,
                    ];
                    $this->useLookupAsStandard[$sampleId] = false;
                }
            }

            // Find lookup table marked as standard (last step with is_standard = 1)
            $this->lookupStandardInfo = $this->getStandardLookupTable();

            $this->samplesWithStandards = array_values($samples);
            $this->showPostResultsModal = true;
            $this->currentStep = 0;
            $this->postingInProgress = false;

            // Defaults for analysis dates (UI only; posting uses these)
            $today = now()->format('Y-m-d');
            $this->startAnalysisDate = $this->startAnalysisDate ?: $today;
            $this->endAnalysisDate = $this->endAnalysisDate ?: $today;

            // Build preview rows for the modal results table
            $this->postResultsPreviewRows = $this->buildPostResultsPreviewRows();
        } catch (\Exception $e) {
            Log::error('Error opening post results modal: ' . $e->getMessage());
            $this->setMessage('Error loading standards information', 'error');
        }
    }

    public function updatedUseLookupAsStandard(): void
    {
        if (! $this->showPostResultsModal || $this->postingInProgress) {
            return;
        }

        $this->postResultsPreviewRows = $this->buildPostResultsPreviewRows();
    }

    private function buildPostResultsPreviewRows(): array
    {
        $rows = [];

        foreach ($this->capturedResults as $captured) {
            $wsData = $this->worksheetData[$captured->id] ?? [];
            $finalResult = (string) ($wsData['final_result'] ?? '');

            $reportingSymbol = '';
            $numericResult = $finalResult;
            if (preg_match('/^(<=|>=|<|>)\s*(.+)$/', trim($finalResult), $matches)) {
                $reportingSymbol = $matches[1];
                $numericResult = trim($matches[2]);
            }

            $sample = $captured->sample;
            $sampleId = $sample ? (string) $sample->id : '';
            $analyteName = $captured->analysisElement?->analyte?->name ?? '';
            $methodName = $this->resolveAnalysisElementMethodName($captured);

            $mainStandardId = $sample?->main_standard ? (string) $sample->main_standard : null;
            $secondaryStandardId = $sample?->secondary_standard ? (string) $sample->secondary_standard : null;

            $standardLimits = $this->getStandardLimitsDisplay($captured, $sample);

            $remarkPreview = '-';
            if ($numericResult !== '') {
                if (
                    $sampleId !== '' &&
                    ! empty($this->useLookupAsStandard[$sampleId]) &&
                    $this->lookupStandardInfo
                ) {
                    $lookupStepId = $this->lookupStandardInfo['step_id'];
                    $remarkPreview = (string) (($wsData['steps'][$lookupStepId] ?? '') ?: '-');
                } else {
                    $remarkPreview = $this->calculateRemark($captured, $sample, $numericResult, $reportingSymbol);
                }
            }

            $rows[] = [
                'captured_result_id' => (string) $captured->id,
                'sample_id' => $sampleId,
                'sample_code' => (string) ($sample?->sample_code ?? ''),
                'analyte' => (string) $analyteName,
                'result' => (string) $numericResult,
                'reporting_symbol' => (string) $reportingSymbol,
                'main_standard' => $mainStandardId,
                'secondary_standard' => $secondaryStandardId,
                'standard' => $this->getSelectedStandardName($mainStandardId),
                'standard_limits' => (string) $standardLimits,
                'remark' => (string) $remarkPreview,
                'method' => (string) $methodName,
                'reporting_unit' => (string) ($captured->analysisElement?->getAttribute('reporting_unit') ?? ''),
            ];
        }

        return $rows;
    }

    private function resolveAnalysisElementMethodName($captured): string
    {
        $element = $captured->analysisElement;
        if (! $element) {
            return '';
        }

        // AnalysisElements::method() is a helper that returns AnalysisMethod|null (not a relation).
        $methodModel = $element->method();
        if ($methodModel && ! empty($methodModel->name)) {
            return (string) $methodModel->name;
        }

        $methodId = $element->getAttribute('method');
        if ($methodId) {
            $fromCollection = $this->methods instanceof \Illuminate\Support\Collection
                ? $this->methods->firstWhere('id', (string) $methodId)
                : null;

            if ($fromCollection && ! empty($fromCollection->name)) {
                return (string) $fromCollection->name;
            }
        }

        return '';
    }

    private function getStandardLimitsDisplay($captured, $sample): string
    {
        if (!$sample || !$sample->main_standard) {
            return '-';
        }

        $analyte = \App\Analyte::find($captured->analyte_id);
        if (!$analyte) {
            return '-';
        }

        $analyteGuide = \App\StandardAnalytes::where('analyte_id', $analyte->id)
            ->where('standard_id', $sample->main_standard)
            ->first();

        if (!$analyteGuide) {
            return '-';
        }

        if (($analyteGuide->standard_value_type ?? null) === 'is_range') {
            $low = $analyteGuide->low ?? null;
            $high = $analyteGuide->high ?? null;

            if ($low !== null && $high !== null) {
                return trim((string) $low) . ' - ' . trim((string) $high);
            }
        }

        if ($analyteGuide->standard_is_value !== null && $analyteGuide->standard_is_value !== '') {
            return (string) $analyteGuide->standard_is_value;
        }

        if (!empty($analyteGuide->standard_value_id)) {
            $standardValue = \App\StandardValue::find($analyteGuide->standard_value_id);
            if ($standardValue && isset($standardValue->code) && $standardValue->code !== '') {
                return (string) $standardValue->code;
            }
        }

        return '-';
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
    public function updateSampleStandard(string $sampleId, string $standardType, $standardId = null): void
    {
        try {
            $sample = \App\SampleDetails::find($sampleId);
            if (! $sample) {
                return;
            }

            $standardId = $standardId !== null && $standardId !== '' ? (string) $standardId : null;

            if ($standardType === 'main') {
                $sample->main_standard = $standardId;
            } elseif ($standardType === 'secondary') {
                $sample->secondary_standard = $standardId;
            } elseif ($standardType === 'third') {
                $sample->third_standard_id = $standardId;
            }

            $sample->save();
            $sample->refresh();

            foreach ($this->samplesWithStandards as $key => $s) {
                if ((string) $s['id'] === $sampleId) {
                    if ($standardType === 'third') {
                        $this->samplesWithStandards[$key]['third_standard_id'] = $standardId;
                    } else {
                        $this->samplesWithStandards[$key][$standardType.'_standard'] = $standardId;
                    }
                    break;
                }
            }

            foreach ($this->capturedResults as $captured) {
                if ((string) $captured->sample_detail_id === $sampleId) {
                    $captured->setRelation('sample', $sample);
                }
            }

            if ($standardType === 'main') {
                $this->showMainStandardDropdown[$sampleId] = false;
                $this->mainStandardSearch[$sampleId] = '';
            } else {
                $this->showSecondaryStandardDropdown[$sampleId] = false;
                $this->secondaryStandardSearch[$sampleId] = '';
            }

            if ($this->showPostResultsModal && ! $this->postingInProgress) {
                $this->postResultsPreviewRows = $this->buildPostResultsPreviewRows();
            }
        } catch (\Exception $e) {
            Log::error('Error updating sample standard: '.$e->getMessage());
        }
    }

    public function searchMainStandards(string $sampleId): void
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

        $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'main']);
    }

    public function searchSecondaryStandards(string $sampleId): void
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

        $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'secondary']);
    }

    public function getSelectedStandardName($standardId): string
    {
        if (! $standardId) {
            return '';
        }

        $standard = $this->availableStandards->firstWhere('id', (string) $standardId);

        return $standard ? $standard->name.' ('.$standard->code.')' : '';
    }

    /**
     * Toggle main standard dropdown for a sample
     */
    public function toggleMainStandardDropdown(string $sampleId): void
    {
        $this->showMainStandardDropdown[$sampleId] = ! ($this->showMainStandardDropdown[$sampleId] ?? false);

        if ($this->showMainStandardDropdown[$sampleId]) {
            $this->searchMainStandards($sampleId);
            $this->dispatch('dropdownOpened', ['sampleId' => $sampleId, 'type' => 'main']);
        }
    }

    /**
     * Toggle secondary standard dropdown for a sample
     */
    public function toggleSecondaryStandardDropdown(string $sampleId): void
    {
        $this->showSecondaryStandardDropdown[$sampleId] = ! ($this->showSecondaryStandardDropdown[$sampleId] ?? false);

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
            $access = app(LabSectionResultAccess::class);
            $user = Auth::user();
            if (! $access->hasLabSectionAssignment($user)) {
                $this->setMessage($access->denyEditMessage($user), 'error');
                return;
            }

            foreach ($this->capturedResults as $captured) {
                if (! $access->canEditCapturedResult($user, $captured)) {
                    $this->setMessage($access->denyEditMessage($user), 'error');
                    return;
                }
            }

            $this->validate([
                'startAnalysisDate' => ['required', 'date'],
                'endAnalysisDate' => ['required', 'date', 'after_or_equal:startAnalysisDate'],
            ]);

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
                $captured->assignAnalyst($wsData['done_by_user_id'] ?? Auth::id());

                if ($captured->analysisElement) {
                    $captured->applyAnalysisElementDefaults();
                }

                // Ensure analysis dates exist with proper lab section tracking.
                // Schema only has start_analysis_date + analysis_dates JSON (no end_analysis_date column).
                $analysis_date = SampleAnalysisDates::where('sample_header_id', $captured->sample_header_id)
                    ->where('sample_detail_id', $captured->sample_detail_id)
                    ->first() ?? new SampleAnalysisDates();

                $startDate = $this->startAnalysisDate ?: ($wsData['date'] ?? now()->format('Y-m-d'));
                $endDate = $this->endAnalysisDate ?: $startDate;

                $decodedDates = $analysis_date->analysis_dates ? json_decode($analysis_date->analysis_dates, true) : [];
                $prev_dates = [];
                if (is_array($decodedDates)) {
                    foreach ($decodedDates as $sectionId => $sectionValue) {
                        if (is_array($sectionValue)) {
                            $prev_dates[$sectionId] = [
                                'start_date' => $sectionValue['start_date'] ?? $sectionValue['start'] ?? null,
                                'end_date' => $sectionValue['end_date'] ?? $sectionValue['end'] ?? null,
                            ];
                            continue;
                        }

                        $prev_dates[$sectionId] = [
                            'start_date' => $sectionValue,
                            'end_date' => null,
                        ];
                    }
                }

                if ($captured->lab_section_id) {
                    $prev_dates[(string) $captured->lab_section_id] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ];
                }

                // Keep table start_analysis_date as earliest section start
                $earliestStart = $startDate;
                foreach ($prev_dates as $val) {
                    $sectionStartDate = is_array($val) ? ($val['start_date'] ?? '') : (string) $val;
                    if ($sectionStartDate !== '' && $sectionStartDate < $earliestStart) {
                        $earliestStart = $sectionStartDate;
                    }
                }

                $analysis_date->sample_header_id = $captured->sample_header_id;
                $analysis_date->sample_detail_id = $captured->sample_detail_id;
                $analysis_date->start_analysis_date = $earliestStart;
                $analysis_date->analysis_dates = json_encode($prev_dates);
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
                    $sample &&
                    ! empty($this->useLookupAsStandard[(string) $sample->id]) &&
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
    public function openChangeLookupModal(string $capturedResultId, string $stepId): void
    {
        try {
            $this->currentCapturedResultId = $capturedResultId;
            $this->selectedLookupStepId = $stepId;
            $this->currentLookupTableInfo = null;

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
                $this->currentLookupTableInfo = [
                    'id' => (string) $currentLookupTable->id,
                    'name' => (string) $currentLookupTable->name,
                    'lookup_type' => (string) ($currentLookupTable->lookup_type ?? 'key_value_comparison'),
                    'key_columns' => (array) ($currentLookupTable->key_columns ?? []),
                ];

                // Still open the modal so the user sees why nothing is available
                $this->selectedReplacementLookupTableId = (string) $lookupConfig['lookup_table_id'];
                $this->showLookupTableModal = true;
                $this->setMessage('No compatible lookup tables found. Create another active lookup table with the same key columns to enable switching.', 'warning');
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
    public function resetLookupTable(?string $capturedResultId = null, ?string $stepId = null): void
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
    public function getOriginalLookupTable(string $stepId): ?\App\Models\Formulars\LookupTable
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
    public function getCurrentLookupTable(string $capturedResultId, string $stepId): ?\App\Models\Formulars\LookupTable
    {
        $override = $this->worksheetData[$capturedResultId]['lookup_overrides'][$stepId] ?? null;

        if ($override) {
            return \App\Models\Formulars\LookupTable::find($override);
        }

        return $this->getOriginalLookupTable($stepId);
    }

    public function toggleCheckboxForSample(string $capturedResultId, string $stepId, string $optionId): void
    {
        $current = $this->worksheetData[$capturedResultId]['steps'][$stepId] ?? [];
        if (is_string($current)) {
            $current = json_decode($current, true) ?: [];
        }

        if (in_array($optionId, $current, true)) {
            $current = array_values(array_diff($current, [$optionId]));
        } else {
            $current[] = $optionId;
        }

        $this->worksheetData[$capturedResultId]['steps'][$stepId] = $current;

        // Recalculate derived formula steps
        $this->calculateFormulaResult($capturedResultId);

        // Auto-save the change
        $this->saveWorksheet($capturedResultId);
    }

    public function updatedViewMode(string $value): void
    {
        if ($value === 'form') {
            $this->bootstrapSharedWorksheetState();
        } else {
            $this->syncSharedWorksheetMetaToRows();
        }
    }

    protected function syncSharedWorksheetMetaToRows(): void
    {
        foreach ($this->capturedResults as $captured) {
            $crId = (string) $captured->id;

            if (! isset($this->worksheetData[$crId])) {
                continue;
            }

            $this->worksheetData[$crId]['date'] = $this->sharedWorksheetMeta['date'] ?? null;
            $this->worksheetData[$crId]['lab_no'] = $this->sharedWorksheetMeta['lab_no'] ?? $this->batch->batch_code;
            $this->worksheetData[$crId]['time_in'] = $this->sharedWorksheetMeta['time_in'] ?? null;
            $this->worksheetData[$crId]['done_by_user_id'] = $this->sharedWorksheetMeta['done_by_user_id'] ?? null;
            $this->worksheetData[$crId]['time_out'] = $this->sharedWorksheetMeta['time_out'] ?? null;
            $this->worksheetData[$crId]['read_by_user_id'] = $this->sharedWorksheetMeta['read_by_user_id'] ?? null;
            $this->worksheetData[$crId]['read_date'] = $this->sharedWorksheetMeta['read_date'] ?? null;
        }
    }

    public function render()
    {
        $capturedCollection = collect($this->capturedResults);
        $metaResolver = app(WorksheetMetaResolver::class);

        return view('livewire.worksheets.formula-worksheet', [
            'users' => User::where('active', 1)->get(),
            'worksheetMetaSummary' => $metaResolver->summaryForMany($capturedCollection),
            'worksheetMetaRows' => $metaResolver->forMany($capturedCollection),
        ]);
    }
}
