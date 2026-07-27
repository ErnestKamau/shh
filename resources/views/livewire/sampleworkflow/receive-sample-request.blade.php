<div class="receive-sample-modal-body">
    @if ($showPhysicalConfirmModal)
        @teleport('body')
            <div
                class="modal fade show d-block"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                aria-labelledby="receive-physical-confirm-title"
                style="background: rgba(0,0,0,.45); z-index: 1055;"
            >
                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: min(420px, calc(100vw - 2rem));">
                    <div class="modal-content receive-sample-modal-content border-0 shadow">
                        <div class="modal-header receive-sample-modal-header border-0">
                            <h5 class="modal-title mb-0" id="receive-physical-confirm-title">
                                <i class="mdi mdi-clipboard-arrow-right text-primary mr-2"></i>
                                Move to In Review
                            </h5>
                            <button type="button" class="close" wire:click="closePhysicalConfirmModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body px-4 pb-2">
                            <section class="receive-sample-checkin-confirm text-center py-3 px-2">
                                <i class="mdi mdi-clipboard-arrow-right text-primary" style="font-size: 2.5rem;"></i>
                                <p class="mt-3 mb-0 h6 font-weight-normal">
                                    Are you sure you want to move {{ count($selectedFormInstanceIds) === 1 ? 'this request' : 'these requests' }} to In Review?
                                </p>
                                @error('selection')
                                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 small text-left">{{ $message }}</div>
                                @enderror
                            </section>
                        </div>
                        <div class="modal-footer receive-sample-modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-sm btn-light" wire:click="closePhysicalConfirmModal">Cancel</button>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                wire:click="confirmReceive"
                                wire:loading.attr="disabled"
                                wire:target="confirmReceive"
                            >
                                <span wire:loading.remove wire:target="confirmReceive">
                                    <i class="mdi mdi-package-variant-closed mr-1" aria-hidden="true"></i>
                                    Yes, move to In Review
                                </span>
                                <span wire:loading wire:target="confirmReceive">
                                    <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                                    Processing…
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    <style>
        .receive-sample-modal-body .check-in-trf-metadata {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #e2e8f0;
        }
        .receive-sample-modal-body .receive-sample-type-field {
            margin-top: 2px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .table th,
        .receive-sample-modal-body .walk-in-trf-rows-table .table td {
            vertical-align: top;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .select2-container {
            font-size: 11px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-table .select2-container--default .select2-selection--multiple {
            min-height: 28px;
            padding: 1px 2px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid {
            table-layout: fixed;
            min-width: 1280px;
            font-size: 11px;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid th,
        .receive-sample-modal-body .walk-in-trf-rows-grid td {
            padding: 5px 6px;
            vertical-align: top;
        }
        .receive-sample-modal-body .walk-in-trf-rows-grid th {
            font-size: 10px;
            line-height: 1.25;
            white-space: normal;
            word-break: break-word;
        }
        .receive-sample-modal-body .walk-in-trf-col-sn { width: 42px; min-width: 42px; }
        .receive-sample-modal-body .walk-in-trf-col-desc { width: 40px; min-width: 40px; }
        .receive-sample-modal-body .walk-in-trf-col-location { width: 110px; min-width: 110px; }
        .receive-sample-modal-body .walk-in-trf-col-qty { width: 200px; min-width: 200px; }
        .receive-sample-modal-body .walk-in-trf-col-analysis-type { width: 145px; min-width: 145px; }
        .receive-sample-modal-body .walk-in-trf-col-parameters { width: 180px; min-width: 180px; }
        .receive-sample-modal-body .walk-in-trf-col-radio { width: 105px; min-width: 105px; }
        .receive-sample-modal-body .walk-in-trf-col-date { width: 115px; min-width: 115px; }
        .receive-sample-modal-body .walk-in-trf-col-batch { width: 90px; min-width: 90px; }
        .receive-sample-modal-body .walk-in-trf-col-field-data { width: 80px; min-width: 80px; }
        .receive-sample-modal-body .walk-in-trf-col-default { width: 90px; min-width: 90px; }
        .receive-sample-modal-body .walk-in-trf-col-actions { width: 36px; min-width: 36px; }
        .receive-sample-modal-body .walk-in-trf-desc-btn {
            font-size: 14px;
            padding: 4px 6px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-wrap .select2-container {
            width: 100% !important;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-wrap .select2-container--default .select2-selection--multiple {
            min-height: 32px;
            max-height: 72px;
            overflow-y: auto;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-actions {
            gap: 0.25rem;
            line-height: 1.2;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-action-btns .btn-link {
            font-size: 11px;
            line-height: 1.2;
            text-decoration: none;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-action-btns .btn-link:hover {
            text-decoration: underline;
        }
        .receive-sample-modal-body .walk-in-trf-parameters-count {
            font-size: 10px;
        }
        .receive-sample-modal-body .walk-in-trf-desc-modal .modal-dialog {
            max-width: 640px;
        }
        .walk-in-trf-desc-modal {
            z-index: 1065 !important;
        }
        .walk-in-trf-desc-modal + .modal-backdrop {
            z-index: 1060 !important;
        }
        .receive-sample-modal-body .walk-in-trf-field-label-row {
            min-height: 1.25rem;
        }
        .receive-walk-in-entity-modal {
            z-index: 1070 !important;
        }
        .receive-walk-in-entity-modal + .modal-backdrop {
            z-index: 1065 !important;
        }
    </style>

        @if ($pageMode && ! $wizardOnly && ! $this->isPhysicalCheckIn)
            {{-- How it works section temporarily hidden
            <div class="rft-overview-section mb-3">
                <div class="workflow-board-section-label mb-2">
                    <i class="mdi mdi-information-outline"></i> How it works
                </div>
                <div class="row rft-card-row rft-how-it-works-row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="rft-workflow-card w-100 text-left">
                            <div class="rft-workflow-card-icon" style="--card-accent: var(--workflow-accent);">
                                <i class="mdi mdi-file-edit-outline"></i>
                            </div>
                            <div class="rft-workflow-card-body">
                                <strong>Choose a form</strong>
                                <p class="text-muted small mb-0 mt-1">Pick a sample type / TRF template to start a test request capture.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="rft-workflow-card w-100 text-left">
                            <div class="rft-workflow-card-icon" style="--card-accent: var(--workflow-accent);">
                                <i class="mdi mdi-format-list-checks"></i>
                            </div>
                            <div class="rft-workflow-card-body">
                                <strong>Fill section by section</strong>
                                <p class="text-muted small mb-0 mt-1">Complete each wizard step on a tablet-friendly form.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rft-workflow-card w-100 text-left">
                            <div class="rft-workflow-card-icon" style="--card-accent: var(--workflow-accent);">
                                <i class="mdi mdi-flask-outline"></i>
                            </div>
                            <div class="rft-workflow-card-body">
                                <strong>Lab Reception</strong>
                                <p class="text-muted small mb-0 mt-1">Submitted requests appear in Samples Receiving for lab staff.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            --}}

            @if ($formTypeCards->isNotEmpty())
                <div class="rft-overview-section mb-3">
                    <div class="workflow-board-section-label mb-2 d-flex flex-wrap align-items-center justify-content-between">
                        <span>
                            <i class="mdi mdi-form-select"></i>
                            {{ $plannerMode ? 'Sampling forms' : 'Test request forms' }}
                        </span>
                        <div class="d-flex align-items-center" style="gap: .5rem;">
                            @if ($plannerMode)
                                <span class="text-muted text-normal" style="text-transform:none;letter-spacing:0;font-weight:500;">
                                    Choose a form, then complete it for a schedule
                                </span>
                            @endif
                            @can('laboratory.components.rft form.add')
                                <a href="{{ route('submission-forms.create', ['from' => 'rft', 'trf' => 1]) }}"
                                   class="btn btn-sm btn-outline-primary btn-action-sm">
                                    <i class="mdi mdi-plus"></i> Add TRF
                                </a>
                            @endcan
                        </div>
                    </div>
                    @if ($plannerMode)
                        <div class="fsf-form-grid">
                            @foreach ($formTypeCards as $card)
                                <div class="rft-form-type-card {{ (string) $selectedSampleTypeId === (string) $card['sample_type_id'] ? 'is-filtered' : '' }}"
                                     wire:key="planner-form-card-{{ $card['sample_type_id'] }}">
                                    <a href="{{ $card['view_url'] }}" class="text-decoration-none text-reset d-block">
                                    <div class="rft-form-type-card-header">
                                        <div class="rft-form-type-card-icon">
                                            <i class="mdi {{ $card['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <h6 class="mb-0">{{ $card['name'] }}</h6>
                                            @if ($card['document_code'])
                                                <span class="text-muted small">{{ $card['document_code'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="rft-form-type-card-desc">{{ $card['description'] }}</p>
                                    <div class="rft-form-type-card-meta">
                                        <span><i class="mdi mdi-view-list"></i> {{ $card['sections_count'] }} sections</span>
                                        <span><i class="mdi mdi-test-tube"></i> {{ $card['sample_type_name'] }}</span>
                                    </div>
                                    </a>
                                    <div class="rft-form-type-card-actions">
                                        @can('laboratory.components.rft form.view')
                                            <a href="{{ $card['view_url'] }}"
                                               class="btn btn-sm btn-outline-secondary btn-action-sm">
                                                <i class="mdi mdi-eye-outline"></i> View
                                            </a>
                                        @endcan
                                        @can('laboratory.components.rft form.edit')
                                            <a href="{{ $card['edit_url'] }}"
                                               class="btn btn-sm btn-outline-warning btn-action-sm">
                                                <i class="mdi mdi-pencil-outline"></i> Edit
                                            </a>
                                        @endcan
                                        <button type="button"
                                                class="btn btn-sm btn-primary btn-action-sm"
                                                wire:click="startWalkInForSampleType('{{ $card['sample_type_id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="startWalkInForSampleType">
                                            <span wire:loading.remove wire:target="startWalkInForSampleType('{{ $card['sample_type_id'] }}')">
                                                <i class="mdi mdi-file-document-edit-outline"></i> Fill form
                                            </span>
                                            <span wire:loading wire:target="startWalkInForSampleType('{{ $card['sample_type_id'] }}')">
                                                <i class="mdi mdi-loading mdi-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                    <div class="row rft-card-row">
                        @foreach ($formTypeCards as $card)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="rft-form-type-card {{ (string) $selectedSampleTypeId === (string) $card['sample_type_id'] ? 'is-filtered' : '' }}">
                                    <a href="{{ $card['view_url'] }}" class="text-decoration-none text-reset d-block">
                                    <div class="rft-form-type-card-header">
                                        <div class="rft-form-type-card-icon">
                                            <i class="mdi {{ $card['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <h6 class="mb-0 text-truncate">{{ $card['name'] }}</h6>
                                            @if ($card['document_code'])
                                                <span class="text-muted small">{{ $card['document_code'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="rft-form-type-card-desc">{{ $card['description'] }}</p>
                                    <div class="rft-form-type-card-meta">
                                        <span><i class="mdi mdi-view-list"></i> {{ $card['sections_count'] }} sections</span>
                                        <span><i class="mdi mdi-test-tube"></i> {{ $card['sample_type_name'] }}</span>
                                    </div>
                                    </a>
                                    <div class="rft-form-type-card-actions">
                                        @can('laboratory.components.rft form.view')
                                            <a href="{{ $card['view_url'] }}"
                                               class="btn btn-sm btn-outline-secondary btn-action-sm">
                                                <i class="mdi mdi-eye-outline"></i> View
                                            </a>
                                        @endcan
                                        @can('laboratory.components.rft form.edit')
                                            <a href="{{ $card['edit_url'] }}"
                                               class="btn btn-sm btn-outline-warning btn-action-sm">
                                                <i class="mdi mdi-pencil-outline"></i> Edit
                                            </a>
                                        @endcan
                                        @if ((string) $selectedSampleTypeId === (string) $card['sample_type_id'])
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-action-sm"
                                                    wire:click="clearSelectedSampleType">
                                                <i class="mdi mdi-close"></i> Clear
                                            </button>
                                        @endif
                                        <button type="button"
                                                class="btn btn-sm btn-primary btn-action-sm"
                                                wire:click="startWalkInForSampleType('{{ $card['sample_type_id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="startWalkInForSampleType">
                                            <span wire:loading.remove wire:target="startWalkInForSampleType('{{ $card['sample_type_id'] }}')">
                                                <i class="mdi mdi-plus"></i> Start
                                            </span>
                                            <span wire:loading wire:target="startWalkInForSampleType('{{ $card['sample_type_id'] }}')">
                                                <i class="mdi mdi-loading mdi-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        @if ($plannerMode)
                            No active sampling form templates are linked to sample types.
                        @else
                            No active Test Request Form templates are linked to sample types.
                        @endif
                    </div>
                    @can('laboratory.components.rft form.add')
                        <a href="{{ route('submission-forms.create', ['from' => 'rft', 'trf' => 1]) }}"
                           class="btn btn-sm btn-primary mt-2 mt-md-0">
                            <i class="mdi mdi-plus"></i> Add TRF
                        </a>
                    @endcan
                </div>
            @endif

            <div class="workflow-board-panel mb-3">
                <div class="workflow-board-panel-header d-flex flex-wrap align-items-start justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                            @if ($plannerMode)
                                Scheduled collections
                            @else
                                {{ __('lab.request_for_testing') === 'lab.request_for_testing' ? 'Request For Testing' : __('lab.request_for_testing') }}
                            @endif
                        </h5>
                        <p class="text-muted mb-0 small">
                            {{ $plannerMode ? 'Pending schedules ready for sampling forms' : 'Capture and manage sample submission forms' }}
                        </p>
                    </div>
                    @if ($plannerMode)
                        <a href="{{ route('system-planner.schedule-sampling') }}" class="fsf-panel-link mt-1">
                            View sampling schedule
                        </a>
                    @endif
                </div>
                <div class="workflow-board-panel-body">
                    @if ($plannerMode)
                        <div class="rft-segmented" role="tablist">
                            <button type="button"
                                    class="rft-segmented__btn {{ $rftInstancesTab === 'pending' ? 'is-active' : '' }}"
                                    wire:click="setRftInstancesTab('pending')"
                                    role="tab"
                                    @if ($rftInstancesTab === 'pending') aria-selected="true" @endif>
                                Pending schedules
                            </button>
                            <button type="button"
                                    class="rft-segmented__btn {{ $rftInstancesTab === 'filled' ? 'is-active' : '' }}"
                                    wire:click="setRftInstancesTab('filled')"
                                    role="tab"
                                    @if ($rftInstancesTab === 'filled') aria-selected="true" @endif>
                                Filled today
                            </button>
                        </div>

                        <div class="rft-toolbar">
                            <input type="search"
                                   wire:model.live.debounce.300ms="rftInstancesSearch"
                                   class="form-control"
                                   placeholder="Search schedule, client, location..."
                                   autocomplete="off">
                            <button type="button" wire:click="clearRftInstanceFilters" class="btn btn-outline-secondary">
                                <i class="mdi mdi-refresh"></i> Clear
                            </button>
                        </div>

                        @if ($plannerSchedules->isNotEmpty())
                            <div class="rft-instance-list">
                                @foreach ($plannerSchedules as $schedule)
                                    @php
                                        $progress = $schedule->collectionProgress();
                                        $statusChip = match ($progress['status']) {
                                            'collected' => 'rft-status-chip--submitted',
                                            'partial' => 'rft-status-chip--partial',
                                            default => 'rft-status-chip--draft',
                                        };
                                        $statusLabel = match ($progress['status']) {
                                            'collected' => __('planner.collected'),
                                            'partial' => __('planner.partial').' '.$progress['label'],
                                            default => 'Pending',
                                        };
                                        $scheduleSampleTypeId = $schedule->sample_type_id
                                            ?? collect($schedule->sample_details ?? [])->pluck('sample_type_id')->filter()->first();
                                    @endphp
                                    <article class="rft-instance-card" wire:key="planner-schedule-{{ $schedule->id }}">
                                        <div class="rft-instance-card__main">
                                            <span class="rft-instance-card__number">
                                                {{ optional($schedule->sampling_datetime)->format('M d') ?? '—' }}
                                            </span>
                                            <div class="min-width-0">
                                                <p class="rft-instance-card__title text-truncate mb-0">{{ $schedule->title }}</p>
                                                <p class="rft-instance-card__subtitle text-truncate mb-0">
                                                    {{ $schedule->client?->name ?? 'No client' }}
                                                    @if ($schedule->sample_type?->name)
                                                        · {{ $schedule->sample_type->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="rft-instance-card__meta">
                                            <span class="rft-status-chip {{ $statusChip }}">
                                                {{ $statusLabel }}
                                            </span>
                                            <time class="rft-instance-card__time" datetime="{{ optional($schedule->sampling_datetime)->toIso8601String() }}">
                                                {{ optional($schedule->sampling_datetime)->format('M d, H:i') }}
                                            </time>
                                        </div>
                                        <div class="rft-instance-card__actions">
                                            @if ($progress['status'] !== 'collected' && $scheduleSampleTypeId)
                                                <button type="button"
                                                        wire:click="startFillForSchedule('{{ $schedule->id }}')"
                                                        class="btn btn-sm btn-primary btn-action-sm"
                                                        title="Fill sampling form"
                                                        aria-label="Fill sampling form">
                                                    <i class="mdi mdi-file-document-edit-outline"></i> Fill
                                                </button>
                                            @elseif ($progress['status'] === 'collected')
                                                <a href="{{ route('system-planner.actual-collections') }}"
                                                   class="btn btn-sm btn-outline-primary btn-action-sm"
                                                   title="View collections"
                                                   aria-label="View collections">
                                                    <i class="mdi mdi-eye-outline"></i> View
                                                </a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="workflow-empty-state text-center py-5">
                                <i class="mdi mdi-clipboard-text-outline" style="font-size: 2.75rem; opacity: 0.35; color: var(--color-primary, #8a1a1f);"></i>
                                <h5 class="text-muted mt-3 mb-1" style="font-size:1rem;">No schedules found</h5>
                                <p class="text-muted small mb-3">
                                    {{ $rftInstancesTab === 'pending'
                                        ? 'Create a sampling schedule, then fill its sampling form here.'
                                        : 'No sampling forms filled today yet.' }}
                                </p>
                                @if ($rftInstancesTab === 'pending')
                                    <a href="{{ route('system-planner.schedule-sampling') }}" class="btn btn-sm btn-primary">
                                        Go to Sampling Schedule
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                    <div class="rft-segmented" role="tablist">
                        <button type="button"
                                class="rft-segmented__btn {{ $rftInstancesTab === 'open' ? 'is-active' : '' }}"
                                wire:click="setRftInstancesTab('open')"
                                role="tab"
                                @if ($rftInstancesTab === 'open') aria-selected="true" @endif>
                            Open Drafts
                        </button>
                        <button type="button"
                                class="rft-segmented__btn {{ $rftInstancesTab === 'today' ? 'is-active' : '' }}"
                                wire:click="setRftInstancesTab('today')"
                                role="tab"
                                @if ($rftInstancesTab === 'today') aria-selected="true" @endif>
                            Today
                        </button>
                    </div>

                    <div class="rft-toolbar">
                        <input type="search"
                               wire:model.live.debounce.300ms="rftInstancesSearch"
                               class="form-control"
                               placeholder="Search form number, title..."
                               autocomplete="off">
                        <button type="button" wire:click="clearRftInstanceFilters" class="btn btn-outline-secondary">
                            <i class="mdi mdi-refresh"></i> Clear
                        </button>
                    </div>

                    @if ($rftInstances->isNotEmpty())
                        <div class="rft-instance-list">
                            @foreach ($rftInstances as $instance)
                                @php
                                    $statusChip = match ($instance->status) {
                                        'draft' => 'rft-status-chip--draft',
                                        'submitted' => 'rft-status-chip--submitted',
                                        default => 'rft-status-chip--default',
                                    };
                                    $showUrl = route('submission-forms.instances.show', [
                                        $instance->submission_form_id,
                                        $instance->id,
                                    ]);
                                    $fillUrl = route('submission-forms.instances.fill', [
                                        $instance->submission_form_id,
                                        $instance->id,
                                    ]);
                                @endphp
                                <article class="rft-instance-card" wire:key="rft-instance-{{ $instance->id }}">
                                    <div class="rft-instance-card__main">
                                        <span class="rft-instance-card__number">{{ $instance->form_number ?? '—' }}</span>
                                        <div class="min-width-0">
                                            <p class="rft-instance-card__title text-truncate mb-0">{{ $instance->submissionForm?->name ?? 'Test request' }}</p>
                                            <p class="rft-instance-card__subtitle text-truncate mb-0">{{ $instance->title }}</p>
                                        </div>
                                    </div>
                                    <div class="rft-instance-card__meta">
                                        <span class="rft-status-chip {{ $statusChip }}">
                                            {{ ucfirst(str_replace('_', ' ', (string) $instance->status)) }}
                                        </span>
                                        <time class="rft-instance-card__time" datetime="{{ optional($instance->updated_at)->toIso8601String() }}">
                                            {{ optional($instance->updated_at)->format('M d, H:i') }}
                                        </time>
                                    </div>
                                    <div class="rft-instance-card__actions">
                                        @if ($instance->status === 'draft')
                                            <a href="{{ $fillUrl }}"
                                               class="btn btn-sm btn-primary btn-action-sm"
                                               title="Continue"
                                               aria-label="Continue editing">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    wire:click="deleteRftDraft('{{ $instance->id }}')"
                                                    wire:confirm="Delete this draft?"
                                                    class="btn btn-sm btn-outline-danger btn-action-sm"
                                                    title="Delete draft"
                                                    aria-label="Delete draft">
                                                <i class="mdi mdi-delete-outline"></i>
                                            </button>
                                        @else
                                            <a href="{{ $showUrl }}"
                                               class="btn btn-sm btn-outline-primary btn-action-sm"
                                               title="View"
                                               aria-label="View submission">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="workflow-empty-state text-center py-5">
                            <i class="mdi mdi-file-document-outline" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h5 class="text-muted mt-3">No submissions found</h5>
                            <p class="text-muted small mb-0">
                                {{ $rftInstancesTab === 'open'
                                    ? 'Start a new test request above to capture samples. Drafts you resume will appear here.'
                                    : 'Nothing captured or submitted today yet.' }}
                            </p>
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        @endif

    @if (! $this->isPhysicalCheckIn && $selectedFormInstanceIds !== [])
            <section class="receive-sample-selected mb-3">
                <p class="receive-sample-section-label font-weight-bold">Selected requests</p>
                <div class="receive-sample-chips">
                    @foreach ($selectedFormSummaries as $summary)
                        <span class="receive-sample-chip badge mr-1 mb-1 p-2">
                            <span class="receive-sample-chip-code font-weight-bold">{{ $summary['label'] ?? 'Request' }}</span>
                            @if (!empty($summary['customer']))
                                <span class="receive-sample-chip-meta small">({{ $summary['customer'] }})</span>
                            @endif
                        </span>
                    @endforeach
                    @if ($selectedFormSummaries === [] && $selectedFormInstanceIds !== [])
                        <span class="receive-sample-chip receive-sample-chip--muted">{{ count($selectedFormInstanceIds) }} request(s)</span>
                    @endif
                </div>
                <p class="receive-sample-selected-hint text-muted small mt-1">Verify the enquiry summary below, then confirm check-in.</p>
            </section>
    @endif

        @if ($checkInContexts !== [] && ! $this->isPhysicalCheckIn)
            <section class="receive-sample-checkin mb-3">
                @foreach ($checkInContexts as $context)
                    <article class="receive-checkin-card" wire:key="receive-context-{{ $context['instance_id'] ?? $loop->index }}">
                        <header class="receive-checkin-card__header">
                            <div class="receive-checkin-card__identity">
                                <span class="receive-checkin-card__ref">{{ $context['form_number'] ?? 'Request' }}</span>
                                @if (!empty($context['customer_name']))
                                    <span class="receive-checkin-card__customer">{{ $context['customer_name'] }}</span>
                                @endif
                            </div>
                            <div class="receive-checkin-card__header-aside">
                                @if (!empty($context['enquiry_status']))
                                    <span class="receive-checkin-card__status">{{ $context['enquiry_status'] }}</span>
                                @endif
                                @if (!empty($context['source_channel']))
                                    <span class="receive-checkin-card__channel badge badge-light border text-uppercase">{{ str_replace('_', ' ', $context['source_channel']) }}</span>
                                @endif
                            </div>
                        </header>

                        @if (! ($context['can_receive'] ?? true))
                            <div class="alert alert-warning py-2 px-3 mb-0 mx-3 mt-2 small">
                                {{ $context['receive_block_reason'] ?? 'This request cannot be received yet.' }}
                            </div>
                        @endif

                        <div class="receive-checkin-card__grid">
                            @if (!empty($context['number_of_samples']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Samples</span>
                                    <span class="receive-checkin-stat__value">{{ $context['number_of_samples'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sampling_date']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Sampling date</span>
                                    <span class="receive-checkin-stat__value">{{ $context['sampling_date'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sampling_location']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Sampling location</span>
                                    <span class="receive-checkin-stat__value">{{ $context['sampling_location'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['quotation_number']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Accepted quotation</span>
                                    <span class="receive-checkin-stat__value">{{ $context['quotation_number'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['client_po_number']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Client PO</span>
                                    <span class="receive-checkin-stat__value">{{ $context['client_po_number'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['advance_payment_reference']))
                                <div class="receive-checkin-stat">
                                    <span class="receive-checkin-stat__label">Advance payment</span>
                                    <span class="receive-checkin-stat__value">{{ $context['advance_payment_reference'] }}</span>
                                </div>
                            @endif
                            @if (!empty($context['sample_description']) || !empty($context['sample_description_html']))
                                <div class="receive-checkin-stat receive-checkin-stat--full">
                                    <span class="receive-checkin-stat__label">Sample description</span>
                                    <div class="receive-checkin-stat__value receive-checkin-rich-text">
                                        {!! $context['sample_description_html'] ?? e($context['sample_description'] ?? '') !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (!empty($context['instance_id']))
                            @include('livewire.partials.check-in-trf-metadata-fields', ['instanceId' => $context['instance_id']])
                        @endif

                        <footer class="receive-checkin-card__footer">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                wire:click="openRejectWizard('{{ $context['instance_id'] }}')"
                            >
                                <i class="mdi mdi-close-circle-outline mr-1"></i> Reject sample
                            </button>
                        </footer>
                    </article>
                @endforeach
            </section>
        @endif

        @unless($this->isPhysicalCheckIn)
        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
                <strong class="d-block mb-1">Please fix the following before submitting:</strong>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($pageMode)
            @if ($selectedSampleTypeId && $submissionForm)
                <div class="workflow-board-panel mb-3">
                    <div class="workflow-board-panel-header d-flex flex-wrap align-items-center justify-content-between rft-gap">
                        <div class="min-width-0">
                            <h5 class="mb-1">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                                @if ($plannerMode)
                                    {{ __('planner.fill_sampling_forms') === 'planner.fill_sampling_forms' ? 'Fill Sampling Forms' : __('planner.fill_sampling_forms') }}
                                @else
                                    {{ __('lab.request_for_testing') === 'lab.request_for_testing' ? 'Request For Testing' : __('lab.request_for_testing') }}
                                @endif
                            </h5>
                            <p class="text-muted mb-0 small">{{ $submissionForm->name }} · {{ $this->selectedSampleType?->name }}</p>
                        </div>
                        <div class="submission-instance-actions ml-auto">
                            @include('livewire.partials.walk-in-trf-wizard-nav')
                        </div>
                    </div>
                    <div class="workflow-board-panel-body">
                        @if ($plannerMode && $selectedScheduleId)
                            @php
                                $linkedSchedule = collect($plannerScheduleOptions ?? [])->firstWhere('id', $selectedScheduleId);
                                if (! $linkedSchedule) {
                                    $linkedSchedule = \App\Models\SamplingSchedule::query()
                                        ->visibleTo()
                                        ->with('client')
                                        ->find($selectedScheduleId);
                                }
                            @endphp
                            @if ($linkedSchedule)
                                <div class="alert alert-light border py-2 px-3 mb-3 small d-flex align-items-center justify-content-between flex-wrap" style="border-radius:10px;">
                                    <span>
                                        <i class="mdi mdi-calendar-clock text-primary mr-1"></i>
                                        Filling for schedule:
                                        <strong>{{ $linkedSchedule->title }}</strong>
                                        @if ($linkedSchedule->client?->name)
                                            · {{ $linkedSchedule->client->name }}
                                        @endif
                                        @if ($linkedSchedule->sampling_datetime)
                                            · {{ $linkedSchedule->sampling_datetime->format('d M Y H:i') }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        @elseif ($plannerMode)
                            @error('selectedScheduleId')
                                <div class="alert alert-danger py-2 px-3 mb-3 small">{{ $message }}</div>
                            @enderror
                        @endif
                        @include('livewire.partials.walk-in-trf-wizard-styles')
                        <div class="walk-in-trf-wizard-shell mb-0 border-0 bg-transparent p-0">
                            @include('livewire.partials.walk-in-trf-wizard-stepper')
                            @include('livewire.partials.walk-in-trf-capture-sections', [
                                'submissionForm' => $submissionForm,
                                'formData' => $formData,
                                'walkInSections' => $walkInSections,
                                'walkInActiveStepIndex' => $walkInActiveStepIndex,
                            ])
                        </div>
                    </div>
                </div>
            @elseif ($selectedSampleTypeId)
                <div class="alert alert-warning py-2 px-3 mb-0 small">
                    @if ($plannerMode)
                        No active sampling form template is linked to this sample type. Link a TRF template to the sample type in Submission Forms, then try again.
                    @else
                        No active Test Request Form template is linked to this sample type. Link a TRF template to the sample type in Submission Forms, then try again.
                    @endif
                </div>
            @endif
        @else
            <!-- Walk-in only: Sample Type + TRF (modal) -->
            <div class="form-group mb-4 receive-sample-type-field">
                <label for="selectedSampleTypeId" class="font-weight-bold text-dark">Sample Type <span class="text-danger">*</span></label>
                <select id="selectedSampleTypeId" wire:model.live="selectedSampleTypeId" class="form-control form-control-sm @error('selectedSampleTypeId') is-invalid @enderror">
                    <option value="">-- Select Sample Type --</option>
                    @foreach($sampleTypes as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
                @error('selectedSampleTypeId')
                    <div class="invalid-feedback d-block font-weight-semibold">{{ $message }}</div>
                @enderror
            </div>

            @if($selectedSampleTypeId && $submissionForm)
                @include('livewire.partials.walk-in-trf-wizard-styles')
                <div class="walk-in-trf-wizard-shell mb-3">
                    @include('livewire.partials.walk-in-trf-wizard-stepper')
                    @include('livewire.partials.walk-in-trf-capture-sections', [
                        'submissionForm' => $submissionForm,
                        'formData' => $formData,
                        'walkInSections' => $walkInSections,
                        'walkInActiveStepIndex' => $walkInActiveStepIndex,
                    ])
                </div>
            @elseif($selectedSampleTypeId)
                <div class="alert alert-warning py-2 px-3 mb-0 small">
                    No active Test Request Form template is linked to this sample type. Link a TRF template to the sample type in Submission Forms, then try again.
                </div>
            @endif
        @endif
        @endunless

        {{-- Remarks / reception notes removed for physical check-in; now a simple confirmation. --}}

    @error('selection')
        <div class="receive-sample-alert receive-sample-alert--warning alert alert-warning mt-3 mb-0">{{ $message }}</div>
    @enderror

    @if($showWalkInAddContactModal)
        <div class="modal fade show d-block receive-walk-in-entity-modal" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Add customer contact</h5>
                        <button type="button" class="close" wire:click="closeWalkInAddContactModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small font-weight-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewContactName" placeholder="Contact name">
                            @error('walkInNewContactName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Email</label>
                            <input type="email" class="form-control form-control-sm" wire:model="walkInNewContactEmail" placeholder="Email">
                            @error('walkInNewContactEmail') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0 mt-2">
                            <label class="small font-weight-bold">Phone</label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewContactPhone" placeholder="Phone">
                            @error('walkInNewContactPhone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeWalkInAddContactModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveWalkInContact">Save contact</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show receive-walk-in-entity-modal"></div>
    @endif

    @if($showWalkInAddPointModal)
        <div class="modal fade show d-block receive-walk-in-entity-modal" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Add sample point</h5>
                        <button type="button" class="close" wire:click="closeWalkInAddPointModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small font-weight-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model="walkInNewPointName" placeholder="Sample point name">
                            @error('walkInNewPointName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Client unit <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" wire:model="walkInNewPointUnitId">
                                <option value="">Select unit...</option>
                                @foreach($this->customerCompanyUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('walkInNewPointUnitId') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeWalkInAddPointModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveWalkInSamplePoint">Save sample point</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show receive-walk-in-entity-modal"></div>
    @endif

    @if ($pageMode)
        @if ((! $selectedSampleTypeId || ! $submissionForm) && ! $this->isPhysicalCheckIn && ! $plannerMode)
            <footer class="rft-touch-bar">
                <span class="text-muted small mb-0">{{ $wizardOnly ? 'Loading form…' : 'Select a form type above to begin capture.' }}</span>
                <a href="{{ $wizardOnly ? route('sample-workflow.request-for-testing') : route('sample-workflow', ['status' => 'Samples Receiving']) }}" class="btn btn-sm btn-light">
                    {{ $wizardOnly ? 'Back' : 'Back to Receiving' }}
                </a>
            </footer>
        @endif
    @elseif (! $showPhysicalConfirmModal)
        <footer class="receive-sample-modal-footer d-flex justify-content-between align-items-center border-top pt-3 flex-wrap rft-gap">
            @if ($selectedSampleTypeId && $submissionForm && $this->walkInTotalSteps > 0)
                <span class="walk-in-trf-wizard__step-hint mb-0" aria-live="polite">
                    Step {{ $walkInActiveStepIndex + 1 }} of {{ $this->walkInTotalSteps }}
                    · {{ $this->walkInWizardSteps[$walkInActiveStepIndex]['title'] ?? '' }}
                </span>
            @else
                <span></span>
            @endif

            <div class="d-flex align-items-center rft-gap">
                @if ($selectedSampleTypeId && $submissionForm && $this->walkInTotalSteps > 0)
                    @include('livewire.partials.walk-in-trf-wizard-nav')
                @else
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary receive-sample-submit-btn"
                        wire:click="confirmReceive"
                        wire:loading.attr="disabled"
                        wire:target="confirmReceive"
                        onclick="try { if (typeof window.syncTrfSignaturesBeforeSubmit === 'function') { window.syncTrfSignaturesBeforeSubmit(); } } catch (error) { console.error('TRF pre-submit sync failed', error); }"
                        @if (! $selectedSampleTypeId) disabled @endif
                    >
                        <span wire:loading.remove wire:target="confirmReceive">
                            <i class="mdi mdi-package-variant-closed mr-1" aria-hidden="true"></i>
                            Submit walk-in request
                        </span>
                        <span wire:loading wire:target="confirmReceive">
                            <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                            Processing…
                        </span>
                    </button>
                @endif
            </div>
        </footer>
    @endif
</div>
@script
<script>
    Alpine.data('rftParamPickerUi', (config = {}) => ({
        open: false,
        openUp: false,
        search: '',
        rowIndex: config.rowIndex ?? 0,
        options: Array.isArray(config.options) ? config.options.slice() : [],
        selected: Array.isArray(config.selected) ? config.selected.slice() : [],
        hydrating: false,
        init() {
            // Card body uses x-if: remount must re-read Livewire (wire:ignore freezes Blade snapshot).
            this.hydrateFromWire();
        },
        get filtered() {
            const query = String(this.search || '').trim().toLowerCase();
            if (!query) {
                return this.options;
            }

            return this.options.filter((name) => String(name).toLowerCase().includes(query));
        },
        get visibleChips() {
            return this.selected.slice(0, 8);
        },
        get hiddenCount() {
            return Math.max(0, this.selected.length - 8);
        },
        isSelected(name) {
            return this.selected.includes(name);
        },
        toggleOpen() {
            this.open = !this.open;
            if (this.open) {
                this.hydrateFromWire();
                this.$nextTick(() => this.decideDirection());
            }
        },
        decideDirection() {
            const rect = this.$el.getBoundingClientRect();
            this.openUp = (window.innerHeight - rect.bottom) < 320;
        },
        matches(name) {
            const query = String(this.search || '').trim().toLowerCase();
            return !query || String(name).includes(query);
        },
        toggle(name) {
            if (this.isSelected(name)) {
                this.selected = this.selected.filter((item) => item !== name);
            } else {
                this.selected = this.selected.concat([name]);
            }
            this.sync();
        },
        selectAll() {
            this.selected = this.options.slice();
            this.sync();
        },
        clearAll() {
            this.selected = [];
            this.search = '';
            this.sync();
        },
        sync() {
            if (this.$wire) {
                this.$wire.setWalkInParameters(this.rowIndex, this.selected.slice());
            }
        },
        applySelectedFromWire() {
            if (!this.$wire) {
                return;
            }

            const raw = this.$wire.get(`formData.parameters.${this.rowIndex}`);
            if (Array.isArray(raw)) {
                this.selected = raw.map((value) => String(value));
            } else if (raw !== null && raw !== undefined && raw !== '') {
                this.selected = [String(raw)];
            } else {
                this.selected = [];
            }
        },
        async hydrateFromWire() {
            if (!this.$wire || this.hydrating) {
                return;
            }

            this.hydrating = true;
            try {
                this.applySelectedFromWire();

                const state = await this.$wire.walkInParameterPickerState(this.rowIndex);
                if (state && Array.isArray(state.options)) {
                    this.options = state.options.map((value) => String(value));
                }
                if (state && Array.isArray(state.selected)) {
                    this.selected = state.selected.map((value) => String(value));
                }
            } catch (error) {
                this.applySelectedFromWire();
            } finally {
                this.hydrating = false;
            }
        },
    }));

    Alpine.data('rftSampleDescriptionEditor', (config) => ({
        editorId: config.editorId,
        wireKey: config.wireKey,
        rowIndex: config.rowIndex ?? 0,
        init() {
            this.$nextTick(() => this.mountEditor());
        },
        mountEditor() {
            if (typeof tinymce === 'undefined') {
                const existing = document.querySelector('script[data-rft-tinymce]');
                if (existing) {
                    existing.addEventListener('load', () => this.initTiny());
                    return;
                }
                const script = document.createElement('script');
                script.src = '/tinymce/tinymce.min.js';
                script.dataset.rftTinymce = '1';
                script.onload = () => this.initTiny();
                document.head.appendChild(script);
                return;
            }
            this.initTiny();
        },
        initTiny() {
            if (typeof tinymce === 'undefined') {
                return;
            }
            if (tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
            const self = this;
            tinymce.init({
                selector: '#' + this.editorId,
                height: 160,
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: 'lists',
                toolbar: 'bold italic underline | bullist numlist',
                setup(editor) {
                    editor.on('change keyup blur', function () {
                        if (self.$wire) {
                            self.$wire.set(self.wireKey, editor.getContent(), false);
                        }
                    });
                },
            });
        },
        destroy() {
            if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
        },
    }));
</script>
@endscript
