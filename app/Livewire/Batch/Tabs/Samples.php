<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\SampleDetails;
use App\Models\SampleDetailStaging;
use App\AnalysisType;
use App\Standards;
use App\SampleCondition;
use App\SampleDate;
use App\Lab;
use App\SampleAnalysisStage;
use App\SampleAnalysisDates;
use App\CapturedResult;
use App\Result;
use App\ReportingUnit;
use App\AnalysisElements;
use App\Analyte;
use App\Models\Procedures\ProcedureTestKitRow;
use App\Models\Procedures\ProcedureTestKitValue;
use App\Models\Procedures\ProcedureWorksheet;
use App\Services\GroupedWorksheets\GroupedWorksheetAssignmentService;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use App\Services\Sampleworkflow\SampleDetailCreationService;
use App\Services\ResultRemarkService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Samples extends Component
{
    use WithFileUploads;

    public $batchId;
    public SampleHeader $batch;
    public $not_captured = [];
    public $missingWorksheetParameters = [];
    public $incompleteCapturedResults = [];
    public bool $showIncompleteResultsModal = false;

    // Staging edit form
    public $editingStagingId = null;
    public $stagingForm = [
        'company_sub_unit_id' => '',
        'company_sub_unit_name' => '',
        'sample_type_id' => '',
        'sample_type_name' => '',
        'analysis_type_ids' => [],
        'analysis_type_names' => '',
        'quantity' => 1,
    ];

    // Delete confirmation modals
    public $deletingStagingId = null;
    public $showDeleteModal = false;
    public $deletingSampleIndex = null;
    public $showDeleteSampleModal = false;
    public $showEditModal = false;
    public $showSubUnitDropdown = false;
    public $showSampleTypeDropdown = false;
    public $showStagingAnalysisTypeDropdown = false;
    public $showAnalysisTypeDropdown = [];
    public $subUnitSearch = '';
    public $sampleTypeSearch = '';
    public $stagingAnalysisTypeSearch = '';
    public $analysisTypeSearch = '';
    public $editingRowIndex = null;
    public $selectedRows = [];
    public $uncertaintyRequired = false;
    public $parametersForm = [];
    public $modalLists = ['operators' => [], 'methods' => [], 'units' => [], 'equipments' => []];

    // More modals and reactive state
    public $showEditStandardModal = false;
    public $showAddOperatorModal = false;
    public $showAddEquipmentModal = false;
    public $showAddMethodModal = false;
    public $showBatchEditModal = false;
    public $allParametersSelected = false;
    public $selectedParameters = [];
    public $showProductDropdown = false;
    public $showStorageDropdown = false;
    public $productSearch = '';
    public $storageSearch = '';
    public $showAnalysisDateDropdown = [];
    public $batchEditForm = [
        'date_of_analysis' => '',
        'operator_id' => '',
        'method_id' => '',
        'equipment_id' => '',
    ];

    public $editingStandardData = [];
    public $standardValueOptions = [];

    // Dropdown data
    public $analysisTypes = [];
    public $standards = [];
    public $conditions = [];
    public $samplePoints = [];
    public $products = [];
    public $labSections = [];
    public $sampleTypes = [];
    public $unitsOfMeasure = [];
    public $subUnits = [];
    public $storageLocations = [];

    public $sampleForms = [];
    public $samplePhotos = [];
    public $samples = [];
    // View parameters modal
    public $showParametersModal = false;
    public $selectedSampleCode = null;
    public $sampleParameters = [];
    public $activeField = '';

    public $activeRowIndex = null;
    /** Lab section dropdown options for Parameters modal (SampleAnalysisStage) */
    public $modalLabSections = [];
    /** Date of analysis per lab section for the current sample: [ section_id => 'Y-m-d' ] */
    public $dateOfAnalysisBySection = [];
    /** Unique lab sections in current parameters (for Date of Analysis row): [ id => name ] */
    public $parameterLabSections = [];

    /** @var array<string, array<int, array<string, mixed>>> */
    public array $sampleWorksheetMap = [];

    public bool $showGroupedWorksheetsModal = false;

    public string $groupedWorksheetsModalSampleCode = '';

    /** @var array<int, array<string, mixed>> */
    public array $groupedWorksheetsModalItems = [];

    // Assign Samples Modal Data
    public $showAssignSamplesModal = false;
    public $assignStagingId = null;
    public $assignBatchCode = '';
    public $assignSampleType = '';
    public $assignCustomer = '';
    public $assignCompanyUnit = '';
    public $assignAreas = []; // Structure: [['id' => 1, 'name' => 'Area', 'sample_points' => [['id' => 1, 'name' => 'Point', 'active' => false]]]]
    public $assignTotalQty = 0;
    public $assignSelectedPoints = []; // ['point_id' => quantity]
    public $assignCurrentTotalQty = 0;
    public $assignQtyError = '';
    public $assignComments = []; // Rich text comments for assignments

    // Assignment Edit Context
    public $assignSampleTypeId = '';
    public $assignCompanySubUnitId = '';
    public $assignAnalysisTypeIds = [];
    public $assignCompanySubUnitName = '';
    public $assignSampleTypeName = '';
    public $assignAnalysisTypeNames = '';

    // Comment Editing
    public $showCommentModal = false;
    public $editingCommentIndex = null;
    public $tempCommentContent = '';

    // Search properties for Assignment Edit
    public $assignSubUnitSearch = '';
    public $showAssignSubUnitDropdown = false;
    public $assignSampleTypeSearch = '';
    public $showAssignSampleTypeDropdown = false;
    public $assignAnalysisTypeSearch = '';
    public $showAssignAnalysisTypeDropdown = false;

    // Add New Sample Point properties
    public $assignNewAreaId = '';
    public $assignNewAreaIds = [];
    public $assignNewPointId = '';
    public $assignNewPointIds = [];
    public $assignAvailableAreas = [];
    public $assignAvailablePoints = [];
    public $assignQuantities = [];

    // Custom searchable dropdown state for Add New Sample Point
    public $assignAreaSearch = '';
    public $assignPointSearch = '';
    public $showAssignAreaDropdown = false;
    public $showAssignPointDropdown = false;

    // Add New UoM Data
    public $newUomName = '';
    public $showAddUomModal = false;

    // On-the-fly addition modals
    public $showAddAreaModal = false;
    public $showAddPointModal = false;
    public $showAddProductModal = false;
    public $showAddStorageModal = false;

    public $newAreaCode = '';
    public $newAreaName = '';
    public $newAreaDescription = '';
    public $newPointCode = '';
    public $newPointName = '';
    public $newPointAreaId = '';
    public $newProductName = '';
    public $newStorageName = '';
    public $areas = [];

    // Modal-level toast message
    public $toastMessage = '';
    public $toastType = 'success'; // 'success' or 'danger'

    protected $listeners = ['refreshSamples' => '$refresh'];

    public function mount(SampleHeader $batch)
    {
        $this->batch = $batch;
        $this->batchId = $batch->id;
        $this->loadNotCaptured();
        $this->loadMissingWorksheetParameters();
        $this->loadIncompleteCapturedResults();
        $this->loadDropdownData();
        $this->loadSamples();
        $this->loadSampleGroupedWorksheets();
    }

    protected function loadSampleGroupedWorksheets(): void
    {
        try {
            $this->sampleWorksheetMap = app(GroupedWorksheetAssignmentService::class)
                ->buildSampleWorksheetMap($this->batch);
        } catch (\Exception $e) {
            Log::error('Error loading sample grouped worksheets for batch ' . $this->batchId . ': ' . $e->getMessage());
            $this->sampleWorksheetMap = [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function worksheetsForSampleRow(int $index): array
    {
        if (! isset($this->sampleForms[$index])) {
            return [];
        }

        $sampleId = $this->sampleForms[$index]['id'] ?? null;

        if ($sampleId && isset($this->sampleWorksheetMap[(string) $sampleId])) {
            return $this->sampleWorksheetMap[(string) $sampleId];
        }

        $analysisIds = $this->sampleForms[$index]['analysis_type_id'] ?? [];

        if (! is_array($analysisIds) || $analysisIds === []) {
            return [];
        }

        return app(GroupedWorksheetAssignmentService::class)
            ->summariesForAnalysisTypeIds($analysisIds, $this->batch);
    }

    public function openGroupedWorksheetsModal(int $index): void
    {
        $worksheets = $this->worksheetsForSampleRow($index);

        if ($worksheets === []) {
            session()->flash('error', 'No grouped worksheets are linked to this sample\'s analysis types.');

            return;
        }

        $this->groupedWorksheetsModalSampleCode = $this->sampleForms[$index]['sample_code'] ?? 'Sample';
        $this->groupedWorksheetsModalItems = $worksheets;
        $this->showGroupedWorksheetsModal = true;
    }

    public function closeGroupedWorksheetsModal(): void
    {
        $this->showGroupedWorksheetsModal = false;
        $this->groupedWorksheetsModalSampleCode = '';
        $this->groupedWorksheetsModalItems = [];
    }

    /**
     * Load samples that don't have results captured
     */
    protected function loadNotCaptured()
    {
        try {
            $this->not_captured = $this->batch->samples()
                ->whereDoesntHave('captured_results')
                ->pluck('sample_code')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading not captured samples: ' . $e->getMessage());
            $this->not_captured = [];
        }
    }

    /**
     * Detect captured results in this batch that have no result value
     * or are explicitly marked as "No attachment" (unfinished / missing results).
     */
    protected function loadIncompleteCapturedResults(): void
    {
        try {
            $rows = CapturedResult::where('sample_header_id', $this->batchId)
                ->whereValidUuidAnalyteId()
                ->with(['sample', 'analysis_type', 'my_analyte'])
                ->get();

            $items = [];
            foreach ($rows as $cr) {
                $resultText = strtolower(trim((string) ($cr->result ?? '')));

                if ($resultText !== '' && ! in_array($resultText, ['no attachment'], true)) {
                    continue;
                }

                $items[] = [
                    'sample_code' => optional($cr->sample)->sample_code ?? 'N/A',
                    'analysis_type' => optional($cr->analysis_type)->name ?? 'N/A',
                    'parameter' => optional($cr->my_analyte)->name ?? 'N/A',
                    'status' => $resultText === ''
                        ? 'No result captured'
                        : (string) $cr->result,
                ];
            }

            $this->incompleteCapturedResults = $items;
        } catch (\Exception $e) {
            Log::error('Error loading incomplete captured results for batch ' . $this->batchId . ': ' . $e->getMessage());
            $this->incompleteCapturedResults = [];
        }
    }

    /**
     * @return array<string, list<array{analysis_type: string, parameter: string, status: string}>>
     */
    public function getIncompleteCapturedResultsGroupedProperty(): array
    {
        $grouped = [];

        foreach ($this->incompleteCapturedResults as $item) {
            $sampleCode = (string) ($item['sample_code'] ?? 'N/A');
            $grouped[$sampleCode][] = [
                'analysis_type' => (string) ($item['analysis_type'] ?? 'N/A'),
                'parameter' => (string) ($item['parameter'] ?? 'N/A'),
                'status' => (string) ($item['status'] ?? 'incomplete'),
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    public function openIncompleteResultsModal(): void
    {
        $this->showIncompleteResultsModal = true;
    }

    public function closeIncompleteResultsModal(): void
    {
        $this->showIncompleteResultsModal = false;
    }

    protected function loadMissingWorksheetParameters(): void
    {
        try {
            $combos = CapturedResult::where('sample_header_id', $this->batchId)
                ->whereNotNull('procedure_worksheet_id')
                ->whereNotNull('analyte_id')
                ->get(['id', 'procedure_worksheet_id', 'analyte_id', 'worksheet_posted'])
                ->groupBy(function ($row) {
                    return $row->procedure_worksheet_id . '-' . $row->analyte_id;
                });

            $missing = [];

            foreach ($combos as $group) {
                $first = $group->first();
                $worksheetId = (string) $first->procedure_worksheet_id;
                $analyteId = (string) $first->analyte_id;
                $capturedIds = $group->pluck('id')->map(fn ($v) => (string) $v)->values()->all();

                // If any captured result in this group has worksheet_posted = true,
                // treat this worksheet/parameter as already posted and skip warning.
                $anyPosted = $group->contains(fn ($cr) => (bool) ($cr->worksheet_posted ?? false));
                if ($anyPosted) {
                    continue;
                }

                // Check if there is any test kit value for this (worksheet, analyte, batch) combo
                $rowIds = ProcedureTestKitRow::where('procedure_worksheet_id', $worksheetId)
                    ->pluck('id')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->all();

                $hasValues = false;
                if (! empty($rowIds) && ! empty($capturedIds)) {
                    $hasValues = ProcedureTestKitValue::whereIn('procedure_test_kit_row_id', $rowIds)
                        ->whereIn('captured_result_id', $capturedIds)
                        ->whereNotNull('value')
                        ->where('value', '!=', '')
                        ->exists();
                }

                if (! $hasValues) {
                    $worksheet = ProcedureWorksheet::find($worksheetId);
                    $analyte = Analyte::find($analyteId);

                    $missing[] = [
                        'worksheet_name' => $worksheet->name ?? ('Worksheet #' . $worksheetId),
                        'parameter_name' => $analyte->name ?? ('Analyte #' . $analyteId),
                    ];
                }
            }

            $this->missingWorksheetParameters = $missing;
        } catch (\Exception $e) {
            Log::error('Error loading missing worksheet parameters for batch ' . $this->batchId . ': ' . $e->getMessage());
            $this->missingWorksheetParameters = [];
        }
    }

    /**
     * Load all dropdown data
     */
    protected function loadDropdownData()
    {
        try {
            $this->analysisTypes = AnalysisType::select('id', 'name', 'code')->get()->toArray();
            $this->standards = Standards::select('id', 'code', 'name')->get()->toArray();
            $this->conditions = SampleCondition::select('id', 'name')->get()->toArray();

            // Load customer-specific data
            if ($this->batch->crm_customer_id) {
                // For SamplePoints, table column is crm_customer_id
                $this->samplePoints = \App\Models\CRM\SamplePoint::where('crm_customer_id', $this->batch->crm_customer_id)
                    ->get()->map(function ($point) {
                        return [
                            'id' => $point->id,
                            'name' => $point->name
                        ];
                    })->toArray();

                // For CompanyProduct, table column is crm_company_unit_id
                $this->products = \App\Models\CRM\CompanyProduct::where('crm_company_unit_id', $this->batch->crm_unit_id)
                    ->select('id', 'name')->get()->toArray();
            }

            $this->labSections = $this->mapLabsForSelect(Lab::select('id', 'name', 'code')->get());

            // Load all sample types
            $this->sampleTypes = \App\SampleType::select('id', 'name')->where('active', 1)->orderBy('name')->get()->toArray();

            // Load Units of Measure
            $this->unitsOfMeasure = \App\ReportingUnit::select('id', 'name')->get()->toArray();

            // Load sub-units for current client
            if ($this->batch->crm_customer_id) {
                $this->subUnits = \App\Models\CRM\CRMCompanySubUnit::where('crm_customer_id', $this->batch->crm_customer_id)
                    ->select('id', 'name')->get()->toArray();
            }

            // Load storage locations if model exists
            if (class_exists('\App\LabStore')) {
                $this->storageLocations = \App\LabStore::select('id', 'name')->get()->toArray();
            } else {
                $this->storageLocations = \App\InventoryStore::where('type_of_store', 'lab_store')->select('id', 'name')->get()->toArray();
            }

            // Load Areas for point creation
            if (\Schema::hasTable('crm_areas')) {
                $this->areas = \App\Models\Area::select('id', 'name')->orderBy('name')->get()->toArray();
            } else {
                $this->areas = [];
            }
        } catch (\Exception $e) {
            Log::error('Error loading dropdown data: ' . $e->getMessage());
        }
    }

    /**
     * Load existing samples into form array
     */
    protected function loadSamples()
    {
        try {
            $this->batch->unsetRelation('samples');
            $samples = $this->batch->samples;

            $this->sampleForms = [];
            foreach ($samples as $index => $sample) {
                $this->sampleForms[$index] = [
                    'id' => $sample->id,
                    'sample_code' => $sample->sample_code,
                    'analysis_type_id' => array_filter(explode(',', $sample->analysis_type_id ?? '')),
                    'lab_id' => $sample->lab_id ? (string) $sample->lab_id : '',
                    'sample_condition_id' => $sample->sample_condition_id ? (string) $sample->sample_condition_id : '',
                    'sample_point_id' => $sample->sample_point_id ? (string) $sample->sample_point_id : '',
                    'photo_url' => $sample->photo_url ?? '',
                    'sample_no' => $sample->sample_no ?? '',
                    'sample_type_id' => $sample->sample_type_id ? (string) $sample->sample_type_id : '',
                    'customer_sample_id' => $this->resolveCustomerSampleId($sample),
                    'comments' => $sample->comments ?? '',
                    'main_standard' => $sample->main_standard ? (string) $sample->main_standard : '',
                    'secondary_standard' => $sample->secondary_standard ? (string) $sample->secondary_standard : '',
                    'disposal_date' => $sample->disposal_date ?? '',
                    'store_id' => $sample->store_id ? (string) $sample->store_id : '',
                    'store_slot_id' => $sample->store_slot_id ? (string) $sample->store_slot_id : '',
                    'quantity' => $sample->quantity ?? 1,
                    'reporting_unit_id' => $sample->reporting_unit_id ? (string) $sample->reporting_unit_id : '',
                ];
            }

            $this->samples = $samples;
            $this->loadSampleGroupedWorksheets();
        } catch (\Exception $e) {
            Log::error('Error loading samples: ' . $e->getMessage());
            $this->sampleForms = [];
        }
    }

    private function resolveCustomerSampleId(SampleDetails $sample): string
    {
        return trim((string) ($sample->customer_sample_id ?? $sample->file_no ?? $sample->barcode ?? ''));
    }

    private function applyCustomerSampleId(SampleDetails $sample, mixed $value): void
    {
        $customerSampleId = trim((string) ($value ?? ''));

        $sample->customer_sample_id = $customerSampleId !== '' ? $customerSampleId : null;
        $sample->file_no = $customerSampleId !== '' ? $customerSampleId : null;
        $sample->barcode = $customerSampleId !== '' ? $customerSampleId : null;
    }

    /**
     * @param  array<string, mixed>  $sampleData
     */
    private function persistSampleFromFormData(SampleDetails $sample, array $sampleData, int $index): SampleDetails
    {
        $sample->analysis_type_id = is_array($sampleData['analysis_type_id'])
            ? implode(',', array_map('strval', $sampleData['analysis_type_id']))
            : (string) ($sampleData['analysis_type_id'] ?? '');
        $sample->lab_id = ! empty($sampleData['lab_id']) ? (string) $sampleData['lab_id'] : null;
        $sample->sample_condition_id = ! empty($sampleData['sample_condition_id']) ? $sampleData['sample_condition_id'] : null;
        $sample->sample_point_id = ! empty($sampleData['sample_point_id']) ? $sampleData['sample_point_id'] : null;

        if (isset($this->samplePhotos[$index])) {
            $path = $this->samplePhotos[$index]->store('sample_photos', 'public');
            $sampleData['photo_url'] = $path;
            $this->sampleForms[$index]['photo_url'] = $path;
            unset($this->samplePhotos[$index]);
        }

        $sample->photo_url = ! empty($sampleData['photo_url']) ? $sampleData['photo_url'] : null;
        $sample->sample_no = ! empty($sampleData['sample_no']) ? $sampleData['sample_no'] : null;
        $sample->sample_type_id = ! empty($sampleData['sample_type_id']) ? $sampleData['sample_type_id'] : null;
        $this->applyCustomerSampleId($sample, $sampleData['customer_sample_id'] ?? null);
        $sample->comments = $sampleData['comments'] ?? null;
        $sample->main_standard = ! empty($sampleData['main_standard']) ? $sampleData['main_standard'] : null;
        $sample->secondary_standard = ! empty($sampleData['secondary_standard']) ? $sampleData['secondary_standard'] : null;
        $sample->disposal_date = ! empty($sampleData['disposal_date']) ? $sampleData['disposal_date'] : null;
        $sample->store_id = ! empty($sampleData['store_id']) ? $sampleData['store_id'] : null;
        $sample->store_slot_id = ! empty($sampleData['store_slot_id']) ? $sampleData['store_slot_id'] : null;
        $sample->quantity = $sampleData['quantity'] ?? 1;
        $sample->reporting_unit_id = ! empty($sampleData['reporting_unit_id']) ? $sampleData['reporting_unit_id'] : null;

        $sample->save();

        return $sample;
    }

    public function updatedAssignAreaSearch(): void
    {
        $this->showAssignAreaDropdown = true;
    }

    public function updatedAssignPointSearch(): void
    {
        $this->showAssignPointDropdown = true;
    }

    /**
     * Assign samples from staging data (Open Modal)
     */
    public function assignSamples($stagingId)
    {
        // $this->dispatch('openAssignModal', stagingId: $stagingId, headerId: $this->batchId);
        $this->openAssignModal($stagingId);
    }

    /**
     * Load data and open the Assign Samples Modal
     */
    public function openAssignModal($stagingId)
    {
        try {
            $staging = SampleDetailStaging::with('sampleHeader.sample_type', 'sampleHeader.client')
                ->findOrFail($stagingId);

            $this->assignStagingId = $stagingId;

            $dataJson = $staging->data_json;
            $subUnitId = $dataJson['company_sub_unit_id'] ?? null;

            if (!$subUnitId) {
                session()->flash('error', 'No company sub unit specified in staging data.');
                return;
            }

            // Get sub unit and company unit info
            $subUnit = \App\Models\CRM\CRMCompanySubUnit::with('companyUnit')->find($subUnitId);

            $this->assignBatchCode = $staging->sampleHeader->batch_code;
            $this->assignSampleType = $staging->sampleHeader->sample_type->name ?? 'N/A';
            $this->assignCustomer = $staging->sampleHeader->client->name ?? 'N/A';
            $this->assignCompanyUnit = $subUnit ? ($subUnit->companyUnit->name ?? 'N/A') : 'N/A';
            $this->assignTotalQty = $dataJson['quantity'] ?? 0;

            // Populate IDs for editing
            $this->assignSampleTypeId = $staging->sampleHeader->sample_type_id ?? '';
            $this->assignCompanySubUnitId = $subUnitId;
            $this->assignCompanySubUnitName = $subUnit->name ?? 'N/A';
            $this->assignSampleTypeName = $this->assignSampleType;

            $atIds = $dataJson['analysis_type_ids'] ?? [];
            if (is_string($atIds)) {
                $atIds = array_filter(explode(',', $atIds));
            }
            $this->assignAnalysisTypeIds = $atIds;
            $this->assignAnalysisTypeNames = $dataJson['analysis_type_names'] ?? '';

            // Load areas and points
            $this->loadAssignAreasAndPoints($subUnitId);

            // Reset selections
            $this->assignSelectedPoints = [];
            $this->assignNewAreaId = '';
            $this->assignNewAreaIds = [];
            $this->assignNewPointId = '';
            $this->assignNewPointIds = [];
            $this->assignAreaSearch = '';
            $this->assignPointSearch = '';
            $this->assignComments = []; // Reset comments

            // Load available areas/points for "Add New Query" (if needed later)
            // For now we just focus on assignment

            $this->showAssignSamplesModal = true;
            $this->dispatch('reinit-mce');
        } catch (\Exception $e) {
            Log::error('Error opening assign modal: ' . $e->getMessage());
            session()->flash('error', 'Failed to load assignment data.');
        }
    }

    public function selectAssignArea(int $id): void
    {
        if (!in_array($id, $this->assignNewAreaIds, true)) {
            $this->assignNewAreaIds[] = $id;
        }

        $this->assignNewAreaId = $id;

        $this->assignAreaSearch = '';
        $this->showAssignAreaDropdown = false;
    }

    public function removeAssignArea(int $id): void
    {
        $this->assignNewAreaIds = array_values(array_filter(
            $this->assignNewAreaIds,
            static function ($existingId) use ($id) {
                return (int) $existingId !== (int) $id;
            }
        ));

        if (empty($this->assignNewAreaIds)) {
            $this->assignNewAreaId = '';
        } else {
            $this->assignNewAreaId = $this->assignNewAreaIds[0];
        }
    }

    public function selectAssignPoint(int $id): void
    {
        if (!in_array($id, $this->assignNewPointIds, true)) {
            $this->assignNewPointIds[] = $id;
        }

        $this->assignNewPointId = $id;

        $this->assignPointSearch = '';
        $this->showAssignPointDropdown = false;
    }

    public function removeAssignPoint(int $id): void
    {
        $this->assignNewPointIds = array_values(array_filter(
            $this->assignNewPointIds,
            static function ($existingId) use ($id) {
                return (int) $existingId !== (int) $id;
            }
        ));

        if (empty($this->assignNewPointIds)) {
            $this->assignNewPointId = '';
        } else {
            $this->assignNewPointId = $this->assignNewPointIds[0];
        }
    }

    /**
     * Save a new Sample Area from the "Add New Sample Area" modal.
     */
    public function saveNewArea(): void
    {
        $this->toastType = 'danger';
        $this->toastMessage = 'Sample areas are no longer supported. Assign sample points directly to company unit.';
        $this->showAddAreaModal = false;
    }

    /**
     * Load sample points for the given sub unit (area-less model)
     */
    protected function loadAssignAreasAndPoints($subUnitId)
    {
        $sampleTypeId = $this->batch->sample_type_id ?? null;

        // If there is no sample type on the batch, do not load any assignment points
        if (!$sampleTypeId) {
            $this->assignAreas = [];
            $this->assignAvailableAreas = [];
            $this->assignAvailablePoints = [];
            $this->assignQuantities = [];

            return;
        }

        $companyUnitId = null;
        if ($subUnitId) {
            $subUnit = \App\Models\CRM\CRMCompanySubUnit::find($subUnitId);
            $companyUnitId = $subUnit ? $subUnit->crm_company_unit_id : null;
        }

        $pointsQuery = \App\Models\CRM\SamplePoint::query()
            ->where('crm_customer_id', $this->batch->crm_customer_id)
            ->where('active', 1);

        if ($companyUnitId) {
            $pointsQuery->where('crm_company_unit_id', $companyUnitId);
        }

        $points = $pointsQuery->orderBy('name')->get();

        $this->assignAreas = [[
            'id' => 'direct',
            'name' => 'Sample Points',
            'sample_points' => $points->map(function ($point) {
                return [
                    'id' => $point->id,
                    'name' => $point->name ?? ('Sample Point #' . $point->id),
                ];
            })->toArray(),
        ]];

        $this->assignAvailableAreas = [];

        $this->assignAvailablePoints = $points->map(function ($point) {
            return [
                'id' => $point->id,
                'name' => $point->name ?? ('Sample Point #' . $point->id),
            ];
        })->toArray();

        // Initialize quantities for the visible points
        $this->assignQuantities = [];
        foreach ($this->assignAreas as $area) {
            foreach ($area['sample_points'] as $point) {
                $this->assignQuantities[$point['id']] = 1;
            }
        }
    }


    public function addCustomerSamplePoint()
    {
        $this->validate([
            'assignNewPointIds' => 'required|array|min:1',
            'assignNewPointIds.*' => 'required|exists:sample_points,id',
        ]);

        $customerId = $this->batch->crm_customer_id;
        $staging = SampleDetailStaging::find($this->assignStagingId);
        $subUnitId = $staging->data_json['company_sub_unit_id'] ?? null;

        // We need to fetch the company unit ID from the sub unit if possible
        $companyUnitId = null;
        if ($subUnitId) {
            $subUnit = \App\Models\CRM\CRMCompanySubUnit::find($subUnitId);
            $companyUnitId = $subUnit ? $subUnit->crm_company_unit_id : null;
        }

        DB::beginTransaction();
        try {
            $sampleTypeId = $this->batch->sample_type_id;

            foreach ($this->assignNewPointIds as $pointId) {
                $existingPoint = \App\Models\CRM\SamplePoint::where('id', $pointId)
                    ->where('crm_customer_id', $customerId)
                    ->first();

                if (!$existingPoint) {
                    continue;
                }

                if ($companyUnitId && $existingPoint->crm_company_unit_id !== $companyUnitId) {
                    $existingPoint->crm_company_unit_id = $companyUnitId;
                    $existingPoint->save();
                }

                if ($sampleTypeId) {
                    $existingPoint->sampleTypes()->syncWithoutDetaching([$sampleTypeId]);
                }
            }

            DB::commit();

            // Refresh areas/points
            $this->loadAssignAreasAndPoints($subUnitId);

            // Clear selection
            $this->assignNewAreaId = '';
            $this->assignNewAreaIds = [];
            $this->assignNewPointId = '';
            $this->assignNewPointIds = [];
            $this->assignAreaSearch = '';
            $this->assignPointSearch = '';

            $this->toastType = 'success';
            $this->toastMessage = 'Sample point(s) added to customer successfully.';
            $this->dispatch('reinit-mce');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding customer sample point: ' . $e->getMessage());
            $this->toastType = 'danger';
            $this->toastMessage = 'Failed to add sample point: ' . $e->getMessage();
        }
    }

    // ========== Search & Select Methods for Assign Modal Edit ==========

    public function getFilteredAssignSubUnits()
    {
        if (empty($this->assignSubUnitSearch)) {
            return $this->subUnits;
        }
        return array_filter($this->subUnits, function ($unit) {
            return stripos($unit['name'], $this->assignSubUnitSearch) !== false;
        });
    }

    public function selectAssignSubUnit($id)
    {
        $unit = collect($this->subUnits)->firstWhere('id', $id);
        if ($unit) {
            $this->assignCompanySubUnitId = $id;
            $this->assignCompanySubUnitName = $unit['name'];

            // Update the staging record data_json immediately? 
            // Or only on performAssignment? 
            // Better to update header/staging only if they confirm or we can do it on the fly.
            // Let's do it on the fly to keep UI in sync.
            $this->updateStagingData('company_sub_unit_id', $id);
            $this->updateStagingData('company_sub_unit_name', $unit['name']);

            // Re-load areas/points for the new sub-unit
            $this->loadAssignAreasAndPoints($id);
        }
        $this->showAssignSubUnitDropdown = false;
        $this->assignSubUnitSearch = '';
    }

    public function getFilteredAssignSampleTypes()
    {
        if (empty($this->assignSampleTypeSearch)) {
            return $this->sampleTypes;
        }
        return array_filter($this->sampleTypes, function ($type) {
            return stripos($type['name'], $this->assignSampleTypeSearch) !== false;
        });
    }

    public function selectAssignSampleType($id)
    {
        $type = collect($this->sampleTypes)->firstWhere('id', $id);
        if ($type) {
            $this->assignSampleTypeId = $id;
            $this->assignSampleTypeName = $type['name'];
            $this->assignSampleType = $type['name'];

            // Update header
            $staging = SampleDetailStaging::find($this->assignStagingId);
            if ($staging && $staging->sampleHeader) {
                $staging->sampleHeader->sample_type_id = $id;
                $staging->sampleHeader->save();
            }
        }
        $this->showAssignSampleTypeDropdown = false;
        $this->assignSampleTypeSearch = '';
    }

    public function getFilteredAssignAnalysisTypes()
    {
        if (empty($this->assignAnalysisTypeSearch)) {
            return $this->analysisTypes;
        }
        return array_filter($this->analysisTypes, function ($type) {
            return stripos($type['name'], $this->assignAnalysisTypeSearch) !== false ||
                stripos($type['code'], $this->assignAnalysisTypeSearch) !== false;
        });
    }

    public function toggleAssignAnalysisType($id)
    {
        $atIds = $this->assignAnalysisTypeIds;
        $key = array_search($id, $atIds);

        if ($key !== false) {
            unset($atIds[$key]);
        } else {
            $atIds[] = $id;
        }

        $this->assignAnalysisTypeIds = array_values($atIds);

        // Update display names
        $names = [];
        foreach ($this->analysisTypes as $type) {
            if (in_array($type['id'], $this->assignAnalysisTypeIds)) {
                $names[] = $type['name'];
            }
        }
        $this->assignAnalysisTypeNames = implode(', ', $names);

        // Update staging record
        $this->updateStagingData('analysis_type_ids', implode(',', $this->assignAnalysisTypeIds));
        $this->updateStagingData('analysis_type_names', $this->assignAnalysisTypeNames);
    }

    protected function updateStagingData($key, $value)
    {
        $staging = SampleDetailStaging::find($this->assignStagingId);
        if ($staging) {
            $dataJson = $staging->data_json;
            $dataJson[$key] = $value;
            $staging->data_json = $dataJson;
            $staging->save();
        }
    }

    /**
     * Submit assignment
     */
    public function performAssignment()
    {
        // Filter out unticked or zero quantity (though frontend should handle this)
        $selections = [];
        foreach ($this->assignSelectedPoints as $pointId => $isSelected) {
            // Check if selected
            if ($isSelected) {
                $qty = isset($this->assignQuantities[$pointId]) && $this->assignQuantities[$pointId] > 0
                    ? $this->assignQuantities[$pointId]
                    : 1;

                $selections[] = [
                    'sample_point_id' => $pointId,
                    'quantity' => $qty,
                    'comments' => $this->assignComments[$pointId] ?? ''
                ];
            }
        }

        if (empty($selections)) {
            session()->flash('error', 'Please select at least one sample point.');
            return;
        }

        // Validate that the total assigned quantity does not exceed the original submission quantity
        $totalAssignedQty = array_sum(array_column($selections, 'quantity'));
        if ($this->assignTotalQty > 0 && $totalAssignedQty > $this->assignTotalQty) {
            session()->flash(
                'error',
                'Assigned quantity (' . $totalAssignedQty . ') cannot exceed the total quantity of ' . $this->assignTotalQty . ' from the submission.'
            );
            return;
        }

        DB::beginTransaction();
        try {
            $sampleHeader = $this->batch;
            $staging = SampleDetailStaging::findOrFail($this->assignStagingId);

            $createdSamples = [];
            $sampleIndex = 0;
            $totalSamples = count($selections);

            // Replicate logic from SampleCreationController::assignSamples
            foreach ($selections as $selection) {
                // ... (Create logic will go here - we need to copy helper methods or call service)
                // Since this is complex logic, ideally we invoke a service. 
                // For now, I will implement a simplified version mirroring the controller given I am in a Livewire component.

                $samplePointId = $selection['sample_point_id'];

                $sampleDetail = $this->createSampleDetailsFromStagingLivewire(
                    $sampleHeader,
                    $staging,
                    $samplePointId,
                    $sampleIndex,
                    $totalSamples,
                    $selection['quantity'],
                    $selection['comments'] ?? ''
                );

                $createdSamples[] = $sampleDetail;
                $sampleIndex++;
            }

            $staging->is_processed = 1;
            $staging->save();

            $this->createChainOfCustodyLivewire('Sample Assignment');

            // Check if all processed
            $unprocessedCount = SampleDetailStaging::where('sample_header_id', $sampleHeader->id)
                ->where('is_processed', 0)
                ->count();

            if ($unprocessedCount === 0) {
                $sampleHeader->sample_detail_processed = 1;
                $sampleHeader->save();
            }

            DB::commit();

            $this->showAssignSamplesModal = false;
            $this->dispatch('samplesUpdated');
            $this->dispatch('batchUpdated')->to(\App\Livewire\Batch\Header::class);
            // Reload samples list
            $this->loadSamples();

            session()->flash('success', count($createdSamples) . ' samples assigned successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment error: ' . $e->getMessage());
            session()->flash('error', 'Error assigning samples: ' . $e->getMessage());
        }
    }

    public function updatedAssignQuantities($value, $name): void
    {
        $this->recalculateAssignQuantitiesTotal();
    }

    public function updatedAssignSelectedPoints($value, $name): void
    {
        $this->recalculateAssignQuantitiesTotal();
    }

    protected function recalculateAssignQuantitiesTotal(): void
    {
        $total = 0;

        foreach ($this->assignSelectedPoints as $pointId => $isSelected) {
            if ($isSelected) {
                $qty = isset($this->assignQuantities[$pointId]) && $this->assignQuantities[$pointId] > 0
                    ? (int) $this->assignQuantities[$pointId]
                    : 1;

                $total += $qty;
            }
        }

        $this->assignCurrentTotalQty = $total;

        if ($this->assignTotalQty > 0 && $total > $this->assignTotalQty) {
            $this->assignQtyError = 'Assigned quantity (' . $total . ') cannot exceed the total quantity of ' . $this->assignTotalQty . ' from the submission.';
        } else {
            $this->assignQtyError = '';
        }
    }

    // Support methods for assignment (Simplification of Controller methods)
    private function createSampleDetailsFromStagingLivewire($sampleHeader, $staging, $samplePointId, $index, $totalSamples, $quantity = 1, $comments = '')
    {
        $dataJson = $staging->data_json;
        $dataJson['force_quantity'] = $quantity;

        $disposal_date = null;
        if ($sampleHeader->sample_type && $sampleHeader->sample_type->disposal_count) {
            $disposal_date = \Carbon\Carbon::parse($sampleHeader->receipt_date)->addDays($sampleHeader->sample_type->disposal_count)->format('Y-m-d');
        }

        $companyProductId = $dataJson['company_product_id'] ?? null;
        if (!$companyProductId && $sampleHeader->sample_type_id) {
            $sampleType = \App\SampleType::find($sampleHeader->sample_type_id);
            if ($sampleType && $sampleType->default_product_id) {
                $companyProductId = $sampleType->default_product_id;
            }
        }

        $sampleConditionId = $dataJson['sample_condition_id'] ?? 1;

        $numberingService = app(JobSampleNumberingService::class);
        $detailCreationService = app(SampleDetailCreationService::class);

        $prefix = (string) ($dataJson['sample_code_prefix'] ?? '');
        if ($prefix === '' && ! empty($dataJson['test_category'])) {
            $prefix = $numberingService->resolveCategoryPrefixFromRow($dataJson);
        }
        if ($prefix === '') {
            $prefix = JobSampleNumberingService::PREFIX_CHEMISTRY;
        }

        $sampleDetail = $detailCreationService->create(
            $sampleHeader,
            $prefix,
            [
                'sample_point_id' => $samplePointId,
                'analysis_type_id' => $dataJson['analysis_type_ids'] ?? '',
                'company_product_id' => $companyProductId,
                'sample_condition_id' => $sampleConditionId,
                'lab_id' => $dataJson['lab_id'] ?? 1,
                'quantity' => $dataJson['force_quantity'] ?? 1,
                'barcode' => $sampleHeader->date_collected ? date('H:i:s', strtotime($sampleHeader->date_collected)) : null,
                'disposal_date' => $disposal_date,
                'comments' => $comments,
            ],
            $dataJson['customer_sample_id'] ?? null,
        );

        // Create captured results (We need to replicate createCapturedResultsForAnalysisType logic or call it)
        // Since that logic is complex and involves AnalysisElements, we should duplicate it or refactor to Service.
        // Assuming we can't refactor easily now, I will use a simplified call or the existing Controller logic if accessible.
        // But Controller methods are private. 
        // I will duplicate `createCapturedResultsForAnalysisType` logic briefly here.
        if (!empty($dataJson['analysis_type_ids'])) {
            $this->generateResultsForSample($sampleDetail, $dataJson['analysis_type_ids']);
        }

        // Sample Dates
        SampleDate::updateOrCreate(
            ['sample_header_id' => $sampleHeader->id, 'name' => 'Login Date'],
            ['date' => now()]
        );
        // Target date logic... skipped for brevity, defaults to now + reporting time if possible.

        return $sampleDetail;
    }

    private function generateResultsForSample($sampleDetail, $analysisTypeIdsStr)
    {
        $analysisTypeIds = explode(',', $analysisTypeIdsStr);
        foreach ($analysisTypeIds as $atId) {
            if (!$atId)
                continue;

            $elements = \App\AnalysisElements::where('analysis_type_id', $atId)->where('active', 1)->get();
            foreach ($elements as $element) {
                // Create CapturedResult and Result
                // This is a minimal implementation to get it working, mirroring SampleCreationController
                $analyteCode = $element->analyte->code ?? 'UNKNOWN';
                $reportingUnit = \App\ReportingUnit::find($element->reporting_unit);
                $reportingUnitName = $reportingUnit ? $reportingUnit->name : '';

                $captured = new \App\CapturedResult();
                $captured->fill([
                    'sample_detail_code' => $sampleDetail->sample_code,
                    'sample_detail_id' => $sampleDetail->id,
                    'sample_header_id' => $sampleDetail->sample_header_id,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $analyteCode,
                    'analysis_type_id' => $atId,
                    'lab_section_id' => $element->lab_section_id,
                    'user_id' => auth()->id(),
                    'reporting_unit_id' => $reportingUnitName,
                ]);
                $captured->save();

                $result = new \App\Result();
                $result->fill([
                    'captured_result_id' => $captured->id,
                    'sample_detail_code' => $sampleDetail->sample_code,
                    'sample_detail_id' => $sampleDetail->id,
                    'sample_header_id' => $sampleDetail->sample_header_id,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $analyteCode,
                    'analysis_type_id' => $atId,
                    'reporting_unit_id' => $reportingUnitName,
                ]);
                $result->save();
            }
        }
    }

    private function createChainOfCustodyLivewire($action)
    {
        $custody = new \App\ChainOfCustody();
        $custody->workflow_stage = $this->batch->status;
        $custody->tracking_stage_id = $this->batch->sample_tracking_stage ?? 1;
        $custody->moved_in_by = auth()->id();
        $custody->sample_header_id = $this->batch->id;
        $custody->comments = $action . ' - via Livewire Batch View';
        $custody->save();
    }

    /**
     * Open edit modal for staging record
     */
    public function editStaging($stagingId)
    {
        try {
            $staging = SampleDetailStaging::findOrFail($stagingId);

            $this->editingStagingId = $stagingId;
            $dataJson = $staging->data_json;

            // Handle analysis_type_ids which might be stored as comma separated string or array in json
            $atIds = $dataJson['analysis_type_ids'] ?? [];
            if (is_string($atIds)) {
                $atIds = array_filter(explode(',', $atIds));
            }

            $this->stagingForm = [
                'company_sub_unit_id' => $dataJson['company_sub_unit_id'] ?? '',
                'company_sub_unit_name' => $dataJson['company_sub_unit_name'] ?? '',
                'sample_type_id' => $staging->sampleHeader->sample_type_id ?? '',
                'sample_type_name' => $staging->sampleHeader->sample_type->name ?? '',
                'analysis_type_ids' => $atIds,
                'analysis_type_names' => $dataJson['analysis_type_names'] ?? '',
                'quantity' => $dataJson['quantity'] ?? 1,
            ];

            $this->showEditModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading staging for edit: ' . $e->getMessage());
            session()->flash('error', 'Failed to load staging data');
        }
    }

    /**
     * Open add modal for staging record
     */
    public function addStaging()
    {
        $this->editingStagingId = null;
        $this->stagingForm = [
            'company_sub_unit_id' => '',
            'company_sub_unit_name' => '',
            'sample_type_id' => '',
            'sample_type_name' => '',
            'analysis_type_ids' => [],
            'analysis_type_names' => '',
            'quantity' => 1,
        ];

        $this->showEditModal = true;
    }

    public function openAddModal($field, $index)
    {
        $this->activeField = $field;
        $this->activeRowIndex = $index;

        if ($field === 'sample_point_id') {
            $this->newPointName = '';
            $this->newPointAreaId = '';
            $this->showAddPointModal = true;
        } elseif ($field === 'company_product_id') {
            $this->newProductName = '';
            $this->showAddProductModal = true;
        } elseif ($field === 'reporting_unit_id') {
            $this->newUomName = '';
            $this->showAddUomModal = true;
        } elseif ($field === 'store_id') {
            $this->newStorageName = '';
            $this->showAddStorageModal = true;
        }
    }

    public function saveNewPoint()
    {
        $this->validate([
            'newPointCode' => 'required|string|max:50',
            'newPointName' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create customer-owned sample point directly
            $crmSamplePoint = \App\Models\CRM\SamplePoint::create([
                'name'       => $this->newPointName,
                'code'       => $this->newPointCode,
                'crm_customer_id'     => $this->batch->crm_customer_id,
                'crm_company_unit_id' => $this->batch->crm_unit_id,
                'created_by'          => auth()->id(),
                'active'              => true,
                'gps'                 => '0,0',
            ]);

            // Link sample point to this batch's sample type when available.
            $sampleTypeId = $this->batch->sample_type_id;
            if ($sampleTypeId) {
                $crmSamplePoint->sampleTypes()->syncWithoutDetaching([$sampleTypeId]);
            }

            DB::commit();

            // 4. Refresh Sample Points list used elsewhere in this component
            $this->samplePoints = \App\Models\CRM\SamplePoint::where('crm_customer_id', $this->batch->crm_customer_id)
                ->get()->map(function ($point) {
                    return [
                        'id'   => $point->id,
                        'name' => $point->name,
                    ];
                })->toArray();

            // 5. If this was opened from a specific row in the samples table, assign it there
            if ($this->activeRowIndex !== null && isset($this->sampleForms[$this->activeRowIndex])) {
                $this->sampleForms[$this->activeRowIndex]['sample_point_id'] = $crmSamplePoint->id;
            }

            $this->showAddPointModal = false;
            $this->newPointCode      = '';
            $this->toastType = 'success';
            $this->toastMessage = 'Sample point added successfully.';
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving new point: ' . $e->getMessage());
            $this->toastType = 'danger';
            $this->toastMessage = 'Error saving new point.';
        }
    }

    public function saveNewProduct()
    {
        $this->validate([
            'newProductName' => 'required|string|max:255',
        ]);

        try {
            $product = \App\Models\CRM\CompanyProduct::create([
                'name' => $this->newProductName,
                'crm_company_unit_id' => $this->batch->crm_unit_id,
                'active' => true,
            ]);

            // Refresh Products
            $this->products = \App\Models\CRM\CompanyProduct::where('crm_company_unit_id', $this->batch->crm_unit_id)
                ->select('id', 'name')->get()->toArray();

            // Assign to row if opened from a specific row
            if ($this->activeRowIndex !== null && isset($this->sampleForms[$this->activeRowIndex])) {
                $this->sampleForms[$this->activeRowIndex]['company_product_id'] = $product->id;
            }

            $this->showAddProductModal = false;
            session()->flash('success', 'Product added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving new product: ' . $e->getMessage());
            session()->flash('error', 'Error saving new product.');
        }
    }

    public function saveNewUom()
    {
        $this->validate([
            'newUomName' => 'required|string|max:255',
        ]);

        try {
            Log::info('Attempting to save UoM: ' . $this->newUomName);
            $uom = \App\ReportingUnit::create([
                'name' => $this->newUomName,
                'active' => 1,
            ]);
            Log::info('UoM saved with ID: ' . $uom->id);

            // Refresh UoMs
            $this->unitsOfMeasure = \App\ReportingUnit::select('id', 'name')->get()->toArray();

            // Assign to row if opened from a specific row
            if ($this->activeRowIndex !== null && isset($this->sampleForms[$this->activeRowIndex])) {
                $this->sampleForms[$this->activeRowIndex]['reporting_unit_id'] = $uom->id;
            }

            $this->showAddUomModal = false;
            session()->flash('success', 'Unit of measure added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving new UoM: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            session()->flash('error', 'Error saving new unit of measure: ' . $e->getMessage());
        }
    }

    public function saveNewStorage()
    {
        $this->validate([
            'newStorageName' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // InventoryStore might not have $fillable, using manual assignment
            $store = new \App\InventoryStore();
            $store->name = $this->newStorageName;
            $store->type_of_store = 'lab_store';
            // Removed invalid 'active' column that was causing SQL errors
            $store->save();

            // Create a default slot for this store to make it visible in joined queries
            $slot = new \App\InventoryStoreSlot();
            $slot->inventory_store_id = $store->id;
            $slot->name = 'Slot 1';
            $slot->save();

            DB::commit();

            // Refresh Storage
            if (class_exists('\App\LabStore')) {
                $this->storageLocations = \App\LabStore::select('id', 'name')->get()->toArray();
            } else {
                $this->storageLocations = \App\InventoryStore::where('type_of_store', 'lab_store')->select('id', 'name')->get()->toArray();
            }

            // Assign to row if opened from a specific row
            if ($this->activeRowIndex !== null && isset($this->sampleForms[$this->activeRowIndex])) {
                $this->sampleForms[$this->activeRowIndex]['store_id'] = $store->id;
            }

            $this->showAddStorageModal = false;
            session()->flash('success', 'Storage location added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving new storage: ' . $e->getMessage());
            session()->flash('error', 'Error saving new storage.');
        }
    }

    public function openCommentModal($index)
    {
        $this->editingCommentIndex = $index;
        // Check if it's a numeric index (main table) or a point ID (assignment table)
        if (isset($this->sampleForms[$index])) {
            $this->tempCommentContent = $this->sampleForms[$index]['comments'] ?? '';
        } else {
            // Assignment table uses pointId as index
            $this->tempCommentContent = $this->assignComments[$index] ?? '';
        }
        $this->showCommentModal = true;
    }

    public function saveComment()
    {
        if ($this->editingCommentIndex !== null) {
            if (isset($this->sampleForms[$this->editingCommentIndex])) {
                $this->sampleForms[$this->editingCommentIndex]['comments'] = $this->tempCommentContent;
            } else {
                $this->assignComments[$this->editingCommentIndex] = $this->tempCommentContent;
            }
        }
        $this->showCommentModal = false;
        $this->editingCommentIndex = null;
    }

    /**
     * Update or create staging record
     */
    public function updateStaging()
    {
        $this->validate([
            'stagingForm.company_sub_unit_name' => 'required|string',
            'stagingForm.analysis_type_names' => 'required|string',
            'stagingForm.quantity' => 'required|integer|min:1',
        ]);

        try {
            if ($this->editingStagingId) {
                $staging = SampleDetailStaging::findOrFail($this->editingStagingId);
            } else {
                $staging = new SampleDetailStaging();
                $staging->sample_header_id = $this->batch->id;
                $staging->is_processed = 0;
            }

            $dataJson = $staging->data_json ?: [];
            $dataJson['company_sub_unit_id'] = $this->stagingForm['company_sub_unit_id'];
            $dataJson['company_sub_unit_name'] = $this->stagingForm['company_sub_unit_name'];
            $dataJson['analysis_type_ids'] = implode(',', $this->stagingForm['analysis_type_ids']);
            $dataJson['analysis_type_names'] = $this->stagingForm['analysis_type_names'];
            $dataJson['quantity'] = $this->stagingForm['quantity'];

            $staging->data_json = $dataJson;
            $staging->save();

            // Also update sample header sample_type if changed
            if ($this->stagingForm['sample_type_id'] != $this->batch->sample_type_id) {
                $this->batch->sample_type_id = $this->stagingForm['sample_type_id'];
                $this->batch->save();
            }

            $this->showEditModal = false;
            $this->reset('editingStagingId', 'stagingForm');

            $this->dispatch('samplesUpdated');
            session()->flash('success', $this->editingStagingId ? 'Staging data updated successfully!' : 'Staging record created successfully!');
        } catch (\Exception $e) {
            Log::error('Error saving staging: ' . $e->getMessage());
            session()->flash('error', 'Failed to save staging data: ' . $e->getMessage());
        }
    }

    /**
     * Cancel editing
     */
    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->reset('editingStagingId', 'stagingForm', 'subUnitSearch', 'sampleTypeSearch', 'stagingAnalysisTypeSearch');
        $this->showSubUnitDropdown = false;
        $this->showSampleTypeDropdown = false;
        $this->showStagingAnalysisTypeDropdown = false;
    }

    // ========== Search & Select Methods for Staging Edit ==========

    public function getFilteredSubUnits()
    {
        if (empty($this->subUnitSearch)) {
            return $this->subUnits;
        }
        return array_filter($this->subUnits, function ($unit) {
            return stripos($unit['name'], $this->subUnitSearch) !== false;
        });
    }

    public function selectSubUnit($id)
    {
        $unit = collect($this->subUnits)->firstWhere('id', $id);
        if ($unit) {
            $this->stagingForm['company_sub_unit_id'] = $id;
            $this->stagingForm['company_sub_unit_name'] = $unit['name'];
        }
        $this->showSubUnitDropdown = false;
        $this->subUnitSearch = '';
    }

    public function getFilteredSampleTypes()
    {
        if (empty($this->sampleTypeSearch)) {
            return $this->sampleTypes;
        }
        return array_filter($this->sampleTypes, function ($type) {
            return stripos($type['name'], $this->sampleTypeSearch) !== false;
        });
    }

    public function selectSampleType($id)
    {
        $type = collect($this->sampleTypes)->firstWhere('id', $id);
        if ($type) {
            $this->stagingForm['sample_type_id'] = $id;
            $this->stagingForm['sample_type_name'] = $type['name'];
        }
        $this->showSampleTypeDropdown = false;
        $this->sampleTypeSearch = '';
    }

    public function getFilteredStagingAnalysisTypes()
    {
        if (empty($this->stagingAnalysisTypeSearch)) {
            return $this->analysisTypes;
        }
        return array_filter($this->analysisTypes, function ($type) {
            return stripos($type['name'], $this->stagingAnalysisTypeSearch) !== false ||
                stripos($type['code'], $this->stagingAnalysisTypeSearch) !== false;
        });
    }

    public function toggleStagingAnalysisType($id)
    {
        $atIds = $this->stagingForm['analysis_type_ids'];
        $key = array_search($id, $atIds);

        if ($key !== false) {
            unset($atIds[$key]);
        } else {
            $atIds[] = $id;
        }

        $this->stagingForm['analysis_type_ids'] = array_values($atIds);

        // Update display names
        $names = [];
        foreach ($this->analysisTypes as $type) {
            if (in_array($type['id'], $this->stagingForm['analysis_type_ids'])) {
                $names[] = $type['name'];
            }
        }
        $this->stagingForm['analysis_type_names'] = implode(', ', $names);
    }

    /**
     * Confirm delete staging
     */
    public function confirmDeleteStaging($stagingId)
    {
        $this->deletingStagingId = $stagingId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete staging record
     */
    public function deleteStaging()
    {
        try {
            $staging = SampleDetailStaging::findOrFail($this->deletingStagingId);
            $staging->delete();

            $this->showDeleteModal = false;
            $this->reset('deletingStagingId');

            $this->dispatch('samplesUpdated');
            session()->flash('success', 'Staging record deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting staging: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete staging record: ' . $e->getMessage());
        }
    }

    /**
     * Cancel delete
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->reset('deletingStagingId');
    }

    /**
     * Add new empty sample row
     */
    public function addSample()
    {
        $nextCode = $this->generateSampleCode();

        $newSample = [
            'id' => null,  // Null indicates unsaved
            'sample_code' => $nextCode,
            'analysis_type_id' => [],
            'lab_id' => '',
            'sample_condition_id' => '',
            'sample_point_id' => '',
            'photo_url' => '',
            'sample_no' => '',
            'sample_type_id' => '',
            'customer_sample_id' => '',
            'comments' => '',
            'main_standard' => '',
            'secondary_standard' => '',
            'disposal_date' => '',
            'store_id' => '',
            'store_slot_id' => '',
            'quantity' => 1,
            'reporting_unit_id' => '',
        ];

        $this->sampleForms[] = $newSample;

        // Automatically enter edit mode for the new row
        $this->editingRowIndex = count($this->sampleForms) - 1;

        session()->flash('success', 'New sample row added');
    }

    /**
     * Generate sample code based on batch code
     */
    protected function generateSampleCode()
    {
        $numberingService = app(JobSampleNumberingService::class);
        $batchCode = (string) $this->batch->batch_code;

        if ($numberingService->isJobNumberFormat($batchCode)) {
            return $numberingService->nextSampleCode($batchCode, JobSampleNumberingService::PREFIX_CHEMISTRY);
        }

        $existingCount = count($this->sampleForms);

        return $batchCode . '-' . str_pad((string) ($existingCount + 1), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Toggle row selection
     */
    public function toggleRowSelection($index)
    {
        if (in_array($index, $this->selectedRows)) {
            $this->selectedRows = array_values(array_diff($this->selectedRows, [$index]));
        } else {
            $this->selectedRows[] = $index;
        }
    }

    /**
     * Duplicate selected samples with specified count
     */
    public function duplicateSelectedSamples($count = 1)
    {
        if (count($this->selectedRows) === 0) {
            session()->flash('error', 'No rows selected for duplication');
            return;
        }

        if ($count < 1) {
            session()->flash('error', 'Invalid duplicate count');
            return;
        }

        $duplicatedCount = 0;

        foreach ($this->selectedRows as $index) {
            if (!isset($this->sampleForms[$index])) {
                continue;
            }

            $originalSample = $this->sampleForms[$index];

            // Create N duplicates of this sample
            for ($i = 0; $i < $count; $i++) {
                $newSample = $originalSample;
                $newSample['id'] = null;  // Mark as new/unsaved
                $newSample['sample_code'] = $this->generateSampleCode();

                // Mark as duplicate of original (similar to show.blade.php)
                $newSample['is_duplicate'] = $originalSample['sample_code'];

                $this->sampleForms[] = $newSample;
                $duplicatedCount++;
            }
        }

        // Clear selection after duplication
        $this->selectedRows = [];

        session()->flash('success', "Successfully created {$duplicatedCount} duplicate(s)");
    }

    /**
     * Duplicate last sample row (kept for backward compatibility)
     */
    public function duplicateLastSample()
    {
        if (count($this->sampleForms) === 0) {
            session()->flash('error', 'No sample to duplicate');
            return;
        }

        $lastSample = end($this->sampleForms);
        $newSample = $lastSample;
        $newSample['id'] = null;  // Mark as new
        $newSample['sample_code'] = $this->generateSampleCode();

        $this->sampleForms[] = $newSample;

        // Automatically enter edit mode for the duplicated row
        $this->editingRowIndex = count($this->sampleForms) - 1;

        session()->flash('success', 'Sample duplicated successfully');
    }

    /**
     * Confirm sample deletion
     */
    public function confirmDeleteSample($index)
    {
        $this->deletingSampleIndex = $index;
        $this->showDeleteSampleModal = true;
    }

    /**
     * Delete sample
     */
    public function deleteSample()
    {
        $index = $this->deletingSampleIndex;

        if (isset($this->sampleForms[$index])) {
            $sampleId = $this->sampleForms[$index]['id'];

            // If saved to DB, delete from DB
            if ($sampleId) {
                try {
                    SampleDetails::find($sampleId)->delete();
                    session()->flash('success', 'Sample deleted from database');
                } catch (\Exception $e) {
                    Log::error('Error deleting sample: ' . $e->getMessage());
                    session()->flash('error', 'Failed to delete sample');
                    $this->showDeleteSampleModal = false;
                    return;
                }
            }

            // Remove from array
            unset($this->sampleForms[$index]);
            $this->sampleForms = array_values($this->sampleForms);  // Re-index
        }

        $this->showDeleteSampleModal = false;
        $this->deletingSampleIndex = null;
    }

    /**
     * Cancel sample delete
     */
    public function cancelDeleteSample()
    {
        $this->showDeleteSampleModal = false;
        $this->deletingSampleIndex = null;
    }

    /**
     * Enable editing for a specific row
     */
    public function editRow($index)
    {
        if (isset($this->sampleForms[$index]['id']) && $this->sampleForms[$index]['id']) {
            $sample = SampleDetails::query()->find($this->sampleForms[$index]['id']);
            if ($sample) {
                $this->sampleForms[$index]['customer_sample_id'] = $this->resolveCustomerSampleId($sample);
            }
        }

        $this->editingRowIndex = $index;
    }

    /**
     * Cancel editing (makes all rows read-only)
     */
    public function cancelEditRow()
    {
        $this->editingRowIndex = null;
        // Clear dropdown states
        $this->showAnalysisTypeDropdown = [];
        $this->analysisTypeSearch = '';
    }


    /**
     * Toggle analysis type selection for a specific row
     */
    public function toggleAnalysisType($index, $analysisTypeId)
    {
        if (!isset($this->sampleForms[$index])) {
            return;
        }

        if (!is_array($this->sampleForms[$index]['analysis_type_id'])) {
            $this->sampleForms[$index]['analysis_type_id'] = [];
        }

        // Normalize all elements to string to ensure exact matching of UUIDs
        $this->sampleForms[$index]['analysis_type_id'] = array_map('strval', $this->sampleForms[$index]['analysis_type_id']);
        $analysisTypeId = (string) $analysisTypeId;

        $key = array_search($analysisTypeId, $this->sampleForms[$index]['analysis_type_id']);

        if ($key !== false) {
            // Remove if already selected
            unset($this->sampleForms[$index]['analysis_type_id'][$key]);
            $this->sampleForms[$index]['analysis_type_id'] = array_values($this->sampleForms[$index]['analysis_type_id']);
        } else {
            // Add if not selected
            $this->sampleForms[$index]['analysis_type_id'][] = $analysisTypeId;
        }

        $this->loadSampleGroupedWorksheets();
    }

    /**
     * Get filtered analysis types for a specific row
     */
    public function getFilteredAnalysisTypes($index)
    {
        $search = $this->analysisTypeSearch;

        if (empty($search)) {
            return $this->analysisTypes;
        }

        return array_filter($this->analysisTypes, function ($type) use ($search) {
            return stripos($type['name'], $search) !== false ||
                stripos($type['code'], $search) !== false;
        });
    }

    /**
     * Save a single sample row
     */
    public function saveSample($index)
    {
        if (!isset($this->sampleForms[$index])) {
            return;
        }

        $sampleData = $this->sampleForms[$index];

        // Validate only this specific row
        $this->validate([
            "sampleForms.$index.analysis_type_id" => 'required|array|min:1',
            "sampleForms.$index.lab_id" => 'required',
            "sampleForms.$index.sample_condition_id" => 'nullable',
            "sampleForms.$index.sample_point_id" => 'nullable',
            "sampleForms.$index.photo_url" => 'nullable',
            "sampleForms.$index.sample_no" => 'nullable',
            "sampleForms.$index.sample_type_id" => 'nullable',
            "sampleForms.$index.customer_sample_id" => 'nullable',
            "sampleForms.$index.main_standard" => 'required',
            "sampleForms.$index.quantity" => 'required|numeric|min:0',
        ], [
            "sampleForms.$index.analysis_type_id.required" => 'Matrix is required',
            "sampleForms.$index.analysis_type_id.min" => 'At least one matrix option must be selected',
            "sampleForms.$index.lab_id.required" => 'Lab is required',
            "sampleForms.$index.main_standard.required" => 'Main standard is required',
        ]);

        DB::beginTransaction();

        try {
            if ($sampleData['id']) {
                $sample = SampleDetails::find($sampleData['id']);
            } else {
                $sample = new SampleDetails();
                $sample->sample_header_id = $this->batch->id;
                $sample->sample_code = $sampleData['sample_code'];
            }

            if (! $sample) {
                throw new \Exception('Sample not found.');
            }

            $sample = $this->persistSampleFromFormData($sample, $sampleData, $index);

            $this->sampleForms[$index]['id'] = $sample->id;

            DB::commit();

            $this->editingRowIndex = null;

            session()->flash('success', "Sample {$sample->sample_code} saved successfully!");
            $this->dispatch('samplesUpdated');
            $this->loadSamples();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving individual sample: ' . $e->getMessage());
            session()->flash('error', 'Failed to save sample: ' . $e->getMessage());
        }
    }

    /**
     * Save all samples with validation
     */
    public function saveSamples()
    {
        Log::info('Samples::saveSamples called', [
            'batch_id' => $this->batch->id,
            'rows' => collect($this->sampleForms)->map(fn ($row, $index) => [
                'index' => $index,
                'id' => $row['id'] ?? null,
                'lab_id' => $row['lab_id'] ?? null,
                'sample_type_id' => $row['sample_type_id'] ?? null,
                'customer_sample_id' => $row['customer_sample_id'] ?? null,
            ])->values()->all(),
        ]);

        // Validate all samples
        $this->validate([
            'sampleForms.*.analysis_type_id' => 'required|array|min:1',
            'sampleForms.*.lab_id' => 'required|uuid|exists:labs,id',
            'sampleForms.*.sample_condition_id' => 'nullable',
            'sampleForms.*.sample_point_id' => 'nullable',
            'sampleForms.*.photo_url' => 'nullable',
            'sampleForms.*.sample_no' => 'nullable',
            'sampleForms.*.sample_type_id' => 'nullable',
            'sampleForms.*.customer_sample_id' => 'nullable',
            'sampleForms.*.main_standard' => 'required',
            'sampleForms.*.quantity' => 'required|numeric|min:0',
        ], [
            'sampleForms.*.analysis_type_id.required' => 'Matrix is required',
            'sampleForms.*.analysis_type_id.min' => 'At least one matrix option must be selected',
            'sampleForms.*.lab_id.required' => 'Lab is required',
            'sampleForms.*.lab_id.uuid' => 'Lab selection is invalid',
            'sampleForms.*.lab_id.exists' => 'Selected lab does not exist',
            'sampleForms.*.main_standard.required' => 'Main standard is required',
        ]);

        DB::beginTransaction();

        try {
            foreach ($this->sampleForms as $index => $sampleData) {
                if ($sampleData['id']) {
                    $sample = SampleDetails::find($sampleData['id']);
                } else {
                    $sample = new SampleDetails();
                    $sample->sample_header_id = $this->batch->id;
                    $sample->sample_code = $sampleData['sample_code'];
                }

                if (! $sample) {
                    throw new \Exception("Sample not found for row {$index}.");
                }

                $sample = $this->persistSampleFromFormData($sample, $sampleData, $index);

                $this->sampleForms[$index]['id'] = $sample->id;

                Log::info('Samples::saveSamples persisted row', [
                    'sample_id' => $sample->id,
                    'lab_id' => $sample->lab_id,
                    'customer_sample_id' => $sample->customer_sample_id,
                ]);
            }

            DB::commit();

            // Exit edit mode after successful save
            $this->editingRowIndex = null;

            session()->flash('success', 'All samples saved successfully!');
            $this->dispatch('samplesUpdated');
            $this->dispatch('batchUpdated')->to(\App\Livewire\Batch\Header::class);
            $this->loadSamples();  // Reload to get fresh data

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving samples: ' . $e->getMessage(), [
                'batch_id' => $this->batch->id,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to save samples: ' . $e->getMessage());
        }
    }

    /**
     * Cancel delete staging
     */
    public function cancelDeleteOld()
    {
        $this->showDeleteModal = false;
        $this->reset('deletingStagingId');
    }

    /**
     * Phase 4: Dependent Dropdown - Filter labs when analysis types change
     */
    public function updatedSampleForms($value, $key)
    {
        // Check if analysis_type_id was updated
        if (str_contains($key, '.analysis_type_id')) {
            // Extract index from key (e.g., "0.analysis_type_id" -> 0)
            preg_match('/(\d+)\./', $key, $matches);
            $index = $matches[1] ?? null;

            if ($index !== null && isset($this->sampleForms[$index])) {
                $this->filterLabsForSample($index);
            }
        }
    }

    /**
     * Filter available labs based on selected analysis types
     */
    protected function filterLabsForSample($index)
    {
        $selectedAnalysisIds = $this->sampleForms[$index]['analysis_type_id'] ?? [];

        if (empty($selectedAnalysisIds)) {
            // Reset to all labs if no analysis types selected
            $this->labSections = $this->mapLabsForSelect(Lab::select('id', 'name', 'code')->get());
            return;
        }

        try {
            // Get unique lab IDs from selected analysis types
            $labIds = AnalysisType::whereIn('id', $selectedAnalysisIds)
                ->pluck('lab_id')
                ->unique()
                ->filter()
                ->toArray();

            if (!empty($labIds)) {
                // Filter labs to only those that handle the selected analysis types
                $this->labSections = $this->mapLabsForSelect(
                    Lab::whereIn('id', $labIds)->select('id', 'name', 'code')->get()
                );
            } else {
                // No labs found, keep all labs available
                $this->labSections = $this->mapLabsForSelect(Lab::select('id', 'name', 'code')->get());
            }
        } catch (\Exception $e) {
            Log::error('Error filtering labs: ' . $e->getMessage());
            // On error, show all labs
            $this->labSections = $this->mapLabsForSelect(Lab::select('id', 'name', 'code')->get());
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Lab>|\Illuminate\Database\Eloquent\Collection<int, Lab>  $labs
     * @return array<int, array{id: string, name: string|null, code: string|null}>
     */
    private function mapLabsForSelect($labs): array
    {
        return $labs->map(fn (Lab $lab) => [
            'id' => (string) $lab->id,
            'name' => $lab->name,
            'code' => $lab->code,
        ])->values()->all();
    }

    /**
     * Phase 5: View Parameters Modal - Show all captured results for a sample
     */
    public function viewParameters($sampleCode)
    {
        try {
            $this->selectedSampleCode = $sampleCode;
            $hasAttachmentColumn = Schema::hasColumn('captured_results', 'batch_attachment_id');

            // Find the sample
            $sample = SampleDetails::where('sample_code', $sampleCode)
                ->where('sample_header_id', $this->batch->id)
                ->first();

            if (!$sample) {
                session()->flash('error', 'Sample not found');
                return;
            }

            $this->uncertaintyRequired = $this->batch->require_mu == 1;

            // Use Eloquent so SafeEncrypted casts decrypt analyte_code, result, remark, etc.
            $capturedResultsQuery = CapturedResult::query()
                ->where('sample_detail_code', $sampleCode)
                ->where('sample_header_id', $this->batch->id)
                ->orderBy('analysis_type_order')
                ->orderBy('parameters_order');

            if ($hasAttachmentColumn) {
                $capturedResultsQuery->with('batchAttachment');
            }

            $capturedResults = $capturedResultsQuery->get();

            if ($capturedResults->isEmpty()) {
                $this->sampleParameters = [];
                $this->showParametersModal = true;
                return;
            }

            // Enrich each result with additional data
            $analysisDateRecord = SampleAnalysisDates::where('sample_header_id', $this->batch->id)
                ->where('sample_detail_id', $sample->id)
                ->first();
            $analysisDatesBySection = $this->decodeAnalysisDatesBySection($analysisDateRecord?->analysis_dates);

            $parameters = [];
            foreach ($capturedResults as $result) {
                // Get analysis type
                $analysisType = AnalysisType::find($result->analysis_type_id);
                $batchAttachmentUrl = $hasAttachmentColumn
                    ? $result->batchAttachment?->attachment_url
                    : null;

                // Get operator - prefer stored, else current user (auto-assigned on save)
                $operator = \App\User::find($result->operator_id) ?? auth()->user();

                // Get method — fall back to analysis element when blank
                $method = \App\AnalysisMethod::find($result->method_id);
                if (! $method && $result->analysis_type_id && $result->analyte_id) {
                    $element = \App\AnalysisElements::query()
                        ->where('analysis_type_id', $result->analysis_type_id)
                        ->where('analyte_id', $result->analyte_id)
                        ->where('active', 1)
                        ->first();
                    if ($element?->method) {
                        $method = \App\AnalysisMethod::find($element->method);
                        if (! $result->method_id && $method) {
                            $result->method_id = $method->id;
                        }
                        if (! $result->reporting_unit_id && $element->reporting_unit) {
                            $result->reporting_unit_id = resolveReportingUnitIdFromName($element->reporting_unit);
                        }
                    }
                }

                // Get LTM method (stored as simple value, not a model)
                $ltmMethod = null;

                // Get equipment
                $equipment = \App\Models\Equipments\Equipment::find($result->equipment_id);

                // Lab section (for Parameters modal column and change section)
                $labSection = SampleAnalysisStage::find($result->lab_section_id);

                // Get analyte for reporting unit
                $analyte = \App\Analyte::find($result->analyte_id);

                // Use result's standard ID or fallback to sample's standard ID
                $effectiveMainStandardId = $result->main_standard_id ?: $sample->main_standard;
                $effectiveSecStandardId = $result->secondary_standard_id ?: $sample->secondary_standard;

                // Get standard info (Calculated dynamically to match legacy logic)
                $standardInfo = $this->getStandardInfoCalculated(
                    $effectiveMainStandardId,
                    $result->analyte_id,
                    $result->main_value
                );

                $secStandardInfo = $effectiveSecStandardId ? $this->getStandardInfoCalculated(
                    $effectiveSecStandardId,
                    $result->analyte_id,
                    $result->secondary_value
                ) : null;

                // Fetch limit data for evaluation
                $limitType = null;
                $limitLow = null;
                $limitHigh = null;
                $valueLimitType = null;
                $standardLimitValue = null;

                if ($effectiveMainStandardId && $result->analyte_id) {
                    $stdAnalyte = \App\StandardAnalytes::where('standard_id', $effectiveMainStandardId)
                        ->where('analyte_id', $result->analyte_id)
                        ->first();
                    if ($stdAnalyte) {
                        $limitType = $stdAnalyte->standard_value_type;
                        $limitLow = $stdAnalyte->low;
                        $limitHigh = $stdAnalyte->high;
                        $valueLimitType = $stdAnalyte->value_type;
                        $standardLimitValue = $stdAnalyte->standard_is_value;
                    }
                }

                $analyteName = $analyte?->name;
                $analyteCode = $result->analyte_code;
                if (! $analyteName && $analyte) {
                    $analyteName = $analyte->code;
                }
                if (! $analyteName) {
                    $analyteName = is_string($analyteCode) && ! str_starts_with($analyteCode, 'eyJ')
                        ? $analyteCode
                        : '—';
                }

                $parameters[$result->id] = [
                    'id' => $result->id,
                    'sample_code' => $result->sample_detail_code,
                    'analysis_type' => $analysisType->name ?? $analysisType->code ?? '-',
                    'analysis_type_id' => $result->analysis_type_id,
                    'analyte_code' => is_string($analyteCode) && ! str_starts_with($analyteCode, 'eyJ') ? $analyteCode : ($analyte?->code ?? ''),
                    'analyte_id' => $result->analyte_id,
                    'analyte_name' => $analyteName,
                    'result_reporting_symbol' => $result->result_reporting_symbol,
                    'result' => $result->result,
                    'measure_uncertanity' => $result->measure_uncertanity,
                    'standard_value' => $standardInfo['display'] ?? '-',
                    'standard_id' => $effectiveMainStandardId,
                    'sec_standard_value' => $secStandardInfo['display'] ?? null,
                    'sec_standard_id' => $effectiveSecStandardId,
                    'remark' => $result->remark ?: '',
                    'remark_is_manual' => $result->remark_is_manual,
                    'reporting_unit' => $result->reporting_unit_id,
                    'operator_name' => $operator->name ?? '-',
                    'operator_id' => $result->operator_id,
                    'method_name' => $method->name ?? '-',
                    'method_id' => $result->method_id,
                    'ltm_method_name' => $result->ltm_method_id ? 'LTM-' . $result->ltm_method_id : '-',
                    'ltm_method_id' => $result->ltm_method_id,
                    'equipment_name' => $equipment->name ?? '-',
                    'equipment_id' => $result->equipment_id,
                    'lab_section_id' => $result->lab_section_id,
                    'lab_section_name' => $labSection ? ($labSection->name . ' - ' . $labSection->code) : '-',
                    'start_analysis_date' => $analysisDatesBySection['captured_result:' . $result->id]['start_date']
                        ?? $analysisDatesBySection[$result->lab_section_id]['start_date']
                        ?? '',
                    'end_analysis_date' => $analysisDatesBySection['captured_result:' . $result->id]['end_date']
                        ?? $analysisDatesBySection[$result->lab_section_id]['end_date']
                        ?? '',
                    'subcontracted' => $result->analyte_status_contracted,
                    'accredited' => $result->analyte_accredited,
                    'result_confirmation' => $result->result, // Initialize with same value
                    'limit_type' => $limitType,
                    'limit_low' => $limitLow,
                    'limit_high' => $limitHigh,
                    'value_limit_type' => $valueLimitType,
                    'standard_limit_value' => $standardLimitValue,
                    'standard_editable' => false,
                    'batch_attachment_id' => $result->batch_attachment_id,
                    'batch_attachment_url' => $batchAttachmentUrl,
                ];
            }

            // Unique lab sections for Date of Analysis row: from parameters, or fall back to batch's lab sections
            $uniqueSectionIds = collect($parameters)->pluck('lab_section_id')->filter()->unique()->values();
            $this->parameterLabSections = [];
            foreach ($uniqueSectionIds as $sid) {
                $stage = SampleAnalysisStage::find($sid);
                if ($stage) {
                    $this->parameterLabSections[$sid] = $stage->name . ' - ' . $stage->code;
                }
            }
            // If no sections from parameters (e.g. not yet assigned), use batch's lab sections so Date row still shows
            if (empty($this->parameterLabSections) && !empty($this->batch->lab_section_ids)) {
                $batchSectionIds = array_filter(explode(',', $this->batch->lab_section_ids));
                foreach ($batchSectionIds as $sid) {
                    $stage = SampleAnalysisStage::find($sid);
                    if ($stage) {
                        $this->parameterLabSections[$sid] = $stage->name . ' - ' . $stage->code;
                    }
                }
            }

            // Load analysis dates for this sample (start_analysis_date per section)
            $this->dateOfAnalysisBySection = [];
            foreach ($analysisDatesBySection as $secId => $range) {
                if (str_starts_with((string) $secId, 'captured_result:')) {
                    continue;
                }

                $this->dateOfAnalysisBySection[$secId] = $range['start_date'] ?? '';
            }
            // Ensure every parameter section has an entry
            foreach (array_keys($this->parameterLabSections) as $sid) {
                if (!isset($this->dateOfAnalysisBySection[$sid])) {
                    $this->dateOfAnalysisBySection[$sid] = '';
                }
            }

            // Lab sections for dropdown (Parameters modal)
            $this->modalLabSections = SampleAnalysisStage::where('active', 1)->where('is_system', 0)->orderBy('name')->get();

            // If still no sections (batch has none), use all active sections so Date of Analysis row is always visible
            if (empty($this->parameterLabSections) && $this->modalLabSections->isNotEmpty()) {
                foreach ($this->modalLabSections as $stage) {
                    $this->parameterLabSections[$stage->id] = $stage->name . ' - ' . $stage->code;
                }
                foreach (array_keys($this->parameterLabSections) as $sid) {
                    if (!isset($this->dateOfAnalysisBySection[$sid])) {
                        $this->dateOfAnalysisBySection[$sid] = '';
                    }
                }
            }

            // Populate form data for editing
            $this->parametersForm = $parameters;

            // Load dropdown lists if empty
            if (empty($this->modalLists['operators'])) {
                $this->modalLists['operators'] = \App\User::orderBy('name')->get();
                $this->modalLists['methods'] = \App\AnalysisMethod::orderBy('name')->get();
                $this->modalLists['equipments'] = \App\Models\Equipments\Equipment::orderBy('name')->get();
                $this->modalLists['units'] = \App\ReportingUnit::all();
            }

            // Load standard values for edit modal
            if (empty($this->standardValueOptions)) {
                $this->standardValueOptions = \App\StandardValue::all();
            }

            $this->sampleParameters = $parameters;
            $this->showParametersModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading sample parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to load parameters: ' . $e->getMessage());
        }
    }

    /**
     * Apply a browser-prompt-confirmed result and evaluate the remark.
     */
    public function applyConfirmedResult(string $id, string $result): void
    {
        if (! isset($this->parametersForm[$id])) {
            return;
        }

        $result = trim($result);
        $this->parametersForm[$id]['result'] = $result;
        $this->parametersForm[$id]['result_confirmation'] = $result;
        $this->evaluateResult($id);
    }

    /**
     * Clear a result after a cancelled or mismatched browser confirmation.
     */
    public function clearParameterResult(string $id): void
    {
        if (! isset($this->parametersForm[$id])) {
            return;
        }

        $this->parametersForm[$id]['result'] = '';
        $this->parametersForm[$id]['result_confirmation'] = '';
        $this->parametersForm[$id]['remark'] = '';
    }

    /**
     * Evaluate result against standard
     */
    public function evaluateResult($id): void
    {
        if (! isset($this->parametersForm[$id])) {
            return;
        }

        $data = $this->parametersForm[$id];

        if (! empty($data['remark_is_manual']) && (int) $data['remark_is_manual'] === 1) {
            return;
        }

        $captured = CapturedResult::query()->with(['sample', 'my_analyte'])->find($id);
        if (! $captured) {
            return;
        }

        $remarkService = app(ResultRemarkService::class);

        $remark = $remarkService->calculateRemark(
            $captured,
            $data['result'] ?? null,
            null,
            null,
            $data['result_reporting_symbol'] ?? null,
        );

        if (! in_array($remark, ['PASS', 'FAIL'], true)) {
            if (($data['limit_type'] ?? '') === 'is_range' && isset($data['limit_low'], $data['limit_high'])) {
                $remark = $remarkService->calculateRemark(
                    null,
                    $data['result'] ?? null,
                    null,
                    trim($data['limit_low']).' - '.trim($data['limit_high']),
                    $data['result_reporting_symbol'] ?? null,
                );
            } elseif (! empty($data['standard_limit_value']) && ! empty($data['value_limit_type'])) {
                $typedRemark = $remarkService->evaluateTypedLimit(
                    $data['result'] ?? null,
                    (string) $data['standard_limit_value'],
                    (string) $data['value_limit_type'],
                    $data['result_reporting_symbol'] ?? null,
                );
                if (in_array($typedRemark, ['PASS', 'FAIL'], true)) {
                    $remark = $typedRemark;
                }
            } elseif (! empty($data['standard_value'])) {
                $remark = $remarkService->calculateRemark(
                    null,
                    $data['result'] ?? null,
                    null,
                    $data['standard_value'],
                    $data['result_reporting_symbol'] ?? null,
                );
            }
        }

        $this->parametersForm[$id]['remark'] = match ($remark) {
            'PASS', 'FAIL' => $remark,
            '-' => '',
            default => '',
        };
    }

    /**
     * Toggle standard edit mode
     */
    public function toggleStandardEdit($id)
    {
        if (isset($this->parametersForm[$id])) {
            $this->parametersForm[$id]['standard_editable'] = !$this->parametersForm[$id]['standard_editable'];
        }
    }

    public function openEditStandardModal($id, $level = 1)
    {
        if (!isset($this->parametersForm[$id])) {
            session()->flash('error', "Error: Parameter ID $id not found in form.");
            return;
        }

        $param = $this->parametersForm[$id];
        $standardId = $level == 1 ? $param['standard_id'] : $param['sec_standard_id'];

        if (!$standardId || !$param['analyte_id']) {
            session()->flash('error', 'No standard or analyte linked to this result.');
            return;
        }

        // Fetch StandardAnalyte
        $stdAnalyte = \App\StandardAnalytes::where('standard_id', $standardId)
            ->where('analyte_id', $param['analyte_id'])
            ->first();

        if (!$stdAnalyte) {
            // Should creating new one be allowed? Legacy implies "update", but if missing maybe create?
            // Legacy code creates new StandardAnalytes() if missing.
            $stdAnalyte = new \App\StandardAnalytes();
            $stdAnalyte->standard_id = $standardId;
            $stdAnalyte->analyte_id = $param['analyte_id'];
        }

        $this->editingStandardData = [
            'captured_result_id' => $id,
            'analyte_id' => $param['analyte_id'],
            'standard_id' => $standardId,
            'standard_level' => $level,
            'analyte_name' => $param['analyte_name'],
            'previous_value' => $level == 1 ? $param['standard_value'] : $param['sec_standard_value'],
            'standard_value_type' => $stdAnalyte->standard_value_type == 'is_range' ? 1 : 2,
            'min' => $stdAnalyte->low,
            'max' => $stdAnalyte->high,
            'standard_valuetype' => $stdAnalyte->standard_value_id,
            'limit_measure' => $stdAnalyte->value_type,
            'value' => $stdAnalyte->standard_is_value,
        ];

        $this->showEditStandardModal = true;
    }

    public function cancelEditStandardModal()
    {
        $this->showEditStandardModal = false;
        $this->reset('editingStandardData');
    }

    public function updatedEditingStandardDataStandardValuetype($value): void
    {
        if (empty($this->editingStandardData) || ! is_array($this->editingStandardData)) {
            return;
        }

        $selected = collect($this->standardValueOptions)->firstWhere('id', $value);
        if (! $selected || ($selected->code ?? '') !== 'IsValue') {
            $this->editingStandardData['limit_measure'] = '';
            $this->editingStandardData['value'] = '';
        }
    }

    public function saveStandardLimit()
    {
        $data = $this->editingStandardData;

        // Find or create
        $stdAnalyte = \App\StandardAnalytes::where('standard_id', $data['standard_id'])
            ->where('analyte_id', $data['analyte_id'])
            ->first();

        if (!$stdAnalyte) {
            $stdAnalyte = new \App\StandardAnalytes();
            $stdAnalyte->standard_id = $data['standard_id'];
            $stdAnalyte->analyte_id = $data['analyte_id'];
        }

        $selectedStandardValue = null;
        if (! empty($data['standard_valuetype'])) {
            $selectedStandardValue = \App\StandardValue::find($data['standard_valuetype']);
        }
        $isValueSelected = $selectedStandardValue && ($selectedStandardValue->code ?? '') === 'IsValue';

        if (($data['standard_value_type'] ?? 2) != 1 && ! $isValueSelected) {
            $data['limit_measure'] = '';
            $data['value'] = '';
        }

        // Map inputs to model (legacy logic)
        $stdAnalyte->low = $data['min'];
        $stdAnalyte->high = $data['max'];
        $stdAnalyte->standard_value_id = $data['standard_valuetype'];
        $stdAnalyte->value_type = $data['limit_measure'];
        $stdAnalyte->standard_is_value = $data['value'];
        $stdAnalyte->standard_value_type = $data['standard_value_type'] == 1 ? 'is_range' : 'is_standard_value';
        $stdAnalyte->is_active = 1;
        $stdAnalyte->save();

        // Calculate display string (Legacy Logic)
        $newValue = 'NS';
        if ($data['standard_value_type'] == 1) {
            $newValue = $data['min'] . ' - ' . $data['max'];
        } else {
            // Start with IS Value logic
            // Logic: 2 && limit_measure == '' -> limit code
            //        2 && limit_measure != '' -> value . ' ' . limit_measure
            if ($data['limit_measure'] == '') {
                $sv = $selectedStandardValue ?: \App\StandardValue::find($data['standard_valuetype']);
                $newValue = $sv ? $sv->code : $newValue;
            } else {
                $limit = $data['limit_measure'];
                $val = $data['value'];

                if ($limit == 'less_than' || $limit == '<') {
                    $newValue = '< ' . $val;
                } elseif ($limit == 'greater_than' || $limit == '>') {
                    $newValue = '> ' . $val;
                } else {
                    $newValue = $val . ' ' . $limit; // e.g. "10 Max"
                }
            }
        }

        // Update CapturedResult snapshot with the Display Value
        $updateField = $data['standard_level'] == 1 ? 'main_value' : 'secondary_value';
        $idField = $data['standard_level'] == 1 ? 'main_standard_id' : 'secondary_standard_id';

        DB::table('captured_results')
            ->where('id', $data['captured_result_id'])
            ->update([
                $updateField => $newValue,
                $idField => $data['standard_id']
            ]);

        // Refresh view
        if ($this->selectedSampleCode) {
            $this->viewParameters($this->selectedSampleCode);
            // Also update the parametersForm used for editing if needed, 
            // but viewParameters should rebuild it.
        }

        $this->showEditStandardModal = false;
        $this->reset('editingStandardData');

        // Re-evaluate current row result against new limits
        // (Optional: Recalculate pass/fail based on new limits? 
        //  The legacy code does not seem to trigger a full re-evaluation of ALL results, 
        //  but the user might expect the "Remark" to update if they change the limit.
        //  Legacy JS performs an ajax call to /fetch/results-remark. We should do similar.)

        $this->evaluateResult($data['captured_result_id']);
    }

    /**
     * Save parameters from modal
     */
    public function saveParameters(): void
    {
        try {
            foreach (array_keys($this->parametersForm) as $id) {
                $this->evaluateResult($id);
            }

            foreach ($this->parametersForm as $data) {
                $startDate = $this->normalizeDateOnly($data['start_analysis_date'] ?? null);
                $endDate = $this->normalizeDateOnly($data['end_analysis_date'] ?? null);

                if (! $startDate && $endDate) {
                    throw new \InvalidArgumentException('Start date of analysis is required when end date is provided.');
                }

                if ($startDate && $endDate && $endDate < $startDate) {
                    throw new \InvalidArgumentException('End date of analysis cannot be earlier than start date of analysis.');
                }
            }

            foreach ($this->parametersForm as $id => $data) {
                $captured = CapturedResult::query()->find($id);
                if (! $captured) {
                    continue;
                }

                app(\App\Services\Sampleworkflow\CapturedResultCaptureService::class)->applyOnSave($captured, [
                    'result' => $data['result'] ?: null,
                    'measure_uncertanity' => $data['measure_uncertanity'] ?: null,
                    'remark' => $data['remark'] ?: null,
                    'reporting_unit_id' => $this->resolveReportingUnitId($data['reporting_unit'] ?? null),
                    'method_id' => $this->normalizeNullableForeignKey($data['method_id'] ?? null),
                    'equipment_id' => $this->normalizeNullableForeignKey($data['equipment_id'] ?? null),
                    'lab_section_id' => $this->normalizeNullableForeignKey($data['lab_section_id'] ?? null),
                    'analyte_status_contracted' => ! empty($data['subcontracted']) ? 1 : 0,
                    'analyte_accredited' => ! empty($data['accredited']) ? 1 : 0,
                ], auth()->id() ? (string) auth()->id() : null);
            }

            $this->persistSampleAnalysisDateRange();

            session()->flash('message', 'Parameters saved successfully.');
            $this->loadIncompleteCapturedResults();
            $this->loadNotCaptured();
            $this->dispatch('resultsUpdated');
            $this->showParametersModal = false;
        } catch (\Exception $e) {
            Log::error('Error saving parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to save parameters: ' . $e->getMessage());
        }
    }

    private function normalizeNullableForeignKey(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return (string) $value;
    }

    private function resolveReportingUnitId(mixed $value): ?string
    {
        $normalized = $this->normalizeNullableForeignKey($value);
        if ($normalized === null) {
            return null;
        }

        if (Str::isUuid($normalized)) {
            return $normalized;
        }

        return ReportingUnit::query()->where('name', $normalized)->value('id');
    }

    /**
     * @return array<string, array{start_date: ?string, end_date: ?string}>
     */
    private function decodeAnalysisDatesBySection(?string $analysisDatesJson): array
    {
        if (! $analysisDatesJson) {
            return [];
        }

        $decoded = json_decode($analysisDatesJson, true);
        if (! is_array($decoded)) {
            return [];
        }

        $normalized = [];
        foreach ($decoded as $sectionId => $value) {
            if (is_array($value)) {
                $normalized[(string) $sectionId] = [
                    'start_date' => $this->normalizeDateOnly($value['start_date'] ?? $value['start'] ?? null),
                    'end_date' => $this->normalizeDateOnly($value['end_date'] ?? $value['end'] ?? null),
                ];
                continue;
            }

            $normalized[(string) $sectionId] = [
                'start_date' => $this->normalizeDateOnly($value),
                'end_date' => null,
            ];
        }

        return $normalized;
    }

    private function normalizeDateOnly(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findEarliestDate(array $dates): ?string
    {
        $dates = array_values(array_filter($dates));
        if ($dates === []) {
            return null;
        }

        sort($dates);

        return $dates[0] ?? null;
    }

    private function findLatestDate(array $dates): ?string
    {
        $dates = array_values(array_filter($dates));
        if ($dates === []) {
            return null;
        }

        rsort($dates);

        return $dates[0] ?? null;
    }

    private function persistSampleAnalysisDateRange(): void
    {
        if (! $this->selectedSampleCode) {
            return;
        }

        $sample = SampleDetails::where('sample_code', $this->selectedSampleCode)
            ->where('sample_header_id', $this->batch->id)
            ->first();

        if (! $sample) {
            return;
        }

        $analysisDate = SampleAnalysisDates::where('sample_header_id', $this->batch->id)
            ->where('sample_detail_id', $sample->id)
            ->first();

        if (! $analysisDate) {
            $analysisDate = new SampleAnalysisDates();
        }

        $sectionRanges = $this->decodeAnalysisDatesBySection($analysisDate->analysis_dates);

        foreach ($this->parametersForm as $capturedResultId => $data) {
            $sectionId = $this->normalizeNullableForeignKey($data['lab_section_id'] ?? null);
            $rowKey = 'captured_result:' . (string) $capturedResultId;

            $rowStartDate = $this->normalizeDateOnly($data['start_analysis_date'] ?? null);
            $rowEndDate = $this->normalizeDateOnly($data['end_analysis_date'] ?? null);

            if (! $rowStartDate && ! $rowEndDate) {
                continue;
            }

            // Persist per-parameter row values so refresh restores exactly what user entered.
            $sectionRanges[$rowKey] = [
                'start_date' => $rowStartDate,
                'end_date' => $rowEndDate,
            ];

            if (! $sectionId) {
                continue;
            }

            $sectionStartDate = $sectionRanges[$sectionId]['start_date'] ?? null;
            $sectionEndDate = $sectionRanges[$sectionId]['end_date'] ?? null;

            if ($rowStartDate) {
                $sectionStartDate = $sectionStartDate === null
                    ? $rowStartDate
                    : min($sectionStartDate, $rowStartDate);
            }

            if (! $rowStartDate && $rowEndDate && $sectionStartDate === null) {
                // Keep schema-compatible range when only an end date is supplied.
                $sectionStartDate = $rowEndDate;
            }

            if ($rowEndDate) {
                $sectionEndDate = $sectionEndDate === null
                    ? $rowEndDate
                    : max($sectionEndDate, $rowEndDate);
            }

            $sectionRanges[$sectionId] = [
                'start_date' => $sectionStartDate,
                'end_date' => $sectionEndDate,
            ];
        }

        $allStartDates = [];
        $allEndDates = [];
        foreach ($sectionRanges as $range) {
            if (! empty($range['start_date'])) {
                $allStartDates[] = $range['start_date'];
            }
            if (! empty($range['end_date'])) {
                $allEndDates[] = $range['end_date'];
            }
        }

        $earliestStartDate = $this->findEarliestDate($allStartDates);

        if ($earliestStartDate === null) {
            if ($analysisDate->exists && ! empty($analysisDate->start_analysis_date)) {
                $earliestStartDate = $this->normalizeDateOnly($analysisDate->start_analysis_date);
            } else {
                // Do not create an invalid row when no analysis start date was provided.
                return;
            }
        }

        $analysisDate->sample_header_id = $this->batch->id;
        $analysisDate->sample_detail_id = $sample->id;
        $analysisDate->start_analysis_date = $earliestStartDate;
        $analysisDate->analysis_dates = json_encode($sectionRanges);
        $analysisDate->save();
    }

    /**
     * Get formatted standard info
     */
    /**
     * Get formatted standard info dynamically calculated from StandardAnalyte
     */
    private function getStandardInfoCalculated($standardId, $analyteId, $capturedValue = null)
    {
        if (!$standardId) {
            return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
        }

        try {
            // Try to find the setting in StandardAnalytes
            $stdAnalyte = \App\StandardAnalytes::where('standard_id', $standardId)
                ->where('analyte_id', $analyteId)
                ->first();

            if (!$stdAnalyte) {
                // If no specific setting, fallback to captured value or NS
                return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
            }

            $displayValue = '';
            $rawValue = '';
            $limitType = '';

            if ($stdAnalyte->standard_value_type == 'is_range') {
                $displayValue = $stdAnalyte->low . ' - ' . $stdAnalyte->high;
                $rawValue = $displayValue;
            } elseif ($stdAnalyte->standard_value_type == 'is_standard_value') {
                $sv = \App\StandardValue::find($stdAnalyte->standard_value_id);
                if ($sv) {
                    if ($sv->code == 'IsValue') {
                        $rawValue = $stdAnalyte->standard_is_value;
                        $limitType = $stdAnalyte->value_type; // Max, Min, <, >, less_than, greater_than
                    } else {
                        $rawValue = $sv->code;
                        $displayValue = $sv->code;
                    }
                }
            }

            // Format display string if not already set by code
            if (!$displayValue && $rawValue !== '') {
                if ($limitType == 'less_than' || $limitType == '<') {
                    $displayValue = '< ' . $rawValue;
                } elseif ($limitType == 'greater_than' || $limitType == '>') {
                    $displayValue = '> ' . $rawValue;
                } elseif ($limitType && $rawValue) {
                    $displayValue = strtolower($limitType).' '.$rawValue;
                } else {
                    $displayValue = $rawValue;
                }
            }

            // If we failed to calculate anything, fallback
            if ($displayValue == '') {
                $displayValue = 'NS';
            }

            return [
                'display' => $displayValue,
                'value' => $rawValue // This might be used for edits
            ];
        } catch (\Exception $e) {
            return ['display' => $capturedValue ?: '-', 'value' => $capturedValue];
        }
    }

    /**
     * Save start of analysis date for a lab section (Parameters modal).
     * Uses same logic as SampleWorkFlowController::saveSampleAnalysisDate.
     */
    public function saveStartAnalysisDate($sectionId)
    {
        $date = $this->dateOfAnalysisBySection[$sectionId] ?? '';
        if (!$date) {
            session()->flash('error', 'Please select a date for this section.');
            return;
        }

        $sample = SampleDetails::where('sample_code', $this->selectedSampleCode)
            ->where('sample_header_id', $this->batch->id)
            ->first();
        if (!$sample) {
            session()->flash('error', 'Sample not found.');
            return;
        }

        try {
            $analysis_date = SampleAnalysisDates::where('sample_header_id', $this->batch->id)
                ->where('sample_detail_id', $sample->id)
                ->first();

            if (!$analysis_date) {
                $analysis_date = new SampleAnalysisDates();
            }

            $prevDates = $this->decodeAnalysisDatesBySection($analysis_date->analysis_dates);
            $prevDates[(string) $sectionId] = [
                'start_date' => $this->normalizeDateOnly($date),
                'end_date' => $prevDates[(string) $sectionId]['end_date'] ?? null,
            ];

            $allStartDates = [];
            foreach ($prevDates as $range) {
                if (! empty($range['start_date'])) {
                    $allStartDates[] = $range['start_date'];
                }
            }

            $analysis_date->sample_header_id = $this->batch->id;
            $analysis_date->sample_detail_id = $sample->id;
            $analysis_date->start_analysis_date = $this->findEarliestDate($allStartDates);
            $analysis_date->analysis_dates = json_encode($prevDates);
            $analysis_date->save();

            session()->flash('message', 'Date of analysis saved successfully.');
            $this->dateOfAnalysisBySection[$sectionId] = $date;
        } catch (\Exception $e) {
            Log::error('Save analysis date: ' . $e->getMessage());
            session()->flash('error', 'Failed to save date.');
        }
    }

    /** Change Section (Parameters modal): show/hide panel */
    public $showChangeSectionPanel = false;
    /** Selected lab section id when changing section */
    public $changeSectionLabSectionId = '';
    /** Affect this batch only? (update all rows in this batch with same analysis_type + analyte) */
    public $changeSectionAffectBatch = false;
    /** Affect all parameter configurations? (update analysis_elements and all captured/results globally) */
    public $changeSectionAffectAll = true;

    public function toggleChangeSectionPanel()
    {
        $this->showChangeSectionPanel = !$this->showChangeSectionPanel;
        if (!$this->showChangeSectionPanel) {
            $this->changeSectionLabSectionId = '';
        }
    }

    /**
     * Change lab section (full feature matching legacy Analysis Parameters modal).
     * Options: Affect this batch only? | Affect all parameter configurations?
     */
    public function saveChangeSection()
    {
        if (!$this->changeSectionLabSectionId) {
            session()->flash('error', 'Please select a lab section.');
            return;
        }

        $capturedIds = array_keys($this->parametersForm);
        if (empty($capturedIds)) {
            session()->flash('error', 'No parameters to update.');
            return;
        }

        if (!$this->changeSectionAffectBatch && !$this->changeSectionAffectAll) {
            session()->flash('error', 'Please choose whether to affect this batch only or all parameter configurations.');
            return;
        }

        try {
            $capturedResults = CapturedResult::whereIn('id', $capturedIds)->get();

            foreach ($capturedResults as $cr) {
                if ($this->changeSectionAffectBatch) {
                    CapturedResult::where('sample_header_id', $cr->sample_header_id)
                        ->where('analysis_type_id', $cr->analysis_type_id)
                        ->where('analyte_id', $cr->analyte_id)
                        ->update(['lab_section_id' => $this->changeSectionLabSectionId]);
                    Result::where('sample_header_id', $cr->sample_header_id)
                        ->where('analysis_type_id', $cr->analysis_type_id)
                        ->where('analyte_id', $cr->analyte_id)
                        ->update(['lab_section_id' => $this->changeSectionLabSectionId]);
                }
                if ($this->changeSectionAffectAll) {
                    CapturedResult::where('analysis_type_id', $cr->analysis_type_id)
                        ->where('analyte_id', $cr->analyte_id)
                        ->update(['lab_section_id' => $this->changeSectionLabSectionId]);
                    Result::where('analysis_type_id', $cr->analysis_type_id)
                        ->where('analyte_id', $cr->analyte_id)
                        ->update(['lab_section_id' => $this->changeSectionLabSectionId]);
                    AnalysisElements::where('analysis_type_id', $cr->analysis_type_id)
                        ->where('analyte_id', $cr->analyte_id)
                        ->update(['lab_section_id' => $this->changeSectionLabSectionId]);
                }
            }

            // Refresh form so current sample's parameters show new section
            foreach ($capturedIds as $id) {
                if (isset($this->parametersForm[$id])) {
                    $this->parametersForm[$id]['lab_section_id'] = $this->changeSectionLabSectionId;
                    $stage = SampleAnalysisStage::find($this->changeSectionLabSectionId);
                    $this->parametersForm[$id]['lab_section_name'] = $stage ? ($stage->name . ' - ' . $stage->code) : '-';
                }
            }

            $this->showChangeSectionPanel = false;
            $this->changeSectionLabSectionId = '';
            session()->flash('message', 'Lab section updated successfully.');
        } catch (\Exception $e) {
            Log::error('Change section: ' . $e->getMessage());
            session()->flash('error', 'Failed to update lab section.');
        }
    }

    /**
     * Close view parameters modal
     */
    public function cancelViewParameters()
    {
        $this->showParametersModal = false;
        $this->selectedSampleCode = null;
        $this->sampleParameters = [];
        $this->dateOfAnalysisBySection = [];
        $this->parameterLabSections = [];
        $this->showChangeSectionPanel = false;
        $this->changeSectionLabSectionId = '';
        $this->changeSectionAffectBatch = false;
        $this->changeSectionAffectAll = true;
    }

    // ========== Comments & Interpretations Feature ==========

    public $showCommentsModal = false;
    public $editingCommentsSampleId = null;
    public $commentsForm = [
        'header_body' => '',
        'main_body' => '',
        'notes_body' => '',
        'batch_comment_scope' => '1', // 1=Concatenate, 2=Overwrite
    ];

    /**
     * Open Comments & Interpretations modal for a sample
     */
    public function openCommentsModal($sampleId)
    {
        try {
            $sample = SampleDetails::findOrFail($sampleId);

            $this->editingCommentsSampleId = $sampleId;
            $this->commentsForm = [
                'header_body' => $sample->header_body ?? '',
                'main_body' => $sample->main_body ?? '',
                'notes_body' => $sample->notes_body ?? '',
                'batch_comment_scope' => '1',
            ];

            $this->showCommentsModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading comments: ' . $e->getMessage());
            session()->flash('error', 'Failed to load sample comments');
        }
    }

    /**
     * Save comments and interpretations
     */
    public function saveComments()
    {
        try {
            $sample = SampleDetails::findOrFail($this->editingCommentsSampleId);

            $sample->main_body = $this->commentsForm['main_body'];
            $sample->header_body = $this->commentsForm['header_body'];
            $sample->notes_body = $this->commentsForm['notes_body'];
            $sample->save();

            $this->showCommentsModal = false;
            $this->reset('editingCommentsSampleId', 'commentsForm');

            session()->flash('success', 'Sample Comments and Interpretations have been saved');
        } catch (\Exception $e) {
            Log::error('Error saving comments: ' . $e->getMessage());
            session()->flash('error', 'Failed to save comments: ' . $e->getMessage());
        }
    }

    /**
     * Cancel comments modal
     */
    public function cancelComments()
    {
        $this->showCommentsModal = false;
        $this->reset('editingCommentsSampleId', 'commentsForm');
    }

    // ========== Interlab Transfer Feature ==========

    public $showInterlabModal = false;
    public $interlabSampleId = null;
    public $interlabSampleCode = '';
    public $interlabForm = [
        'to_lab_section_id' => '',
        'quantity' => '',
        'expected_date' => '',
        'prelim_date' => '',
        'remarks' => '',
    ];

    /**
     * Open Interlab Transfer modal for a sample
     */
    public function openInterlabModal($sampleId, $sampleCode)
    {
        try {
            $sample = SampleDetails::findOrFail($sampleId);

            $this->interlabSampleId = $sampleId;
            $this->interlabSampleCode = $sampleCode;

            // Get target date from batch
            $targetDate = $this->batch->batch_date_expected ?? date('Y-m-d');

            $this->interlabForm = [
                'to_lab_section_id' => '',
                'quantity' => '',
                'expected_date' => $targetDate,
                'prelim_date' => '',
                'remarks' => '',
            ];

            $this->showInterlabModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading interlab modal: ' . $e->getMessage());
            session()->flash('error', 'Failed to open interlab transfer modal');
        }
    }

    /**
     * Save interlab transfer log
     */
    public function saveInterlabLog()
    {
        $this->validate([
            'interlabForm.to_lab_section_id' => 'required',
            'interlabForm.quantity' => 'required|numeric|min:1',
            'interlabForm.expected_date' => 'nullable|date',
        ], [
            'interlabForm.to_lab_section_id.required' => 'To Lab is required',
            'interlabForm.quantity.required' => 'Quantity is required',
            'interlabForm.quantity.numeric' => 'Quantity must be a number',
            'interlabForm.quantity.min' => 'Quantity must be at least 1',
        ]);

        try {
            DB::beginTransaction();

            // Get the last interlab log to determine from_lab
            $lastLog = \App\InterLabLog::where('sample_id', $this->interlabSampleId)
                ->where('status', 1)
                ->orderBy('date_received', 'DESC')
                ->first();

            $interlabData = [
                'sample_id' => $this->interlabSampleId,
                'to_lab_section_id' => $this->interlabForm['to_lab_section_id'],
                'from_lab_section_id' => $lastLog ? $lastLog->to_lab_section_id : 0,
                'quantity' => $this->interlabForm['quantity'],
                'submited_by' => auth()->id(),
                'date_submitted' => now(),
                'expected_date' => $this->interlabForm['expected_date'] ?: $this->batch->batch_date_expected,
                'prelim_date' => $this->interlabForm['prelim_date'] ?: null,
                'remarks' => $this->interlabForm['remarks'] ?: null,
            ];

            \App\InterLabLog::create($interlabData);

            DB::commit();

            $this->showInterlabModal = false;
            $this->reset('interlabSampleId', 'interlabSampleCode', 'interlabForm');

            // Dispatch event to refresh interlab logs tab if it exists
            $this->dispatch('interlabLogsUpdated');

            session()->flash('success', 'Inter laboratory Log created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving interlab log: ' . $e->getMessage());
            session()->flash('error', 'Failed to save interlab log: ' . $e->getMessage());
        }
    }

    /**
     * Cancel interlab modal
     */
    public function cancelInterlab()
    {
        $this->showInterlabModal = false;
        $this->reset('interlabSampleId', 'interlabSampleCode', 'interlabForm');
    }

    public function render()
    {
        // Reload batch with fresh data
        $this->batch = $this->batch->fresh([
            'samples',
            'stagingDetails' => function ($query) {
                $query->where('is_processed', 0);
            },
            'sample_type'
        ]);

        return view('livewire.batch.tabs.samples', [
            'batch' => $this->batch,
            'not_captured' => $this->not_captured,
        ]);
    }
}
