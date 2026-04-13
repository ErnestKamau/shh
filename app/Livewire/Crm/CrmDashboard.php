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
use Illuminate\Support\Facades\Schema;

class CrmDashboard extends BaseCrmComponent
{
    public function mount()
    {
        $this->initialize();
        $this->checkPermission('CRM.permission');
    }

    public function getTotalComplaintsProperty()
    {
        return getAllComplaints();
    }

    public function getTotalCustomersProperty()
    {
        return CRMCustomer::count();
    }

    public function getTotalFeedbackProperty()
    {
        return CustomerFeedback::count();
    }

    /**
     * Open incidents: all complaints not yet Closed (5) or Cancelled (6) — i.e. awaiting resolution.
     */
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

    /** Samples not yet Completed (in pipeline). Uses isactive when column exists. */
    public function getSamplesInPipelineProperty()
    {
        $q = SampleHeader::where('status', '!=', 'Completed');
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        return $q->count();
    }

    /** Lab bookings (customer-linked) that are Upcoming and start on or after today. */
    public function getLabBookingsUpcomingProperty()
    {
        if (!Schema::hasColumn('calendar_events', 'is_lab_booking')) {
            return 0;
        }
        return Event::where('is_lab_booking', 1)
            ->whereNotNull('client_id')
            ->where('status', 'Upcoming')
            ->where('start_date', '>=', Carbon::today()->toDateString())
            ->count();
    }

    /** Lab bookings awaiting approval (lab_booking_status = 0). */
    public function getLabBookingsAwaitingApprovalProperty()
    {
        if (!Schema::hasColumn('calendar_events', 'lab_booking_status') || !Schema::hasColumn('calendar_events', 'is_lab_booking')) {
            return 0;
        }
        return Event::where('is_lab_booking', 1)->where('lab_booking_status', 0)->count();
    }

    /** Samples Request Review count for Action Queue. */
    public function getSamplesRequestReviewCountProperty()
    {
        $q = SampleHeader::where('status', 'Samples Request Review');
        if (Schema::hasColumn('sample_headers', 'isactive')) {
            $q->where('isactive', 1);
        }
        return $q->count();
    }

