<div>
    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$this->breadcrumbItems"
                :title="__('crm.complaint_type')"
                :subtitle="__('crm.manage_complaint_categories_types')"
                icon="mdi-message-cog"
            >
                <x-slot:actions>
                    <button type="button" class="btn btn-outline-success btn-sm mr-2 crm-btn-export crm-outline-btn-sm" wire:click="exportToExcel" wire:loading.attr="disabled">
                        <i class="fa fa-file-excel mr-1"></i> {{ __('crm.export_to_excel') }}
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm crm-outline-btn-sm" wire:click="openAddForm">
                        <i class="mdi mdi-plus"></i> {{ __('crm.add') }}
                    </button>
                </x-slot:actions>
            </x-crm.page-header>

        <div class="card tab-card">
            <div class="card-body p-0">
                <x-crm.filter-bar :title="__('crm.filters')" class="crm-filter-bar-sticky p-3 border-bottom">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="crm-search-wrapper w-100">
                                <i class="mdi mdi-magnify crm-search-icon"></i>
                                <input type="text" class="form-control w-100" placeholder="{{ __('crm.search_complaint_types') }}"
                                    wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                        <div class="col-md-8 d-flex justify-content-md-end align-items-center flex-wrap">
                            <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                                <select wire:model.live="activeTab" class="crm-select custom-select-sm no-select2" style="width: 180px;">
                                    <option value="all">{{ __('crm.all_complaint_types') }}</option>
                                    <option value="active">{{ __('crm.active') }}</option>
                                    <option value="archived">{{ __('crm.archived') }}</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                                <select wire:model.live="perPage" class="crm-select custom-select-sm no-select2" style="width: 70px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
                            </div>
                        </div>
                </x-crm.filter-bar>

                <x-crm.data-table class="p-3 crm-loading-overlay" wire:loading.class="opacity-50" plain-rows>
                    <x-slot:header>
                            <tr>
                                <th style="width: 100px;">{{ __('crm.actions') }}</th>
                                <th>{{ __('crm.no') }}</th>
                                <th>{{ __('crm.complaint_name') }}</th>
                                <th>{{ __('crm.complaint_description') }}</th>
                                <th>{{ __('crm.active') }}</th>
                            </tr>
                    </x-slot:header>
                            @forelse($types as $index => $type)
                                <tr wire:key="type-{{ $type->id }}">
                                    <td nowrap>
                                        <div class="d-flex flex-nowrap">
                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="openEditForm('{{ $type->id }}')" title="{{ __('crm.edit') }}">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="confirmDelete('{{ $type->id }}')" title="{{ __('crm.delete') }}">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-crm.empty-state
                                            icon="mdi-message-cog-outline"
                                            :message="__('crm.no_complaint_types_found')"
                                            :help="__('crm.no_complaint_types_help')"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                </x-crm.data-table>

                @if($types->hasPages())
                    <x-crm.pagination :summary="__('crm.showing_to_of_results', ['from' => ($types->firstItem() ?? 0), 'to' => ($types->lastItem() ?? 0), 'total' => $types->total()])">
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
                            {{ $editingTypeId ? __('crm.edit') : __('crm.add') }} {{ __('crm.complaint_type') }}
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('crm.complaint_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="name" placeholder="{{ __('crm.complaint_name_placeholder') }}" required />
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('crm.complaint_description') }}</label>
                            <input type="text" class="form-control" wire:model="description" placeholder="{{ __('crm.complaint_description_placeholder') }}" />
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="complaintTypeActive"
                                    wire:model="status">
                                <label class="custom-control-label" for="complaintTypeActive">{{ __('crm.active') }}</label>
                            </div>
                            <small class="text-muted form-text"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('crm.close') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="mdi mdi-content-save"></i> {{ $editingTypeId ? __('crm.update') : __('crm.save') }}
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
                        <h5 class="modal-title" id="typeDeleteConfirmationModalLabel">{{ __('crm.confirm_deletion') }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="mdi mdi-alert-circle-outline text-danger mb-3" style="font-size: 3rem;"></i>
                        <h4>{{ __('crm.are_you_sure') }}</h4>
                        <p class="text-muted">{{ __('crm.delete_complaint_type_warning') }}</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('crm.cancel') }}</button>
                        <button type="button" class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="delete">{{ __('crm.confirm_delete_action') }}</span>
                            <span wire:loading wire:target="delete">{{ __('crm.deleting') }}...</span>
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
