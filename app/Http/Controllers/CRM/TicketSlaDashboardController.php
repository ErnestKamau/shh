<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\Complaint;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TicketSlaDashboardController extends Controller
{
    protected $pgsqlConnection = 'pgsql_ai';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Ticket SLA board
     */
    public function index()
    {
        $data = [
            'firstResponseSla' => $this->getFirstResponseSla(),
            'resolutionSla' => $this->getResolutionSla(),
            'reopenRate' => $this->getReopenRate(),
            'escalationWaterfall' => $this->getEscalationWaterfall(),
            'agingByAssignee' => $this->getAgingByAssignee(),
            'unrespondedQueue' => $this->getUnrespondedQueue(),
            'slaMetrics' => $this->getSlaMetrics(),
        ];

        return view('crm.dashboards.ticket-sla', $data);
    }

    /**
     * First-response SLA compliance
     */
    private function getFirstResponseSla()
    {
        try {
            $sla = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    COUNT(DISTINCT ticket_id) as total_tickets,
                    COUNT(DISTINCT CASE WHEN first_response_sla_met = 1 THEN ticket_id END) as sla_met,
                    COUNT(DISTINCT CASE WHEN first_response_sla_met = 0 THEN ticket_id END) as sla_breached
                ')
                ->first();

            return [
                'totalTickets' => (int)$sla->total_tickets,
                'slaMet' => (int)$sla->sla_met,
                'slaBreached' => (int)$sla->sla_breached,
                'complianceRate' => $sla->total_tickets > 0 
                    ? round(($sla->sla_met / $sla->total_tickets) * 100, 2)
                    : 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load first response SLA: ' . $e->getMessage());
            return ['totalTickets' => 0, 'slaMet' => 0, 'slaBreached' => 0, 'complianceRate' => 0];
        }
    }

    /**
     * Resolution SLA compliance
     */
    private function getResolutionSla()
    {
        try {
            $sla = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    COUNT(DISTINCT ticket_id) as total_tickets,
                    COUNT(DISTINCT CASE WHEN resolution_sla_met = 1 THEN ticket_id END) as sla_met,
                    COUNT(DISTINCT CASE WHEN resolution_sla_met = 0 THEN ticket_id END) as sla_breached
                ')
                ->first();

            return [
                'totalTickets' => (int)$sla->total_tickets,
                'slaMet' => (int)$sla->sla_met,
                'slaBreached' => (int)$sla->sla_breached,
                'complianceRate' => $sla->total_tickets > 0
                    ? round(($sla->sla_met / $sla->total_tickets) * 100, 2)
                    : 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load resolution SLA: ' . $e->getMessage());
            return ['totalTickets' => 0, 'slaMet' => 0, 'slaBreached' => 0, 'complianceRate' => 0];
        }
    }

    /**
     * Reopen rate analysis
     */
    private function getReopenRate()
    {
        try {
            $reopens = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    COUNT(DISTINCT ticket_id) as total_closed,
                    COUNT(DISTINCT CASE WHEN was_reopened = 1 THEN ticket_id END) as reopened_tickets
                ')
                ->where('status', 'CLOSED')
                ->first();

            $total_closed = $reopens->total_closed ?? 0;
            $reopened = $reopens->reopened_tickets ?? 0;

            return [
                'totalClosed' => (int)$total_closed,
                'reopenedTickets' => (int)$reopened,
                'reopenRate' => $total_closed > 0
                    ? round(($reopened / $total_closed) * 100, 2)
                    : 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load reopen rate: ' . $e->getMessage());
            return ['totalClosed' => 0, 'reopenedTickets' => 0, 'reopenRate' => 0];
        }
    }

    /**
     * Escalation waterfall
     */
    private function getEscalationWaterfall()
    {
        try {
            $escalations = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    escalation_level,
                    COUNT(DISTINCT ticket_id) as ticket_count
                ')
                ->whereNotNull('escalation_level')
                ->groupBy('escalation_level')
                ->orderBy('escalation_level')
                ->get();

            return $escalations->map(function ($row) {
                return [
                    'level' => $row->escalation_level ?? 'Not Escalated',
                    'ticketCount' => (int)$row->ticket_count,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load escalation waterfall: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aging by assignee/team
     */
    private function getAgingByAssignee()
    {
        try {
            $aging = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    assigned_to,
                    COUNT(DISTINCT ticket_id) as ticket_count,
                    ROUND(AVG(CAST(days_open AS DECIMAL)), 2) as avg_days_open,
                    MAX(CAST(days_open AS DECIMAL)) as max_days_open
                ')
                ->where('status', '!=', 'CLOSED')
                ->groupBy('assigned_to')
                ->orderByDesc('avg_days_open')
                ->limit(20)
                ->get();

            return $aging->map(function ($row) {
                return [
                    'assignee' => $row->assigned_to ?? 'Unassigned',
                    'ticketCount' => (int)$row->ticket_count,
                    'avgDaysOpen' => (float)$row->avg_days_open,
                    'maxDaysOpen' => (int)$row->max_days_open,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load aging by assignee: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Unresponded queue (no response in 24h)
     */
    private function getUnrespondedQueue()
    {
        try {
            $unresponded = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    ticket_id,
                    ticket_number,
                    customer_name,
                    created_at,
                    hours_since_creation,
                    priority
                ')
                ->whereRaw('hours_since_creation > 24')
                ->where('status', '!=', 'CLOSED')
                ->where('first_response_at', 'IS NULL')
                ->orderByDesc('hours_since_creation')
                ->limit(30)
                ->get();

            return $unresponded->map(function ($row) {
                return [
                    'ticketNumber' => $row->ticket_number,
                    'customer' => $row->customer_name,
                    'createdAt' => $row->created_at ? Carbon::parse($row->created_at)->format('M d, Y H:i') : 'N/A',
                    'hoursSinceCreation' => (int)$row->hours_since_creation,
                    'priority' => $row->priority ?? 'Normal',
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load unresponded queue: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Overall SLA metrics
     */
    private function getSlaMetrics()
    {
        try {
            $metrics = DB::connection($this->pgsqlConnection)
                ->table('reporting.ticket_sla_summary')
                ->selectRaw('
                    COUNT(DISTINCT ticket_id) as total_tickets,
                    COUNT(DISTINCT CASE WHEN status = "OPEN" THEN ticket_id END) as open_tickets,
                    COUNT(DISTINCT CASE WHEN status = "IN_PROGRESS" THEN ticket_id END) as in_progress_tickets,
                    COUNT(DISTINCT CASE WHEN status = "CLOSED" THEN ticket_id END) as closed_tickets,
                    ROUND(AVG(CAST(days_open AS DECIMAL)), 2) as avg_resolution_time
                ')
                ->first();

            return [
                'totalTickets' => (int)$metrics->total_tickets,
                'openTickets' => (int)$metrics->open_tickets,
                'inProgressTickets' => (int)$metrics->in_progress_tickets,
                'closedTickets' => (int)$metrics->closed_tickets,
                'avgResolutionTime' => (float)$metrics->avg_resolution_time,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load SLA metrics: ' . $e->getMessage());
            return [];
        }
    }
}
