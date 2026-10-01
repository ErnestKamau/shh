<div class="container-fluid schedule-sampling-page lab-surface-theme" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap schedule-page-header">
                        <div class="mb-2 mb-md-0 pr-md-3">
                            <h3 class="mb-1 schedule-page-title"><i class="mdi mdi-clock-outline text-primary"></i> {{ __('planner.sampling_schedules') }}</h3>
                            <p class="text-muted mb-0">{{ __('planner.sampling_schedules_subtitle') }}</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-outline-primary schedule-page-cta px-3" style="border-radius:8px;">
                            <i class="mdi mdi-plus-circle mr-1"></i> {{ __('planner.new_sampling_schedule') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($message)
    <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
        <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} mr-1"></i> {{ $message }}
        <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
    </div>
    @endif

    <!-- Search & Filter Toggle -->
    <div class="row mb-3 align-items-stretch">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('planner.search_placeholder') }}" style="border-radius:10px;">
        </div>
        <div class="col-12 col-md-6 text-md-right schedule-toolbar-actions">
            <button wire:click="toggleFilters" class="btn btn-outline-secondary mr-2 mb-2 mb-md-0" style="border-radius:10px;">
                <i class="mdi mdi-filter-variant mr-1"></i> {{ $showFilters ? __('planner.hide_filters') : __('planner.show_filters') }}
            </button>
            <button wire:click="exportToExcel" class="btn btn-success mr-2 mb-2 mb-md-0" style="border-radius:10px;">
                <i class="mdi mdi-file-excel mr-1"></i> {{ __('planner.export_excel') }}
            </button>
            <button wire:click="exportToPdf" class="btn btn-danger mb-2 mb-md-0" style="border-radius:10px;">
                <i class="mdi mdi-file-pdf mr-1"></i> {{ __('planner.export_pdf') }}
            </button>
        </div>
    </div>

    <!-- Advanced Filters -->
    @if($showFilters)
    <div class="card shadow-sm border-0 mb-3" style="border-radius:var(--ls-radius-xl,12px);background:var(--ls-color-bg,#f8fafc);border:1px solid var(--ls-color-border,#e2e8f0);">
        <div class="card-body p-4">
            <div class="row">
                {{-- Date Range --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">{{ __('planner.date_from') }}</label>
                    <input type="date" wire:model.live="filterDateFrom" class="form-control" style="border-radius:8px;">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">{{ __('planner.date_to') }}</label>
                    <input type="date" wire:model.live="filterDateTo" class="form-control" style="border-radius:8px;">
                </div>

                {{-- Client --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">{{ __('planner.client') }}</label>
                    <select wire:model.live="filterClientId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">{{ __('planner.all_clients') }}</option>
                        @foreach($clients as $client)
                        <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Frequency --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">{{ __('planner.frequency') }}</label>
                    <select wire:model.live="filterFrequency" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">{{ __('planner.all_frequencies') }}</option>
                        @foreach($frequencies as $freq)
                        <option value="{{ $freq }}">{{ $freq }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                {{-- Sample Type --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">Sample Type</label>
                    <select wire:model.live="filterSampleTypeId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Sample Types</option>
                        @foreach($allSampleTypes as $st)
                        <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Analysis Type --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">Analysis Type</label>
                    <select wire:model.live="filterAnalysisTypeId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Analysis Types</option>
                        @foreach($allAnalysisTypes as $at)
                        <option value="{{ $at['id'] }}">{{ $at['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Parameters --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">Parameters</label>
                    <select wire:model.live="filterParameterId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Parameters</option>
                        @foreach($allParameters as $param)
                        <option value="{{ $param['id'] }}">{{ $param['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sample Count Range --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="ss-filter-label">Samples Range</label>
                    <div class="d-flex gap-2">
                        <input type="number" wire:model.live="filterMinSamples" class="form-control" placeholder="Min" min="1" style="border-radius:8px;width:48%;">
                        <input type="number" wire:model.live="filterMaxSamples" class="form-control" placeholder="Max" min="1" style="border-radius:8px;width:48%;">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-right">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                        <i class="mdi mdi-refresh mr-1"></i> Reset All Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="card shadow-sm border-0 schedule-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 schedule-sampling-table">
                    <thead>
                        <tr>
                            <th>Schedule</th>
                            <th>When</th>
                            <th>Client / Location</th>
                            <th>Tests</th>
                            <th>Personnel</th>
                            <th class="text-center">Forms</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->scheduleRows as $row)
                        @php
                            $s = $row['schedule'];
                            $series = $row['series'];
                            $details = $this->resolveSampleDetails($s);
                            $tableContacts = $s->contacts();
                            $contactNames = $tableContacts->map(fn ($c) => trim(($c->first_name ?? '').' '.($c->last_name ?? '')))->filter()->values();
                            $personnelLabel = $s->personnelNames();
                            $formCount = $series ? $series['forms_count'] : $s->submissionFormInstances->count();
                            $collectionProgress = $s->collectionProgress();
                            $visibleDetails = array_slice($details, 0, 2);
                            $hiddenDetailsCount = max(0, count($details) - 2);
                        @endphp
                        <tr>
                            <td class="ss-col-schedule">
                                <div class="ss-title">
                                    {{ $s->title }}
                                    @if($series)
                                    <span class="badge badge-pill ml-1" style="background:var(--color-primary-soft,#e8eaf6);color:var(--color-primary,#3949ab);font-size:11px;padding:3px 10px;vertical-align:middle;">
                                        <i class="mdi mdi-repeat mr-1"></i>Recurring
                                    </span>
                                    @endif
                                </div>
                                <div class="ss-meta">
                                    <span>{{ $s->frequency ?: 'One-time' }}</span>
                                    <span class="ss-dot"></span>
                                    @if($series)
                                    <span>{{ $series['count'] }} scheduling occurrence{{ $series['count'] === 1 ? '' : 's' }}</span>
                                    <span class="ss-dot"></span>
                                    <span>{{ $series['collected_count'] }}/{{ $series['count'] }} collected</span>
                                    @if($series['collected_count'] >= $series['count'])
                                    <span class="ss-pill ss-pill--ok">{{ __('planner.collected') }}</span>
                                    @elseif($series['collected_count'] > 0)
                                    <span class="ss-pill ss-pill--partial">{{ __('planner.partial') }}</span>
                                    @endif
                                    @else
                                    <span>{{ $collectionProgress['collected'] }}/{{ $collectionProgress['scheduled'] }} sample{{ $collectionProgress['scheduled'] === 1 ? '' : 's' }}</span>
                                    @if($collectionProgress['status'] === 'collected')
                                    <span class="ss-pill ss-pill--ok">{{ __('planner.collected') }}</span>
                                    @elseif($collectionProgress['status'] === 'partial')
                                    <span class="ss-pill ss-pill--partial">{{ __('planner.partial') }}</span>
                                    @endif
                                    @endif
                                </div>
                            </td>
                            <td class="ss-col-when">
                                @if($series)
                                @if($s->sampling_datetime)
                                <div class="ss-when-date">{{ $s->sampling_datetime->format('d M Y') }}</div>
                                <div class="ss-when-time">{{ $s->sampling_datetime->format('H:i') }}</div>
                                @endif
                                <div class="ss-sub text-muted" style="font-size:11px;">
                                    {{ $series['first_date'] ? $series['first_date']->format('d M Y') : '—' }}
                                    –
                                    {{ $series['last_date'] ? $series['last_date']->format('d M Y') : '—' }}
                                </div>
                                @elseif($s->sampling_datetime)
                                <div class="ss-when-date">{{ $s->sampling_datetime->format('d M Y') }}</div>
                                <div class="ss-when-time">{{ $s->sampling_datetime->format('H:i') }}</div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="ss-col-client">
                                <div class="ss-client">{{ $s->client->name ?? 'N/A' }}</div>
                                @if($contactNames->isNotEmpty())
                                <div class="ss-sub">{{ $contactNames->take(2)->implode(', ') }}{{ $contactNames->count() > 2 ? ' +'.($contactNames->count() - 2) : '' }}</div>
                                @endif
                                <div class="ss-sub ss-location">{{ $s->locationDisplayName() }}</div>
                            </td>
                            <td class="ss-col-tests">
                                @if(!empty($visibleDetails))
                                <div class="ss-tests">
                                    @foreach($visibleDetails as $d)
                                    <div class="ss-test-line">
                                        <span class="ss-test-type">{{ $d['type'] }}</span>
                                        @if(!empty($d['analysis']))
                                        <span class="ss-test-sep">·</span>
                                        <span class="ss-test-analysis">{{ $d['analysis'] }}</span>
                                        @endif
                                        <span class="ss-test-params">{{ $d['params_count'] }}p</span>
                                    </div>
                                    @endforeach
                                    @if($hiddenDetailsCount > 0)
                                    <div class="ss-test-more">+{{ $hiddenDetailsCount }} more</div>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="ss-col-personnel">
                                @if($personnelLabel !== 'N/A' && $personnelLabel !== '')
                                <div class="ss-personnel" title="{{ $personnelLabel }}">{{ $personnelLabel }}</div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center ss-col-forms">
                                @if($series)
                                <a href="{{ route('system-planner.schedule-sampling.show', ['schedule' => $s->id]) }}"
                                        class="ss-forms-btn {{ $formCount > 0 ? 'has-forms' : '' }}"
                                        title="Forms across all scheduling occurrences — open series to view per run">
                                    {{ $formCount }}
                                </a>
                                @else
                                <button type="button"
                                        class="ss-forms-btn {{ $formCount > 0 ? 'has-forms' : '' }}"
                                        wire:click="viewTrfForms('{{ $s->id }}')"
                                        title="View submitted sampling forms">
                                    {{ $formCount }}
                                </button>
                                @endif
                            </td>
                            <td class="text-right ss-col-actions">
                                <div class="ss-actions">
                                    @php
                                        $fillSampleTypeId = $s->sample_type_id
                                            ?? collect($s->sample_details ?? [])->pluck('sample_type_id')->filter()->first();
                                    @endphp
                                    @if ($fillSampleTypeId)
                                        <a href="{{ route('system-planner.fill-sampling-forms.fill', ['sampleType' => $fillSampleTypeId, 'schedule' => $s->id]) }}"
                                           class="ss-act ss-act--form"
                                           title="Fill sampling form"
                                           aria-label="Fill sampling form">
                                            <i class="mdi mdi-clipboard-edit-outline"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('system-planner.fill-sampling-forms', ['schedule' => $s->id]) }}"
                                           class="ss-act ss-act--form"
                                           title="Choose a form for this schedule"
                                           aria-label="Choose a form for this schedule">
                                            <i class="mdi mdi-clipboard-edit-outline"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('system-planner.schedule-sampling.sample-collection-label', ['schedule' => $s->id]) }}"
                                       class="ss-act ss-act--label"
                                       target="_blank"
                                       rel="noopener"
                                       title="Sample collection label"
                                       aria-label="Sample collection label">
                                        <i class="mdi mdi-printer"></i>
                                    </a>
                                    <a href="{{ route('system-planner.schedule-sampling.collection-qr-codes', ['schedule' => $s->id]) }}"
                                       class="ss-act ss-act--qr"
                                       target="_blank"
                                       rel="noopener"
                                       title="Collection QR codes"
                                       aria-label="Collection QR codes">
                                        <i class="mdi mdi-qrcode"></i>
                                    </a>
                                    <a href="{{ route('system-planner.schedule-sampling.show', ['schedule' => $s->id]) }}" class="ss-act ss-act--view" title="{{ $series ? 'View series & scheduling occurrences' : 'View schedule details' }}"><i class="mdi mdi-eye-outline"></i></a>
                                    <button wire:click="showEditModal('{{ $s->id }}')" class="ss-act ss-act--edit" title="{{ $series ? 'Edit occurrences' : 'Edit' }}"><i class="mdi mdi-pencil-outline"></i></button>
                                    @if($series)
                                    <button wire:click="deleteSeries('{{ $series['group_id'] }}')" class="ss-act ss-act--delete" title="Delete entire series" onclick="return confirm('Delete this recurring schedule and all {{ $series['count'] }} of its occurrences?')"><i class="mdi mdi-trash-can-outline"></i></button>
                                    @else
                                    <button wire:click="delete('{{ $s->id }}')" class="ss-act ss-act--delete" title="Delete" onclick="return confirm('Are you sure you want to delete this schedule?')"><i class="mdi mdi-trash-can-outline"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="mdi mdi-calendar-blank" style="font-size:2rem;"></i>
                                <p class="mb-0 mt-2">No sampling schedules created yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══ CREATE / EDIT MODAL ═══ -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-clock-outline mr-2"></i>{{ $editingSchedule ? 'Edit' : 'Schedule' }} Sampling Run</h5>
                    <button type="button" class="close text-white" wire:click="closeModal"><span>&times;</span></button>
                </div>

                <nav class="ss-form-nav" aria-label="Form sections">
                    <a href="#ss-section-basic" class="ss-form-nav__item" data-ss-nav>Basic</a>
                    <a href="#ss-section-client" class="ss-form-nav__item" data-ss-nav>Client</a>
                    <a href="#ss-section-when" class="ss-form-nav__item" data-ss-nav>When / Where</a>
                    <a href="#ss-section-samples" class="ss-form-nav__item" data-ss-nav>Samples</a>
                    <a href="#ss-section-ops" class="ss-form-nav__item" data-ss-nav>Ops</a>
                </nav>

                <div class="modal-body schedule-run-modal__body" id="schedule-run-modal-body">

                    {{-- Recurring series occurrence switcher --}}
                    @php $editSeriesOccurrences = $this->editingSeriesOccurrences; @endphp
                    @if($editSeriesOccurrences->count() > 1)
                    <div class="alert alert-info d-flex align-items-center flex-wrap py-2 px-3 mb-3" style="border-radius:8px;gap:10px;">
                        <span style="font-size:13px;">
                            <i class="mdi mdi-repeat mr-1"></i>
                            <strong>Recurring series</strong> — you are editing one occurrence only ({{ $editSeriesOccurrences->count() }} in total).
                        </span>
                        <select class="form-control form-control-sm no-select2" style="width:auto;max-width:260px;border-radius:6px;"
                                wire:change="showEditModal($event.target.value)">
                            @foreach($editSeriesOccurrences as $occurrence)
                            <option value="{{ $occurrence->id }}" @selected((string) $occurrence->id === (string) $editingSchedule->id)>
                                {{ $occurrence->sampling_datetime?->format('d M Y H:i') ?? 'Unscheduled' }}{{ $occurrence->is_collected ? ' · collected' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Basic Info --}}
                    <section class="ss-section" id="ss-section-basic">
                        <div class="ss-section__title"><i class="mdi mdi-information-outline"></i> Basic</div>
                        <div class="form-group mb-0">
                            <label class="ss-label">Schedule Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model="form.title" class="form-control ss-control" placeholder="e.g. Monthly Water Sampling at Site A">
                            @error('form.title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </section>

                    {{-- Client --}}
                    <section class="ss-section" id="ss-section-client">
                        <div class="ss-section__title"><i class="mdi mdi-account-outline"></i> Client</div>
                        <div class="form-group">
                            <label class="ss-label">Client / Customer Name <span class="text-danger">*</span></label>
                            <div wire:ignore wire:key="schedule-client-{{ $editingSchedule->id ?? 'create' }}">
                                <select class="form-control no-select2 ss-control"
                                        x-data="scheduleSelect2Bridge({
                                            model: 'form.crm_customer_id',
                                            multiple: false,
                                            placeholder: 'Search client...',
                                            initial: @js($form['crm_customer_id'] ?: ''),
                                        })">
                                    <option value="">Select Customer</option>
                                    @foreach($clients as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.crm_customer_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($contractValidFrom || $contractValidTo)
                        <p class="ss-contract-chip mb-3">
                            Contract
                            @if($contractValidFrom)<span>{{ $contractValidFrom }}</span>@endif
                            @if($contractValidFrom && $contractValidTo)<span class="ss-contract-chip__sep">–</span>@endif
                            @if($contractValidTo)<span>{{ $contractValidTo }}</span>@endif
                        </p>
                        @endif

                        @if(!empty($form['crm_customer_id']))
                        <div class="form-group mb-2" wire:key="schedule-contacts-{{ $form['crm_customer_id'] }}-{{ count($customerContactOptions) }}">
                            <label class="ss-label">Customer Contact Personnel</label>
                            @if(count($customerContactOptions) > 0)
                            <div wire:ignore class="ss-compact-select">
                                <select class="form-control no-select2 ss-control" multiple
                                        x-data="scheduleSelect2Bridge({
                                            model: 'form.contact_ids',
                                            multiple: true,
                                            placeholder: 'Search and select contacts...',
                                            initial: @js(array_values(array_map('strval', $form['contact_ids'] ?? []))),
                                        })">
                                    @foreach($customerContactOptions as $cc)
                                    <option value="{{ $cc['id'] }}">{{ $cc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">Notify Client emails go to these contacts.</small>
                            @else
                            <p class="text-muted mb-1">No active contacts found for this customer.</p>
                            @endif
                            @error('form.contact_ids') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        @if(count($selectedContactsSummary) > 0)
                        <div class="mb-0">
                            @foreach($selectedContactsSummary as $summary)
                            <div class="ss-contact-card mb-2">
                                <div class="ss-contact-card__name">{{ $summary['name'] }}</div>
                                <div class="ss-contact-card__meta">
                                    <span>{{ $summary['email'] ?: 'N/A' }}</span>
                                    <span>{{ $summary['phone'] ?: 'N/A' }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @endif
                    </section>

                    {{-- Timeline --}}
                    <section class="ss-section" id="ss-section-when">
                        <div class="ss-section__title"><i class="mdi mdi-calendar-clock"></i> When / Where</div>
                        <div class="row ss-field-row">
                            <div class="col-md-6 form-group">
                                <label class="ss-label">Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" wire:model="form.sampling_datetime" class="form-control ss-control">
                                @error('form.sampling_datetime') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="ss-label">Location (Sample Point) <span class="text-danger">*</span></label>
                                <div class="ss-location-control">
                                    <div class="ss-location-control__input">
                                        @if(!empty($form['crm_customer_id']))
                                        <div wire:ignore wire:key="schedule-sample-point-{{ $form['crm_customer_id'] }}-{{ count($customerSamplePointOptions) }}-{{ $form['sample_point_id'] ?: 'none' }}">
                                            <select class="form-control no-select2 ss-control"
                                                    x-data="scheduleSelect2Bridge({
                                                        model: 'form.sample_point_id',
                                                        multiple: false,
                                                        placeholder: 'Search sample point...',
                                                        initial: @js($form['sample_point_id'] ?: ''),
                                                    })">
                                                <option value="">Select sample point</option>
                                                @foreach($customerSamplePointOptions as $point)
                                                <option value="{{ $point['id'] }}">{{ $point['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @else
                                        <input type="text" class="form-control bg-light ss-control" value="" placeholder="Select a customer first" disabled>
                                        @endif
                                    </div>
                                    <button type="button"
                                            class="btn btn-outline-primary ss-location-control__add"
                                            wire:click="openAddSamplePointModal('schedule')"
                                            @disabled(empty($form['crm_customer_id']))
                                            title="Add sample point">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">From the customer's sample points.</small>
                                @error('form.sample_point_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Multi Sample Details --}}
                    <section class="ss-section" id="ss-section-samples">
                        <div class="ss-section__title ss-section__title--row">
                            <span><i class="mdi mdi-flask-outline"></i> Samples</span>
                            <button type="button" wire:click="addSampleEntry" class="btn btn-sm btn-outline-primary ss-add-sample-btn"><i class="mdi mdi-plus"></i> Add Sample Type</button>
                        </div>

                        @foreach($sampleEntries as $idx => $entry)
                        <div class="ss-sample-card mb-2" wire:key="sample-entry-{{ $idx }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ss-sample-card__label">Sample #{{ $idx + 1 }}</span>
                                <button type="button" wire:click="removeSampleEntry({{ $idx }})" class="btn btn-sm btn-outline-danger ss-sample-card__remove" title="Remove"><i class="mdi mdi-close"></i></button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="ss-label">Sample Type</label>
                                    <select wire:model.live="sampleEntries.{{ $idx }}.sample_type_id" class="form-control no-select2 ss-control">
                                        <option value="">Select Sample Type</option>
                                        @foreach($allSampleTypes as $st)
                                        <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="ss-label">Analysis Type</label>
                                    <select wire:model.live="sampleEntries.{{ $idx }}.analysis_type_id" class="form-control no-select2 ss-control" {{ empty($entry['analysisTypes']) ? 'disabled' : '' }}>
                                        <option value="">Select Analysis Type</option>
                                        @foreach($entry['analysisTypes'] ?? [] as $at)
                                        <option value="{{ $at['id'] }}">{{ $at['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if(!empty($entry['availableParameters']))
                            @php
                                $selectedParamIds = array_values(array_unique(array_filter(array_map('strval', $entry['parameters'] ?? []))));
                                $availableParamCount = count($entry['availableParameters']);
                                $selectedParamCount = count($selectedParamIds);
                            @endphp
                            <div class="form-group mb-0 schedule-params-field"
                                 wire:key="params-{{ $idx }}-{{ $entry['analysis_type_id'] }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="ss-label mb-0">Parameters / Analytes
                                        <small class="text-muted ml-1 schedule-params-count">
                                            ({{ $selectedParamCount }}/{{ $availableParamCount }} selected)
                                        </small>
                                    </label>
                                    <div class="schedule-params-actions">
                                        <button type="button" class="btn btn-link btn-sm p-0 mr-2 schedule-params-select-all" style="font-size:12px;">Select all</button>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-danger schedule-params-clear" style="font-size:12px;">Clear</button>
                                    </div>
                                </div>
                                <div wire:ignore>
                                    <select class="form-control no-select2 schedule-params-select"
                                            multiple
                                            data-placeholder="Search and select parameters..."
                                            x-data="scheduleSelect2Bridge({
                                                model: 'sampleEntries.{{ $idx }}.parameters',
                                                multiple: true,
                                                placeholder: 'Search and select parameters...',
                                                initial: @js($selectedParamIds),
                                                countSelector: '.schedule-params-count',
                                                total: {{ $availableParamCount }},
                                            })">
                                        @foreach($entry['availableParameters'] as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        @if(empty($sampleEntries))
                        <p class="text-muted text-center py-2 mb-0"><i class="mdi mdi-information-outline"></i> Click “Add Sample Type” to add sample details.</p>
                        @endif
                    </section>

                    {{-- Operations --}}
                    <section class="ss-section" id="ss-section-ops">
                        <div class="ss-section__title"><i class="mdi mdi-account-multiple-outline"></i> Operations</div>
                        <div class="row ss-field-row">
                            <div class="col-md-4 form-group">
                                <label class="ss-label">No. of Samples</label>
                                <input type="number" class="form-control bg-light ss-control" value="{{ $form['number_of_samples'] }}" min="1" readonly
                                       title="Automatically set from the number of sample entries added">
                                <small class="text-muted">Auto from sample entries.</small>
                                @error('form.number_of_samples') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="ss-label">Frequency <span class="text-danger">*</span></label>
                                <select wire:model="form.frequency" class="form-control no-select2 ss-control"
                                        @disabled($editingSchedule && $editingSchedule->isPartOfRecurringSeries())>
                                    <option value="One-time">One-time</option>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Annually">Annually</option>
                                </select>
                                @if($editingSchedule && $editingSchedule->isPartOfRecurringSeries())
                                <small class="text-muted">Set by the recurring series.</small>
                                @else
                                <small class="text-muted">Recurring frequencies create one grouped schedule with individual occurrences.</small>
                                @endif
                                @error('form.frequency') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="ss-label" for="notify_client_lw">Notify Client</label>
                                <div class="ss-notify-control ss-control">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" wire:model="form.notify_client" id="notify_client_lw">
                                        <label class="form-check-label mb-0" for="notify_client_lw">Email selected contacts</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="ss-label">Personnel Carrying Out Sampling <span class="text-danger">*</span></label>
                            <div wire:ignore class="ss-compact-select" wire:key="schedule-personnel-{{ $editingSchedule->id ?? 'create' }}">
                                <select class="form-control no-select2 ss-control" multiple
                                        x-data="scheduleSelect2Bridge({
                                            model: 'form.personnel_ids',
                                            multiple: true,
                                            placeholder: 'Search and select personnel...',
                                            initial: @js(array_values(array_map('strval', $form['personnel_ids'] ?? []))),
                                        })">
                                    @foreach($users as $u)
                                    <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.personnel_ids') <span class="text-danger small">{{ $message }}</span> @enderror
                            @error('form.personnel_ids.*') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="ss-label">Description / Special Instructions</label>
                            <textarea wire:model="form.description" class="form-control ss-control" rows="3" placeholder="Enter special instructions or notes..."></textarea>
                        </div>
                    </section>
                </div>
                <div class="modal-footer bg-light p-3 schedule-run-modal__footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save"><i class="mdi mdi-calendar-check mr-1"></i> {{ $editingSchedule ? 'Update' : 'Save' }} Schedule</span>
                        <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showAddSamplePointModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.45);z-index:1060;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius:12px;">
                <div class="modal-header py-2">
                    <h5 class="modal-title schedule-run-modal__title mb-0">Add sample point</h5>
                    <button type="button" class="close" wire:click="closeAddSamplePointModal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="ss-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" wire:model="newSamplePointName" placeholder="Sample point name">
                        @error('newSamplePointName') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="ss-label">Client unit <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm no-select2" wire:model="newSamplePointUnitId">
                            <option value="">Select unit...</option>
                            @foreach($customerCompanyUnitOptions as $unit)
                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                            @endforeach
                        </select>
                        @error('newSamplePointUnitId') <small class="text-danger">{{ $message }}</small> @enderror
                        @if(empty($customerCompanyUnitOptions))
                        <small class="text-muted d-block mt-1">This customer has no active units. Add a unit in CRM first.</small>
                        @endif
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light" wire:click="closeAddSamplePointModal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="saveNewSamplePoint" wire:loading.attr="disabled">Save sample point</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ═══ TRF FORMS LIST MODAL ═══ -->
    @if($showTrfFormsModal && $viewingTrfSchedule)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-file-document-multiple-outline mr-2"></i>Test Request Forms</h5>
                    <button type="button" class="close text-white" wire:click="closeModal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4" style="overflow-y:auto;flex:1 1 auto;">
                    <div class="mb-3">
                        <h5 class="font-weight-bold mb-1">{{ $viewingTrfSchedule->title }}</h5>
                        <div class="text-muted small">
                            {{ $viewingTrfSchedule->client->name ?? 'N/A' }}
                            · {{ $viewingTrfSchedule->sampling_datetime ? $viewingTrfSchedule->sampling_datetime->format('Y-m-d H:i') : 'N/A' }}
                            · {{ $viewingTrfSchedule->locationDisplayName() }}
                        </div>
                    </div>

                    @if($viewingTrfSchedule->submissionFormInstances->count() > 0)
                    <div class="table-responsive" style="border:1px solid #eee;border-radius:10px;overflow:hidden;">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="border-0 px-3 py-2">Form</th>
                                    <th class="border-0 px-3 py-2">Sample Type</th>
                                    <th class="border-0 px-3 py-2">Submitted By</th>
                                    <th class="border-0 px-3 py-2">Status</th>
                                    <th class="border-0 px-3 py-2">Date</th>
                                    <th class="border-0 px-3 py-2 text-center">Open</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($viewingTrfSchedule->submissionFormInstances as $instance)
                                <tr>
                                    <td class="px-3 py-2 font-weight-bold">{{ $instance->submissionForm?->name ?? 'Request Form' }}</td>
                                    <td class="px-3 py-2">{{ $instance->selectedSampleTypeName() ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $instance->submittedBy?->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2"><span class="badge badge-success">{{ ucfirst($instance->status) }}</span></td>
                                    <td class="px-3 py-2 text-muted">{{ $instance->submitted_at?->format('M d, Y H:i') ?? $instance->created_at?->format('M d, Y H:i') }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <a href="{{ route('test-request-form.preview', $instance->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:16px;">
                                            <i class="mdi mdi-open-in-new"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border mb-0" style="border-radius:10px;">
                        <i class="mdi mdi-information-outline mr-1"></i>
                        No test request forms have been filled for this schedule yet.
                        Use the yellow form icon on the schedule row to fill one.
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light p-3" style="flex-shrink:0;">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    @php
                        $fillSampleTypeId = $viewingTrfSchedule->sample_type_id
                            ?? collect($viewingTrfSchedule->sample_details ?? [])->pluck('sample_type_id')->filter()->first();
                    @endphp
                    @if ($fillSampleTypeId)
                        <a href="{{ route('system-planner.fill-sampling-forms.fill', ['sampleType' => $fillSampleTypeId, 'schedule' => $viewingTrfSchedule->id]) }}"
                           class="btn btn-primary">
                            <i class="mdi mdi-plus mr-1"></i>Fill Sampling Form
                        </a>
                    @else
                        <a href="{{ route('system-planner.fill-sampling-forms', ['schedule' => $viewingTrfSchedule->id]) }}"
                           class="btn btn-primary">
                            <i class="mdi mdi-plus mr-1"></i>Choose Form for Schedule
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ═══ FORM MODAL ═══ -->
    @if($showFormModal && $selectedScheduleId)
    <div id="schedule-sampling-form-modal" class="modal fade show d-block schedule-sampling-form-modal" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-file-document-edit mr-2"></i> Fill Sampling Form</h5>
                    <button type="button" class="close text-white" wire:click="$set('showFormModal', false)"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4" style="overflow-y:auto;flex:1 1 auto;">
                    
                    <div class="alert alert-info py-2 px-3 mb-4" style="border-radius:8px;">
                        <i class="mdi mdi-information mr-1"></i> Filling form responses for schedule: <strong>{{ \App\Models\SamplingSchedule::find($selectedScheduleId)?->title }}</strong>
                    </div>

                    <!-- Select Sample Type from Schedule -->
                    <div class="form-group mb-4">
                        <label for="selectedSampleTypeId" class="font-weight-bold text-dark">Select Sample Type <span class="text-danger">*</span></label>
                        <select id="selectedSampleTypeId" wire:model.live="selectedSampleTypeId" class="form-control form-control-sm">
                            <option value="">-- Select Sample Type --</option>
                            @foreach($scheduleSampleTypes as $st)
                                <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedSampleTypeId')
                            <div class="invalid-feedback d-block font-weight-semibold mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($selectedSampleTypeId && $submissionForm)
                        @include('livewire.partials.submission-form-capture-sections', [
                            'submissionForm' => $submissionForm,
                            'formData' => $formData,
                        ])
                    @elseif($selectedSampleTypeId)
                        <div class="alert alert-warning mb-0">
                            No active Test Request Form template found for the selected sample type.
                        </div>
                    @endif

                </div>
                <div class="modal-footer bg-light p-3" style="flex-shrink:0; gap: 8px;">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showFormModal', false)">Close</button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="if (typeof window.syncScheduleTrfBeforeSubmitAndSave === 'function') { window.syncScheduleTrfBeforeSubmitAndSave(); }"
                        wire:loading.attr="disabled"
                        wire:target="saveScheduleForm"
                        @if(!$selectedSampleTypeId) disabled @endif
                    >
                        <span wire:loading.remove wire:target="saveScheduleForm"><i class="mdi mdi-check mr-1"></i> Save Responses</span>
                        <span wire:loading wire:target="saveScheduleForm"><span class="spinner-border spinner-border-sm"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
    [x-cloak]{display:none!important;}
    .schedule-page-title{
        font-family:var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
        font-size:var(--ls-text-xl, 1.05rem);
        font-weight:500;
        color:var(--ls-color-ink, #1e293b);
    }
    .ss-filter-label{
        display:block;
        font-family:var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
        margin-bottom:0.25rem;
    }
    .rm-act-btn{border-radius:7px;padding:4px 8px;margin-right:3px;font-size:12px;}
    .rm-act-btn:last-child{margin-right:0;}
    .rm-act-btn--form{border:1px solid #ffeeba;color:#856404;background:#fff3cd;}
    .rm-act-btn--form:hover{background:#ffeeba;border-color:#ffdf7e;}
    .rm-act-btn--forms{border:1px solid #bee5eb;color:#0c5460;background:#d1ecf1;}
    .rm-act-btn--forms:hover{background:#bee5eb;border-color:#9fdbe5;}
    .rm-act-btn--view{border:1px solid #c3e6cb;color:#155724;background:#d4edda;}
    .rm-act-btn--view:hover{background:#c3e6cb;border-color:#a3d5b5;}
    .rm-act-btn--edit{border:1px solid #bfdbfe;color:#1d4ed8;background:#eff6ff;}
    .rm-act-btn--edit:hover{background:#dbeafe;border-color:#93c5fd;}
    .rm-act-btn--delete{border:1px solid #fecaca;color:#b91c1c;background:#fef2f2;}
    .rm-act-btn--delete:hover{background:#fee2e2;border-color:#fca5a5;}

    /* —— Schedule run modal —— */
    body:has(.schedule-run-modal){
        overflow:hidden !important;
    }
    .schedule-run-modal{
        position:fixed;
        inset:0;
        z-index:1050;
        background:rgba(0,0,0,0.5);
        overflow-x:hidden;
        overflow-y:auto;
        -webkit-overflow-scrolling:touch;
    }
    .schedule-run-modal__dialog{
        max-width:800px;
        width:calc(100% - 1.5rem);
        max-height:calc(100vh - 1.5rem);
        margin:0.75rem auto;
        display:flex;
        align-items:stretch;
    }
    .schedule-run-modal__content{
        border-radius:var(--ls-radius-xl, 12px);
        overflow:hidden;
        max-height:calc(100vh - 1.5rem);
        width:100%;
        display:flex;
        flex-direction:column;
        min-height:0;
        font-family:var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
    }
    .schedule-run-modal__header{flex:0 0 auto;}
    .schedule-run-modal__title{
        font-size:var(--ls-text-lg, 0.95rem);
        font-weight:500;
    }
    .schedule-run-modal__body{
        flex:1 1 auto;
        min-height:0;
        overflow-y:auto !important;
        overflow-x:hidden;
        -webkit-overflow-scrolling:touch;
        overscroll-behavior:contain;
        padding:0.85rem 1.15rem 1.15rem;
        touch-action:pan-y;
    }
    .schedule-run-modal__footer{flex:0 0 auto;}
    .ss-form-nav{
        display:flex;
        flex-wrap:wrap;
        gap:0.15rem 0.35rem;
        padding:0.55rem 1.15rem;
        border-bottom:1px solid var(--ls-color-border, #e2e8f0);
        background:var(--ls-color-bg, #f8fafc);
        flex:0 0 auto;
    }
    .ss-form-nav__item{
        font-size:var(--ls-text-sm, 0.75rem);
        font-weight:400;
        color:var(--ls-color-muted, #64748b);
        text-decoration:none;
        padding:0.3rem 0.55rem;
        border-radius:999px;
        border:1px solid transparent;
        transition:background .15s ease, color .15s ease, border-color .15s ease;
    }
    .ss-form-nav__item:hover{
        color:var(--ls-color-ink, #1e293b);
        background:#fff;
        border-color:var(--ls-color-border, #e2e8f0);
        text-decoration:none;
    }
    .ss-form-nav__item.is-active{
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        background:var(--ls-color-primary-soft, #f8ecec);
        border-color:var(--ls-color-primary-border, #e2b4b4);
        font-weight:500;
    }
    .ss-section{margin-bottom:1rem;scroll-margin-top:0.5rem;}
    .ss-section:last-child{margin-bottom:0;}
    .ss-section__title{
        display:flex;
        align-items:center;
        gap:0.4rem;
        font-size:var(--ls-text-sm, 0.75rem);
        font-weight:500;
        color:var(--ls-color-muted, #64748b);
        border-bottom:1px solid var(--ls-color-border, #e2e8f0);
        padding:0 0 0.4rem;
        margin:0 0 0.75rem;
    }
    .ss-section__title i{color:var(--ls-color-primary, var(--color-primary, #6D0A0E));font-size:14px;}
    .ss-section__title--row{justify-content:space-between;}
    .ss-label,
    .schedule-run-modal .ss-label,
    .schedule-sampling-page .modal .ss-label,
    .schedule-sampling-page .modal label.ss-label{
        display:block;
        font-family:var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:600 !important;
        color:#5a6a7c;
        margin-bottom:0.25rem;
        min-height:1.2em;
    }
    .schedule-run-modal .form-group{margin-bottom:0.75rem;}
    .schedule-run-modal .text-muted,
    .schedule-run-modal small.text-muted{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        color:#5a6a7c;
    }
    .ss-form-nav__item{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        color:#5a6a7c;
    }
    .ss-section__title{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        color:#5a6a7c;
    }
    .ss-form-nav__item:first-child.is-active,
    .ss-form-nav:not(:has(.ss-form-nav__item.is-active)) .ss-form-nav__item:first-child{
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        background:var(--ls-color-primary-soft, #f8ecec);
        border-color:var(--ls-color-primary-border, #e2b4b4);
        font-weight:500;
    }

    /* Shared control chrome = Date & Time look */
    .schedule-run-modal .ss-control,
    .schedule-run-modal .form-control.ss-control,
    .schedule-run-modal select.ss-control,
    .schedule-run-modal textarea.ss-control{
        min-height:38px;
        height:38px;
        border:1px solid #ced4da;
        border-radius:0.25rem;
        background-color:#fff;
        box-shadow:none;
        font-size:calc(0.875rem + 1px);
        color:#212529;
    }
    .schedule-run-modal textarea.ss-control{
        height:auto;
        min-height:84px;
    }
    .schedule-run-modal .form-control.ss-control.bg-light{
        background-color:#f8f9fa;
        border:1px solid #ced4da;
    }
    .schedule-run-modal .ss-control:focus{
        border-color:#80bdff;
        outline:0;
        box-shadow:0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .ss-location-control{
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
    .ss-location-control__input{
        flex:1 1 auto;
        min-width:0;
    }
    .ss-location-control__input .select2-container{
        width:100% !important;
    }
    .ss-location-control__add{
        flex:0 0 38px;
        width:38px;
        min-width:38px;
        height:38px;
        max-height:38px;
        padding:0;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid #ced4da;
        border-radius:0.25rem;
        line-height:1;
        align-self:center;
    }
    .ss-notify-control{
        display:flex;
        align-items:center;
        padding:0 0.75rem;
        min-height:38px;
        height:38px;
        box-sizing:border-box;
        margin-top:0;
    }
    .ss-notify-control .form-check{
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
    .ss-notify-control .form-check-input{
        margin-top:0;
        position:static;
    }
    .ss-notify-control .form-check-label{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400 !important;
        color:#5a6a7c;
    }

    /* Compact multi-selects: same height as Client single select */
    .schedule-run-modal .ss-compact-select .select2-container{
        width:100% !important;
        display:block;
    }
    .schedule-run-modal .ss-compact-select .select2-container--default .select2-selection--multiple,
    .schedule-sampling-page .schedule-run-modal .ss-compact-select .select2-container--default .select2-selection--multiple{
        min-height:38px !important;
        height:38px !important;
        max-height:38px !important;
        overflow:hidden;
        display:flex;
        align-items:center;
        padding:3px 8px !important;
        border:1px solid #ced4da !important;
        border-radius:0.25rem !important;
        background:#fff;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-selection__rendered{
        display:flex !important;
        flex-direction:row;
        flex-wrap:nowrap;
        align-items:center;
        justify-content:flex-start;
        gap:4px;
        overflow-x:auto;
        overflow-y:hidden;
        margin:0 !important;
        padding:0 !important;
        width:100% !important;
        max-width:100% !important;
        white-space:nowrap;
        float:none !important;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-selection__choice{
        margin-top:0 !important;
        margin-bottom:0 !important;
        margin-right:0 !important;
        flex:0 0 auto;
        max-width:160px;
        height:26px;
        line-height:24px;
        padding:0 8px 0 6px !important;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        font-size:12px;
        font-weight:400 !important;
        display:inline-flex;
        align-items:center;
        box-sizing:border-box;
        float:none !important;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-selection__choice__remove{
        margin-right:4px;
        font-weight:400;
        line-height:1;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-search--inline{
        float:none !important;
        position:static !important;
        flex:1 1 auto !important;
        min-width:0 !important;
        width:auto !important;
        max-width:100% !important;
        margin:0 !important;
        padding:0 !important;
        display:block !important;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-selection__rendered:not(:has(.select2-selection__choice)) .select2-search--inline{
        flex:1 1 100% !important;
        width:100% !important;
        max-width:100% !important;
    }
    .schedule-run-modal .ss-compact-select .select2-selection--multiple .select2-search--inline .select2-search__field{
        margin:0 !important;
        padding:0 !important;
        height:28px !important;
        min-width:0 !important;
        width:100% !important;
        max-width:100% !important;
        text-align:left !important;
        font-weight:400;
        font-size:calc(0.875rem + 1px);
        box-sizing:border-box !important;
    }
    .schedule-run-modal .select2-container--default .select2-selection--single{
        min-height:38px !important;
        height:38px !important;
        border:1px solid #ced4da !important;
        border-radius:0.25rem !important;
        background:#fff;
    }
    .schedule-run-modal .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height:36px;
        padding-left:12px;
        font-size:calc(0.875rem + 1px);
        color:#212529;
    }
    .schedule-run-modal .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:36px;
    }

    .ss-contract-chip{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
        background:var(--ls-color-bg, #f8fafc);
        border:1px solid #ced4da;
        border-radius:0.25rem;
        padding:0.4rem 0.65rem;
    }
    .ss-contract-chip__sep{margin:0 0.25rem;opacity:.6;}
    .ss-contact-card{
        border:1px solid #ced4da;
        border-radius:0.25rem;
        padding:0.45rem 0.65rem;
        background:#fff;
    }
    .ss-contact-card__name{
        font-size:calc(var(--ls-text-base, 0.8125rem) + 1px);
        font-weight:400;
        color:#172033;
    }
    .ss-contact-card__meta{
        display:flex;
        flex-wrap:wrap;
        gap:0.75rem;
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        color:#5a6a7c;
        margin-top:0.15rem;
    }
    .ss-sample-card{
        border:1px solid #ced4da;
        border-radius:0.25rem;
        padding:0.65rem 0.75rem;
        background:#fff;
    }
    .ss-sample-card__label{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
    }
    .ss-sample-card__remove{
        border-radius:0.25rem;
        width:28px;
        height:28px;
        padding:0;
    }
    .ss-add-sample-btn{border-radius:0.25rem;font-weight:500;}

    .schedule-table-card{
        border-radius:var(--ls-radius-xl, 14px);
        overflow:hidden;
        border:1px solid var(--ls-color-border, #e2e8f0);
    }
    .schedule-sampling-table{
        width:100%;
        margin:0;
        font-family:var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
    }
    .schedule-sampling-table thead th{
        background:var(--ls-color-bg, #f8fafc);
        border:0;
        border-bottom:1px solid var(--ls-color-border, #e2e8f0);
        color:#5a6a7c;
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:500;
        letter-spacing:0.02em;
        text-transform:none;
        padding:var(--ls-table-cell-y, 0.65rem) var(--ls-table-cell-x, 0.7rem);
        white-space:nowrap;
        vertical-align:middle;
    }
    .schedule-sampling-table tbody td{
        border-top:1px solid var(--ls-color-border, #e2e8f0);
        padding:var(--ls-table-cell-y, 0.65rem) var(--ls-table-cell-x, 0.7rem);
        vertical-align:middle;
        color:#172033;
        font-weight:400;
        font-size:calc(var(--ls-text-base, 0.8125rem) + 1px);
    }
    .schedule-sampling-table tbody tr:hover{
        background:var(--ls-color-bg, #f8fafc);
    }
    .ss-title{
        font-size:calc(var(--ls-text-base, 0.8125rem) + 1px);
        font-weight:500;
        color:#172033;
        line-height:1.3;
        max-width:220px;
    }
    .ss-meta{
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:6px;
        margin-top:4px;
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
    }
    .ss-dot{
        width:3px;height:3px;border-radius:50%;background:#c4c9d2;display:inline-block;
    }
    .ss-pill{
        display:inline-flex;
        align-items:center;
        padding:1px 7px;
        border-radius:999px;
        font-size:10px;
        font-weight:500;
        line-height:1.5;
    }
    .ss-pill--ok{
        background:#e8f5e9;
        color:#1b5e20;
    }
    .ss-pill--partial{
        background:#fff8e1;
        color:#f57f17;
    }
    .ss-when-date{
        font-size:calc(var(--ls-text-base, 0.8125rem) + 1px);
        font-weight:500;
        color:#172033;
        white-space:nowrap;
    }
    .ss-when-time{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
        margin-top:2px;
    }
    .ss-client{
        font-size:calc(var(--ls-text-base, 0.8125rem) + 1px);
        font-weight:500;
        color:#172033;
        line-height:1.3;
        max-width:180px;
    }
    .ss-sub{
        font-size:calc(var(--ls-text-sm, 0.75rem) + 1px);
        font-weight:400;
        color:#5a6a7c;
        margin-top:2px;
        max-width:180px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .ss-location{
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
    }
    .ss-tests{
        display:flex;
        flex-direction:column;
        gap:3px;
        min-width:180px;
        max-width:260px;
    }
    .ss-test-line{
        display:flex;
        align-items:baseline;
        gap:4px;
        font-size:var(--ls-text-sm, 0.75rem);
        font-weight:400;
        line-height:1.35;
        min-width:0;
    }
    .ss-test-type{
        font-weight:500;
        color:var(--ls-color-ink, #1e293b);
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        max-width:90px;
        flex:0 1 auto;
    }
    .ss-test-sep{color:#c4c9d2;flex:0 0 auto;}
    .ss-test-analysis{
        color:var(--ls-color-muted, #64748b);
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        min-width:0;
        flex:1 1 auto;
    }
    .ss-test-params{
        color:var(--ls-color-muted, #64748b);
        font-size:11px;
        flex:0 0 auto;
        margin-left:auto;
        padding-left:6px;
    }
    .ss-test-more{
        font-size:11px;
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        font-weight:500;
    }
    .ss-personnel{
        font-size:var(--ls-text-sm, 0.75rem);
        font-weight:400;
        color:var(--ls-color-ink, #1e293b);
        line-height:1.35;
        max-width:150px;
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
    .ss-forms-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:34px;
        height:28px;
        padding:0 10px;
        border-radius:999px;
        border:1px solid var(--ls-color-border, #d7dde5);
        background:#fff;
        color:var(--ls-color-muted, #64748b);
        font-size:var(--ls-text-sm, 0.75rem);
        font-weight:500;
        line-height:1;
        text-decoration:none;
    }
    .ss-forms-btn.has-forms{
        border-color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        background:var(--ls-color-primary-soft, rgba(138,26,31,0.06));
    }
    .ss-forms-btn:hover{
        border-color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
        color:var(--ls-color-primary, var(--color-primary, #6D0A0E));
    }
    .ss-actions{
        display:inline-flex;
        align-items:center;
        gap:4px;
        white-space:nowrap;
    }
    .ss-act{
        width:30px;
        height:30px;
        border-radius:8px;
        border:1px solid transparent;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:15px;
        padding:0;
        background:transparent;
        transition:background .15s ease,border-color .15s ease;
        text-decoration:none;
    }
    .ss-act--form{color:#856404;background:#fff8e8;border-color:#f0e0b2;}
    .ss-act--label{color:#6d28d9;background:#f5f3ff;border-color:#ddd6fe;}
    .ss-act--qr{color:#0f766e;background:#f0fdfa;border-color:#99f6e4;}
    .ss-act--view{color:#1b5e20;background:#edf7ee;border-color:#c9e6cb;}
    .ss-act--edit{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe;}
    .ss-act--delete{color:#b91c1c;background:#fef2f2;border-color:#fecaca;}
    .ss-act:hover{filter:brightness(0.97);}
    .ss-col-schedule{min-width:180px;}
    .ss-col-when{min-width:96px;}
    .ss-col-client{min-width:150px;}
    .ss-col-tests{min-width:190px;}
    .ss-col-personnel{min-width:120px;}
    .ss-col-forms{width:70px;}
    .ss-col-actions{width:172px;}
    @media (max-width: 991.98px) {
        .schedule-sampling-page .card-body.p-4{padding:1rem!important;}
        .ss-title{max-width:160px;}
        .ss-client,.ss-sub{max-width:140px;}
        .ss-tests{min-width:150px;max-width:200px;}
    }
    @media (max-width: 767.98px) {
        .schedule-page-header{gap:10px;}
        .schedule-page-cta{width:100%;}
        .schedule-toolbar-actions{text-align:left!important;}
        .schedule-toolbar-actions .btn{margin-right:6px!important;}
        .schedule-sampling-table thead th,
        .schedule-sampling-table tbody td{padding:10px 12px;}
        .ss-actions{gap:3px;}
        .ss-act{width:28px;height:28px;font-size:14px;}
        .schedule-sampling-form-modal .modal-dialog,
        .schedule-sampling-page .modal-dialog{margin:0.5rem;max-width:calc(100% - 1rem);}
        .schedule-run-modal__dialog{
            margin:0.5rem auto;
            width:calc(100% - 1rem);
            max-height:calc(100vh - 1rem);
        }
        .schedule-run-modal__content{
            max-height:calc(100vh - 1rem);
        }
    }
    @media (max-width: 575.98px) {
        .schedule-sampling-page h3{font-size:1.15rem;}
        .ss-col-personnel,.ss-col-forms{display:none;}
        .schedule-sampling-table thead th:nth-child(5),
        .schedule-sampling-table tbody td:nth-child(5),
        .schedule-sampling-table thead th:nth-child(6),
        .schedule-sampling-table tbody td:nth-child(6){display:none;}
    }

    .schedule-sampling-page .select2-container{width:100%!important;}
    .schedule-sampling-page .select2-container--default .select2-selection--single,
    .schedule-sampling-page .select2-container--default .select2-selection--multiple{
        min-height:38px;border:1px solid #ced4da;border-radius:0.25rem;
    }
    .schedule-sampling-page .select2-container--default .select2-selection--multiple{
        min-height:38px;max-height:140px;overflow-y:auto;padding:4px 6px;
    }
    .schedule-sampling-page .schedule-run-modal .ss-compact-select .select2-container--default .select2-selection--multiple{
        min-height:38px !important;
        height:38px !important;
        max-height:38px !important;
        overflow:hidden !important;
    }
    .schedule-sampling-page .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background:var(--color-primary-soft, #f3e8e9);
        border:1px solid var(--color-primary-highlight, #e2b8bb);
        color:var(--color-primary, #6D0A0E);
        border-radius:999px;
        padding:2px 8px;
        margin-top:4px;
        font-size:12px;
        font-weight:400;
    }
    .schedule-run-modal .select2-container--default .select2-selection--multiple .select2-selection__choice{
        font-weight:400 !important;
    }
    .schedule-sampling-page .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
        color:var(--color-primary, #6D0A0E);
        margin-right:4px;
    }
    .schedule-sampling-page .select2-dropdown{
        z-index:3000;
        border-color:#ced4da;
    }
    .schedule-sampling-page .select2-results__option--highlighted[aria-selected]{
        background:var(--color-primary, #6D0A0E);
    }
    .schedule-params-field .schedule-params-actions .btn-link{
        text-decoration:none;
    }
    .schedule-params-field .schedule-params-actions .btn-link:hover{
        text-decoration:underline;
    }
    .schedule-sampling-form-modal .trf-signature-pad.acc-signature-pad {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 0.5rem;
    }
    .schedule-sampling-form-modal .trf-signature-pad.acc-signature-pad canvas {
        width: 100%;
        height: 120px;
        display: block;
        border-radius: 6px;
        background: #fff;
    }
    .schedule-sampling-form-modal .walk-in-trf-parameters-wrap .select2-container {
        width: 100% !important;
    }
    .schedule-sampling-form-modal .walk-in-trf-parameters-wrap .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        max-height: 120px;
        overflow-y: auto;
    }
    .schedule-sampling-form-modal .walk-in-trf-parameters-actions {
        gap: 0.25rem;
        line-height: 1.2;
    }
    .schedule-sampling-form-modal .walk-in-trf-parameters-action-btns .btn-link {
        font-size: 12px;
        line-height: 1.2;
        text-decoration: none;
    }
    .schedule-sampling-form-modal .walk-in-trf-parameters-action-btns .btn-link:hover {
        text-decoration: underline;
    }
    .schedule-sampling-form-modal {
        --workflow-accent: var(--color-primary, #6D0A0E);
        --workflow-accent-soft: rgba(138, 26, 31, 0.1);
    }
    .schedule-sampling-form-modal .rft-param-picker {
        position: relative;
        z-index: 5;
    }
    .schedule-sampling-form-modal .rft-param-picker__trigger {
        min-height: 38px;
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 6px 10px;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        text-align: left;
    }
    .schedule-sampling-form-modal .rft-param-picker__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
        align-content: flex-start;
        min-width: 0;
        flex: 1 1 auto;
        width: 100%;
    }
    .schedule-sampling-form-modal .rft-param-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--workflow-accent-soft);
        color: var(--workflow-accent);
        font-size: 0.68rem;
        font-weight: 600;
        max-width: 180px;
        flex: 0 1 auto;
        min-width: 0;
    }
    .schedule-sampling-form-modal .rft-param-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }
    .schedule-sampling-form-modal .rft-param-chip__remove,
    .schedule-sampling-form-modal .rft-param-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        padding: 0;
        line-height: 1;
        cursor: pointer;
        flex: 0 0 auto;
    }
    .schedule-sampling-form-modal .rft-param-picker__caret {
        flex: 0 0 auto;
        margin-top: 4px;
    }
    .schedule-sampling-form-modal .rft-param-picker__more {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: var(--workflow-accent);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        flex: 0 0 auto;
    }
    .schedule-sampling-form-modal .rft-param-picker__panel {
        position: absolute;
        z-index: 1080;
        left: 0;
        right: 0;
        top: calc(100% + 6px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
        padding: 10px;
        max-height: min(320px, 55vh);
        overflow: auto;
    }
    .schedule-sampling-form-modal .rft-param-picker__panel.is-up {
        top: auto;
        bottom: calc(100% + 6px);
    }
    .schedule-sampling-form-modal .rft-param-picker__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-top: 8px;
    }
    .schedule-sampling-form-modal .rft-param-option {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        border: 1px solid #e8ecf2;
        border-radius: 8px;
        padding: 6px 8px;
        cursor: pointer;
        font-size: 0.72rem;
        color: #475569;
        background: #fff;
        margin: 0;
        min-width: 0;
    }
    .schedule-sampling-form-modal .rft-param-option span {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .schedule-sampling-form-modal .rft-param-option.is-selected {
        border-color: var(--workflow-accent);
        background: var(--workflow-accent-soft);
        color: var(--workflow-accent);
        font-weight: 600;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @script
    <script>
        Alpine.data('scheduleSelect2Bridge', (config = {}) => ({
            model: config.model || '',
            multiple: !!config.multiple,
            placeholder: config.placeholder || 'Select...',
            initial: config.initial ?? (config.multiple ? [] : ''),
            countSelector: config.countSelector || null,
            total: config.total ?? null,
            syncing: false,
            init() {
                this.$nextTick(() => this.mount());
            },
            mount() {
                if (typeof $ === 'undefined' || !$.fn.select2) {
                    return;
                }

                const el = this.$el;
                const $el = $(el);
                const modal = el.closest('.modal');

                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.off('change.scheduleSelect2');
                    $el.select2('destroy');
                }

                $el.select2({
                    width: '100%',
                    placeholder: this.placeholder,
                    allowClear: !this.multiple,
                    closeOnSelect: !this.multiple,
                    dropdownParent: modal ? $(modal) : $(document.body),
                });

                const fixCompactSearchField = () => {
                    if (!el.closest('.ss-compact-select')) {
                        return;
                    }
                    const instance = $el.data('select2');
                    const $field = instance?.$selection?.find('.select2-search__field');
                    if ($field && $field.length) {
                        $field.attr('style', 'width:100%!important;max-width:100%!important;min-width:0!important;text-align:left!important;margin:0!important;height:28px!important;box-sizing:border-box!important;');
                    }
                };

                // Select2 rewrites search width on every resize; keep it full-bleed left.
                const instance = $el.data('select2');
                if (instance?.selection && typeof instance.selection.resizeSearch === 'function') {
                    instance.selection.resizeSearch = function () {
                        fixCompactSearchField();
                    };
                }

                const initial = this.multiple
                    ? (Array.isArray(this.initial) ? this.initial.map(String) : [])
                    : (this.initial === null || this.initial === undefined ? '' : String(this.initial));

                this.syncing = true;
                $el.val(initial).trigger('change.select2');
                this.syncing = false;
                this.updateCount($el.val());
                fixCompactSearchField();
                requestAnimationFrame(fixCompactSearchField);
                $el.off('.scheduleSelect2Search')
                    .on('change.scheduleSelect2Search select2:open.scheduleSelect2Search select2:close.scheduleSelect2Search select2:select.scheduleSelect2Search select2:unselect.scheduleSelect2Search', fixCompactSearchField);

                $el.off('change.scheduleSelect2').on('change.scheduleSelect2', () => {
                    if (this.syncing) {
                        return;
                    }

                    let value = $el.val();
                    if (this.multiple) {
                        value = value || [];
                    } else {
                        value = value || '';
                    }

                    this.updateCount(value);

                    if (this.$wire && this.model) {
                        this.$wire.set(this.model, value);
                    }
                });

                const field = el.closest('.schedule-params-field');
                if (field) {
                    const selectAllBtn = field.querySelector('.schedule-params-select-all');
                    const clearBtn = field.querySelector('.schedule-params-clear');

                    if (selectAllBtn) {
                        selectAllBtn.onclick = (event) => {
                            event.preventDefault();
                            const all = $el.find('option').map(function () { return this.value; }).get().filter(Boolean);
                            $el.val(all).trigger('change');
                        };
                    }

                    if (clearBtn) {
                        clearBtn.onclick = (event) => {
                            event.preventDefault();
                            $el.val([]).trigger('change');
                        };
                    }
                }

                if (this.$wire && this.model) {
                    this.$wire.$watch(this.model, (value) => {
                        const next = this.multiple
                            ? (Array.isArray(value) ? value.map(String) : [])
                            : (value === null || value === undefined ? '' : String(value));
                        const current = $el.val() || (this.multiple ? [] : '');
                        const currentNorm = this.multiple
                            ? (Array.isArray(current) ? current.map(String) : [])
                            : String(current || '');
                        const nextNorm = this.multiple ? next.slice().sort().join('|') : next;
                        const currNorm = this.multiple ? currentNorm.slice().sort().join('|') : currentNorm;
                        if (nextNorm === currNorm) {
                            return;
                        }
                        this.syncing = true;
                        $el.val(next).trigger('change.select2');
                        this.syncing = false;
                        this.updateCount(next);
                        fixCompactSearchField();
                    });
                }
            },
            updateCount(value) {
                if (!this.countSelector) {
                    return;
                }

                const field = this.$el.closest('.schedule-params-field');
                if (!field) {
                    return;
                }

                const countEl = field.querySelector(this.countSelector);
                if (!countEl) {
                    return;
                }

                const selected = Array.isArray(value) ? value.length : (value ? 1 : 0);
                const total = this.total ?? this.$el.querySelectorAll('option').length;
                countEl.textContent = `(${selected}/${total} selected)`;
            },
        }));

        @include('livewire.partials.walk-in-trf-rft-param-picker-alpine')

        Alpine.data('rftIdLabelMultiPicker', (config = {}) => ({
            open: false,
            openUp: false,
            search: '',
            syncing: false,
            dirty: false,
            wireKey: config.wireKey || '',
            syncMethod: config.syncMethod || 'setWalkInSampleTypes',
            options: Array.isArray(config.options) ? config.options.slice() : [],
            selected: Array.isArray(config.selected) ? config.selected.map(String) : [],
            placeholder: config.placeholder || 'Choose…',
            emptyHint: config.emptyHint || 'No options available.',
            init() {
                this.applySelectedFromWire();
            },
            get filtered() {
                const query = String(this.search || '').trim().toLowerCase();
                if (!query) {
                    return this.options;
                }

                return this.options.filter((opt) => String(opt.label || '').toLowerCase().includes(query));
            },
            get selectedChips() {
                const map = {};
                this.options.forEach((opt) => { map[String(opt.id)] = opt; });
                return this.selected
                    .map((id) => map[String(id)] || { id: String(id), label: String(id) })
                    .filter(Boolean);
            },
            get visibleChips() {
                return this.selectedChips.slice(0, 8);
            },
            get hiddenCount() {
                return Math.max(0, this.selectedChips.length - 8);
            },
            isSelected(id) {
                return this.selected.includes(String(id));
            },
            toggleOpen() {
                if (this.open) {
                    this.closePanel();
                    return;
                }
                this.open = true;
                this.applySelectedFromWire();
                this.$nextTick(() => this.decideDirection());
            },
            closePanel() {
                this.open = false;
                this.search = '';
                this.flushIfDirty();
            },
            decideDirection() {
                const rect = this.$el.getBoundingClientRect();
                this.openUp = (window.innerHeight - rect.bottom) < 320;
            },
            markDirty() {
                this.dirty = true;
            },
            toggle(id) {
                const value = String(id);
                if (this.isSelected(value)) {
                    this.selected = this.selected.filter((item) => item !== value);
                } else {
                    this.selected = this.selected.concat([value]);
                }
                this.markDirty();
            },
            removeChip(id) {
                const value = String(id);
                this.selected = this.selected.filter((item) => item !== value);
                this.markDirty();
                if (!this.open) {
                    this.flushIfDirty();
                }
            },
            selectAll() {
                this.selected = this.options.map((opt) => String(opt.id));
                this.markDirty();
            },
            clearAll() {
                this.selected = [];
                this.search = '';
                this.markDirty();
            },
            async flushIfDirty() {
                if (!this.dirty || this.syncing || !this.$wire || !this.syncMethod || !this.wireKey) {
                    return;
                }
                this.syncing = true;
                try {
                    await this.$wire[this.syncMethod](this.wireKey, this.selected.slice());
                    this.dirty = false;
                } finally {
                    this.syncing = false;
                }
            },
            applySelectedFromWire() {
                if (!this.$wire || !this.wireKey || this.syncing || this.dirty) {
                    return;
                }
                const raw = this.$wire.get(this.wireKey);
                if (Array.isArray(raw)) {
                    this.selected = raw.map(String).filter(Boolean);
                } else if (raw !== null && raw !== undefined && raw !== '') {
                    this.selected = [String(raw)];
                } else {
                    this.selected = [];
                }
            },
        }));

        (function () {
            const modalSelector = '#schedule-sampling-form-modal';
            let listenersBound = false;

            function getModal() {
                return document.querySelector(modalSelector);
            }

            function destroyScheduleParamSelect($select) {
                if (!$select || !$select.length) {
                    return;
                }

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.off('change.schedule-trf-params');
                    $select.select2('destroy');
                }

                $select.removeData('schedule-trf-params-bound');
            }

            function bindScheduleParamSelect($select) {
                if (!$select || !$select.length || $select.data('schedule-trf-params-bound')) {
                    return;
                }

                const modal = getModal();
                const livewireModel = $select.data('livewire-model');
                const componentEl = $select.closest('[wire\\:id]');
                const wrapEl = $select.closest('.walk-in-trf-parameters-wrap').get(0);

                $select.select2({
                    width: '100%',
                    placeholder: 'Select parameters...',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: modal ? $(modal) : $(document.body),
                });

                $select.on('change.schedule-trf-params', function () {
                    const val = $(this).val() || [];

                    if (wrapEl) {
                        $(wrapEl).attr('data-selected', JSON.stringify(val));
                        updateScheduleParamSelectionCount(wrapEl, val);
                    }

                    if (livewireModel && componentEl && window.Livewire) {
                        const component = Livewire.find(componentEl.getAttribute('wire:id'));
                        if (component) {
                            component.set(livewireModel, val, false);
                        }
                    }
                });

                $select.data('schedule-trf-params-bound', true);
            }

            function updateScheduleParamSelectionCount(wrapEl, selected) {
                if (!wrapEl) {
                    return;
                }

                const countEl = wrapEl.querySelector('.walk-in-trf-parameters-count');
                if (!countEl) {
                    return;
                }

                let options = [];
                try {
                    options = JSON.parse(wrapEl.getAttribute('data-options') || '[]');
                } catch (error) {
                    options = [];
                }

                if (!Array.isArray(options) || options.length === 0) {
                    const $select = $(wrapEl).find('.walk-in-trf-parameters-select');
                    options = $select.find('option').map(function () {
                        return this.value;
                    }).get();
                }

                const selectedCount = Array.isArray(selected) ? selected.length : 0;
                countEl.textContent = options.length > 0
                    ? (selectedCount + '/' + options.length + ' selected')
                    : '';
            }

            function applyScheduleParamBulkSelection(wrapEl, selectAll) {
                if (!wrapEl || typeof $ === 'undefined' || !$.fn.select2) {
                    return;
                }

                const $wrap = $(wrapEl);
                const $select = $wrap.find('.walk-in-trf-parameters-select');
                if ($select.length === 0) {
                    return;
                }

                let options = [];
                try {
                    options = JSON.parse(wrapEl.getAttribute('data-options') || '[]');
                } catch (error) {
                    options = [];
                }

                if (!Array.isArray(options) || options.length === 0) {
                    options = $select.find('option').map(function () {
                        return this.value;
                    }).get();
                }

                const selected = selectAll ? options : [];
                $select.val(selected).trigger('change');
                $wrap.attr('data-selected', JSON.stringify(selected));
                updateScheduleParamSelectionCount(wrapEl, selected);
            }

            window.resetScheduleTrfParamRow = function (wrapEl, options, selected) {
                if (!wrapEl || typeof $ === 'undefined' || !$.fn.select2) {
                    return;
                }

                const $wrap = $(wrapEl);
                const $select = $wrap.find('.walk-in-trf-parameters-select');
                const livewireModel = $wrap.data('livewire-model');
                const componentEl = $wrap.closest('[wire\\:id]');
                const safeSelected = Array.isArray(selected) ? selected : [];
                const safeOptions = Array.isArray(options) ? options : [];

                destroyScheduleParamSelect($select);
                $select.empty();

                safeOptions.forEach(function (name) {
                    const isSelected = safeSelected.indexOf(name) !== -1;
                    $select.append(new Option(name, name, isSelected, isSelected));
                });

                bindScheduleParamSelect($select);
                $select.val(safeSelected).trigger('change.select2');
                $wrap.attr('data-options', JSON.stringify(safeOptions));
                $wrap.attr('data-selected', JSON.stringify(safeSelected));
                updateScheduleParamSelectionCount(wrapEl, safeSelected);
                $wrap.find('[data-walk-in-params-action]').prop('disabled', safeOptions.length === 0);

                if (livewireModel && componentEl && window.Livewire) {
                    const component = Livewire.find(componentEl.getAttribute('wire:id'));
                    if (component) {
                        component.set(livewireModel, safeSelected, false);
                    }
                }
            };

            window.initScheduleTrfParameterSelects = function (options) {
                options = options || {};
                const targetRowIndex = options.rowIndex;
                const modal = getModal();

                if (!modal || typeof $ === 'undefined' || !$.fn.select2) {
                    return;
                }

                modal.querySelectorAll('.walk-in-trf-parameters-wrap').forEach(function (wrap) {
                    const rowIndex = wrap.getAttribute('data-row-index');
                    if (targetRowIndex !== undefined && targetRowIndex !== null && String(rowIndex) !== String(targetRowIndex)) {
                        return;
                    }

                    const $select = $(wrap).find('.walk-in-trf-parameters-select');
                    if ($select.length === 0 || $select.data('schedule-trf-params-bound')) {
                        return;
                    }

                    let selected = [];
                    try {
                        selected = JSON.parse(wrap.getAttribute('data-selected') || '[]');
                    } catch (error) {
                        selected = [];
                    }

                    bindScheduleParamSelect($select);
                    $select.val(selected).trigger('change.select2');
                    updateScheduleParamSelectionCount(wrap, selected);
                });
            };

            $(document).off('click.schedule-trf-params-bulk', modalSelector + ' [data-walk-in-params-action]')
                .on('click.schedule-trf-params-bulk', modalSelector + ' [data-walk-in-params-action]', function (event) {
                    event.preventDefault();
                    const wrap = this.closest('.walk-in-trf-parameters-wrap');
                    if (!wrap || this.disabled) {
                        return;
                    }

                    applyScheduleParamBulkSelection(
                        wrap,
                        this.getAttribute('data-walk-in-params-action') === 'select-all',
                    );
                });

            function syncSignatureValue(canvas, input, pad) {
                const value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
                input.value = value;

                const livewireModel = canvas.getAttribute('data-livewire-model');
                const componentEl = canvas.closest('[wire\\:id]');
                if (livewireModel && componentEl && window.Livewire) {
                    const component = Livewire.find(componentEl.getAttribute('wire:id'));
                    if (component) {
                        component.set(livewireModel, value, false);
                    }
                }

                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function resolveCanvasSize(canvas) {
                const rect = canvas.getBoundingClientRect();
                const width = Math.max(rect.width, canvas.clientWidth, canvas.offsetWidth, 0);
                const height = Math.max(rect.height, canvas.clientHeight, canvas.offsetHeight, 120);

                return {
                    width: width > 10 ? width : (canvas.parentElement?.clientWidth || 0),
                    height: height > 10 ? height : 120,
                };
            }

            function setupCanvas(canvas, forceReinit) {
                if (forceReinit) {
                    if (canvas._trfSignaturePad) {
                        try {
                            canvas._trfSignaturePad.off();
                        } catch (e) {}
                        canvas._trfSignaturePad = null;
                    }
                    delete canvas.dataset.signatureInitialized;
                }

                const fieldId = canvas.getAttribute('data-field');
                const input = document.getElementById('field_' + fieldId);
                if (!input || !canvas.parentElement) {
                    return false;
                }

                const size = resolveCanvasSize(canvas);
                if (size.width < 10 || size.height < 10) {
                    delete canvas.dataset.signatureInitialized;
                    return false;
                }

                if (canvas.dataset.signatureInitialized === '1' && canvas._trfSignaturePad) {
                    return true;
                }

                if (typeof SignaturePad === 'undefined') {
                    return false;
                }

                canvas.dataset.signatureInitialized = '1';
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = size.width * ratio;
                canvas.height = size.height * ratio;
                canvas.style.width = size.width + 'px';
                canvas.style.height = size.height + 'px';
                const context = canvas.getContext('2d');
                context.setTransform(1, 0, 0, 1, 0, 0);
                context.scale(ratio, ratio);

                const pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255,255,255)',
                    penColor: 'rgb(0,0,0)',
                });
                canvas._trfSignaturePad = pad;

                if (input.value) {
                    try {
                        pad.fromDataURL(input.value);
                    } catch (error) {}
                }

                pad.addEventListener('endStroke', function () {
                    syncSignatureValue(canvas, input, pad);
                });

                const clearBtn = canvas.parentElement.querySelector('.trf-signature-clear[data-canvas="' + canvas.id + '"]');
                if (clearBtn) {
                    clearBtn.onclick = function (event) {
                        event.preventDefault();
                        pad.clear();
                        syncSignatureValue(canvas, input, pad);
                    };
                }

                return true;
            }

            window.initScheduleTrfSignaturePads = function (forceReinit, attempt) {
                const modal = getModal();
                if (!modal) {
                    return;
                }

                if (typeof SignaturePad === 'undefined') {
                    if ((attempt || 0) < 8) {
                        setTimeout(function () {
                            window.initScheduleTrfSignaturePads(forceReinit, (attempt || 0) + 1);
                        }, 200);
                    }
                    return;
                }

                let pending = false;
                modal.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
                    if (!setupCanvas(canvas, !!forceReinit)) {
                        pending = true;
                    }
                });

                if (pending && (attempt || 0) < 10) {
                    setTimeout(function () {
                        window.initScheduleTrfSignaturePads(true, (attempt || 0) + 1);
                    }, 150);
                }
            };

            window.syncScheduleTrfBeforeSubmit = function () {
                const modal = getModal();
                if (!modal) {
                    return;
                }

                modal.querySelectorAll('.walk-in-trf-parameters-wrap').forEach(function (wrap) {
                    const $select = $(wrap).find('.walk-in-trf-parameters-select');
                    if ($select.length === 0) {
                        return;
                    }

                    const livewireModel = $(wrap).data('livewire-model') || $select.data('livewire-model');
                    const componentEl = wrap.closest('[wire\\:id]');
                    const selected = $select.val() || [];

                    if (livewireModel && componentEl && window.Livewire) {
                        const component = Livewire.find(componentEl.getAttribute('wire:id'));
                        if (component) {
                            component.set(livewireModel, selected, false);
                        }
                    }
                });

                if (typeof SignaturePad === 'undefined') {
                    return;
                }

                modal.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
                    const pad = canvas._trfSignaturePad;
                    const fieldId = canvas.getAttribute('data-field');
                    if (!pad || !fieldId) {
                        return;
                    }

                    const input = document.getElementById('field_' + fieldId);
                    if (!input) {
                        return;
                    }

                    syncSignatureValue(canvas, input, pad);
                });
            };

            window.syncScheduleTrfBeforeSubmitAndSave = async function () {
                const modal = getModal();
                if (!modal || !window.Livewire) {
                    return;
                }

                const componentEl = modal.closest('[wire\\:id]');
                if (!componentEl) {
                    return;
                }

                const component = Livewire.find(componentEl.getAttribute('wire:id'));
                if (!component) {
                    return;
                }

                const syncTasks = [];

                modal.querySelectorAll('.walk-in-trf-parameters-wrap').forEach(function (wrap) {
                    const $select = $(wrap).find('.walk-in-trf-parameters-select');
                    if ($select.length === 0) {
                        return;
                    }

                    const livewireModel = $(wrap).data('livewire-model') || $select.data('livewire-model');
                    const selected = $select.val() || [];

                    if (livewireModel) {
                        syncTasks.push(component.set(livewireModel, selected));
                    }
                });

                if (typeof SignaturePad !== 'undefined') {
                    modal.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
                        const pad = canvas._trfSignaturePad;
                        const fieldId = canvas.getAttribute('data-field');
                        if (!pad || !fieldId) {
                            return;
                        }

                        const input = document.getElementById('field_' + fieldId);
                        if (!input) {
                            return;
                        }

                        syncSignatureValue(canvas, input, pad);
                    });
                }

                if (syncTasks.length > 0) {
                    await Promise.all(syncTasks);
                }

                component.call('saveScheduleForm');
            };

            function reinitScheduleTrfWidgets() {
                setTimeout(function () {
                    window.initScheduleTrfSignaturePads(true);
                    window.initScheduleTrfParameterSelects();
                }, 200);
            }

            function bindListeners() {
                if (listenersBound || typeof Livewire === 'undefined') {
                    return;
                }

                listenersBound = true;

                Livewire.on('schedule-trf-reinit-widgets', reinitScheduleTrfWidgets);

                Livewire.on('schedule-trf-params-row-reset', function (payload) {
                    setTimeout(function () {
                        const data = Array.isArray(payload) ? payload[0] : payload;
                        const modal = getModal();
                        if (!modal || data?.rowIndex === undefined || typeof window.resetScheduleTrfParamRow !== 'function') {
                            return;
                        }

                        const wrap = modal.querySelector('.walk-in-trf-parameters-wrap[data-row-index="' + data.rowIndex + '"]');
                        if (!wrap) {
                            return;
                        }

                        window.resetScheduleTrfParamRow(wrap, data.options || [], data.selected || []);
                    }, 80);
                });

                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        if (!getModal()) {
                            return;
                        }

                        queueMicrotask(function () {
                            window.initScheduleTrfSignaturePads(false);
                            window.initScheduleTrfParameterSelects();
                        });
                    });
                });
            }

            bindListeners();
            document.addEventListener('livewire:init', bindListeners);
            document.addEventListener('livewire:navigated', function () {
                listenersBound = false;
                bindListeners();
                if (getModal()) {
                    reinitScheduleTrfWidgets();
                }
            });

            document.addEventListener('click', function (event) {
                const toggle = event.target.closest(
                    '[wire\\:click*="openScheduleFormModal"], [wire\\:click*="showFormModal"]'
                );
                if (toggle) {
                    setTimeout(reinitScheduleTrfWidgets, 250);
                }
            });

            if (getModal()) {
                reinitScheduleTrfWidgets();
            }
        })();

        (function () {
            function getRunModalBody() {
                return document.getElementById('schedule-run-modal-body');
            }

            function setActiveNav(hash) {
                document.querySelectorAll('.ss-form-nav__item').forEach(function (item) {
                    item.classList.toggle('is-active', item.getAttribute('href') === hash);
                });
            }

            function scrollToSection(hash) {
                const body = getRunModalBody();
                if (!body || !hash) {
                    return;
                }
                const target = body.querySelector(hash);
                if (!target) {
                    return;
                }
                const top = target.offsetTop - 8;
                body.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                setActiveNav(hash);
            }

            function syncActiveFromScroll() {
                const body = getRunModalBody();
                if (!body) {
                    return;
                }
                const sections = body.querySelectorAll('.ss-section[id]');
                let active = null;
                const scrollTop = body.scrollTop + 24;
                sections.forEach(function (section) {
                    if (section.offsetTop <= scrollTop) {
                        active = '#' + section.id;
                    }
                });
                if (active) {
                    setActiveNav(active);
                }
            }

            document.addEventListener('click', function (event) {
                const link = event.target.closest('[data-ss-nav]');
                if (!link) {
                    return;
                }
                event.preventDefault();
                scrollToSection(link.getAttribute('href'));
            });

            document.addEventListener('scroll', function (event) {
                if (event.target && event.target.id === 'schedule-run-modal-body') {
                    syncActiveFromScroll();
                }
            }, true);

            document.addEventListener('livewire:navigated', function () {
                setTimeout(function () {
                    const first = document.querySelector('.ss-form-nav__item');
                    if (first) {
                        setActiveNav(first.getAttribute('href'));
                    }
                }, 50);
            });
        })();
    </script>
    @endscript
</div>
