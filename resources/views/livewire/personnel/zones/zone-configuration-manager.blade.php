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
                    <button type="button" class="btn btn-outline-primary btn-sm zone-btn-round" wire:click="openDirectorateModal" @if(!$selectedZoneId) disabled @endif><i class="mdi mdi-plus"></i> {{ __('personnel.add') }}</button>
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
                    <h5 class="mb-0"><i class="mdi mdi-map"></i> Zones</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm zone-btn-round" wire:click="openZoneModal"><i class="mdi mdi-plus"></i> Add Zone</button>
                </div>
                <div class="card-body zone-section-body">
                    @if($selectedDirectorateId)
                        <input type="text" class="form-control mb-3" wire:model.live.debounce.300ms="zoneSearch" placeholder="Search zones...">
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
                                        <div>{{ __('personnel.no_zones_found') }}</div>
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
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi {{ $editingZoneId ? 'mdi-pencil' : 'mdi-plus' }}"></i>
                            {{ $editingZoneId ? 'Edit Zone' : 'Add Zone' }}
                        </h5>
                        <button type="button" class="close" wire:click="closeZoneModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Zone Key</label>
                            <input type="text" class="form-control @error('zoneForm.key') is-invalid @enderror" wire:model="zoneForm.key">
                            @error('zoneForm.key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label>Zone Name</label>
                            <input type="text" class="form-control @error('zoneForm.value') is-invalid @enderror" wire:model="zoneForm.value">
                            @error('zoneForm.value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('personnel.section_head') }}</label>
                            <select class="form-control @error('zoneForm.section_head_user_id') is-invalid @enderror" wire:model="zoneForm.section_head_user_id">
                                <option value="">{{ __('personnel.not_set') }}</option>
                                @foreach($this->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('zoneForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="zoneIsHqCheckbox" wire:model="zoneForm.is_hq_zone">
                                <label class="form-check-label" for="zoneIsHqCheckbox">Is HQ zone</label>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ __('personnel.description') }}</label>
                            <textarea class="form-control @error('zoneForm.description') is-invalid @enderror" wire:model="zoneForm.description"></textarea>
                            @error('zoneForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveZone"><i class="mdi mdi-content-save"></i> {{ $editingZoneId ? __('personnel.update') : __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeZoneModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDirectorateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi {{ $editingDirectorateId ? 'mdi-pencil' : 'mdi-plus' }}"></i>
                            {{ $editingDirectorateId ? __('personnel.edit_directorate') : __('personnel.add_directorate') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeDirectorateModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('personnel.name') }}</label>
                            <input type="text" class="form-control @error('directorateForm.name') is-invalid @enderror" wire:model="directorateForm.name">
                            @error('directorateForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('personnel.code') }}</label>
                            <input type="text" class="form-control @error('directorateForm.code') is-invalid @enderror" wire:model="directorateForm.code">
                            @error('directorateForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ __('personnel.section_head') }}</label>
                            <select class="form-control @error('directorateForm.section_head_user_id') is-invalid @enderror" wire:model="directorateForm.section_head_user_id">
                                <option value="">{{ __('personnel.not_set') }}</option>
                                @foreach($this->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('directorateForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveDirectorate"><i class="mdi mdi-content-save"></i> {{ $editingDirectorateId ? __('personnel.update') : __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDirectorateModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showLabModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi {{ $editingLabId ? 'mdi-pencil' : 'mdi-plus' }}"></i>
                            {{ $editingLabId ? __('personnel.edit_lab') : __('personnel.add_lab') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeLabModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.name') }}</label>
                                <input type="text" class="form-control @error('labForm.name') is-invalid @enderror" wire:model="labForm.name">
                                @error('labForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.code') }}</label>
                                <input type="text" class="form-control @error('labForm.code') is-invalid @enderror" wire:model="labForm.code">
                                @error('labForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.section_head') }}</label>
                                <select class="form-control @error('labForm.section_head_user_id') is-invalid @enderror" wire:model="labForm.section_head_user_id">
                                    <option value="">{{ __('personnel.not_set') }}</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('labForm.section_head_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.email') }}</label>
                                <input type="email" class="form-control @error('labForm.email') is-invalid @enderror" wire:model="labForm.email">
                                @error('labForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.phone') }}</label>
                                <input type="text" class="form-control @error('labForm.phone1') is-invalid @enderror" wire:model="labForm.phone1">
                                @error('labForm.phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.start_sample_no') }}</label>
                                <input type="text" class="form-control @error('labForm.start_sample_no') is-invalid @enderror" wire:model="labForm.start_sample_no">
                                @error('labForm.start_sample_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.address') }}</label>
                                <input type="text" class="form-control @error('labForm.address') is-invalid @enderror" wire:model="labForm.address">
                                @error('labForm.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('personnel.location') }}</label>
                                <input type="text" class="form-control @error('labForm.location') is-invalid @enderror" wire:model="labForm.location">
                                @error('labForm.location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @error('selectedDirectorateId') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveLab"><i class="mdi mdi-content-save"></i> {{ $editingLabId ? __('personnel.update') : __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeLabModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-alert"></i> {{ __('personnel.confirm_delete') }}</h5>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        {{ $this->deletePrompt }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteSelected"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.cancel') }}</button>
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
            border-color: #0d6efd;
            background: #eff6ff;
            box-shadow: inset 0 0 0 1px #0d6efd;
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
    </style>
</div>
