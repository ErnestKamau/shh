<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\CorrectiveAction;
use Livewire\Component;
use Livewire\WithPagination;

class CorrectiveActionsTable extends Component
{
    use WithPagination;

    public $status = 'All CAPAs';
    public $search = '';
    public $perPage = 15;
    public $sortField = 'due_date';
    public $sortDirection = 'asc';
    public $showFilters = false;
    
    public $filters = [
        'priority_id' => '',
        'action_type_id' => '',
        'action_owner_id' => '',
        'date_from' => '',
        'date_to' => '',
    ];

    public $showDeleteModal = false;
    public $capaToDelete = null;
    public $showStatusModal = false;
    public $capaToChangeStatus = null;
    public $newStatus = '';
    public $statusNotes = '';
    public $implementationNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'All CAPAs'],
        'perPage' => ['except' => 15],
    ];

    public function mount($status = 'All CAPAs')
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
            'priority_id' => '',
            'action_type_id' => '',
            'action_owner_id' => '',
            'date_from' => '',
            'date_to' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->capaToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCAPA()
    {
        if ($this->capaToDelete) {
            $capa = CorrectiveAction::find($this->capaToDelete);
            if ($capa) {
                $capa->delete();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Corrective action deleted successfully.']);
            }
        }
        $this->showDeleteModal = false;
        $this->capaToDelete = null;
    }

    public function openStatusModal($id)
    {
        $this->capaToChangeStatus = $id;
        $capa = CorrectiveAction::find($id);
        $this->newStatus = $capa->status_name;
        $this->statusNotes = '';
        $this->implementationNotes = $capa->implementation_notes ?? '';
        $this->showStatusModal = true;
    }

    public function changeStatus()
    {
        if ($this->capaToChangeStatus && $this->newStatus) {
            $capa = CorrectiveAction::find($this->capaToChangeStatus);
            if ($capa) {
                $oldStatus = $capa->status_name;
                
                $capa->status_name = $this->newStatus;
                
                if (in_array($this->newStatus, ['Implemented', 'Verification Pending'])) {
                    $capa->implementation_date = now();
                    $capa->implementation_notes = $this->implementationNotes;
                }
                
                $capa->updated_by = auth()->id();
                $capa->save();
                
                \App\Models\AuditModule\AuditActivityLog::logStatusChange($capa, $oldStatus, $this->newStatus, $this->statusNotes);
                
                $this->dispatch('notify', ['type' => 'success', 'message' => "Corrective action status changed to {$this->newStatus}."]);
            }
        }
        $this->showStatusModal = false;
        $this->capaToChangeStatus = null;
        $this->newStatus = '';
        $this->statusNotes = '';
        $this->implementationNotes = '';
    }

    public function render()
    {
        $query = CorrectiveAction::forCompany()
            ->with(['nonConformance', 'category', 'actionOwnerUser', 'actionType', 'status', 'latestVerification', 'priority']);

        // Status filter
        if ($this->status === 'Overdue') {
            $query->overdue();
        } elseif ($this->status !== 'All CAPAs') {
            $query->where('status_name', $this->status);
        }

        // Search
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('capa_number', 'like', $searchTerm)
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Advanced filters
        if ($this->filters['priority_id']) {
            $query->where('priority_id', $this->filters['priority_id']);
        }
        if ($this->filters['action_type_id']) {
            $query->where('action_type_id', $this->filters['action_type_id']);
        }
        if ($this->filters['action_owner_id']) {
            $query->where('action_owner_id', $this->filters['action_owner_id']);
        }
        if ($this->filters['date_from']) {
            $query->whereDate('due_date', '>=', $this->filters['date_from']);
        }
        if ($this->filters['date_to']) {
            $query->whereDate('due_date', '<=', $this->filters['date_to']);
        }

        $capas = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        
        $users = getAuditorUsers();
        $priorities = getActiveCapaPriorities();
        $actionTypes = getActiveCapaActionTypes();

        return view('livewire.audit-module.corrective-actions-table', [
            'capas' => $capas,
            'users' => $users,
            'priorities' => $priorities,
            'actionTypes' => $actionTypes,
        ]);
    }
}
