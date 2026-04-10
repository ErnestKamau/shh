<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Exports\AuditModule\AuditsExport;
use App\Exports\AuditModule\NonConformancesExport;
use App\Exports\AuditModule\CorrectiveActionsExport;
use App\Services\AuditModule\AuditStatisticsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditReportController extends Controller
{
    protected $statisticsService;

    public function __construct(AuditStatisticsService $statisticsService)
    {
        $this->middleware('auth');
        $this->statisticsService = $statisticsService;
    }

    public function index()
    {
        return view('layouts.audit.reports.index');
    }

    public function auditSummary(Request $request)
    {
        return view('layouts.audit.reports.audit-summary');
    }

    public function ncRegister(Request $request)
    {
        return view('layouts.audit.reports.nc-register');
    }

    public function capaStatus(Request $request)
    {
        return view('layouts.audit.reports.capa-status');
    }

    public function export(Request $request, $type)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $format = $request->get('format', 'xlsx');
        
        switch ($type) {
            case 'audits':
                return $this->exportAudits($dateFrom, $dateTo, $format);
            case 'ncs':
                return $this->exportNCs($dateFrom, $dateTo, $format);
            case 'capas':
                return $this->exportCAPAs($dateFrom, $dateTo, $format);
            default:
                return back()->with('error', 'Invalid export type.');
        }
    }

    private function calculateKPIs(): array
    {
        $companyId = getUserCompany() ?? 0;
        
        // Average NC closure time (days)
        $avgNCClosureTime = NonConformance::where('company_id', $companyId)
            ->whereNotNull('actual_closure_date')
            ->selectRaw('AVG(DATEDIFF(actual_closure_date, date_identified)) as avg_days')
            ->value('avg_days') ?? 0;
        
        // Average CAPA closure time (days)
        $avgCAPAClosureTime = CorrectiveAction::where('company_id', $companyId)
            ->whereNotNull('implementation_date')
            ->selectRaw('AVG(DATEDIFF(implementation_date, created_at)) as avg_days')
            ->value('avg_days') ?? 0;
        
        // NC by origin distribution
        $ncByOrigin = NonConformance::where('company_id', $companyId)
            ->selectRaw('origin_name as origin, count(*) as count')
            ->groupBy('origin_name')
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
        
        // Monthly trend (last 12 months)
        $monthlyTrend = NonConformance::where('company_id', $companyId)
            ->where('date_identified', '>=', now()->subMonths(12))
            ->selectRaw("DATE_FORMAT(date_identified, '%Y-%m') as month, count(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return [
            'avg_nc_closure_time' => round($avgNCClosureTime, 1),
            'avg_capa_closure_time' => round($avgCAPAClosureTime, 1),
            'nc_by_origin' => $ncByOrigin,
            'effectiveness_rate' => $effectivenessRate,
            'overdue_rate' => $overdueRate,
            'monthly_trend' => $monthlyTrend,
        ];
    }

    private function exportAudits($dateFrom, $dateTo, $format)
    {
        $audits = Audit::forCompany()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['auditType', 'leadAuditor', 'createdBy'])
            ->get();
        
        if ($format === 'xlsx' || $format === 'xls') {
            return Excel::download(new AuditsExport($audits), 'Audits_Export_' . date('Y-m-d') . '.' . $format);
        }
        
        // PDF export
        $company = \App\Company::find(getUserCompany() ?? 0);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.audits-export', compact('audits', 'company', 'dateFrom', 'dateTo'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Audits_Export_' . date('Y-m-d') . '.pdf');
    }

    private function exportNCs($dateFrom, $dateTo, $format)
    {
        $ncs = NonConformance::forCompany()
            ->whereBetween('date_identified', [$dateFrom, $dateTo])
            ->with(['riskLevel', 'identifiedByUser'])
            ->get();
        
        if ($format === 'xlsx' || $format === 'xls') {
            return Excel::download(new NonConformancesExport($ncs), 'NC_Register_' . date('Y-m-d') . '.' . $format);
        }
        
        // PDF export
        $company = \App\Company::find(getUserCompany() ?? 0);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.ncs-export', compact('ncs', 'company', 'dateFrom', 'dateTo'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('NC_Register_' . date('Y-m-d') . '.pdf');
    }

    private function exportCAPAs($dateFrom, $dateTo, $format)
    {
        $capas = CorrectiveAction::forCompany()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['nonConformance', 'actionOwnerUser', 'category', 'priority', 'latestVerification'])
            ->get();
        
        if ($format === 'xlsx' || $format === 'xls') {
            return Excel::download(new CorrectiveActionsExport($capas), 'CAPA_Status_' . date('Y-m-d') . '.' . $format);
        }
        
        // PDF export
        $company = \App\Company::find(getUserCompany() ?? 0);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.capas-export', compact('capas', 'company', 'dateFrom', 'dateTo'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('CAPA_Status_' . date('Y-m-d') . '.pdf');
    }

    public function advancedStatistics()
    {
        return view('layouts.audit.reports.advanced-statistics');
    }
}
