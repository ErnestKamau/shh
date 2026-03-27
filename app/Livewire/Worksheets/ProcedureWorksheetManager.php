<?php

namespace App\Livewire\Worksheets;

use Livewire\Component;
use App\CapturedResult;
use App\Analyte;
use App\BatchAttachment;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\CapturedProcedureValue;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\CapturedProcedureConfigValue;
use App\Models\Procedures\ProcedureTestKitColumn;
use App\Models\Procedures\ProcedureTestKitRow;
use App\Models\Procedures\ProcedureTestKitValue;
use App\Models\Procedures\ProcedureWorksheetStepAnalyst;
use App\SampleHeader;
use App\User;
use App\SampleDetails;
use App\SampleType;
use App\AnalysisMethod;
use App\ReportFormat;
use App\Services\ProcedureWorksheetPdfService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Str;

class ProcedureWorksheetManager extends Component
{
    protected $listeners = [
        // Triggered by the parent WorksheetManager "Post Results" button.
        'triggerPostResults' => 'postResults',
    ];
    public $batchId;
    public $activeTabs = []; // This will hold an array of active Analyte IDs
    public $selectedWorksheetId = null;
    public $selectedSamples = []; // Array of sample_detail_ids
    public $inputValues = []; // [captured_result_id => [step_id => value]]
    public $configFieldValues = []; // [captured_result_id => [config_field_id => value]]
    public $testKitRows = []; // [row_id => ['row_index' => n]]
    public $testKitData = []; // [row_id => [column_id => value]]
    public array $stepEquipmentOverrides = []; // [step_id => [equipment_id, ...]]
    public array $stepMeasurandOverrides = []; // [step_id => [measurand_id, ...]]
    public array $stepAnalystOverrides = []; // [step_id => [user_id, ...]]
    public ?int $lastLoadedWorksheetId = null;
    public bool $worksheetAlreadyPosted = false;
    /**
     * Hash used in Blade wire:key attributes to force step rows
     * to be re-rendered when importing data from another analyte.
     */
    public string $importHash = '';

