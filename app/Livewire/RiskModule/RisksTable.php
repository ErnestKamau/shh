<?php

namespace App\Livewire\RiskModule;

use App\Models\RiskManagement\Risk;
use Livewire\Component;
use Livewire\WithPagination;

class RisksTable extends Component
{
    use WithPagination;

    public $status = 'All Risks';
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    
    public $filters = [
        'category_id' => '',
        'source_id' => '',
        'risk_level' => '',
        'date_from' => '',
        'date_to' => '',
    ];

    public $showDeleteModal = false;
    public $riskToDelete = null;
    public $showStatusModal = false;
    public $riskToChangeStatus = null;
    public $newStatus = '';
    public $statusNotes = '';
    public $availableWorkflowSteps = [];
    public $currentWorkflowStep = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'All Risks'],
        'perPage' => ['except' => 15],
    ];

    public function mount($status = 'All Risks')
    {
        $this->status = $status;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->filters = [
            'category_id' => '',
            'source_id' => '',
            'risk_level' => '',
            'date_from' => '',
            'date_to' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->riskToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRisk()
    {
        if ($this->riskToDelete) {
            $risk = Risk::find($this->riskToDelete);
            if ($risk) {
                $risk->delete();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Risk deleted successfully.']);
            }
        }
        $this->showDeleteModal = false;
        $this->riskToDelete = null;
    }

    public function openStatusModal($id)
    {
        $this->riskToChangeStatus = $id;
        $risk = Risk::find($id);
        
        if ($risk->status_name === 'Closed') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot change status. Risk is closed and finalized.']);
            return;
        }
        
        $rawStep = (int) ($risk->workflow_step ?? $risk->getCurrentWorkflowStep() ?? 2);
        $this->currentWorkflowStep = \mapRiskRecordWorkflowStepToStatusStep($rawStep) ?? 1;
        $workflowSteps = \getRiskWorkflowSteps();
        
        $this->availableWorkflowSteps = [];
        
        // Add all previous steps (can go back) and next step
        for ($i = 1; $i <= $this->currentWorkflowStep; $i++) {
            $this->availableWorkflowSteps[$i] = $workflowSteps[$i] ?? "Step $i";
        }
        
        if ($this->currentWorkflowStep < 7) {
            $nextStep = $this->currentWorkflowStep + 1;
            $this->availableWorkflowSteps[$nextStep] = $workflowSteps[$nextStep] ?? "Step $nextStep";
        }

        $this->newStatus = $workflowSteps[$this->currentWorkflowStep] ?? 'Identified';
        $this->statusNotes = '';
        $this->showStatusModal = true;
    }

    public function changeStatus()
    {
        if ($this->riskToChangeStatus && $this->newStatus) {
            $risk = Risk::find($this->riskToChangeStatus);
            if ($risk) {
                if ($risk->status_name === 'Closed') {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot change status. Risk is closed and finalized.']);
                    return;
                }
                
                $oldStatus = $risk->status_name;
                $workflowSteps = \getRiskWorkflowSteps();
                
                $targetStepNum = array_search($this->newStatus, $workflowSteps);

                if ($targetStepNum === false || $targetStepNum === 0) {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Invalid workflow step selected.']);
                    return;
                }

                $targetRiskStep = \mapRiskStatusWorkflowStepToRiskRecordStep((int) $targetStepNum);

                // Validate workflow progression
                if ($targetStepNum > $this->currentWorkflowStep) {
                    if (! $risk->canProceedToStep($targetRiskStep)) {
                        $this->dispatch('notify', ['type' => 'error', 'message' => "Cannot proceed to '{$this->newStatus}'. Please complete the required steps in order."]);
                        return;
                    }
                }

                // Get the status record
                $targetStatus = \App\Models\RiskManagement\RiskStatus::forCompany()
                    ->where('name', $this->newStatus)
                    ->first();
                
                if ($targetStatus) {
                    $risk->status_id = $targetStatus->id;
                    $risk->status_name = $this->newStatus;
                } else {
                    $risk->status_name = $this->newStatus;
                }

                $risk->workflow_step = $targetRiskStep;
                
                if ($targetStepNum === 7) {
                    $risk->closure_date = now();
                    $risk->closed_by = auth()->id();
                }
                
                $risk->updated_by = auth()->id();
                $risk->save();
                
                $this->dispatch('notify', ['type' => 'success', 'message' => "Risk workflow step changed to {$this->newStatus}."]);
            }
        }
        $this->showStatusModal = false;
        $this->riskToChangeStatus = null;
        $this->newStatus = '';
        $this->statusNotes = '';
        $this->availableWorkflowSteps = [];
        $this->currentWorkflowStep = null;
    }

    public function render()
    {
        $query = Risk::forCompany()->with(['category', 'source', 'riskOwner', 'status', 'treatmentPlans', 'sample', 'method', 'equipment', 'latestReview', 'currentEvaluation', 'audit', 'nonConformance']);
        
        // Filter by status/workflow step - optimized to use database queries
        if ($this->status !== 'All Risks') {
            $workflowSteps = \getRiskWorkflowSteps();
            $stepNum = array_search($this->status, $workflowSteps, true);
            if ($stepNum !== false && $stepNum !== 0) {
                $riskStep = \mapRiskStatusWorkflowStepToRiskRecordStep((int) $stepNum);
                $query->where('workflow_step', $riskStep);
            } else {
                $query->where('status_name', $this->status);
            }
        }
        
        // Search - optimized with indexes
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('risk_number', 'like', $searchTerm)
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }
        
        // Filters
        if ($this->filters['category_id']) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if ($this->filters['source_id']) {
            $query->where('other_source_id', $this->filters['source_id']);
        }
        if ($this->filters['risk_level']) {
            $query->where('risk_level', $this->filters['risk_level']);
        }
        if ($this->filters['date_from']) {
            $query->whereDate('date_identified', '>=', $this->filters['date_from']);
        }
        if ($this->filters['date_to']) {
            $query->whereDate('date_identified', '<=', $this->filters['date_to']);
        }
        
        // Sorting with eager loading
        $risks = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        // Cache categories and sources to avoid repeated queries
        $categories = \App\Models\RiskManagement\RiskCategory::forCompany()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $sources = \App\Models\RiskManagement\RiskSource::forCompany()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('livewire.risk-module.risks-table', [
            'risks' => $risks,
            'categories' => $categories,
            'sources' => $sources,
        ]);
    }
}

