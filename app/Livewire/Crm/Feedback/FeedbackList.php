<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\CRMCustomer;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;

class FeedbackList extends BaseCrmComponent
{

    public $search = '';
    public $activeTab = 'all';
    public $showForm = false;
    public $selectedFeedback;
    public $editingFeedbackId = null;
    public $perPage = 10;

    /** @var string Time range preset for insights: last_7_days, last_30_days, last_90_days, this_month, last_month, all_time, custom */
    public $datePreset = 'last_30_days';
    public $dateFrom = null;
    public $dateTo = null;
    /** @var string|null Filter feedback list by service type (e.g. 'Testing', 'Sampling') */
    public $serviceTypeFilter = null;

    /** @var string Content tab: overview | service_breakdown | analytics */
    public $contentTab = 'overview';

    /** @var string|null Quick filter: negative | high_value | needs_reply | today */
    public $quickFilter = null;

    /**
     * Cached insights data — computed ONCE on mount() and ONLY re-computed
     * when the time period changes (datePreset/dateFrom/dateTo).
     * NOT re-computed on search, pagination, or tab changes.
     */
    public $insights = [];
    public $averageOverallRating = null;
    public $riskFeedbacksPreview = [];

    public $showInsights = true;
    public $showRisksOnly = false;
    public $responseRate = 0;
    public $sortedDimensions = [];
    public $topLowestFeedbacks = [];
    public $selectedCategory = null;
    public $categoryDetails = null;
    /** @var string|null When set, filter list to feedback with low rating (<=2) for this category */
    public $lowRatingCategoryFilter = null;

    protected $paginationTheme = 'bootstrap';

    protected $resetPageOnUpdate = ['search', 'activeTab', 'perPage', 'datePreset', 'dateFrom', 'dateTo', 'serviceTypeFilter', 'lowRatingCategoryFilter', 'quickFilter'];