    /** Simple in-component flash messaging for Livewire actions. */
    public ?string $flashMessage = null;
    public ?string $flashType = null; // success|error|warning|null

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
    /** Tracks which external-selection cache key has been loaded into memory. */
    public ?string $externalSelectionCacheKeyLoaded = null;

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->importHash = Str::random(8);
        $this->syncCapturedResultsProcedureWorksheetIds();
        $this->initActiveTab();
    }

    private function syncCapturedResultsProcedureWorksheetIds(): void
    {
        // If a procedure worksheet gets deleted/replaced, existing captured_results rows can keep
        // pointing to the old worksheet id. That results in an empty worksheet dropdown because
        // ProcedureWorksheet::find(oldId) returns nothing.
        //
        // Reconcile captured_results.procedure_worksheet_id to the currently configured ACTIVE
        // analysis_elements.procedure_worksheet_id for this batch, but only when the current
        // captured worksheet is missing or inactive.
        $rows = CapturedResult::query()
            ->where('captured_results.sample_header_id', $this->batchId)
            ->whereNotNull('captured_results.analysis_element_id')
            ->leftJoin('analysis_elements', 'analysis_elements.id', '=', 'captured_results.analysis_element_id')
            ->leftJoin('procedure_worksheets as current_pw', 'current_pw.id', '=', 'captured_results.procedure_worksheet_id')
            ->leftJoin('procedure_worksheets as target_pw', 'target_pw.id', '=', 'analysis_elements.procedure_worksheet_id')
            ->whereNotNull('analysis_elements.procedure_worksheet_id')
            ->where('target_pw.is_active', true)
            ->select([
                'captured_results.id as captured_result_id',
                'captured_results.procedure_worksheet_id as current_procedure_worksheet_id',
                'current_pw.id as current_pw_id',
                'current_pw.is_active as current_pw_is_active',
                'analysis_elements.procedure_worksheet_id as target_procedure_worksheet_id',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        /** @var array<int, array<int, int>> $idsByTargetWorksheetId */
        $idsByTargetWorksheetId = [];

        foreach ($rows as $row) {
            $targetWorksheetId = (int) $row->target_procedure_worksheet_id;
            $currentWorksheetId = $row->current_procedure_worksheet_id !== null ? (int) $row->current_procedure_worksheet_id : null;

            $currentPwId = $row->current_pw_id !== null ? (int) $row->current_pw_id : null;
            $currentPwIsActive = $row->current_pw_is_active !== null ? (bool) $row->current_pw_is_active : null;

            // Update only if the currently referenced worksheet is missing or inactive.
            $needsUpdate = $currentWorksheetId === null
                || $currentPwId === null
                || ($currentPwIsActive !== null && $currentPwIsActive === false);

            if (! $needsUpdate) {
                continue;
            }

            $idsByTargetWorksheetId[$targetWorksheetId][] = (int) $row->captured_result_id;
        }

        foreach ($idsByTargetWorksheetId as $targetWorksheetId => $ids) {
            if (empty($ids)) {
                continue;
            }

            CapturedResult::query()
                ->whereIn('id', $ids)
                ->update([
                    'procedure_worksheet_id' => $targetWorksheetId,
                    'has_procedure_worksheet' => true,
                ]);
        }
    }

    public function initActiveTab()
    {
        $params = $this->paramsWithWorksheets;
        if ($params->isNotEmpty() && empty($this->activeTabs)) {
            $this->activeTabs = [$params->first()->id];
            $this->updatedActiveTabs();
        }
    }

    public function getParamsWithWorksheetsProperty()
    {
        // Get analytes that have captured results tied to an ACTIVE procedure worksheet.
        // (Works even if captured_results still contain a stale/deleted worksheet id,
        // because mount() reconciles them to the current active configuration.)
        return CapturedResult::query()
            ->where('captured_results.sample_header_id', $this->batchId)
            ->whereHas('procedureWorksheet', fn ($q) => $q->where('is_active', true))
            ->with('my_analyte')
            ->get()
            ->pluck('my_analyte')
            ->unique('id')
            ->values();
    }

    public function getWorksheetsForParamProperty()
    {
        if (empty($this->activeTabs)) return collect();

        // Get unique procedure worksheets for the selected analytes in this batch
        $worksheetIds = CapturedResult::where('sample_header_id', $this->batchId)
            ->whereIn('analyte_id', $this->activeTabs)
            ->whereNotNull('procedure_worksheet_id')
            ->pluck('procedure_worksheet_id')
            ->unique();

        return ProcedureWorksheet::whereIn('id', $worksheetIds)
            ->where('is_active', true)
            ->get();
    }

    public function setActiveTab($tabId)
    {
        $this->activeTabs = [$tabId];
        $this->updatedActiveTabs();
    }

    public function updatedActiveTabs()
    {
        $this->selectedWorksheetId = null;
        $this->selectedSamples = [];
        // Preserve $externalCapturedResultIds so user-added external samples aren't lost when toggling tabs
        $this->externalSearchResults = [];
        $this->externalSelectionItems = [];
        $this->externalSearch = '';
        $this->showExternalPanel = false;
        $this->showExternalDropdown = false;

        // Clear cached input values to prevent cross-tab saves
        $this->inputValues = [];
        $this->configFieldValues = [];
        $this->testKitData = [];
        $this->testKitRows = [];
        $this->stepEquipmentOverrides = [];
        $this->stepMeasurandOverrides = [];
        $this->stepAnalystOverrides = [];
        // Auto-select first worksheet ?
        $worksheets = $this->worksheetsForParam;
        if ($worksheets->count() > 0) {
            $this->selectWorksheet($worksheets->first()->id);
        }
    }

    public function selectWorksheet($worksheetId)
    {
        $this->selectedWorksheetId = $worksheetId;
        // Preserve $externalCapturedResultIds so user-added external samples aren't lost when switching worksheets
        $this->externalSearchResults = [];
        $this->externalSelectionItems = [];
        $this->externalSearch = '';
        $this->showExternalPanel = false;
        $this->showExternalDropdown = false;
        $this->loadSamples();
    }

    public function updatedSelectedWorksheetId($value)
    {
        if (!$value) {
            return;
        }
        // Preserve $externalCapturedResultIds so user-added external samples aren't lost when switching worksheets
        $this->externalSearchResults = [];
        $this->externalSelectionItems = [];
        $this->externalSearch = '';
        $this->showExternalPanel = false;
        $this->showExternalDropdown = false;

        // Clear cached input values to prevent cross-worksheet saves
        $this->inputValues = [];
        $this->configFieldValues = [];
        $this->testKitData = [];
        $this->testKitRows = [];
        $this->stepEquipmentOverrides = [];
        $this->stepMeasurandOverrides = [];
        $this->stepAnalystOverrides = [];
        $this->loadSamples();
    }

    public function loadSamples()
    {
        if (empty($this->activeTabs) || !$this->selectedWorksheetId) {
            $this->selectedSamples = [];
            $this->testKitRows = [];
            $this->testKitData = [];
            return;
        }

        // Persist external samples per (batch, worksheet, analyte, user) so they survive reloads.
        $activeAnalyteIdForExternal = count($this->activeTabs) > 0 ? (int) $this->activeTabs[0] : null;
        $externalCacheKey = $activeAnalyteIdForExternal
            ? "worksheet_external_cr_{$this->batchId}_{$this->selectedWorksheetId}_{$activeAnalyteIdForExternal}_" . \Illuminate\Support\Facades\Auth::id()
            : null;

        if ($externalCacheKey && $this->externalSelectionCacheKeyLoaded !== $externalCacheKey) {
            $cachedExternal = \Illuminate\Support\Facades\Cache::get($externalCacheKey);
            $cachedExternalIds = is_array($cachedExternal) ? array_values(array_filter(array_map('intval', $cachedExternal))) : [];

            $this->externalCapturedResultIds = $cachedExternalIds;
            $this->externalSelectionCacheKeyLoaded = $externalCacheKey;
        }

        // When switching to a different worksheet tab, always start from a clean
        // in-memory slate and then repopulate only from persisted data for that
        // specific worksheet.
        if ($this->lastLoadedWorksheetId !== (int) $this->selectedWorksheetId) {
            Log::info('ProcedureWorksheetManager worksheet switch detected, resetting in-memory state', [
                'batch_id' => $this->batchId,
                'from_worksheet_id' => $this->lastLoadedWorksheetId,
                'to_worksheet_id' => (int) $this->selectedWorksheetId,
                'active_tabs' => $this->activeTabs,
            ]);

            $this->inputValues = [];
            $this->configFieldValues = [];
            $this->testKitRows = [];
            $this->testKitData = [];
            $this->stepEquipmentOverrides = [];
            $this->stepMeasurandOverrides = [];
            $this->stepAnalystOverrides = [];
        }

        // Base query for captured results for these analytes and worksheet
        $query = CapturedResult::query()
            ->whereIn('analyte_id', $this->activeTabs)
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

        $this->lastLoadedWorksheetId = (int) $this->selectedWorksheetId;

        // Get samples for this analyte and worksheet (current + external)
        $samples = $capturedResults
            ->pluck('sample.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $activeAnalyteId = count($this->activeTabs) > 0 ? $this->activeTabs[0] : null;
        $cacheKey = $activeAnalyteId ? "worksheet_selection_{$this->batchId}_{$this->selectedWorksheetId}_{$activeAnalyteId}_" . \Illuminate\Support\Facades\Auth::id() : null;

        $cachedSelection = $cacheKey ? \Illuminate\Support\Facades\Cache::get($cacheKey) : null;

        // Default select all only when nothing is selected yet and no cache exists.
        // Otherwise, preserve the current selection (or cache) but keep it within the available samples.
        if (empty($this->selectedSamples) && $cachedSelection === null) {
            $this->selectedSamples = $samples;
        } else {
            $baseSelection = $cachedSelection !== null ? $cachedSelection : $this->selectedSamples;
            $this->selectedSamples = array_values(array_intersect($baseSelection, $samples));
            // Avoid artificially re-selecting everything if cache itself is intentionally empty
            if (empty($this->selectedSamples) && $cachedSelection === null) {
                $this->selectedSamples = $samples;
            }
        }

        // Load existing values for all included captured results
        $capturedResultIds = $capturedResults->pluck('id');
        $steps = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();
        $configFields = ProcedureConfigField::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();

        // Step values (per captured_result/step) including associated overrides (equipment/measurand/analyst IDs)
        $stepValues = CapturedProcedureValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($stepValues as $val) {
            $stored = $val->value;

            // Support both legacy string values and new JSON-encoded
            // per-measurand value maps.
            $decoded = null;
            if (is_string($stored)) {
                $trimmed = trim($stored);
                if ($trimmed !== '' && $trimmed[0] === '{') {
                    $maybe = json_decode($trimmed, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) {
                        $decoded = $maybe;
                    }
                }
            }

            $this->inputValues[$val->captured_result_id][$val->procedure_worksheet_step_id] = $decoded ?? $stored;
        }

        $configValues = CapturedProcedureConfigValue::whereIn('captured_result_id', $capturedResultIds)->get();

        foreach ($configValues as $val) {
            $this->configFieldValues[$val->captured_result_id][$val->procedure_config_field_id] = $val->value;
        }

        // Ensure every displayed captured_result has configFieldValues keys for each config field
        // and pre-fill dynamically from the underlying entities when nothing is saved yet.
        $batchHeader = SampleHeader::find($this->batchId);

        foreach ($capturedResults as $cr) {
            foreach ($configFields as $field) {
                $existing = $this->configFieldValues[$cr->id][$field->id] ?? null;
                $isEmpty = $existing === null || $existing === '' || $existing === [];

                if ($isEmpty) {
                    $value = null;

                    switch ($field->model_tied_to) {
                        case 'users':
                            $value = $cr->operator_id ? (string) $cr->operator_id : null;
                            break;

                        case 'methods':
                            $value = $cr->method_id ? (string) $cr->method_id : null;
                            break;

                        case 'sample_types':
                            $value = $batchHeader && $batchHeader->sample_type_id
                                ? (string) $batchHeader->sample_type_id
                                : null;
                            break;

                        case 'sample_details':
                            $value = $cr->sample_detail_id ? (string) $cr->sample_detail_id : null;
                            break;

                        case 'captured_results':
                            $value = $cr->analyte_id ? (string) $cr->analyte_id : null;
                            break;

                        case 'report_formats':
                            $value = null;
                            break;

                        default:
                            $value = null;
                    }

                    // Special-case: "Date Received" field should default from the batch header's receipt date.
                    if (
                        $value === null
                        && $batchHeader
                        && $batchHeader->receipt_date
                        && in_array(
                            strtolower((string) $field->field_value_name),
                            ['date_received', 'date_recieved'],
                            true
                        )
                        && $field->field_type === 'date'
                    ) {
                        // Normalize to HTML5 date input format (Y-m-d) so the
                        // value actually renders in the browser control.
                        $raw = (string) $batchHeader->receipt_date;
                        try {
                            $value = \Carbon\Carbon::parse($raw)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $value = substr($raw, 0, 10);
                        }
                    }

                    if ($field->field_type === 'dataset_multiselect') {
                        $this->configFieldValues[$cr->id][$field->id] = $value !== null ? [(string) $value] : [];
                    } else {
                        $this->configFieldValues[$cr->id][$field->id] = $value !== null ? (string) $value : '';
                    }
                } elseif ($field->field_type === 'dataset_multiselect') {
                    $val = $this->configFieldValues[$cr->id][$field->id];
                    $this->configFieldValues[$cr->id][$field->id] = is_array($val)
                        ? $val
                        : array_values(array_filter(explode(',', (string) $val)));
                }
            }
        }

        // Ensure "Lab No." (sample_details) dropdowns stay in sync with the
        // currently selected samples in the UI.
        $this->syncLabNoConfigFieldToSelectedSamples($capturedResults, $configFields);

        // Mark worksheet as "already posted" when any captured result in this
        // batch + worksheet + active analyte has the worksheet_posted flag set.
        $this->worksheetAlreadyPosted = $capturedResults
            ->contains(fn($cr) => (bool) ($cr->worksheet_posted ?? false));

        // Kit columns are worksheet-scoped.
        // Kit rows + values are instance-scoped by captured_result_id (sample/analyte instance).
        $columns = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();

        $selectedCapturedResultIds = $capturedResults
            ->filter(fn($r) => $r->sample && in_array($r->sample->id, $this->selectedSamples, true))
            ->pluck('id')
            ->values()
            ->all();

        $firstCapturedResultId = $selectedCapturedResultIds[0] ?? null;

        // Load rows for the UI instance:
        // - instance rows (captured_result_id = firstCapturedResultId)
        // - or legacy shared rows (captured_result_id IS NULL) *only if* they
        //   have at least one non-empty value for the UI instance.
        $rowsQuery = ProcedureTestKitRow::where('procedure_worksheet_id', $this->selectedWorksheetId);
        if ($firstCapturedResultId !== null) {
            $rowsQuery->where(function ($q) use ($firstCapturedResultId) {
                $q->where('captured_result_id', $firstCapturedResultId)
                    ->orWhereNull('captured_result_id');
            });
        } else {
            $rowsQuery->whereNull('captured_result_id');
        }

        $rows = $rowsQuery->orderBy('row_index')->get();
        $rowIds = $rows->pluck('id')->values()->all();

        $tkValuesQuery = ProcedureTestKitValue::whereIn('procedure_test_kit_row_id', $rowIds)
            ->where(function ($q) use ($firstCapturedResultId) {
                if ($firstCapturedResultId !== null) {
                    $q->where('captured_result_id', $firstCapturedResultId)
                        ->orWhereNull('captured_result_id');
                } else {
                    $q->whereNull('captured_result_id');
                }
            });

        $tkValues = $tkValuesQuery->get();

        // Default values come from captured_result_id = NULL.
        // Instance values come from captured_result_id = first selected sample.
        $defaultsByCell = [];
        $instanceByCell = [];

        foreach ($tkValues as $val) {
            $rowId = (int) $val->procedure_test_kit_row_id;
            $colId = (int) $val->procedure_test_kit_column_id;

            if ($val->captured_result_id === null) {
                $defaultsByCell[$rowId][$colId] = $val->value;
                continue;
            }

            if ($firstCapturedResultId !== null && (int) $val->captured_result_id === (int) $firstCapturedResultId) {
                $instanceByCell[$rowId][$colId] = $val->value;
            }
        }

        // Populate UI grid values, then hide rows that are completely blank
        // for the UI instance (fixes "blank rows when switching instances").
        $this->testKitRows = [];
        $this->testKitData = [];

        foreach ($rows as $row) {
            $rowId = (int) $row->id;
            $hasAnyNonEmptyValue = false;

            foreach ($columns as $col) {
                $colId = (int) $col->id;

                $val = '';
                if (array_key_exists($rowId, $instanceByCell) && array_key_exists($colId, $instanceByCell[$rowId] ?? [])) {
                    $val = $instanceByCell[$rowId][$colId] ?? '';
                } else {
                    $val = $defaultsByCell[$rowId][$colId] ?? '';
                }

                $valStr = $val === null ? '' : (string) $val;
                if (trim($valStr) !== '') {
                    $hasAnyNonEmptyValue = true;
                }

                $this->testKitData[$rowId][$colId] = $valStr;
            }

            if ($hasAnyNonEmptyValue) {
                $this->testKitRows[$rowId] = ['row_index' => (int) $row->row_index];
            }
        }

        // Load default step equipment/measurand overrides (editable per-render)
        $selectedResultIds = $this->getSelectedCapturedResultIds();
        $selectedValues = $stepValues
            ->filter(fn($v) => in_array($v->captured_result_id, $selectedResultIds))
            ->groupBy('procedure_worksheet_step_id');

        // Derive current analyte from the captured results actually in play for this
        // worksheet, instead of relying on activeTabs ordering. If more than one
        // analyte is present, skip loading analysts to avoid cross-element bleed.
        $currentAnalyteIds = $capturedResults->pluck('analyte_id')->filter()->unique()->values();
        $activeAnalyteId = $currentAnalyteIds->count() === 1 ? $currentAnalyteIds->first() : null;

        // Load per-step analysts from dedicated table (one analyst set per step per
        // batch + analyte + worksheet), independent of individual samples.
        $stepAnalystRows = $activeAnalyteId && $this->selectedWorksheetId
            ? ProcedureWorksheetStepAnalyst::where('batch_id', $this->batchId)
            ->where('analyte_id', $activeAnalyteId)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->get()
            ->keyBy('procedure_worksheet_step_id')
            : collect();

        foreach ($steps as $step) {
            $capturedValObj = $selectedValues->get($step->id)?->first();

            if (! isset($this->stepEquipmentOverrides[$step->id])) {
                if ($capturedValObj && $capturedValObj->equipment_ids !== null) {
                    $this->stepEquipmentOverrides[$step->id] = is_array($capturedValObj->equipment_ids) ? array_map('strval', $capturedValObj->equipment_ids) : [];
                } else {
                    $rawEq = $step->default_equipment_id;

                    if (is_string($rawEq)) {
                        $trimmed = trim($rawEq);
                        if ($trimmed !== '' && $trimmed[0] === '{' || $trimmed !== '' && $trimmed[0] === '[') {
                            $decoded = json_decode($trimmed, true);
                            if (is_array($decoded)) {
                                $rawEq = $decoded;
                            }
                        } elseif (str_contains($trimmed, ',')) {
                            $rawEq = array_map('trim', explode(',', $trimmed));
                        }
                    }

                    $eqIds = $rawEq === null || $rawEq === '' || $rawEq === 0
                        ? []
                        : (is_array($rawEq) ? $rawEq : [(string) $rawEq]);
                    $this->stepEquipmentOverrides[$step->id] = array_map('strval', $eqIds);
                }
            }

            if (! isset($this->stepMeasurandOverrides[$step->id])) {
                if ($capturedValObj && $capturedValObj->measurand_ids !== null) {
                    $this->stepMeasurandOverrides[$step->id] = is_array($capturedValObj->measurand_ids) ? array_map('strval', $capturedValObj->measurand_ids) : [];
                } else {
                    $this->stepMeasurandOverrides[$step->id] = is_array($step->default_measurand_ids)
                        ? array_map('strval', $step->default_measurand_ids)
                        : [];
                }
            }

            if (! isset($this->stepAnalystOverrides[$step->id])) {
                $analystRow = $stepAnalystRows->get($step->id);
                $this->stepAnalystOverrides[$step->id] = $analystRow && is_array($analystRow->analyst_ids)
                    ? array_map('strval', $analystRow->analyst_ids)
                    : [];
            }
        }

        // Seed configured per-step default values into inputValues (and
        // persist them immediately) for the currently selected samples.
        $this->seedDefaultStepValuesForSelectedResults($steps, $selectedResultIds);
    }

    /**
     * Autosave a single step's value for all selected samples and assign analyst
     * (logged-in user) to the step when first saved.
     */
    public function updatedInputValues($value, $key)
    {
        $parts = explode('.', (string) $key);
        if (count($parts) >= 2) {
            $stepId = (int) $parts[1];
            $this->autosaveStepValue($stepId);
        }
    }

    public function autosaveStepValue(int $stepId): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return;
        }

        $selectedIds = $this->getSelectedCapturedResultIds();
        if (count($selectedIds) === 0) {
            return;
        }

        $firstId = (int) $selectedIds[0];
        if (! isset($this->inputValues[$firstId][$stepId])) {
            return;
        }

        Log::info('ProcedureWorksheetManager autosaveStepValue called', [
            'batch_id' => $this->batchId,
            'selected_ids' => $selectedIds,
            'first_id' => $firstId,
            'step_id' => $stepId,
            'value_snapshot' => $this->inputValues[$firstId][$stepId] ?? null,
        ]);

        $value = $this->inputValues[$firstId][$stepId];

        // Mirror existing "shared value" behaviour: copy first sample's value to the rest.
        if (count($selectedIds) > 1) {
            foreach ($selectedIds as $id) {
                $id = (int) $id;
                if ($id === $firstId) {
                    continue;
                }
                if (! isset($this->inputValues[$id])) {
                    $this->inputValues[$id] = [];
                }
                $this->inputValues[$id][$stepId] = $value;
            }
        }

        // Persist the value only for the selected captured results for this single step.
        foreach ($selectedIds as $capturedResultId) {
            $capturedResultId = (int) $capturedResultId;
            $val = $this->inputValues[$capturedResultId][$stepId] ?? '';
            $valueToStore = is_array($val) ? json_encode($val) : ($val ?? '');

            CapturedProcedureValue::updateOrCreate(
                [
                    'captured_result_id' => $capturedResultId,
                    'procedure_worksheet_step_id' => $stepId,
                ],
                [
                    'value' => $valueToStore,
                ]
            );
        }

        // When this step is first saved, optionally assign the analyst as the logged-in user
        // for this worksheet step (shared across all selected samples).
        $userId = Auth::id();

        // Derive current analyte from the analysis samples to avoid relying on activeTabs order.
        $analysisSamples = $this->analysisSamples;
        $currentAnalyteIds = $analysisSamples->pluck('analyte_id')->filter()->unique()->values();
        $activeAnalyteId = $currentAnalyteIds->count() === 1 ? $currentAnalyteIds->first() : null;

        if ($userId && $activeAnalyteId && $this->selectedWorksheetId) {
            $current = $this->stepAnalystOverrides[$stepId] ?? [];
            if (! is_array($current)) {
                $current = $current !== null && $current !== '' ? [(string) $current] : [];
            }

            if (empty($current)) {
                $newAnalysts = [(string) $userId];

                ProcedureWorksheetStepAnalyst::updateOrCreate(
                    [
                        'batch_id' => $this->batchId,
                        'analyte_id' => $activeAnalyteId,
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'analyst_ids' => $newAnalysts,
                    ]
                );

                $this->stepAnalystOverrides[$stepId] = $newAnalysts;
                $this->dispatch('syncStepAnalystSelect', stepId: $stepId, analystIds: $newAnalysts);
            }
        }
    }

    /**
     * Autosave a single configurable field's value for all selected samples.
     */
    public function updatedConfigFieldValues($value, $key)
    {
        $parts = explode('.', (string) $key);
        if (count($parts) >= 2) {
            $fieldId = (int) $parts[1];
            $this->autosaveConfigField($fieldId);
        }
    }

    public function autosaveConfigField(int $fieldId): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return;
        }

        $selectedIds = $this->getSelectedCapturedResultIds();
        if (count($selectedIds) === 0) {
            return;
        }

        $firstId = (int) $selectedIds[0];
        if (! isset($this->configFieldValues[$firstId][$fieldId])) {
            return;
        }

        Log::info('ProcedureWorksheetManager autosaveConfigField called', [
            'batch_id' => $this->batchId,
            'selected_ids' => $selectedIds,
            'first_id' => $firstId,
            'field_id' => $fieldId,
            'value_snapshot' => $this->configFieldValues[$firstId][$fieldId] ?? null,
        ]);

        $value = $this->configFieldValues[$firstId][$fieldId];

        // Mirror existing "shared value" behaviour for config fields.
        if (count($selectedIds) > 1) {
            foreach ($selectedIds as $id) {
                $id = (int) $id;
                if ($id === $firstId) {
                    continue;
                }
                if (! isset($this->configFieldValues[$id])) {
                    $this->configFieldValues[$id] = [];
                }
                $this->configFieldValues[$id][$fieldId] = $value;
            }
        }

        foreach ($selectedIds as $capturedResultId) {
            $capturedResultId = (int) $capturedResultId;
            $val = $this->configFieldValues[$capturedResultId][$fieldId] ?? '';
            $valueToStore = is_array($val) ? implode(',', $val) : ($val ?? '');

            CapturedProcedureConfigValue::updateOrCreate(
                [
                    'captured_result_id' => $capturedResultId,
                    'procedure_worksheet_id' => $this->selectedWorksheetId,
                    'procedure_config_field_id' => $fieldId,
                ],
                [
                    'value' => $valueToStore,
                ]
            );
        }
    }

    public function updatedSelectedSamples(): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return;
        }

        $activeAnalyteId = count($this->activeTabs) > 0 ? $this->activeTabs[0] : null;
        if ($activeAnalyteId) {
            $cacheKey = "worksheet_selection_{$this->batchId}_{$this->selectedWorksheetId}_{$activeAnalyteId}_" . \Illuminate\Support\Facades\Auth::id();
            \Illuminate\Support\Facades\Cache::put($cacheKey, array_map('intval', $this->selectedSamples), now()->addDays(7));
        }

        // Only sync when there are selected samples; otherwise do nothing and let the UI warn.
        if (empty($this->selectedSamples)) {
            return;
        }

        // Re-load the relevant captured results + config fields for the active worksheet.
        $capturedResults = CapturedResult::query()
            ->whereIn('analyte_id', $this->activeTabs)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->where(function ($q) {
                $q->where('sample_header_id', $this->batchId)
                    ->orWhereIn('id', $this->externalCapturedResultIds);
            })
            ->get();

        $configFields = ProcedureConfigField::where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->orderBy('order')
            ->get();

        $this->syncLabNoConfigFieldToSelectedSamples($capturedResults, $configFields);

        // Re-load the full worksheet samples + kit state.
        // This ensures test-kit rows/values are correctly re-scoped to the new
        // selected captured_result_id and avoids showing blank rows.
        $this->loadSamples();
        return;
    }

    private function syncLabNoConfigFieldToSelectedSamples(Collection $capturedResults, Collection $configFields): void
    {
        // In the UI, config fields are bound to the first selected captured_result_id.
        $selectedCr = $capturedResults
            ->filter(fn($r) => $r->sample && in_array($r->sample->id, $this->selectedSamples))
            ->first();

        if (! $selectedCr) {
            return;
        }

        $labNoFields = $configFields->filter(function (ProcedureConfigField $field) {
            $label = strtolower((string) $field->label);
            $valueName = strtolower((string) $field->field_value_name);

            return $field->model_tied_to === 'sample_details'
                && (
                    $valueName === 'lab_no' ||
                    str_contains($label, 'lab no') ||
                    str_contains($label, 'lab no.')
                );
        });

        if ($labNoFields->isEmpty()) {
            return;
        }

        $selectedSampleIds = array_map('strval', $this->selectedSamples);

        foreach ($labNoFields as $field) {
            if ($field->field_type === 'dataset_multiselect') {
                $this->configFieldValues[$selectedCr->id][$field->id] = $selectedSampleIds;
                $this->dispatch('syncConfigFieldSelect', capturedResultId: $selectedCr->id, fieldId: $field->id, values: $selectedSampleIds);
            } else {
                // Single select: if multiple samples are selected, pick the first.
                $value = $selectedSampleIds[0] ?? '';
                $this->configFieldValues[$selectedCr->id][$field->id] = $value;
                $this->dispatch('syncConfigFieldSelect', capturedResultId: $selectedCr->id, fieldId: $field->id, values: [$value]);
            }
        }
    }

    public function getAnalysisSamplesProperty()
    {
        if (empty($this->activeTabs) || !$this->selectedWorksheetId) return collect();

        $query = CapturedResult::query()
            ->whereIn('analyte_id', $this->activeTabs)
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

    /**
     * Other worksheet/element combinations in this batch that have step values to import.
     */
    public function getImportableSourcesProperty(): Collection
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return collect();
        }

        $currentAnalyteIds = $this->analysisSamples->pluck('analyte_id')->filter()->unique()->values();
        if ($currentAnalyteIds->count() !== 1) {
            return collect();
        }

        $activeAnalyteId = (int) $currentAnalyteIds->first();

        // Get all other captured results in same batch that have a worksheet assigned,
        // grouping by worksheet + analyte combination
        $sources = CapturedResult::query()
            ->where('sample_header_id', $this->batchId)
            ->whereNotNull('procedure_worksheet_id')
            ->where(function ($q) use ($activeAnalyteId) {
                $q->where('analyte_id', '!=', $activeAnalyteId)
                    ->orWhere('procedure_worksheet_id', '!=', $this->selectedWorksheetId);
            })
            ->with(['my_analyte', 'procedureWorksheet'])
            ->get();

        // Filter and uniquely group by worksheet_id + analyte_id
        $uniqueSources = [];
        foreach ($sources as $source) {
            if (!$source->procedureWorksheet || !$source->my_analyte) continue;
            $key = $source->procedure_worksheet_id . '-' . $source->analyte_id;
            if (!isset($uniqueSources[$key])) {
                $uniqueSources[$key] = [
                    'worksheet_id' => $source->procedure_worksheet_id,
                    'analyte_id' => $source->analyte_id,
                    'worksheet_name' => $source->procedureWorksheet->name,
                    'analyte_name' => $source->my_analyte->name,
                ];
            }
        }

        return collect(array_values($uniqueSources));
    }

    /**
     * Captured result IDs for the currently selected samples (for shared-value spread on save).
     *
     * @return array<int>
     */
    public function getSelectedCapturedResultIds(): array
    {
        $samples = $this->analysisSamples;
        $selected = $samples->filter(fn($r) => $r->sample && in_array($r->sample->id, $this->selectedSamples));

        return $selected->pluck('id')->values()->all();
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
     * Test kit rows in row_index order for display.
     *
     * @return array<int, array{id: int, row_index: int}>
     */
    public function getOrderedTestKitRowsProperty(): array
    {
        if (empty($this->testKitRows)) {
            return [];
        }
        $out = [];
        foreach ($this->testKitRows as $id => $meta) {
            $out[] = ['id' => (int) $id, 'row_index' => (int) ($meta['row_index'] ?? 0)];
        }
        usort($out, fn($a, $b) => $a['row_index'] <=> $b['row_index']);

        return $out;
    }

    public function addTestKitRow(): void
    {
        if (!$this->selectedWorksheetId) {
            return;
        }

        $selectedCapturedResultIds = $this->getSelectedCapturedResultIds();
        if (empty($selectedCapturedResultIds)) {
            return;
        }

        $firstCapturedResultId = (int) $selectedCapturedResultIds[0];

        $maxIndex = 0;
        foreach ($this->testKitRows as $meta) {
            $maxIndex = max($maxIndex, (int) ($meta['row_index'] ?? 0));
        }

        $newRowIndex = $maxIndex + 1;

        $columns = $this->getTestKitColumnsProperty();

        $displayRow = null;
        foreach ($selectedCapturedResultIds as $capturedResultId) {
            $capturedResultId = (int) $capturedResultId;

            $instanceRow = ProcedureTestKitRow::firstOrCreate(
                [
                    'procedure_worksheet_id' => $this->selectedWorksheetId,
                    'captured_result_id' => $capturedResultId,
                    'row_index' => $newRowIndex,
                ],
                [
                    'row_index' => $newRowIndex,
                ]
            );

            if ($capturedResultId === $firstCapturedResultId) {
                $displayRow = $instanceRow;
            }
        }

        if (! $displayRow) {
            return;
        }

        $this->testKitRows[$displayRow->id] = ['row_index' => $displayRow->row_index];
        $this->testKitData[$displayRow->id] = [];
        foreach ($columns as $col) {
            $this->testKitData[$displayRow->id][$col->id] = '';
        }
    }

    public function updatedTestKitData($value, $key): void
    {
        // $key comes in as "rowId.columnId"
        $parts = explode('.', (string) $key);
        if (count($parts) !== 2) {
            return;
        }
        $rowId = (int) $parts[0];
        $this->autosaveTestKitRow($rowId);
    }

    protected function autosaveTestKitRow(int $rowId): void
    {
        if (! $this->selectedWorksheetId) {
            return;
        }

        if (! isset($this->testKitRows[$rowId]) || ! isset($this->testKitData[$rowId])) {
            return;
        }

        // Prevent stale updates from moving test-kit rows across worksheets.
        // The UI should only ever autosave rows that already belong to the
        // currently selected worksheet.
        $rowBelongsToWorksheet = ProcedureTestKitRow::where('id', $rowId)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->exists();

        if (! $rowBelongsToWorksheet) {
            return;
        }

        // Ensure the row exists with the correct worksheet and index.
        $meta = $this->testKitRows[$rowId];

        // Apply the typed value to all currently selected sample instances.
        $selectedCapturedResultIds = $this->getSelectedCapturedResultIds();
        if (empty($selectedCapturedResultIds)) {
            return;
        }

        $rowIndex = (int) ($meta['row_index'] ?? 0);

        foreach ($selectedCapturedResultIds as $capturedResultId) {
            $capturedResultId = (int) $capturedResultId;

            // Ensure the row exists for this instance.
            $instanceRow = ProcedureTestKitRow::firstOrCreate(
                [
                    'procedure_worksheet_id' => $this->selectedWorksheetId,
                    'captured_result_id' => $capturedResultId,
                    'row_index' => $rowIndex,
                ],
                [
                    'row_index' => $rowIndex,
                ]
            );

            foreach ($this->testKitData[$rowId] as $columnId => $val) {
                ProcedureTestKitValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_test_kit_row_id' => $instanceRow->id,
                        'procedure_test_kit_column_id' => (int) $columnId,
                    ],
                    [
                        'value' => $val,
                    ]
                );
            }
        }

        // At least one worksheet value now exists for this worksheet/parameter,
        // so flag it as "posted" for the UI.
        $this->worksheetAlreadyPosted = true;
    }

    public function removeTestKitRow(int $rowId): void
    {
        $rowMeta = $this->testKitRows[$rowId] ?? null;
        $rowIndex = $rowMeta['row_index'] ?? null;

        $selectedCapturedResultIds = $this->getSelectedCapturedResultIds();
        if (empty($selectedCapturedResultIds)) {
            $selectedCapturedResultIds = [];
        }

        // Remove values for selected instances.
        if (! empty($selectedCapturedResultIds)) {
            ProcedureTestKitValue::where('procedure_test_kit_row_id', $rowId)
                ->whereIn('captured_result_id', array_map('intval', $selectedCapturedResultIds))
                ->delete();
        }

        // Also remove instance rows if they were created for selected captured results.
        if ($rowIndex !== null) {
            foreach ($selectedCapturedResultIds as $capturedResultId) {
                $capturedResultId = (int) $capturedResultId;
                $instanceRow = ProcedureTestKitRow::where('procedure_worksheet_id', $this->selectedWorksheetId)
                    ->where('captured_result_id', $capturedResultId)
                    ->where('row_index', (int) $rowIndex)
                    ->first();

                if ($instanceRow) {
                    ProcedureTestKitValue::where('procedure_test_kit_row_id', $instanceRow->id)->delete();
                    $instanceRow->delete();
                }
            }
        }

        unset($this->testKitRows[$rowId], $this->testKitData[$rowId]);
    }

    /**
     * Get options for dataset-type config fields. Returns a collection of objects with id and label.
     * Sample types are limited to the batch's sample type; methods to those used in selected samples' captured results.
     */
    public function getDatasetOptions(string $modelTiedTo, ?ProcedureConfigField $field = null): Collection
    {
        $samples = $this->analysisSamples;
        $selectedSamplesFilter = $this->selectedSamples;
        $filteredSamples = $samples->filter(fn($r) => $r->sample && in_array($r->sample->id, $selectedSamplesFilter))->values();
        if ($filteredSamples->isEmpty()) {
            $filteredSamples = $samples;
        }

        return match ($modelTiedTo) {
            'users' => User::where('active', 1)
                ->orderBy('name')
                ->get()
                ->map(fn($u) => (object) ['id' => (string) $u->id, 'label' => $u->name ?: $u->email]),
            'sample_details' => $this->getSampleDetailsDatasetOptions($samples),
            'sample_types' => $this->getSampleTypesDatasetOptionsForBatch(),
            'methods' => $this->getMethodsDatasetOptionsForSelectedSamples($filteredSamples),
            'captured_results' => $this->getCapturedResultsDatasetOptions($samples),
            'report_formats' => ReportFormat::active()
                ->orderBy('report_name')
                ->get()
                ->map(fn($r) => (object) ['id' => (string) $r->id, 'label' => $r->report_name ?: $r->report_code]),
            default => collect(),
        };
    }

    /**
     * Sample types used in this batch (batch has one sample type; return that one so the list is short).
     */
    protected function getSampleTypesDatasetOptionsForBatch(): Collection
    {
        $header = SampleHeader::find($this->batchId);
        if (! $header || ! $header->sample_type_id) {
            return collect();
        }
        $type = SampleType::find($header->sample_type_id);

        return $type ? collect([(object) ['id' => (string) $type->id, 'label' => $type->name]]) : collect();
    }

    /**
     * Methods used in the captured results for the (selected) samples.
     */
    protected function getMethodsDatasetOptionsForSelectedSamples(Collection $samples): Collection
    {
        // Derive methods via analytes, since analytes carry method IDs (possibly comma-separated).
        $analyteIds = $samples->pluck('analyte_id')->filter()->unique()->values();
        if ($analyteIds->isEmpty()) {
            return collect();
        }

        $analytes = Analyte::whereIn('id', $analyteIds)->get(['id', 'method']);

        $methodIds = collect();
        foreach ($analytes as $analyte) {
            if (! $analyte->method) {
                continue;
            }

            $ids = collect(explode(',', (string) $analyte->method))
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => $v !== '');

            $methodIds = $methodIds->merge($ids);
        }

        $methodIds = $methodIds->unique()->values();

        if ($methodIds->isEmpty()) {
            return collect();
        }

        return AnalysisMethod::whereIn('id', $methodIds)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => (object) ['id' => (string) $m->id, 'label' => $m->name]);
    }

    protected function getSampleDetailsDatasetOptions(Collection $analysisSamples): Collection
    {
        $sampleDetailIds = $analysisSamples->pluck('sample_detail_id')->filter()->unique()->values();
        if ($sampleDetailIds->isEmpty()) {
            return collect();
        }
        return SampleDetails::whereIn('id', $sampleDetailIds)
            ->orderBy('sample_code')
            ->get()
            ->map(fn($s) => (object) ['id' => (string) $s->id, 'label' => $s->sample_code ?? (string) $s->id]);
    }

    protected function getCapturedResultsDatasetOptions(Collection $analysisSamples): Collection
    {
        return CapturedResult::with('my_analyte')
            ->where('sample_header_id', $this->batchId)
            ->get()
            ->map(function ($cr) {
                $name = $cr->my_analyte ? $cr->my_analyte->name : '—';
                $id = $cr->analyte_id ? (string) $cr->analyte_id : '';
                return (object) ['id' => $id, 'label' => $name];
            })
            ->filter(fn($obj) => $obj->id !== '')
            ->unique('id')
            ->values();
    }

    /**
     * Get the current worksheet model for document control display.
     */
    public function getSelectedWorksheetProperty(): ?ProcedureWorksheet
    {
        if (!$this->selectedWorksheetId) {
            return null;
        }
        return ProcedureWorksheet::find($this->selectedWorksheetId);
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

        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
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
            ->whereIn('cr.analyte_id', $this->activeTabs)
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
            fn($item) => (int) $item['id'] !== $id
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

        $ids = array_map(fn($item) => (int) $item['id'], $this->externalSelectionItems);
        $this->externalCapturedResultIds = array_values(array_unique(array_merge(
            $this->externalCapturedResultIds,
            $ids
        )));

        // Also auto-select the corresponding external sample_details so:
        // 1) the UI checkboxes show them as included,
        // 2) `selectedSamples` is updated (so PDF sample filtering includes them),
        // 3) the selection cache is updated for this worksheet/analyte tab.
        $additionalSampleIds = CapturedResult::query()
            ->whereIn('id', $ids)
            ->pluck('sample_detail_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->selectedSamples = array_values(array_unique(array_merge(
            $this->selectedSamples,
            $additionalSampleIds
        )));

        $activeAnalyteId = count($this->activeTabs) > 0 ? (int) $this->activeTabs[0] : null;
        if ($activeAnalyteId && $this->selectedWorksheetId) {
            $cacheKey = "worksheet_selection_{$this->batchId}_{$this->selectedWorksheetId}_{$activeAnalyteId}_" . \Illuminate\Support\Facades\Auth::id();
            \Illuminate\Support\Facades\Cache::put($cacheKey, array_map('intval', $this->selectedSamples), now()->addDays(7));

            // Persist external captured results selection too (so it survives reload).
            $externalKey = "worksheet_external_cr_{$this->batchId}_{$this->selectedWorksheetId}_{$activeAnalyteId}_" . \Illuminate\Support\Facades\Auth::id();
            \Illuminate\Support\Facades\Cache::put($externalKey, array_map('intval', $this->externalCapturedResultIds), now()->addDays(7));
            $this->externalSelectionCacheKeyLoaded = $externalKey;
        }

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

    /**
     * Open the PDF preview for the currently selected worksheet in a new tab.
     *
     * We generate the URL at click-time (server-side) to avoid stale hrefs
     * when switching parameter/worksheet tabs.
     */
    public function previewProcedureWorksheetPdf(): void
    {
        if (empty($this->selectedWorksheetId)) {
            return;
        }

        $url = route('batch-worksheets.procedure-preview', [
            'batch' => $this->batchId,
            'worksheet' => $this->selectedWorksheetId,
        ]);

        $this->dispatch('openProcedureWorksheetPreview', url: $url);
    }

    /**
     * Clear all saved analysts for the current worksheet in this batch.
     * This wipes analyst_ids on CapturedProcedureValue for the relevant captured results
     * and resets the in-memory overrides, but leaves step values and other overrides intact.
     */
    public function clearAnalystsForWorksheet(): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return;
        }

        $query = CapturedResult::query()
            ->whereIn('analyte_id', $this->activeTabs)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId);

        // Restrict to this batch plus any explicitly-added external results
        if (! empty($this->externalCapturedResultIds)) {
            $query->where(function ($inner) {
                $inner->where('sample_header_id', $this->batchId)
                    ->orWhereIn('id', $this->externalCapturedResultIds);
            });
        } else {
            $query->where('sample_header_id', $this->batchId);
        }

        $capturedResultIds = $query->pluck('id');

        if ($capturedResultIds->isEmpty()) {
            $this->flashType = 'info';
            $this->flashMessage = 'No analysts to clear for this worksheet.';
            return;
        }

        // Determine which analytes are actually present for this worksheet+batch.
        $analyteIds = CapturedResult::whereIn('id', $capturedResultIds)
            ->pluck('analyte_id')
            ->filter()
            ->unique()
            ->values();

        // Delete worksheet-step level analysts only for those analytes, keeping other
        // elements' worksheets intact.
        ProcedureWorksheetStepAnalyst::where('batch_id', $this->batchId)
            ->whereIn('analyte_id', $analyteIds)
            ->where('procedure_worksheet_id', $this->selectedWorksheetId)
            ->delete();

        $this->stepAnalystOverrides = [];

        $this->flashType = 'success';
        $this->flashMessage = 'All analysts for this worksheet have been cleared.';

        // Reload samples/overrides so the UI immediately reflects the cleared analysts.
        $this->loadSamples();

        // Also force all analyst Select2 widgets to clear their selection (wire:ignore prevents
        // automatic DOM updates when we change the underlying state).
        foreach ($this->getStepsProperty() as $step) {
            $this->dispatch('syncStepAnalystSelect', stepId: $step->id, analystIds: []);
        }
    }

    public function save()
    {
        $this->validate([
            'inputValues.*.*' => 'nullable', // string or array (per-measurand map)
            'configFieldValues.*.*' => 'nullable', // string or array (for dataset_multiselect)
            'testKitData.*.*' => 'nullable|string',
        ]);

        // When multiple samples are selected, one shared value applies to all: copy first result's values to the rest
        $selectedIds = $this->getSelectedCapturedResultIds();
        if (count($selectedIds) > 1) {
            $firstId = (int) $selectedIds[0];
            foreach ($this->getStepsProperty() as $step) {
                $value = $this->inputValues[$firstId][$step->id] ?? '';
                foreach ($selectedIds as $id) {
                    $id = (int) $id;
                    if ($id === $firstId) {
                        continue;
                    }
                    if (! isset($this->inputValues[$id])) {
                        $this->inputValues[$id] = [];
                    }
                    $this->inputValues[$id][$step->id] = $value;
                }
            }
            foreach ($this->getConfigFieldsProperty() as $field) {
                $value = $this->configFieldValues[$firstId][$field->id] ?? '';
                foreach ($selectedIds as $id) {
                    $id = (int) $id;
                    if ($id === $firstId) {
                        continue;
                    }
                    if (! isset($this->configFieldValues[$id])) {
                        $this->configFieldValues[$id] = [];
                    }
                    $this->configFieldValues[$id][$field->id] = $value;
                }
            }
        }

        foreach ($this->inputValues as $capturedResultId => $steps) {
            $capturedResultId = (int) $capturedResultId;
            if (! in_array($capturedResultId, $selectedIds) || ! is_array($steps)) {
                continue;
            }
            foreach ($steps as $stepId => $value) {
                $stepId = (int) $stepId;
                $valueToStore = is_array($value) ? json_encode($value) : ($value ?? '');
                CapturedProcedureValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'value' => $valueToStore,
                    ]
                );
            }
        }

        foreach ($this->configFieldValues as $capturedResultId => $fields) {
            $capturedResultId = (int) $capturedResultId;
            if (! in_array($capturedResultId, $selectedIds) || ! is_array($fields)) {
                continue;
            }
            foreach ($fields as $fieldId => $value) {
                $fieldId = (int) $fieldId;
                $valueToStore = is_array($value) ? implode(',', $value) : ($value ?? '');
                CapturedProcedureConfigValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'procedure_config_field_id' => $fieldId,
                    ],
                    [
                        'value' => $valueToStore,
                    ]
                );
            }
        }

        // Save test kit values (per sample instances selected in this UI).
        $selectedIds = $this->getSelectedCapturedResultIds();
        if (empty($selectedIds)) {
            $selectedIds = [];
        }

        foreach ($this->testKitRows as $rowId => $rowMeta) {
            if (! isset($this->testKitData[$rowId])) {
                continue;
            }

            if (empty($selectedIds)) {
                // No selected samples: do not persist kit values.
                continue;
            }

            $rowIndex = (int) ($rowMeta['row_index'] ?? 0);

            foreach ($selectedIds as $capturedResultId) {
                $capturedResultId = (int) $capturedResultId;
                $instanceRow = ProcedureTestKitRow::firstOrCreate(
                    [
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'captured_result_id' => $capturedResultId,
                        'row_index' => $rowIndex,
                    ],
                    [
                        'row_index' => $rowIndex,
                    ]
                );

                foreach ($this->testKitData[$rowId] as $columnId => $value) {
                    ProcedureTestKitValue::updateOrCreate(
                        [
                            'captured_result_id' => $capturedResultId,
                            'procedure_test_kit_row_id' => $instanceRow->id,
                            'procedure_test_kit_column_id' => (int) $columnId,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
            }
        }

        // Save step overrides (measurands, equipment) back to the template.
        // Analysts stay per-worksheet/per-run only (via CapturedProcedureValue), so we
        // intentionally do NOT write analyst overrides back to the step defaults here.
        $steps = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)->get();
        foreach ($steps as $step) {
            $changed = false;

            if (isset($this->stepEquipmentOverrides[$step->id])) {
                $step->default_equipment_id = $this->stepEquipmentOverrides[$step->id];
                $changed = true;
            }

            if (isset($this->stepMeasurandOverrides[$step->id])) {
                $step->default_measurand_ids = $this->stepMeasurandOverrides[$step->id];
                $changed = true;
            }

            if ($changed) {
                $step->save();
            }
        }

        $this->flashType = 'success';
        $this->flashMessage = 'Worksheet values saved successfully.';
    }

    public function autosaveStepOverride(int $stepId): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            return;
        }

        $selectedIds = $this->getSelectedCapturedResultIds();
        if (count($selectedIds) === 0) {
            return;
        }

        $equipments = $this->stepEquipmentOverrides[$stepId] ?? null;
        $measurands = $this->stepMeasurandOverrides[$stepId] ?? null;
        $analysts = $this->stepAnalystOverrides[$stepId] ?? null;

        foreach ($selectedIds as $capturedResultId) {
            $val = CapturedProcedureValue::firstOrNew([
                'captured_result_id' => $capturedResultId,
                'procedure_worksheet_step_id' => $stepId,
            ]);
            if ($equipments !== null) {
                $val->equipment_ids = $equipments;
            }
            if ($measurands !== null) {
                $val->measurand_ids = $measurands;
            }
            $val->save();
        }

        // Persist analysts at the worksheet-step level (shared across all selected samples).
        $analysisSamples = $this->analysisSamples;
        $currentAnalyteIds = $analysisSamples->pluck('analyte_id')->filter()->unique()->values();
        $activeAnalyteId = $currentAnalyteIds->count() === 1 ? $currentAnalyteIds->first() : null;

        if ($analysts !== null && $activeAnalyteId && $this->selectedWorksheetId) {
            ProcedureWorksheetStepAnalyst::updateOrCreate(
                [
                    'batch_id' => $this->batchId,
                    'analyte_id' => $activeAnalyteId,
                    'procedure_worksheet_id' => $this->selectedWorksheetId,
                    'procedure_worksheet_step_id' => $stepId,
                ],
                [
                    'analyst_ids' => $analysts,
                ]
            );
        }

        // Ensure any newly selected measurands receive configured default
        // values immediately (and are persisted for PDF output).
        $this->seedDefaultStepValuesForStepId($stepId, $selectedIds);
    }

    /**
     * Import step values from another analyte that uses the same
     * procedure worksheet for the currently selected samples.
     *
     * Only step values are imported; config fields and overrides
     * remain as configured for the current analyte.
     */
    /**
     * Import mapped data from another Worksheet/Analyte combo across the same batch samples.
     */
    public function importDataFromSource(int $sourceWorksheetId, int $fromAnalyteId): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            $this->flashType = 'warning';
            $this->flashMessage = 'Select a parameter and worksheet before importing data.';
            return;
        }

        if (empty($this->selectedSamples)) {
            $this->flashType = 'warning';
            $this->flashMessage = 'Select at least one sample before importing data.';
            return;
        }

        $currentAnalyteIds = $this->analysisSamples->pluck('analyte_id')->filter()->unique()->values();
        if ($currentAnalyteIds->count() !== 1) {
            $this->flashType = 'warning';
            $this->flashMessage = 'Import is only available when a single parameter is active.';
            return;
        }

        $activeAnalyteId = (int) $currentAnalyteIds->first();
        $sourceWorksheetId = (int) $sourceWorksheetId;
        $fromAnalyteId = (int) $fromAnalyteId;

        if ($fromAnalyteId === $activeAnalyteId && $sourceWorksheetId === (int)$this->selectedWorksheetId) {
            $this->flashType = 'info';
            $this->flashMessage = 'Selected parameter and worksheet is already active; nothing to import.';
            return;
        }

        $samples = $this->analysisSamples;
        $selectedSampleIds = array_map('intval', $this->selectedSamples);

        $targetBySample = $samples
            ->filter(fn($r) => $r->sample && (int) $r->analyte_id === $activeAnalyteId && in_array((int) $r->sample->id, $selectedSampleIds, true))
            ->keyBy(fn($r) => (int) $r->sample->id);

        if ($targetBySample->isEmpty()) {
            $this->flashType = 'warning';
            $this->flashMessage = 'No samples found for the active parameter to receive imported data.';
            return;
        }

        $sourceResults = CapturedResult::query()
            ->where('sample_header_id', $this->batchId)
            ->where('procedure_worksheet_id', $sourceWorksheetId)
            ->where('analyte_id', $fromAnalyteId)
            ->with('sample')
            ->get();

        if ($sourceResults->isEmpty()) {
            $this->flashType = 'warning';
            $this->flashMessage = 'No existing data found to import from the selected source.';
            return;
        }

        $sourceBySample = $sourceResults
            ->filter(fn($r) => $r->sample)
            ->keyBy(fn($r) => (int) $r->sample->id);

        $targetSteps = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)->get();
        if ($targetSteps->isEmpty()) {
            $this->flashType = 'warning';
            $this->flashMessage = 'No steps defined for this worksheet to receive imported data.';
            return;
        }

        $sourceStepIds = ProcedureWorksheetStep::where('procedure_worksheet_id', $sourceWorksheetId)->pluck('id');
        $sourceSteps = ProcedureWorksheetStep::whereIn('id', $sourceStepIds)->get()->keyBy('id');

        $importedCount = 0;
        $importedStepIds = [];

        foreach ($selectedSampleIds as $sampleId) {
            $sampleId = (int) $sampleId;
            // Fallback to first source sample if not exactly matched by sample ID
            $sourceCr = $sourceBySample->get($sampleId) ?? $sourceResults->first();
            $targetCr = $targetBySample->get($sampleId);

            if (! $sourceCr || ! $targetCr) continue;

            $sourceValues = CapturedProcedureValue::where('captured_result_id', $sourceCr->id)->get();

            foreach ($sourceValues as $srcVal) {
                $sourceStepInfo = $sourceSteps->get($srcVal->procedure_worksheet_step_id);
                if (!$sourceStepInfo) continue;

                $matchedTarget = $targetSteps->firstWhere('step', $sourceStepInfo->step);
                if (!$matchedTarget && $this->selectedWorksheetId == $sourceWorksheetId) {
                    $matchedTarget = $targetSteps->firstWhere('id', $srcVal->procedure_worksheet_step_id);
                }

                if ($matchedTarget) {
                    $valueToStore = $srcVal->value;
                    CapturedProcedureValue::updateOrCreate(
                        [
                            'captured_result_id' => $targetCr->id,
                            'procedure_worksheet_step_id' => $matchedTarget->id,
                        ],
                        [
                            'value' => $valueToStore,
                        ]
                    );

                    $decoded = null;
                    if (is_string($valueToStore)) {
                        $trimmed = trim($valueToStore);
                        if ($trimmed !== '' && $trimmed[0] === '{') {
                            $maybe = json_decode($trimmed, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) $decoded = $maybe;
                        }
                    }

                    if (! isset($this->inputValues[$targetCr->id])) $this->inputValues[$targetCr->id] = [];
                    $this->inputValues[$targetCr->id][$matchedTarget->id] = $decoded ?? $valueToStore;
                    $importedCount++;
                    $importedStepIds[$matchedTarget->id] = true;
                }
            }
        }

        $userId = Auth::id();
        if ($userId && !empty($importedStepIds)) {
            $newAnalysts = [(string) $userId];
            foreach (array_keys($importedStepIds) as $stepId) {
                ProcedureWorksheetStepAnalyst::updateOrCreate(
                    [
                        'batch_id' => $this->batchId,
                        'analyte_id' => $activeAnalyteId,
                        'procedure_worksheet_id' => $this->selectedWorksheetId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'analyst_ids' => $newAnalysts,
                    ]
                );
                $this->stepAnalystOverrides[$stepId] = $newAnalysts;
                $this->dispatch('syncStepAnalystSelect', stepId: $stepId, analystIds: $newAnalysts);
            }
        }

        $this->loadSamples();
        $this->importHash = Str::random(8);

        $this->flashType = 'success';
        if ($importedCount > 0) {
            $this->flashMessage = 'Imported step values successfully mapped for ' . $importedCount . ' step(s).';
        } else {
            $this->flashMessage = 'Action completed, but no matching steps were found to import values into.';
        }
    }

    /**
     * Post results for the current procedure worksheet:
     * - Set analyst (operator) and method on captured results from shared config fields.
     * - Persist captured results (which triggers TAT creation/updates via CapturedObserver).
     * - Generate a Procedure Worksheet PDF and store it as a batch attachment.
     */
    public function postResults(): void
    {
        if (empty($this->activeTabs) || ! $this->selectedWorksheetId) {
            $this->flashType = 'warning';
            $this->flashMessage = 'Select at least one parameter and a procedure worksheet before posting results.';
            return;
        }

        $selectedIds = $this->getSelectedCapturedResultIds();
        if (empty($selectedIds)) {
            $this->flashType = 'warning';
            $this->flashMessage = 'Select at least one sample before posting results.';
            return;
        }

        $configFields = $this->getConfigFieldsProperty();
        $firstId = (int) $selectedIds[0];

        // Resolve analyst and method config fields (if configured on this worksheet)
        $analystField = $configFields->first(function (ProcedureConfigField $field) {
            return $field->model_tied_to === 'users';
        });

        $methodField = $configFields->first(function (ProcedureConfigField $field) {
            return $field->model_tied_to === 'methods';
        });

        $analystId = null;
        if ($analystField) {
            $analystValue = $this->configFieldValues[$firstId][$analystField->id] ?? null;
            // Dataset fields store a single scalar value; multiselect would store array.
            if (is_array($analystValue)) {
                $analystId = count($analystValue) > 0 ? (int) $analystValue[0] : null;
            } elseif ($analystValue !== null && $analystValue !== '') {
                $analystId = (int) $analystValue;
            }
        }

        $methodId = null;
        if ($methodField) {
            $methodValue = $this->configFieldValues[$firstId][$methodField->id] ?? null;
            if (is_array($methodValue)) {
                $methodId = count($methodValue) > 0 ? (int) $methodValue[0] : null;
            } elseif ($methodValue !== null && $methodValue !== '') {
                $methodId = (int) $methodValue;
            }
        }

        if (! $analystId && ! $methodId) {
            // Nothing to update; avoid touching captured results.
            $this->flashType = 'warning';
            $this->flashMessage = 'No analyst or method selected for this procedure worksheet.';
            return;
        }

        DB::beginTransaction();

        try {
            // Update captured results for the selected samples / analyte / worksheet.
            $capturedResults = CapturedResult::whereIn('id', $selectedIds)->get();

            foreach ($capturedResults as $captured) {
                if ($analystId) {
                    $captured->operator_id = $analystId;
                }

                if ($methodId) {
                    $captured->method_id = $methodId;
                }

                // Saving will trigger CapturedObserver::updated(), which handles TAT.
                $captured->save();
            }

            // Flag these captured results as having their worksheet posted.
            CapturedResult::whereIn('id', $selectedIds)
                ->update(['worksheet_posted' => true]);

            DB::commit();

            $this->worksheetAlreadyPosted = true;
            $this->flashType = 'success';
            $this->flashMessage = 'Procedure worksheet results posted successfully.';
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error posting procedure worksheet results', [
                'batch_id' => $this->batchId,
                'worksheet_id' => $this->selectedWorksheetId,
                'error' => $e->getMessage(),
            ]);

            $this->flashType = 'error';
            $this->flashMessage = 'Error posting procedure worksheet results: ' . $e->getMessage();
        }
    }

    private function seedDefaultStepValuesForSelectedResults(\Illuminate\Support\Collection $steps, array $selectedResultIds): void
    {
        if (empty($selectedResultIds)) {
            return;
        }

        foreach ($steps as $step) {
            if ($step instanceof ProcedureWorksheetStep) {
                $this->seedDefaultStepValuesForStepModel($step, $selectedResultIds);
            }
        }
    }

    private function seedDefaultStepValuesForStepId(int $stepId, array $selectedResultIds): void
    {
        if (empty($selectedResultIds)) {
            return;
        }

        $step = ProcedureWorksheetStep::find($stepId);
        if (! $step) {
            return;
        }

        $this->seedDefaultStepValuesForStepModel($step, $selectedResultIds);
    }

    private function seedDefaultStepValuesForStepModel(ProcedureWorksheetStep $step, array $selectedResultIds): void
    {
        $stepId = (int) $step->id;
        $valueType = (string) ($step->value_type ?: 'text');

        $stepDefaultNormalized = $this->normalizeStepValueForInput(
            is_string($step->default_value ?? null) ? (string) $step->default_value : null,
            $valueType
        );

        $perMeasurandDefaultsRaw = is_array($step->default_measurand_values ?? null)
            ? $step->default_measurand_values
            : [];

        $perMeasurandDefaults = [];
        foreach ($perMeasurandDefaultsRaw as $measurandId => $rawValue) {
            $perMeasurandDefaults[(string) $measurandId] = $rawValue;
        }

        $hasAnyPerMeasurandDefaults = false;
        foreach ($perMeasurandDefaults as $rawValue) {
            $normalized = $this->normalizeStepValueForInput(
                is_string($rawValue) ? $rawValue : (string) $rawValue,
                $valueType
            );
            if ($normalized !== null && trim((string) $normalized) !== '') {
                $hasAnyPerMeasurandDefaults = true;
                break;
            }
        }

        if (
            ($stepDefaultNormalized === null || trim((string) $stepDefaultNormalized) === '')
            && ! $hasAnyPerMeasurandDefaults
        ) {
            return;
        }

        $measurandIds = $this->stepMeasurandOverrides[$stepId] ?? [];

        foreach ($selectedResultIds as $capturedResultId) {
            $capturedResultId = (int) $capturedResultId;

            $current = $this->inputValues[$capturedResultId][$stepId] ?? null;
            $changed = false;

            if (!empty($measurandIds)) {
                // UI expects a per-measurand value map when measurands are selected.
                if (is_array($current)) {
                    foreach ($measurandIds as $mId) {
                        $mId = (string) $mId;
                        $existing = $current[$mId] ?? null;

                        if ($existing === null || trim((string) ($existing ?? '')) === '') {
                            $perRaw = $perMeasurandDefaults[$mId] ?? null;
                            $perNormalized = $this->normalizeStepValueForInput(
                                $perRaw === null ? null : (is_string($perRaw) ? $perRaw : (string) $perRaw),
                                $valueType
                            );

                            $shouldUseStepDefault = $stepDefaultNormalized !== null
                                && trim((string) $stepDefaultNormalized) !== '';

                            if ($perNormalized !== null && trim((string) $perNormalized) !== '') {
                                $current[$mId] = $perNormalized;
                                $changed = true;
                                continue;
                            }

                            if ($shouldUseStepDefault) {
                                $current[$mId] = $stepDefaultNormalized;
                                $changed = true;
                            }

                            // Else: leave it empty (means "keep blank" for this measurand).
                        }

                        $normalized = $this->normalizeStepValueForInput((string) $existing, $valueType);
                        if ($normalized !== null && $normalized !== (string) $existing) {
                            $current[$mId] = $normalized;
                        }
                    }
                } else {
                    // If current value is scalar (legacy/mismatch), preserve it if non-empty.
                    $scalarStr = $current === null ? '' : (string) $current;
                    $scalarStrTrim = trim($scalarStr);

                    $map = [];

                    if ($scalarStrTrim !== '') {
                        $mapValue = $this->normalizeStepValueForInput($scalarStrTrim, $valueType) ?? $scalarStrTrim;
                        foreach ($measurandIds as $mId) {
                            $map[(string) $mId] = $mapValue;
                        }
                        $changed = true;
                    } else {
                        $shouldUseStepDefault = $stepDefaultNormalized !== null
                            && trim((string) $stepDefaultNormalized) !== '';

                        foreach ($measurandIds as $mId) {
                            $mId = (string) $mId;
                            $perRaw = $perMeasurandDefaults[$mId] ?? null;
                            $perNormalized = $this->normalizeStepValueForInput(
                                $perRaw === null ? null : (is_string($perRaw) ? $perRaw : (string) $perRaw),
                                $valueType
                            );

                            if ($perNormalized !== null && trim((string) $perNormalized) !== '') {
                                $map[$mId] = $perNormalized;
                                $changed = true;
                                continue;
                            }

                            if ($shouldUseStepDefault) {
                                $map[$mId] = $stepDefaultNormalized;
                                $changed = true;
                            }
                        }
                    }

                    $current = $map;
                }
            } else {
                // UI expects a scalar when no measurands are selected.
                if (is_array($current)) {
                    $firstNonEmpty = '';
                    foreach ($current as $v) {
                        $vStr = $v === null ? '' : (string) $v;
                        if (trim($vStr) !== '') {
                            $firstNonEmpty = $vStr;
                            break;
                        }
                    }

                    $current = $firstNonEmpty;
                    $changed = $firstNonEmpty !== '';
                }

                $currentStr = $current === null ? '' : (string) $current;

                if (trim($currentStr) === '') {
                    $current = $stepDefaultNormalized;
                    $changed = true;
                } else {
                    $normalizedScalar = $this->normalizeStepValueForInput($currentStr, $valueType);
                    if ($normalizedScalar !== null && $normalizedScalar !== $currentStr) {
                        $current = $normalizedScalar;
                    }
                }
            }

            if (! isset($this->inputValues[$capturedResultId])) {
                $this->inputValues[$capturedResultId] = [];
            }

            $this->inputValues[$capturedResultId][$stepId] = $current;

            if ($changed) {
                $valueToStore = is_array($current) ? json_encode($current) : ($current ?? '');

                CapturedProcedureValue::updateOrCreate(
                    [
                        'captured_result_id' => $capturedResultId,
                        'procedure_worksheet_step_id' => $stepId,
                    ],
                    [
                        'value' => $valueToStore,
                    ]
                );
            }
        }
    }

    /**
     * Normalize a stored step value into the canonical format expected by the
     * HTML input controls we render for that step type.
     */
    private function normalizeStepValueForInput(?string $value, string $valueType): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return match ($valueType) {
            'date' => $this->normalizeDateValueForInput($value),
            'time' => $this->normalizeTimeValueForInput($value),
            'datetime' => $this->normalizeDateTimeValueForInput($value),
            'number', 'text' => $value,
            default => $value,
        };
    }

    private function normalizeDateValueForInput(string $value): string
    {
        if (\preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function normalizeTimeValueForInput(string $value): string
    {
        if (\preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }

        if (\preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            try {
                return \Carbon\Carbon::parse($value)->format('H:i');
            } catch (\Throwable) {
                return $value;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function normalizeDateTimeValueForInput(string $value): string
    {
        if (\preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }

        if (\preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i');
            } catch (\Throwable) {
                return $value;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * All active equipment for step equipment dropdowns.
     */
    public function getEquipmentOptionsProperty(): \Illuminate\Support\Collection
    {
        return \App\Models\Equipments\Equipment::orderBy('name')
            ->get()
            ->map(function ($e) {
                $number = $e->equipment_number ?? null;
                $label = $number
                    ? sprintf('%s (%s)', $e->name, $number)
                    : $e->name;

                return (object) [
                    'id' => (string) $e->id,
                    'label' => $label,
                ];
            });
    }

    /**
     * All reporting units (measurands) for the step measurand dropdowns.
     */
    public function getMeasurandOptionsProperty(): \Illuminate\Support\Collection
    {
        return \App\ReportingUnit::orderBy('name')
            ->get()
            ->map(fn($r) => (object) ['id' => (string) $r->id, 'label' => $r->name]);
    }

    /**
     * Active users for per-step analyst dropdowns.
     */
    public function getAnalystOptionsProperty(): \Illuminate\Support\Collection
    {
        return User::where('active', 1)
            ->orderBy('name')
            ->get()
            ->map(fn($u) => (object) ['id' => (string) $u->id, 'label' => $u->name ?: $u->email]);
    }
}
