<?php

namespace App\Livewire\RiskModule;

use App\Models\RiskManagement\Risk;
use Livewire\Component;
use Carbon\Carbon;

class RiskDashboard extends Component
{
    // Date filters
    public $startDate;
    public $endDate;
    
    public $stats = [];
    public $workflowTotals = [];
    public $workflowSteps = [];
    public $riskHotspotsByCategory = [];
    public $riskHotspotsBySource = [];
    public $riskHotspotsByLevel = [];
    public $riskTrends = [];
    public $trendLabel = '';
    
    public $recentRisks = [];
    public $overdueReviews = [];
    public $criticalRisks = [];

    protected $listeners = [
        'refreshRiskDashboard' => 'loadDashboardData',
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
            \Log::error('Risk Dashboard Error: ' . $e->getMessage());
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
            $this->loadRiskHotspots();
            $this->loadRiskTrends($startDateTime, $endDateTime);
            
            // Load recent items
            $this->loadRecentItems();
        } catch (\Exception $e) {
            \Log::error('Risk Dashboard Data Loading Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function loadDashboardStats($startDateTime, $endDateTime)
    {
        $companyId = \riskCompanyId();
        
        $risksQuery = Risk::where('company_id', $companyId)
            ->whereBetween('date_identified', [$startDateTime, $endDateTime]);
        
        // Get workflow steps mapping
        $workflowSteps = \getRiskWorkflowSteps();
        
        // Initialize stats with workflow step counts
        $workflowStats = [];
        foreach ($workflowSteps as $stepNum => $stepName) {
            if ($stepNum === 0) {
                continue;
            }

            $riskWorkflowStep = \mapRiskStatusWorkflowStepToRiskRecordStep((int) $stepNum);
            
            // Create a key from step name (lowercase, spaces to underscores)
            $key = strtolower(str_replace(' ', '_', $stepName));
            
            // Also create common aliases for backward compatibility
            $aliases = $this->getWorkflowStepAliases($stepNum, $stepName);
            
            $count = Risk::where('company_id', $companyId)
                ->where('workflow_step', $riskWorkflowStep)
                ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                ->count();
            
            // Store with primary key
            $workflowStats[$key] = $count;
            
            // Also store with aliases for backward compatibility
            foreach ($aliases as $alias) {
                if (!isset($workflowStats[$alias])) {
                    $workflowStats[$alias] = $count;
                }
            }
        }
        
        $this->stats = [
            'risks' => array_merge([
                'total' => $risksQuery->count(),
            ], $workflowStats),
            'risk_levels' => [
                'critical' => Risk::where('company_id', $companyId)
                    ->where('risk_level', 'Critical')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'high' => Risk::where('company_id', $companyId)
                    ->where('risk_level', 'High')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'medium' => Risk::where('company_id', $companyId)
                    ->where('risk_level', 'Medium')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
                'low' => Risk::where('company_id', $companyId)
                    ->where('risk_level', 'Low')
                    ->whereBetween('date_identified', [$startDateTime, $endDateTime])
                    ->count(),
            ],
        ];
    }
    
    /**
     * Get aliases for workflow steps for backward compatibility
     */
    private function getWorkflowStepAliases($stepNum, $stepName)
    {
        $aliases = [];
        
        // Map common workflow step numbers to their traditional names
        $stepMapping = [
            1 => ['identified'],
            2 => ['assessed', 'under_assessment'],
            3 => ['evaluated', 'under_evaluation'],
            4 => ['treatment_planned', 'treatment_planning'],
            5 => ['implemented', 'treatment_implementation'],
            6 => ['monitored', 'risk_monitoring', 'under_monitoring'],
            7 => ['closed'],
        ];
        
        if (isset($stepMapping[$stepNum])) {
            $aliases = $stepMapping[$stepNum];
        }
        
        return $aliases;
    }

    private function loadWorkflowTotals()
    {
        $this->workflowTotals = \getRiskWorkflowTotals();
        $this->workflowSteps = \getRiskWorkflowSteps();
    }

    private function loadRiskHotspots()
    {
        $companyId = \riskCompanyId();
        $startDateTime = Carbon::parse($this->startDate)->startOfDay();
        $endDateTime = Carbon::parse($this->endDate)->endOfDay();
        
        // By Category - optimized query
        $this->riskHotspotsByCategory = Risk::where('company_id', $companyId)
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($risks) {
                return [
                    'name' => $risks->first()->category->name ?? 'Uncategorized',
                    'count' => $risks->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->toArray();
        
        // By Source - optimized query
        $this->riskHotspotsBySource = Risk::where('company_id', $companyId)
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with('source')
            ->get()
            ->groupBy('source_id')
            ->map(function ($risks) {
                return [
                    'name' => $risks->first()->source->name ?? 'Unknown',
                    'count' => $risks->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->toArray();
        
        // By Risk Level - optimized query
        $this->riskHotspotsByLevel = Risk::where('company_id', $companyId)
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->get()
            ->groupBy('risk_level')
            ->map(function ($risks, $level) {
                return [
                    'name' => $level ?? 'Unspecified',
                    'count' => $risks->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }

    private function loadRiskTrends($startDateTime, $endDateTime)
    {
        $companyId = \riskCompanyId();
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->riskTrends = [];
        
        // Determine the appropriate time period based on date range
        if ($daysDiff <= 1) {
            $this->trendLabel = 'Hourly (' . $startDate->format('M d, Y') . ')';
            $this->loadDailyRiskTrend($startDate, $endDate, $companyId);
        } elseif ($daysDiff <= 7) {
            $this->trendLabel = 'Daily (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
            $this->loadWeeklyRiskTrend($startDate, $endDate, $companyId);
        } elseif ($daysDiff <= 31) {
            $this->trendLabel = 'Daily (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
            $this->loadMonthlyRiskTrendDaily($startDate, $endDate, $companyId);
        } elseif ($daysDiff <= 365) {
            $this->trendLabel = 'Monthly (' . $startDate->format('M Y') . ' - ' . $endDate->format('M Y') . ')';
            $this->loadYearlyRiskTrend($startDate, $endDate, $companyId);
        } else {
            $this->trendLabel = 'Yearly (' . $startDate->format('Y') . ' - ' . $endDate->format('Y') . ')';
            $this->loadMultiYearRiskTrend($startDate, $endDate, $companyId);
        }
    }

    private function loadDailyRiskTrend($startDate, $endDate, $companyId)
    {
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $startDate->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $hourEnd = $hourStart->copy()->addHour();
            
            $baseQuery = Risk::where('company_id', $companyId)
                ->whereBetween('date_identified', [$hourStart, $hourEnd]);
            
            $created = $baseQuery->clone()->count();
            $critical = $baseQuery->clone()->where('risk_level', 'Critical')->count();
            $high = $baseQuery->clone()->where('risk_level', 'High')->count();
            $closed = $baseQuery->clone()->where('status_name', 'Closed')->count();
            
            $this->riskTrends[] = [
                'month' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                'created' => $created,
                'critical' => $critical,
                'high' => $high,
                'closed' => $closed
            ];
        }
    }

    private function loadWeeklyRiskTrend($startDate, $endDate, $companyId)
    {
        $this->loadMonthlyRiskTrendDaily($startDate, $endDate, $companyId);
    }

    private function loadMonthlyRiskTrendDaily($startDate, $endDate, $companyId)
    {
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            $baseQuery = Risk::where('company_id', $companyId)
                ->whereBetween('date_identified', [$dayStart, $dayEnd]);
            
            $created = $baseQuery->clone()->count();
            $critical = $baseQuery->clone()->where('risk_level', 'Critical')->count();
            $high = $baseQuery->clone()->where('risk_level', 'High')->count();
            $closed = $baseQuery->clone()->where('status_name', 'Closed')->count();
            
            $this->riskTrends[] = [
                'month' => $currentDate->format('M d'),
                'created' => $created,
                'critical' => $critical,
                'high' => $high,
                'closed' => $closed
            ];
            
            $currentDate->addDay();
        }
    }

    private function loadYearlyRiskTrend($startDate, $endDate, $companyId)
    {
        $currentDate = $startDate->copy()->startOfMonth();
        while ($currentDate <= $endDate) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $rangeStart = max($monthStart, $startDate);
            $rangeEnd = min($monthEnd, $endDate);
            
            $baseQuery = Risk::where('company_id', $companyId)
                ->whereBetween('date_identified', [$rangeStart, $rangeEnd]);
            
            $created = $baseQuery->clone()->count();
            $critical = $baseQuery->clone()->where('risk_level', 'Critical')->count();
            $high = $baseQuery->clone()->where('risk_level', 'High')->count();
            $closed = $baseQuery->clone()->where('status_name', 'Closed')->count();
            
            $this->riskTrends[] = [
                'month' => $currentDate->format('M Y'),
                'created' => $created,
                'critical' => $critical,
                'high' => $high,
                'closed' => $closed
            ];
            
            $currentDate->addMonth();
        }
    }

    private function loadMultiYearRiskTrend($startDate, $endDate, $companyId)
    {
        $currentDate = $startDate->copy()->startOfYear();
        while ($currentDate <= $endDate) {
            $yearStart = $currentDate->copy()->startOfYear();
            $yearEnd = $currentDate->copy()->endOfYear();
            
            $rangeStart = max($yearStart, $startDate);
            $rangeEnd = min($yearEnd, $endDate);
            
            $baseQuery = Risk::where('company_id', $companyId)
                ->whereBetween('date_identified', [$rangeStart, $rangeEnd]);
            
            $created = $baseQuery->clone()->count();
            $critical = $baseQuery->clone()->where('risk_level', 'Critical')->count();
            $high = $baseQuery->clone()->where('risk_level', 'High')->count();
            $closed = $baseQuery->clone()->where('status_name', 'Closed')->count();
            
            $this->riskTrends[] = [
                'month' => $currentDate->format('Y'),
                'created' => $created,
                'critical' => $critical,
                'high' => $high,
                'closed' => $closed
            ];
            
            $currentDate->addYear();
        }
    }

    private function loadRecentItems()
    {
        $startDateTime = Carbon::parse($this->startDate)->startOfDay();
        $endDateTime = Carbon::parse($this->endDate)->endOfDay();
        
        // Recent risks
        $this->recentRisks = Risk::forCompany()
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with(['category', 'source', 'riskOwner'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Overdue reviews
        $this->overdueReviews = Risk::forCompany()
            ->whereNotNull('next_review_date')
            ->where('next_review_date', '<', now())
            ->whereNotIn('status_name', ['Closed', 'Cancelled'])
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with(['category', 'source'])
            ->orderBy('next_review_date', 'asc')
            ->limit(5)
            ->get();
        
        // Critical risks
        $this->criticalRisks = Risk::forCompany()
            ->where('risk_level', 'Critical')
            ->whereNotIn('status_name', ['Closed', 'Cancelled'])
            ->whereBetween('date_identified', [$startDateTime, $endDateTime])
            ->with(['category', 'source', 'riskOwner'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.risk-module.risk-dashboard');
    }
}


