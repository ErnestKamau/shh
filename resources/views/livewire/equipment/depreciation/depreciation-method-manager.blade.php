<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-cog-outline text-primary"></i>
                                Depreciation Methods
                            </h2>
                            <p class="text-muted mb-0">Configure calculation methodologies used for asset depreciation</p>
                        </div>
                        @can('equipment.components.depreciation.methods.add')
                            <button type="button" class="btn btn-outline-primary px-3" style="border-radius: 9px;" wire:click="openModal">
                                <i class="mdi mdi-plus"></i> Add Method
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search by code or name...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    @if($methods->total() > 0)
                        @include('livewire.equipment.depreciation.partials.table-pagination', ['paginator' => $methods, 'position' => 'controls'])
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle equipment-table">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th style="width: 120px;">Actions</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Default Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($methods as $method)
                                    <tr>
                                        <td>
                                            @can('equipment.components.depreciation.methods.edit')
                                                <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="edit('{{ $method->id }}')" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                            @endcan
                                        </td>
                                        <td><code class="text-primary">{{ $method->code instanceof \App\Enums\Equipment\DepreciationMethodCode ? $method->code->value : $method->code }}</code></td>
                                        <td class="fw-bold">{{ $method->name }}</td>
                                        <td>{{ $method->default_rate !== null ? $method->default_rate . '%' : '—' }}</td>
                                        <td>
                                            @if($method->is_active)
                                                <span class="badge badge-success badge-pill px-3 py-2">
                                                    <i class="mdi mdi-check-circle-outline mr-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-secondary badge-pill px-3 py-2">
                                                    <i class="mdi mdi-minus-circle-outline mr-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-cog-outline text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">No depreciation methods found</h5>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($methods->total() > 0)
                        @include('livewire.equipment.depreciation.partials.table-pagination', ['paginator' => $methods, 'position' => 'links'])
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 12px;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editId ? 'pencil' : 'plus' }} text-primary"></i>
                            {{ $editId ? 'Edit' : 'Add' }} Depreciation Method
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Code</label>
                            <input type="text" class="form-control" wire:model="code" @if($editId) readonly @endif>
                            @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" class="form-control" wire:model="name">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" wire:model="description" rows="2"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Default Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" wire:model="default_rate">
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="method_active" wire:model="is_active">
                            <label class="custom-control-label" for="method_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