    protected $queryString = [
        'search' => ['except' => ''],
        'datePreset' => ['except' => 'last_30_days'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'serviceTypeFilter' => ['except' => ''],
        'showRisksOnly' => ['except' => false],
        'activeTab' => ['except' => 'all'],
        'lowRatingCategoryFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }



    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->initialize();
        $this->checkPermission('crm.components.feedbacks.view');

        // Hydrate filter state from URL for consistency on reload/navigation
        $query = request()->query();
        if (array_key_exists('showRisksOnly', $query)) {
            $this->showRisksOnly = filter_var($query['showRisksOnly'], FILTER_VALIDATE_BOOLEAN);
        }
        if (array_key_exists('activeTab', $query) && in_array($query['activeTab'], ['all', 'submitted', 'pending'])) {
            $this->activeTab = $query['activeTab'];
        }
        if (array_key_exists('lowRatingCategoryFilter', $query)) {
            $this->lowRatingCategoryFilter = $query['lowRatingCategoryFilter'] ?: null;
        }
        if (array_key_exists('serviceTypeFilter', $query)) {
            $this->serviceTypeFilter = $query['serviceTypeFilter'] ?: null;
        }
        if (array_key_exists('datePreset', $query)) {
            $this->datePreset = $query['datePreset'] ?? 'last_30_days';
        }
        if (array_key_exists('dateFrom', $query)) {
            $this->dateFrom = $query['dateFrom'] ?: null;
        }
        if (array_key_exists('dateTo', $query)) {
            $this->dateTo = $query['dateTo'] ?: null;
        }

        // Set custom page name to prevent URL duplication
        $this->setPage(request()->query('page', 1), 'page');

        // Compute insights once on mount — not on every render
        $this->loadInsights();
    }

    /**
     * Load all expensive insight DB queries and cache results in component state.
     * Called ONLY on mount() and when date period changes.
     * Search, pagination, and tab changes do NOT trigger this.
     */
    public function loadInsights(): void
    {
        $query = CustomerFeedback::query();

        $insightsOptions = [
            'date_preset' => $this->datePreset,
            'date_from'   => $this->dateFrom,
            'date_to'     => $this->dateTo,
        ];

        $this->insights = CustomerFeedback::getInsightsData($query, $insightsOptions);

        // Compute response rate for the Uptake Rate widget
        $totalSent = $this->insights['pulse']['total_sent'] ?? 0;
        $totalSubmitted = $this->insights['pulse']['total_submitted'] ?? 0;
        $this->responseRate = $totalSent > 0 ? round(($totalSubmitted / $totalSent) * 100) : 0;

        // --- NEW REAL INSIGHTS LOGIC ---
        
        // 1. Resolve Date Range for manual Eloquent checks
        list($currentStart, $currentEnd, , ) = CustomerFeedback::resolveDateRange($this->datePreset, $this->dateFrom, $this->dateTo);

        $avgQuery = CustomerFeedback::query()
            ->where('status', CustomerFeedback::STATUS_SUBMITTED)
            ->whereNotNull('rating_overall');
        if ($currentStart && $currentEnd) {
            $avgQuery->whereBetween('submitted_at', [$currentStart, $currentEnd]);
        }
        $avg = $avgQuery->avg('rating_overall');
        $this->averageOverallRating = $avg ? number_format($avg, 2) : null;
        
        // 2. Aggregating Dimensions (Dynamic based on metrics)
        $ratings = $this->insights['ratings'] ?? [];
        $dimensions = [];
        foreach ($ratings as $slug => $data) {
            $dimensions[$data['label']] = [
                'average' => (float)($data['average'] ?? 0),
                'normalized' => (float)($data['normalized_average'] ?? 0),
                'max' => (int)($data['max_rating'] ?? 4)
            ];
        }

        // Sort by normalized score (ascending) to highlight areas needing most attention
        uasort($dimensions, function($a, $b) {
            return $a['normalized'] <=> $b['normalized'];
        });

        $this->sortedDimensions = $dimensions;

        // 3. Generating Lowest Rated Feedback (Dynamic average based on crm_feedback_ratings)
        $topLowestQuery = CustomerFeedback::query()
            ->select('customerfeedbacks.*')
            ->selectRaw('(
                SELECT AVG(CAST(r.rating AS DECIMAL(10,2)) / CAST(m.max_rating AS DECIMAL(10,2))) * 4.0
                FROM crm_feedback_ratings r 
                JOIN crm_evaluation_metrics m ON r.evaluation_metric_id = m.id 
                WHERE r.customer_feedback_id = customerfeedbacks.id
            ) as avg_score')
            ->where('status', CustomerFeedback::STATUS_SUBMITTED)
            ->with(['customer', 'contact'])
            ->having('avg_score', '<=', 1.6) // Bottom 40% (equivalent to 1.6/4)
            ->having('avg_score', '>', 0)
            ->orderBy('avg_score', 'asc')
            ->orderBy('id', 'desc');

        if ($currentStart && $currentEnd) {
            $topLowestQuery->whereBetween('submitted_at', [$currentStart, $currentEnd]);
        }
        
        $this->topLowestFeedbacks = $topLowestQuery->get()->toArray();
        
        // Keep risk preview functionality intact for ISO flags
        $risks = $this->insights['pulse']['risk_count'] ?? 0;
        if ($risks > 0) {
            $riskPreviewQuery = CustomerFeedback::query()
                ->where(function($q) {
                    $q->whereBetween('rating_technical', [1, 2])
                      ->orWhereBetween('rating_accuracy', [1, 2])
                      ->orWhereHas('ratings', function($q2) {
                          $q2->whereHas('metric', function($q3) {
                              $q3->whereRaw('crm_feedback_ratings.rating <= (crm_evaluation_metrics.max_rating * 0.4)');
                          });
                      });
                })
                ->with('customer')
                ->orderBy('submitted_at', 'desc')
                ->limit(10);
            if ($currentStart && $currentEnd) {
                $riskPreviewQuery->whereBetween('submitted_at', [$currentStart, $currentEnd]);
            }
            $this->riskFeedbacksPreview = $riskPreviewQuery
                ->get()
                ->map(fn($f) => [
                    'id'       => $f->id,
                    'code'     => $f->code ?? 'FB' . str_pad($f->id, 4, '0', STR_PAD_LEFT),
                    'customer' => $f->customer->name ?? 'N/A',
                    'date'     => ($f->submitted_at ?? $f->created_at)?->format('d M Y') ?? 'N/A',
                ])
                ->toArray();
        } else {
            $this->riskFeedbacksPreview = [];
        }
    }

    public function paginationView()
    {
        return 'pagination::bootstrap-4';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage(); // If using pagination
    }

    public function setContentTab($tab)
    {
        if (in_array($tab, ['overview', 'service_breakdown', 'analytics'], true)) {
            $this->contentTab = $tab;
        }
    }

    public function setQuickFilter($filter)
    {
        $this->quickFilter = ($this->quickFilter === $filter) ? null : $filter;
        $this->resetPage();
    }

    // getFeedbacksProperty removed in favor of render() query building 
    // to allow sharing the query builder with getStats()

    public function viewFeedback($id)
    {
        $this->selectedFeedback = CustomerFeedback::find($id);
        $this->dispatch('open-view-modal');
    }

    public function openAddForm()
    {
        $this->editingFeedbackId = null;
        $this->showForm = true;
        $this->dispatch('add-feedback');
        $this->dispatch('show-feedback-modal');
    }

    public function openSendCampaignModal()
    {
        $this->dispatch('open-send-campaign-modal');
    }

    #[On('feedback-requests-sent')]
    public function onFeedbackRequestsSent()
    {
        $this->resetPage(); // Jump to page 1 so new pending items (at top) are visible
    }

    public function editFeedback($id)
    {
        $this->editingFeedbackId = $id;
        $this->showForm = true;
        $this->dispatch('edit-feedback', feedbackId: $id);
        $this->dispatch('show-feedback-modal');
    }

    public function toggleInsights()
    {
        $this->showInsights = !$this->showInsights;
    }

    public function toggleRiskFilter()
    {
        $this->showRisksOnly = !$this->showRisksOnly;
        $this->resetPage();
    }

    public function setDatePreset($preset)
    {
        $this->datePreset = $preset;
        if ($preset !== 'custom') {
            $this->dateFrom = null;
            $this->dateTo = null;
        } else {
            // Default custom range: last 30 days when switching to custom
            if (!$this->dateFrom && !$this->dateTo) {
                $this->dateFrom = now()->subDays(29)->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
            }
        }
        $this->resetPage();
    }

    public function updatedDatePreset()
    {
        $this->loadInsights();
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->loadInsights();
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->loadInsights();
        $this->resetPage();
    }

    public function setServiceTypeFilter($type)
    {
        $this->serviceTypeFilter = ($type === '' || $type === null) ? null : $type;
        if ($this->serviceTypeFilter === '__pending__') {
            $this->activeTab = 'pending';
        }
        $this->resetPage();
    }

    public function clearServiceTypeFilter()
    {
        $this->reset('serviceTypeFilter');
        // Reset activeTab when clearing - it may have been set to 'pending' by Unknown filter
        if ($this->activeTab === 'pending') {
            $this->activeTab = 'all';
        }
        $this->resetPage();
    }

    public function filterByLowRatingCategory($category)
    {
        $this->lowRatingCategoryFilter = $category;
        $this->resetPage();
        $this->dispatch('close-category-modal');
    }

    public function clearLowRatingCategoryFilter()
    {
        $this->reset('lowRatingCategoryFilter');
        $this->resetPage();
        $this->loadInsights();
    }

    public function loadCategoryDetails($categorySlug)
    {
        $this->selectedCategory = $categorySlug;
        
        // Find the metric
        $metric = \App\Models\CRM\EvaluationMetric::where('is_active', true)
            ->get()
            ->first(fn($m) => \Illuminate\Support\Str::slug($m->name) === $categorySlug);

        if (!$metric) {
            // Fallback for legacy categories if needed, though insights use slugs of metric names
            $columnMap = [
                'communication' => 'rating_communication',
                'turnaround' => 'rating_turnaround',
                'technical' => 'rating_technical',
                'accuracy' => 'rating_accuracy',
                'reports' => 'rating_reports',
                'professionalism' => 'rating_professionalism',
                'handling' => 'rating_handling'
            ];
            $col = $columnMap[$categorySlug] ?? null;
            if (!$col) return;

            // Legacy Logic
            $query = CustomerFeedback::query();
            if ($this->search) {
                 $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('service_reference_no', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->activeTab == 'submitted') $query->where('status', CustomerFeedback::STATUS_SUBMITTED);
            
            list($currentStart, $currentEnd, , ) = CustomerFeedback::resolveDateRange($this->datePreset, $this->dateFrom, $this->dateTo);
            if ($currentStart && $currentEnd) $query->whereBetween('submitted_at', [$currentStart, $currentEnd]);

            $stats = $query->clone()
                ->selectRaw("
                    AVG($col) as average,
                    SUM(CASE WHEN $col >= 4 THEN 1 ELSE 0 END) as count_5,
                    SUM(CASE WHEN $col = 3 THEN 1 ELSE 0 END) as count_3,
                    SUM(CASE WHEN $col = 2 THEN 1 ELSE 0 END) as count_2,
                    SUM(CASE WHEN $col <= 1 THEN 1 ELSE 0 END) as count_1,
                    COUNT($col) as total
                ")
                ->whereNotNull($col)
                ->where($col, '>', 0)
                ->first();

            $comments = $query->clone()->whereNotNull($col)->where(fn($q) => $q->where('suggestions', '!=', '')->orWhere('issue_description', '!=', ''))->limit(5)->get();

            $this->categoryDetails = [
                'label' => ucfirst($categorySlug),
                'category_key' => $categorySlug,
                'average' => number_format($stats->average ?? 0, 1),
                'total' => $stats->total,
                'max' => 4,
                'low_rating_count' => ($stats->count_2 ?? 0) + ($stats->count_1 ?? 0),
                'histogram' => [5 => $stats->count_5, 4 => 0, 3 => $stats->count_3, 2 => $stats->count_2, 1 => $stats->count_1],
                'comments' => $comments
            ];
        } else {
            // New Dynamic Logic
            $query = CustomerFeedback::query()->where('status', CustomerFeedback::STATUS_SUBMITTED);
            list($currentStart, $currentEnd, , ) = CustomerFeedback::resolveDateRange($this->datePreset, $this->dateFrom, $this->dateTo);
            if ($currentStart && $currentEnd) $query->whereBetween('submitted_at', [$currentStart, $currentEnd]);

            $feedbackIds = $query->pluck('id');
            $max = $metric->max_rating;

            $stats = \App\Models\CRM\FeedbackRating::where('evaluation_metric_id', $metric->id)
                ->whereIn('customer_feedback_id', $feedbackIds)
                ->selectRaw("
                    AVG(rating) as average,
                    SUM(CASE WHEN rating >= $max THEN 1 ELSE 0 END) as count_5,
                    SUM(CASE WHEN rating >= ($max * 0.7) AND rating < $max THEN 1 ELSE 0 END) as count_4,
                    SUM(CASE WHEN rating >= ($max * 0.4) AND rating < ($max * 0.7) THEN 1 ELSE 0 END) as count_3,
                    SUM(CASE WHEN rating < ($max * 0.4) AND rating >= ($max * 0.2) THEN 2 ELSE 0 END) as count_2, 
                    SUM(CASE WHEN rating < ($max * 0.2) THEN 1 ELSE 0 END) as count_1,
                    COUNT(*) as total
                ")
                ->first();
            
            // Note: count_2/count_1 above is a bit simplified, let's just use exact ratings if max is small
            if ($max <= 5) {
                $histogramQuery = \App\Models\CRM\FeedbackRating::where('evaluation_metric_id', $metric->id)
                    ->whereIn('customer_feedback_id', $feedbackIds)
                    ->selectRaw('rating, COUNT(*) as count')
                    ->groupBy('rating')
                    ->pluck('count', 'rating');
                
                $histogram = [];
                for ($i = 5; $i >= 1; $i--) $histogram[$i] = $histogramQuery[$i] ?? 0;
            } else {
                $histogram = [5 => $stats->count_5, 4 => $stats->count_4, 3 => $stats->count_3, 2 => $stats->count_2, 1 => $stats->count_1];
            }

            $comments = CustomerFeedback::whereIn('id', $feedbackIds)
                ->whereHas('ratings', fn($q) => $q->where('evaluation_metric_id', $metric->id))
                ->where(fn($q) => $q->where('suggestions', '!=', '')->orWhere('issue_description', '!=', ''))
                ->limit(5)->get();

            $this->categoryDetails = [
                'label' => $metric->name,
                'category_key' => $categorySlug,
                'average' => number_format($stats->average ?? 0, 1),
                'total' => $stats->total,
                'max' => $max,
                'low_rating_count' => \App\Models\CRM\FeedbackRating::where('evaluation_metric_id', $metric->id)
                    ->whereIn('customer_feedback_id', $feedbackIds)
                    ->where('rating', '<=', $max * 0.4)
                    ->count(),
                'histogram' => $histogram,
                'comments' => $comments
            ];
        }
        
        $this->dispatch('open-category-modal');
    }

    #[On('feedback-saved')]
    #[On('feedback-form-closed')]
    public function closeForm()
    {
        $this->showForm = false;
        $this->editingFeedbackId = null;
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.components.feedbacks.view');

        $filters = [
            'search' => $this->search,
            'activeTab' => $this->activeTab,
            'showRisksOnly' => $this->showRisksOnly,
        ];

        return (new \App\Exports\CRM\FeedbackExport($filters))->download('crm_feedback_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportInsights()
    {
        $this->checkPermission('crm.components.feedbacks.view');

        $query = CustomerFeedback::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('service_reference_no', 'like', '%' . $this->search . '%')
                    ->orWhere('iso_concerns_description', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->activeTab == 'submitted') {
            $query->where('status', CustomerFeedback::STATUS_SUBMITTED);
        } elseif ($this->activeTab == 'pending') {
            $query->where('status', CustomerFeedback::STATUS_PENDING);
        }
        if ($this->showRisksOnly) {
            $query->where(function($q) {
                $q->where('iso_impartiality', 'No')->orWhere('iso_confidentiality', 'No');
            });
        }

        $insightsOptions = [
            'date_preset' => $this->datePreset,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
        $insights = CustomerFeedback::getInsightsData($query, $insightsOptions);
        $actionableInsights = CustomerFeedback::getActionableInsights($insights);

        return (new \App\Exports\CRM\FeedbackInsightsExport($insights, $actionableInsights))
            ->download('feedback_insights_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $query = CustomerFeedback::query();

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('service_reference_no', 'like', $term)
                    ->orWhere('iso_concerns_description', 'like', $term)
                    ->orWhere('suggestions', 'like', $term)
                    ->orWhere('issue_description', 'like', $term)
                    ->orWhere('received_from', 'like', $term)
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }

        if ($this->activeTab == 'submitted') {
            $query->where('status', CustomerFeedback::STATUS_SUBMITTED);
        } elseif ($this->activeTab == 'pending') {
            $query->where('status', CustomerFeedback::STATUS_PENDING);
        }

        if ($this->showRisksOnly) {
            $query->where(function($q) {
                $q->where('iso_impartiality', 'No')
                  ->orWhere('iso_confidentiality', 'No');
            });
        }

        if ($this->quickFilter === 'negative') {
            $query->where(function($q) {
                $q->where(function($q2) {
                    $q2->where('iso_impartiality', 'No')->orWhere('iso_confidentiality', 'No');
                })
                ->orWhereHas('ratings', function($q2) {
                    $q2->where('rating', '<=', 2);
                });
            });
        } elseif ($this->quickFilter === 'high_value') {
            $query->where('will_recommend', 'Yes');
        } elseif ($this->quickFilter === 'needs_reply') {
            $query->where('status', CustomerFeedback::STATUS_PENDING);
        } elseif ($this->quickFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        }

        if ($this->serviceTypeFilter !== null && $this->serviceTypeFilter !== '') {
            if ($this->serviceTypeFilter === '__pending__') {
                $query->where('status', CustomerFeedback::STATUS_PENDING);
            } else {
                $query->where('service_type', $this->serviceTypeFilter);
            }
        }

        if ($this->lowRatingCategoryFilter) {
            // Prefer dynamic ratings (crm_feedback_ratings); fall back to legacy columns for backfilled data
            $metricIds = \App\Models\CRM\EvaluationMetric::select('id', 'name')
                ->get()
                ->filter(fn($m) => \Illuminate\Support\Str::slug($m->name) === $this->lowRatingCategoryFilter)
                ->pluck('id');
            if ($metricIds->isNotEmpty()) {
                $query->whereHas('ratings', function($q) use ($metricIds) {
                    $q->where('rating', '<=', 2)->whereIn('evaluation_metric_id', $metricIds);
                });
            } else {
                $columnMap = [
                    'communication' => 'rating_communication',
                    'turnaround' => 'rating_turnaround',
                    'technical' => 'rating_technical',
                    'accuracy' => 'rating_accuracy',
                    'reports' => 'rating_reports',
                    'professionalism' => 'rating_professionalism',
                    'handling' => 'rating_handling'
                ];
                $col = $columnMap[$this->lowRatingCategoryFilter] ?? null;
                if ($col) {
                    $query->whereNotNull($col)->where($col, '<=', 2);
                }
            }
        }

        // Resolve date range and apply to main query
        list($currentStart, $currentEnd, , ) = CustomerFeedback::resolveDateRange($this->datePreset, $this->dateFrom, $this->dateTo);
        if ($currentStart && $currentEnd) {
            if ($this->activeTab == 'submitted') {
                // For submitted tab, filter by submission date
                $query->whereBetween('submitted_at', [$currentStart, $currentEnd]);
            } else {
                // For others, default to created_at (sent date)
                $query->whereBetween('created_at', [$currentStart, $currentEnd]);
            }
        }

        // Insights are held in $this->insights — computed in loadInsights(), NOT here.
        // This keeps render() fast for search/pagination updates.

        // Clone for pagination
        $feedbacks = $query->clone()->with(['customer', 'contact'])->orderBy('id', 'desc')->paginate($this->perPage);
        $feedbacks->withPath(route('feedback-home'));

        return view('livewire.crm.feedback.feedback-list', [
            'feedbacks'             => $feedbacks,
            'editingFeedbackId'     => $this->editingFeedbackId,
            'insights'              => $this->insights,
            'riskFeedbacksPreview'  => $this->riskFeedbacksPreview,
            'averageOverallRating'  => $this->averageOverallRating,
        ])->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => true])
            ->section('content2');
    }

    // specific getter method is not strictly needed if we build it in render, 
    // but creating a reuseable method is cleaner if we had other uses. 
    // For now, keeping it inline in render as per original pattern but ensuring it's efficient.
    public $resendFeedbackId = null;

    public function confirmResend($id)
    {
        $this->resendFeedbackId = $id;
        $this->dispatch('open-resend-confirmation');
    }

    public function resendFeedback()
    {
        if (!$this->resendFeedbackId) return;
        
        $id = $this->resendFeedbackId;
        $feedback = CustomerFeedback::find($id);

        if (!$feedback) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Feedback request not found.']);
            return;
        }

        // 1. Validation: Status must be Pending
        if ($feedback->status != CustomerFeedback::STATUS_PENDING) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Feedback has already been submitted or is not pending.']);
            $this->resendFeedbackId = null;
            $this->dispatch('close-resend-confirmation');
            return;
        }

        // 2. Spam Check: Last reminded at > 24 hours
        if ($feedback->last_reminded_at && \Carbon\Carbon::parse($feedback->last_reminded_at)->gt(now()->subHours(24))) {
            $diff = \Carbon\Carbon::parse($feedback->last_reminded_at)->addHours(24)->diffForHumans();
            $this->dispatch('alert', ['type' => 'warning', 'message' => "Please wait before resending. You can resend {$diff}."]);
            $this->resendFeedbackId = null;
            $this->dispatch('close-resend-confirmation');
            return;
        }

        // 3. Retrieval: Request & Contact
        $request = $feedback->feedbackRequest;
        $contact = $feedback->contact;

        if (!$request || !$contact || !$contact->email) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Cannot resend: Missing contact email or original request data.']);
            $this->resendFeedbackId = null;
            return;
        }

        // 4. Action: Send Email
        try {
            // Generate Signed URL (reuse logic)
            $feedbackLink = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'feedback.form',
                now()->addDays(7), // Refresh expiry
                [
                    'contact_id' => $contact->id,
                    'token'      => $request->token 
                ]
            );

            $subject = "Reminder: Feedback Request from " . ($this->getCompanyDetails()['name'] ?? config('app.name'));
            $recipientName = $contact->first_name ?? ($contact->surname ?? 'Valued Customer');
            
            // Custom Body for Reminder
            $body = "We noticed you haven't had a chance to share your feedback yet. Your input is valuable to us. Please take a moment to rate our services.";

            $companyId = $this->getUserCompany();
            $company = \App\Company::find($companyId);

            \Illuminate\Support\Facades\Mail::to($contact->email)->send(
                new \App\Mail\FeedbackCampaignMail($body, $subject, $recipientName, $feedbackLink, $company)
            );

            // Update Timestamp
            $feedback->update(['last_reminded_at' => now()]);
            
            $this->dispatch('close-resend-confirmation');
            $this->dispatch('alert', ['type' => 'success', 'message' => "Reminder sent to {$contact->email}"]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Resend Feedback Failed: " . $e->getMessage());
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Failed to send email. Please try again later.']);
            $this->dispatch('close-resend-confirmation');
        }
        
        $this->resendFeedbackId = null;
    }
}