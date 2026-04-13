<div>
    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$this->breadcrumbItems"
                title="Complaint Type"
                subtitle="Manage complaint categories and types"
                icon="mdi-message-cog"
            >
                <x-slot:actions>
                    <button type="button" class="btn btn-outline-success btn-sm mr-2 crm-btn-export" wire:click="exportToExcel" wire:loading.attr="disabled">
                        <i class="fa fa-file-excel mr-1"></i> Export to Excel
                    </button>
                    <button type="button" class="btn btn-add btn-sm crm-btn-add" wire:click="openAddForm">
                        <i class="mdi mdi-plus"></i> Add
                    </button>
                </x-slot:actions>
            </x-crm.page-header>

        <div class="card tab-card">
            <div class="card-body p-0">
                <x-crm.filter-bar title="Filters" class="crm-filter-bar-sticky p-3 border-bottom">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="crm-search-wrapper w-100">
                                <i class="mdi mdi-magnify crm-search-icon"></i>
                                <input type="text" class="form-control w-100" placeholder="Search complaint types..."
                                    wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                        <div class="col-md-8 d-flex justify-content-md-end align-items-center flex-wrap">
                            <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                                <select wire:model.live="activeTab" class="crm-select custom-select-sm no-select2" style="width: 180px;">
                                    <option value="all">All Complaint Types</option>
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                                <select wire:model.live="perPage" class="crm-select custom-select-sm no-select2" style="width: 70px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
                            </div>
                        </div>
                </x-crm.filter-bar>

                <x-crm.data-table class="p-3 crm-loading-overlay" wire:loading.class="opacity-50">
                    <x-slot:header>
                            <tr>
                                <th>No</th>
                                <th>Complaint Name</th>
                                <th>Complaint Description</th>
                                <th>Active</th>
                                <th></th>
                            </tr>
                    </x-slot:header>
                            @forelse($types as $index => $type)
                                <tr wire:key="type-{{ $type->id }}">
                                    <td valign="center">{{ $types->firstItem() + $loop->iteration - 1 }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->description ?? '-' }}</td>
                                    <td class="text-small text-center">
                                        @if($type->status == 1 || $type->status === 'active')
                                            <i class="mdi mdi-marker-check text-success"></i>
                                        @else
                                            <i class="mdi mdi-close-circle text-danger"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <x-crm.action-buttons>
                                            <button type="button" class="btn crm-btn crm-btn-edit btn-sm" wire:click="openEditForm({{ $type->id }})" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn crm-btn crm-btn-delete btn-sm" wire:click="confirmDelete({{ $type->id }})" title="Delete">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </x-crm.action-buttons>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-crm.empty-state
                                            icon="mdi-message-cog-outline"
                                            message="No complaint types found."
                                            help="Try adjusting your filters or add a new complaint type."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                </x-crm.data-table>

                @if($types->hasPages())
                    <x-crm.pagination :summary="'Showing ' . ($types->firstItem() ?? 0) . ' to ' . ($types->lastItem() ?? 0) . ' of ' . $types->total() . ' results'">
                        {{ $types->links() }}
                    </x-crm.pagination>
                @endif
            </div>
        </div>
        </div>

        {{-- Add/Edit Modal --}}
        @teleport('body')
        <div wire:ignore.self class="modal fade" id="complaintTypeModal" tabindex="-1" role="dialog"
            aria-labelledby="complaintTypeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="complaintTypeModalLabel">
                            <i class="mdi mdi-{{ $editingTypeId ? 'pencil' : 'plus' }}"></i>
                            {{ $editingTypeId ? 'Edit' : 'Add' }} Complaint Type
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Complaint Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="name" placeholder="Complaint Name..." required />
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">Complaint Description</label>
                            <input type="text" class="form-control" wire:model="description" placeholder="Complaint Description..." />
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="complaintTypeActive"
                                    wire:model="status">
                                <label class="custom-control-label" for="complaintTypeActive">Active</label>
                            </div>
                            <small class="text-muted form-text"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="mdi mdi-content-save"></i> {{ $editingTypeId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport

        {{-- Delete Confirmation Modal --}}
        @teleport('body')
        <div wire:ignore.self class="modal fade" id="typeDeleteConfirmationModal" tabindex="-1" role="dialog"
            aria-labelledby="typeDeleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="typeDeleteConfirmationModalLabel">Confirm Deletion</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                        <h4>Are you sure?</h4>
                        <p class="text-muted">You are about to delete this complaint type. This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="delete">Yes, Delete it</span>
                            <span wire:loading wire:target="delete">Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    </main>

    @script
    <script>
        (function () {
            window.addEventListener('open-complaint-type-modal', function () {
                $('#complaintTypeModal').modal('show');
            });

            window.addEventListener('close-complaint-type-modal', function () {
                $('#complaintTypeModal').modal('hide');
            });

            window.addEventListener('show-type-delete-modal', function () {
                $('#typeDeleteConfirmationModal').modal('show');
            });

            window.addEventListener('hide-type-delete-modal', function () {
                $('#typeDeleteConfirmationModal').modal('hide');
            });
        })();
    </script>
    @endscript
</div>

</div>
