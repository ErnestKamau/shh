<div x-data="{
        initSelect2Styles() {
            let style = document.getElementById('unit-section-select2-style');
            if (!style) {
                style = document.createElement('style');
                style.id = 'unit-section-select2-style';
                style.textContent = '#company-unit-modal .select2-container { z-index: 1060 !important; } #company-unit-modal .select2-dropdown { z-index: 1061 !important; }';
                document.head.appendChild(style);
            }
        },
        openUnitModal() {
            $('#company-unit-modal').modal('show');
        }
    }"
    x-init="initSelect2Styles()"
    x-on:show-unit-modal.window="openUnitModal()"
    x-on:hide-unit-modal.window="$('#company-unit-modal').modal('hide')">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                    style="width:28px;height:28px;background:#f5f0ff;">
                    <i class="mdi mdi-sitemap text-purple" style="font-size:1rem;color:#7c3aed;"></i>
                </span>
                <div>
                    @if($isEditingLabel && $labelColumn == 'unit_configurable_name')
                        <div class="d-inline-flex align-items-center">
                            <input type="text" class="form-control form-control-sm mr-1" wire:model="customLabel"
                                style="width:160px;height:24px;font-size:0.78rem;">
                            <button class="btn btn-xs btn-success mr-1" wire:click="saveLabel"><i
                                    class="mdi mdi-check"></i></button>
                            <button class="btn btn-xs btn-danger" wire:click="cancelEditLabel"><i
                                    class="mdi mdi-close"></i></button>
                        </div>
                    @else
                        <small class="font-weight-bold text-dark" style="font-size:0.82rem;">
                            {{ trim($customer->unit_configurable_name) != '' ? $customer->unit_configurable_name : __('crm.organisational_units') }}
                            <button type="button" class="btn btn-transparent text-info p-0 ml-1"
                                style="font-size:0.7rem;vertical-align:middle;"
                                wire:click="editLabel('unit_configurable_name')">
                                <i class="mdi mdi-pencil-outline"></i>
                            </button>
                        </small>
                        <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.organisational_units_subtitle') }}</small>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-end align-items-center">
                <div class="crm-search-wrapper mr-2">
                    <i class="mdi mdi-magnify crm-search-icon"></i>
                    <input type="text" class="form-control" placeholder="{{ __('crm.search_units') }}"
                        wire:model.live.debounce.300ms="search">
                </div>
                <!-- Show Entries -->
                <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                    <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                    <select wire:model.live="perPage" wire:key="per-page-select-units"
                        class="custom-select custom-select-sm no-select2" style="width: 70px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
                </div>
                <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                    <i class="mdi mdi-file-excel"></i> {{ __('crm.export_to_excel') }}
                </button>
                <button class="btn btn-add btn-sm" wire:click.prevent="openUnitForm">
                    <i class="mdi mdi-plus"></i> {{ __('crm.add') }}
                </button>
            </div>
        </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th nowrap>{{ __('crm.name') }}</th>

                <th nowrap>{{ __('crm.status') }}</th>
                <th style="min-width: 100px;">{{ __('crm.actions') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $unit->name }}</td>

                            <td>
                                @if($unit->active == '1')
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click.prevent="openUnitForm({{$unit->id}})">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-crm.empty-state
                                    icon="mdi-sitemap"
                                    :message="__('crm.no_organisational_units_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($units->firstItem() ?? 0) . ' to ' . ($units->lastItem() ?? 0) . ' of ' . $units->total() . ' results'">
        {{ $units->links() }}
    </x-crm.pagination>

    <!-- Combined Form Modal -->
    <template x-teleport="body">
        <div id="company-unit-modal" class="modal fade" x-on:click.self="$('#company-unit-modal').modal('hide')"
            tabindex="-1" role="dialog" wire:ignore.self>
            <div class="modal-dialog">
                <form class="modal-content" wire:submit.prevent="save">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $unit_id ? 'pencil' : 'plus' }}"></i>
                            {{ $unit_id ? __('crm.edit') : __('crm.add') }} {{ __('crm.company_unit') }}
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($unit_id)
                            <div class="alert alert-primary p-2 d-flex mb-3">
                                <i class="mdi mdi-information-outline mr-2" style="font-size: 20px"></i>
                                <span>{{ __('crm.editing_unit') }}: <strong>{{ $name }}</strong></span>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="control-label">{{ __('crm.name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                wire:model="name" placeholder="{{ __('crm.name') }}..." required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>



                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activeCheck"
                                    wire:model="active">
                                <label class="custom-control-label" for="activeCheck">{{ __('crm.is_active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> {{ __('crm.save_changes') }}
                            <div wire:loading wire:target="save" class="spinner-border spinner-border-sm ml-1"
                                role="status"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>