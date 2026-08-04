<div class="container-fluid schedule-details-page lab-surface-theme lab-panel-theme" data-ls-type="plex">
    @php
        $viewProgress = $schedule->collectionProgress();
        $seriesOccurrences = $this->seriesOccurrences;
        $isSeries = $schedule->isPartOfRecurringSeries() && $seriesOccurrences->count() > 1;
        $collectedCount = $seriesOccurrences->filter(fn ($o) => (bool) $o->is_collected)->count();
        $calendar = $this->calendar;
        $fillSampleTypeId = $schedule->sample_type_id
            ?? collect($schedule->sample_details ?? [])->pluck('sample_type_id')->filter()->first();
        $vDetails = $this->resolveDetailedSampleDetails($schedule);
        $planHistories = $schedule->samplePlanHistories ?? collect();
        $viewContacts = $schedule->contacts();
    @endphp

    {{-- Flash / Livewire messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-circle mr-1"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if($message)
    <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
        <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} mr-1"></i>{{ $message }}
        <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
    </div>
    @endif

    {{-- Page header --}}
    <div class="workflow-board-panel sd-hero mb-3">
        <div class="workflow-board-panel-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:12px;">
                <div class="pr-md-3" style="min-width:0;flex:1 1 280px;">
                    <a href="{{ route('system-planner.schedule-sampling') }}" class="sd-back-link">
                        <i class="mdi mdi-arrow-left"></i> Back to Sampling Schedule
                    </a>
                    <h3 class="sd-title mb-2">
                        <i class="mdi mdi-calendar-check text-primary"></i>
                        {{ $schedule->title }}
                    </h3>
                    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                        <span class="sd-chip sd-chip--ok">
                            <i class="mdi mdi-clock-outline"></i>
                            {{ $schedule->sampling_datetime ? $schedule->sampling_datetime->format('D, d M Y · H:i') : 'Unscheduled' }}
                        </span>
                        <span class="sd-chip sd-chip--primary">
                            <i class="mdi mdi-refresh"></i>{{ $schedule->frequency ?: 'One-time' }}
                        </span>
                        @if($isSeries)
                        <span class="sd-chip sd-chip--series">
                            <i class="mdi mdi-repeat"></i>Recurring · {{ $seriesOccurrences->count() }} runs
                        </span>
                        @endif
                        @if($schedule->notify_client)
                        <span class="sd-chip sd-chip--warn"><i class="mdi mdi-bell-ring"></i>Client notified</span>
                        @endif
                        @if($viewProgress['status'] === 'collected')
                        <span class="sd-chip sd-chip--ok"><i class="mdi mdi-check-circle-outline"></i>{{ __('planner.collected') }}</span>
                        @elseif($viewProgress['status'] === 'partial')
                        <span class="sd-chip sd-chip--warn"><i class="mdi mdi-progress-clock"></i>{{ __('planner.partial') }} ({{ $viewProgress['label'] }})</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <a href="{{ route('system-planner.schedule-sampling.sample-collection-label', ['schedule' => $schedule->id]) }}"
                       class="btn btn-outline-primary btn-sm sd-btn"
                       target="_blank" rel="noopener">
                        <i class="mdi mdi-printer mr-1"></i>Sample label
                    </a>
                    @if($fillSampleTypeId)
                    <a href="{{ route('system-planner.fill-sampling-forms.fill', ['sampleType' => $fillSampleTypeId, 'schedule' => $schedule->id]) }}"
                       class="btn btn-outline-info btn-sm sd-btn">
                        <i class="mdi mdi-clipboard-edit-outline mr-1"></i>Fill form
                    </a>
                    @else
                    <a href="{{ route('system-planner.fill-sampling-forms', ['schedule' => $schedule->id]) }}"
                       class="btn btn-outline-info btn-sm sd-btn">
                        <i class="mdi mdi-clipboard-edit-outline mr-1"></i>Fill form
                    </a>
                    @endif
                    <a href="{{ route('system-planner.schedule-sampling', ['edit' => $schedule->id]) }}"
                       class="btn btn-primary btn-sm sd-btn">
                        <i class="mdi mdi-pencil mr-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards-row">
        <div class="stat-card">
            <div class="stat-card-label">Client</div>
            <div class="stat-card-content">
                <div class="stat-card-value" style="font-size:var(--ls-text-xl,1.05rem);">{{ $schedule->client->name ?? 'N/A' }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-domain"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Location</div>
            <div class="stat-card-content">
                <div class="stat-card-value" style="font-size:var(--ls-text-xl,1.05rem);">{{ $schedule->locationDisplayName() }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-map-marker-radius"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">{{ __('planner.samples_collected_vs_scheduled') }}</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $viewProgress['label'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-flask"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Personnel</div>
            <div class="stat-card-content">
                <div class="stat-card-value" style="font-size:var(--ls-text-xl,1.05rem);">{{ $schedule->personnelNames() }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-account-hard-hat"></i></div>
            </div>
        </div>
    </div>

    {{-- Scheduling Occurrence --}}
    @if($isSeries)
    <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
            <h5>
                <i class="mdi mdi-calendar-month-outline"></i>
                Scheduling Occurrence
                <span class="badge badge-light ml-1">{{ $seriesOccurrences->count() }}</span>
            </h5>
            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <span class="sd-chip sd-chip--ok" style="font-size:11px;">{{ $collectedCount }} collected</span>
                <span class="sd-chip" style="font-size:11px;">{{ $seriesOccurrences->count() - $collectedCount }} remaining</span>
                <button type="button"
                        class="btn btn-outline-danger btn-sm sd-btn"
                        wire:click="deleteSeries"
                        onclick="return confirm('Delete this recurring schedule and all {{ $seriesOccurrences->count() }} of its occurrences?')">
                    <i class="mdi mdi-trash-can-outline mr-1"></i>Delete series
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body">
            <p class="text-muted mb-3" style="font-size:var(--ls-text-md,0.875rem);max-width:62ch;">
                Each date below is one scheduled sampling run. Open a run to view its forms and details, or use the mini calendar to jump by month.
            </p>

            <div class="row">
                {{-- Mini calendar --}}
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="sd-mini-cal">
                        <div class="sd-mini-cal__header">
                            <button type="button" class="sd-mini-cal__nav" wire:click="previousMonth" title="Previous month">
                                <i class="mdi mdi-chevron-left"></i>
                            </button>
                            <div class="sd-mini-cal__label">{{ $calendar['label'] }}</div>
                            <button type="button" class="sd-mini-cal__nav" wire:click="nextMonth" title="Next month">
                                <i class="mdi mdi-chevron-right"></i>
                            </button>
                        </div>
                        <div class="sd-mini-cal__weekdays">
                            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $wd)
                            <span>{{ $wd }}</span>
                            @endforeach
                        </div>
                        <div class="sd-mini-cal__grid">
                            @foreach($calendar['weeks'] as $week)
                                @foreach($week as $cell)
                                    @if($cell === null)
                                    <div class="sd-mini-cal__cell sd-mini-cal__cell--empty"></div>
                                    @else
                                    @php
                                        $hasOcc = count($cell['occurrences']) > 0;
                                        $cellStatus = $hasOcc ? ($cell['occurrences'][0]['status'] ?? 'pending') : null;
                                        $isToday = $cell['date'] === now()->format('Y-m-d');
                                        $isCurrentDay = collect($cell['occurrences'])->contains(fn ($o) => $o['is_current']);
                                        $firstId = $hasOcc ? $cell['occurrences'][0]['id'] : null;
                                    @endphp
                                    <div class="sd-mini-cal__cell
                                        {{ $hasOcc ? 'sd-mini-cal__cell--has' : '' }}
                                        {{ $hasOcc ? 'sd-mini-cal__cell--'.$cellStatus : '' }}
                                        {{ $isToday ? 'sd-mini-cal__cell--today' : '' }}
                                        {{ $isCurrentDay ? 'sd-mini-cal__cell--current' : '' }}">
                                        @if($firstId)
                                        <a href="{{ route('system-planner.schedule-sampling.show', ['schedule' => $firstId]) }}"
                                           class="sd-mini-cal__day"
                                           title="{{ count($cell['occurrences']) }} scheduling occurrence{{ count($cell['occurrences']) === 1 ? '' : 's' }}">
                                            <span class="sd-mini-cal__num">{{ $cell['day'] }}</span>
                                            @if($hasOcc)
                                            <span class="sd-mini-cal__dot"></span>
                                            @endif
                                        </a>
                                        @else
                                        <span class="sd-mini-cal__day">
                                            <span class="sd-mini-cal__num">{{ $cell['day'] }}</span>
                                        </span>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                        <div class="sd-mini-cal__legend">
                            <span><i class="sd-dot sd-dot--pending"></i> Pending</span>
                            <span><i class="sd-dot sd-dot--partial"></i> Partial</span>
                            <span><i class="sd-dot sd-dot--collected"></i> Collected</span>
                            <span><i class="sd-dot sd-dot--current"></i> Viewing</span>
                        </div>
                    </div>
                </div>

                {{-- Occurrence list --}}
                <div class="col-lg-7">
                    <div class="sd-occ-list">
                        @foreach($seriesOccurrences as $i => $occurrence)
                        @php
                            $isCurrent = (string) $occurrence->id === (string) $schedule->id;
                            $occFormCount = $occurrence->submissionFormInstances->count();
                            $occFillSampleTypeId = $occurrence->sample_type_id
                                ?? collect($occurrence->sample_details ?? [])->pluck('sample_type_id')->filter()->first();
                            $occStatus = $occurrence->is_collected ? 'collected' : ($occFormCount > 0 ? 'partial' : 'pending');
                        @endphp
                        <div class="sd-occ-item {{ $isCurrent ? 'sd-occ-item--current' : '' }}" wire:key="occ-{{ $occurrence->id }}">
                            <div class="sd-occ-item__index">{{ $i + 1 }}</div>
                            <div class="sd-occ-item__body">
                                <div class="sd-occ-item__when">
                                    <strong>{{ $occurrence->sampling_datetime?->format('d M Y') ?? '—' }}</strong>
                                    <span class="text-muted">{{ $occurrence->sampling_datetime?->format('H:i') }}</span>
                                    @if($isCurrent)
                                    <span class="badge badge-primary" style="font-size:10px;">Viewing</span>
                                    @endif
                                </div>
                                <div class="sd-occ-item__status">
                                    @if($occStatus === 'collected')
                                    <span class="sd-chip sd-chip--ok" style="font-size:11px;">Collected</span>
                                    @elseif($occStatus === 'partial')
                                    <span class="sd-chip sd-chip--warn" style="font-size:11px;">Partial</span>
                                    @else
                                    <span class="sd-chip" style="font-size:11px;">Pending</span>
                                    @endif
                                    <button type="button"
                                            class="ss-forms-btn {{ $occFormCount > 0 ? 'has-forms' : '' }}"
                                            wire:click="viewTrfForms('{{ $occurrence->id }}')"
                                            title="View forms for this scheduling occurrence">
                                        {{ $occFormCount }} form{{ $occFormCount === 1 ? '' : 's' }}
                                    </button>
                                </div>
                            </div>
                            <div class="sd-occ-item__actions">
                                <button type="button" class="ss-act ss-act--view" wire:click="goToOccurrenceMonth('{{ $occurrence->id }}')" title="Show on calendar">
                                    <i class="mdi mdi-calendar-search"></i>
                                </button>
                                @unless($isCurrent)
                                <a href="{{ route('system-planner.schedule-sampling.show', ['schedule' => $occurrence->id]) }}"
                                   class="ss-act ss-act--view" title="Open this scheduling occurrence">
                                    <i class="mdi mdi-eye-outline"></i>
                                </a>
                                @endunless
                                @if($occFillSampleTypeId)
                                <a href="{{ route('system-planner.fill-sampling-forms.fill', ['sampleType' => $occFillSampleTypeId, 'schedule' => $occurrence->id]) }}"
                                   class="ss-act ss-act--form" title="Fill sampling form"><i class="mdi mdi-clipboard-edit-outline"></i></a>
                                @else
                                <a href="{{ route('system-planner.fill-sampling-forms', ['schedule' => $occurrence->id]) }}"
                                   class="ss-act ss-act--form" title="Choose a form"><i class="mdi mdi-clipboard-edit-outline"></i></a>
                                @endif
                                <a href="{{ route('system-planner.schedule-sampling', ['edit' => $occurrence->id]) }}"
                                   class="ss-act ss-act--edit" title="Edit this scheduling occurrence"><i class="mdi mdi-pencil-outline"></i></a>
                                <button type="button"
                                        class="ss-act ss-act--delete"
                                        wire:click="deleteOccurrence('{{ $occurrence->id }}')"
                                        title="Delete this scheduling occurrence only"
                                        onclick="return confirm('Delete this scheduling occurrence ({{ $occurrence->sampling_datetime?->format('d M Y H:i') }})? The rest of the series is kept.')">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Left column: contacts + description + samples --}}
        <div class="col-lg-7">
            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header">
                    <h5><i class="mdi mdi-account-tie"></i> Contact(s)</h5>
                </div>
                <div class="workflow-board-panel-body">
                    @if($viewContacts->isNotEmpty())
                        <div class="row">
                            @foreach($viewContacts as $vc)
                            <div class="col-md-6 mb-2">
                                <div class="sd-contact-card">
                                    <strong>{{ trim(($vc->first_name ?? '').' '.($vc->last_name ?? '')) ?: 'Contact' }}</strong>
                                    @if(!empty($vc->email))
                                    <div class="text-muted small">{{ $vc->email }}</div>
                                    @endif
                                    @php $viewPhone = $vc->telephone ?: $vc->mobile; @endphp
                                    @if(!empty($viewPhone))
                                    <div class="text-muted small">{{ $viewPhone }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No contacts linked.</p>
                    @endif
                </div>
            </div>

            @if(!empty($vDetails))
            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header">
                    <h5><i class="mdi mdi-flask-outline"></i> Sample Details</h5>
                </div>
                <div class="workflow-board-panel-body">
                    @foreach($vDetails as $i => $d)
                    <div class="sd-sample-card {{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="sd-sample-card__head">
                            <span class="sd-sample-card__num">{{ $i + 1 }}</span>
                            <span class="font-weight-bold text-primary">{{ $d['type'] }}</span>
                        </div>
                        <div class="sd-sample-card__body">
                            <div class="mb-2">
                                <small class="sd-section-label">Analysis Type</small>
                                @if($d['analysis'])
                                <span class="badge badge-info px-2 py-1">{{ $d['analysis'] }}</span>
                                @else
                                <span class="text-muted font-italic">Not specified</span>
                                @endif
                            </div>
                            <div>
                                <small class="sd-section-label">Parameters ({{ $d['params_count'] }})</small>
                                @if(!empty($d['param_names']))
                                <div class="d-flex flex-wrap" style="gap:6px;">
                                    @foreach($d['param_names'] as $paramName)
                                    <span class="sd-param-chip">{{ $paramName }}</span>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-muted small">No parameters selected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($schedule->description)
            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header">
                    <h5><i class="mdi mdi-text"></i> Description</h5>
                </div>
                <div class="workflow-board-panel-body">
                    <p class="mb-0" style="white-space:pre-wrap;">{{ $schedule->description }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Right column: forms + history --}}
        <div class="col-lg-5">
            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header">
                    <h5>
                        <i class="mdi mdi-file-document-outline"></i>
                        Test Request Forms
                        <span class="badge badge-light ml-1">{{ $schedule->submissionFormInstances->count() }}</span>
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-info sd-btn" wire:click="viewTrfForms('{{ $schedule->id }}')">
                        View all
                    </button>
                </div>
                <div class="workflow-board-panel-body p-0">
                    @if($schedule->submissionFormInstances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 sd-table">
                            <thead>
                                <tr>
                                    <th>Form</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule->submissionFormInstances->take(8) as $instance)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold" style="font-size:13px;">{{ $instance->submissionForm?->name ?? 'Request Form' }}</div>
                                        <small class="text-muted">{{ $instance->selectedSampleTypeName() ?? $instance->submissionForm?->sampleTypes->first()?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td><span class="badge badge-success">{{ ucfirst($instance->status) }}</span></td>
                                    <td class="text-muted small">{{ $instance->submitted_at?->format('d M Y H:i') ?? $instance->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-3">
                        <div class="alert alert-light border mb-0" style="border-radius:8px;">
                            No test request forms have been filled for this scheduling occurrence yet.
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header">
                    <h5>
                        <i class="mdi mdi-history"></i>
                        Sample plan history
                        <span class="badge badge-light ml-1">{{ $planHistories->count() }}</span>
                    </h5>
                </div>
                <div class="workflow-board-panel-body">
                    @if($planHistories->isNotEmpty())
                        <div class="d-flex flex-column" style="gap:12px;">
                            @foreach($planHistories as $history)
                            @php
                                $before = is_array($history->display_before) ? $history->display_before : [];
                                $after = is_array($history->display_after) ? $history->display_after : [];
                                $beforeEntries = is_array($before['entries'] ?? null) ? $before['entries'] : [];
                                $afterEntries = is_array($after['entries'] ?? null) ? $after['entries'] : [];
                                $changedBy = $history->changedByUser?->name ?? 'Unknown user';
                            @endphp
                            <div class="sd-history-card">
                                <div class="sd-history-card__meta">
                                    <span><i class="mdi mdi-account-outline mr-1"></i>{{ $changedBy }}</span>
                                    <span>{{ optional($history->created_at)->format('d M Y H:i') }}</span>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="sd-section-label">Previously</small>
                                        <div class="small text-muted mb-1">Samples: <strong>{{ $before['number_of_samples'] ?? $history->number_of_samples_before ?? '—' }}</strong></div>
                                        @forelse($beforeEntries as $entry)
                                        <div class="sd-history-entry mb-1">
                                            <div class="font-weight-bold" style="font-size:12px;">{{ $entry['type'] ?? '—' }}</div>
                                            @if(!empty($entry['analysis']))
                                            <div class="text-muted" style="font-size:11px;">{{ $entry['analysis'] }}</div>
                                            @endif
                                        </div>
                                        @empty
                                        <span class="text-muted font-italic small">None</span>
                                        @endforelse
                                    </div>
                                    <div class="col-6">
                                        <small class="sd-section-label">Updated to</small>
                                        <div class="small text-muted mb-1">Samples: <strong>{{ $after['number_of_samples'] ?? $history->number_of_samples_after ?? '—' }}</strong></div>
                                        @forelse($afterEntries as $entry)
                                        <div class="sd-history-entry sd-history-entry--after mb-1">
                                            <div class="font-weight-bold text-primary" style="font-size:12px;">{{ $entry['type'] ?? '—' }}</div>
                                            @if(!empty($entry['analysis']))
                                            <div class="text-muted" style="font-size:11px;">{{ $entry['analysis'] }}</div>
                                            @endif
                                        </div>
                                        @empty
                                        <span class="text-muted font-italic small">None</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0" style="border-radius:8px;">
                            No sample-plan changes recorded yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TRF Forms Modal --}}
    @if($showTrfFormsModal && $this->viewingTrfSchedule)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="flex-shrink:0;background:var(--ls-color-primary,var(--color-primary));">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-file-document-multiple-outline mr-2"></i>Test Request Forms</h5>
                    <button type="button" class="close text-white" wire:click="closeTrfFormsModal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4" style="overflow-y:auto;flex:1 1 auto;">
                    <div class="mb-3">
                        <h5 class="font-weight-bold mb-1">{{ $this->viewingTrfSchedule->title }}</h5>
                        <div class="text-muted small">
                            {{ $this->viewingTrfSchedule->client->name ?? 'N/A' }}
                            · {{ $this->viewingTrfSchedule->sampling_datetime ? $this->viewingTrfSchedule->sampling_datetime->format('Y-m-d H:i') : 'N/A' }}
                        </div>
                    </div>
                    @if($this->viewingTrfSchedule->submissionFormInstances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Form Title / Sample Type</th>
                                    <th>Submitted By</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->viewingTrfSchedule->submissionFormInstances as $instance)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $instance->submissionForm?->name ?? 'Request Form' }}</div>
                                        <small class="text-muted">{{ $instance->selectedSampleTypeName() ?? $instance->submissionForm?->sampleTypes->first()?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $instance->submittedBy?->name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-success">{{ ucfirst($instance->status) }}</span></td>
                                    <td class="text-muted">{{ $instance->submitted_at?->format('M d, Y H:i') ?? $instance->created_at?->format('M d, Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border mb-0">No forms filled for this scheduling occurrence.</div>
                    @endif
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary" wire:click="closeTrfFormsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.schedule-details-page {
    padding-bottom: 2rem;
    font-family: var(--ls-font-sans, "IBM Plex Sans", system-ui, sans-serif);
    color: var(--ls-color-ink, #1e293b);
}
.schedule-details-page .sd-back-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: var(--ls-text-sm, 0.75rem);
    font-weight: 600;
    color: var(--ls-color-muted, #64748b);
    text-decoration: none;
    margin-bottom: 0.5rem;
}
.schedule-details-page .sd-back-link:hover { color: var(--ls-color-primary, var(--color-primary)); }
.schedule-details-page .sd-title {
    font-size: var(--ls-text-2xl, 1.25rem);
    font-weight: 700;
    color: var(--ls-color-ink, #1e293b);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.schedule-details-page .sd-btn {
    border-radius: var(--ls-radius-md, 8px);
    font-weight: 600;
}
.schedule-details-page .sd-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    background: var(--ls-color-bg, #f8fafc);
    color: var(--ls-color-muted, #64748b);
    border: 1px solid var(--ls-color-border, #e2e8f0);
}
.schedule-details-page .sd-chip--primary {
    background: var(--ls-color-primary-soft, var(--color-primary-soft));
    color: var(--ls-color-primary, var(--color-primary));
    border-color: var(--ls-color-primary-border, transparent);
}
.schedule-details-page .sd-chip--ok {
    background: var(--ls-color-success-soft, #dcfce7);
    color: var(--ls-color-success-text, #166534);
    border-color: transparent;
}
.schedule-details-page .sd-chip--warn {
    background: var(--ls-color-warning-soft, #fef3c7);
    color: var(--ls-color-warning-text, #92400e);
    border-color: transparent;
}
.schedule-details-page .sd-chip--series {
    background: #ede7f6;
    color: #4527a0;
    border-color: transparent;
}
.schedule-details-page .sd-section-label {
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
    font-size: 10px;
    color: var(--ls-color-muted, #64748b);
    margin-bottom: 4px;
}
.schedule-details-page .sd-contact-card,
.schedule-details-page .sd-history-card,
.schedule-details-page .sd-sample-card {
    background: var(--ls-color-bg, #f8fafc);
    border: 1px solid var(--ls-color-border, #e2e8f0);
    border-radius: var(--ls-radius-lg, 10px);
    padding: 12px;
}
.schedule-details-page .sd-sample-card__head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--ls-color-border, #e2e8f0);
}
.schedule-details-page .sd-sample-card__num {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--ls-color-primary, var(--color-primary));
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}
.schedule-details-page .sd-param-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    background: #fff;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    color: var(--ls-color-ink, #1e293b);
}
.schedule-details-page .sd-history-card__meta {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 12px;
    color: var(--ls-color-muted, #64748b);
}
.schedule-details-page .sd-history-entry {
    background: #fff;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    border-radius: 8px;
    padding: 8px;
}
.schedule-details-page .sd-history-entry--after {
    background: var(--ls-color-primary-soft, var(--color-primary-soft));
    border-color: var(--ls-color-primary-border, #e2b4b4);
}
.schedule-details-page .sd-table thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--ls-color-muted, #64748b);
    border-top: 0;
    background: var(--ls-color-bg, #f8fafc);
}
.schedule-details-page .sd-table td {
    vertical-align: middle;
    font-size: 13px;
}

/* Scheduling Occurrence list */
.schedule-details-page .sd-occ-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 420px;
    overflow-y: auto;
    padding-right: 4px;
}
.schedule-details-page .sd-occ-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #fff;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    border-radius: var(--ls-radius-lg, 10px);
    transition: border-color 0.15s, box-shadow 0.15s;
}
.schedule-details-page .sd-occ-item:hover {
    border-color: var(--ls-color-primary-border, #e2b4b4);
    box-shadow: var(--ls-shadow-sm, 0 1px 2px rgb(0 0 0 / 0.05));
}
.schedule-details-page .sd-occ-item--current {
    background: var(--ls-color-primary-soft, var(--color-primary-soft));
    border-color: var(--ls-color-primary-border, #e2b4b4);
}
.schedule-details-page .sd-occ-item__index {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--ls-color-bg, #f8fafc);
    color: var(--ls-color-muted, #64748b);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}
.schedule-details-page .sd-occ-item__body {
    flex: 1 1 auto;
    min-width: 0;
}
.schedule-details-page .sd-occ-item__when {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 13px;
}
.schedule-details-page .sd-occ-item__status {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}
.schedule-details-page .sd-occ-item__actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

/* Mini calendar */
.schedule-details-page .sd-mini-cal {
    background: #fff;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    border-radius: var(--ls-radius-xl, 12px);
    padding: 14px;
    box-shadow: var(--ls-shadow-sm, 0 1px 2px rgb(0 0 0 / 0.05));
}
.schedule-details-page .sd-mini-cal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.schedule-details-page .sd-mini-cal__label {
    font-weight: 700;
    font-size: var(--ls-text-lg, 0.95rem);
    color: var(--ls-color-ink, #1e293b);
}
.schedule-details-page .sd-mini-cal__nav {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    background: var(--ls-color-bg, #f8fafc);
    color: var(--ls-color-ink, #1e293b);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.schedule-details-page .sd-mini-cal__nav:hover {
    border-color: var(--ls-color-primary, var(--color-primary));
    color: var(--ls-color-primary, var(--color-primary));
}
.schedule-details-page .sd-mini-cal__weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 6px;
}
.schedule-details-page .sd-mini-cal__weekdays span {
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--ls-color-muted, #64748b);
}
.schedule-details-page .sd-mini-cal__grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}
.schedule-details-page .sd-mini-cal__cell {
    aspect-ratio: 1;
    border-radius: 8px;
    position: relative;
}
.schedule-details-page .sd-mini-cal__cell--empty { visibility: hidden; }
.schedule-details-page .sd-mini-cal__day {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.15s;
}
.schedule-details-page .sd-mini-cal__cell--has .sd-mini-cal__day {
    background: var(--ls-color-bg, #f8fafc);
    border: 1px solid var(--ls-color-border, #e2e8f0);
}
.schedule-details-page .sd-mini-cal__cell--pending .sd-mini-cal__day {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.schedule-details-page .sd-mini-cal__cell--partial .sd-mini-cal__day {
    background: var(--ls-color-warning-soft, #fef3c7);
    border-color: #fcd34d;
    color: var(--ls-color-warning-text, #92400e);
}
.schedule-details-page .sd-mini-cal__cell--collected .sd-mini-cal__day {
    background: var(--ls-color-success-soft, #dcfce7);
    border-color: #86efac;
    color: var(--ls-color-success-text, #166534);
}
.schedule-details-page .sd-mini-cal__cell--current .sd-mini-cal__day {
    background: var(--ls-color-primary-soft, var(--color-primary-soft));
    border-color: var(--ls-color-primary, var(--color-primary));
    color: var(--ls-color-primary, var(--color-primary));
    box-shadow: 0 0 0 2px var(--ls-color-primary-focus, rgba(128,0,0,0.18));
}
.schedule-details-page .sd-mini-cal__cell--today:not(.sd-mini-cal__cell--has) .sd-mini-cal__day {
    outline: 1px dashed var(--ls-color-primary, var(--color-primary));
}
.schedule-details-page a.sd-mini-cal__day:hover {
    filter: brightness(0.97);
}
.schedule-details-page .sd-mini-cal__dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
    margin-top: 2px;
}
.schedule-details-page .sd-mini-cal__legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
    font-size: 11px;
    color: var(--ls-color-muted, #64748b);
}
.schedule-details-page .sd-mini-cal__legend span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.schedule-details-page .sd-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.schedule-details-page .sd-dot--pending { background: #94a3b8; }
.schedule-details-page .sd-dot--partial { background: #d97706; }
.schedule-details-page .sd-dot--collected { background: #16a34a; }
.schedule-details-page .sd-dot--current { background: var(--ls-color-primary, var(--color-primary)); }

/* Reuse action button styles from schedule list */
.schedule-details-page .ss-act {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1px solid var(--ls-color-border, #e2e8f0);
    background: #fff;
    color: var(--ls-color-muted, #64748b);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    padding: 0;
}
.schedule-details-page .ss-act:hover { color: var(--ls-color-primary, var(--color-primary)); border-color: var(--ls-color-primary-border, #e2b4b4); }
.schedule-details-page .ss-act--delete:hover { color: #dc2626; border-color: #fecaca; }
.schedule-details-page .ss-forms-btn {
    border: 1px solid var(--ls-color-border, #e2e8f0);
    background: #fff;
    border-radius: 999px;
    padding: 2px 10px;
    font-size: 11px;
    font-weight: 600;
    color: var(--ls-color-muted, #64748b);
    cursor: pointer;
}
.schedule-details-page .ss-forms-btn.has-forms {
    background: var(--ls-color-info-soft, #e0f2fe);
    color: var(--ls-color-info-text, #075985);
    border-color: transparent;
}

@media (max-width: 768px) {
    .schedule-details-page .sd-occ-item {
        flex-wrap: wrap;
    }
    .schedule-details-page .sd-occ-item__actions {
        width: 100%;
        justify-content: flex-end;
        margin-top: 4px;
    }
}
</style>
