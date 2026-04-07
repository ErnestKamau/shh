<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Auth;

class DocumentTypesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    public $showAdvancedFilters = false;
    public $filters = [
        'created_by' => '',
        'created_date_from' => '',
        'created_date_to' => '',
        'updated_date_from' => '',
        'updated_date_to' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = ['refreshTable' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatingFiltersCreatedBy()
    {
        $this->resetPage();
    }

    public function updatingFiltersCreatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFiltersCreatedDateTo()
    {
        $this->resetPage();
    }

    public function updatingFiltersUpdatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFiltersUpdatedDateTo()
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
        $this->reset(['search', 'statusFilter', 'filters']);
        $this->resetPage();
    }

    public function deleteDocumentType($id)
    {
        $documentType = DocumentType::withCount('documents')->findOrFail($id);
        
        if ($documentType->documents_count > 0) {
            session()->flash('error', 'Cannot delete document type that is being used by documents.');
            return;
        }

        $documentType->delete();
        session()->flash('success', 'Document type deleted successfully.');
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        $query = DocumentType::withCount('documents');

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply advanced filters
        if (!empty($this->filters['created_by'])) {
            $query->where('created_by', $this->filters['created_by']);
        }

        if (!empty($this->filters['created_date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['created_date_from']);
        }

        if (!empty($this->filters['created_date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['created_date_to']);
        }

        if (!empty($this->filters['updated_date_from'])) {
            $query->whereDate('updated_at', '>=', $this->filters['updated_date_from']);
        }

        if (!empty($this->filters['updated_date_to'])) {
            $query->whereDate('updated_at', '<=', $this->filters['updated_date_to']);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $documentTypes = $query->paginate($this->perPage);

        // Get filter options
        $statusOptions = [
            '' => 'All Status',
            'active' => 'Active',
            'inactive' => 'Inactive'
        ];

        // Get available users for filter
        $availableUsers = \App\User::orderBy('name')->get();

        return view('livewire.documents.document-types-table', [
            'documentTypes' => $documentTypes,
            'statusOptions' => $statusOptions,
            'availableUsers' => $availableUsers,
        ]);
    }
}
