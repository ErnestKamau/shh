<?php

namespace App\Livewire\Lab;

use App\Lab;
use App\LabSectionApprover;
use App\LabSectionApproverRelationShip;
use App\Models\LabSectionReportConfig;
use App\ReportFormat;
use App\SampleAnalysisStage;
use App\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LabSectionManager extends Component
{
    // Active tab
    public $activeTab = 'lab-sections';

    // Lab Section Form
    public $labSectionForm = [
        'name' => '',
        'code' => '',
        'section_head_id' => null,
        'lab_id' => null,
        'active' => true,
    ];

    // Sample Stage Form
    public $sampleStageForm = [
        'name' => '',
        'code' => '',
        'sample_workflow' => '',
        'level' => 1,
        'is_system' => false,
        'active' => true,
    ];

    // Verifier Form
    public $verifierForm = [
        'user_id' => null,
        'title' => '',
        'section_ids' => [],
    ];

    // Search and Filters
    public $labSectionSearch = '';
    public $labSectionStatusFilter = '';

    public $sampleStageSearch = '';
    public $sampleStageStatusFilter = '';

    public $verifierSearch = '';

    // Modal States
    public $showLabSectionModal = false;
    public $showSampleStageModal = false;
    public $showVerifierModal = false;
    public $showDeleteModal = false;

    // Delete Confirmation
    public $deleteType = ''; // 'lab-section', 'sample-stage', 'verifier'
    public $deleteId = null;
    public $deleteDetails = [];

    // Editing States
    public $editingLabSection = null;
    public $editingSampleStage = null;
    public $editingVerifier = null;

    // Supporting Data
    public $users = [];
    public $labs = [];
    public $workflows = [];
    public $reportFormats = [];

    // Searchable Dropdown States - Lab Sections
    public $sectionHeadSearch = '';
    public $showSectionHeadDropdown = false;
    public $labSearch = '';
    public $showLabDropdown = false;

    // Searchable Dropdown States - Sample Stages
    // (none needed, uses standard select)

    // Searchable Dropdown States - Verifiers
    public $verifierUserSearch = '';
    public $showVerifierUserDropdown = false;
    public $verifierSectionsSearch = '';
    public $showVerifierSectionsDropdown = false;

    // Report Configurations
    public $reportConfigForm = [
        'sample_analysis_stage_id' => null,
        'report_format_id' => null,
        'document_code' => '',
        'issue_date' => '',
        'revision_number' => '',
        'is_default' => false,
    ];
    public $selectedLabSectionForConfig = null;   // Lab section currently being configured
    public $showReportConfigModal = false;
    public $editingReportConfig = null;

    // Inline Report Format Creation
    public $isCreatingNewReportFormat = false;
    public $newReportFormatName = '';
    public $newReportFormatCode = '';

    // UI State
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->loadSupportingData();
    }

    public function loadSupportingData()
    {
        $this->users = User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $this->labs = Lab::where('active', 1)->orderBy('name')->get();
        $this->workflows = getSampleWorflowStages();
        $this->reportFormats = ReportFormat::where('is_active', true)->orderBy('report_name')->get();
    }

    // ========================================
    // LAB SECTIONS METHODS
    // ========================================

    public function getLabSectionsProperty()
    {
        $query = SampleAnalysisStage::where('is_sample_stage', 0);

        if ($this->labSectionSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->labSectionSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->labSectionSearch . '%');
            });
        }

        if ($this->labSectionStatusFilter !== '') {
            $query->where('active', $this->labSectionStatusFilter);
        }

        return $query->orderBy('name')->get();
    }

    public function showCreateLabSectionModal()
    {
        $this->resetLabSectionForm();
        $this->showLabSectionModal = true;
    }

    public function showEditLabSectionModal($id)
    {
        $labSection = SampleAnalysisStage::findOrFail($id);
        $this->labSectionForm = [
            'name' => $labSection->name,
            'code' => $labSection->code,
            'section_head_id' => $labSection->section_head_id,
            'lab_id' => $labSection->lab_id,
            'active' => (bool) $labSection->active,
        ];
        $this->editingLabSection = $id;
        $this->showLabSectionModal = true;
    }

    public function saveLabSection()
    {
        $this->validate([
            'labSectionForm.name' => 'required|string|max:255',
            'labSectionForm.code' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingLabSection) {
                $labSection = SampleAnalysisStage::findOrFail($this->editingLabSection);
                $labSection->update([
                    'name' => $this->labSectionForm['name'],
                    'code' => $this->labSectionForm['code'],
                    'section_head_id' => $this->labSectionForm['section_head_id'],
                    'lab_id' => $this->labSectionForm['lab_id'],
                    'active' => $this->labSectionForm['active'],
                ]);
                $this->message = 'Lab section updated successfully!';
            } else {
                SampleAnalysisStage::create([
                    'name' => $this->labSectionForm['name'],
                    'code' => $this->labSectionForm['code'],
                    'section_head_id' => $this->labSectionForm['section_head_id'],
                    'lab_id' => $this->labSectionForm['lab_id'],
                    'active' => $this->labSectionForm['active'],
                    'is_sample_stage' => 0,
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Lab section created successfully!';
            }

            DB::commit();
            $this->closeLabSectionModal();
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteLabSection($id): void
    {
        $labSection = SampleAnalysisStage::findOrFail($id);

        $this->deleteType = 'lab-section';
        $this->deleteId = $id;
        $this->deleteDetails = [
            'name' => $labSection->name,
            'code' => $labSection->code,
            'section_head' => $labSection->getSectionHead()->name ?? '-',
            'lab' => $labSection->getLabDetails()->name ?? '-',
            'active' => $labSection->active ? 'Active' : 'Inactive',
        ];
        $this->showDeleteModal = true;
    }

    public function deleteLabSection(): void
    {
        try {
            SampleAnalysisStage::findOrFail($this->deleteId)->delete();
            $this->message = 'Lab section deleted successfully!';
            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = 'Error deleting lab section: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeLabSectionModal()
    {
        $this->showLabSectionModal = false;
        $this->resetLabSectionForm();
    }

    public function resetLabSectionForm()
    {
        $this->labSectionForm = [
            'name' => '',
            'code' => '',
            'section_head_id' => null,
            'lab_id' => null,
            'active' => true,
        ];
        $this->editingLabSection = null;
        $this->sectionHeadSearch = '';
        $this->showSectionHeadDropdown = false;
        $this->labSearch = '';
        $this->showLabDropdown = false;
    }

    // ========================================
    // SAMPLE STAGES METHODS
    // ========================================

    public function getSampleStagesProperty()
    {
        $query = SampleAnalysisStage::where('is_sample_stage', 1);

        if ($this->sampleStageSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->sampleStageSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->sampleStageSearch . '%');
            });
        }

        if ($this->sampleStageStatusFilter !== '') {
            $query->where('active', $this->sampleStageStatusFilter);
        }

        return $query->orderBy('level')->orderBy('name')->get();
    }

    public function showCreateSampleStageModal()
    {
        $this->resetSampleStageForm();
        $this->showSampleStageModal = true;
    }

    public function showEditSampleStageModal($id)
    {
        $sampleStage = SampleAnalysisStage::findOrFail($id);
        $this->sampleStageForm = [
            'name' => $sampleStage->name,
            'code' => $sampleStage->code,
            'sample_workflow' => $sampleStage->sample_workflow,
            'level' => $sampleStage->level,
            'is_system' => (bool) $sampleStage->is_system,
            'active' => (bool) $sampleStage->active,
        ];
        $this->editingSampleStage = $id;
        $this->showSampleStageModal = true;
    }

    public function saveSampleStage()
    {
        $this->validate([
            'sampleStageForm.name' => 'required|string|max:255',
            'sampleStageForm.code' => 'required|string|max:255',
            'sampleStageForm.sample_workflow' => 'required|string',
            'sampleStageForm.level' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingSampleStage) {
                $sampleStage = SampleAnalysisStage::findOrFail($this->editingSampleStage);
                $sampleStage->update([
                    'name' => $this->sampleStageForm['name'],
                    'code' => $this->sampleStageForm['code'],
                    'sample_workflow' => $this->sampleStageForm['sample_workflow'],
                    'level' => $this->sampleStageForm['level'],
                    'is_system' => $this->sampleStageForm['is_system'],
                    'active' => $this->sampleStageForm['active'],
                ]);
                $this->message = 'Sample stage updated successfully!';
            } else {
                SampleAnalysisStage::create([
                    'name' => $this->sampleStageForm['name'],
                    'code' => $this->sampleStageForm['code'],
                    'sample_workflow' => $this->sampleStageForm['sample_workflow'],
                    'level' => $this->sampleStageForm['level'],
                    'is_system' => $this->sampleStageForm['is_system'],
                    'active' => $this->sampleStageForm['active'],
                    'is_sample_stage' => 1,
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Sample stage created successfully!';
            }

            DB::commit();
            $this->closeSampleStageModal();
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteSampleStage($id): void
    {
        $sampleStage = SampleAnalysisStage::findOrFail($id);

        $this->deleteType = 'sample-stage';
        $this->deleteId = $id;
        $this->deleteDetails = [
            'name' => $sampleStage->name,
            'code' => $sampleStage->code,
            'workflow' => $sampleStage->sample_workflow,
            'level' => $sampleStage->level,
            'active' => $sampleStage->active ? 'Active' : 'Inactive',
            'is_system' => $sampleStage->is_system ? 'Yes' : 'No',
        ];
        $this->showDeleteModal = true;
    }

    public function deleteSampleStage(): void
    {
        try {
            SampleAnalysisStage::findOrFail($this->deleteId)->delete();
            $this->message = 'Sample stage deleted successfully!';
            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = 'Error deleting sample stage: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeSampleStageModal()
    {
        $this->showSampleStageModal = false;
        $this->resetSampleStageForm();
    }

    public function resetSampleStageForm()
    {
        $this->sampleStageForm = [
            'name' => '',
            'code' => '',
            'sample_workflow' => '',
            'level' => 1,
            'is_system' => false,
            'active' => true,
        ];
        $this->editingSampleStage = null;
    }

    // ========================================
    // VERIFIER CONFIGURATION METHODS
    // ========================================

    public function getVerifiersProperty()
    {
        $query = LabSectionApprover::query();

        if ($this->verifierSearch) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->verifierSearch . '%');
            });
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    public function showCreateVerifierModal()
    {
        $this->resetVerifierForm();
        $this->showVerifierModal = true;
    }

    public function showEditVerifierModal($id)
    {
        $verifier = LabSectionApprover::findOrFail($id);
        $this->verifierForm = [
            'user_id' => $verifier->user_id,
            'title' => $verifier->title,
            'section_ids' => explode(',', $verifier->lab_section_ids),
        ];
        $this->editingVerifier = $id;
        $this->showVerifierModal = true;
    }

    public function saveVerifier()
    {
        $this->validate([
            'verifierForm.user_id' => 'required|exists:users,id',
            'verifierForm.title' => 'required|string|max:255',
            'verifierForm.section_ids' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $approver = $this->editingVerifier
                ? LabSectionApprover::findOrFail($this->editingVerifier)
                : new LabSectionApprover();

            $approver->user_id = $this->verifierForm['user_id'];
            $approver->lab_section_ids = implode(',', $this->verifierForm['section_ids']);
            $approver->title = $this->verifierForm['title'];
            $approver->save();

            // Update relationships
            LabSectionApproverRelationShip::whereIn('lab_section_id', $this->verifierForm['section_ids'])->delete();
            LabSectionApproverRelationShip::where('parent_id', $approver->id)->delete();

            $data = [];
            foreach ($this->verifierForm['section_ids'] as $sectionId) {
                $data[] = [
                    'lab_section_id' => $sectionId,
                    'user_id' => $this->verifierForm['user_id'],
                    'title' => $this->verifierForm['title'],
                    'parent_id' => $approver->id,
                ];
            }
            LabSectionApproverRelationShip::insert($data);

            DB::commit();
            $this->message = $this->editingVerifier
                ? 'Verifier configuration updated successfully!'
                : 'Verifier configuration created successfully!';
            $this->closeVerifierModal();
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteVerifier($id): void
    {
        $verifier = LabSectionApprover::findOrFail($id);

        $this->deleteType = 'verifier';
        $this->deleteId = $id;
        $this->deleteDetails = [
            'name' => $verifier->approvername,
            'title' => $verifier->title,
            'sections' => $verifier->sectionname,
        ];
        $this->showDeleteModal = true;
    }

    public function deleteVerifier(): void
    {
        try {
            DB::beginTransaction();

            LabSectionApprover::findOrFail($this->deleteId)->delete();
            LabSectionApproverRelationShip::where('parent_id', $this->deleteId)->delete();

            DB::commit();
            $this->message = 'Verifier configuration deleted successfully!';
            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error deleting verifier: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeVerifierModal()
    {
        $this->showVerifierModal = false;
        $this->resetVerifierForm();
    }

    public function resetVerifierForm()
    {
        $this->verifierForm = [
            'user_id' => null,
            'title' => '',
            'section_ids' => [],
        ];
        $this->editingVerifier = null;
        $this->verifierUserSearch = '';
        $this->showVerifierUserDropdown = false;
        $this->verifierSectionsSearch = '';
        $this->showVerifierSectionsDropdown = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteType = '';
        $this->deleteId = null;
        $this->deleteDetails = [];
    }

    // ========================================
    // SEARCHABLE DROPDOWN METHODS
    // ========================================

    // Section Head Dropdown
    public function selectSectionHead($userId)
    {
        $this->labSectionForm['section_head_id'] = $userId;
        $this->sectionHeadSearch = '';
        $this->showSectionHeadDropdown = false;
    }

    public function updatedSectionHeadSearch()
    {
        $this->showSectionHeadDropdown = !empty($this->sectionHeadSearch);
    }

    public function getFilteredSectionHeadsProperty()
    {
        if (empty($this->sectionHeadSearch)) {
            return [];
        }

        return User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->where('name', 'like', '%' . $this->sectionHeadSearch . '%')
            ->limit(10)
            ->get();
    }

    public function getSelectedSectionHeadProperty()
    {
        if (empty($this->labSectionForm['section_head_id'])) {
            return null;
        }

        return User::find($this->labSectionForm['section_head_id']);
    }

    // Lab Dropdown
    public function selectLab($labId)
    {
        $this->labSectionForm['lab_id'] = $labId;
        $this->labSearch = '';
        $this->showLabDropdown = false;
    }

    public function updatedLabSearch()
    {
        $this->showLabDropdown = !empty($this->labSearch);
    }

    public function getFilteredLabsProperty()
    {
        if (empty($this->labSearch)) {
            return [];
        }

        return Lab::where('active', 1)
            ->where('name', 'like', '%' . $this->labSearch . '%')
            ->limit(10)
            ->get();
    }

    public function getSelectedLabProperty()
    {
        if (empty($this->labSectionForm['lab_id'])) {
            return null;
        }

        return Lab::find($this->labSectionForm['lab_id']);
    }

    // Verifier User Dropdown
    public function selectVerifierUser($userId)
    {
        $this->verifierForm['user_id'] = $userId;
        $this->verifierUserSearch = '';
        $this->showVerifierUserDropdown = false;
    }

    public function updatedVerifierUserSearch()
    {
        $this->showVerifierUserDropdown = !empty($this->verifierUserSearch);
    }

    public function getFilteredVerifierUsersProperty()
    {
        if (empty($this->verifierUserSearch)) {
            return [];
        }

        return User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->where('name', 'like', '%' . $this->verifierUserSearch . '%')
            ->limit(10)
            ->get();
    }

    public function getSelectedVerifierUserProperty()
    {
        if (empty($this->verifierForm['user_id'])) {
            return null;
        }

        return User::find($this->verifierForm['user_id']);
    }

    // Verifier Sections Multi-select
    public function toggleVerifierSection($sectionId)
    {
        if (in_array($sectionId, $this->verifierForm['section_ids'])) {
            $this->verifierForm['section_ids'] = array_values(
                array_filter($this->verifierForm['section_ids'], fn($id) => $id != $sectionId)
            );
        } else {
            $this->verifierForm['section_ids'][] = $sectionId;
        }
    }

    public function updatedVerifierSectionsSearch()
    {
        $this->showVerifierSectionsDropdown = !empty($this->verifierSectionsSearch);
    }

    public function getFilteredVerifierSectionsProperty()
    {
        if (empty($this->verifierSectionsSearch)) {
            return SampleAnalysisStage::where('is_sample_stage', 0)
                ->where('active', 1)
                ->limit(10)
                ->get();
        }

        return SampleAnalysisStage::where('is_sample_stage', 0)
            ->where('active', 1)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->verifierSectionsSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->verifierSectionsSearch . '%');
            })
            ->limit(10)
            ->get();
    }

    public function getSelectedVerifierSectionsProperty()
    {
        if (empty($this->verifierForm['section_ids'])) {
            return collect([]);
        }

        return SampleAnalysisStage::whereIn('id', $this->verifierForm['section_ids'])->get();
    }

    // ========================================
    // REPORT CONFIGURATION METHODS
    // ========================================

    public function getReportConfigsProperty()
    {
        return LabSectionReportConfig::with(['reportFormat', 'labSection'])
            ->orderBy('sample_analysis_stage_id')
            ->get();
    }

    public function showCreateReportConfigModal()
    {
        $this->resetReportConfigForm();
        $this->showReportConfigModal = true;
    }

    public function showEditReportConfigModal($id)
    {
        $config = LabSectionReportConfig::findOrFail($id);
        $this->reportConfigForm = [
            'sample_analysis_stage_id' => $config->sample_analysis_stage_id,
            'report_format_id'         => $config->report_format_id,
            'document_code'            => $config->document_code ?? '',
            'issue_date'               => $config->issue_date ? $config->issue_date->format('Y-m-d') : '',
            'revision_number'          => $config->revision_number ?? '',
            'is_default'               => (bool) $config->is_default,
        ];
        $this->editingReportConfig = $id;
        $this->showReportConfigModal = true;
    }

    public function saveReportConfig()
    {
        // Require format ID unless we are creating a new one
        $formatValidation = $this->isCreatingNewReportFormat ? 'nullable' : 'required|exists:report_formats,id';

        $this->validate([
            'reportConfigForm.sample_analysis_stage_id' => 'required|exists:sample_analysis_stages,id',
            'reportConfigForm.report_format_id'         => $formatValidation,
            'reportConfigForm.document_code'            => 'nullable|string|max:100',
            'reportConfigForm.issue_date'               => 'nullable|date',
            'reportConfigForm.revision_number'          => 'nullable|string|max:50',
            // Validation for new format if creating inline
            'newReportFormatName'                       => $this->isCreatingNewReportFormat ? 'required|string|max:255' : 'nullable',
            'newReportFormatCode'                       => $this->isCreatingNewReportFormat ? 'required|string|max:50|unique:report_formats,report_code' : 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $formatId = $this->reportConfigForm['report_format_id'];

            // Handle inline creation of Report Format
            if ($this->isCreatingNewReportFormat) {
                $newFormat = ReportFormat::create([
                    'report_name' => $this->newReportFormatName,
                    'report_code' => $this->newReportFormatCode,
                    'is_active' => true,
                ]);
                $formatId = $newFormat->id;
                // Reload supporting data so the new format appears in other dropdowns
                $this->reportFormats = ReportFormat::where('is_active', true)->orderBy('report_name')->get();
            }

            $data = [
                'sample_analysis_stage_id' => $this->reportConfigForm['sample_analysis_stage_id'],
                'report_format_id'         => $formatId,
                'document_code'            => $this->reportConfigForm['document_code'] ?: null,
                'issue_date'               => $this->reportConfigForm['issue_date'] ?: null,
                'revision_number'          => $this->reportConfigForm['revision_number'] ?: null,
                'is_default'               => $this->reportConfigForm['is_default'],
            ];

            if ($this->reportConfigForm['is_default']) {
                // Unset any existing default for this lab section
                LabSectionReportConfig::where('sample_analysis_stage_id', $data['sample_analysis_stage_id'])
                    ->where('id', '!=', $this->editingReportConfig ?? 0)
                    ->update(['is_default' => false]);
            }

            if ($this->editingReportConfig) {
                LabSectionReportConfig::findOrFail($this->editingReportConfig)->update($data);
                $this->message = 'Report configuration updated successfully!';
            } else {
                LabSectionReportConfig::create($data);
                $this->message = 'Report configuration added successfully!';
            }

            DB::commit();
            $this->closeReportConfigModal();
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteReportConfig($id): void
    {
        try {
            LabSectionReportConfig::findOrFail($id)->delete();
            $this->message = 'Report configuration removed successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeReportConfigModal()
    {
        $this->showReportConfigModal = false;
        $this->resetReportConfigForm();
    }

    public function resetReportConfigForm()
    {
        $this->reportConfigForm = [
            'sample_analysis_stage_id' => null,
            'report_format_id'         => null,
            'document_code'            => '',
            'issue_date'               => '',
            'revision_number'          => '',
            'is_default'               => false,
        ];
        $this->editingReportConfig = null;
        $this->isCreatingNewReportFormat = false;
        $this->newReportFormatName = '';
        $this->newReportFormatCode = '';
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.lab.lab-section-manager');
    }
}
