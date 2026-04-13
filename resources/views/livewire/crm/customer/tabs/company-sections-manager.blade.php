<div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('message'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information"></i> {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-folder-outline text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Company Sections</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Group customer units by section</small>
            </div>
        </div>
        <button class="btn btn-add btn-sm" wire:click="openCreateModal">
            <i class="mdi mdi-plus"></i> Add
        </button>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th nowrap>Name</th>
                <th nowrap>Status</th>
                <th style="min-width: 100px;">Actions</th>
            </tr>
        </x-slot:header>
                    @forelse($this->sections as $section)
                        <tr wire:key="section-{{ $section->id }}">
                            <td>{{ $this->sections->firstItem() + $loop->index }}</td>
                            <td>{{ $section->name }}</td>
                            <td>
                                @if($section->active == '1')
                                    <span class="crm-badge crm-badge-success">Active</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click="editSection({{ $section->id }})" title="Edit">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button class="btn crm-btn crm-btn-delete btn-sm"
                                        wire:click="deleteSection({{ $section->id }})"
                                        wire:confirm="Are you sure you want to delete this company section?"
                                        title="Delete">
                                        <i class="mdi mdi-delete-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-crm.empty-state
                                    icon="mdi-folder-outline"
                                    message="No company sections added for this client."
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($this->sections->firstItem() ?? 0) . ' to ' . ($this->sections->lastItem() ?? 0) . ' of ' . $this->sections->total() . ' results'">
        {{ $this->sections->links() }}
    </x-crm.pagination>

    @if($showCreateModal)
        <template x-teleport="body">
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
                role="dialog" wire:key="create-section-modal" wire:click.self="close">
                <div class="modal-dialog" wire:click.self="close">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-{{ $editingSectionId ? 'pencil-outline' : 'plus' }}"></i>
                                {{ $editingSectionId ? 'Edit' : 'Add' }} Company Section
                            </h5>
                            <button type="button" wire:click="close" class="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="saveSection">
                            <div class="modal-body">
                                @if($modalError)
                                    <div class="alert alert-danger">{{ $modalError }}</div>
                                @endif

                                <div class="form-group">
                                    <label class="control-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="Name..." required>
                                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="sectionActiveCheck"
                                            wire:model="active">
                                        <label class="custom-control-label" for="sectionActiveCheck">Is active?</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="saveSection"><i class="mdi mdi-content-save"></i> Save</span>
                                    <span wire:loading wire:target="saveSection">Saving...</span>
                                </button>
                                <button type="button" wire:click="close" class="btn btn-secondary">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
