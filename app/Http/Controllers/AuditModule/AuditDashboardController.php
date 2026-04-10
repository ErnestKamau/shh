<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use Illuminate\Http\Request;

class AuditDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stats = $this->getDashboardStats();
        
        // Recent audits
        $recentAudits = Audit::forCompany()
            ->with(['auditType', 'leadAuditor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Overdue CAPAs
        $overdueCAPAs = CorrectiveAction::forCompany()
            ->overdue()
            ->with(['nonConformance', 'actionOwnerUser'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
        
        // Recent NCs
        $recentNCs = NonConformance::forCompany()
            ->with(['riskLevel', 'identifiedByUser'])
            ->whereNotIn('status_name', ['Closed', 'Cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Upcoming audits (scheduled for next 30 days)
        $upcomingAudits = Audit::forCompany()
            ->where('status_name', 'Scheduled')
            ->where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(30))
            ->with(['auditType', 'leadAuditor'])
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get();
        
        return view('layouts.audit.dashboard', compact(
            'stats',
            'recentAudits',
            'overdueCAPAs',
            'recentNCs',
            'upcomingAudits'
        ));
    }

    private function getDashboardStats(): array
    {
        $auditQuery = Audit::forCompany();
        $ncQuery = NonConformance::forCompany();
        $capaQuery = CorrectiveAction::forCompany();

        return [
            'total_audits' => (clone $auditQuery)->count(),
            'scheduled_audits' => (clone $auditQuery)->where('status_name', 'Scheduled')->count(),
            'in_progress_audits' => (clone $auditQuery)->where('status_name', 'In Progress')->count(),
            'completed_audits' => (clone $auditQuery)->where('status_name', 'Completed')->count(),
            'open_ncs' => (clone $ncQuery)->whereNotIn('status_name', ['Closed', 'Cancelled'])->count(),
            'overdue_capas' => (clone $capaQuery)
                ->whereNotIn('status_name', ['Closed', 'Verified', 'Cancelled'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
        ];
    }
}
