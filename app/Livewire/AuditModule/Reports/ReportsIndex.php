<?php

namespace App\Livewire\AuditModule\Reports;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use Livewire\Component;
use Carbon\Carbon;

class ReportsIndex extends Component
{
    public $kpis = [];

    public function mount()
    {
        $this->loadKPIs();
    }

    private function loadKPIs()
    {
        $companyId = getUserCompany() ?? 0;
        
        // Average NC closure time (days)
        $avgNCClosureTime = NonConformance::where('company_id', $companyId)
            ->whereNotNull('actual_closure_date')
            ->selectRaw('AVG((actual_closure_date::date - date_identified::date)) as avg_days')
            ->value('avg_days') ?? 0;
        
        // Average CAPA closure time (days)
        $avgCAPAClosureTime = CorrectiveAction::where('company_id', $companyId)
            ->whereNotNull('implementation_date')
            ->selectRaw('AVG((implementation_date::date - created_at::date)) as avg_days')
            ->value('avg_days') ?? 0;
        
        // NC by origin distribution
        $ncByOrigin = NonConformance::where('company_id', $companyId)
            ->whereNotNull('origin_name')
            ->selectRaw('origin_name as origin, count(*) as count')
            ->groupBy('origin_name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'origin')
            ->toArray();
        
        // CAPA effectiveness rate
        $totalVerified = CorrectiveAction::where('company_id', $companyId)
            ->whereHas('latestVerification')
            ->count();
        
        $effectiveVerified = CorrectiveAction::where('company_id', $companyId)
            ->whereHas('latestVerification', function ($q) {
                $q->where('effectiveness_result_name', 'Effective');
            })
            ->count();
        
        $effectivenessRate = $totalVerified > 0 ? round(($effectiveVerified / $totalVerified) * 100, 1) : 0;
        
        // Overdue rate
        $totalOpenCAPAs = CorrectiveAction::where('company_id', $companyId)
            ->whereNotIn('status_name', ['Closed', 'Verified', 'Cancelled'])
            ->count();
        
        $overdueCAPAs = CorrectiveAction::where('company_id', $companyId)
            ->overdue()
            ->count();
        
        $overdueRate = $totalOpenCAPAs > 0 ? round(($overdueCAPAs / $totalOpenCAPAs) * 100, 1) : 0;
        
        $this->kpis = [
            'avg_nc_closure_time' => round($avgNCClosureTime, 1),
            'avg_capa_closure_time' => round($avgCAPAClosureTime, 1),
            'nc_by_origin' => $ncByOrigin,
            'effectiveness_rate' => $effectivenessRate,
            'overdue_rate' => $overdueRate,
        ];
    }

    public function render()
    {
        return view('livewire.audit-module.reports.reports-index');
    }
}
