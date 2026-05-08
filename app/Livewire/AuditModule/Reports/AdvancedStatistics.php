<?php

namespace App\Livewire\AuditModule\Reports;

use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\RootCauseAnalysis;
use App\Services\AuditModule\AuditStatisticsService;
use Livewire\Component;
use Carbon\Carbon;

class AdvancedStatistics extends Component
{
    public $startDate;
    public $endDate;
    public $trendLabel = '';
    
    // Dynamic trend data
    public $ncTrends = [];
    public $capaTrends = [];
    public $auditTrends = [];
    public $rootCauseTrends = [];
    
    // Statistics data
    public $repeatedNCs = [];
    public $ncClosureTrends = [];
    public $capaEffectivenessTrends = [];
    public $topRootCauseMethods = [];
    
    // Distribution data (filtered by date)
    public $ncByOrigin = [];
    public $ncByRiskLevel = [];
    public $ncByDepartment = [];
    public $avgClosureTime = [];

    protected $statisticsService;

    public function boot(AuditStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function mount()
    {
        // Set default dates (last 12 months)
        $this->startDate = Carbon::now()->subMonths(12)->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $this->loadStatistics();
    }

    public function updatedStartDate()
    {
        $this->loadStatistics();
        $this->dispatch('chartsDataUpdated');
    }

    public function updatedEndDate()
    {
        $this->loadStatistics();
        $this->dispatch('chartsDataUpdated');
    }

    public function setDateRange($range)
    {
        switch ($range) {
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
                $this->startDate = now()->subMonths(6)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_12_months':
                $this->startDate = now()->subMonths(12)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
        }
        
        $this->loadStatistics();
        $this->dispatch('chartsDataUpdated');
    }

    public function loadStatistics()
    {
        // Load all statistics (no company filtering)
        $this->repeatedNCs = $this->statisticsService->getRepeatedNCs(null);
        $this->topRootCauseMethods = $this->statisticsService->getTopRootCauseMethods(null);
        $this->ncClosureTrends = $this->statisticsService->getNCClosureTrends(null);
        $this->capaEffectivenessTrends = $this->statisticsService->getCAPAEffectivenessTrends(null);
        
        // Load all dynamic trends (date filtered)
        $this->loadRootCauseTrends();
        $this->loadNCTrends();
        $this->loadCAPATrends();
        $this->loadAuditTrends();
        
        // Load distribution data (date filtered)
        $this->loadNCByOrigin();
        $this->loadNCByRiskLevel();
        $this->loadNCByDepartment();
        $this->loadAvgClosureTime();
    }

    private function loadRootCauseTrends()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->rootCauseTrends = [];
        
        // Determine the appropriate time period based on date range
        if ($daysDiff <= 31) {
            // Monthly view - show daily data
            $this->trendLabel = 'Daily (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
            $this->loadDailyRCATrend($startDate, $endDate);
        } elseif ($daysDiff <= 365) {
            // Yearly view - show monthly data
            $this->trendLabel = 'Monthly (' . $startDate->format('M Y') . ' - ' . $endDate->format('M Y') . ')';
            $this->loadMonthlyRCATrend($startDate, $endDate);
        } else {
            // Multi-year view - show yearly data
            $this->trendLabel = 'Yearly (' . $startDate->format('Y') . ' - ' . $endDate->format('Y') . ')';
            $this->loadYearlyRCATrend($startDate, $endDate);
        }
    }

