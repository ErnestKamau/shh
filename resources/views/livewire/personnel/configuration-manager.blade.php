<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-format-list-bulleted-type"></i> {{ $config }}</h5>
            <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search configurations...">
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
                            <th>Name</th>
                            <th>Description</th>
                            @if($config === 'Job Description')
                                <th>Responsibilities</th>
                            @endif
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->configItems as $item)
                            <tr>
                                <td>{{ $this->configItems->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                @if($config === 'Job Description')
                                    <td class="text-center">
                                        <a href="{{ route('showResponsibility', ['id' => $item->id]) }}" class="btn btn-outline-success btn-sm" title="View Responsibilities">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                @endif
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openEditModal({{ $item->id }})" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteModal({{ $item->id }})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $config === 'Job Description' ? 5 : 4 }}" class="text-center text-muted">No configurations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showConfigModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingConfigId ? 'pencil' : 'plus' }}"></i> {{ $editingConfigId ? 'Edit' : 'Add' }} {{ $config }}</h4>
                        <button type="button" class="close" wire:click="closeConfigModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" wire:model="configName" placeholder="Name...">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" wire:model="configDescription" placeholder="Description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveConfig"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeConfigModal">Close</button>
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
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Configuration</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">Are you sure you want to delete <strong>{{ $configName }}</strong>?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteConfig"><i class="mdi mdi-delete"></i> Delete</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
