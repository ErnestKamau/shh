<div class="container-fluid asset-type-manager-page {{ ($showModal || $showDeleteConfirmModal) ? 'modal-active' : '' }}">
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
                                    <th style="width: 160px;" class="text-center">{{ __('equipment.active_equipments') }}</th>
                                    <th style="width: 140px;">{{ __('equipment.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                    <tr>
                                        <td class="equipment-actions-cell text-start">
                                            <div class="equipment-actions-group">
                                                <button type="button" wire:click="edit('{{ $type->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="{{ __('equipment.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                        wire:click="openDeleteAssetTypeConfirmModal('{{ $type->id }}')"
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
                                            <span class="badge rounded-pill asset-type-count-badge">
                                                {{ (int) ($type->active_equipments_count ?? 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge rounded-pill asset-type-status-badge asset-type-status-badge--active">
                                                    <i class="mdi mdi-check-circle-outline me-1"></i>{{ __('equipment.active') }}
                                                </span>
                                            @else
                                                <span class="badge rounded-pill asset-type-status-badge asset-type-status-badge--inactive">
                                                    <i class="mdi mdi-minus-circle-outline me-1"></i>{{ __('equipment.inactive') }}
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

    @if($showDeleteConfirmModal)
        @include('livewire.equipment.partials.delete-asset-type-confirm-modal', [
            'preview' => $pendingDeleteAssetTypePreview,
        ])
    @endif

    <style>
        body:has(.asset-type-manager-page.modal-active) {
            overflow: hidden;
        }
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
        .asset-type-count-badge {
            display: inline-block;
            min-width: 2.25rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #ffffff;
            background-color: #0ea5e9;
            border: 1px solid #0284c7;
        }
        .asset-type-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .asset-type-status-badge--active {
            color: #ffffff;
            background-color: #16a34a;
            border: 1px solid #15803d;
        }
        .asset-type-status-badge--inactive {
            color: #ffffff;
            background-color: #64748b;
            border: 1px solid #475569;
        }

        .asset-type-manager-page .eq-delete-overlay {
            background: rgba(15, 23, 42, 0.52) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 1060;
            overflow-x: hidden;
        }
        .asset-type-manager-page .eq-delete-dialog { max-width: min(520px, calc(100vw - 1.5rem)); }
        .asset-type-manager-page .eq-delete-shell {
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(226, 232, 240, 0.9);
        }
        .asset-type-manager-page .eq-delete-body {
            position: relative;
            padding: 1.35rem;
            background: linear-gradient(180deg, #fafbfc 0%, #fff 42%);
            overflow-x: hidden;
        }
        .asset-type-manager-page .eq-delete-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 2;
            opacity: 0.45;
        }
        .asset-type-manager-page .eq-delete-frame {
            position: relative;
            padding: 1.25rem 1.35rem;
            border-radius: 14px;
            border: 2px dashed rgba(220, 38, 38, 0.55);
            background: linear-gradient(145deg, rgba(254, 242, 242, 0.65) 0%, rgba(255, 255, 255, 0.92) 38%, #fff 100%);
            overflow: hidden;
        }
        .asset-type-manager-page .eq-delete-frame__glow {
            position: absolute;
            top: -30%;
            right: 0;
            width: 45%;
            height: 70%;
            background: radial-gradient(ellipse at center, rgba(248, 113, 113, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .asset-type-manager-page .eq-delete-intro {
            display: flex;
            gap: 0.85rem;
            margin-bottom: 1.15rem;
            padding-right: 1.5rem;
        }
        .asset-type-manager-page .eq-delete-intro__icon {
            width: 2.75rem;
            height: 2.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.35rem;
            color: #b91c1c;
            background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
            border: 1px solid rgba(254, 202, 202, 0.9);
        }
        .asset-type-manager-page .eq-delete-intro__eyebrow {
            margin: 0 0 0.2rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b91c1c;
        }
        .asset-type-manager-page .eq-delete-intro__lead {
            margin: 0;
            font-size: 0.875rem;
            color: #64748b;
        }
        .asset-type-manager-page .eq-delete-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem 1.25rem;
            margin-bottom: 1rem;
        }
        .asset-type-manager-page .eq-delete-field--full { grid-column: 1 / -1; }
        .asset-type-manager-page .eq-delete-field__label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .asset-type-manager-page .eq-delete-field__value {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e293b;
            word-break: break-word;
        }
        .asset-type-manager-page .eq-delete-field__value--primary {
            font-size: 1rem;
            font-weight: 600;
        }
        .asset-type-manager-page .eq-delete-pill {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.78rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }
        .asset-type-manager-page .eq-delete-warning {
            display: flex;
            gap: 0.65rem;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            border: 1px solid rgba(254, 202, 202, 0.65);
        }
        .asset-type-manager-page .eq-delete-warning__icon { color: #dc2626; font-size: 1.15rem; }
        .asset-type-manager-page .eq-delete-warning__text {
            font-size: 0.8125rem;
            color: #991b1b;
            font-weight: 500;
        }
        .asset-type-manager-page .eq-delete-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0.85rem 1.35rem 1.25rem;
            background: #fafbfc;
            border-top: 1px solid #eef2f6;
        }
        .asset-type-manager-page .eq-delete-btn {
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1.15rem;
        }
        .asset-type-manager-page .eq-delete-btn--cancel {
            color: #475569;
            background: #fff;
            border: 1px solid #d8e0eb;
        }
        .asset-type-manager-page .eq-delete-btn--confirm {
            color: #fff;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border: none;
        }
        .asset-type-manager-page .eq-delete-btn--confirm:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
    </style>
</div>
