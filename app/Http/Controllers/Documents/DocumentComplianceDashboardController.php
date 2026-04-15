<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DocumentComplianceDashboardController extends Controller
{
    protected $pgsqlConnection = 'pgsql_ai';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Document Compliance board
     */
    public function index()
    {
        $data = [
            'renewalWorkload' => $this->getRenewalWorkload(),
            'expiryHeatmap' => $this->getExpiryHeatmap(),
            'approvalCycleTime' => $this->getApprovalCycleTime(),
            'overdueOwnership' => $this->getOverdueOwnership(),
            'upcomingRenewals' => $this->getUpcomingRenewals(),
            'overdueDocuments' => $this->getOverdueDocuments(),
            'documentMetrics' => $this->getDocumentMetrics(),
        ];

        return view('documents.dashboards.compliance', $data);
    }

    /**
     * Renewal workload by month (12-month forecast)
     */
    private function getRenewalWorkload()
    {
        try {
            $workload = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    CONCAT(YEAR(next_renewal_date), "-", LPAD(MONTH(next_renewal_date), 2, "0")) as renewal_month,
                    COUNT(DISTINCT document_id) as documents_due,
                    COUNT(DISTINCT CASE WHEN approval_status != "APPROVED" THEN document_id END) as pending_approval
                ')
                ->where('next_renewal_date', '>=', Carbon::now())
                ->where('next_renewal_date', '<=', Carbon::now()->addMonths(12))
                ->groupBy('renewal_month')
                ->orderBy('renewal_month')
                ->get();

            return $workload->map(function ($row) {
                $month = Carbon::createFromFormat('Y-m', $row->renewal_month);
                return [
                    'month' => $month->format('M Y'),
                    'documentsDue' => (int)$row->documents_due,
                    'pendingApproval' => (int)$row->pending_approval,
                    'approvalRate' => $row->documents_due > 0 
                        ? round((($row->documents_due - $row->pending_approval) / $row->documents_due) * 100, 2)
                        : 0,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load renewal workload: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Expiry heatmap (document type vs days until expiry)
     */
    private function getExpiryHeatmap()
    {
        try {
            $heatmap = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    document_type,
                    CASE
                        WHEN days_until_expiry < 0 THEN "0_Overdue"
                        WHEN days_until_expiry BETWEEN 0 AND 30 THEN "1_0-30"
                        WHEN days_until_expiry BETWEEN 31 AND 60 THEN "2_31-60"
                        WHEN days_until_expiry BETWEEN 61 AND 90 THEN "3_61-90"
                        ELSE "4_90plus"
                    END as days_bucket,
                    COUNT(DISTINCT document_id) as document_count
                ')
                ->groupBy('document_type', 'days_bucket')
                ->orderBy('document_type')
                ->orderBy('days_bucket')
                ->get();

            return $heatmap->map(function ($row) {
                $bucketLabels = [
                    '0_Overdue' => 'Overdue',
                    '1_0-30' => '0-30 Days',
                    '2_31-60' => '31-60 Days',
                    '3_61-90' => '61-90 Days',
                    '4_90plus' => '90+ Days',
                ];

                return [
                    'documentType' => $row->document_type,
                    'daysBucket' => $bucketLabels[$row->days_bucket] ?? $row->days_bucket,
                    'documentCount' => (int)$row->document_count,
                    'intensity' => $row->days_bucket == '0_Overdue' ? 100 : (100 - ((int)substr($row->days_bucket, 0, 1) * 20)),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load expiry heatmap: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Approval cycle time analysis
     */
    private function getApprovalCycleTime()
    {
        try {
            $cycleTime = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    approval_status,
                    COUNT(DISTINCT document_id) as document_count,
                    ROUND(AVG(CAST(days_in_approval AS DECIMAL)), 2) as avg_cycle_days,
                    MAX(CAST(days_in_approval AS DECIMAL)) as max_cycle_days
                ')
                ->groupBy('approval_status')
                ->orderByDesc('avg_cycle_days')
                ->get();

            return $cycleTime->map(function ($row) {
                return [
                    'status' => $row->approval_status,
                    'documentCount' => (int)$row->document_count,
                    'avgCycleDays' => (float)$row->avg_cycle_days,
                    'maxCycleDays' => (int)$row->max_cycle_days,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load approval cycle time: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Overdue documents by owner
     */
    private function getOverdueOwnership()
    {
        try {
            $overdue = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    document_owner,
                    COUNT(DISTINCT document_id) as overdue_count,
                    MIN(CAST(days_until_expiry AS DECIMAL)) as earliest_expiry
                ')
                ->where('days_until_expiry', '<', 0)
                ->groupBy('document_owner')
                ->orderByRaw('COUNT(DISTINCT document_id) DESC')
                ->limit(20)
                ->get();

            return $overdue->map(function ($row) {
                return [
                    'owner' => $row->document_owner,
                    'overdueCount' => (int)$row->overdue_count,
                    'earliestExpiry' => (int)$row->earliest_expiry,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load overdue ownership: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Upcoming renewals (next 90 days)
     */
    private function getUpcomingRenewals()
    {
        try {
            $upcoming = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    document_id,
                    document_name,
                    document_type,
                    next_renewal_date,
                    approval_status,
                    document_owner,
                    DATEDIFF(next_renewal_date, NOW()) as days_until_renewal
                ')
                ->where('next_renewal_date', '>=', Carbon::now())
                ->where('next_renewal_date', '<=', Carbon::now()->addDays(90))
                ->orderBy('next_renewal_date')
                ->limit(30)
                ->get();

            return $upcoming->map(function ($row) {
                return [
                    'documentId' => $row->document_id,
                    'documentName' => $row->document_name,
                    'documentType' => $row->document_type,
                    'renewalDate' => Carbon::parse($row->next_renewal_date)->format('M d, Y'),
                    'daysUntilRenewal' => (int)$row->days_until_renewal,
                    'approvalStatus' => $row->approval_status,
                    'owner' => $row->document_owner,
                    'urgency' => $row->days_until_renewal <= 14 ? 'critical' : ($row->days_until_renewal <= 30 ? 'high' : 'medium'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load upcoming renewals: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Overdue documents
     */
    private function getOverdueDocuments()
    {
        try {
            $overdue = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    document_id,
                    document_name,
                    document_type,
                    next_renewal_date,
                    document_owner,
                    ABS(DATEDIFF(next_renewal_date, NOW())) as days_overdue
                ')
                ->where('next_renewal_date', '<', Carbon::now())
                ->orderByRaw('ABS(DATEDIFF(next_renewal_date, NOW())) DESC')
                ->limit(30)
                ->get();

            return $overdue->map(function ($row) {
                return [
                    'documentId' => $row->document_id,
                    'documentName' => $row->document_name,
                    'documentType' => $row->document_type,
                    'overdueDate' => Carbon::parse($row->next_renewal_date)->format('M d, Y'),
                    'daysOverdue' => (int)$row->days_overdue,
                    'owner' => $row->document_owner,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load overdue documents: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Overall document compliance metrics
     */
    private function getDocumentMetrics()
    {
        try {
            $metrics = DB::connection($this->pgsqlConnection)
                ->table('reporting.document_compliance')
                ->selectRaw('
                    COUNT(DISTINCT document_id) as total_documents,
                    COUNT(DISTINCT CASE WHEN days_until_expiry < 0 THEN document_id END) as overdue_documents,
                    COUNT(DISTINCT CASE WHEN days_until_expiry >= 0 AND days_until_expiry <= 30 THEN document_id END) as due_soon,
                    COUNT(DISTINCT CASE WHEN approval_status != "APPROVED" THEN document_id END) as pending_approval,
                    ROUND(AVG(CAST(days_in_approval AS DECIMAL)), 2) as avg_approval_time
                ')
                ->first();

            $totalDocs = $metrics->total_documents ?? 0;
            $overduePercentage = $totalDocs > 0 ? round(($metrics->overdue_documents / $totalDocs) * 100, 2) : 0;

            return [
                'totalDocuments' => (int)$totalDocs,
                'overdueDocuments' => (int)$metrics->overdue_documents,
                'overduePercentage' => (float)$overduePercentage,
                'dueSoon' => (int)$metrics->due_soon,
                'pendingApproval' => (int)$metrics->pending_approval,
                'avgApprovalTime' => (float)$metrics->avg_approval_time,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load document metrics: ' . $e->getMessage());
            return [];
        }
    }
}
