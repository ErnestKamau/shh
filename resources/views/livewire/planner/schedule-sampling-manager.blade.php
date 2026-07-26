<div class="container-fluid schedule-sampling-page">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap schedule-page-header">
                        <div class="mb-2 mb-md-0 pr-md-3">
                            <h3 class="mb-1 font-weight-bold"><i class="mdi mdi-clock-outline text-primary"></i> {{ __('planner.sampling_schedules') }}</h3>
                            <p class="text-muted mb-0">{{ __('planner.sampling_schedules_subtitle') }}</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary schedule-page-cta" style="border-radius:30px;padding:0.6rem 1.8rem;font-weight:600;">
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
    <div class="card shadow-sm border-0 mb-3" style="border-radius:15px;background:linear-gradient(135deg,#f8f9fa,#e9ecef);">
        <div class="card-body p-4">
            <div class="row">
                {{-- Date Range --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">{{ __('planner.date_from') }}</label>
                    <input type="date" wire:model.live="filterDateFrom" class="form-control" style="border-radius:8px;">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">{{ __('planner.date_to') }}</label>
                    <input type="date" wire:model.live="filterDateTo" class="form-control" style="border-radius:8px;">
                </div>

                {{-- Client --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">{{ __('planner.client') }}</label>
                    <select wire:model.live="filterClientId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">{{ __('planner.all_clients') }}</option>
                        @foreach($clients as $client)
                        <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Frequency --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">{{ __('planner.frequency') }}</label>
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
                    <label class="font-weight-bold text-muted small text-uppercase">Sample Type</label>
                    <select wire:model.live="filterSampleTypeId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Sample Types</option>
                        @foreach($allSampleTypes as $st)
                        <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Analysis Type --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">Analysis Type</label>
                    <select wire:model.live="filterAnalysisTypeId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Analysis Types</option>
                        @foreach($allAnalysisTypes as $at)
                        <option value="{{ $at['id'] }}">{{ $at['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Parameters --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">Parameters</label>
                    <select wire:model.live="filterParameterId" class="form-control no-select2" style="border-radius:8px;">
                        <option value="">All Parameters</option>
                        @foreach($allParameters as $param)
                        <option value="{{ $param['id'] }}">{{ $param['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sample Count Range --}}
                <div class="col-md-3 form-group mb-3">
                    <label class="font-weight-bold text-muted small text-uppercase">Samples Range</label>
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
                        @forelse($this->schedules as $s)
                        @php
                            $details = $this->resolveSampleDetails($s);
                            $tableContacts = $s->contacts();
                            $contactNames = $tableContacts->map(fn ($c) => trim(($c->first_name ?? '').' '.($c->last_name ?? '')))->filter()->values();
                            $personnelLabel = $s->personnelNames();
                            $formCount = $s->submissionFormInstances->count();
                            $collectionProgress = $s->collectionProgress();
                            $visibleDetails = array_slice($details, 0, 2);
                            $hiddenDetailsCount = max(0, count($details) - 2);
                        @endphp
                        <tr>
                            <td class="ss-col-schedule">
                                <div class="ss-title">{{ $s->title }}</div>
                                <div class="ss-meta">
                                    <span>{{ $s->frequency ?: 'One-time' }}</span>
                                    <span class="ss-dot"></span>
                                    <span>{{ $collectionProgress['collected'] }}/{{ $collectionProgress['scheduled'] }} sample{{ $collectionProgress['scheduled'] === 1 ? '' : 's' }}</span>
                                    @if($collectionProgress['status'] === 'collected')
                                    <span class="ss-pill ss-pill--ok">{{ __('planner.collected') }}</span>
                                    @elseif($collectionProgress['status'] === 'partial')
                                    <span class="ss-pill ss-pill--partial">{{ __('planner.partial') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="ss-col-when">
                                @if($s->sampling_datetime)
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
                                <button type="button"
                                        class="ss-forms-btn {{ $formCount > 0 ? 'has-forms' : '' }}"
                                        wire:click="viewTrfForms('{{ $s->id }}')"
                                        title="View submitted sampling forms">
                                    {{ $formCount }}
                                </button>
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
                                        <a href="{{ route('system-planner.fill-sampling-forms') }}"
                                           class="ss-act ss-act--form"
                                           title="Fill sampling form"
                                           aria-label="Fill sampling form">
                                            <i class="mdi mdi-clipboard-edit-outline"></i>
                                        </a>
                                    @endif
                                    <button wire:click="viewSchedule('{{ $s->id }}')" class="ss-act ss-act--view" title="View schedule"><i class="mdi mdi-eye-outline"></i></button>
                                    <button wire:click="showEditModal('{{ $s->id }}')" class="ss-act ss-act--edit" title="Edit"><i class="mdi mdi-pencil-outline"></i></button>
                                    <button wire:click="delete('{{ $s->id }}')" class="ss-act ss-act--delete" title="Delete" onclick="return confirm('Are you sure you want to delete this schedule?')"><i class="mdi mdi-trash-can-outline"></i></button>
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
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--color-primary),#8a1a1f);flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-clock-outline mr-2"></i>{{ $editingSchedule ? 'Edit' : 'Schedule' }} Sampling Run</h5>
                    <button type="button" class="close text-white" wire:click="closeModal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4" style="overflow-y:auto;flex:1 1 auto;">

                    {{-- Basic Info --}}
                    <div class="form-section-title"><i class="mdi mdi-information-outline"></i> Basic Info</div>
                    <div class="form-group">
                        <label class="font-weight-bold">Schedule Title <span class="text-danger">*</span></label>
                        <input type="text" wire:model="form.title" class="form-control" placeholder="e.g. Monthly Water Sampling at Site A">
                        @error('form.title') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    {{-- Client --}}
                    <div class="form-section-title"><i class="mdi mdi-account-outline"></i> Client Selection</div>
                    <div class="form-group">
                        <label class="font-weight-bold">Client / Customer Name <span class="text-danger">*</span></label>
                        <div wire:ignore wire:key="schedule-client-{{ $editingSchedule->id ?? 'create' }}">
                            <select class="form-control no-select2"
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
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Contract Valid From <span class="badge badge-info" style="font-size:10px;">Prefilled</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $contractValidFrom }}" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Contract Valid To <span class="badge badge-info" style="font-size:10px;">Prefilled</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $contractValidTo }}" readonly>
                        </div>
                    </div>
                    @endif

                    @if(!empty($form['crm_customer_id']))
                    <div class="form-group" wire:key="schedule-contacts-{{ $form['crm_customer_id'] }}-{{ count($customerContactOptions) }}">
                        <label class="font-weight-bold">Customer Contact Personnel</label>
                        @if(count($customerContactOptions) > 0)
                        <div wire:ignore>
                            <select class="form-control no-select2" multiple
                                    x-data="scheduleSelect2Bridge({
                                        model: 'form.contact_ids',
                                        multiple: true,
                                        placeholder: 'Search and select contacts...',
                                        initial: @js(array_values(array_map('strval', $form['contact_ids'] ?? []))),
                                    })">
                                @foreach($customerContactOptions as $cc)
                                <option value="{{ $cc['id'] }}">{{ $cc['name'] }}{{ !empty($cc['email']) ? ' — '.$cc['email'] : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">You can select more than one contact. Notify Client emails go to these contacts.</small>
                        @else
                        <p class="text-muted mb-1">No active contacts found for this customer.</p>
                        @endif
                        @error('form.contact_ids') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    </div>
                    @if(count($selectedContactsSummary) > 0)
                    <div class="mb-3">
                        @foreach($selectedContactsSummary as $summary)
                        <div class="alert alert-light border p-2 mb-2" style="border-radius:8px;">
                            <strong>{{ $summary['name'] }}</strong>
                            <div class="d-flex flex-wrap" style="gap:12px;">
                                <small class="text-muted">Email: <strong class="text-dark">{{ $summary['email'] ?: 'N/A' }}</strong></small>
                                <small class="text-muted">Phone: <strong class="text-dark">{{ $summary['phone'] ?: 'N/A' }}</strong></small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif

                    {{-- Timeline --}}
                    <div class="form-section-title"><i class="mdi mdi-calendar-clock"></i> Timeline & Location</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" wire:model="form.sampling_datetime" class="form-control">
                            @error('form.sampling_datetime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="font-weight-bold mb-0">Location (Sample Point) <span class="text-danger">*</span></label>
                                <button type="button"
                                        class="btn btn-xs btn-outline-primary py-0 px-2"
                                        wire:click="openAddSamplePointModal('schedule')"
                                        @disabled(empty($form['crm_customer_id']))
                                        title="Add sample point">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>
                            @if(!empty($form['crm_customer_id']))
                            <div wire:ignore wire:key="schedule-sample-point-{{ $form['crm_customer_id'] }}-{{ count($customerSamplePointOptions) }}-{{ $form['sample_point_id'] ?: 'none' }}">
                                <select class="form-control no-select2"
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
                            <input type="text" class="form-control bg-light" value="" placeholder="Select a customer first" disabled>
                            @endif
                            <small class="text-muted">Locations come from the customer's sample points.</small>
                            @error('form.sample_point_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Multi Sample Details --}}
                    <div class="form-section-title d-flex justify-content-between align-items-center">
                        <span><i class="mdi mdi-flask-outline"></i> Sample Details</span>
                        <button type="button" wire:click="addSampleEntry" class="btn btn-sm btn-outline-primary" style="border-radius:20px;"><i class="mdi mdi-plus"></i> Add Sample Type</button>
                    </div>

                    @foreach($sampleEntries as $idx => $entry)
                    <div class="card border mb-3" style="border-radius:10px;" wire:key="sample-entry-{{ $idx }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-muted" style="font-size:12px;">Sample Entry #{{ $idx + 1 }}</strong>
                                <button type="button" wire:click="removeSampleEntry({{ $idx }})" class="btn btn-sm btn-outline-danger" style="border-radius:50%;width:28px;height:28px;padding:0;"><i class="mdi mdi-close"></i></button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Sample Type</label>
                                    <select wire:model.live="sampleEntries.{{ $idx }}.sample_type_id" class="form-control no-select2">
                                        <option value="">Select Sample Type</option>
                                        @foreach($allSampleTypes as $st)
                                        <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Analysis Type</label>
                                    <select wire:model.live="sampleEntries.{{ $idx }}.analysis_type_id" class="form-control no-select2" {{ empty($entry['analysisTypes']) ? 'disabled' : '' }}>
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
                                    <label class="font-weight-bold mb-0">Parameters / Analytes
                                        <small class="text-muted font-weight-normal ml-1 schedule-params-count">
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
                    </div>
                    @endforeach

                    @if(empty($sampleEntries))
                    <p class="text-muted text-center py-2"><i class="mdi mdi-information-outline"></i> Click "Add Sample Type" to add sample details.</p>
                    @endif

                    {{-- Operations --}}
                    <div class="form-section-title"><i class="mdi mdi-account-multiple-outline"></i> Operations & Logistics</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">No. of Samples</label>
                            <input type="number" class="form-control bg-light" value="{{ $form['number_of_samples'] }}" min="1" readonly
                                   title="Automatically set from the number of sample entries added">
                            <small class="text-muted">Auto-set from sample entries added above.</small>
                            @error('form.number_of_samples') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Frequency <span class="text-danger">*</span></label>
                            <select wire:model="form.frequency" class="form-control no-select2">
                                <option value="One-time">One-time</option>
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Annually">Annually</option>
                            </select>
                            @error('form.frequency') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group d-flex align-items-center pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="form.notify_client" id="notify_client_lw">
                                <label class="form-check-label font-weight-bold" for="notify_client_lw">Notify Client</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Personnel Carrying Out Sampling <span class="text-danger">*</span></label>
                        <div wire:ignore wire:key="schedule-personnel-{{ $editingSchedule->id ?? 'create' }}">
                            <select class="form-control no-select2" multiple
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
                    <div class="form-group">
                        <label class="font-weight-bold">Description / Special Instructions</label>
                        <textarea wire:model="form.description" class="form-control" rows="3" placeholder="Enter special instructions or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3" style="flex-shrink:0;">
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
                    <h5 class="modal-title font-weight-bold mb-0">Add sample point</h5>
                    <button type="button" class="close" wire:click="closeAddSamplePointModal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" wire:model="newSamplePointName" placeholder="Sample point name">
                        @error('newSamplePointName') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Client unit <span class="text-danger">*</span></label>
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

    <!-- ═══ VIEW MODAL ═══ -->
    @if($showViewModal && $viewingSchedule)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--color-primary),#8a1a1f);flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-eye mr-2"></i> Schedule Details</h5>
                    <button type="button" class="close text-white" wire:click="closeModal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0" style="overflow-y:auto;flex:1 1 auto;">
                    {{-- Title banner --}}
                    @php $viewProgress = $viewingSchedule->collectionProgress(); @endphp
                    <div class="px-4 pt-4 pb-3" style="background:#f8f9fa;border-bottom:1px solid #e9ecef;">
                        <h4 class="mb-1 font-weight-bold"><i class="mdi mdi-calendar-check text-primary mr-1"></i> {{ $viewingSchedule->title }}</h4>
                        <span class="badge badge-pill" style="background:#e8f5e9;color:#2e7d32;padding:6px 14px;font-size:12px;"><i class="mdi mdi-clock-outline mr-1"></i>{{ $viewingSchedule->sampling_datetime ? $viewingSchedule->sampling_datetime->format('D, d M Y \a\t H:i') : 'N/A' }}</span>
                        <span class="badge badge-pill ml-1" style="background:var(--color-primary-soft);color:var(--color-primary);padding:6px 14px;font-size:12px;"><i class="mdi mdi-refresh mr-1"></i>{{ $viewingSchedule->frequency }}</span>
                        @if($viewingSchedule->notify_client)
                        <span class="badge badge-pill ml-1" style="background:#fff3e0;color:#e65100;padding:6px 14px;font-size:12px;"><i class="mdi mdi-bell-ring mr-1"></i>Client Notified</span>
                        @endif
                        @if($viewProgress['status'] === 'collected')
                        <span class="badge badge-pill ml-1" style="background:#e8f5e9;color:#2e7d32;padding:6px 14px;font-size:12px;"><i class="mdi mdi-check-circle-outline mr-1"></i>{{ __('planner.collected') }}</span>
                        @elseif($viewProgress['status'] === 'partial')
                        <span class="badge badge-pill ml-1" style="background:#fff8e1;color:#f57f17;padding:6px 14px;font-size:12px;"><i class="mdi mdi-progress-clock mr-1"></i>{{ __('planner.partial') }} ({{ $viewProgress['label'] }})</span>
                        @endif
                    </div>

                    <div class="p-4">
                        {{-- Client & Contact --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div style="width:36px;height:36px;border-radius:8px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;" class="mr-2"><i class="mdi mdi-domain text-primary"></i></div>
                                            <small class="text-muted text-uppercase font-weight-bold" style="letter-spacing:0.5px;">Client</small>
                                        </div>
                                        <h6 class="font-weight-bold mb-0">{{ $viewingSchedule->client->name ?? 'N/A' }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div style="width:36px;height:36px;border-radius:8px;background:#fce4ec;display:flex;align-items:center;justify-content:center;" class="mr-2"><i class="mdi mdi-account-tie text-danger"></i></div>
                                            <small class="text-muted text-uppercase font-weight-bold" style="letter-spacing:0.5px;">Contact(s)</small>
                                        </div>
                                        @php $viewContacts = $viewingSchedule->contacts(); @endphp
                                        @if($viewContacts->isNotEmpty())
                                            @foreach($viewContacts as $vc)
                                            <div class="{{ !$loop->last ? 'mb-2' : '' }}">
                                                <h6 class="font-weight-bold mb-0">{{ trim(($vc->first_name ?? '').' '.($vc->last_name ?? '')) ?: 'Contact' }}</h6>
                                                @if(!empty($vc->email))
                                                <small class="text-muted d-block">{{ $vc->email }}</small>
                                                @endif
                                                @php $viewPhone = $vc->telephone ?: $vc->mobile; @endphp
                                                @if(!empty($viewPhone))
                                                <small class="text-muted d-block">{{ $viewPhone }}</small>
                                                @endif
                                            </div>
                                            @endforeach
                                        @else
                                            <h6 class="font-weight-bold mb-0">N/A</h6>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Location & Operations --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3 text-center">
                                        <i class="mdi mdi-map-marker-radius text-danger" style="font-size:24px;"></i>
                                        <small class="text-muted d-block mt-1">Location</small>
                                        <strong>{{ $viewingSchedule->locationDisplayName() }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3 text-center">
                                        <i class="mdi mdi-flask text-info" style="font-size:24px;"></i>
                                        <small class="text-muted d-block mt-1">{{ __('planner.samples_collected_vs_scheduled') }}</small>
                                        <strong style="font-size:20px;">{{ $viewProgress['label'] }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3 text-center">
                                        <i class="mdi mdi-account-hard-hat text-warning" style="font-size:24px;"></i>
                                        <small class="text-muted d-block mt-1">Personnel</small>
                                        <strong>{{ $viewingSchedule->personnelNames() }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sample Details --}}
                        @php $vDetails = $this->resolveDetailedSampleDetails($viewingSchedule); @endphp
                        @if(!empty($vDetails))
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size:12px;letter-spacing:1px;">
                                <i class="mdi mdi-flask-outline mr-1"></i>Sample Details
                            </h6>
                            @foreach($vDetails as $i => $d)
                            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;overflow:hidden;">
                                <div class="card-header py-2 px-3" style="background:linear-gradient(135deg,var(--color-primary-soft),var(--color-primary-soft-light));border-bottom:1px solid var(--color-primary-highlight);">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-primary mr-2" style="border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;">{{ $i+1 }}</span>
                                        <span class="font-weight-bold text-primary" style="font-size:14px;">{{ $d['type'] }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    {{-- Analysis Type --}}
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase font-weight-bold d-block mb-1" style="font-size:10px;letter-spacing:0.5px;">
                                            <i class="mdi mdi-microscope mr-1"></i>Analysis Type
                                        </small>
                                        @if($d['analysis'])
                                        <span class="badge badge-info px-3 py-2" style="font-size:13px;border-radius:6px;">
                                            {{ $d['analysis'] }}
                                        </span>
                                        @else
                                        <span class="text-muted font-italic">Not specified</span>
                                        @endif
                                    </div>

                                    {{-- Parameters --}}
                                    @if(!empty($d['param_names']))
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block mb-2" style="font-size:10px;letter-spacing:0.5px;">
                                            <i class="mdi mdi-test-tube mr-1"></i>Parameters ({{ $d['params_count'] }})
                                        </small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($d['param_names'] as $paramName)
                                            <span class="badge badge-outline-secondary mr-1 mb-1 px-2 py-1" style="font-size:12px;border:1px solid #dee2e6;background:#f8f9fa;color:#495057;border-radius:4px;">
                                                {{ $paramName }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block mb-1" style="font-size:10px;letter-spacing:0.5px;">
                                            <i class="mdi mdi-test-tube mr-1"></i>Parameters
                                        </small>
                                        <span class="badge badge-light px-3 py-2" style="font-size:12px;color:#6c757d;">
                                            No parameters selected
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Tied Request Forms --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-uppercase text-muted mb-0" style="font-size:12px;letter-spacing:1px;">
                                    <i class="mdi mdi-file-document-outline mr-1"></i>Test Request Forms
                                    <span class="badge badge-light ml-1">{{ $viewingSchedule->submissionFormInstances->count() }}</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-info" style="border-radius:16px;"
                                        wire:click="viewTrfForms('{{ $viewingSchedule->id }}')">
                                    View all
                                </button>
                            </div>
                            @if($viewingSchedule->submissionFormInstances->count() > 0)
                            <div class="p-0" style="background:#fafbfc;border-radius:8px;border:1px solid #eee;overflow:hidden;max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="px-3 py-2" style="font-size:11px;font-weight:bold;color:#495057;">Form Title / Sample Type</th>
                                            <th class="px-3 py-2" style="font-size:11px;font-weight:bold;color:#495057;">Submitted By</th>
                                            <th class="px-3 py-2" style="font-size:11px;font-weight:bold;color:#495057;">Status</th>
                                            <th class="px-3 py-2" style="font-size:11px;font-weight:bold;color:#495057;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($viewingSchedule->submissionFormInstances->take(5) as $instance)
                                        <tr style="border-top: 1px solid #eee;">
                                            <td class="px-3 py-2">
                                                <div class="font-weight-bold" style="font-size:13px;color:#333;">{{ $instance->submissionForm?->name ?? 'Request Form' }}</div>
                                                <small class="text-muted">{{ $instance->selectedSampleTypeName() ?? $instance->submissionForm?->sampleTypes->first()?->name ?? 'N/A' }}</small>
                                            </td>
                                            <td class="px-3 py-2" style="font-size:12px;vertical-align:middle;color:#555;">
                                                {{ $instance->submittedBy?->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-3 py-2" style="vertical-align:middle;">
                                                <span class="badge badge-success" style="font-size:11px;padding:3px 8px;">{{ ucfirst($instance->status) }}</span>
                                            </td>
                                            <td class="px-3 py-2" style="font-size:12px;vertical-align:middle;color:#6c757d;">
                                                {{ $instance->submitted_at?->format('M d, Y H:i') ?? $instance->created_at?->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-light border mb-0" style="border-radius:8px;">
                                No test request forms have been filled for this schedule yet.
                            </div>
                            @endif
                        </div>

                        {{-- Description --}}
                        @if($viewingSchedule->description)
                        <div class="mb-2">
                            <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:12px;letter-spacing:1px;"><i class="mdi mdi-text mr-1"></i>Description</h6>
                            <div class="p-3" style="background:#fafbfc;border-radius:8px;border:1px solid #eee;">
                                <p class="mb-0" style="white-space:pre-wrap;">{{ $viewingSchedule->description }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer bg-light p-3" style="flex-shrink:0;">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal"><i class="mdi mdi-close mr-1"></i>Close</button>
                    <button type="button" class="btn btn-primary" wire:click="showEditModal('{{ $viewingSchedule->id }}')"><i class="mdi mdi-pencil mr-1"></i>Edit</button>
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
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--color-primary),#8a1a1f);flex-shrink:0;">
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
                                    <td class="px-3 py-2">{{ $instance->selectedSampleTypeName() ?? $instance->submissionForm?->sampleTypes->first()?->name ?? 'N/A' }}</td>
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
                        <a href="{{ route('system-planner.fill-sampling-forms') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus mr-1"></i>Fill Sampling Form
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
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--color-primary),#8a1a1f);flex-shrink:0;">
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
    .form-section-title{font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--color-primary);border-left:4px solid var(--color-primary);padding-left:8px;margin-top:1.5rem;margin-bottom:1rem;}
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

    .schedule-table-card{
        border-radius:14px;
        overflow:hidden;
        border:1px solid #e8ecf1;
    }
    .schedule-sampling-table{
        width:100%;
        margin:0;
    }
    .schedule-sampling-table thead th{
        background:#f7f8fa;
        border:0;
        border-bottom:1px solid #e8ecf1;
        color:#6b7280;
        font-size:11px;
        font-weight:700;
        letter-spacing:0.04em;
        text-transform:uppercase;
        padding:12px 14px;
        white-space:nowrap;
        vertical-align:middle;
    }
    .schedule-sampling-table tbody td{
        border-top:1px solid #eef1f5;
        padding:12px 14px;
        vertical-align:middle;
        color:#1f2937;
    }
    .schedule-sampling-table tbody tr:hover{
        background:#fcfbfa;
    }
    .ss-title{
        font-size:13.5px;
        font-weight:700;
        color:#111827;
        line-height:1.3;
        max-width:220px;
    }
    .ss-meta{
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:6px;
        margin-top:4px;
        font-size:11px;
        color:#6b7280;
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
        font-weight:700;
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
        font-size:13px;
        font-weight:600;
        color:#111827;
        white-space:nowrap;
    }
    .ss-when-time{
        font-size:12px;
        color:#6b7280;
        margin-top:2px;
    }
    .ss-client{
        font-size:13px;
        font-weight:600;
        color:#111827;
        line-height:1.3;
        max-width:180px;
    }
    .ss-sub{
        font-size:11px;
        color:#6b7280;
        margin-top:2px;
        max-width:180px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .ss-location{
        color:#8a1a1f;
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
        font-size:12px;
        line-height:1.35;
        min-width:0;
    }
    .ss-test-type{
        font-weight:700;
        color:#1f2937;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        max-width:90px;
        flex:0 1 auto;
    }
    .ss-test-sep{color:#c4c9d2;flex:0 0 auto;}
    .ss-test-analysis{
        color:#4b5563;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        min-width:0;
        flex:1 1 auto;
    }
    .ss-test-params{
        color:#6b7280;
        font-size:11px;
        flex:0 0 auto;
        margin-left:auto;
        padding-left:6px;
    }
    .ss-test-more{
        font-size:11px;
        color:#8a1a1f;
        font-weight:600;
    }
    .ss-personnel{
        font-size:12px;
        color:#374151;
        line-height:1.35;
        max-width:150px;
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
    .ss-forms-btn{
        min-width:34px;
        height:28px;
        padding:0 10px;
        border-radius:999px;
        border:1px solid #d7dde5;
        background:#fff;
        color:#6b7280;
        font-size:12px;
        font-weight:700;
        line-height:1;
    }
    .ss-forms-btn.has-forms{
        border-color:#8a1a1f;
        color:#8a1a1f;
        background:rgba(138,26,31,0.06);
    }
    .ss-forms-btn:hover{
        border-color:#8a1a1f;
        color:#8a1a1f;
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
    .ss-col-actions{width:140px;}
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
        min-height:42px;max-height:140px;overflow-y:auto;padding:4px 6px;
    }
    .schedule-sampling-page .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background:var(--color-primary-soft, #f3e8e9);
        border:1px solid var(--color-primary-highlight, #e2b8bb);
        color:var(--color-primary, #8a1a1f);
        border-radius:999px;
        padding:2px 8px;
        margin-top:4px;
        font-size:12px;
        font-weight:600;
    }
    .schedule-sampling-page .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
        color:var(--color-primary, #8a1a1f);
        margin-right:4px;
    }
    .schedule-sampling-page .select2-dropdown{
        z-index:3000;
        border-color:#ced4da;
    }
    .schedule-sampling-page .select2-results__option--highlighted[aria-selected]{
        background:var(--color-primary, #8a1a1f);
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
        --workflow-accent: var(--color-primary, #8a1a1f);
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
                    allowClear: true,
                    closeOnSelect: !this.multiple,
                    dropdownParent: modal ? $(modal) : $(document.body),
                });

                const initial = this.multiple
                    ? (Array.isArray(this.initial) ? this.initial.map(String) : [])
                    : (this.initial === null || this.initial === undefined ? '' : String(this.initial));

                this.syncing = true;
                $el.val(initial).trigger('change.select2');
                this.syncing = false;
                this.updateCount($el.val());

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

        Alpine.data('rftParamPickerUi', (config = {}) => ({
            open: false,
            openUp: false,
            search: '',
            rowIndex: config.rowIndex ?? 0,
            options: Array.isArray(config.options) ? config.options.slice() : [],
            selected: Array.isArray(config.selected) ? config.selected.slice() : [],
            hydrating: false,
            init() {
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
    </script>
    @endscript
</div>
