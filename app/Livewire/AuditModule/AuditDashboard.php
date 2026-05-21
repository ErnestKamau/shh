<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use Livewire\Component;
use Carbon\Carbon;

class AuditDashboard extends Component
{
    // Date filters
    public $startDate;
    public $endDate;
    
    public $stats = [];
    public $workflowTotals = [];
    public $ncHotspotsByDepartment = [];
    public $ncHotspotsByOrigin = [];
    public $ncHotspotsByRiskLevel = [];
    public $ncTrends = [];
    public $auditTrends = []; // Dynamic audit trends
    public $trendLabel = ''; // Dynamic trend label based on date range
    
    public $recentAudits = [];
    public $overdueCAPAs = [];
    public $recentNCs = [];
    public $upcomingAudits = [];

    protected $listeners = [
        'refreshAuditDashboard' => 'loadDashboardData',
    ];

    public function mount()
    {
        try {
            // Set default dates (current month)
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
            
            // Load all dashboard data
            $this->loadDashboardData();
        } catch (\Exception $e) {
            \Log::error('Audit Dashboard Error: ' . $e->getMessage());
            session()->flash('error', 'Error loading dashboard data: ' . $e->getMessage());
        }
    }

    public function updatedStartDate()
    {
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    public function updatedEndDate()
    {
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    public function setDateRange($range)
    {
        switch ($range) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
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
        }
        
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    public function loadDashboardData()
    {
        try {
            $startDateTime = Carbon::parse($this->startDate)->startOfDay();
            $endDateTime = Carbon::parse($this->endDate)->endOfDay();
            
            // Load dashboard statistics
            $this->loadDashboardStats($startDateTime, $endDateTime);
            
            // Load workflow totals
            $this->loadWorkflowTotals();
            
            // Load chart data
            $this->loadNCHotspots();
            $this->loadNCTrends($startDateTime, $endDateTime);
            $this->loadAuditTrends(); // Load audit trends
            
            // Load recent items
            $this->loadRecentItems();
        } catch (\Exception $e) {
            \Log::error('Audit Dashboard Data Loading Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function loadDashboardStats($startDateTime, $endDateTime)
    {
        // Audits stats
        $auditsQuery = Audit::forCompany()
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
        
        // NCs stats
        $ncsQuery = NonConformance::forCompany()
            ->whereBetween('date_identified', [$startDateTime, $endDateTime]);
        
        // CAPAs stats
        $capasQuery = CorrectiveAction::forCompany()
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
        
        $this->stats = [
            'audits' => [
                'total' => $auditsQuery->count(),
                'scheduled' => Audit::forCompany()
                    ->where('status_name', 'Scheduled')
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'in_progress' => Audit::forCompany()
                    ->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'rca_phase' => Audit::forCompany()
                    ->where('status_name', 'Root Cause Analysis')
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'pending_closure' => Audit::forCompany()
                    ->where('status_name', 'Pending Closure')
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
            ],
            'non_conformances' => [
                'identified' => NonConformance::forCompany()
                    ->where('status_name', 'Identified')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'rca_in_progress' => NonConformance::forCompany()
                    ->where('status_name', 'RCA In Progress')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'capa_assigned' => NonConformance::forCompany()
                    ->where('status_name', 'CAPA Assigned')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'verification_pending' => NonConformance::forCompany()
                    ->where('status_name', 'Verification Pending')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'overdue' => NonConformance::forCompany()
                    ->whereNotIn('status_name', ['Closed', 'Cancelled'])
                    ->whereNotNull('target_closure_date')
                    ->where('target_closure_date', '<', now())
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'total' => $ncsQuery->count(),
            ],
            'corrective_actions' => [
                'in_progress' => CorrectiveAction::forCompany()
                    ->where('status_name', 'In Progress')
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'verified' => CorrectiveAction::forCompany()
                    ->where('status_name', 'Verified')
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'overdue' => CorrectiveAction::forCompany()
                    ->overdue()
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->count(),
                'total' => $capasQuery->count(),
            ],
        ];
    }

    private function loadWorkflowTotals()
    {
        $workflowSteps = getAuditWorkflowSteps();
        $totals = [];

        foreach ($workflowSteps as $stepNum => $stepName) {
            $totals[$stepNum] = Audit::forCompany()
                ->where('status_name', $stepName)
                ->count();
        }

        $this->workflowTotals = $totals;
    }

    private function loadNCHotspots()
    {
        $this->ncHotspotsByDepartment = NonConformance::forCompany()
            ->whereNotNull('department')
            ->selectRaw('department as label, count(*) as count')
            ->groupBy('department')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'label')
            ->toArray();

        $this->ncHotspotsByOrigin = NonConformance::forCompany()
            ->whereNotNull('origin_name')
            ->selectRaw('origin_name as label, count(*) as count')
            ->groupBy('origin_name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'label')
            ->toArray();

        $this->ncHotspotsByRiskLevel = NonConformance::forCompany()
            ->whereNotNull('risk_level_name')
            ->selectRaw('risk_level_name as label, count(*) as count')
            ->groupBy('risk_level_name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'label')
            ->toArray();
    }

    private function loadNCTrends($startDateTime, $endDateTime)
    {
        // Get NC trends for the selected date range
        $this->ncTrends = NonConformance::forCompany()
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->selectRaw(auditSqlMonthExpression('date_identified') . ' as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    private function loadAuditTrends()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->auditTrends = [];
        
        // Determine the appropriate time period based on date range
        if ($daysDiff <= 1) {
            // Daily view - show hourly data for the day
            $this->trendLabel = 'Hourly (' . $startDate->format('M d, Y') . ')';
            $this->loadDailyAuditTrend($startDate, $endDate);
        } elseif ($daysDiff <= 7) {
            // Weekly view - show daily data
            $this->trendLabel = 'Daily (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
            $this->loadWeeklyAuditTrend($startDate, $endDate);
        } elseif ($daysDiff <= 31) {
            // Monthly view - show daily data
            $this->trendLabel = 'Daily (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
            $this->loadMonthlyAuditTrendDaily($startDate, $endDate);
        } elseif ($daysDiff <= 365) {
            // Yearly view - show monthly data
            $this->trendLabel = 'Monthly (' . $startDate->format('M Y') . ' - ' . $endDate->format('M Y') . ')';
            $this->loadYearlyAuditTrend($startDate, $endDate);
        } else {
            // Multi-year view - show yearly data
            $this->trendLabel = 'Yearly (' . $startDate->format('Y') . ' - ' . $endDate->format('Y') . ')';
            $this->loadMultiYearAuditTrend($startDate, $endDate);
        }
    }

    private function loadDailyAuditTrend($startDate, $endDate)
    {
        // Show hourly data for the selected day
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $startDate->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $hourEnd = $hourStart->copy()->addHour();
            
            $baseQuery = Audit::forCompany()
                ->whereBetween('created_at', [$hourStart, $hourEnd]);
            
            $created = (clone $baseQuery)->count();
            $scheduled = (clone $baseQuery)->where('status_name', 'Scheduled')->count();
            $inProgress = (clone $baseQuery)->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])->count();
            $closed = (clone $baseQuery)->where('status_name', 'Closed')->count();
            
            $this->auditTrends[] = [
                'month' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                'created' => $created,
                'scheduled' => $scheduled,
                'in_progress' => $inProgress,
                'closed' => $closed
            ];
        }
    }

    private function loadWeeklyAuditTrend($startDate, $endDate)
    {
        // Show daily data for the week
        $this->loadMonthlyAuditTrendDaily($startDate, $endDate);
    }

    private function loadMonthlyAuditTrendDaily($startDate, $endDate)
    {
        // Show daily data for the month
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            $baseQuery = Audit::forCompany()
                ->whereBetween('created_at', [$dayStart, $dayEnd]);
            
            $created = (clone $baseQuery)->count();
            $scheduled = (clone $baseQuery)->where('status_name', 'Scheduled')->count();
            $inProgress = (clone $baseQuery)->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])->count();
            $closed = (clone $baseQuery)->where('status_name', 'Closed')->count();
            
            $this->auditTrends[] = [
                'month' => $currentDate->format('M d'),
                'created' => $created,
                'scheduled' => $scheduled,
                'in_progress' => $inProgress,
                'closed' => $closed
            ];
            
            $currentDate->addDay();
        }
    }

    private function loadYearlyAuditTrend($startDate, $endDate)
    {
        // Show monthly data for the year
        $currentDate = $startDate->copy()->startOfMonth();
        while ($currentDate <= $endDate) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $rangeStart = max($monthStart, $startDate);
            $rangeEnd = min($monthEnd, $endDate);
            
            $baseQuery = Audit::forCompany()
                ->whereBetween('created_at', [$rangeStart, $rangeEnd]);
            
            $created = (clone $baseQuery)->count();
            $scheduled = (clone $baseQuery)->where('status_name', 'Scheduled')->count();
            $inProgress = (clone $baseQuery)->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])->count();
            $closed = (clone $baseQuery)->where('status_name', 'Closed')->count();
            
