<div class="container-fluid">
    @if($showToast && $message)
        <div class="zone-inline-toast alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h2 class="mb-1"><i class="mdi mdi-map-marker-radius text-primary"></i> Zone Configuration</h2>
                    <p class="text-muted mb-0">Manage directorates, zones, and labs in one place. Select a directorate first, then review its linked zone and labs.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card h-100 shadow-sm border-0 zone-section-card zone-section-directorates" style="border-radius: 14px;">
                <div class="card-header border-0 d-flex justify-content-between align-items-center" style="border-radius: 14px 14px 0 0;">
                    <h5 class="mb-0"><i class="mdi mdi-office-building"></i> {{ __('personnel.directorates') }}</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm zone-btn-round" wire:click="openDirectorateModal"><i class="mdi mdi-plus"></i> {{ __('personnel.add') }}</button>
                </div>
                <div class="card-body zone-section-body">
                    <input type="text" class="form-control mb-3" wire:model.live.debounce.300ms="directorateSearch" placeholder="{{ __('personnel.search_directorates') }}">
                    <div class="zone-column-scroll">
                        @forelse($this->directorates as $directorate)
                            <div class="zone-card p-3 mb-2 {{ $selectedDirectorateId === (string) $directorate->id ? 'active' : '' }}" wire:click="selectDirectorate('{{ $directorate->id }}')">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="pr-2">
                                        <div class="font-weight-bold">{{ $directorate->name }}</div>
                                        <small class="text-muted d-block">{{ __('personnel.head') }}: {{ $directorate->sectionHeadUser?->name ?? $directorate->head?->name ?? __('personnel.not_set') }}</small>
                                        <small class="text-muted d-block">Zone: {{ $directorate->zone?->key }}{{ $directorate->zone?->value ? ' - '.$directorate->zone?->value : '' }}</small>
                                        <small class="text-muted d-block">{{ __('personnel.labs') }}: {{ $directorate->labs_count }}</small>
                                    </div>
                                    <div class="zone-card-actions">
                                        <button type="button" class="btn btn-xs btn-outline-primary" wire:click.stop="editDirectorate('{{ $directorate->id }}')"><i class="mdi mdi-pencil"></i></button>
                                        <button type="button" class="btn btn-xs btn-outline-danger" wire:click.stop="confirmDeleteDirectorate('{{ $directorate->id }}')" @if($directorate->labs_count > 0) disabled title="{{ __('personnel.remove_labs_first') }}" @endif><i class="mdi mdi-delete"></i></button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="zone-empty-state text-muted">
                                <div class="zone-empty-state-inner">
                                    <div class="zone-empty-icon"><i class="mdi mdi-office-building-marker-outline"></i></div>
                                    <div>{{ __('personnel.no_directorates_for_selected_zone') }}</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card h-100 shadow-sm border-0 zone-section-card zone-section-zones" style="border-radius: 14px;">
                <div class="card-header border-0 d-flex justify-content-between align-items-center" style="border-radius: 14px 14px 0 0;">
                    <h5 class="mb-0"><i class="mdi mdi-map"></i> {{ __('personnel.zones') }}</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm zone-btn-round" wire:click="openZoneModal" @if(!$selectedDirectorateId) disabled title="{{ __('personnel.select_directorate_before_zone') }}" @endif><i class="mdi mdi-plus"></i> {{ __('personnel.add_zone') }}</button>
                </div>
                <div class="card-body zone-section-body">
                    @if($selectedDirectorateId)
                        <input type="text" class="form-control mb-3" wire:model.live.debounce.300ms="zoneSearch" placeholder="{{ __('personnel.search_zones') }}">
                        <div class="zone-column-scroll">
                            @forelse($this->zones as $zone)
                                <div class="zone-card p-3 mb-2 {{ $selectedZoneId === (string) $zone->id ? 'active' : '' }}" wire:click="selectZone('{{ $zone->id }}')">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold">{{ $zone->key }}{{ $zone->value ? ' - ' . $zone->value : '' }}</div>
                                            <small class="d-block mt-1">
                                                <span class="badge badge-pill {{ $zone->is_hq_zone ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $zone->is_hq_zone ? 'HQ Zone' : 'Non-HQ Zone' }}
                                                </span>
                                            </small>
                                            <small class="text-muted d-block">{{ __('personnel.head') }}: {{ $zone->sectionHeadUser?->name ?? __('personnel.not_set') }}</small>
                                            <small class="text-muted d-block">{{ __('personnel.directorates') }}: {{ $zone->directorates_count }}</small>
                                        </div>
                                        <div class="zone-card-actions">
                                            <button type="button" class="btn btn-xs btn-outline-primary" wire:click.stop="editZone('{{ $zone->id }}')"><i class="mdi mdi-pencil"></i></button>
                                            <button type="button" class="btn btn-xs btn-outline-danger" wire:click.stop="confirmDeleteZone('{{ $zone->id }}')" @if($zone->directorates_count > 0) disabled title="{{ __('personnel.remove_directorates_first') }}" @endif><i class="mdi mdi-delete"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="zone-empty-state text-muted">
                                    <div class="zone-empty-state-inner">
                                        <div class="zone-empty-icon"><i class="mdi mdi-map-search-outline"></i></div>
                                        <div>{{ __('personnel.no_linked_zone_for_directorate') }}</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="zone-empty-state text-muted">
                            <div class="zone-empty-state-inner">
                                <div class="zone-empty-icon"><i class="mdi mdi-cursor-default-click-outline"></i></div>
                                <div>Select a directorate to view its linked zone.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card h-100 shadow-sm border-0 zone-section-card zone-section-labs" style="border-radius: 14px;">
                <div class="card-header border-0 d-flex justify-content-between align-items-center" style="border-radius: 14px 14px 0 0;">
                    <h5 class="mb-0"><i class="mdi mdi-flask-outline"></i> {{ __('personnel.labs') }}</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm zone-btn-round" wire:click="openLabModal" @if(!$selectedDirectorateId) disabled @endif><i class="mdi mdi-plus"></i> {{ __('personnel.add') }}</button>
                </div>
                <div class="card-body zone-section-body">
                    @if($selectedDirectorateId)
                        <input type="text" class="form-control mb-3" wire:model.live.debounce.300ms="labSearch" placeholder="{{ __('personnel.search_labs') }}">
                        <div class="zone-column-scroll">
                            @forelse($this->labs as $lab)
                                <div class="zone-card p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold">{{ $lab->name }}</div>
                                            <small class="text-muted d-block">{{ __('personnel.head') }}: {{ $lab->sectionHeadUser?->name ?? $lab->manager?->name ?? __('personnel.not_set') }}</small>
                                        </div>
                                        <div class="zone-card-actions">
                                            <button type="button" class="btn btn-xs btn-outline-primary" wire:click.stop="editLab('{{ $lab->id }}')"><i class="mdi mdi-pencil"></i></button>
                                            <button type="button" class="btn btn-xs btn-outline-danger" wire:click.stop="confirmDeleteLab('{{ $lab->id }}')"><i class="mdi mdi-delete"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="zone-empty-state text-muted">
                                    <div class="zone-empty-state-inner">
                                        <div class="zone-empty-icon"><i class="mdi mdi-flask-empty-outline"></i></div>
                                        <div>{{ __('personnel.no_labs_for_current_selection') }}</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="zone-empty-state text-muted">
                            <div class="zone-empty-state-inner">
                                <div class="zone-empty-icon"><i class="mdi mdi-cursor-default-click-outline"></i></div>
                                <div>Select a directorate to view labs.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showZoneModal)
        <div class="modal fade show zone-cfg-modal-overlay" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog zm-dialog">
                <div class="modal-content zm-modal-shell border-0 shadow-lg">
                    <div class="modal-header zm-modal-header border-0 align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="zm-modal-icon mr-2">
                                <i class="mdi {{ $editingZoneId ? 'mdi-pencil' : 'mdi-map-marker-radius' }}"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 font-weight-bold">{{ $editingZoneId ? __('personnel.edit_zone') : __('personnel.add_zone') }}</h5>
                                @if(!$editingZoneId)
                                    <p class="zm-modal-subtitle mb-0">{{ __('personnel.zone_add_modal_hint') }}</p>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="close zm-modal-close" wire:click="closeZoneModal" aria-label="{{ __('personnel.close') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body zm-modal-body">
                        @if(!$editingZoneId)
                            <div class="zm-panel zm-panel--link mb-4">
                                <div class="zm-panel-title">
                                    <i class="mdi mdi-link-variant text-primary"></i>
                                    {{ __('personnel.link_existing_zone') }}
                                </div>
                                <p class="zm-panel-hint">{{ __('personnel.select_existing_zone') }}</p>
                                <div class="zm-field mb-3">
                                    <select class="form-control zm-control zm-select @error('linkExistingZoneId') is-invalid @enderror" wire:model.live="linkExistingZoneId">
                                        <option value="">{{ __('personnel.select_existing_zone') }}</option>
                                        @foreach($this->zonesAvailableForLink as $z)
                                            <option value="{{ $z->id }}">{{ $z->key }}{{ $z->value ? ' - ' . $z->value : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('linkExistingZoneId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <button type="button" class="btn btn-primary zm-btn-primary btn-block" wire:click="linkExistingZoneToDirectorate" @if($linkExistingZoneId === '') disabled @endif wire:loading.attr="disabled" wire:target="linkExistingZoneToDirectorate">
                                    <span wire:loading.remove wire:target="linkExistingZoneToDirectorate"><i class="mdi mdi-link mr-1"></i>{{ __('personnel.link_zone_to_directorate') }}</span>
                                    <span wire:loading wire:target="linkExistingZoneToDirectorate"><i class="mdi mdi-loading mdi-spin"></i> {{ __('personnel.submit') }}</span>
                                </button>
                            </div>
                            <div class="zm-divider mb-4">
                                <span>{{ __('personnel.or_create_new_zone') }}</span>
                            </div>
                        @endif
                        <div class="zm-form-grid">
                            <div class="zm-field">
                                <label class="zm-label" for="zoneFormKey">{{ __('personnel.zone_key') }}</label>
                                <input id="zoneFormKey" type="text" class="form-control zm-control @error('zoneForm.key') is-invalid @enderror" wire:model="zoneForm.key" placeholder="{{ __('personnel.zone_key') }}">
                                @error('zoneForm.key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="zoneFormValue">{{ __('personnel.zone_value') }}</label>
                                <input id="zoneFormValue" type="text" class="form-control zm-control @error('zoneForm.value') is-invalid @enderror" wire:model="zoneForm.value" placeholder="{{ __('personnel.name') }}">
                                @error('zoneForm.value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field zm-field--full">
                                <label class="zm-label" for="zoneSectionHead">{{ __('personnel.section_head') }}</label>
                                <select id="zoneSectionHead" class="form-control zm-control zm-select @error('zoneForm.section_head_user_id') is-invalid @enderror" wire:model="zoneForm.section_head_user_id">
                                    <option value="">{{ __('personnel.not_set') }}</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('zoneForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field zm-field--full">
                                <span class="zm-label d-block">{{ __('personnel.is_hq_zone') }}</span>
                                <label class="zm-hq-pill" for="zoneIsHqCheckbox">
                                    <input type="checkbox" class="zm-hq-input" id="zoneIsHqCheckbox" wire:model.live="zoneForm.is_hq_zone">
                                    <span class="zm-hq-text-off">{{ __('personnel.no') }}</span>
                                    <span class="zm-hq-track" aria-hidden="true"><span class="zm-hq-knob"></span></span>
                                    <span class="zm-hq-text-on">{{ __('personnel.yes') }}</span>
                                </label>
                            </div>
                            <div class="zm-field zm-field--full mb-0">
                                <label class="zm-label" for="zoneDescription">{{ __('personnel.description') }}</label>
                                <textarea id="zoneDescription" rows="3" class="form-control zm-control zm-textarea @error('zoneForm.description') is-invalid @enderror" wire:model="zoneForm.description" placeholder="{{ __('personnel.description') }}"></textarea>
                                @error('zoneForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer zm-modal-footer border-0">
                        <button type="button" class="btn btn-light zm-btn-ghost" wire:click="closeZoneModal">{{ __('personnel.cancel') }}</button>
                        <button type="button" class="btn btn-primary zm-btn-primary px-4" wire:click="saveZone" wire:loading.attr="disabled" wire:target="saveZone">
                            <span wire:loading.remove wire:target="saveZone"><i class="mdi mdi-content-save mr-1"></i>{{ $editingZoneId ? __('personnel.update') : __('personnel.save') }}</span>
                            <span wire:loading wire:target="saveZone"><i class="mdi mdi-loading mdi-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDirectorateModal)
        <div class="modal fade show zone-cfg-modal-overlay" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog zm-dialog">
                <div class="modal-content zm-modal-shell border-0 shadow-lg">
                    <div class="modal-header zm-modal-header border-0 align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="zm-modal-icon mr-2">
                                <i class="mdi {{ $editingDirectorateId ? 'mdi-pencil' : 'mdi-office-building' }}"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 font-weight-bold">{{ $editingDirectorateId ? __('personnel.edit_directorate') : __('personnel.add_directorate') }}</h5>
                            </div>
                        </div>
                        <button type="button" class="close zm-modal-close" wire:click="closeDirectorateModal" aria-label="{{ __('personnel.close') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body zm-modal-body">
                        <div class="zm-form-grid">
                            <div class="zm-field">
                                <label class="zm-label" for="directorateFormName">{{ __('personnel.name') }}</label>
                                <input id="directorateFormName" type="text" class="form-control zm-control @error('directorateForm.name') is-invalid @enderror" wire:model="directorateForm.name" placeholder="{{ __('personnel.name') }}">
                                @error('directorateForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="directorateFormCode">{{ __('personnel.code') }}</label>
                                <input id="directorateFormCode" type="text" class="form-control zm-control @error('directorateForm.code') is-invalid @enderror" wire:model="directorateForm.code" placeholder="{{ __('personnel.code') }}">
                                @error('directorateForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field zm-field--full mb-0">
                                <label class="zm-label" for="directorateSectionHead">{{ __('personnel.section_head') }}</label>
                                <select id="directorateSectionHead" class="form-control zm-control zm-select @error('directorateForm.section_head_user_id') is-invalid @enderror" wire:model="directorateForm.section_head_user_id">
                                    <option value="">{{ __('personnel.not_set') }}</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('directorateForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer zm-modal-footer border-0">
                        <button type="button" class="btn btn-light zm-btn-ghost" wire:click="closeDirectorateModal">{{ __('personnel.cancel') }}</button>
                        <button type="button" class="btn btn-primary zm-btn-primary px-4" wire:click="saveDirectorate" wire:loading.attr="disabled" wire:target="saveDirectorate">
                            <span wire:loading.remove wire:target="saveDirectorate"><i class="mdi mdi-content-save mr-1"></i>{{ $editingDirectorateId ? __('personnel.update') : __('personnel.save') }}</span>
                            <span wire:loading wire:target="saveDirectorate"><i class="mdi mdi-loading mdi-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showLabModal)
        <div class="modal fade show zone-cfg-modal-overlay" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg zm-dialog--wide">
                <div class="modal-content zm-modal-shell border-0 shadow-lg">
                    <div class="modal-header zm-modal-header zm-modal-header--lab border-0 align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="zm-modal-icon zm-modal-icon--lab mr-2">
                                <i class="mdi {{ $editingLabId ? 'mdi-pencil' : 'mdi-flask-outline' }}"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 font-weight-bold">{{ $editingLabId ? __('personnel.edit_lab') : __('personnel.add_lab') }}</h5>
                            </div>
                        </div>
                        <button type="button" class="close zm-modal-close" wire:click="closeLabModal" aria-label="{{ __('personnel.close') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body zm-modal-body">
                        <div class="zm-form-grid">
                            <div class="zm-field">
                                <label class="zm-label" for="labFormName">{{ __('personnel.name') }}</label>
                                <input id="labFormName" type="text" class="form-control zm-control @error('labForm.name') is-invalid @enderror" wire:model="labForm.name" placeholder="{{ __('personnel.name') }}">
                                @error('labForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormCode">{{ __('personnel.code') }}</label>
                                <input id="labFormCode" type="text" class="form-control zm-control @error('labForm.code') is-invalid @enderror" wire:model="labForm.code" placeholder="{{ __('personnel.code') }}">
                                @error('labForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labSectionHead">{{ __('personnel.section_head') }}</label>
                                <select id="labSectionHead" class="form-control zm-control zm-select @error('labForm.section_head_user_id') is-invalid @enderror" wire:model="labForm.section_head_user_id">
                                    <option value="">{{ __('personnel.not_set') }}</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('labForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormEmail">{{ __('personnel.email') }}</label>
                                <input id="labFormEmail" type="email" class="form-control zm-control @error('labForm.email') is-invalid @enderror" wire:model="labForm.email" placeholder="{{ __('personnel.email') }}">
                                @error('labForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormPhone">{{ __('personnel.phone') }}</label>
                                <input id="labFormPhone" type="text" class="form-control zm-control @error('labForm.phone1') is-invalid @enderror" wire:model="labForm.phone1" placeholder="{{ __('personnel.phone') }}">
                                @error('labForm.phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormStartSample">{{ __('personnel.start_sample_no') }}</label>
                                <input id="labFormStartSample" type="text" class="form-control zm-control @error('labForm.start_sample_no') is-invalid @enderror" wire:model="labForm.start_sample_no" placeholder="{{ __('personnel.start_sample_no') }}">
                                @error('labForm.start_sample_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormAddress">{{ __('personnel.address') }}</label>
                                <input id="labFormAddress" type="text" class="form-control zm-control @error('labForm.address') is-invalid @enderror" wire:model="labForm.address" placeholder="{{ __('personnel.address') }}">
                                @error('labForm.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field">
                                <label class="zm-label" for="labFormLocation">{{ __('personnel.location') }}</label>
                                <input id="labFormLocation" type="text" class="form-control zm-control @error('labForm.location') is-invalid @enderror" wire:model="labForm.location" placeholder="{{ __('personnel.location') }}">
                                @error('labForm.location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="zm-field zm-field--full mb-0">
                                @error('selectedDirectorateId') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer zm-modal-footer border-0">
                        <button type="button" class="btn btn-light zm-btn-ghost" wire:click="closeLabModal">{{ __('personnel.cancel') }}</button>
                        <button type="button" class="btn btn-primary zm-btn-primary px-4" wire:click="saveLab" wire:loading.attr="disabled" wire:target="saveLab">
                            <span wire:loading.remove wire:target="saveLab"><i class="mdi mdi-content-save mr-1"></i>{{ $editingLabId ? __('personnel.update') : __('personnel.save') }}</span>
                            <span wire:loading wire:target="saveLab"><i class="mdi mdi-loading mdi-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show zone-cfg-modal-overlay" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog zm-dialog">
                <div class="modal-content zm-modal-shell border-0 shadow-lg">
                    <div class="modal-header zm-modal-header zm-modal-header--danger border-0 align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="zm-modal-icon zm-modal-icon--danger mr-2">
                                <i class="mdi mdi-alert-circle-outline"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 font-weight-bold">{{ __('personnel.confirm_delete') }}</h5>
                            </div>
                        </div>
                        <button type="button" class="close zm-modal-close" wire:click="closeDeleteModal" aria-label="{{ __('personnel.close') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body zm-modal-body">
                        <p class="mb-0 text-secondary">{{ $this->deletePrompt }}</p>
                    </div>
                    <div class="modal-footer zm-modal-footer border-0">
                        <button type="button" class="btn btn-light zm-btn-ghost" wire:click="closeDeleteModal">{{ __('personnel.cancel') }}</button>
                        <button type="button" class="btn btn-danger zm-btn-danger px-4" wire:click="deleteSelected" wire:loading.attr="disabled" wire:target="deleteSelected">
                            <span wire:loading.remove wire:target="deleteSelected"><i class="mdi mdi-delete mr-1"></i>{{ __('personnel.delete') }}</span>
                            <span wire:loading wire:target="deleteSelected"><i class="mdi mdi-loading mdi-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .zone-inline-toast {
            position: sticky;
            top: 12px;
            z-index: 1065;
            margin-bottom: 12px;
        }

        .zone-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .zone-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12);
        }

        .zone-card.active {
            border: 1px solid #e5e7eb;
            border-left: 4px solid #6ee7b7;
            background: linear-gradient(90deg, rgba(236, 253, 245, 0.85) 0%, #f0fdf4 18px, #ffffff 100%);
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
        }

        .zone-column-scroll {
            min-height: 40vh;
            max-height: 50vh;
            overflow-y: auto;
            padding-right: 4px;
        }

        .zone-section-card {
            overflow: hidden;
            border: 1px solid #dbe4ee !important;
        }

        .zone-section-zones {
            background: linear-gradient(160deg, #f8fbff 0%, #f0f7ff 100%);
        }

        .zone-section-directorates {
            background: linear-gradient(160deg, #fcfdf8 0%, #f3f9ec 100%);
        }

        .zone-section-labs {
            background: linear-gradient(160deg, #fffaf5 0%, #fff2e6 100%);
        }

        .zone-section-card .card-header {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(2px);
            border-bottom: 1px solid #e5e7eb;
        }

        .zone-section-body {
            min-height: 50vh;
            display: flex;
            flex-direction: column;
        }

        .zone-empty-state {
            min-height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px dashed #d8dee6;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.6);
            padding: 16px;
        }

        .zone-empty-state-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .zone-empty-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #5f6d7b;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #dbe4ee;
            font-size: 20px;
        }

        .zone-card-actions {
            display: inline-flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .zone-card-actions .btn {
            padding: 2px 6px;
            line-height: 1.2;
            border-radius: 999px;
        }

        .zone-btn-round {
            border-radius: 999px;
            padding-left: 12px;
            padding-right: 12px;
        }

        /* —— Modal overlay: centered; scroll inside overlay (not body). Use flex !important so Bootstrap .d-block / .modal do not force display:block. —— */
        .modal.zone-cfg-modal-overlay.fade.show {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
            padding: 1.5rem 0.75rem 2.5rem;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(4px);
            z-index: 1055 !important;
        }

        .modal.zone-cfg-modal-overlay.fade.show .modal-dialog {
            margin: 0.75rem !important;
            flex-shrink: 0;
            align-self: center;
            width: auto;
            max-width: calc(100vw - 1.5rem);
        }

        /* —— Zone add/edit modal (modern) —— */
        .zm-dialog {
            max-width: 460px;
        }

        .zm-dialog--wide.modal-lg {
            max-width: min(880px, calc(100vw - 1.5rem));
        }

        .zm-modal-shell {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.95);
        }

        .zm-modal-header {
            padding: 1.15rem 1.35rem;
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 55%, #e0f2fe 100%);
        }

        .zm-modal-header--lab {
            background: linear-gradient(135deg, #fffaf5 0%, #fff7ed 55%, #ffedd5 100%);
        }

        .zm-modal-header--danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 55%, #fecaca 100%);
        }

        .zm-modal-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #2563eb;
            font-size: 1.35rem;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12);
            border: 1px solid #e2e8f0;
        }

        .zm-modal-icon--lab {
            color: #c2410c;
            box-shadow: 0 2px 8px rgba(194, 65, 12, 0.12);
            border-color: #fed7aa;
        }

        .zm-modal-icon--danger {
            color: #b91c1c;
            box-shadow: 0 2px 8px rgba(185, 28, 28, 0.12);
            border-color: #fecaca;
        }

        .zm-modal-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.2rem;
            line-height: 1.35;
            max-width: 20rem;
        }

        .zm-modal-close {
            opacity: 0.55;
            text-shadow: none;
            font-size: 1.5rem;
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }

        .zm-modal-close:hover {
            opacity: 1;
        }

        .zm-modal-body {
            padding: 1.35rem 1.35rem 1.15rem;
            background: linear-gradient(180deg, #fafbfc 0%, #f4f6f8 100%);
        }

        .zm-modal-footer {
            padding: 0.85rem 1.35rem 1.2rem;
            background: #fff;
            gap: 0.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
        }

        .zm-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.1rem 1.15rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }

        .zm-panel--link {
            border-color: #bfdbfe;
            background: linear-gradient(160deg, #ffffff 0%, #f8fbff 100%);
        }

        .zm-panel-title {
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.35rem;
        }

        .zm-panel-hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .zm-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .zm-divider::before,
        .zm-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }

        .zm-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 1rem;
        }

        .zm-field--full {
            grid-column: 1 / -1;
        }

        @media (max-width: 480px) {
            .zm-form-grid {
                grid-template-columns: 1fr;
            }
        }

        .zm-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.35rem;
        }

        .zm-control,
        .zm-select {
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            font-size: 0.9rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .zm-control:focus,
        .zm-select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
        }

        .zm-textarea {
            resize: vertical;
            min-height: 5rem;
        }

        .zm-btn-primary {
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.22);
        }

        .zm-btn-primary:disabled {
            opacity: 0.55;
            box-shadow: none;
        }

        .zm-btn-ghost {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 500;
        }

        .zm-btn-ghost:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .zm-btn-danger {
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(185, 28, 28, 0.2);
        }

        .zm-btn-danger:disabled {
            opacity: 0.55;
            box-shadow: none;
        }

        .zm-hq-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            cursor: pointer;
            user-select: none;
            margin: 0;
            position: relative;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .zm-hq-pill:hover {
            border-color: #cbd5e1;
            background: #e8eef5;
        }

        .zm-hq-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
        }

        .zm-hq-pill:focus-within {
            outline: 2px solid rgba(59, 130, 246, 0.35);
            outline-offset: 2px;
        }

        .zm-hq-track {
            width: 2.5rem;
            height: 1.35rem;
            border-radius: 999px;
            background: #cbd5e1;
            position: relative;
            flex-shrink: 0;
            transition: background 0.2s ease;
        }

        .zm-hq-knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: calc(1.35rem - 6px);
            height: calc(1.35rem - 6px);
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.18);
            transition: transform 0.2s ease;
        }

        .zm-hq-input:checked ~ .zm-hq-track {
            background: #2563eb;
        }

        .zm-hq-input:checked ~ .zm-hq-track .zm-hq-knob {
            transform: translateX(1.15rem);
        }

        .zm-hq-text-off,
        .zm-hq-text-on {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            transition: color 0.15s ease, opacity 0.15s ease;
        }

        .zm-hq-input:checked ~ .zm-hq-text-off {
            color: #94a3b8;
            opacity: 0.5;
        }

        .zm-hq-input:checked ~ .zm-hq-text-on {
            color: #1d4ed8;
            opacity: 1;
        }

        .zm-hq-input:not(:checked) ~ .zm-hq-text-on {
            opacity: 0.45;
        }

        .zm-hq-input:not(:checked) ~ .zm-hq-text-off {
            color: #334155;
            opacity: 1;
        }
    </style>
</div>