    private function loadDailyRCATrend($startDate, $endDate)
    {
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            // Query all RCAs (no company filter)
            $count = RootCauseAnalysis::whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            
            $this->rootCauseTrends[] = [
                'period' => $currentDate->format('M d'),
                'count' => $count
            ];
            
            $currentDate->addDay();
        }
    }

    private function loadMonthlyRCATrend($startDate, $endDate)
    {
        $currentDate = $startDate->copy()->startOfMonth();
        while ($currentDate <= $endDate) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $rangeStart = max($monthStart, $startDate);
            $rangeEnd = min($monthEnd, $endDate);
            
            // Query all RCAs (no company filter)
            $count = RootCauseAnalysis::whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->count();
            
            $this->rootCauseTrends[] = [
                'period' => $currentDate->format('M Y'),
                'count' => $count
            ];
            
            $currentDate->addMonth();
        }
    }

    private function loadYearlyRCATrend($startDate, $endDate)
    {
        $currentDate = $startDate->copy()->startOfYear();
        while ($currentDate <= $endDate) {
            $yearStart = $currentDate->copy()->startOfYear();
            $yearEnd = $currentDate->copy()->endOfYear();
            
            $rangeStart = max($yearStart, $startDate);
            $rangeEnd = min($yearEnd, $endDate);
            
            // Query all RCAs (no company filter)
            $count = RootCauseAnalysis::whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->count();
            
            $this->rootCauseTrends[] = [
                'period' => $currentDate->format('Y'),
                'count' => $count
            ];
            
            $currentDate->addYear();
        }
    }

    private function loadNCTrends()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->ncTrends = [];
        
        if ($daysDiff <= 31) {
            // Daily data
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $count = NonConformance::whereDate('date_identified', $currentDate)->count();
                $this->ncTrends[] = ['period' => $currentDate->format('M d'), 'count' => $count];
                $currentDate->addDay();
            }
        } elseif ($daysDiff <= 365) {
            // Monthly data
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                $rangeStart = max($monthStart, $startDate);
                $rangeEnd = min($monthEnd, $endDate);
                
                $count = NonConformance::whereBetween('date_identified', [$rangeStart, $rangeEnd])->count();
                $this->ncTrends[] = ['period' => $currentDate->format('M Y'), 'count' => $count];
                $currentDate->addMonth();
            }
        } else {
            // Yearly data
            $currentDate = $startDate->copy()->startOfYear();
            while ($currentDate <= $endDate) {
                $yearStart = $currentDate->copy()->startOfYear();
                $yearEnd = $currentDate->copy()->endOfYear();
                $rangeStart = max($yearStart, $startDate);
                $rangeEnd = min($yearEnd, $endDate);
                
                $count = NonConformance::whereBetween('date_identified', [$rangeStart, $rangeEnd])->count();
                $this->ncTrends[] = ['period' => $currentDate->format('Y'), 'count' => $count];
                $currentDate->addYear();
            }
        }
    }

    private function loadCAPATrends()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->capaTrends = [];
        
        if ($daysDiff <= 31) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $count = CorrectiveAction::whereDate('created_at', $currentDate)->count();
                $this->capaTrends[] = ['period' => $currentDate->format('M d'), 'count' => $count];
                $currentDate->addDay();
            }
        } elseif ($daysDiff <= 365) {
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                $rangeStart = max($monthStart, $startDate);
                $rangeEnd = min($monthEnd, $endDate);
                
                $count = CorrectiveAction::whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
                $this->capaTrends[] = ['period' => $currentDate->format('M Y'), 'count' => $count];
                $currentDate->addMonth();
            }
        } else {
            $currentDate = $startDate->copy()->startOfYear();
            while ($currentDate <= $endDate) {
                $yearStart = $currentDate->copy()->startOfYear();
                $yearEnd = $currentDate->copy()->endOfYear();
                $rangeStart = max($yearStart, $startDate);
                $rangeEnd = min($yearEnd, $endDate);
                
                $count = CorrectiveAction::whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
                $this->capaTrends[] = ['period' => $currentDate->format('Y'), 'count' => $count];
                $currentDate->addYear();
            }
        }
    }

    private function loadAuditTrends()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->auditTrends = [];
        
        if ($daysDiff <= 31) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $count = \App\Models\AuditModule\Audit::whereDate('scheduled_date', $currentDate)->count();
                $this->auditTrends[] = ['period' => $currentDate->format('M d'), 'count' => $count];
                $currentDate->addDay();
            }
        } elseif ($daysDiff <= 365) {
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                $rangeStart = max($monthStart, $startDate);
                $rangeEnd = min($monthEnd, $endDate);
                
                $count = \App\Models\AuditModule\Audit::whereBetween('scheduled_date', [$rangeStart, $rangeEnd])->count();
                $this->auditTrends[] = ['period' => $currentDate->format('M Y'), 'count' => $count];
                $currentDate->addMonth();
            }
        } else {
            $currentDate = $startDate->copy()->startOfYear();
            while ($currentDate <= $endDate) {
                $yearStart = $currentDate->copy()->startOfYear();
                $yearEnd = $currentDate->copy()->endOfYear();
                $rangeStart = max($yearStart, $startDate);
                $rangeEnd = min($yearEnd, $endDate);
                
                $count = \App\Models\AuditModule\Audit::whereBetween('scheduled_date', [$rangeStart, $rangeEnd])->count();
                $this->auditTrends[] = ['period' => $currentDate->format('Y'), 'count' => $count];
                $currentDate->addYear();
            }
        }
    }

    private function loadNCByOrigin()
    {
        $this->ncByOrigin = NonConformance::whereBetween('date_identified', [$this->startDate, $this->endDate])
            ->selectRaw('origin_name, COUNT(*) as count')
            ->whereNotNull('origin_name')
            ->groupBy('origin_name')
            ->orderByDesc('count')
            ->pluck('count', 'origin_name')
            ->toArray();
    }

    private function loadNCByRiskLevel()
    {
        $this->ncByRiskLevel = NonConformance::whereBetween('date_identified', [$this->startDate, $this->endDate])
            ->selectRaw('risk_level_name, COUNT(*) as count')
            ->whereNotNull('risk_level_name')
            ->groupBy('risk_level_name')
            ->orderByDesc('count')
            ->pluck('count', 'risk_level_name')
            ->toArray();
    }

    private function loadNCByDepartment()
    {
        $this->ncByDepartment = NonConformance::whereBetween('non_conformances.date_identified', [$this->startDate, $this->endDate])
            ->join('iso_audits', 'non_conformances.audit_id', '=', 'iso_audits.id')
            ->selectRaw('iso_audits.auditee_department_name as department, COUNT(*) as count')
            ->whereNotNull('iso_audits.auditee_department_name')
            ->groupBy('iso_audits.auditee_department_name')
            ->orderByDesc('count')
            ->pluck('count', 'department')
            ->toArray();
    }

    private function loadAvgClosureTime()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        $this->avgClosureTime = [];
        
        if ($daysDiff <= 31) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $avg = NonConformance::whereDate('date_identified', $currentDate)
                    ->whereNotNull('actual_closure_date')
                    ->selectRaw('AVG((actual_closure_date::date - date_identified::date)) as avg_days')
                    ->value('avg_days');
                    
                $this->avgClosureTime[] = [
                    'period' => $currentDate->format('M d'),
                    'avg_days' => $avg ? round($avg, 1) : 0
                ];
                $currentDate->addDay();
            }
        } elseif ($daysDiff <= 365) {
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                $rangeStart = max($monthStart, $startDate);
                $rangeEnd = min($monthEnd, $endDate);
                
                $avg = NonConformance::whereBetween('date_identified', [$rangeStart, $rangeEnd])
                    ->whereNotNull('actual_closure_date')
                    ->selectRaw('AVG((actual_closure_date::date - date_identified::date)) as avg_days')
                    ->value('avg_days');
                    
                $this->avgClosureTime[] = [
                    'period' => $currentDate->format('M Y'),
                    'avg_days' => $avg ? round($avg, 1) : 0
                ];
                $currentDate->addMonth();
            }
        } else {
            $currentDate = $startDate->copy()->startOfYear();
            while ($currentDate <= $endDate) {
                $yearStart = $currentDate->copy()->startOfYear();
                $yearEnd = $currentDate->copy()->endOfYear();
                $rangeStart = max($yearStart, $startDate);
                $rangeEnd = min($yearEnd, $endDate);
                
                $avg = NonConformance::whereBetween('date_identified', [$rangeStart, $rangeEnd])
                    ->whereNotNull('actual_closure_date')
                    ->selectRaw('AVG((actual_closure_date::date - date_identified::date)) as avg_days')
                    ->value('avg_days');
                    
                $this->avgClosureTime[] = [
                    'period' => $currentDate->format('Y'),
                    'avg_days' => $avg ? round($avg, 1) : 0
                ];
                $currentDate->addYear();
            }
        }
    }

    public function render()
    {
        return view('livewire.audit-module.reports.advanced-statistics');
    }
}
