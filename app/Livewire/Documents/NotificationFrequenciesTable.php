<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\NotificationFrequency;
use Illuminate\Support\Facades\Auth;

class NotificationFrequenciesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $intervalFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $showAdvancedFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'intervalFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'showAdvancedFilters' => ['except' => false],
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

    public function updatingIntervalFilter()
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
        $this->reset(['search', 'statusFilter', 'intervalFilter']);
        $this->resetPage();
    }

    public function deleteFrequency($id)
    {
        $frequency = NotificationFrequency::withCount('documents')->findOrFail($id);
        
        if ($frequency->documents_count > 0) {
            session()->flash('error', 'Cannot delete frequency that is being used by documents.');
            return;
        }

        $frequency->delete();
        session()->flash('success', 'Notification frequency deleted successfully.');
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        $query = NotificationFrequency::withCount('documents');

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

        // Apply interval filter
        if (!empty($this->intervalFilter)) {
            switch ($this->intervalFilter) {
                case 'daily':
                    $query->where('days_interval', 1);
                    break;
                case 'weekly':
                    $query->where('days_interval', 7);
                    break;
                case 'bi-weekly':
                    $query->where('days_interval', 14);
                    break;
                case 'monthly':
                    $query->where('days_interval', 30);
                    break;
                case 'custom':
                    $query->whereNotIn('days_interval', [1, 7, 14, 30]);
                    break;
            }
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $notificationFrequencies = $query->paginate($this->perPage);

        // Get filter options
        $statusOptions = [
            '' => 'All Status',
            'active' => 'Active',
            'inactive' => 'Inactive'
        ];

        $intervalOptions = [
            '' => 'All Intervals',
            'daily' => 'Daily (1 day)',
            'weekly' => 'Weekly (7 days)',
            'bi-weekly' => 'Bi-weekly (14 days)',
            'monthly' => 'Monthly (30 days)',
            'custom' => 'Custom'
        ];

        return view('livewire.documents.notification-frequencies-table', [
            'notificationFrequencies' => $notificationFrequencies,
            'statusOptions' => $statusOptions,
            'intervalOptions' => $intervalOptions,
        ]);
    }
}
