<?php

namespace App\Http\Controllers\Modules\Documents\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TicketSLADashboardController extends Controller
{
    /**
     * Show Ticket SLA Dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        
        $cacheKey = 'dashboard_ticket_sla_' . implode('_', array_values($filters));
        $data = Cache::remember($cacheKey, 300, function () use ($filters) {
            return [
                'slaMetrics' => $this->getSLAMetrics($filters),
                'agingByAssignee' => $this->getTicketAgingByAssignee($filters),
                'unrespondedQueue' => $this->getUnrespondedQueue($filters),
                'slaComplianceByPriority' => $this->getSLAComplianceByPriority($filters),
                'ticketTrend' => $this->getTicketTrend($filters),
                'teamWorkload' => $this->getTeamWorkload($filters),
                'escalatedTickets' => $this->getEscalatedTickets($filters),
                'resolutionTimes' => $this->getResolutionTimes($filters),
            ];
        });

        return view('documents.dashboards.ticket-sla', $data);
    }

    /**
     * Get SLA Metrics (Compliance %, Avg Response, Avg Resolution)
     */
    private function getSLAMetrics(array $filters): array
    {
        $totalTickets = DB::table('support_tickets')->count();
        $openTickets = DB::table('support_tickets')->where('status', '!=', 'Closed')->count();
        $slaCompliant = DB::table('support_tickets')->where('sla_status', 'Met')->count();
        $slaViolated = DB::table('support_tickets')->where('sla_status', 'Violated')->count();

        return [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'closedTickets' => $totalTickets - $openTickets,
            'slaCompliancePercentage' => $totalTickets > 0 ? round(($slaCompliant / $totalTickets) * 100, 2) : 0,
            'slaViolationPercentage' => $totalTickets > 0 ? round(($slaViolated / $totalTickets) * 100, 2) : 0,
            'averageResponseTime' => round($this->calculateAverageResponseTime(), 2),
            'averageResolutionTime' => round($this->calculateAverageResolutionTime(), 2),
        ];
    }

