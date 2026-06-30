<?php

namespace App\Livewire\CRM;

use App\Event;
use App\SampleHeader;
use App\Models\CRM\Complaint;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback;
use App\Livewire\Crm\BaseCrmComponent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmDashboard extends BaseCrmComponent
{
    public $period = '12m';
    public $showAlertsModal = false;

    public function toggleAlertsModal()
    {
        $this->showAlertsModal = !$this->showAlertsModal;
    }

    public function mount()
    {
        $this->initialize();
        $this->checkPermission('CRM.permission');
        
        if (\App\Models\CRM\CrmDashboardWidget::count() === 0) {
            $this->seedDefaultWidgets();
        }
    }


    public function getKpiWidgetsProperty()
    {
        return \App\Models\CRM\CrmDashboardWidget::where('is_active', true)
            ->where('type', 'kpi_card')
            ->orderBy('position_order')
            ->get();
    }

    public function getChartWidgetsProperty()
    {
        return \App\Models\CRM\CrmDashboardWidget::where('is_active', true)
            ->where('type', '!=', 'kpi_card')
            ->orderBy('position_order')
            ->get();
    }

    private function seedDefaultWidgets()
    {
        $defaults = [
            // KPI Band
            ['title' => 'Total Client Accounts', 'type' => 'kpi_card', 'data_source' => 'total_customers', 'order' => 1, 'ui' => ['color' => 'blue', 'icon' => 'mdi-domain']],
            ['title' => 'Would Recommend', 'type' => 'kpi_card', 'data_source' => 'nps_score', 'order' => 2, 'ui' => ['color' => 'success', 'icon' => 'mdi-account-heart', 'unit' => '%']],
            ['title' => 'Total Received Samples', 'type' => 'kpi_card', 'data_source' => 'total_samples', 'order' => 3, 'ui' => ['color' => 'primary', 'icon' => 'mdi-flask-outline']],
            ['title' => 'Overall Satisfaction', 'type' => 'kpi_card', 'data_source' => 'overall_rating', 'order' => 4, 'ui' => ['color' => 'warning', 'icon' => 'mdi-star-face', 'unit' => ' / 5']],
            ['title' => 'Open Complaints', 'type' => 'kpi_card', 'data_source' => 'open_complaints', 'order' => 5, 'ui' => ['color' => 'danger', 'icon' => 'mdi-alert-circle-outline']],
            ['title' => 'All Feedbacks', 'type' => 'kpi_card', 'data_source' => 'total_feedbacks', 'order' => 6, 'ui' => ['color' => 'indigo', 'icon' => 'mdi-comment-multiple-outline']],

            // Analytics Grid
            ['title' => 'Sample Volume Trend', 'type' => 'line', 'data_source' => 'sample_volume_trend', 'order' => 7, 'ui' => ['grid_width' => 8]],
            ['title' => 'Complaints Priority Scan', 'type' => 'doughnut', 'data_source' => 'complaints_by_priority', 'order' => 8, 'ui' => ['grid_width' => 4]],
            ['title' => 'Overall Satisfaction vs Issues', 'type' => 'line_dual', 'data_source' => 'rating_vs_issues_trend', 'order' => 9, 'ui' => ['grid_width' => 7]],
            ['title' => 'Client Sentiment by Service', 'type' => 'bar_stacked', 'data_source' => 'feedback_sentiment_by_service', 'order' => 10, 'ui' => ['grid_width' => 5]],
            ['title' => 'Complaint Issue Types', 'type' => 'bar_stacked', 'data_source' => 'complaints_by_type_priority', 'order' => 11, 'ui' => ['grid_width' => 6]],
            ['title' => 'Frequent Issue Tally', 'type' => 'horizontalBar', 'data_source' => 'feedback_by_issues', 'order' => 12, 'ui' => ['grid_width' => 6]],
        ];

        foreach ($defaults as $w) {
            \App\Models\CRM\CrmDashboardWidget::create([
                'title' => $w['title'],
                'type' => $w['type'],
                'data_source' => $w['data_source'],
                'position_order' => $w['order'],
                'is_active' => true,
                'ui_config' => $w['ui'] ?? []
            ]);
        }
    }

    public function getWidgetPayload($dataSource)
    {
        return match($dataSource) {
            // Analytics
            'sample_volume_trend'           => $this->getSampleVolumeTrendProperty(),
            'complaints_by_priority'        => $this->getComplaintsByPriorityProperty(),
            'rating_vs_issues_trend'        => $this->getRatingVsIssuesTrendProperty(),
            'feedback_sentiment_by_service' => $this->getFeedbackSentimentByServiceProperty(),
            'complaints_by_type_priority'   => $this->getComplaintsByTypeAndPriorityProperty(),
            'feedback_by_issues'            => $this->getFeedbackByIssuesProperty(),
            'top_customers'                 => $this->getTopCustomersProperty(),
            'least_interactive_customers'   => $this->getLeastInteractiveCustomersProperty(),
            // KPIs
            'total_customers'               => ['value' => $this->getTotalCustomersProperty(), 'sub' => 'Total active laboratory clients'],
            'total_samples'                 => ['value' => $this->getYtdSamplesProperty(), 'sub' => 'Samples received year to date'],
            'total_feedbacks'               => ['value' => $this->getTotalFeedbackProperty(), 'sub' => 'Forms successfully returned'],
            'open_complaints'               => ['value' => $this->getOpenComplaintsProperty(), 'sub' => 'Requires immediate action'],
            'nps_score'                     => ['value' => $this->getOverallNpsProperty(), 'sub' => 'Clients expressing positive loyalty'],
            'overall_rating'                => ['value' => $this->getOverallCustomerRatingProperty(), 'sub' => 'Average satisfaction score from clients'],
            default => []
        };
    }

    public function getTopCustomersProperty()
    {
        $customers = \App\Models\CRM\CRMCustomer::orderBy('interaction_score', 'desc')->take(10)->get();
        $results = [];
        $tiers = ['Platinum (Rank 1)', 'Gold (Rank 2)', 'Silver (Rank 3)', 'Elite (Rank 4)'];
        foreach ($customers as $index => $c) {
            $tier = $tiers[$index] ?? 'Rank ' . ($index + 1);
            $results[$c->name] = $tier;
        }
        return $results;
    }

    public function getLeastInteractiveCustomersProperty()
    {
        $customers = \App\Models\CRM\CRMCustomer::orderBy('interaction_score', 'asc')->take(10)->get();
        $results = [];
        $tiers = ['Critical Watch (Rank 1)', 'High Risk (Rank 2)', 'At Risk (Rank 3)'];
        foreach ($customers as $index => $c) {
            $tier = $tiers[$index] ?? 'Rank ' . ($index + 1);
            $results[$c->name] = $tier;
        }
        return $results;
    }

    // ── Core Complaint KPIs ───────────────────────────────────────────────

    public function getTotalComplaintsProperty()
    {
        return getAllComplaints();
    }

    public function getOverallNpsProperty()
    {
        return $this->getFeedbackNpsPercentProperty();
    }

    public function getYtdSamplesProperty()
    {
        return SampleHeader::whereYear('created_at', \Carbon\Carbon::now()->year)->count();
    }

    public function getTotalCustomersProperty()
    {
        return CRMCustomer::count();
    }

    public function getTotalFeedbackProperty()
    {
        return CustomerFeedback::where('is_submitted', true)
            ->where('submitted_at', '>=', $this->getPeriodStartDate())
            ->count();
    }

    /** Feedback forms sent to clients but not yet completed and returned. */
    public function getPendingFeedbacksProperty()
    {
        return CustomerFeedback::where(function ($q) {
                $q->where('is_submitted', false)
                  ->orWhereNull('is_submitted');
            })
            ->count();
    }

    public function getOpenComplaintsProperty()
    {
        return Complaint::whereNotIn('complaint_workflow', [5, 6])->count();
    }

    public function getComplaintsNeedingApprovalProperty()
    {
        return getComplaintsInWorkflow(2);
    }

    public function getResolutionsPendingApprovalProperty()
    {
        return getComplaintsInWorkflow(4);
    }

    public function getWorkflowCountsProperty()
    {
        $workflow = getComplaintWorkflow();
        $counts = [];
        foreach ($workflow as $id => $name) {
            if ($id > 0) {
                $counts[$name] = getComplaintsInWorkflow($id);
            }
        }
        return $counts;
    }

    public function getTotalContactsProperty()
    {
        return CustomerContact::count();
    }

    public function getHighPriorityComplaintsProperty()
    {
        return Complaint::where('priority', 'High')->count();
    }

    public function getHighPriorityOpenComplaintsProperty()
    {
        return Complaint::where('priority', 'High')
            ->whereNotIn('complaint_workflow', [5, 6])
            ->count();
    }

    // ── Sample / Lab Pipeline KPIs ────────────────────────────────────────

    public function getSamplesInPipelineProperty()
    {
        $q = SampleHeader::where('status', '!=', 'Completed');
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        return $q->count();
    }

    public function getSamplesRequestReviewCountProperty()
    {
        $q = SampleHeader::where('status', 'Samples Request Review');
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        return $q->count();
    }

    public function getSamplesByWorkflowProperty()
    {
        $q = SampleHeader::select('status')->selectRaw('count(*) as count')
            ->where('status', '!=', 'Completed')
            ->groupBy('status');
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        $rows = $q->get();
        $order = getSampleWorflowStages();
        $counts = [];
        foreach ($order as $stage) {
            if ($stage === 'All Samples') continue;
            $r = $rows->firstWhere('status', $stage);
            $counts[$stage] = $r ? (int) $r->count : 0;
        }
        return $counts;
    }

    public function getSamplesByCustomerProperty()
    {
        $q = SampleHeader::select('crm_customer_id')->selectRaw('count(*) as count')
            ->where('status', '!=', 'Completed')
            ->groupBy('crm_customer_id')
            ->orderByDesc('count')
            ->limit(8);
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        $rows = $q->get();
        $out = [];
        foreach ($rows as $row) {
            $name = CRMCustomer::find($row->crm_customer_id)?->name ?? 'Customer #' . $row->crm_customer_id;
            $out[$name] = $row->count;
        }
        return $out;
    }

    public function getLabBookingsByStatusProperty()
    {
        if (!Schema::hasColumn('calendar_events', 'is_lab_booking')) {
            return ['Upcoming' => 0, 'Complete' => 0, 'Delayed' => 0, 'Cancelled' => 0];
        }
        $rows = Event::where('is_lab_booking', 1)
            ->whereNotNull('client_id')
            ->select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        $order = ['Upcoming' => 0, 'Complete' => 0, 'Delayed' => 0, 'Cancelled' => 0];
        return array_merge($order, $rows);
    }

    // ── Monthly Sample Volume Trend (last N months) ───────────────────────

    public function getSamplesTrendProperty()
    {
        $months = match($this->period) {
            '1m' => 3, '3m' => 6, '6m' => 9, '12m' => 12, default => 12
        };
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $data = SampleHeader::selectRaw('YEAR(date_collected) as y, MONTH(date_collected) as m')
            ->selectRaw('count(*) as c')
            ->whereNotNull('date_collected')
            ->where('date_collected', '>=', $start)
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get();
        $labels = [];
        $values = [];
        foreach ($data as $row) {
            $labels[] = Carbon::createFromDate($row->y, $row->m, 1)->format('M Y');
            $values[] = $row->c;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    // ── SLA Breach Metrics ────────────────────────────────────────────────

    private function getComplaintSlaDays()
    {
        $rule = \App\Models\CRM\CrmAlertRule::where('condition_type', 'complaint_sla_overdue_days')
            ->where('is_active', true)
            ->first();
        return $rule && is_numeric($rule->threshold_value) ? (int)$rule->threshold_value : 14;
    }

    /** Complaints older than SLA days and still open (SLA breach). */
    public function getOverdueComplaintsProperty()
    {
        $slaDays = $this->getComplaintSlaDays();
        return Complaint::whereNotIn('complaint_workflow', [5, 6])
            ->whereDate('date', '<', Carbon::now()->subDays($slaDays)->toDateString())
            ->count();
    }

    /** SLA compliance percentage: % of complaints resolved within SLA days. */
    public function getSlaComplianceRateProperty(): int
    {
        $closed = Complaint::whereIn('complaint_workflow', [5])->count();
        if ($closed === 0) return 100;

        $slaDays = $this->getComplaintSlaDays();
        $closedOnTime = Complaint::whereIn('complaint_workflow', [5])
            ->whereNotNull('date_closed')
            ->whereNotNull('date')
            ->whereRaw('DATEDIFF(date_closed, date) <= ?', [$slaDays])
            ->count();

        return (int) round(($closedOnTime / $closed) * 100);
    }

    public function getActiveAlertsCountProperty()
    {
        return count($this->activeAlerts);
    }

    public function getActiveAlertsProperty()
    {
        $rules = \App\Models\CRM\CrmAlertRule::where('is_active', true)->get();
        $alerts = [];

        foreach ($rules as $rule) {
            $threshold = (int) $rule->threshold_value;
            $count = 0;

            if ($rule->condition_type === 'complaint_sla_overdue_days') {
                $count = Complaint::whereNotIn('complaint_workflow', [5, 6])
                    ->whereDate('date', '<', Carbon::now()->subDays($threshold)->toDateString())
                    ->count();
                
                if ($count > 0) {
                    $alerts[] = [
                        'rule_id' => $rule->id,
                        'title' => $rule->rule_name,
                        'count' => $count,
                        'label' => 'Complaints Overdue',
                        'severity' => 'danger',
                        'icon' => 'mdi-alert-box-outline',
                        'link' => route('complaint-workflow', ['stage' => 1]), // Open Stage
                    ];
                }
            }
            
            if ($rule->condition_type === 'feedback_sla_overdue_days') {
                $count = CustomerFeedback::where('is_submitted', false)
                    ->where('created_at', '<', Carbon::now()->subDays($threshold))
                    ->count();

                if ($count > 0) {
                    $alerts[] = [
                        'rule_id' => $rule->id,
                        'title' => $rule->rule_name,
                        'count' => $count,
                        'label' => 'Pending Feedbacks',
                        'severity' => 'warning',
                        'icon' => 'mdi-comment-clock-outline',
                        'link' => '#', 
                    ];
                }
            }

            if ($rule->condition_type === 'nps_critical_threshold') {
                $nps = $this->overallNps;
                if ($nps < $threshold) {
                    $alerts[] = [
                        'rule_id' => $rule->id,
                        'title' => $rule->rule_name,
                        'count' => $nps . '%',
                        'label' => 'Critically Low NPS',
                        'severity' => 'danger',
                        'icon' => 'mdi-trending-down',
                        'link' => '#',
                    ];
                }
            }
        }

        return $alerts;
    }

    // ── Client Retention / Repeat Usage ──────────────────────────────────

    /** % of customers who submitted samples in the last 90 days (active). */
    public function getActiveClientRateProperty(): int
    {
        $total = CRMCustomer::count();
        if ($total === 0) return 0;

        $customerIds = CRMCustomer::pluck('id')->toArray();

        $recentCount = SampleHeader::select('crm_customer_id')
            ->distinct()
            ->whereNotNull('crm_customer_id')
            ->whereIn('crm_customer_id', $customerIds)
            ->where('date_collected', '>=', Carbon::now()->subDays(90))
            ->count();

        return (int) min(100, round(($recentCount / $total) * 100));
    }

    // ── Feedback / NPS KPIs ───────────────────────────────────────────────

    public function getFeedbackStatsProperty()
    {
        $query = CustomerFeedback::where('is_submitted', true)
            ->where('submitted_at', '>=', $this->getPeriodStartDate());
        return CustomerFeedback::getStats($query);
    }

    public function getFeedbackNpsPercentProperty()
    {
        return $this->feedbackStats['pulse']['nps_percent'] ?? 0;
    }

    public function getOverallCustomerRatingProperty()
    {
        $avg = CustomerFeedback::where('is_submitted', true)
            ->where('submitted_at', '>=', $this->getPeriodStartDate())
            ->whereNotNull('rating_overall')
            ->avg('rating_overall');
        return $avg ? round($avg, 1) : 0;
    }

    public function getFeedbackIsoRiskCountProperty()
    {
        return $this->feedbackStats['pulse']['risk_count'] ?? 0;
    }

    // ── Rating Breakdown by Dimension ─────────────────────────────────────

    /** Average per rating dimension (Turnaround, Communication, etc.) */
    public function getRatingByDimensionProperty(): array
    {
        $rows = DB::table('crm_feedback_ratings')
            ->join('crm_evaluation_metrics', 'crm_evaluation_metrics.id', '=', 'crm_feedback_ratings.evaluation_metric_id')
            ->join('customerfeedbacks', 'customerfeedbacks.id', '=', 'crm_feedback_ratings.customer_feedback_id')
            ->where('customerfeedbacks.is_submitted', true)
            ->where('customerfeedbacks.submitted_at', '>=', $this->getPeriodStartDate())
            ->select('crm_evaluation_metrics.name as metric', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(*) as total'))
            ->groupBy('crm_evaluation_metrics.id', 'crm_evaluation_metrics.name')
            ->orderBy('crm_evaluation_metrics.display_order')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->metric] = ['avg' => round((float) $r->avg_rating, 1), 'count' => $r->total];
        }
        return $out;
    }

    // ── Client Watchlist / Top Accounts Table ─────────────────────────────



    // ── Chart Data Sources ────────────────────────────────────────────────

    public function getComplaintsByTypeProperty()
    {
        return Complaint::select('type')
            ->selectRaw('count(*) as count')
            ->where('date', '>=', $this->getPeriodStartDate())
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($row) {
                return [($row->type ?: 'Unspecified') => $row->count];
            })
            ->toArray();
    }

    public function getComplaintsByPriorityProperty()
    {
        $counts = Complaint::select('priority')
            ->selectRaw('count(*) as count')
            ->where('date', '>=', $this->getPeriodStartDate())
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
        $ordered = [];
        foreach (\App\Constants\CRM\CrmConstants::getPriorities() as $p) {
            $ordered[$p] = $counts[$p] ?? 0;
        }
        return $ordered;
    }

    public function getComplaintsByTypeAndPriorityProperty()
    {
        return Complaint::select('type', 'priority')
            ->selectRaw('count(*) as count')
            ->where('date', '>=', $this->getPeriodStartDate())
            ->groupBy(['type', 'priority'])
            ->get()
            ->groupBy('type')
            ->map(function ($group) {
                $priorityCounts = [];
                foreach (\App\Constants\CRM\CrmConstants::getPriorities() as $priority) {
                    $priorityCounts[$priority] = $group->where('priority', $priority)->sum('count') ?? 0;
                }
                return $priorityCounts;
            })
            ->toArray();
    }

    public function getFeedbackSentimentByServiceProperty()
    {
        $rows = CustomerFeedback::query()
            ->where('is_submitted', true)
            ->where('submitted_at', '>=', $this->getPeriodStartDate())
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->select('service_type', DB::raw("
                SUM(CASE WHEN will_recommend = 1 OR will_recommend = '1' THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN will_recommend = 0 OR will_recommend = '0' THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN will_recommend IS NULL OR (will_recommend != 1 AND will_recommend != '1' AND will_recommend != 0 AND will_recommend != '0') THEN 1 ELSE 0 END) as neutral,
                COUNT(*) as total
            "))
            ->groupBy('service_type')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->service_type] = [
                'positive' => (int) $row->positive,
                'neutral'  => (int) $row->neutral,
                'negative' => (int) $row->negative,
                'total'    => (int) $row->total,
            ];
        }
        return $out;
    }

    public function getFeedbackByIssuesProperty()
    {
        return $this->complaintsByType;
    }

    public function getFeedbackByServiceTypeProperty()
    {
        return $this->feedbackStats['service_types'] ?? [];
    }

    /**
     * Rating vs Issues Trend (dual-axis chart).
     * Y1 = avg overall rating, Y2 = complaint count per month.
     */
    public function getRatingVsIssuesTrendProperty()
    {
        $months = match($this->period) {
            '1m' => 1, '3m' => 3, '6m' => 6, '12m' => 12, default => 12
        };

        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $labels = [];
        $ratings = [];
        $issues = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M Y');

            $ratingAvg = CustomerFeedback::where('is_submitted', true)
                ->whereNotNull('rating_overall')
                ->whereYear('submitted_at', $month->year)
                ->whereMonth('submitted_at', $month->month)
                ->avg('rating_overall');
            $ratings[] = $ratingAvg ? round($ratingAvg, 1) : 0;

            $issues[] = Complaint::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();
        }

        return ['labels' => $labels, 'ratings' => $ratings, 'issues' => $issues];
    }

    /**
     * Sample volume trend per month for the selected period.
     * Used by the "Sample Volume Trend" area chart.
     */
    public function getSampleVolumeTrendProperty(): array
    {
        $months = match($this->period) {
            '1m' => 3, '3m' => 6, '6m' => 9, '12m' => 12, default => 12
        };
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $labels = [];
        $values = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M');
            $count = SampleHeader::whereNotNull('date_collected')
                ->whereYear('date_collected', $month->year)
                ->whereMonth('date_collected', $month->month)
                ->count();
            $values[] = $count;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ── Activity Timeline ─────────────────────────────────────────────────

    public function getIntelligentTimelineProperty()
    {
        $types = [
            Complaint::class, CRMCustomer::class, CustomerFeedback::class,
            SampleHeader::class, Event::class,
        ];
        $audits = \OwenIt\Auditing\Models\Audit::whereIn('auditable_type', $types)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        $modelLabels = [
            'Complaint'        => 'complaint',
            'CustomerFeedback' => 'feedback submission',
            'CRMCustomer'      => 'client account',
            'SampleHeader'     => 'sample batch',
            'Event'            => 'lab booking',
        ];

        $groups = [];
        foreach ($audits as $audit) {
            $userName   = $audit->user->name ?? 'System';
            $modelBase  = class_basename($audit->auditable_type);
            $hourBucket = $audit->created_at?->format('Y-m-d H') ?? 'unknown';
            $key        = "$userName|$modelBase|{$audit->event}|$hourBucket";

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'user'       => $userName,
                    'initial'    => strtoupper(substr($userName, 0, 1)),
                    'model'      => $modelLabels[$modelBase] ?? $modelBase,
                    'event'      => $audit->event,
                    'count'      => 0,
                    'created_at' => $audit->created_at,
                    'type'       => 'audit',
                ];
            }
            $groups[$key]['count']++;
        }

        $timeline = array_values($groups);

        // Fetch unread alerts natively from the database schema
        $dbAlerts = \App\Models\CRM\CrmAlert::where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($alert) {
                return [
                    'type'       => $alert->type === 'system' ? 'system_alert' : 'crm_alert',
                    'level'      => $alert->level ?? 'info',
                    'icon'       => $alert->icon ?? 'mdi-alert-circle-outline',
                    'message'    => $alert->message,
                    'created_at' => $alert->created_at,
                ];
            })
            ->toArray();

        return array_merge($dbAlerts, $timeline);
    }

    public function getRecentActivityProperty()
    {
        $types = [
            Complaint::class, CRMCustomer::class, CustomerFeedback::class,
            SampleHeader::class, Event::class,
        ];
        return \OwenIt\Auditing\Models\Audit::whereIn('auditable_type', $types)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────

    public function updatedPeriod()
    {
        $this->dispatch('period-updated');
    }

    private function getPeriodStartDate()
    {
        $months = match($this->period) {
            '1m'  => 1,
            '3m'  => 3,
            '6m'  => 6,
            '12m' => 12,
            default => 12,
        };
        return Carbon::now()->subMonths($months - 1)->startOfMonth();
    }

    public function getChartUiConfigProperty()
    {
        // Replace with DB query logic for settings when implemented
        return [
            'priorities' => \App\Constants\CRM\CrmConstants::getPriorities(),
            'priority_colors' => [
                'rgba(239,68,68,.85)',  // High
                'rgba(245,158,11,.85)', // Medium
                'rgba(34,197,94,.85)'   // Low
            ],
            'sentiment' => [
                'positive' => 'rgba(56,161,105,.75)',
                'neutral'  => 'rgba(160,174,192,.55)',
                'negative' => 'rgba(229,62,62,.7)'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.crm.crm-dashboard')
            ->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => false])
            ->section('content2');
    }
}