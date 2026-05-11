<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-map-marker-radius"></i> Zones</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i> Add Zone
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search zones...">
                </div>
                <div class="col-md-4">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>{{ __('personnel.no') }}</th>
                            <th>Zone Key</th>
                            <th>Zone Name</th>
                            <th>Is HQ Zone</th>
                            <th>{{ __('personnel.description') }}</th>
                            <th style="width: 150px;">{{ __('personnel.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->zoneItems as $zone)
                            <tr>
                                <td>{{ $this->zoneItems->firstItem() + $loop->index }}</td>
                                <td>{{ $zone->key }}</td>
                                <td>{{ $zone->value }}</td>
                                <td>
                                    <span class="badge badge-pill {{ $zone->is_hq_zone ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $zone->is_hq_zone ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $zone->description }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditModal('{{ $zone->id }}')" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteModal('{{ $zone->id }}')" title="{{ __('personnel.delete') }}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No zones found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showZoneModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingZoneId ? 'pencil' : 'plus' }}"></i> {{ $editingZoneId ? 'Edit Zone' : 'Add Zone' }}</h4>
                        <button type="button" class="close" wire:click="closeZoneModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Zone Key</label>
                            <input type="text" class="form-control" wire:model="zoneKey" placeholder="Zone Key...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Zone Name</label>
                            <input type="text" class="form-control" wire:model="zoneValue" placeholder="Zone Name...">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('personnel.description') }}</label>
                            <textarea class="form-control" wire:model="zoneDescription" placeholder="{{ __('personnel.description') }}..."></textarea>
                        </div>
                        <div class="form-group mt-2 mb-0">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="zoneManagerIsHqCheckbox" wire:model="zoneIsHq">
                                <label class="form-check-label" for="zoneManagerIsHqCheckbox">Is HQ zone</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveZone"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeZoneModal">{{ __('personnel.close') }}</button>
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
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.delete_zone') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">{!! __('personnel.confirm_delete_item', ['name' => $zoneKey]) !!}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteZone"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pm-act-btn { border-radius: 7px; padding: 4px 8px; margin-right: 3px; font-size: 12px; }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--edit { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .pm-act-btn--edit:hover { background: #dbeafe; border-color: #93c5fd; }
        .pm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .pm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }
    </style>
</div>