            $this->auditTrends[] = [
                'month' => $currentDate->format('M Y'),
                'created' => $created,
                'scheduled' => $scheduled,
                'in_progress' => $inProgress,
                'closed' => $closed
            ];
            
            $currentDate->addMonth();
        }
    }

    private function loadMultiYearAuditTrend($startDate, $endDate)
    {
        // Show yearly data for multi-year view
        $currentDate = $startDate->copy()->startOfYear();
        while ($currentDate <= $endDate) {
            $yearStart = $currentDate->copy()->startOfYear();
            $yearEnd = $currentDate->copy()->endOfYear();
            
            $rangeStart = max($yearStart, $startDate);
            $rangeEnd = min($yearEnd, $endDate);
            
            $baseQuery = Audit::forCompany()
                ->whereBetween('created_at', [$rangeStart, $rangeEnd]);
            
            $created = (clone $baseQuery)->count();
            $scheduled = (clone $baseQuery)->where('status_name', 'Scheduled')->count();
            $inProgress = (clone $baseQuery)->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])->count();
            $closed = (clone $baseQuery)->where('status_name', 'Closed')->count();
            
            $this->auditTrends[] = [
                'month' => $currentDate->format('Y'),
                'created' => $created,
                'scheduled' => $scheduled,
                'in_progress' => $inProgress,
                'closed' => $closed
            ];
            
            $currentDate->addYear();
        }
    }

    private function loadRecentItems()
    {
        $startDateTime = Carbon::parse($this->startDate)->startOfDay();
        $endDateTime = Carbon::parse($this->endDate)->endOfDay();
        
        // Recent audits
        $this->recentAudits = Audit::forCompany()
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['auditType', 'leadAuditor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Overdue CAPAs
        $this->overdueCAPAs = CorrectiveAction::forCompany()
            ->overdue()
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['nonConformance', 'actionOwnerUser'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
        
        // Recent NCs
        $this->recentNCs = NonConformance::forCompany()
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with(['riskLevel', 'identifiedByUser'])
            ->whereNotIn('status_name', ['Closed', 'Cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Upcoming audits
        $this->upcomingAudits = Audit::forCompany()
            ->where('status_name', 'Scheduled')
            ->where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(30))
            ->with(['auditType', 'leadAuditor'])
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.audit-module.audit-dashboard');
    }
}
