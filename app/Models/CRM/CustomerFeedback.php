<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerFeedback extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	protected $table = 'customerfeedbacks';


    protected $fillable = [
        'customer_id',
        'contact_id',
        'contact_position',
        'contact_phone',
        'usage_duration',
        'doc_ref',
        'doc_version',
        'code',
        'service_type',
        'service_type_other',
        'service_reference_no',
        'equipment_sample_id',
        'results_issued_date',
        'rating_communication',
        'rating_turnaround',
        'rating_technical',
        'rating_accuracy',
        'rating_reports',
        'rating_professionalism',
        'rating_handling',
        'rating_overall',
        'iso_impartiality',
        'iso_confidentiality',
        'iso_concerns_description',
        'has_issues',
        'issue_description',
        'specific_feedback',
        'reported_previously',
        'suggestions',
        'will_use_again',
        'will_recommend',
        'consent_contact',
        'preferred_contact_method',
        'status',
        'is_submitted',
        'received_from', // Explicitly allow setting this
        'registered_by', // Explicitly allow setting this
        'delivery_status', // Added for tracking email delivery
        'last_reminded_at',
        'submitted_at',
        'feedback',
        'user_type',
        'date',
    ];

    const STATUS_SUBMITTED = 0;
    const STATUS_PENDING = 1;

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the feedback request that triggered this feedback (if any).
     */
    public function feedbackRequest()
    {
        return $this->hasOne(\App\Models\CRM\FeedbackRequest::class, 'feedback_id');
    }

    /**
     * Get the contact that submitted this feedback.
     */
    public function contact()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerContact::class, 'contact_id');
    }

    /**
     * Get the customer associated with this feedback.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\CRM\CRMCustomer::class, 'customer_id');
    }

    /**
     * Get the dynamic ratings associated with this feedback.
     */
    public function ratings()
    {
        return $this->hasMany(\App\Models\CRM\FeedbackRating::class, 'customer_feedback_id');
    }

    /**
     * Get the submitter name with intelligent fallback.
     * Tries: 1) contact via contact_id, 2) contact via email, 3) registered_by, 4) 'Guest'
     */
    public function getSubmitterNameAttribute()
    {
        // 1. Try contact via contact_id (primary relationship)
        if ($this->contact && $this->contact->name) {
            return $this->contact->name;
        }
        
        // 2. Try contact via email lookup (fallback for records without contact_id)
        if ($this->registered_by) {
            $contactByEmail = \App\Models\CRM\CustomerContact::where('email', $this->registered_by)->first();
            if ($contactByEmail) {
                return $contactByEmail->name;
            }
            
            // 3. Use registered_by text field as-is (legacy or non-email values)
            return $this->registered_by;
        }
        
        // 4. Last resort
        return 'Guest';
    }

    /**
     * Set the submitted_at timestamp.
     */
    public function markAsSubmitted()
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Calculate feedback statistics for the Insights Dashboard using DB Aggregation.
     * 
     * @param \Illuminate\Database\Eloquent\Builder|null $query
     * @return array
     */
    public static function getStats($query = null)
    {
        $baseQuery = $query ? $query->clone() : self::query();
        $baseQuery->whereHas('ratings', function ($q) {}); // exclude legacy/pending defaults

        $serviceTypes = $baseQuery->clone()
            ->select('service_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('service_type')
            ->pluck('count', 'service_type')
            ->toArray();

        $pulseResult = $baseQuery->clone()->select([
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_count'),
            \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN will_recommend = 'Yes' THEN 1 ELSE 0 END) as nps_yes_count"),
            \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN iso_impartiality = 'No' OR iso_confidentiality = 'No' THEN 1 ELSE 0 END) as risk_count"),
        ])->first();

        // Dynamically aggregate from the pivot table based on the filtered query
        $ratingsAgg = \Illuminate\Support\Facades\DB::table('crm_feedback_ratings')
            ->joinSub($baseQuery->clone()->select('id'), 'filtered_feedbacks', function($join) {
                $join->on('crm_feedback_ratings.customer_feedback_id', '=', 'filtered_feedbacks.id');
            })
            ->join('crm_evaluation_metrics', 'crm_evaluation_metrics.id', '=', 'crm_feedback_ratings.evaluation_metric_id')
            ->select(
                'crm_evaluation_metrics.name as metric_name',
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as count_4"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as count_3"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as count_2"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as count_1"),
                \Illuminate\Support\Facades\DB::raw("COUNT(rating) as total_ratings")
            )
            ->groupBy('crm_evaluation_metrics.id', 'crm_evaluation_metrics.name')
            ->get();

        $stats = [
            'pulse' => [
                'total' => $pulseResult->total_count ?? 0,
                'nps_percent' => 0,
                'nps_yes' => $pulseResult->nps_yes_count ?? 0,
                'risk_count' => $pulseResult->risk_count ?? 0,
            ],
            'ratings' => [],
            'service_types' => $serviceTypes,
        ];

        if ($stats['pulse']['total'] > 0) {
            $stats['pulse']['nps_percent'] = round(($stats['pulse']['nps_yes'] / $stats['pulse']['total']) * 100);
        }

        foreach ($ratingsAgg as $row) {
            $key = \Illuminate\Support\Str::slug($row->metric_name);
            $stats['ratings'][$key] = [
                'label'     => $row->metric_name,
                'excellent' => $row->count_4,
                'good'      => $row->count_3,
                'fair'      => $row->count_2,
                'poor'      => $row->count_1,
                'total'     => $row->total_ratings,
            ];
        }

        return $stats;
    }

    /**
     * Get ISO compliance chart data (Compliant vs At Risk counts).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array{date_preset?: string, date_from?: string, date_to?: string} $options
     * @return array{compliant: int, at_risk: int}
     */
    public static function getIsoComplianceChartData($query, array $options = [])
    {
        $preset = $options['date_preset'] ?? 'all_time';
        $dateFrom = $options['date_from'] ?? null;
        $dateTo = $options['date_to'] ?? null;

        list($currentStart, $currentEnd, , ) = self::resolveDateRange($preset, $dateFrom, $dateTo);

        $chartQuery = $query->clone();
        if ($preset !== 'all_time' && $currentStart && $currentEnd) {
            $chartQuery->whereBetween('created_at', [$currentStart, $currentEnd]);
        }

        $total = $chartQuery->clone()->count();
        $atRisk = $chartQuery->clone()
            ->where(function($q) {
                $q->where('iso_impartiality', 'No')->orWhere('iso_confidentiality', 'No');
            })
            ->count();
        $compliant = $total - $atRisk;

        return ['compliant' => max(0, $compliant), 'at_risk' => $atRisk];
    }

    /**
     * Resolve date range from preset or custom dates.
     *
     * @param string|null $preset
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array{0: \Carbon\Carbon|null, 1: \Carbon\Carbon|null, 2: \Carbon\Carbon|null, 3: \Carbon\Carbon|null} [currentStart, currentEnd, prevStart, prevEnd]
     */
    public static function resolveDateRange($preset = 'all_time', $dateFrom = null, $dateTo = null)
    {
        $now = \Carbon\Carbon::now();

        if ($preset === 'custom' && $dateFrom && $dateTo) {
            $currentStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $currentEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();
            if ($currentStart->gt($currentEnd)) {
                [$currentStart, $currentEnd] = [$currentEnd, $currentStart];
            }
            $days = $currentStart->diffInDays($currentEnd) ?: 1;
            $prevEnd = $currentStart->copy()->subDay()->endOfDay();
            $prevStart = $prevEnd->copy()->subDays($days)->startOfDay();
            return [$currentStart, $currentEnd, $prevStart, $prevEnd];
        }

        switch ($preset) {
            case 'last_7_days':
                // 7 days inclusive (today + 6 days back)
                $currentStart = $now->copy()->subDays(6)->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                $prevStart = $prevEnd->copy()->subDays(6)->startOfDay();
                return [$currentStart, $currentEnd, $prevStart, $prevEnd];
            case 'last_30_days':
                // 30 days inclusive (today + 29 days back)
                $currentStart = $now->copy()->subDays(29)->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                $prevStart = $prevEnd->copy()->subDays(29)->startOfDay();
                return [$currentStart, $currentEnd, $prevStart, $prevEnd];
            case 'last_90_days':
                // 90 days inclusive (today + 89 days back)
                $currentStart = $now->copy()->subDays(89)->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                $prevStart = $prevEnd->copy()->subDays(89)->startOfDay();
                return [$currentStart, $currentEnd, $prevStart, $prevEnd];
            case 'this_month':
                $currentStart = $now->copy()->startOfMonth();
                $currentEnd = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subMonth()->startOfMonth();
                $prevEnd = $now->copy()->subMonth()->endOfMonth();
                return [$currentStart, $currentEnd, $prevStart, $prevEnd];
            case 'last_month':
                $currentStart = $now->copy()->subMonth()->startOfMonth();
                $currentEnd = $now->copy()->subMonth()->endOfMonth();
                $prevStart = $now->copy()->subMonths(2)->startOfMonth();
                $prevEnd = $now->copy()->subMonths(2)->endOfMonth();
                return [$currentStart, $currentEnd, $prevStart, $prevEnd];
            default:
                return [null, null, null, null];
        }
    }

    /**
     * Get weekly average overall rating trend for sparkline (Service Health).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param string $preset
     * @return array{labels: array, values: array}
     */
    public static function getWeeklyAverageTrend($query, $dateFrom = null, $dateTo = null, $preset = 'last_30_days')
    {
        list($currentStart, $currentEnd, , ) = self::resolveDateRange($preset, $dateFrom, $dateTo);
        if (!$currentStart || !$currentEnd) {
            return ['labels' => [], 'values' => []];
        }
        $labels = [];
        $values = [];
        $cursor = $currentStart->copy()->startOfWeek();
        while ($cursor->lte($currentEnd)) {
            $weekEnd = $cursor->copy()->endOfWeek();
            if ($weekEnd->lt($currentStart)) {
                $cursor->addWeek();
                continue;
            }
            $start = $cursor->gte($currentStart) ? $cursor : $currentStart;
            $end = $weekEnd->lte($currentEnd) ? $weekEnd : $currentEnd;
            
            // Average across ALL dynamic ratings submitted within this window
            // Normalized to a base of 4.0: (rating/max_rating) * 4
            $avgNormalized = \Illuminate\Support\Facades\DB::table('crm_feedback_ratings')
                ->join('crm_evaluation_metrics', 'crm_evaluation_metrics.id', '=', 'crm_feedback_ratings.evaluation_metric_id')
                ->joinSub($query->clone()->select('id')->whereBetween('created_at', [$start, $end]), 'filtered', function($join) {
                    $join->on('crm_feedback_ratings.customer_feedback_id', '=', 'filtered.id');
                })
                ->selectRaw('AVG((CAST(rating AS DECIMAL(10,2)) / CAST(crm_evaluation_metrics.max_rating AS DECIMAL(10,2))) * 4.0) as normalized_avg')
                ->value('normalized_avg');

            $labels[] = $cursor->format('M j');
            $values[] = $avgNormalized !== null ? round((float) $avgNormalized, 1) : 0;
            $cursor->addWeek();
        }
        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Get enhanced statistics for the Insights Dashboard.
     * Includes Trends, Detailed Segments, and Risk mapping.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array{date_preset?: string, date_from?: string, date_to?: string} $options
     * @return array
     */
    public static function getInsightsData($query, array $options = [])
    {
        $preset = $options['date_preset'] ?? 'all_time';
        $dateFrom = $options['date_from'] ?? null;
        $dateTo = $options['date_to'] ?? null;

        list($currentStart, $currentEnd, $prevStart, $prevEnd) = self::resolveDateRange($preset, $dateFrom, $dateTo);

        // Apply date range to base query for insights
        $insightsQuery = $query->clone();
        
        // 1. Total Requests (Sent in period) - Always based on created_at
        $sentQuery = $insightsQuery->clone();
        if ($preset !== 'all_time' && $currentStart && $currentEnd) {
            $sentQuery->whereBetween('created_at', [$currentStart, $currentEnd]);
        }
        $totalRequests = $sentQuery->count();

        // 2. Total Responses (Submitted in period) - Based on submitted_at
        $submittedQuery = $insightsQuery->clone()->where('status', self::STATUS_SUBMITTED);
        if ($preset !== 'all_time' && $currentStart && $currentEnd) {
            $submittedQuery->whereBetween('submitted_at', [$currentStart, $currentEnd]);
        }
        $totalResponses = $submittedQuery->count();

        // 3. ISO Risks (Within submitted in period)
        $riskQuery = $submittedQuery->clone()
            ->where(function($q) {
                $q->where('iso_impartiality', 'No')
                  ->orWhere('iso_confidentiality', 'No');
            });
        $riskCount = $riskQuery->count();
        $riskIds = $riskQuery->pluck('id')->toArray();

        // 4. Trends (Current period vs Previous period)
        // Simplified trend calculation using just total responses for now
        $currentNpsVal = $submittedQuery->clone()->where('will_recommend', 'Yes')->count();
        $currentNps = ($totalResponses > 0) ? round(($currentNpsVal / $totalResponses) * 100) : 0;

        $prevResponses = 0;
        $prevNpsVal = 0;
        if ($prevStart && $prevEnd) {
            $prevQuery = $query->clone()->where('status', self::STATUS_SUBMITTED)
                ->whereBetween('submitted_at', [$prevStart, $prevEnd]);
            $prevResponses = $prevQuery->count();
            $prevNpsVal = $prevQuery->where('will_recommend', 'Yes')->count();
        }
        $prevNps = ($prevResponses > 0) ? round(($prevNpsVal / $prevResponses) * 100) : 0;

        // 5. Rating Segments (Spectrum) - On the SUBMITTED IN PERIOD Dataset
        $submittedIdsQuery = $submittedQuery->clone()->select('id');
        
        $ratingsAgg = \Illuminate\Support\Facades\DB::table('crm_feedback_ratings')
            ->joinSub($submittedIdsQuery, 'filtered_feedbacks', function($join) {
                $join->on('crm_feedback_ratings.customer_feedback_id', '=', 'filtered_feedbacks.id');
            })
            ->join('crm_evaluation_metrics', 'crm_evaluation_metrics.id', '=', 'crm_feedback_ratings.evaluation_metric_id')
            ->select(
                'crm_evaluation_metrics.name as metric_name',
                'crm_evaluation_metrics.max_rating as max_rating',
                \Illuminate\Support\Facades\DB::raw("AVG(rating) as average"),
                \Illuminate\Support\Facades\DB::raw("AVG((CAST(rating AS DECIMAL(10,2)) / CAST(crm_evaluation_metrics.max_rating AS DECIMAL(10,2))) * 4.0) as normalized_average"),
                \Illuminate\Support\Facades\DB::raw("COUNT(rating) as total"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating >= crm_evaluation_metrics.max_rating THEN 1 ELSE 0 END) as excellent"), 
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating >= (crm_evaluation_metrics.max_rating * 0.7) AND rating < crm_evaluation_metrics.max_rating THEN 1 ELSE 0 END) as good"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating >= (crm_evaluation_metrics.max_rating * 0.4) AND rating < (crm_evaluation_metrics.max_rating * 0.7) THEN 1 ELSE 0 END) as fair"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN rating < (crm_evaluation_metrics.max_rating * 0.4) THEN 1 ELSE 0 END) as poor")
            )
            ->groupBy('crm_evaluation_metrics.id', 'crm_evaluation_metrics.name')
            ->orderBy('crm_evaluation_metrics.display_order')
            ->get();

        $ratingsData = [];
        // Map of metric slugs to legacy columns for hybrid calculation
        $legacyMap = [
            'communication' => 'rating_communication',
            'turnaround-time' => 'rating_turnaround',
            'technical-competence' => 'rating_technical',
            'accuracy-of-results' => 'rating_accuracy',
            'quality-of-reports' => 'rating_reports',
            'professionalism' => 'rating_professionalism',
            'handling-of-complaints' => 'rating_handling',
            'overall-rating' => 'rating_overall'
        ];

        foreach ($ratingsAgg as $res) {
            $key = \Illuminate\Support\Str::slug($res->metric_name);
            $total = (int) $res->total;
            
            // Hybrid protection: If dynamic ratings are missing for this metric (e.g. legacy data), 
            // check if we can pull from legacy columns for this period.
            $avgValue = (float) $res->average;
            if ($total === 0 && isset($legacyMap[$key])) {
                $legacyCol = $legacyMap[$key];
                $legacyStats = $insightsQuery->clone()
                    ->where('status', self::STATUS_SUBMITTED)
                    ->whereNotNull($legacyCol)
                    ->selectRaw("AVG($legacyCol) as avg, COUNT($legacyCol) as count")
                    ->first();
                if ($legacyStats && $legacyStats->count > 0) {
                    $avgValue = (float) $legacyStats->avg;
                    $total = (int) $legacyStats->count;
                }
            }

            $ratingsData[$key] = [
                'label' => $res->metric_name,
                'total' => $total,
                'average' => $total > 0 ? number_format($avgValue, 1) : null,
                'normalized_average' => $total > 0 ? (float)$res->normalized_average : null,
                'max_rating' => (int)$res->max_rating,
                'segments' => [
                    'excellent' => ['count' => $res->excellent, 'percent' => $total > 0 ? ($res->excellent/$total)*100 : 0, 'color' => '#28a745'],
                    'good'      => ['count' => $res->good, 'percent' => $total > 0 ? ($res->good/$total)*100 : 0, 'color' => '#17a2b8'],
                    'fair'      => ['count' => $res->fair, 'percent' => $total > 0 ? ($res->fair/$total)*100 : 0, 'color' => '#ffc107'],
                    'poor'      => ['count' => $res->poor, 'percent' => $total > 0 ? ($res->poor/$total)*100 : 0, 'color' => '#dc3545'],
                ]
            ];
        }

        // 5. Service Types with quality metrics (within date range)
        $pendingCount = $insightsQuery->clone()->where('status', self::STATUS_PENDING)->count();
        $serviceTypes = [];
        if ($pendingCount > 0) {
            $serviceTypes['__pending__'] = [
                'count' => $pendingCount,
                'avg_rating' => null,
                'nps_percent' => null,
            ];
        }

        $serviceTypesRaw = $insightsQuery->clone()
            ->where('status', self::STATUS_SUBMITTED)
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->select(
                'service_type',
                \Illuminate\Support\Facades\DB::raw('count(*) as count'),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN will_recommend = 'Yes' THEN 1 ELSE 0 END) as nps_yes")
            )
            ->groupBy('service_type')
            ->get();

        foreach ($serviceTypesRaw as $row) {
            $key = $row->service_type ?? '';
            if ($key === '') continue;
            
            // Calculate avg rating independently for this service type across all dynamic metrics (normalized to 4.0)
            $total = (int) $row->count;
            $avgRating = \Illuminate\Support\Facades\DB::table('crm_feedback_ratings')
                ->join('crm_evaluation_metrics', 'crm_evaluation_metrics.id', '=', 'crm_feedback_ratings.evaluation_metric_id')
                ->joinSub($insightsQuery->clone()->select('id')->where('service_type', $key), 'filtered', function($join) {
                    $join->on('crm_feedback_ratings.customer_feedback_id', '=', 'filtered.id');
                })->selectRaw('AVG((CAST(rating AS DECIMAL(10,2)) / CAST(crm_evaluation_metrics.max_rating AS DECIMAL(10,2))) * 4.0) as normalized_avg')
                ->value('normalized_avg');

            $npsYes = (int) $row->nps_yes;
            $serviceTypes[$key] = [
                'count' => $total,
                'avg_rating' => $avgRating ? round((float) $avgRating, 1) : null,
                'nps_percent' => $total > 0 ? round(($npsYes / $total) * 100) : null,
            ];
        }

        $totalTrend = ($prevStart && $prevEnd) ? ($totalResponses - $prevResponses) : null;
        $npsTrendVal = ($prevStart && $prevEnd) ? ($currentNps - $prevNps) : null;

        return [
            'pulse' => [
                'total_sent' => $totalRequests,
                'total_submitted' => $totalResponses,
                'total_trend' => $totalTrend,
                'nps_percent' => $currentNps,
                'nps_trend' => $npsTrendVal,
                'risk_count' => $riskCount,
                'risk_ids' => $riskIds,
                'date_preset' => $preset,
            ],
            'ratings' => $ratingsData,
            'service_types' => $serviceTypes
        ];
    }

    /**
     * Get actionable insights from insights data.
     *
     * @param array $insightsData Output from getInsightsData()
     * @return array<array{type: string, message: string, action_url: string|null, severity: string}>
     */
    public static function getActionableInsights(array $insightsData)
    {
        $insights = [];
        $pulse = $insightsData['pulse'] ?? [];
        $ratings = $insightsData['ratings'] ?? [];
        $serviceTypes = $insightsData['service_types'] ?? [];

        $total = $pulse['total'] ?? 0;
        $riskCount = $pulse['risk_count'] ?? 0;
        $npsPercent = $pulse['nps_percent'] ?? 0;
        $npsTrend = $pulse['nps_trend'] ?? null;

        // ISO risk alert
        if ($riskCount > 0) {
            $insights[] = [
                'type' => 'iso_risk',
                'message' => "{$riskCount} feedback" . ($riskCount > 1 ? 's have' : ' has') . " ISO compliance concerns.",
                'action_url' => null,
                'severity' => 'danger',
            ];
        }

        // Declining NPS
        if ($npsTrend !== null && $npsTrend < 0) {
            $insights[] = [
                'type' => 'declining_nps',
                'message' => "Recommendation rate dropped " . abs($npsTrend) . "% vs previous period.",
                'action_url' => null,
                'severity' => 'warning',
            ];
        }

        // Low volume (for period)
        if ($total > 0 && $total < 5) {
            $insights[] = [
                'type' => 'low_volume',
                'message' => "Only {$total} feedback" . ($total === 1 ? '' : 's') . " in the selected period. Consider sending more requests.",
                'action_url' => null,
                'severity' => 'info',
            ];
        }

        // Pending feedbacks (Unknown - not yet submitted)
        $pendingCount = $serviceTypes['__pending__']['count'] ?? 0;
        if ($pendingCount > 0) {
            $insights[] = [
                'type' => 'pending_feedbacks',
                'message' => "{$pendingCount} feedback" . ($pendingCount > 1 ? 's are' : ' is') . " pending (awaiting submission).",
                'action_url' => null,
                'severity' => 'info',
            ];
        }

        // Weakest and strongest categories
        $withAverage = [];
        foreach ($ratings as $key => $data) {
            if (($data['total'] ?? 0) > 0) {
                $withAverage[$key] = (float) ($data['average'] ?? 0);
            }
        }
        if (count($withAverage) >= 2) {
            asort($withAverage);
            $weakest = array_key_first($withAverage);
            $weakestAvg = $withAverage[$weakest];
            $insights[] = [
                'type' => 'weakest_category',
                'message' => ucfirst($weakest) . " has the lowest average (" . number_format($weakestAvg, 1) . "). Focus improvement here.",
                'action_url' => null,
                'severity' => 'warning',
            ];

            arsort($withAverage);
            $strongest = array_key_first($withAverage);
            if ($strongest !== $weakest) {
                $strongestAvg = $withAverage[$strongest];
                $insights[] = [
                    'type' => 'strongest_category',
                    'message' => ucfirst($strongest) . " is your strongest area (" . number_format($strongestAvg, 1) . ").",
                    'action_url' => null,
                    'severity' => 'info',
                ];
            }
        }

        return $insights;
    }

    /**
     * Get feedback volume trend data for chart (count per period).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $granularity 'week' or 'month'
     * @param \Carbon\Carbon|null $from
     * @param \Carbon\Carbon|null $to
     * @return array{labels: array, values: array}
     */
    public static function getVolumeTrendData($query, $granularity = 'month', $from = null, $to = null)
    {
        $q = $query->clone();
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        }

        if ($granularity === 'week') {
            $data = $q->selectRaw('YEAR(created_at) as y, WEEK(created_at) as w, count(*) as c')
                ->whereNotNull('created_at')
                ->groupBy('y', 'w')
                ->orderBy('y')
                ->orderBy('w')
                ->get();
            $labels = [];
            $values = [];
            foreach ($data as $row) {
                $labels[] = 'W' . $row->w . ' ' . $row->y;
                $values[] = $row->c;
            }
        } else {
            $data = $q->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, count(*) as c')
                ->whereNotNull('created_at')
                ->groupBy('y', 'm')
                ->orderBy('y')
                ->orderBy('m')
                ->get();
            $labels = [];
            $values = [];
            foreach ($data as $row) {
                $labels[] = \Carbon\Carbon::createFromDate($row->y, $row->m, 1)->format('M Y');
                $values[] = $row->c;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Get NPS (Recommendation Rate) trend data per period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $granularity 'week' or 'month'
     * @param \Carbon\Carbon|null $from
     * @param \Carbon\Carbon|null $to
     * @return array{labels: array, values: array}
     */
    public static function getNpsTrendData($query, $granularity = 'month', $from = null, $to = null)
    {
        $q = $query->clone();
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        }

        if ($granularity === 'week') {
            $data = $q->selectRaw("
                YEAR(created_at) as y, WEEK(created_at) as w,
                count(*) as total,
                SUM(CASE WHEN will_recommend = 'Yes' THEN 1 ELSE 0 END) as nps_yes
            ")
                ->whereNotNull('created_at')
                ->groupBy('y', 'w')
                ->orderBy('y')
                ->orderBy('w')
                ->get();
            $labels = [];
            $values = [];
            foreach ($data as $row) {
                $labels[] = 'W' . $row->w . ' ' . $row->y;
                $values[] = $row->total > 0 ? round(($row->nps_yes / $row->total) * 100) : 0;
            }
        } else {
            $data = $q->selectRaw("
                YEAR(created_at) as y, MONTH(created_at) as m,
                count(*) as total,
                SUM(CASE WHEN will_recommend = 'Yes' THEN 1 ELSE 0 END) as nps_yes
            ")
                ->whereNotNull('created_at')
                ->groupBy('y', 'm')
                ->orderBy('y')
                ->orderBy('m')
                ->get();
            $labels = [];
            $values = [];
            foreach ($data as $row) {
                $labels[] = \Carbon\Carbon::createFromDate($row->y, $row->m, 1)->format('M Y');
                $values[] = $row->total > 0 ? round(($row->nps_yes / $row->total) * 100) : 0;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