    /**
     * Get ticket aging by assignee
     */
    private function getTicketAgingByAssignee(array $filters): array
    {
        return DB::table('support_tickets')
            ->join('users', 'support_tickets.assigned_to', '=', 'users.id')
            ->select(
                'users.name as assignee_name',
                DB::raw('COUNT(*) as ticket_count'),
                DB::raw('SUM(CASE WHEN support_tickets.status != "Closed" THEN 1 ELSE 0 END) as open_tickets'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (NOW() - support_tickets.created_at))/3600) as avg_age_hours'),
                DB::raw('MAX(EXTRACT(EPOCH FROM (NOW() - support_tickets.created_at))/3600) as oldest_ticket_hours')
            )
            ->where('support_tickets.created_at', '>=', now()->subDays($filters['days'] ?? 30))
            ->groupBy('users.name')
            ->orderByDesc(DB::raw('AVG(EXTRACT(EPOCH FROM (NOW() - support_tickets.created_at))/3600)'))
            ->limit(20)
            ->get()
            ->map(fn($row) => [
                'assignee' => $row->assignee_name,
                'totalTickets' => $row->ticket_count,
                'openTickets' => $row->open_tickets,
                'avgAgeHours' => round($row->avg_age_hours ?? 0, 1),
                'oldestTicketHours' => round($row->oldest_ticket_hours ?? 0, 1),
                'workload' => $row->ticket_count > 15 ? 'Heavy' : ($row->ticket_count > 8 ? 'Moderate' : 'Light'),
            ])
            ->toArray();
    }

    /**
     * Get unresponded tickets queue
     */
    private function getUnrespondedQueue(array $filters): array
    {
        return DB::table('support_tickets')
            ->select(
                'support_tickets.id',
                'support_tickets.ticket_number',
                'support_tickets.subject',
                'support_tickets.priority',
                'support_tickets.created_at',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - support_tickets.created_at))/3600 as hours_waiting'),
                'support_tickets.sla_response_deadline'
            )
            ->where('support_tickets.status', '!=', 'Closed')
            ->where('support_tickets.first_response_at', null)
            ->orderBy('support_tickets.priority', 'asc')
            ->orderBy('support_tickets.created_at', 'asc')
            ->limit(20)
            ->get()
            ->map(fn($row) => [
                'ticketId' => $row->id,
                'ticketNumber' => $row->ticket_number,
                'subject' => $row->subject,
                'priority' => $row->priority,
                'createdAt' => $row->created_at,
                'hoursWaiting' => round($row->hours_waiting, 1),
                'slaDeadline' => $row->sla_response_deadline,
                'overdueSLA' => $row->sla_response_deadline < now(),
                'urgency' => $row->priority === 'Critical' || $row->sla_response_deadline < now() ? 'Urgent' : 'Standard',
            ])
            ->toArray();
    }

    /**
     * Get SLA compliance by priority
     */
    private function getSLAComplianceByPriority(array $filters): array
    {
        return DB::table('support_tickets')
            ->select(
                'priority',
                DB::raw('COUNT(*) as total_tickets'),
                DB::raw('SUM(CASE WHEN sla_status = "Met" THEN 1 ELSE 0 END) as compliant_tickets'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (COALESCE(resolved_at, NOW()) - created_at))/3600) as avg_resolution_hours')
            )
            ->where('created_at', '>=', now()->subDays($filters['days'] ?? 30))
            ->groupBy('priority')
            ->orderByRaw('CASE WHEN priority = "Critical" THEN 1 WHEN priority = "High" THEN 2 WHEN priority = "Medium" THEN 3 ELSE 4 END')
            ->get()
            ->map(fn($row) => [
                'priority' => $row->priority,
                'totalTickets' => $row->total_tickets,
                'compliantTickets' => $row->compliant_tickets,
                'compliancePercentage' => $row->total_tickets > 0 ? round(($row->compliant_tickets / $row->total_tickets) * 100, 2) : 0,
                'avgResolutionHours' => round($row->avg_resolution_hours ?? 0, 2),
            ])
            ->toArray();
    }

    /**
     * Get ticket creation trend
     */
    private function getTicketTrend(array $filters): array
    {
        return DB::table('support_tickets')
            ->select(
                DB::raw("DATE(support_tickets.created_at) as date"),
                DB::raw('COUNT(*) as created_count'),
                DB::raw('SUM(CASE WHEN status = "Closed" THEN 1 ELSE 0 END) as resolved_count'),
                DB::raw('SUM(CASE WHEN sla_status = "Violated" THEN 1 ELSE 0 END) as violated_count')
            )
            ->where('created_at', '>=', now()->subDays($filters['days'] ?? 30))
            ->groupBy(DB::raw('DATE(support_tickets.created_at)'))
            ->orderBy(DB::raw('DATE(support_tickets.created_at)'))
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'created' => $row->created_count,
                'resolved' => $row->resolved_count,
                'violated' => $row->violated_count,
            ])
            ->toArray();
    }

    /**
     * Get team workload distribution
     */
    private function getTeamWorkload(array $filters): array
    {
        return DB::table('support_tickets')
            ->join('users', 'support_tickets.assigned_to', '=', 'users.id')
            ->select(
                'users.name as assignee_name',
                'support_tickets.priority',
                DB::raw('COUNT(*) as count')
            )
            ->where('support_tickets.status', '!=', 'Closed')
            ->where('support_tickets.created_at', '>=', now()->subDays($filters['days'] ?? 30))
            ->groupBy('users.name', 'support_tickets.priority')
            ->orderBy('users.name')
            ->get()
            ->groupBy('assignee_name')
            ->map(fn($group) => [
                'assignee' => $group->first()->assignee_name,
                'critical' => $group->where('priority', 'Critical')->sum('count'),
                'high' => $group->where('priority', 'High')->sum('count'),
                'medium' => $group->where('priority', 'Medium')->sum('count'),
                'low' => $group->where('priority', 'Low')->sum('count'),
                'total' => $group->sum('count'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get escalated tickets
     */
    private function getEscalatedTickets(array $filters): array
    {
        return DB::table('support_tickets')
            ->select(
                'support_tickets.id',
                'support_tickets.ticket_number',
                'support_tickets.subject',
                'support_tickets.priority',
                'support_tickets.escalation_level',
                'support_tickets.escalated_at',
                'support_tickets.status'
            )
            ->where('support_tickets.escalation_level', '>', 0)
            ->where('support_tickets.status', '!=', 'Closed')
            ->orderByDesc('support_tickets.escalation_level')
            ->orderBy('support_tickets.escalated_at')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'ticketId' => $row->id,
                'ticketNumber' => $row->ticket_number,
                'subject' => $row->subject,
                'priority' => $row->priority,
                'escalationLevel' => $row->escalation_level,
                'escalatedAt' => $row->escalated_at,
                'status' => $row->status,
            ])
            ->toArray();
    }

    /**
     * Get resolution time statistics
     */
    private function getResolutionTimes(array $filters): array
    {
        return DB::table('support_tickets')
            ->select(
                DB::raw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600) as avg_resolution_hours'),
                DB::raw('PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY EXTRACT(EPOCH FROM (resolved_at - created_at))/3600) as median_resolution_hours'),
                DB::raw('MIN(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600) as min_resolution_hours'),
                DB::raw('MAX(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600) as max_resolution_hours')
            )
            ->where('status', 'Closed')
            ->where('resolved_at', 'is not', null)
            ->where('created_at', '>=', now()->subDays($filters['days'] ?? 30))
            ->first();
    }

    /**
     * Calculate average response time in hours
     */
    private function calculateAverageResponseTime(): float
    {
        $result = DB::table('support_tickets')
            ->select(
                DB::raw('AVG(EXTRACT(EPOCH FROM (first_response_at - created_at))/3600) as avg_response_hours')
            )
            ->where('first_response_at', 'is not', null)
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        return $result->avg_response_hours ?? 0;
    }

    /**
     * Calculate average resolution time in hours
     */
    private function calculateAverageResolutionTime(): float
    {
        $result = DB::table('support_tickets')
            ->select(
                DB::raw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600) as avg_resolution_hours')
            )
            ->where('status', 'Closed')
            ->where('resolved_at', 'is not', null)
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        return $result->avg_resolution_hours ?? 0;
    }

    /**
     * Parse filters from request
     */
    private function parseFilters(Request $request): array
    {
        return [
            'days' => $request->input('days', 30),
            'priority' => $request->input('priority', null),
            'status' => $request->input('status', null),
            'assignee' => $request->input('assignee', null),
        ];
    }

    /**
     * Export Ticket SLA Dashboard
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        $filters = $this->parseFilters($request);
        
        $data = [
            'slaMetrics' => $this->getSLAMetrics($filters),
            'agingByAssignee' => $this->getTicketAgingByAssignee($filters),
            'slaComplianceByPriority' => $this->getSLAComplianceByPriority($filters),
            'escalatedTickets' => $this->getEscalatedTickets($filters),
        ];

        return response()->json($data);
    }
}
