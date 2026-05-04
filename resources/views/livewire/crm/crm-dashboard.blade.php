@section('title2')
    <title>{{ __('crm.dashboard') }} | {{ __('crm.module_name') }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"
        integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg=="
        crossorigin="anonymous"></script>
@endsection

<main>
    @php
        $breadcrumbItems = [
            ['link' => route('crm-dashboard'), 'name' => __('crm.module_name'), 'icon' => null],
            ['link' => route('crm-dashboard'), 'name' => __('crm.dashboard'), 'icon' => null],
        ];
        $delta = $this->complaintsTrendDelta;
        $nps = $this->feedbackNpsPercent;
        $hiPri = $this->highPriorityOpenComplaints;
        $upcomingBookings = $this->labBookingsUpcoming;
        $openCount = $this->openComplaints;
        $npsAccent = $nps >= 70 ? 'success' : ($nps < 30 ? 'danger' : 'warning');
    @endphp
    <div class="container-fluid">
        <x-crm.page-header
            :breadcrumbItems="$breadcrumbItems"
            :title="__('crm.operations_centre')"
            :subtitle="__('crm.realtime_overview')"
            icon="mdi-view-dashboard"
        />

        {{-- SECTION 1: 4 KPI Cards --}}
        <div class="px-4 mb-4">
            <div class="row" style="row-gap:16px;">

                <div class="col-6 col-md-3">
                    <x-crm.stat-card
                        :label="__('crm.open_complaints')"
                        :value="$openCount"
                        :sublabel="__('crm.awaiting_resolution')"
                        icon="mdi-alert-circle-outline"
                        :accent="$openCount > 0 ? 'danger' : 'success'"
                    >
                        @if($hiPri > 0)
                            <x-slot:badge>
                                <span class="dash-delta up"><i class="mdi mdi-flag mr-1"></i>{{ $hiPri }} {{ __('crm.high') }}</span>
                            </x-slot:badge>
                        @endif
                    </x-crm.stat-card>
                </div>

                <div class="col-6 col-md-3">
                    <x-crm.stat-card
                        :label="__('crm.upcoming_lab_bookings')"
                        :value="$upcomingBookings"
                        :sublabel="__('crm.scheduled_engagements')"
                        icon="mdi-calendar-clock"
                        accent="primary"
                    >
                        @if($upcomingBookings > 0)
                            <x-slot:badge>
                                <span class="dash-delta flat">{{ $upcomingBookings }} {{ __('crm.active') }}</span>
                            </x-slot:badge>
                        @endif
                    </x-crm.stat-card>
                </div>

                <div class="col-6 col-md-3">
                    <x-crm.stat-card
                        :href="route('feedback-home')"
                        :label="__('crm.all_customer_feedbacks')"
                        :value="$this->totalFeedback"
                        :sublabel="__('crm.total_received')"
                        icon="mdi-comment-multiple-outline"
                        accent="primary"
                    />
                </div>

                <div class="col-6 col-md-3">
                    <x-crm.stat-card
                        :href="route('feedback-home')"
                        :label="__('crm.client_loyalty_index')"
                        :value="$nps . '%'"
                        :sublabel="__('crm.overall_recommendation_score')"
                        icon="mdi-account-heart-outline"
                        :accent="$npsAccent"
                    />
                </div>

            </div>
        </div>

    {{-- ── SECTION 2: Action Queue ──────────────────────────────── --}}
    <div class="px-4 mb-5">
        <div class="crm-dash-section-header">
            <div class="crm-dash-section-icon" style="background: var(--crm-warning-light);">
                <i class="mdi mdi-clock-alert-outline text-warning"></i>
            </div>
            <div>
                <div class="crm-dash-section-title">{{ __('crm.action_queue') }}</div>
                <div class="crm-dash-section-subtitle">{{ __('crm.critical_items_attention') }}</div>
            </div>
        </div>
        <div class="row" style="row-gap:16px;">

            @php $cna = $this->complaintsNeedingApproval; @endphp
            <div class="col-sm-6 col-md-4 col-xl-3">
                <a href="{{ route('crm.complaints-manager', ['stage' => 'Active Investigations']) }}" class="crm-action-card">
                    <div class="crm-action-card-content">
                        <span class="crm-action-card-label">
                            <i class="mdi mdi-check-decagram-outline text-primary"></i>
                            {{ __('crm.complaints_awaiting_approval') }}
                        </span>
                    </div>
                    <span class="badge {{ $cna > 0 ? 'badge-danger' : 'badge-light border' }} badge-pill" style="font-size:0.75rem;padding:4px 10px;">{{ $cna }}</span>
                    <i class="mdi mdi-chevron-right crm-action-card-arrow"></i>
                </a>
            </div>

            @php $rpa = $this->resolutionsPendingApproval; @endphp
            <div class="col-sm-6 col-md-4 col-xl-3">
                <a href="{{ route('crm.complaints-manager', ['stage' => 'Pending Closure']) }}" class="crm-action-card">
                    <div class="crm-action-card-content">
                        <span class="crm-action-card-label">
                            <i class="mdi mdi-file-check-outline text-success"></i>
                            {{ __('crm.resolutions_pending_sign_off') }}
                        </span>
                    </div>
                    <span class="badge {{ $rpa > 0 ? 'badge-warning' : 'badge-light border' }} badge-pill" style="font-size:0.75rem;padding:4px 10px;">{{ $rpa }}</span>
                    <i class="mdi mdi-chevron-right crm-action-card-arrow"></i>
                </a>
            </div>

            <div class="col-sm-6 col-md-4 col-xl-3">
                <a href="{{ route('crm.complaints-manager', ['stage' => 'Verification Review & CAPA']) }}" class="crm-action-card">
                    <div class="crm-action-card-content">
                        <span class="crm-action-card-label">
                            <i class="mdi mdi-file-document-edit-outline text-info"></i>
                            {{ __('crm.active_resolutions_in_progress') }}
                        </span>
                    </div>
                    @php $resInProgress = getComplaintsInWorkflow(3); @endphp
                    <span class="badge {{ $resInProgress > 0 ? 'badge-info' : 'badge-light border' }} badge-pill" style="font-size:0.75rem;padding:4px 10px;">{{ $resInProgress }}</span>
                    <i class="mdi mdi-chevron-right crm-action-card-arrow"></i>
                </a>
            </div>

        </div>
    </div>

    {{-- ── SECTION 3: 2 Charts ─────────────────────────────────── --}}
    <div class="px-4 mb-5">
        <div class="row" style="row-gap:24px;">

            {{-- Chart 1: Feedback Sentiment --}}
            <div class="col-xl-6">
                <div class="crm-card crm-card-insight h-100">
                    <div class="crm-dash-card-header">
                        <div class="crm-dash-card-header-icon" style="background:rgba(99, 102, 241, 0.1);">
                            <i class="mdi mdi-emoticon-outline" style="color:#6366f1;"></i>
                        </div>
                        <div>
                            <div class="crm-dash-section-title" style="font-size:0.9rem;">{{ __('crm.feedbacks_by_service_types') }}</div>
                            <div class="crm-dash-section-subtitle" style="font-size:0.75rem;">{{ __('crm.client_sentiment_across_service_categories') }}</div>
                        </div>
                    </div>
                    <div class="crm-card-body">
                        <canvas id="chart-sentiment" style="height:220px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Complaints vs Samples --}}
            <div class="col-xl-6">
                <div class="crm-card crm-card-insight h-100">
                    <div class="crm-dash-card-header">
                        <div class="crm-dash-card-header-icon" style="background:rgba(37, 99, 235, 0.1);">
                            <i class="mdi mdi-chart-line" style="color:#2563eb;"></i>
                        </div>
                        <div>
                            <div class="crm-dash-section-title" style="font-size:0.9rem;">{{ __('crm.complaints_vs_sample_volume') }} <span class="text-muted font-weight-normal">{{ __('crm.last_6_months') }}</span></div>
                            <div class="crm-dash-section-subtitle" style="font-size:0.75rem;">{{ __('crm.sample_volume_vs_complaints_hint') }}</div>
                        </div>
                    </div>
                    <div class="crm-card-body">
                        <canvas id="chart-dual" style="height:220px;"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── SECTION 4: Recent Operations Feed ────────────────────── --}}
    <div class="px-4 mb-5">

        <div class="crm-dash-section-header">
            <div class="crm-dash-section-icon" style="background:#f1f5f9;">
                <i class="mdi mdi-history text-muted"></i>
            </div>
            <div>
                <div class="crm-dash-section-title">{{ __('crm.recent_operations_feed') }}</div>
                <div class="crm-dash-section-subtitle">{{ __('crm.critical_crm_actions_log') }}</div>
            </div>
        </div>

        <div class="crm-card crm-card-insight">

            {{-- SLA alert banner --}}
            @php $overdue = $this->overdueComplaints; @endphp
            @if($overdue > 0)
                <div class="d-flex align-items-center px-4 py-3"
                    style="background:#fff5f5;border-bottom:1px solid #fed7d7;font-size:0.875rem;">
                    <i class="mdi mdi-timer-alert-outline mr-3 text-danger" style="font-size:1.25rem;flex-shrink:0;"></i>
                    <span class="text-danger flex-grow-1">
                        {{ trans_choice('crm.complaints_exceeded_sla', $overdue, ['count' => $overdue, 'days' => 14]) }}
                    </span>
                    <a href="{{ route('crm.complaints-manager', ['stage' => 'Log & Intake']) }}"
                        class="btn btn-sm crm-btn crm-btn-danger" style="font-size:0.75rem;">
                        {{ __('crm.review_now') }} &rarr;
                    </a>
                </div>
            @endif

            <div class="crm-activity-feed">
                @php
                    $timelineItems = collect($this->intelligentTimeline)
                        ->filter(fn($e) => ($e['type'] ?? '') !== 'system_alert')
                        ->take(6);
                @endphp

                @forelse($timelineItems as $entry)
                    @php
                        $evClass = match ($entry['event'] ?? '') {
                            'created' => 'ev-created',
                            'updated' => 'ev-updated',
                            'deleted' => 'ev-deleted',
                            default   => 'ev-updated',
                        };
                        $verb = match ($entry['event'] ?? '') {
                            'created' => __('crm.logged'),
                            'updated' => __('crm.updated'),
                            'deleted' => __('crm.removed'),
                            default   => __('crm.modified'),
                        };
                        $cnt = $entry['count'] ?? 1;
                    @endphp
                    <div class="crm-activity-item {{ $evClass }}">
                        <div class="crm-activity-avatar">{{ $entry['initial'] ?? '?' }}</div>
                        <div class="crm-activity-content">
                            <div class="crm-activity-text">
                                <strong>{{ $entry['user'] }}</strong>
                                {{ $cnt > 1 ? "$verb $cnt" : $verb }}
                                <strong>{{ $entry['model'] }}</strong> {{ __('crm.records') }}
                            </div>
                            <span class="crm-activity-time">
                                <i class="mdi mdi-clock-outline mr-1"></i>
                                {{ $entry['created_at']?->diffForHumans() ?? '-' }}
                            </span>
                        </div>
                        @if($cnt > 1)
                            <span class="crm-activity-count">×{{ $cnt }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-3" style="opacity:0.2;">
                            <i class="mdi mdi-history" style="font-size:3.5rem;"></i>
                        </div>
                        <div class="crm-dash-section-title">{{ __('crm.no_recent_activity') }}</div>
                        <div class="crm-dash-section-subtitle">{{ __('crm.activity_feed_waiting_hint') }}</div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
    </div>
</main>

@section('script2')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            // ── Chart 1: Feedback Sentiment by Service (Stacked Bar) ──────────
            var sentimentRaw = @json($this->feedbackSentimentByService);
            var sentLabels = Object.keys(sentimentRaw);
            var ctx1 = document.getElementById('chart-sentiment');
            if (ctx1) {
                new Chart(ctx1.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: sentLabels.length > 0 ? sentLabels : ['{{ __('crm.no_data_collected') }}'],
                        datasets: [
                            { label: '{{ __('crm.positive') }}', data: sentLabels.map(k => sentimentRaw[k].positive), backgroundColor: 'rgba(56,161,105,.7)', stack: 's' },
                            { label: '{{ __('crm.neutral') }}', data: sentLabels.map(k => sentimentRaw[k].neutral), backgroundColor: 'rgba(160,174,192,.5)', stack: 's' },
                            { label: '{{ __('crm.negative') }}', data: sentLabels.map(k => sentimentRaw[k].negative), backgroundColor: 'rgba(229,62,62,.65)', stack: 's' },
                        ]
                    },
                    options: {
                        legend: { position: 'bottom', labels: { fontSize: 10, boxWidth: 10 } },
                        scales: {
                            xAxes: [{ stacked: true, gridLines: { display: false }, ticks: { fontSize: 10 } }],
                            yAxes: [{ stacked: true, ticks: { beginAtZero: true, precision: 0, fontSize: 10 }, gridLines: { color: 'rgba(0,0,0,.04)' } }]
                        }
                    }
                });
            }

            // ── Chart 2: Complaints vs Sample Volume (Dual-Axis) ─────────
            var dualData = @json($this->complaintsVsSamplesTrend);
            var ctx2 = document.getElementById('chart-dual');
            if (ctx2) {
                new Chart(ctx2.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dualData.labels || [],
                        datasets: [
                            {
                                label: '{{ __('crm.complaints') }}',
                                data: dualData.complaints || [],
                                borderColor: 'rgba(229,62,62,.9)',
                                backgroundColor: 'rgba(229,62,62,.07)',
                                borderWidth: 2, fill: true, yAxisID: 'y-c',
                                pointRadius: 3, pointBackgroundColor: 'rgba(229,62,62,.9)',
                            },
                            {
                                label: '{{ __('crm.sample_volume') }}',
                                data: dualData.samples || [],
                                borderColor: 'rgba(37,99,235,.9)',
                                backgroundColor: 'rgba(37,99,235,.07)',
                                borderWidth: 2, fill: true, yAxisID: 'y-s',
                                pointRadius: 3, pointBackgroundColor: 'rgba(37,99,235,.9)',
                            }
                        ]
                    },
                    options: {
                        legend: { position: 'bottom', labels: { fontSize: 10, boxWidth: 10 } },
                        scales: {
                            xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 10 } }],
                            yAxes: [
                                { id: 'y-c', position: 'left', ticks: { beginAtZero: true, precision: 0, fontSize: 10, fontColor: '#e53e3e' }, gridLines: { color: 'rgba(0,0,0,.04)' }, scaleLabel: { display: true, labelString: '{{ __('crm.complaints') }}', fontColor: '#e53e3e', fontSize: 9 } },
                                { id: 'y-s', position: 'right', ticks: { beginAtZero: true, precision: 0, fontSize: 10, fontColor: '#2563eb' }, gridLines: { display: false }, scaleLabel: { display: true, labelString: '{{ __('crm.samples') }}', fontColor: '#2563eb', fontSize: 9 } }
                            ]
                        }
                    }
                });
            }
        });
    </script>
@endsection