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
            <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateModal">
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
                            <option value="{{ $option }}">Show {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Description</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->zoneItems as $zone)
                            <tr>
                                <td>{{ $this->zoneItems->firstItem() + $loop->index }}</td>
                                <td>{{ $zone->key }}</td>
                                <td>{{ $zone->value }}</td>
                                <td>{{ $zone->description }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openEditModal({{ $zone->id }})" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteModal({{ $zone->id }})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No zones found.</td>
                            </tr>
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
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingZoneId ? 'pencil' : 'plus' }}"></i> {{ $editingZoneId ? 'Edit' : 'Add' }} Zone</h4>
                        <button type="button" class="close" wire:click="closeZoneModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Key</label>
                            <input type="text" class="form-control" wire:model="zoneKey" placeholder="Zone key...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Value</label>
                            <input type="text" class="form-control" wire:model="zoneValue" placeholder="Zone value...">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" wire:model="zoneDescription" placeholder="Description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveZone"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeZoneModal">Close</button>
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
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Zone</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">Are you sure you want to delete <strong>{{ $zoneKey }}</strong>?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteZone"><i class="mdi mdi-delete"></i> Delete</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
