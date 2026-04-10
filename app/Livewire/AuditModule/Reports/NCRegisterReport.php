<?php

namespace App\Livewire\AuditModule\Reports;

use App\Models\AuditModule\NonConformance;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditModule\NonConformancesExport;

class NCRegisterReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $statusFilter = '';
    public $originFilter = '';
    public $riskLevelFilter = '';
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

    public function updatingOriginFilter()
    {
        $this->resetPage();
    }

    public function updatingRiskLevelFilter()
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
        $this->originFilter = '';
        $this->riskLevelFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function exportExcel()
    {
        $ncs = $this->getNCsQuery()->get();
        return Excel::download(new NonConformancesExport($ncs), 'NC_Register_' . date('Y-m-d') . '.xlsx');
    }

    private function getNCsQuery()
    {
        $query = NonConformance::forCompany()
            ->whereBetween('date_identified', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->with(['riskLevel', 'identifiedByUser', 'correctiveActions', 'audit']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nc_number', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status_name', $this->statusFilter);
        }

        if ($this->originFilter) {
            $query->where('origin_name', $this->originFilter);
        }

        if ($this->riskLevelFilter) {
            $query->where('risk_level_id', $this->riskLevelFilter);
        }

        return $query->orderBy('date_identified', 'desc');
    }

    public function render()
    {
        $ncs = $this->getNCsQuery()->paginate($this->perPage);
        
        // Get unique statuses, origins, and risk levels for filters
        $statuses = NonConformance::forCompany()
            ->whereNotNull('status_name')
            ->distinct()
            ->pluck('status_name');
        
        $origins = NonConformance::forCompany()
            ->whereNotNull('origin_name')
            ->distinct()
            ->pluck('origin_name');
        
        $riskLevels = getActiveRiskLevels();

        return view('livewire.audit-module.reports.n-c-register-report', [
            'ncs' => $ncs,
            'statuses' => $statuses,
            'origins' => $origins,
            'riskLevels' => $riskLevels,
        ]);
    }
}
