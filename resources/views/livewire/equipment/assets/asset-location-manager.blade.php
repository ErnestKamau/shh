<div class="container-fluid">

    {{-- ── Header ────────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-map-marker text-primary"></i>
                                {{ __('equipment.asset_locations') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.asset_locations_subtitle') }}</p>
                        </div>
                        <div>
                            <button wire:click="openModal"
                                class="btn btn-outline-primary"
                                style="border-radius: 8px; font-weight: 500;">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.add_new_location') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters ───────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> {{ __('equipment.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-2 align-items-end">
                        <div class="col-lg-8 col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    class="form-control"
                                    placeholder="{{ __('equipment.search_by_location_code_or_name') }}">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.show') }}</label>
                                <select wire:model.live="perPage" class="form-control">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="75">75</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold d-block">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100 asset-filter-clear-btn">
                                    <i class="mdi mdi-filter-remove-outline"></i> {{ __('equipment.clear_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.lab') ?? 'Lab' }}</label>
                                <div class="tag-select-container"
                                    wire:click="$set('showLabDropdown', true)"
                                    wire:click.outside="$set('showLabDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedLabFilter)
                                            <span class="tag-badge">
                                                <span class="tag-badge-label">{{ data_get($this->selectedLabFilter, 'name') }}</span>
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearLabFilter"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live="labSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedLabFilter ? '' : 'All' }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showLabDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredLabs) > 0)
                                                @foreach($this->filteredLabs as $labFilterOption)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectLabFilter(@js(data_get($labFilterOption, 'id')))">
                                                        {{ data_get($labFilterOption, 'name') }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">{{ __('equipment.no_labs_found') }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Locations table ──────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle equipment-table">
                            <thead style="background-color: rgba(0,0,0,.03);">
                                <tr>
                                    <th class="text-start" style="width: 100px;">{{ __('equipment.actions') }}</th>
                                    <th>{{ __('equipment.location_code') }}</th>
                                    <th>{{ __('equipment.name') }}</th>
                                    <th>{{ __('equipment.lab') ?? 'Lab' }}</th>
                                    <th>{{ __('equipment.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                    <tr>
                                        <td class="equipment-actions-cell text-start">
                                            <div class="equipment-actions-group">
                                                <button wire:click="edit('{{ $location->id }}')" class="btn btn-sm btn-outline-info equipment-action-btn" title="{{ __('equipment.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button wire:click="delete('{{ $location->id }}')"
                                                        wire:confirm="{{ __('equipment.delete_location_confirm') ?? 'Are you sure you want to delete this asset location?' }}"
                                                        class="btn btn-sm btn-outline-danger equipment-action-btn"
                                                        title="{{ __('equipment.delete') }}">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $location->location_code }}</span>
                                        </td>
                                        <td>{{ $location->name }}</td>
                                        <td>
                                            @if($location->lab)
                                                <span class="badge bg-light text-dark border" style="font-weight:500;">
                                                    <i class="mdi mdi-flask-outline me-1"></i>{{ $location->lab->name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($location->is_active)
                                                <span class="badge bg-success">{{ __('equipment.active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('equipment.inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-map-marker text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">{{ __('equipment.no_asset_locations_found') }}</h5>
                                            <p class="text-muted">{{ __('equipment.create_asset_location') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $locations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Sleek Modal ──────────────────────────────── --}}
    @if($showModal)
        <div class="al-modal-backdrop" wire:click.self="closeModal">
            <div class="al-modal-dialog">

                {{-- gradient header --}}
                <div class="al-modal-header">
                    <div class="al-modal-icon">
                        <i class="mdi mdi-map-marker{{ $editId ? '-outline' : '-plus' }}"></i>
                    </div>
                    <div>
                        <h5 class="al-modal-title">
                            {{ $editId ? __('equipment.edit_asset_location') : __('equipment.create_asset_location') }}
                        </h5>
                        <p class="al-modal-subtitle">
                            {{ $editId ? 'Update the details of this location.' : 'Define a new physical asset location tied to a lab.' }}
                        </p>
                    </div>
                    <button type="button" class="al-modal-close" wire:click="closeModal">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="al-modal-body">
                    <form wire:submit.prevent="save" id="al-location-form">

                        {{-- Location Code --}}
                        <div class="al-field-group">
                            <label class="al-label">
                                {{ __('equipment.location_code') }}
                                <span class="al-required">*</span>
                            </label>
                            <div class="al-input-wrap">
                                <i class="mdi mdi-identifier al-input-icon"></i>
                                <input type="text"
                                    class="al-input @error('location_code') al-input--error @enderror"
                                    wire:model="location_code"
                                    placeholder="{{ __('equipment.location_code_example') ?? 'e.g. LOC-001' }}">
                            </div>
                            @error('location_code')
                                <span class="al-error-text"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Name --}}
                        <div class="al-field-group">
                            <label class="al-label">
                                {{ __('equipment.name') }}
                                <span class="al-required">*</span>
                            </label>
                            <div class="al-input-wrap">
                                <i class="mdi mdi-map-marker-outline al-input-icon"></i>
                                <input type="text"
                                    class="al-input @error('name') al-input--error @enderror"
                                    wire:model="name"
                                    placeholder="{{ __('equipment.location_name_example') ?? 'e.g. Main Storage Room' }}">
                            </div>
                            @error('name')
                                <span class="al-error-text"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Lab --}}
                        <div class="al-field-group">
                            <label class="al-label">
                                {{ __('equipment.lab') ?? 'Lab' }}
                            </label>
                            <div class="al-input-wrap">
                                <i class="mdi mdi-flask-outline al-input-icon"></i>
                                <select class="al-select @error('lab_id') al-input--error @enderror"
                                    wire:model="lab_id">
                                    <option value="">— Select a lab (optional) —</option>
                                    @foreach($labs as $lab)
                                        <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('lab_id')
                                <span class="al-error-text"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Active toggle --}}
                        <div class="al-toggle-row">
                            <div class="al-toggle-info">
                                <span class="al-toggle-label">{{ __('equipment.active_status') }}</span>
                                <span class="al-toggle-hint">Mark this location as active and available.</span>
                            </div>
                            <label class="al-switch">
                                <input type="checkbox" wire:model="is_active">
                                <span class="al-switch-track"></span>
                            </label>
                        </div>

                    </form>
                </div>

                <div class="al-modal-footer">
                    <button type="button" class="al-btn al-btn-ghost" wire:click="closeModal">
                        {{ __('equipment.cancel') }}
                    </button>
                    <button type="button" class="al-btn al-btn-primary" wire:click="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="mdi mdi-content-save-outline"></i>
                            {{ $editId ? __('equipment.save_changes') : __('equipment.create_asset_location') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="mdi mdi-loading mdi-spin"></i> Saving…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
/* ── Asset Location Modal ──────────────────────────────────── */
.al-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1055;
    padding: 1rem;
}

.al-modal-dialog {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 25px 60px rgba(15,23,42,.18), 0 8px 20px rgba(15,23,42,.10);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: al-slide-in .22s cubic-bezier(.4,0,.2,1);
}

@keyframes al-slide-in {
    from { opacity: 0; transform: translateY(-12px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.al-modal-header {
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
    padding: 1.4rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    position: relative;
}

.al-modal-icon {
    width: 40px;
    height: 40px;
    background: #eef2ff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.3rem;
    color: #6366f1;
}

.al-modal-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 .15rem;
}

.al-modal-subtitle {
    color: #6b7280;
    font-size: .78rem;
    margin: 0;
}

.al-modal-close {
    position: absolute;
    top: .85rem;
    right: 1rem;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    color: #6b7280;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background .15s;
}
.al-modal-close:hover { background: #e5e7eb; color: #374151; }

.al-modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: .1rem;
}

/* fields */
.al-field-group { margin-bottom: 1.1rem; }

.al-label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .35rem;
    letter-spacing: .01em;
}

.al-required { color: #ef4444; margin-left: 2px; }

.al-input-wrap { position: relative; }

.al-input-icon {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: .95rem;
    pointer-events: none;
}

.al-input,
.al-select {
    width: 100%;
    padding: .55rem .85rem .55rem 2.35rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: .875rem;
    color: #111827;
    background: #f9fafb;
    transition: border-color .15s, box-shadow .15s, background .15s;
    appearance: none;
    outline: none;
}

.al-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%239ca3af' d='M8 11L2 5h12z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; background-size: 12px; padding-right: 2rem; }

.al-input:focus,
.al-select:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

.al-input--error { border-color: #ef4444 !important; }
.al-input--error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important; }

.al-error-text {
    display: block;
    margin-top: .3rem;
    font-size: .75rem;
    color: #ef4444;
}

/* toggle */
.al-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: .75rem 1rem;
    margin-top: .4rem;
}

.al-toggle-label {
    font-size: .83rem;
    font-weight: 600;
    color: #374151;
}

.al-toggle-hint {
    display: block;
    font-size: .72rem;
    color: #9ca3af;
    margin-top: 2px;
}

.al-switch { display: flex; align-items: center; cursor: pointer; }
.al-switch input { display: none; }
.al-switch-track {
    width: 42px;
    height: 24px;
    background: #d1d5db;
    border-radius: 999px;
    position: relative;
    transition: background .2s;
}
.al-switch-track::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(0,0,0,.18);
    transition: transform .2s;
}
.al-switch input:checked ~ .al-switch-track { background: #6366f1; }
.al-switch input:checked ~ .al-switch-track::after { transform: translateX(18px); }

/* footer */
.al-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: .65rem;
    padding: 1rem 1.5rem 1.3rem;
    border-top: 1px solid #f3f4f6;
}

.al-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .52rem 1.15rem;
    border-radius: 9px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
}

.al-btn-ghost {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}
.al-btn-ghost:hover { background: #f3f4f6; color: #374151; }

.al-btn-primary {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    box-shadow: 0 2px 8px rgba(99,102,241,.35);
}
.al-btn-primary:hover { background: linear-gradient(135deg, #4f46e5, #4338ca); box-shadow: 0 4px 14px rgba(99,102,241,.45); }

.asset-filter-clear-btn {
    border-radius: 10px;
}
</style>

</div>
