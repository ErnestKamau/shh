<?php

namespace App\Livewire\AuditModule\Reports;

use App\Models\AuditModule\CorrectiveAction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditModule\CorrectiveActionsExport;

class CAPAStatusReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $statusFilter = '';
    public $categoryFilter = '';
    public $priorityFilter = '';
    public $search = '';
    public $perPage = 25;
    public $showAdvancedFilters = false;

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'search' => ['except' => ''],
        'showAdvancedFilters' => ['except' => false],
    ];

    public function mount()
    {
        $this->startDate = now()->subMonths(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function setDateRange($range)
    {
        switch ($range) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
            case 'last_6_months':
                $this->startDate = now()->subMonths(6)->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->startDate = now()->subMonths(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->statusFilter = '';
        $this->categoryFilter = '';
        $this->priorityFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function exportExcel()
    {
        $capas = $this->getCAPAsQuery()->get();
        return Excel::download(new CorrectiveActionsExport($capas), 'CAPA_Status_' . date('Y-m-d') . '.xlsx');
    }

    private function getCAPAsQuery()
    {
        $query = CorrectiveAction::forCompany()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->with(['nonConformance', 'actionOwnerUser', 'category', 'priority', 'latestVerification']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('capa_number', 'like', '%' . $this->search . '%')
                  ->orWhere('action_description', 'like', '%' . $this->search . '%')
                  ->orWhere('root_cause', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'Overdue') {
                $query->overdue();
            } else {
                $query->where('status_name', $this->statusFilter);
            }
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority_name', $this->priorityFilter);
        }

        return $query->orderBy('due_date', 'asc');
    }

    public function render()
    {
        $capas = $this->getCAPAsQuery()->paginate($this->perPage);
        
        // Get unique statuses, categories, and priorities for filters
        $statuses = CorrectiveAction::forCompany()
            ->whereNotNull('status_name')
            ->distinct()
            ->pluck('status_name')
            ->merge(['Overdue']);
        
        $categories = getActiveCAPACategories();
        
        $priorities = CorrectiveAction::forCompany()
            ->whereNotNull('priority_name')
            ->distinct()
            ->pluck('priority_name');

        return view('livewire.audit-module.reports.c-a-p-a-status-report', [
            'capas' => $capas,
            'statuses' => $statuses,
            'categories' => $categories,
            'priorities' => $priorities,
        ]);
    }
}
