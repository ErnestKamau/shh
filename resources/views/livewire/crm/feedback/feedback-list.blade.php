<div>
    <main>
        @php
            $breadcrumbItems = [
                ['link' => route('customers-list'), 'name' => __('crm.module_name'), 'icon' => null],
                ['link' => route('feedback-home'), 'name' => __('crm.customer_feedback'), 'icon' => null]
            ];
        @endphp
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$breadcrumbItems"
                :title="__('crm.customer_feedback')"
                :subtitle="__('crm.feedback_subtitle')"
                icon="mdi-file-account"
            >
                <x-slot:actions>
                    <button type="button" class="btn btn-outline-success btn-sm mr-2 crm-btn-export" wire:click.prevent="exportToExcel">
                        <i class="fa fa-file-excel mr-1"></i> {{ __('crm.export_to_excel') }}
                    </button>
                    <button type="button" class="btn btn-add btn-sm crm-btn-add" wire:click.prevent="openSendCampaignModal">
                        <i class="mdi mdi-plus"></i> {{ __('crm.send_feedback') }}
                    </button>
                </x-slot:actions>
            </x-crm.page-header>



        @if(!empty($insights))
        @php
            $pulse          = $insights['pulse'] ?? [];
            $totalCount     = $pulse['total_sent'] ?? 0;
            $nps            = $pulse['nps_percent'] ?? 0;
            $risks          = $pulse['risk_count'] ?? 0;

            // Response Rate: submitted vs total sent
            $submittedCount = $pulse['total_submitted'] ?? 0;
            // $responseRate is now calculated in the component

            // Valid service types for performer analysis
            $validServices = collect($insights['service_types'] ?? [])->filter(fn($s, $k) =>
                $k !== '' && $k !== null && $k !== '__pending__' && $s['avg_rating'] !== null
            );
            $highest     = $validServices->sortByDesc('avg_rating')->first();
            $highestName = $validServices->sortByDesc('avg_rating')->keys()->first();
            $lowest      = $validServices->sortBy('avg_rating')->first();
            $lowestName  = $validServices->sortBy('avg_rating')->keys()->first();
        @endphp
        <div class="crm-card mx-4 mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: var(--crm-radius-lg);">

            {{-- Card Header --}}
            <div class="crm-intel-strip d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="mr-3 d-flex align-items-center justify-content-center rounded" style="width:36px;height:36px;background:#eef2ff;">
                        <i class="mdi mdi-shield-check text-primary" style="font-size:1.2rem;"></i>
                    </span>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-0">Client Quality Assurance Pulse</h6>
                        <small class="crm-label-muted">Real-time view of service quality, loyalty signals &amp; compliance health</small>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    @if($submittedCount > 0)
                    <span class="crm-badge crm-badge-neutral mr-3">
                        <i class="mdi mdi-database-outline mr-1"></i>{{ $submittedCount }} responses
                    </span>
                    @endif
                    {{-- no-select2: native select so it works after full page refresh; layout's Select2 runs once and Livewire re-renders replace DOM, breaking Select2 --}}
                    <select class="custom-select custom-select-sm no-select2 border-0 bg-light font-weight-bold" style="width:130px;border-radius:6px;font-size:0.78rem;" wire:model.live="datePreset">
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="last_30_days">Last 30 Days</option>
                        <option value="last_90_days">Last 90 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
            </div>

            @if($datePreset === 'custom')
            <div class="d-flex align-items-center justify-content-end px-4 pt-3 pb-0" style="gap: 8px;">
                <small class="text-muted">From</small>
                <input type="date" class="form-control form-control-sm border-0 bg-light" style="width:140px;" wire:model.live="dateFrom">
                <small class="text-muted">to</small>
                <input type="date" class="form-control form-control-sm border-0 bg-light" style="width:140px;" wire:model.live="dateTo">
            </div>
            @endif

            {{-- Tier 1: Executive Summary & Action (Top Row) --}}
            <div class="crm-intel-body pb-0 pt-0">
                <div class="row mb-4">
                    {{-- Left Column: Stacked Metrics --}}
                    <div class="col-md-5 d-flex flex-column" style="gap: 1.5rem;">
                        
                        {{-- 1. Service Excellence Score --}}
                        <div class="d-flex flex-column justify-content-center p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <p class="crm-label-muted text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing:.06em;">
                                <i class="mdi mdi-star-circle-outline mr-1 text-primary"></i>Service Excellence Score
                            </p>
                            @php
                                $normalizedAverage = $averageOverallRating ? (float) $averageOverallRating : 0;
                                $ratingFill = $normalizedAverage ? min(100, round(($normalizedAverage / 5) * 100)) : 0;
                            @endphp
                            <div class="d-flex align-items-baseline" style="gap: 8px;">
                                <span class="font-weight-bold {{ $ratingFill >= 80 ? 'text-success' : ($ratingFill < 50 ? 'text-danger' : 'text-warning') }}" style="font-size:2rem;line-height:1;">
                                    {{ $normalizedAverage ? number_format($normalizedAverage, 1) . ' / 5' : 'N/A' }}
                                </span>
                                @if($normalizedAverage)
                                    <span class="badge {{ $ratingFill >= 80 ? 'badge-success' : ($ratingFill < 50 ? 'badge-danger' : 'badge-warning') }}" style="font-size: 0.8rem; padding: 0.4em 0.8em;">
                                        {{ $ratingFill }}%
                                    </span>
                                @endif
                            </div>
                            <div class="progress mt-2" style="height:4px;border-radius:2px;background:#e9ecef;">
                                <div class="progress-bar {{ $ratingFill >= 80 ? 'bg-success' : ($ratingFill < 50 ? 'bg-danger' : 'bg-warning') }}" style="width:{{ $ratingFill }}%;border-radius:2px;"></div>
                            </div>
                        </div>

                        {{-- 2. Feedback Uptake Rate --}}
                        <div class="d-flex flex-column justify-content-center p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <p class="crm-label-muted text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing:.06em;">
                                <i class="mdi mdi-clipboard-check-outline mr-1 {{ $responseRate >= 70 ? 'text-success' : ($responseRate < 40 ? 'text-danger' : 'text-warning') }}"></i>Feedback Uptake Rate
                            </p>
                            <div class="d-flex align-items-baseline">
                                <span class="font-weight-bold {{ $responseRate >= 70 ? 'text-success' : ($responseRate < 40 ? 'text-danger' : 'text-warning') }}" style="font-size:2rem;line-height:1;">{{ $responseRate }}%</span>
                            </div>
                            <div class="progress mt-1" style="height:3px;border-radius:2px;background:#e9ecef;">
                                <div class="progress-bar {{ $responseRate >= 70 ? 'bg-success' : ($responseRate < 40 ? 'bg-danger' : 'bg-warning') }}" style="width:{{ min(100,$responseRate) }}%;border-radius:2px;"></div>
                            </div>
                            <small class="crm-label-muted d-block mt-1" style="font-size: 0.65rem;">{{ $submittedCount }} submitted of {{ $totalCount }} sent</small>
                        </div>

                    </div> {{-- End Left Column --}}

                    {{-- Right Column: Critical Review (Action - Collapsible) --}}
                    <div class="col-md-7 pl-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-uppercase font-weight-bold text-dark mb-0" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="mdi mdi-alert-circle-outline mr-1 text-muted"></i> Lowest Rated Feedback (Threshold <= 1.5)
                            </h6>
                        </div>
                        
                        {{-- Vertical Scrollable Container (Top Down) --}}
                        <div id="criticalFeedbackList" class="d-flex flex-column overflow-y-auto pr-2 custom-vertical-scroll" style="gap: 12px; max-height: 250px; scroll-behavior: smooth;">
                            <style>
                                .custom-vertical-scroll::-webkit-scrollbar { width: 4px; }
                                .custom-vertical-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
                                .custom-vertical-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
                                .custom-vertical-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
                            </style>
                            @forelse($topLowestFeedbacks ?? [] as $lowestFb)
                                <div class="d-flex justify-content-between align-items-center p-3 rounded bg-white border shadow-sm" style="border-color: #e2e8f0; width: 100%;">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $lowestFb['code'] ?? 'Ref: ' . $lowestFb['id'] }}</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $lowestFb['service_type'] ?? 'Unknown' }} | Avg: <span class="font-weight-bold text-danger">{{ $lowestFb['avg_score'] ?? 0 ? round((float)($lowestFb['avg_score'] ?? 0) / 4 * 100) : 0 }}%</span></small>
                                    </div>
                                    <button class="btn btn-sm btn-light border py-1 px-3 text-dark font-weight-medium" style="font-size: 0.7rem;" wire:click.prevent="viewFeedback({{ $lowestFb['id'] }})">View</button>
                                </div>
                            @empty
                                <div class="text-center p-4 rounded w-100" style="background:#f8fafc; border: 1px dashed #e2e8f0;">
                                    <small class="text-muted d-block"><i class="mdi mdi-check-circle-outline text-success mr-1"></i>No feedback below 1.5 threshold</small>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

            {{-- Tier 2: Diagnostic Breakdown (Bottom Row - Compact 2-Col Grid) --}}
            <div class="crm-intel-body pt-4" style="border-top:1px dashed #e8eaf0;">
                <h6 class="text-uppercase font-weight-bold text-dark mb-4" style="font-size: 0.7rem; letter-spacing: 0.05em;">Service Dimension Health (Sorted Priority)</h6>
                <div class="row dimension-list">
                    @foreach($sortedDimensions as $label => $data)
                        @php 
                            $score = $data['average'];
                            $max = $data['max'];
                            $pct = $max > 0 ? round(($score / $max) * 100) : 0;
                            // Thresholds for color (Excellent >= 80%, Good >= 60%)
                            $dimColor = $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger');
                            // Text label for score (e.g. 2.8/4 = Good)
                            $dimLabel = $pct >= 80 ? 'Excellent' : ($pct >= 60 ? 'Good' : ($pct >= 40 ? 'Fair' : ($pct >= 20 ? 'Poor' : 'Very Poor')));
                        @endphp
                        <div class="col-md-6 col-lg-5 mb-2 pr-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-1 pb-1">
                                <span class="text-dark font-weight-medium" style="font-size: 0.85rem; min-width: 140px;">{{ $label }}</span>
                                <div class="d-flex align-items-center flex-grow-1 mx-3">
                                    <div class="progress w-100" style="height:6px; border-radius:3px; background:#f0f1f4;">
                                        @if($score > 0)
                                            <div class="progress-bar {{ $dimColor }}" style="width:{{ $pct }}%; border-radius:3px;"></div>
                                        @endif
                                    </div>
                                </div>
                                @if($score > 0)
                                    <span class="font-weight-bold {{ str_replace('bg-', 'text-', $dimColor) }}" style="font-size: 0.85rem; min-width: 100px; text-align: right;" title="{{ $dimLabel }}">
                                        {{ $pct }}%
                                        <small class="font-weight-normal text-muted" style="font-size: 0.65rem;">({{ $dimLabel }})</small>
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 0.65rem; font-style: italic; white-space: nowrap;">
                                        Awaiting Data
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
        @endif


        <div class="crm-filter-bar crm-filter-bar-sticky mx-4 mb-3">
            <h5 class="mb-3 font-weight-bold text-secondary border-bottom pb-2"><i class="mdi mdi-filter-variant"></i> Filters</h5>
            <div class="row align-items-center">
                <!-- Search input on Left -->
                <div class="col-md-5 mb-3 mb-md-0">
                    <div class="crm-search-wrapper w-100">
                        <i class="mdi mdi-magnify crm-search-icon"></i>
                        <input type="text" class="form-control w-100" placeholder="Search by client, code, or keyword..."
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Filters & Show Entries on Right -->
                <div class="col-md-7 d-flex justify-content-md-end align-items-center flex-wrap filter-row">
                    <!-- Filter by Status -->
                    <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                        <label class="mb-0 mr-2 crm-filter-label text-nowrap d-none d-md-inline">Filter by Status</label>
                        <select class="crm-select custom-select-sm no-select2" style="width: 140px;" wire:model.live="activeTab"
                            wire:key="feedback-status-filter">
                            <option value="all">All Feedbacks</option>
                            <option value="submitted">Submitted</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <!-- Show Entries -->
                    <div class="d-flex align-items-center mb-2 mb-md-0" wire:loading.class="opacity-50" wire:target="perPage">
                        <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                        <select wire:model.live="perPage" wire:key="feedback-per-page-select"
                            class="crm-select custom-select-sm no-select2" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
                    </div>
                </div>
            </div>
        </div>

        <x-crm.data-table class="mx-4 crm-loading-overlay" wire:key="feedback-table-{{ $activeTab ?? 'all' }}" wire:loading.class="opacity-50" wire:target="activeTab,search,perPage">
            <x-slot:header>
                <tr>
                    <th style="width: 80px;">Actions</th>
                    <th style="width: 100px;">No</th>
                    <th style="width: 120px;">Date</th>
                    <th>Client</th>
                    <th>Feedback Snippet</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </x-slot:header>
                        @forelse($feedbacks as $feedback)
                            @php
                                $snippet = $feedback->suggestions ?? $feedback->issue_description ?? '';
                                $snippet = strlen($snippet) > 80 ? substr(strip_tags($snippet), 0, 80) . '…' : strip_tags($snippet);
                            @endphp
                            <tr wire:key="feedback-{{ $feedback->id }}" wire:click="viewFeedback({{ $feedback->id }})" class="crm-table-row-clickable">
                                <td wire:click.stop>
                                    <div class="crm-action-buttons">
                                        <button class="btn btn-sm crm-btn crm-btn-view" wire:click="viewFeedback({{ $feedback->id }})" title="View"><i class="mdi mdi-eye"></i></button>
                                        @if($feedback->status == \App\Models\CRM\CustomerFeedback::STATUS_PENDING)
                                            <button wire:click="confirmResend({{ $feedback->id }})" wire:loading.attr="disabled" class="btn btn-sm crm-btn crm-btn-warning" title="Resend">
                                                <span wire:loading.remove wire:target="confirmResend({{ $feedback->id }})"><i class="mdi mdi-email-sync"></i></span>
                                                <span wire:loading wire:target="confirmResend({{ $feedback->id }})"><i class="mdi mdi-loading mdi-spin"></i></span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    <span class="font-weight-bold">{{ $feedback->code ?? 'FB' . str_pad($feedback->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    @if(!empty($feedback->results_issued_date))
                                        {{ date('d M Y', strtotime($feedback->results_issued_date)) }}
                                    @elseif(!empty($feedback->date) && $feedback->date != '0000-00-00 00:00:00')
                                        {{ date('d M Y', strtotime($feedback->date)) }}
                                    @elseif($feedback->created_at)
                                        {{ $feedback->created_at->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($feedback->contact && $feedback->contact->email)
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('show-customer', ['id' => $feedback->customer_id]) }}" class="font-weight-bold text-dark text-decoration-none" wire:click.stop>
                                                {{ $feedback->customer->name ?? $feedback->received_from }}
                                            </a>
                                            <small class="text-muted">{{ $feedback->contact->email }}
                                            @if($feedback->contact->first_name)
                                                ({{ $feedback->contact->first_name }})
                                            @endif
                                            </small>
                                        </div>
                                    @elseif($feedback->customer)
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('show-customer', ['id' => $feedback->customer->id]) }}" class="font-weight-bold text-dark text-decoration-none" wire:click.stop>
                                                {{ $feedback->customer->name }}
                                            </a>
                                            <small class="text-muted">{{ $feedback->customer->email }}</small>
                                        </div>
                                    @else
                                        {{ $feedback->received_from }}
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $snippet ?: '—' }}</td>
                                <td>
                                    @if($feedback->status == \App\Models\CRM\CustomerFeedback::STATUS_SUBMITTED)
                                        <span class="crm-badge crm-badge-submitted">Submitted</span>
                                    @elseif($feedback->status == \App\Models\CRM\CustomerFeedback::STATUS_PENDING)
                                        <span class="crm-badge crm-badge-pending">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-crm.empty-state
                                        icon="mdi-file-account-outline"
                                        message="No feedbacks found."
                                        help="Try adjusting your filters or search to get started."
                                    />
                                </td>
                            </tr>
                        @endforelse
        </x-crm.data-table>

        <div wire:loading wire:target="search,perPage,activeTab" class="crm-loading-indicator mx-4">
            <i class="mdi mdi-loading mdi-spin"></i> Loading...
        </div>

        <x-crm.pagination :summary="'Showing ' . ($feedbacks->firstItem() ?? 0) . ' to ' . ($feedbacks->lastItem() ?? 0) . ' of ' . $feedbacks->total() . ' results'">
            {{ $feedbacks->links() }}
        </x-crm.pagination>

        @if($showForm)
            @livewire(\App\Livewire\Crm\Feedback\FeedbackForm::class, [
                'feedbackId' => $editingFeedbackId ?? null,
                'customerId' => null
            ], 'feedback-form-' . ($editingFeedbackId ?? 'new'))
        @endif

        @livewire(\App\Livewire\Crm\Feedback\SendFeedbackCampaign::class, [], 'send-feedback-campaign')
        </div>
    </main>



    <!-- Read-Only Feedback Modal (legacy) -->
    @teleport('body')
    <div class="modal fade" id="viewFeedbackModal" x-on:click.self="$('#viewFeedbackModal').modal('hide')" tabindex="-1"
        role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 d-flex align-items-center justify-content-center rounded" style="width:32px;height:32px;background:#eef2ff;flex-shrink:0;">
                            <i class="mdi mdi-clipboard-text-outline text-primary" style="font-size:1.1rem;"></i>
                        </span>
                        <div>
                            <span class="font-weight-bold text-dark" style="font-size:1rem;">Feedback Details</span>
                            <small class="text-muted d-block" style="font-size:0.75rem;">
                                Ref: {{ $selectedFeedback ? 'FB' . str_pad($selectedFeedback->id, 5, '0', STR_PAD_LEFT) : '---' }}
                            </small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    @if($selectedFeedback)
                        @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $selectedFeedback])
                    @else
                        <div class="text-center p-5">
                            <i class="mdi mdi-loading mdi-spin display-4 text-primary"></i>
                            <p class="mt-3 text-muted">Loading feedback details...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
 
    <!-- Category Details Modal -->
    <div class="modal fade" id="categoryDetailsModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="mdi mdi-chart-bar mr-1"></i> {{ $categoryDetails['label'] ?? 'Category' }} Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($categoryDetails)
                        <!-- Summary -->
                        <div class="text-center mb-4">
                            <h1 class="font-weight-bold text-primary display-4">{{ $categoryDetails['average'] }}</h1>
                            <div class="text-muted">Average Score</div>
                            <small class="text-muted">Based on {{ $categoryDetails['total'] }} responses</small>
                        </div>

                        <!-- Histogram -->
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3 border-bottom pb-2">Rating Distribution</h6>
                        <div class="mb-4">
                            @foreach([5 => ['label' => 'Excellent', 'color' => 'success'], 4 => ['label' => 'Good', 'color' => 'info'], 3 => ['label' => 'Fair', 'color' => 'warning'], 2 => ['label' => 'Poor', 'color' => 'danger'], 1 => ['label' => 'Very Poor', 'color' => 'danger']] as $score => $cfg)
                                @php 
                                    $count = $categoryDetails['histogram'][$score] ?? 0;
                                    $percent = $categoryDetails['total'] > 0 ? ($count / $categoryDetails['total']) * 100 : 0;
                                @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-muted small mr-2" style="width: 60px;">{{ $cfg['label'] }}</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $cfg['color'] }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-muted small ml-2" style="width: 30px; text-align: right;">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if(($categoryDetails['low_rating_count'] ?? 0) > 0)
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-warning btn-sm"
                                wire:click="filterByLowRatingCategory('{{ $categoryDetails['category_key'] ?? '' }}')">
                                <i class="mdi mdi-filter"></i> View {{ $categoryDetails['low_rating_count'] }} feedback(s) with Fair/Poor rating
                            </button>
                        </div>
                        @endif

                        <!-- Recent Comments -->
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3 border-bottom pb-2">Recent Comments</h6>
                        @if(collect($categoryDetails['comments'])->isNotEmpty())
                            <ul class="list-unstyled">
                                @foreach($categoryDetails['comments'] as $comment)
                                    <li class="media mb-3 border-bottom pb-2">
                                        <i class="mdi mdi-comment-text-outline text-muted mr-2 mt-1"></i>
                                        <div class="media-body">
                                            <p class="mb-1 text-dark small" style="font-size: 0.9rem;">
                                                "{{ $comment->suggestions ?? $comment->issue_description ?? $comment->iso_concerns_description }}"
                                            </p>
                                            <small class="text-muted">
                                                - {{ $comment->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted small font-italic text-center">No qualitative comments found for this category.</p>
                        @endif
                    @else
                        <div class="text-center p-4">
                            <i class="mdi mdi-loading mdi-spin text-primary"></i> Loading...
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resend Confirmation Modal -->
    <div class="modal fade" id="resendConfirmationModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="mdi mdi-alert-circle-outline mr-1"></i> Confirm Resend
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="mdi mdi-email-sync text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-3">Are you sure you want to resend this feedback request?</h5>
                    <p class="text-muted">This will send an email to the customer with a fresh link to provide their feedback.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:loading.attr="disabled">Cancel</button>
                    <button type="button" class="btn btn-warning" wire:click="resendFeedback" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="resendFeedback">
                            <i class="mdi mdi-send mr-1"></i> Yes, Resend Email
                        </span>
                        <span wire:loading wire:target="resendFeedback">
                            <i class="mdi mdi-loading mdi-spin mr-1"></i> Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var feedbackFormSelect2Bound = false;

            function bindFeedbackFormSelect2Events() {
                if (feedbackFormSelect2Bound) return;
                if (!window.Livewire) return;

                feedbackFormSelect2Bound = true;

                Livewire.on('show-feedback-modal', () => {
                    // Select2 initialization is now handled by the Alpine.js component in feedback-form
                });
             }
            document.addEventListener('livewire:initialized', function() {
                bindFeedbackFormSelect2Events();
                
                // Also bind view modal event
                Livewire.on('open-view-modal', () => {
                    $('#viewFeedbackModal').modal('show');
                });

                Livewire.on('open-resend-confirmation', () => {
                    $('#resendConfirmationModal').modal('show');
                });

                Livewire.on('close-resend-confirmation', () => {
                    $('#resendConfirmationModal').modal('hide');
                });
                
                // Category Modal
                Livewire.on('open-category-modal', () => {
                    $('#categoryDetailsModal').modal('show');
                });

                Livewire.on('close-category-modal', () => {
                    $('#categoryDetailsModal').modal('hide');
                });
            });

            if (window.Livewire) {
                bindFeedbackFormSelect2Events();
            }
        })();
    </script>
</div>