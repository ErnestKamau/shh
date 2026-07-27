<div class="container-fluid actual-collections-page lab-surface-theme" data-ls-type="plex">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-1 font-weight-bold">
                                <i class="mdi mdi-clipboard-check-outline text-success"></i> {{ __('planner.actual_collections') }}
                            </h3>
                            <p class="text-muted mb-0">
                                @if($this->canViewAllCollections())
                                    {{ __('planner.actual_collections_subtitle_all') }}
                                @else
                                    {{ __('planner.actual_collections_subtitle_assigned') }}
                                @endif
                            </p>
                        </div>
                        @if($this->canViewAllCollections())
                            <span class="badge badge-primary px-3 py-2 mt-2 mt-md-0">
                                <i class="mdi mdi-shield-account mr-1"></i> {{ __('planner.viewing_all_collections') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                class="form-control"
                placeholder="{{ __('planner.search_placeholder') }}"
                style="border-radius:10px;"
            >
        </div>
        <div class="col-md-6 text-md-right mt-2 mt-md-0">
            <button wire:click="toggleFilters" class="btn btn-outline-secondary" style="border-radius:10px;">
                <i class="mdi mdi-filter-variant mr-1"></i> {{ $showFilters ? __('planner.hide_filters') : __('planner.show_filters') }}
            </button>
        </div>
    </div>

    @if($showFilters)
        <div class="card shadow-sm border-0 mb-3" style="border-radius:var(--ls-radius-xl,12px);background:var(--ls-color-bg,#f8fafc);border:1px solid var(--ls-color-border,#e2e8f0);">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small">{{ __('planner.date_from') }}</label>
                        <input type="date" wire:model.live="filterDateFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small">{{ __('planner.date_to') }}</label>
                        <input type="date" wire:model.live="filterDateTo" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small">{{ __('planner.client') }}</label>
                        <select wire:model.live="filterClientId" class="form-control form-control-sm no-select2">
                            <option value="">{{ __('planner.all_clients') }}</option>
                            @foreach($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($this->canViewAllCollections())
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold small">{{ __('planner.personnel') }}</label>
                            <select wire:model.live="filterPersonnelId" class="form-control form-control-sm no-select2">
                                <option value="">{{ __('planner.all_personnel') }}</option>
                                @foreach($this->personnel as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                    <i class="mdi mdi-refresh mr-1"></i> Reset filters
                </button>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius:15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:rgba(0,0,0,.03);">
                        <tr>
                            <th class="border-0">Title</th>
                            <th class="border-0">Client</th>
                            <th class="border-0">Scheduled</th>
                            <th class="border-0">Collected</th>
                            <th class="border-0">Location</th>
                            <th class="border-0">Sample Details</th>
                            <th class="border-0">Personnel</th>
                            <th class="border-0">Forms</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->collections as $collection)
                            <tr wire:key="collection-{{ $collection->id }}">
                                <td class="font-weight-bold">
                                    {{ $collection->title }}
                                    <span class="badge badge-success ml-1">
                                        <i class="mdi mdi-check-circle-outline mr-1"></i>Collected
                                    </span>
                                </td>
                                <td>
                                    {{ $collection->client->name ?? 'N/A' }}
                                    @if($collection->contact)
                                        <br>
                                        <small class="text-muted">
                                            {{ trim(($collection->contact->first_name ?? '').' '.($collection->contact->last_name ?? '')) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light p-2 text-dark">
                                        <i class="mdi mdi-clock-outline text-primary mr-1"></i>
                                        {{ $collection->sampling_datetime ? $collection->sampling_datetime->format('Y-m-d H:i') : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success p-2">
                                        <i class="mdi mdi-calendar-check mr-1"></i>
                                        {{ $this->collectedAt($collection) ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <i class="mdi mdi-map-marker text-danger mr-1"></i>{{ $collection->location ?: '—' }}
                                </td>
                                <td>
                                    @php $details = $this->resolveSampleDetails($collection); @endphp
                                    @foreach($details as $detail)
                                        <span class="badge badge-info p-1 mb-1 d-inline-block">{{ $detail['type'] }}</span>
                                        @if($detail['analysis'])
                                            <span class="badge badge-secondary p-1 mb-1 d-inline-block">{{ $detail['analysis'] }}</span>
                                        @endif
                                        @if($detail['params_count'] > 0)
                                            <small class="text-muted d-block" style="font-size:11px;">{{ $detail['params_count'] }} params</small>
                                        @endif
                                    @endforeach
                                    @if(empty($details))
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-dark p-2">
                                        <i class="mdi mdi-account-tie mr-1"></i>{{ $collection->personnel->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center font-weight-bold">
                                    {{ $collection->submissionFormInstances->count() }}
                                </td>
                                <td class="text-center">
                                    <button
                                        wire:click="viewCollection('{{ $collection->id }}')"
                                        class="btn btn-sm rm-act-btn rm-act-btn--view"
                                        title="View collection"
                                    >
                                        <i class="mdi mdi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-clipboard-text-off fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-0">No collected samples found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showViewModal && $viewingSchedule)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                    <div class="modal-header text-white" style="flex-shrink:0;">
                        <h5 class="modal-title font-weight-bold m-0">
                            <i class="mdi mdi-clipboard-check mr-2"></i> Collection Details
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeViewModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4" style="overflow-y:auto;flex:1 1 auto;">
                        <div class="mb-4">
                            <h4 class="font-weight-bold mb-1">{{ $viewingSchedule->title }}</h4>
                            <span class="badge badge-success"><i class="mdi mdi-check-circle-outline mr-1"></i>Collected</span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Client</small>
                                <div class="font-weight-semibold">{{ $viewingSchedule->client->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Personnel</small>
                                <div class="font-weight-semibold">{{ $viewingSchedule->personnel->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Scheduled</small>
                                <div>{{ $viewingSchedule->sampling_datetime?->format('M d, Y H:i') ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Collected</small>
                                <div>{{ $this->collectedAt($viewingSchedule) ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Location</small>
                                <div>{{ $viewingSchedule->location ?: '—' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Samples</small>
                                <div>{{ $viewingSchedule->number_of_samples ?? 1 }}</div>
                            </div>
                        </div>

                        @php $detailedSamples = $this->resolveDetailedSampleDetails($viewingSchedule); @endphp
                        @if(!empty($detailedSamples))
                            <div class="mb-4">
                                <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:12px;letter-spacing:1px;">
                                    <i class="mdi mdi-flask-outline mr-1"></i>Sample Details
                                </h6>
                                @foreach($detailedSamples as $index => $detail)
                                    <div class="p-3 mb-2" style="background:#fafbfc;border-radius:8px;border:1px solid #eee;">
                                        <div class="font-weight-bold mb-2">Sample {{ $index + 1 }}</div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Sample type</small>
                                            <span class="badge badge-info">{{ $detail['type'] ?: 'N/A' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Analysis type</small>
                                            @if($detail['analysis'])
                                                <span class="badge badge-secondary">{{ $detail['analysis'] }}</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                        @if(!empty($detail['param_names']))
                                            <div>
                                                <small class="text-muted d-block mb-1">Parameters</small>
                                                @foreach($detail['param_names'] as $paramName)
                                                    <span class="badge badge-light border mr-1 mb-1">{{ $paramName }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($viewingSchedule->submissionFormInstances->count() > 0)
                            <div class="mb-2">
                                <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:12px;letter-spacing:1px;">
                                    <i class="mdi mdi-file-document-outline mr-1"></i>Tied Request Forms
                                </h6>
                                <div style="background:#fafbfc;border-radius:8px;border:1px solid #eee;overflow:hidden;">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="px-3 py-2" style="font-size:11px;">Form / Sample Type</th>
                                                <th class="px-3 py-2" style="font-size:11px;">Submitted By</th>
                                                <th class="px-3 py-2" style="font-size:11px;">Status</th>
                                                <th class="px-3 py-2" style="font-size:11px;">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingSchedule->submissionFormInstances as $instance)
                                                <tr style="border-top:1px solid #eee;">
                                                    <td class="px-3 py-2">
                                                        <div class="font-weight-bold" style="font-size:13px;">
                                                            {{ $instance->submissionForm?->name ?? 'Request Form' }}
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $instance->submissionForm?->sampleTypes->first()?->name ?? 'N/A' }}
                                                        </small>
                                                        @if($instance->form_number)
                                                            <small class="text-muted d-block">{{ $instance->form_number }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2" style="font-size:12px;">
                                                        {{ $instance->submittedBy?->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <span class="badge badge-success" style="font-size:11px;">
                                                            {{ ucfirst($instance->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2" style="font-size:12px;color:#6c757d;">
                                                        {{ $instance->submitted_at?->format('M d, Y H:i') ?? $instance->created_at?->format('M d, Y H:i') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if($viewingSchedule->description)
                            <div class="mb-2">
                                <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:12px;letter-spacing:1px;">
                                    <i class="mdi mdi-text mr-1"></i>Description
                                </h6>
                                <div class="p-3" style="background:#fafbfc;border-radius:8px;border:1px solid #eee;">
                                    <p class="mb-0" style="white-space:pre-wrap;">{{ $viewingSchedule->description }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light p-3" style="flex-shrink:0;">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">
                            <i class="mdi mdi-close mr-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .rm-act-btn{border-radius:7px;padding:4px 8px;font-size:12px;}
    .rm-act-btn--view{border:1px solid #c3e6cb;color:#155724;background:#d4edda;}
    .rm-act-btn--view:hover{background:#c3e6cb;border-color:#a3d5b5;}
    </style>
</div>
