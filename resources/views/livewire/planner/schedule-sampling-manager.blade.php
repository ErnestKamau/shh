<div class="container-fluid schedule-sampling-page">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 font-weight-bold"><i class="mdi mdi-clock-outline text-primary"></i> {{ __('planner.sampling_schedules') }}</h3>
                            <p class="text-muted mb-0">{{ __('planner.sampling_schedules_subtitle') }}</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary" style="border-radius:30px;padding:0.6rem 1.8rem;font-weight:600;">
                            <i class="mdi mdi-plus-circle mr-1"></i> {{ __('planner.schedule_sampling') }}
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
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('planner.search_placeholder') }}" style="border-radius:10px;">
        </div>
        <div class="col-md-6 text-right">
            <button wire:click="toggleFilters" class="btn btn-outline-secondary mr-2" style="border-radius:10px;">
                <i class="mdi mdi-filter-variant mr-1"></i> {{ $showFilters ? __('planner.hide_filters') : __('planner.show_filters') }}
            </button>
            <button wire:click="exportToExcel" class="btn btn-success mr-2" style="border-radius:10px;">
                <i class="mdi mdi-file-excel mr-1"></i> {{ __('planner.export_excel') }}
            </button>
            <button wire:click="exportToPdf" class="btn btn-danger" style="border-radius:10px;">
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
    <div class="card shadow-sm border-0" style="border-radius:15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:rgba(0,0,0,.03);">
                        <tr>
                            <th class="border-0">Title</th>
                            <th class="border-0">Client</th>
                            <th class="border-0">Date & Time</th>
                            <th class="border-0">Location</th>
                            <th class="border-0">Sample Details</th>
                            <th class="border-0">Samples</th>
                            <th class="border-0">Frequency</th>
                            <th class="border-0">Personnel</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->schedules as $s)
                        <tr>
                            <td class="font-weight-bold">
                                {{ $s->title }}
                                @if($s->is_collected)
                                    <span class="badge badge-success ml-1"><i class="mdi mdi-check-circle-outline mr-1"></i>Collected</span>
                                @endif
                            </td>
                            <td>
                                {{ $s->client->name ?? 'N/A' }}
                                @if($s->contact)
                                <br><small class="text-muted">{{ trim(($s->contact->first_name ?? '').' '.($s->contact->last_name ?? '')) }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-light p-2 text-dark"><i class="mdi mdi-clock-outline text-primary mr-1"></i>{{ $s->sampling_datetime ? $s->sampling_datetime->format('Y-m-d H:i') : 'N/A' }}</span></td>
                            <td><i class="mdi mdi-map-marker text-danger mr-1"></i>{{ $s->location }}</td>
                            <td>
                                @php $details = $this->resolveSampleDetails($s); @endphp
                                @foreach($details as $d)
                                    <span class="badge badge-info p-1 mb-1 d-inline-block">{{ $d['type'] }}</span>
                                    @if($d['analysis'])<span class="badge badge-secondary p-1 mb-1 d-inline-block">{{ $d['analysis'] }}</span>@endif
                                    @if($d['params_count'] > 0)<small class="text-muted d-block" style="font-size:11px;">{{ $d['params_count'] }} params</small>@endif
                                @endforeach
                                @if(empty($details))<span class="text-muted">—</span>@endif
                            </td>
                            <td class="text-center font-weight-bold">{{ $s->number_of_samples }}</td>
                            <td><span class="badge badge-outline-primary">{{ $s->frequency }}</span></td>
                            <td><span class="badge badge-dark p-2"><i class="mdi mdi-account-tie mr-1"></i>{{ $s->personnel->name ?? 'N/A' }}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <button wire:click="openScheduleFormModal('{{ $s->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--form" title="Fill Request Form"><i class="mdi mdi-file-document-edit"></i></button>
                                    <button wire:click="viewSchedule('{{ $s->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--view" title="View"><i class="mdi mdi-eye"></i></button>
                                    <button wire:click="showEditModal('{{ $s->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                    <button wire:click="delete('{{ $s->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--delete" title="Delete" onclick="return confirm('Are you sure you want to delete this schedule?')"><i class="mdi mdi-delete"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="mdi mdi-calendar-blank fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">No sampling schedules created yet.</p>
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
                        <select wire:model.live="form.crm_customer_id" class="form-control no-select2">
                            <option value="">Select Customer</option>
                            @foreach($clients as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
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

                    @if(count($customerContacts) > 0)
                    <div class="form-group">
                        <label class="font-weight-bold">Customer Contact Personnel</label>
                        <select wire:model.live="form.contact_id" class="form-control no-select2">
                            <option value="">Select Contact</option>
                            @foreach($customerContacts as $cc)
                            <option value="{{ $cc['id'] }}">{{ $cc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($selectedContactEmail || $selectedContactPhone)
                    <div class="row">
                        <div class="col-md-6"><div class="alert alert-light p-2 mb-2"><small class="text-muted d-block">Email</small><strong>{{ $selectedContactEmail }}</strong></div></div>
                        <div class="col-md-6"><div class="alert alert-light p-2 mb-2"><small class="text-muted d-block">Phone</small><strong>{{ $selectedContactPhone }}</strong></div></div>
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
                            <label class="font-weight-bold">Location <span class="text-danger">*</span></label>
                            <input type="text" wire:model="form.location" class="form-control" placeholder="Enter location/site">
                            @error('form.location') <span class="text-danger small">{{ $message }}</span> @enderror
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
                            <div class="form-group">
                                <label class="font-weight-bold">Parameters / Analytes
                                    <small class="text-muted font-weight-normal ml-1">({{ count($entry['parameters'] ?? []) }} selected)</small>
                                </label>
                                <div class="param-checkbox-list border rounded p-2" style="max-height:180px;overflow-y:auto;background:#fafbfc;">
                                    @foreach($entry['availableParameters'] as $p)
                                    <div class="form-check py-1 px-2 param-check-item" style="border-bottom:1px solid #f0f0f0;">
                                        <input class="form-check-input" type="checkbox"
                                            wire:model="sampleEntries.{{ $idx }}.parameters"
                                            value="{{ $p['id'] }}"
                                            id="param-{{ $idx }}-{{ $p['id'] }}">
                                        <label class="form-check-label w-100 cursor-pointer" for="param-{{ $idx }}-{{ $p['id'] }}" style="cursor:pointer;">
                                            {{ $p['name'] }}
                                        </label>
                                    </div>
                                    @endforeach
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
                            <input type="number" wire:model="form.number_of_samples" class="form-control" min="1" placeholder="Defaults to 1">
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
                        <select wire:model="form.personnel_id" class="form-control no-select2">
                            <option value="">Select Personnel</option>
                            @foreach($users as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.personnel_id') <span class="text-danger small">{{ $message }}</span> @enderror
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
                    <div class="px-4 pt-4 pb-3" style="background:#f8f9fa;border-bottom:1px solid #e9ecef;">
                        <h4 class="mb-1 font-weight-bold"><i class="mdi mdi-calendar-check text-primary mr-1"></i> {{ $viewingSchedule->title }}</h4>
                        <span class="badge badge-pill" style="background:#e8f5e9;color:#2e7d32;padding:6px 14px;font-size:12px;"><i class="mdi mdi-clock-outline mr-1"></i>{{ $viewingSchedule->sampling_datetime ? $viewingSchedule->sampling_datetime->format('D, d M Y \a\t H:i') : 'N/A' }}</span>
                        <span class="badge badge-pill ml-1" style="background:var(--color-primary-soft);color:var(--color-primary);padding:6px 14px;font-size:12px;"><i class="mdi mdi-refresh mr-1"></i>{{ $viewingSchedule->frequency }}</span>
                        @if($viewingSchedule->notify_client)
                        <span class="badge badge-pill ml-1" style="background:#fff3e0;color:#e65100;padding:6px 14px;font-size:12px;"><i class="mdi mdi-bell-ring mr-1"></i>Client Notified</span>
                        @endif
                        @if($viewingSchedule->is_collected)
                        <span class="badge badge-pill ml-1" style="background:#e8f5e9;color:#2e7d32;padding:6px 14px;font-size:12px;"><i class="mdi mdi-check-circle-outline mr-1"></i>Collected</span>
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
                                            <small class="text-muted text-uppercase font-weight-bold" style="letter-spacing:0.5px;">Contact</small>
                                        </div>
                                        <h6 class="font-weight-bold mb-0">{{ $viewingSchedule->contact ? trim(($viewingSchedule->contact->first_name ?? '').' '.($viewingSchedule->contact->last_name ?? '')) : 'N/A' }}</h6>
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
                                        <strong>{{ $viewingSchedule->location ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3 text-center">
                                        <i class="mdi mdi-flask text-info" style="font-size:24px;"></i>
                                        <small class="text-muted d-block mt-1">No. of Samples</small>
                                        <strong style="font-size:20px;">{{ $viewingSchedule->number_of_samples }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                                    <div class="card-body p-3 text-center">
                                        <i class="mdi mdi-account-hard-hat text-warning" style="font-size:24px;"></i>
                                        <small class="text-muted d-block mt-1">Personnel</small>
                                        <strong>{{ $viewingSchedule->personnel->name ?? 'N/A' }}</strong>
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
                        @if($viewingSchedule->submissionFormInstances->count() > 0)
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase text-muted mb-2" style="font-size:12px;letter-spacing:1px;"><i class="mdi mdi-file-document-outline mr-1"></i>Tied Request Forms</h6>
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
                                        @foreach($viewingSchedule->submissionFormInstances as $instance)
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
                        </div>
                        @endif

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

    <!-- ═══ FORM MODAL ═══ -->
    @if($showFormModal && $selectedScheduleId)
    <div id="schedule-sampling-form-modal" class="modal fade show d-block schedule-sampling-form-modal" tabindex="-1" style="background:rgba(0,0,0,0.5);z-index:1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:12px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--color-primary),#8a1a1f);flex-shrink:0;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="mdi mdi-file-document-edit mr-2"></i> Fill Test Request Form</h5>
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
                        onclick="if (typeof window.syncScheduleTrfBeforeSubmit === 'function') { window.syncScheduleTrfBeforeSubmit(); }"
                        wire:click="saveScheduleForm"
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
    .rm-act-btn--view{border:1px solid #c3e6cb;color:#155724;background:#d4edda;}
    .rm-act-btn--view:hover{background:#c3e6cb;border-color:#a3d5b5;}
    .rm-act-btn--edit{border:1px solid #bfdbfe;color:#1d4ed8;background:#eff6ff;}
    .rm-act-btn--edit:hover{background:#dbeafe;border-color:#93c5fd;}
    .rm-act-btn--delete{border:1px solid #fecaca;color:#b91c1c;background:#fef2f2;}
    .rm-act-btn--delete:hover{background:#fee2e2;border-color:#fca5a5;}
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @script
    <script>
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
                            // Soft init only — never wipe an in-progress signature mid-draw.
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