    /** Sample counts by workflow status (for chart). Excludes Completed. */
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
            if ($stage === 'All Samples') {
                continue;
            }
            $r = $rows->firstWhere('status', $stage);
            $counts[$stage] = $r ? (int) $r->count : 0;
        }
        return $counts;
    }

    /** Top N customers by sample count (in pipeline). */
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
            $name = CRMCustomer::find($row->crm_customer_id)->name ?? 'Customer #' . $row->crm_customer_id;
            $out[$name] = $row->count;
        }
        return $out;
    }

    /** Lab bookings count by status (for chart). */
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

    /** Monthly sample count trend (last 6 months) by date_collected. */
    public function getSamplesTrendProperty()
    {
        $start = Carbon::now()->subMonths(6)->startOfMonth();
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

    public function getFeedbackStatsProperty()
    {
        return CustomerFeedback::getStats();
    }

    public function getFeedbackNpsPercentProperty()
    {
        return $this->feedbackStats['pulse']['nps_percent'] ?? 0;
    }

    public function getFeedbackIsoRiskCountProperty()
    {
        return $this->feedbackStats['pulse']['risk_count'] ?? 0;
    }

    public function getComplaintsByTypeProperty()
    {
        return Complaint::select('type')
            ->selectRaw('count(*) as count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($row) {
                $label = $row->type ?: 'Unspecified';
                return [$label => $row->count];
            })
            ->toArray();
    }

    public function getComplaintsByPriorityProperty()
    {
        $counts = Complaint::select('priority')
            ->selectRaw('count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
        $ordered = [];
        foreach (\App\Constants\CRM\CrmConstants::getPriorities() as $p) {
            $ordered[$p] = $counts[$p] ?? 0;
        }
        return $ordered;
    }

    public function getFeedbackByServiceTypeProperty()
    {
        return $this->feedbackStats['service_types'] ?? [];
    }

    public function getComplaintsTrendProperty()
    {
        $data = Complaint::selectRaw('YEAR(date) as y, MONTH(date) as m')
            ->selectRaw('count(*) as c')
            ->whereNotNull('date')
            ->where('date', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get();
        $labels = [];
        $values = [];
        foreach ($data as $row) {
            $labels[] = date('M Y', mktime(0, 0, 0, $row->m, 1, $row->y));
            $values[] = $row->c;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    /** Complaint count delta: this month vs last month. */
    public function getComplaintsTrendDeltaProperty()
    {
        $thisMonth = Complaint::whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->count();
        $lastMonth = Complaint::whereYear('date', Carbon::now()->subMonth()->year)
            ->whereMonth('date', Carbon::now()->subMonth()->month)
            ->count();
        return ['current' => $thisMonth, 'delta' => $thisMonth - $lastMonth];
    }

    /** Open complaints older than 14 days — overdue SLA. */
    public function getOverdueComplaintsProperty()
    {
        return Complaint::whereNotIn('complaint_workflow', [5, 6])
            ->whereNotNull('date')
            ->where('date', '<', Carbon::now()->subDays(14)->toDateString())
            ->count();
    }

    /** High priority open complaints. */
    public function getHighPriorityOpenComplaintsProperty()
    {
        return Complaint::whereNotIn('complaint_workflow', [5, 6])
            ->where('priority', 'High')
            ->count();
    }

    /**
     * Feedback sentiment (Positive / Neutral / Negative) grouped by service type.
     * Positive  = will_recommend = 'Yes'
     * Negative  = will_recommend = 'No'
     * Neutral   = everything else (null / unanswered).
     */
    public function getFeedbackSentimentByServiceProperty()
    {
        $rows = CustomerFeedback::query()
            ->where('is_submitted', true)
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->select('service_type', \Illuminate\Support\Facades\DB::raw(
                "SUM(CASE WHEN will_recommend = 'Yes' THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN will_recommend = 'No' THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN will_recommend IS NULL OR (will_recommend != 'Yes' AND will_recommend != 'No') THEN 1 ELSE 0 END) as neutral,
                COUNT(*) as total"
            ))
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

    /** Dual-axis trend: complaints vs sample volume over the last 6 months. */
    public function getComplaintsVsSamplesTrendProperty()
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();
        $labels = [];
        $complaints = [];
        $samples = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $label = $month->format('M Y');
            $labels[] = $label;

            $complaints[] = Complaint::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();

            $q = SampleHeader::whereYear('date_collected', $month->year)
                ->whereMonth('date_collected', $month->month);
            if (\Illuminate\Support\Facades\Schema::hasColumn('sample_headers', 'isactive')) {
                $q->where('isactive', 1);
            }
            $samples[] = $q->count();
        }

        return ['labels' => $labels, 'complaints' => $complaints, 'samples' => $samples];
    }

    /**
     * Intelligent timeline: groups audit rows by user+model-type within 1 hour,
     * then injects system alerts for NPS drop and overdue complaints.
     */
    public function getIntelligentTimelineProperty()
    {
        $types = [
            Complaint::class,
            CRMCustomer::class,
            CustomerFeedback::class,
            SampleHeader::class,
            Event::class,
        ];
        $audits = \OwenIt\Auditing\Models\Audit::whereIn('auditable_type', $types)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        // Human-readable model labels
        $modelLabels = [
            'Complaint'        => 'complaint',
            'CustomerFeedback' => 'feedback submission',
            'CRMCustomer'      => 'client account',
            'SampleHeader'     => 'sample batch',
            'Event'            => 'lab booking',
        ];

        // Group by user + model_type + hour bucket
        $groups = [];
        foreach ($audits as $audit) {
            $userName = $audit->user->name ?? 'System';
            $modelBase = class_basename($audit->auditable_type);
            $hourBucket = $audit->created_at?->format('Y-m-d H') ?? 'unknown';
            $key = "$userName|$modelBase|{$audit->event}|$hourBucket";

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

        // Inject system alerts at the top
        $alerts = [];
        if ($this->overdueComplaints > 0) {
            $alerts[] = [
                'type'    => 'system_alert',
                'level'   => 'danger',
                'icon'    => 'mdi-timer-alert-outline',
                'message' => "{$this->overdueComplaints} open complaint" . ($this->overdueComplaints > 1 ? 's have' : ' has') . " exceeded the 14-day SLA. Review immediately.",
                'created_at' => Carbon::now(),
            ];
        }
        if ($this->feedbackNpsPercent < 70 && $this->feedbackNpsPercent > 0) {
            $alerts[] = [
                'type'    => 'system_alert',
                'level'   => 'warning',
                'icon'    => 'mdi-account-heart-outline',
                'message' => "Client Loyalty Index is at {$this->feedbackNpsPercent}% — below the 70% target. Review recent feedback for service gaps.",
                'created_at' => Carbon::now(),
            ];
        }

        return array_merge($alerts, $timeline);
    }

    public function getRecentActivityProperty()
    {
        $types = [
            Complaint::class,
            CRMCustomer::class,
            CustomerFeedback::class,
            SampleHeader::class,
            Event::class,
        ];
        return \OwenIt\Auditing\Models\Audit::whereIn('auditable_type', $types)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.crm.crm-dashboard')
            ->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => false])
            ->section('content2');
    }
}
