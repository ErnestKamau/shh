<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-bulleted-type text-primary"></i>
                                {{ __('equipment.asset_types') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.asset_types_subtitle') }}</p>
                        </div>
                        <div>
                            <button wire:click="openModal" class="btn btn-outline-primary px-3" style="border-radius: 9px;">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_new_type') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> {{ __('equipment.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('equipment.search_by_asset_code_or_description') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Types List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle equipment-table">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th class="text-start" style="width: 120px;">{{ __('equipment.actions') }}</th>
                                    <th style="width: 180px;">{{ __('equipment.asset_code') }}</th>
                                    <th>{{ __('equipment.description') }}</th>
                                    <th style="width: 160px;" class="text-center">Active Equipments</th>
                                    <th style="width: 140px;">{{ __('equipment.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                    <tr>
                                        <td class="equipment-actions-cell text-start">
                                            <div class="equipment-actions-group">
                                                <button wire:click="edit({{ $type->id }})" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="{{ __('equipment.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="delete({{ $type->id }})"
                                                        wire:confirm="Are you sure you want to delete this asset type?"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                        title="{{ __('equipment.delete') }}">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $type->asset_code }}</span>
                                        </td>
                                        <td class="asset-type-description-cell">{{ $type->descripton }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-1 fw-bold fs-6 shadow-sm">
                                                {{ $type->active_equipments_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-medium shadow-sm">
                                                    <i class="mdi mdi-check-circle-outline me-1"></i> {{ __('equipment.active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2 fw-medium shadow-sm">
                                                    <i class="mdi mdi-minus-circle-outline me-1"></i> {{ __('equipment.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-format-list-bulleted-type text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">{{ __('equipment.no_asset_types_found') }}</h5>
                                            <p class="text-muted">{{ __('equipment.create_asset_type') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $types->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editId ? 'pencil' : 'plus' }} text-primary"></i>
                            {{ $editId ? __('equipment.edit_asset_type') : __('equipment.create_asset_type') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.asset_code') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('asset_code') is-invalid @enderror" wire:model="asset_code" placeholder="{{ __('equipment.asset_code_example') }}">
                                @error('asset_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.description') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror" wire:model="description" placeholder="{{ __('equipment.asset_description_example') }}">
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" wire:model="is_active">
                                    <label class="form-check-label" for="isActive">{{ __('equipment.active_status') }}</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('equipment.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="mdi mdi-content-save"></i> {{ __('equipment.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .equipment-table {
            table-layout: fixed;
            width: 100%;
        }
        .equipment-actions-group {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        .equipment-actions-group .rm-act-btn {
            margin-right: 0;
        }
        .asset-type-description-cell {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
    </style>
</div>
