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

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->initActiveTab();
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
        if (empty($this->activeTabs)) return collect();

        // Get unique procedure worksheets for the selected analytes in this batch
        $worksheetIds = CapturedResult::where('sample_header_id', $this->batchId)
            ->whereIn('analyte_id', $this->activeTabs)
            ->whereNotNull('procedure_worksheet_id')
            ->pluck('procedure_worksheet_id')
            ->unique();

        return ProcedureWorksheet::whereIn('id', $worksheetIds)->get();
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

        // Get samples for this analyte and worksheet (current + external)
        $samples = $capturedResults
            ->pluck('sample.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Default select all only when nothing is selected yet.
        // Otherwise, preserve the current selection but keep it within the available samples.
        if (empty($this->selectedSamples)) {
            $this->selectedSamples = $samples;
        } else {
            $this->selectedSamples = array_values(array_intersect($this->selectedSamples, $samples));
            if (empty($this->selectedSamples)) {
                $this->selectedSamples = $samples;
            }
        }

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
        // and pre-fill from the captured_result's own operator_id / method_id when nothing is saved yet.
        foreach ($capturedResults as $cr) {
            foreach ($configFields as $field) {
                $existing = $this->configFieldValues[$cr->id][$field->id] ?? null;
                $isEmpty = $existing === null || $existing === '' || $existing === [];

                if ($isEmpty) {
                    // Try to seed from the captured result's fields
                    if ($field->model_tied_to === 'users' && $cr->operator_id) {
                        $this->configFieldValues[$cr->id][$field->id] = (string) $cr->operator_id;
                    } elseif ($field->model_tied_to === 'methods' && $cr->method_id) {
                        $this->configFieldValues[$cr->id][$field->id] = (string) $cr->method_id;
                    } else {
                        $this->configFieldValues[$cr->id][$field->id] = $field->field_type === 'dataset_multiselect' ? [] : '';
                    }
                } elseif ($field->field_type === 'dataset_multiselect') {
                    $val = $this->configFieldValues[$cr->id][$field->id];
                    $this->configFieldValues[$cr->id][$field->id] = is_array($val)
                        ? $val
                        : array_values(array_filter(explode(',', (string) $val)));
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
        $columns = ProcedureTestKitColumn::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();
        foreach ($rows as $row) {
            if (! isset($this->testKitData[$row->id])) {
                $this->testKitData[$row->id] = [];
            }
            foreach ($columns as $col) {
                if (! array_key_exists($col->id, $this->testKitData[$row->id])) {
                    $this->testKitData[$row->id][$col->id] = '';
                }
            }
        }

        // Load default step equipment/measurand/analyst overrides (editable per-render)
        $steps = ProcedureWorksheetStep::where('procedure_worksheet_id', $this->selectedWorksheetId)->orderBy('order')->get();
        foreach ($steps as $step) {
            if (! isset($this->stepEquipmentOverrides[$step->id])) {
                $rawEq = $step->default_equipment_id;
                $eqIds = $rawEq === null || $rawEq === '' || $rawEq === 0
                    ? []
                    : (is_array($rawEq) ? $rawEq : [(string) $rawEq]);
                $this->stepEquipmentOverrides[$step->id] = array_map('strval', $eqIds);
            }
            if (! isset($this->stepMeasurandOverrides[$step->id])) {
                $this->stepMeasurandOverrides[$step->id] = is_array($step->default_measurand_ids)
                    ? array_map('strval', $step->default_measurand_ids)
                    : [];
            }
            if (! isset($this->stepAnalystOverrides[$step->id])) {
                $rawAn = $step->default_analyst_id;
                $anIds = $rawAn === null || $rawAn === '' || $rawAn === 0
                    ? []
                    : (is_array($rawAn) ? $rawAn : [(string) $rawAn]);
                $this->stepAnalystOverrides[$step->id] = array_map('strval', $anIds);
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
        $maxIndex = ProcedureTestKitRow::where('procedure_worksheet_id', $this->selectedWorksheetId)->max('row_index') ?? 0;
        $newRow = ProcedureTestKitRow::create([
            'procedure_worksheet_id' => $this->selectedWorksheetId,
            'row_index' => $maxIndex + 1,
        ]);
        $this->testKitRows[$newRow->id] = ['row_index' => $newRow->row_index];
        $columns = $this->getTestKitColumnsProperty();
        $this->testKitData[$newRow->id] = [];
        foreach ($columns as $col) {
            $this->testKitData[$newRow->id][$col->id] = '';
        }
    }

    public function removeTestKitRow(int $rowId): void
    {
        unset($this->testKitRows[$rowId], $this->testKitData[$rowId]);
        ProcedureTestKitValue::where('procedure_test_kit_row_id', $rowId)->delete();
        ProcedureTestKitRow::where('id', $rowId)->delete();
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
        return $analysisSamples->map(function ($cr) {
            $sampleCode = $cr->sample ? $cr->sample->sample_code : '—';
            $analyteName = $cr->my_analyte ? $cr->my_analyte->name : '—';
            return (object) ['id' => (string) $cr->id, 'label' => $sampleCode . ' – ' . $analyteName];
        })->values();
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

        // Save step overrides (measurands, equipment, analyst) back to the template
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

            if (isset($this->stepAnalystOverrides[$step->id])) {
                $step->default_analyst_id = $this->stepAnalystOverrides[$step->id];
                $changed = true;
            }

            if ($changed) {
                $step->save();
            }
        }

        $this->flashType = 'success';
        $this->flashMessage = 'Worksheet values saved successfully.';
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

            DB::commit();

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

    /**
     * All active equipment for step equipment dropdowns.
     */
    public function getEquipmentOptionsProperty(): \Illuminate\Support\Collection
    {
        return \App\Models\Equipments\Equipment::orderBy('name')
            ->get()
            ->map(fn($e) => (object) ['id' => (string) $e->id, 'label' => $e->name]);
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
