<?php

namespace App\Livewire\AuditModule\Reports;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditType;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditModule\AuditsExport;

class AuditSummaryReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $auditTypeFilter = '';
    public $statusFilter = '';
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

    public function updatingAuditTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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
        $this->auditTypeFilter = '';
        $this->statusFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function exportExcel()
    {
        $audits = $this->getAuditsQuery()->get();
        return Excel::download(new AuditsExport($audits), 'Audit_Summary_' . date('Y-m-d') . '.xlsx');
    }

    private function getAuditsQuery()
    {
        $query = Audit::forCompany()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->with(['auditType', 'leadAuditor', 'findings', 'nonConformances']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('audit_number', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('scope', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->auditTypeFilter) {
            $query->where('audit_type_id', $this->auditTypeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status_name', $this->statusFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $audits = $this->getAuditsQuery()->paginate($this->perPage);
        $auditTypes = AuditType::active()->orderBy('name')->get();
        $statuses = getAuditWorkflowStatuses();

        return view('livewire.audit-module.reports.audit-summary-report', [
            'audits' => $audits,
            'auditTypes' => $auditTypes,
            'statuses' => $statuses,
        ]);
    }
}
