<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\NonConformance;
use Livewire\Component;
use Livewire\WithPagination;

class NonConformancesTable extends Component
{
    use WithPagination;

    public $status = 'All NCs';
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    
    public $filters = [
        'origin_id' => '',
        'risk_level_id' => '',
        'date_from' => '',
        'date_to' => '',
    ];

    public $showDeleteModal = false;
    public $ncToDelete = null;
    public $showStatusModal = false;
    public $ncToChangeStatus = null;
    public $newStatus = '';
    public $statusNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'All NCs'],
        'perPage' => ['except' => 15],
    ];

    public function mount($status = 'All NCs')
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
            'origin_id' => '',
            'risk_level_id' => '',
            'date_from' => '',
            'date_to' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->ncToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteNC()
    {
        if ($this->ncToDelete) {
            $nc = NonConformance::find($this->ncToDelete);
            if ($nc) {
                $nc->delete();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Non-conformance deleted successfully.']);
            }
        }
        $this->showDeleteModal = false;
        $this->ncToDelete = null;
    }

    public function openStatusModal($id)
    {
        $this->ncToChangeStatus = $id;
        $nc = NonConformance::find($id);
        $this->newStatus = $nc->status_name;
        $this->statusNotes = '';
        $this->showStatusModal = true;
    }

    public function changeStatus()
    {
        if ($this->ncToChangeStatus && $this->newStatus) {
            $nc = NonConformance::find($this->ncToChangeStatus);
            if ($nc) {
                $oldStatus = $nc->status_name;
                
                if ($this->newStatus === 'Closed' && !$nc->canBeClosed()) {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot close NC. There are still open corrective actions.']);
                    return;
                }
                
                $nc->status_name = $this->newStatus;
                
                if ($this->newStatus === 'Closed') {
                    $nc->actual_closure_date = now();
                    $nc->closed_by = auth()->id();
                    $nc->closure_notes = $this->statusNotes;
                }
                
                $nc->save();
                
                \App\Models\AuditModule\AuditActivityLog::logStatusChange($nc, $oldStatus, $this->newStatus, $this->statusNotes);
                
                $this->dispatch('notify', ['type' => 'success', 'message' => "Non-conformance status changed to {$this->newStatus}."]);
            }
        }
        $this->showStatusModal = false;
        $this->ncToChangeStatus = null;
        $this->newStatus = '';
        $this->statusNotes = '';
    }

    public function render()
    {
        $query = NonConformance::forCompany()
            ->with(['riskLevel', 'identifiedByUser', 'correctiveActions', 'audit']);

        // Status filter
        if ($this->status !== 'All NCs') {
            $query->where('status_name', $this->status);
        }

        // Search
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nc_number', 'like', $searchTerm)
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('iso_clause_violated', 'like', $searchTerm)
                    ->orWhere('sop_reference', 'like', $searchTerm);
            });
        }

        // Advanced filters
        if ($this->filters['origin_id']) {
            $query->where('origin_id', $this->filters['origin_id']);
        }
        if ($this->filters['risk_level_id']) {
            $query->where('risk_level_id', $this->filters['risk_level_id']);
        }
        if ($this->filters['date_from']) {
            $query->whereDate('date_identified', '>=', $this->filters['date_from']);
        }
        if ($this->filters['date_to']) {
            $query->whereDate('date_identified', '<=', $this->filters['date_to']);
        }

        $ncs = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        
        $riskLevels = getActiveRiskLevels();
        $origins = getActiveNcOrigins();

        return view('livewire.audit-module.non-conformances-table', [
            'ncs' => $ncs,
            'riskLevels' => $riskLevels,
            'origins' => $origins,
        ]);
    }
}
