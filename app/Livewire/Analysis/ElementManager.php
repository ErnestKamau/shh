<?php

namespace App\Livewire\Analysis;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\AnalysisMethod;
use App\Exports\Templates\Lab\AnalysisParameterImportTemplateExport;
use App\Imports\ImportAnalysisElements;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\Equipments\Equipment;
use App\User;
use App\ReportingUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ElementManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Analysis Type ID
    public $analysisTypeId;

    // Elements Management
    public $editingElement = null;
    public $showElementModal = false;
    public bool $showImportModal = false;
    public $importFile = null;
    
    // Element Form
    public $elementForm = [
        'analyte_id' => null,
        'method' => null,
        'equipment_id' => null,
        'operator_id' => null,
        'reporting_unit' => '',
        'decimal_places' => 2,
        'significant_figures' => 3,
        'lod' => null,
        'hod' => null,
        'reporting_time' => null,
        'lab_section_id' => null,
        'level' => 1,
        'active' => true,
        'non_detectable' => false,
        'non_accredited' => false,
        'sub_contracted' => false,
        'show_on_report' => true,
        'recommend_remedies' => false,
        'remedy_header_id' => null,
        'remark_is_manual' => false,
        'result_is_calculated' => false,
        'formular_id' => null,
        'has_method_sequence' => false,
        'method_sequence_id' => null,
        'stage_header_id' => null,
        'log_entry_worksheet_id' => null,
    ];

    // Supporting Data (kept empty — options load on demand via search)
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];
    public $remedyHeaders = [];
    public $reportingUnits = [];
    public $formulars = [];
    public $logEntryWorksheets = [];
    public $methodSequences = [];

    public $stageHeaders = [];

    // Searchable Select Properties
    public $analyteSearch = '';
    public $methodSearch = '';
    public $equipmentSearch = '';
    public $operatorSearch = '';
    public $remedyHeaderSearch = '';
    public $formularSearch = '';
    public $logEntryWorksheetSearch = '';
    public $methodSequenceSearch = '';
    public $reportingUnitSearch = '';
    public $labSectionSearch = '';

    public $showAnalyteDropdown = false;
    public $showMethodDropdown = false;
    public $showEquipmentDropdown = false;
    public $showOperatorDropdown = false;
    public $showRemedyHeaderDropdown = false;
    public $showFormularDropdown = false;
    public $showLogEntryWorksheetDropdown = false;
    public $showMethodSequenceDropdown = false;
    public $showReportingUnitDropdown = false;
    public $showLabSectionDropdown = false;

    public $selectedAnalyteName = '';
    public $selectedMethodName = '';
    public $selectedEquipmentName = '';
    public $selectedOperatorName = '';
    public $selectedRemedyHeaderName = '';
    public $selectedFormularName = '';
    public $selectedLogEntryWorksheetName = '';
    public $selectedMethodSequenceName = '';
    public $selectedLabSectionName = '';

    public $filteredAnalytes = [];
    public $filteredMethods = [];
    public $filteredEquipment = [];
    public $filteredOperators = [];
    public $filteredRemedyHeaders = [];
    public $filteredFormulars = [];
    public $filteredLogEntryWorksheets = [];
    public $filteredMethodSequences = [];
    public $filteredReportingUnits = [];
    public $filteredLabSections = [];

    protected int $searchResultLimit = 20;

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'elementForm.analyte_id' => 'required|exists:analytes,id',
        'elementForm.method' => 'nullable|exists:analysis_methods,id',
        'elementForm.equipment_id' => 'nullable|exists:equipment,id',
        'elementForm.operator_id' => 'nullable|exists:users,id',
        'elementForm.reporting_unit' => 'nullable|string|max:255',
        'elementForm.decimal_places' => 'nullable|integer|min:0|max:10',
        'elementForm.significant_figures' => 'nullable|integer|min:1|max:10',
        'elementForm.lod' => 'nullable|numeric',
        'elementForm.hod' => 'nullable|numeric',
        'elementForm.reporting_time' => 'nullable|integer|min:0',
        'elementForm.lab_section_id' => 'nullable|uuid|exists:sample_analysis_stages,id',
        'elementForm.level' => 'nullable|integer|min:1',
        'elementForm.remark_is_manual' => 'boolean',
        'elementForm.result_is_calculated' => 'boolean',
        'elementForm.formular_id' => 'nullable|uuid|exists:formulas,id',
        'elementForm.log_entry_worksheet_id' => 'nullable|uuid|exists:log_entry_worksheets,id',
        'elementForm.recommend_remedies' => 'boolean',
        'elementForm.remedy_header_id' => 'nullable|uuid|exists:remedy_headers,id',
        'elementForm.has_method_sequence' => 'boolean',
        'elementForm.method_sequence_id' => 'nullable',
        'elementForm.stage_header_id' => 'nullable|exists:stage_headers,id',
    ];

    public function getRules()
    {
        $rules = $this->rules;
        
        // Make formular_id required if result_is_calculated is true
        if ($this->elementForm['result_is_calculated']) {
            $rules['elementForm.formular_id'] = 'required|uuid|exists:formulas,id';
        }
        
        // Make remedy_header_id required if recommend_remedies is true
        if ($this->elementForm['recommend_remedies']) {
            $rules['elementForm.remedy_header_id'] = 'required|uuid|exists:remedy_headers,id';
        }
        
        if ($this->elementForm['has_method_sequence']) {
            $rules['elementForm.stage_header_id'] = 'required|exists:stage_headers,id';
        }

        return $rules;
    }

    protected $messages = [
        'elementForm.analyte_id.required' => 'Analyte selection is required.',
        'elementForm.stage_header_id.required' => 'A stage header is required when "Use stage header workflow" is enabled.',
    ];

    public function mount($analysisTypeId = null)
    {
        $this->analysisTypeId = $analysisTypeId;
        $this->loadInitialData();
        $this->initializeNullLevels();
    }

    public function loadInitialData()
    {
        $this->methodSequences = collect([]);
        $this->stageHeaders = collect([]);
        $this->filteredAnalytes = collect([]);
        $this->filteredMethods = collect([]);
        $this->filteredEquipment = collect([]);
        $this->filteredOperators = collect([]);
        $this->filteredReportingUnits = collect([]);
        $this->filteredFormulars = collect([]);
        $this->filteredLogEntryWorksheets = collect([]);
        $this->filteredLabSections = collect([]);
    }

    public function getAnalysisTypeProperty()
    {
        return AnalysisType::find($this->analysisTypeId);
    }

    protected function defaultLabSectionIdFromAnalysisType(): ?string
    {
        $labSectionId = $this->analysisType?->lab_section_id;

        return $labSectionId !== null && $labSectionId !== ''
            ? (string) $labSectionId
            : null;
    }

    protected function syncSelectedLabSectionName(?string $labSectionId): void
    {
        if ($labSectionId === null || $labSectionId === '') {
            $this->selectedLabSectionName = '';

            return;
        }

        $section = \App\SampleAnalysisStage::query()->find($labSectionId);
        $this->selectedLabSectionName = $section
            ? trim($section->name.($section->code ? ' — '.$section->code : ''))
            : '';
    }

    public function getElementsProperty()
    {
        $query = AnalysisElements::with([
            'analyte', 
            'mmethod', 
            'ltmethod', 
            'equipment', 
            'operator', 
            'remedyHeader', 
            'formular',
            'methodSequence.activeVersion',
            'methodSequence.latestVersion',
            'procedureWorksheet'
        ])->where('analysis_type_id', $this->analysisTypeId);

        if ($this->search) {
            $query->whereHas('analyte', function ($q) {
                $this->applyCaseInsensitiveSearch($q, ['name'], (string) $this->search);
            });
        }

        if ($this->statusFilter) {
            $query->where('active', $this->statusFilter === 'active');
        }

        return $query->orderBy('level', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openImportModal(): void
    {
        $this->resetValidation();
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->resetValidation();
    }

    public function downloadImportTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new AnalysisParameterImportTemplateExport(),
            'import-analysis-type-elements.xlsx'
        );
    }

    public function importParameters(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'importFile.required' => 'Please choose an Excel file to import.',
            'importFile.mimes' => 'The import file must be an Excel workbook (.xlsx or .xls).',
        ]);

        try {
            $analysisType = AnalysisType::findOrFail($this->analysisTypeId);
            $importer = new ImportAnalysisElements($analysisType);

            Excel::import($importer, $this->importFile);

            $this->closeImportModal();
            $this->loadElements();

            if ($importer->importedRows === 0) {
                $detail = $importer->errors !== []
                    ? implode(' ', array_slice($importer->errors, 0, 3))
                    : 'No valid parameter rows were found in the file. Check required columns (parameter, method) and optional columns (lab_section, operator, tat, equipment).';
                $this->message = 'Import finished but no parameters were saved. '.$detail;
                $this->messageType = 'danger';

                return;
            }

            $this->message = "Parameters imported successfully ({$importer->importedRows} row(s)).";
            if ($importer->skippedRows > 0) {
                $this->message .= " {$importer->skippedRows} row(s) were skipped.";
                if ($importer->errors !== []) {
                    $this->message .= ' '.implode(' ', array_slice($importer->errors, 0, 2));
                }
            }
            $this->messageType = $importer->skippedRows > 0 ? 'danger' : 'success';
            if ($importer->skippedRows > 0 && $importer->importedRows > 0) {
                $this->messageType = 'success';
            }
        } catch (\Throwable $e) {
            Log::error('Analysis parameter import failed', [
                'analysis_type_id' => $this->analysisTypeId,
                'error' => $e->getMessage(),
            ]);

            $this->message = 'Error importing parameters: '.$e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function showCreateElementModal()
    {
        $this->resetElementForm();
        $this->elementForm['lab_section_id'] = $this->defaultLabSectionIdFromAnalysisType();
        $this->syncSelectedLabSectionName($this->elementForm['lab_section_id']);
        $this->showElementModal = true;
        $this->editingElement = null;
        $this->dispatch('element-modal-opened');
    }

    public function showEditElementModal($elementId)
    {
        $element = AnalysisElements::findOrFail($elementId);
        
        // Set editing element first to ensure wire:key updates
        $this->editingElement = $element;
        
        // Reset form data only (preserves editingElement)
        $this->resetFormDataOnly();
        
        $labSectionId = $element->lab_section_id
            ?: $this->defaultLabSectionIdFromAnalysisType();

        // Then populate with element data
        $this->elementForm = [
            'analyte_id' => $element->analyte_id,
            'method' => $element->method,
            'equipment_id' => $element->equipment_id,
            'operator_id' => $element->operator_id,
            'reporting_unit' => $element->reporting_unit,
            'decimal_places' => $element->decimal_places,
            'significant_figures' => $element->significant_figures,
            'lod' => $element->lod,
            'hod' => $element->hod,
            'reporting_time' => $element->reporting_time,
            'lab_section_id' => $labSectionId,
            'level' => $element->level,
            'active' => $element->active,
            'non_detectable' => $element->non_detectable,
            'non_accredited' => $element->non_accredited,
            'sub_contracted' => (bool) ($element->sub_contracted ?? false),
            'show_on_report' => $element->show_on_report,
            'recommend_remedies' => $element->recommend_remedies ?? false,
            'remedy_header_id' => $element->remedy_header_id,
            'remark_is_manual' => $element->remark_is_manual ?? false,
            'result_is_calculated' => $element->result_is_calculated ?? false,
            'formular_id' => $element->formular_id,
            'has_method_sequence' => $element->has_method_sequence ?? false,
            'method_sequence_id' => $element->method_sequence_id,
            'stage_header_id' => $element->stage_header_id,
            'log_entry_worksheet_id' => $element->log_entry_worksheet_id,
        ];
        
        // Set selected names for searchable selects
        $this->selectedAnalyteName = $element->analyte->name ?? '';
        $this->analyteSearch = $this->selectedAnalyteName;
        
        $this->selectedMethodName = $element->mmethod->name ?? $element->ltmethod->name ?? '';
        $this->methodSearch = $this->selectedMethodName;
        
        $this->selectedEquipmentName = $element->equipment->name ?? '';
        $this->equipmentSearch = $this->selectedEquipmentName;
        
        $this->selectedOperatorName = $element->operator->name ?? '';
        $this->operatorSearch = $this->selectedOperatorName;
        
        $this->selectedRemedyHeaderName = $element->remedyHeader->name ?? '';
        $this->remedyHeaderSearch = $this->selectedRemedyHeaderName;
        
        $this->selectedFormularName = $element->formular_id ? \App\Models\Formulars\Formula::find($element->formular_id)?->name : '';
        $this->formularSearch = $this->selectedFormularName;

        $this->selectedLogEntryWorksheetName = $element->log_entry_worksheet_id
            ? \App\Models\LogEntryWorksheets\LogEntryWorksheet::find($element->log_entry_worksheet_id)?->name
            : '';
        $this->logEntryWorksheetSearch = $this->selectedLogEntryWorksheetName;
        
        $this->selectedMethodSequenceName = $element->methodSequence->name ?? '';
        $this->methodSequenceSearch = $this->selectedMethodSequenceName;

        $this->syncSelectedLabSectionName($labSectionId ? (string) $labSectionId : null);
        
        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();

        $this->showElementModal = true;
        $this->dispatch('element-modal-opened');
    }

    public function saveElement()
    {
        // Backward compatibility: older records can have null numeric defaults.
        $this->elementForm['decimal_places'] = $this->elementForm['decimal_places'] ?? 2;
        $this->elementForm['significant_figures'] = $this->elementForm['significant_figures'] ?? 3;
        $this->elementForm['level'] = $this->elementForm['level'] ?? 1;
        $this->elementForm['reporting_time'] = ($this->elementForm['reporting_time'] === '' || $this->elementForm['reporting_time'] === null)
            ? null
            : (int) $this->elementForm['reporting_time'];

        if (! $this->elementForm['has_method_sequence']) {
            $this->elementForm['stage_header_id'] = null;
            $this->elementForm['method_sequence_id'] = null;
        } else {
            $this->elementForm['method_sequence_id'] = null;
        }

        $this->validate($this->getRules());

        try {
            $labSectionId = ! empty($this->elementForm['lab_section_id'])
                ? (string) $this->elementForm['lab_section_id']
                : $this->defaultLabSectionIdFromAnalysisType();

            $data = array_merge($this->elementForm, [
                'analysis_type_id' => $this->analysisTypeId,
                'procedure_worksheet_id' => $this->analysisType->procedure_worksheet_id ?? null,
                'lab_section_id' => $labSectionId,
            ]);

            if ($this->editingElement) {
                $this->editingElement->update($data);
                $this->message = 'Element updated successfully!';
            } else {
                AnalysisElements::create($data);
                $this->message = 'Element created successfully!';
            }

            $this->messageType = 'success';
            $this->showElementModal = false;
            $this->dispatch('element-modal-closed');
            $this->loadElements();
        } catch (\Exception $e) {
            $this->message = 'Error saving element: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteElement($elementId)
    {
        try {
            AnalysisElements::findOrFail($elementId)->delete();
            $this->message = 'Element deleted successfully!';
            $this->messageType = 'success';
            $this->loadElements();
        } catch (\Exception $e) {
            $this->message = 'Error deleting element: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetElementForm()
    {
        $this->elementForm = [
            'analyte_id' => null,
            'method' => null,
            'equipment_id' => null,
            'operator_id' => null,
            'reporting_unit' => '',
            'decimal_places' => 2,
            'significant_figures' => 3,
            'lod' => null,
            'hod' => null,
            'reporting_time' => null,
            'lab_section_id' => null,
            'level' => 1,
            'active' => true,
            'non_detectable' => false,
            'non_accredited' => false,
            'sub_contracted' => false,
            'show_on_report' => true,
            'recommend_remedies' => false,
            'remedy_header_id' => null,
            'remark_is_manual' => false,
            'result_is_calculated' => false,
            'formular_id' => null,
            'has_method_sequence' => false,
            'method_sequence_id' => null,
            'stage_header_id' => null,
            'log_entry_worksheet_id' => null,
        ];
        
        // Reset searchable select properties
        $this->analyteSearch = '';
        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->operatorSearch = '';
        $this->remedyHeaderSearch = '';
        $this->formularSearch = '';
        $this->methodSequenceSearch = '';
        $this->labSectionSearch = '';
        $this->logEntryWorksheetSearch = '';
        $this->reportingUnitSearch = '';
        
        $this->selectedAnalyteName = '';
        $this->selectedMethodName = '';
        $this->selectedEquipmentName = '';
        $this->selectedOperatorName = '';
        $this->selectedRemedyHeaderName = '';
        $this->selectedFormularName = '';
        $this->selectedMethodSequenceName = '';
        $this->selectedLabSectionName = '';
        $this->selectedLogEntryWorksheetName = '';
        
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showOperatorDropdown = false;
        $this->showRemedyHeaderDropdown = false;
        $this->showFormularDropdown = false;
        $this->showMethodSequenceDropdown = false;
        $this->showLabSectionDropdown = false;
        $this->showLogEntryWorksheetDropdown = false;
        $this->showReportingUnitDropdown = false;

        $this->filteredAnalytes = collect([]);
        $this->filteredMethods = collect([]);
        $this->filteredEquipment = collect([]);
        $this->filteredOperators = collect([]);
        $this->filteredReportingUnits = collect([]);
        $this->filteredFormulars = collect([]);
        $this->filteredLogEntryWorksheets = collect([]);
        $this->filteredLabSections = collect([]);
        
        $this->editingElement = null;
    }

    public function resetFormDataOnly()
    {
        $this->elementForm = [
            'analyte_id' => null,
            'method' => null,
            'equipment_id' => null,
            'operator_id' => null,
            'reporting_unit' => '',
            'decimal_places' => 2,
            'significant_figures' => 3,
            'lod' => null,
            'hod' => null,
            'reporting_time' => null,
            'lab_section_id' => null,
            'level' => 1,
            'active' => true,
            'non_detectable' => false,
            'non_accredited' => false,
            'sub_contracted' => false,
            'show_on_report' => true,
            'recommend_remedies' => false,
            'remedy_header_id' => null,
            'remark_is_manual' => false,
            'result_is_calculated' => false,
            'formular_id' => null,
            'has_method_sequence' => false,
            'method_sequence_id' => null,
            'stage_header_id' => null,
            'log_entry_worksheet_id' => null,
        ];
        
        // Reset searchable select properties
        $this->analyteSearch = '';
        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->operatorSearch = '';
        $this->remedyHeaderSearch = '';
        $this->formularSearch = '';
        $this->logEntryWorksheetSearch = '';
        $this->methodSequenceSearch = '';
        $this->labSectionSearch = '';
        $this->reportingUnitSearch = '';
        
        $this->selectedAnalyteName = '';
        $this->selectedMethodName = '';
        $this->selectedEquipmentName = '';
        $this->selectedOperatorName = '';
        $this->selectedRemedyHeaderName = '';
        $this->selectedFormularName = '';
        $this->selectedMethodSequenceName = '';
        $this->selectedLabSectionName = '';
        $this->selectedLogEntryWorksheetName = '';
        
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showOperatorDropdown = false;
        $this->showRemedyHeaderDropdown = false;
        $this->showFormularDropdown = false;
        $this->showMethodSequenceDropdown = false;
        $this->showLabSectionDropdown = false;
        $this->showLogEntryWorksheetDropdown = false;
        $this->showReportingUnitDropdown = false;

        $this->filteredAnalytes = collect([]);
        $this->filteredMethods = collect([]);
        $this->filteredEquipment = collect([]);
        $this->filteredOperators = collect([]);
        $this->filteredReportingUnits = collect([]);
        $this->filteredFormulars = collect([]);
        $this->filteredLogEntryWorksheets = collect([]);
        $this->filteredLabSections = collect([]);
    }

    public function updatedElementFormRecommendRemedies()
    {
        if (!$this->elementForm['recommend_remedies']) {
            $this->elementForm['remedy_header_id'] = null;
        }
    }

    public function updatedElementFormResultIsCalculated()
    {
        if (!$this->elementForm['result_is_calculated']) {
            $this->elementForm['formular_id'] = null;
        }
    }

    public function updatedElementFormHasMethodSequence()
    {
        if (! $this->elementForm['has_method_sequence']) {
            $this->elementForm['method_sequence_id'] = null;
            $this->elementForm['stage_header_id'] = null;
        }
    }

    public function updatedElementFormAnalyteId()
    {
        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();
    }

    public function updatedElementFormMethod()
    {
        $this->reloadStageHeaderOptions();
    }

    protected function reloadStageHeaderOptions(): void
    {
        $analyteId = $this->elementForm['analyte_id'] ?? null;
        $methodId = $this->elementForm['method'] ?? null;

        if (! $analyteId) {
            $this->stageHeaders = collect([]);
            $this->elementForm['stage_header_id'] = null;

            return;
        }

        $this->stageHeaders = \App\Models\StageHeader::query()
            ->where('analyte_id', $analyteId)
            ->when($methodId, fn ($q) => $q->where('method_id', $methodId))
            ->orderBy('name')
            ->get();

        $ids = $this->stageHeaders->pluck('id')->map(fn ($id) => (string) $id)->all();
        $currentId = $this->elementForm['stage_header_id'] ? (string) $this->elementForm['stage_header_id'] : null;
        if ($currentId !== null && ! in_array($currentId, $ids, true)) {
            $this->elementForm['stage_header_id'] = null;
        }
    }

    protected function loadMethodSequencesForAnalyte()
    {
        if ($this->elementForm['analyte_id']) {
            $this->methodSequences = \App\Models\MethodSequences\MethodSequence::where('analyte_id', $this->elementForm['analyte_id'])
                ->where('is_active', true)
                ->with(['activeVersion', 'latestVersion'])
                ->get();
        } else {
            $this->methodSequences = collect([]);
        }
    }

    protected function initializeNullLevels(): void
    {
        $elementsWithNullLevel = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
            ->whereNull('level')
            ->get();
        
        if ($elementsWithNullLevel->count() > 0) {
            $maxLevel = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                ->max('level') ?? 0;
            
            foreach ($elementsWithNullLevel as $index => $element) {
                $element->update(['level' => $maxLevel + $index + 1]);
            }
        }
    }

    // Searchable Select Methods
    public function updatedAnalyteSearch(): void
    {
        $this->searchAnalytes();
    }

    public function updatedMethodSearch(): void
    {
        $this->searchMethods();
    }

    public function updatedEquipmentSearch(): void
    {
        $this->searchEquipment();
    }

    public function updatedOperatorSearch(): void
    {
        $this->searchOperators();
    }

    public function updatedRemedyHeaderSearch(): void
    {
        $this->searchRemedyHeaders();
    }

    public function updatedFormularSearch(): void
    {
        $this->searchFormulars();
    }

    public function updatedLogEntryWorksheetSearch(): void
    {
        $this->searchLogEntryWorksheets();
    }

    public function updatedMethodSequenceSearch(): void
    {
        $this->searchMethodSequences();
    }

    public function updatedReportingUnitSearch(): void
    {
        $this->searchReportingUnits();
    }

    public function updatedLabSectionSearch(): void
    {
        $this->searchLabSections();
    }

    public function closeAllDropdowns(): void
    {
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showOperatorDropdown = false;
        $this->showRemedyHeaderDropdown = false;
        $this->showFormularDropdown = false;
        $this->showLogEntryWorksheetDropdown = false;
        $this->showMethodSequenceDropdown = false;
        $this->showReportingUnitDropdown = false;
        $this->showLabSectionDropdown = false;
    }

    public function openAnalyteDropdown(): void
    {
        $this->searchAnalytes();
    }

    public function searchAnalytes(): void
    {
        $this->showAnalyteDropdown = true;
        $query = Analyte::query()->where('active', 1)->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->analyteSearch);
        $this->filteredAnalytes = $query->limit($this->searchResultLimit)->get();
    }

    public function selectAnalyte($id): void
    {
        $analyte = Analyte::query()
            ->where('active', 1)
            ->with(['analysisMethods', 'equipmentItems', 'method', 'equipment'])
            ->find($id);

        if (! $analyte) {
            return;
        }

        $this->elementForm['analyte_id'] = $id;
        $this->selectedAnalyteName = $analyte->name;
        $this->analyteSearch = $analyte->name;
        $this->showAnalyteDropdown = false;

        if ($this->editingElement === null) {
            $this->prefillElementFormFromAnalyte($analyte);
        }

        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();
    }

    /**
     * Copy shared analyte fields into the add-parameter form when present.
     * Values remain freely editable afterwards.
     */
    protected function prefillElementFormFromAnalyte(Analyte $analyte): void
    {
        if ($analyte->decimal_places !== null && is_numeric($analyte->decimal_places)) {
            $this->elementForm['decimal_places'] = (int) $analyte->decimal_places;
        }

        if (filled($analyte->reporting_unit)) {
            $this->elementForm['reporting_unit'] = (string) $analyte->reporting_unit;
            $this->reportingUnitSearch = (string) $analyte->reporting_unit;
        }

        $this->elementForm['non_detectable'] = (bool) $analyte->non_detectable;
        $this->elementForm['non_accredited'] = (bool) $analyte->non_accredited;
        $this->elementForm['show_on_report'] = (bool) $analyte->show_on_report;
        $this->elementForm['active'] = (bool) $analyte->active;

        $method = $analyte->analysisMethods->first() ?? $analyte->method;

        if ($method) {
            $this->elementForm['method'] = $method->id;
            $this->selectedMethodName = $method->name;
            $this->methodSearch = $method->name;
        }

        $equipment = $analyte->equipmentItems->first() ?? $analyte->equipment;

        if ($equipment) {
            $this->elementForm['equipment_id'] = $equipment->id;
            $this->selectedEquipmentName = $equipment->name;
            $this->equipmentSearch = $equipment->name;
        }
    }

    public function clearAnalyte(): void
    {
        $this->elementForm['analyte_id'] = null;
        $this->selectedAnalyteName = '';
        $this->analyteSearch = '';
        $this->methodSequences = collect([]);
        $this->stageHeaders = collect([]);
        $this->elementForm['stage_header_id'] = null;
    }

    public function openMethodDropdown(): void
    {
        $this->searchMethods();
    }

    public function searchMethods(): void
    {
        $this->filteredMethods = $this->queryMethodOptions($this->methodSearch)->get();
        $this->showMethodDropdown = true;
    }

    protected function queryMethodOptions(?string $search = null)
    {
        $query = AnalysisMethod::query()
            ->where('active', 1)
            ->orderBy('name');

        $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) ($search ?? ''));

        return $query->limit($this->searchResultLimit);
    }

    public function selectMethod($id): void
    {
        $method = AnalysisMethod::query()->where('active', 1)->find($id);
        if (! $method) {
            return;
        }

        $this->elementForm['method'] = $id;
        $this->selectedMethodName = $method->name;
        $this->methodSearch = $method->name;
        $this->showMethodDropdown = false;
        $this->reloadStageHeaderOptions();
    }

    public function clearMethod(): void
    {
        $this->elementForm['method'] = null;
        $this->selectedMethodName = '';
        $this->methodSearch = '';
        $this->reloadStageHeaderOptions();
    }

    public function openEquipmentDropdown(): void
    {
        $this->searchEquipment();
    }

    public function searchEquipment(): void
    {
        $this->showEquipmentDropdown = true;
        $query = Equipment::query()->where('active', 1)->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->equipmentSearch);
        $this->filteredEquipment = $query->limit($this->searchResultLimit)->get();
    }

    public function selectEquipment($id): void
    {
        $equipment = Equipment::query()->where('active', 1)->find($id);
        if (! $equipment) {
            return;
        }

        $this->elementForm['equipment_id'] = $id;
        $this->selectedEquipmentName = $equipment->name;
        $this->equipmentSearch = $equipment->name;
        $this->showEquipmentDropdown = false;
    }

    public function clearEquipment(): void
    {
        $this->elementForm['equipment_id'] = null;
        $this->selectedEquipmentName = '';
        $this->equipmentSearch = '';
    }

    public function openOperatorDropdown(): void
    {
        $this->searchOperators();
    }

    public function searchOperators(): void
    {
        $this->showOperatorDropdown = true;
        $query = User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->operatorSearch);
        $this->filteredOperators = $query->limit($this->searchResultLimit)->get();
    }

    public function selectOperator($id): void
    {
        $operator = User::query()->where('active', 1)->where('is_client', 0)->find($id);
        if (! $operator) {
            return;
        }

        $this->elementForm['operator_id'] = $id;
        $this->selectedOperatorName = $operator->name;
        $this->operatorSearch = $operator->name;
        $this->showOperatorDropdown = false;
    }

    public function clearOperator(): void
    {
        $this->elementForm['operator_id'] = null;
        $this->selectedOperatorName = '';
        $this->operatorSearch = '';
    }

    public function searchRemedyHeaders(): void
    {
        $this->showRemedyHeaderDropdown = true;
        $query = \App\Models\RemedyHeader::query()->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->remedyHeaderSearch);
        $this->filteredRemedyHeaders = $query->limit($this->searchResultLimit)->get();
    }

    public function selectRemedyHeader($id): void
    {
        $remedyHeader = \App\Models\RemedyHeader::query()->find($id);
        if (! $remedyHeader) {
            return;
        }

        $this->elementForm['remedy_header_id'] = $id;
        $this->selectedRemedyHeaderName = $remedyHeader->name;
        $this->remedyHeaderSearch = $remedyHeader->name;
        $this->showRemedyHeaderDropdown = false;
    }

    public function clearRemedyHeader(): void
    {
        $this->elementForm['remedy_header_id'] = null;
        $this->selectedRemedyHeaderName = '';
        $this->remedyHeaderSearch = '';
    }

    public function openFormularDropdown(): void
    {
        $this->searchFormulars();
    }

    public function searchFormulars(): void
    {
        $this->showFormularDropdown = true;
        $query = \App\Models\Formulars\Formula::query()
            ->where('is_active', 1)
            ->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->formularSearch);
        $this->filteredFormulars = $query->limit($this->searchResultLimit)->get();
    }

    public function selectFormular($id): void
    {
        $formular = \App\Models\Formulars\Formula::query()
            ->where('is_active', 1)
            ->find($id);
        if (! $formular) {
            return;
        }

        $this->elementForm['formular_id'] = (string) $formular->id;
        $this->selectedFormularName = $formular->name;
        $this->formularSearch = $formular->name;
        $this->showFormularDropdown = false;
    }

    public function clearFormular(): void
    {
        $this->elementForm['formular_id'] = null;
        $this->selectedFormularName = '';
        $this->formularSearch = '';
    }

    public function openLogEntryWorksheetDropdown(): void
    {
        $this->searchLogEntryWorksheets();
    }

    public function searchLogEntryWorksheets(): void
    {
        $this->showLogEntryWorksheetDropdown = true;
        $query = \App\Models\LogEntryWorksheets\LogEntryWorksheet::query()
            ->where('is_active', true)
            ->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->logEntryWorksheetSearch);
        $this->filteredLogEntryWorksheets = $query->limit($this->searchResultLimit)->get();
    }

    public function selectLogEntryWorksheet(string $id): void
    {
        $worksheet = \App\Models\LogEntryWorksheets\LogEntryWorksheet::query()
            ->where('is_active', true)
            ->find($id);
        if (! $worksheet) {
            return;
        }

        $this->elementForm['log_entry_worksheet_id'] = (string) $worksheet->id;
        $this->selectedLogEntryWorksheetName = $worksheet->name;
        $this->logEntryWorksheetSearch = $worksheet->name;
        $this->showLogEntryWorksheetDropdown = false;
    }

    public function clearLogEntryWorksheet(): void
    {
        $this->elementForm['log_entry_worksheet_id'] = null;
        $this->selectedLogEntryWorksheetName = '';
        $this->logEntryWorksheetSearch = '';
    }

    public function searchMethodSequences(): void
    {
        $this->showMethodSequenceDropdown = true;
        $query = \App\Models\MethodSequences\MethodSequence::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->with(['activeVersion', 'latestVersion']);
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->methodSequenceSearch);
        $this->filteredMethodSequences = $query->limit($this->searchResultLimit)->get();
    }

    public function selectMethodSequence($id): void
    {
        $methodSequence = \App\Models\MethodSequences\MethodSequence::query()
            ->where('is_active', true)
            ->find($id);
        if (! $methodSequence) {
            return;
        }

        $this->elementForm['method_sequence_id'] = $id;
        $this->selectedMethodSequenceName = $methodSequence->name;
        $this->methodSequenceSearch = $methodSequence->name;
        $this->showMethodSequenceDropdown = false;
    }

    public function clearMethodSequence(): void
    {
        $this->elementForm['method_sequence_id'] = null;
        $this->selectedMethodSequenceName = '';
        $this->methodSequenceSearch = '';
    }

    public function openReportingUnitDropdown(): void
    {
        $this->searchReportingUnits();
    }

    public function searchReportingUnits(): void
    {
        $this->showReportingUnitDropdown = true;
        $query = ReportingUnit::query()->where('active', 1)->orderBy('name');
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->reportingUnitSearch);
        $this->filteredReportingUnits = $query->limit($this->searchResultLimit)->get();
    }

    public function selectReportingUnit($unit): void
    {
        $this->elementForm['reporting_unit'] = $unit;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function openLabSectionDropdown(): void
    {
        $this->searchLabSections();
    }

    public function searchLabSections(): void
    {
        $this->showLabSectionDropdown = true;

        $query = \App\SampleAnalysisStage::query()
            ->where('active', 1)
            ->where(function ($q): void {
                $q->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
            })
            ->orderBy('name');

        $analysisTypeLabId = $this->analysisType?->lab_id;
        if (! empty($analysisTypeLabId)) {
            $query->where(function ($q) use ($analysisTypeLabId): void {
                $q->where('lab_id', $analysisTypeLabId)->orWhereNull('lab_id');
            });
        }

        $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->labSectionSearch);
        $this->filteredLabSections = $query->limit($this->searchResultLimit)->get();
    }

    public function selectLabSection(string $labSectionId): void
    {
        $section = \App\SampleAnalysisStage::query()->find($labSectionId);
        if (! $section) {
            return;
        }

        $this->elementForm['lab_section_id'] = (string) $section->id;
        $this->syncSelectedLabSectionName((string) $section->id);
        $this->labSectionSearch = '';
        $this->showLabSectionDropdown = false;
    }

    public function clearLabSection(): void
    {
        $this->elementForm['lab_section_id'] = null;
        $this->selectedLabSectionName = '';
        $this->labSectionSearch = '';
        $this->showLabSectionDropdown = false;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    #[On('worksheets-synced')]
    public function handleWorksheetsSynced(string $message = '', string $messageType = 'success'): void
    {
        if ($message === '') {
            return;
        }

        $this->message = $message;
        $this->messageType = $messageType;
    }

    public function closeElementModal()
    {
        $this->showElementModal = false;
        $this->resetElementForm();
        $this->dispatch('element-modal-closed');
    }

    #[On('reorderElements')]
    public function reorderElements($elementIds)
    {
        return $this->updateElementOrder($elementIds);
    }

    public function updateElementOrder($elementIds)
    {
        try {
            Log::info('updateElementOrder called', [
                'elementIds' => $elementIds,
                'analysisTypeId' => $this->analysisTypeId
            ]);
            
            DB::transaction(function () use ($elementIds) {
                // Get ALL elements for this analysis type ordered by current level
                $allElements = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                    ->orderBy('level', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();
                
                // Calculate the starting position (offset) of the first dragged element in the full list
                $firstDraggedId = $elementIds[0];
                $startPosition = array_search($firstDraggedId, $allElements);
                
                // Remove all dragged elements from the full list
                $remainingElements = array_diff($allElements, $elementIds);
                
                // Insert the reordered elements at the correct position
                $newOrder = array_merge(
                    array_slice(array_values($remainingElements), 0, $startPosition),
                    $elementIds,
                    array_slice(array_values($remainingElements), $startPosition)
                );
                
                // Update all elements with their new levels
                foreach ($newOrder as $index => $elementId) {
                    AnalysisElements::where('id', $elementId)
                        ->where('analysis_type_id', $this->analysisTypeId)
                        ->update(['level' => $index + 1]);
                }
                
                Log::info('Elements reordered successfully', [
                    'totalElements' => count($newOrder),
                    'newOrder' => $newOrder
                ]);
            });
            
            $this->message = 'Elements reordered successfully!';
            $this->messageType = 'success';
            
            // Refresh the elements list to show the new order
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            Log::error('Failed to reorder elements', [
                'error' => $e->getMessage(),
                'elementIds' => $elementIds
            ]);
            
            $this->message = 'Failed to reorder elements: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function loadElements()
    {
        // This method is called to refresh the elements list
        // The actual data loading is handled by the getElementsProperty computed property
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.analysis.element-manager');
    }
}