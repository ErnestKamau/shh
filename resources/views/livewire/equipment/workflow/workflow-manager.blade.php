<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-sitemap text-primary"></i>
                                {{ __('equipment.disposal_approval_workflows') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('equipment.disposal_workflow_subtitle') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('equipment.asset-types.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-format-list-bulleted-type"></i> {{ __('equipment.asset_types') }}
                            </a>
                            <a href="{{ route('equipment.asset-locations.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-map-marker"></i> {{ __('equipment.asset_locations') }}
                            </a>
                            <a href="{{ route('equipment.disposal.workflow.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> {{ __('equipment.create_workflow') }}
                            </a>
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
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.search') }}</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('equipment.search_workflow_name') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.equipment_type') }}</label>
                                <select wire:model.live="typeFilter" class="form-select">
                                    <option value="">{{ __('equipment.all_types') }}</option>
                                    @foreach($assetTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('equipment.location') }}</label>
                                <select wire:model.live="locationFilter" class="form-select">
                                    <option value="">{{ __('equipment.all_locations') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle equipment-table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 110px;">{{ __('equipment.actions') }}</th>
                                    <th>{{ __('equipment.name') }}</th>
                                    <th>{{ __('equipment.application_criteria') }}</th>
                                    <th>{{ __('equipment.steps') }}</th>
                                    <th>{{ __('equipment.status') }}</th>
                                    <th>{{ __('equipment.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $workflow)
                                    <tr>
                                        <td class="equipment-actions-cell">
                                            <div class="equipment-actions-group">
                                                <a href="{{ route('equipment.disposal.workflow.edit', $workflow->id) }}" class="btn btn-sm btn-outline-primary equipment-action-btn" title="{{ __('equipment.edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <button wire:click="deleteWorkflow({{ $workflow->id }})"
                                                        class="btn btn-sm btn-outline-danger equipment-action-btn"
                                                        onclick="return confirm('{{ __('equipment.confirm_delete_workflow') }}');"
                                                        title="{{ __('equipment.delete') }}">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $workflow->workflow_name }}</div>
                                            <small class="text-muted">{{ Str::limit($workflow->description, 50) }}</small>
                                        </td>
                                        <td>
                                            @if($workflow->equipmentType)
                                                <div class="badge bg-info text-dark mb-1">{{ __('equipment.type') }}: {{ $workflow->equipmentType->name }}</div>
                                            @else
                                                <div class="badge bg-light text-dark border mb-1">{{ __('equipment.type_any') }}</div>
                                            @endif
                                            <br>
                                            @if($workflow->location)
                                                <div class="badge bg-info text-dark">{{ __('equipment.location') }}: {{ $workflow->location->name }}</div>
                                            @else
                                                <div class="badge bg-light text-dark border">{{ __('equipment.location_any') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $workflow->steps->count() }} {{ __('equipment.steps') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       wire:click="toggleStatus({{ $workflow->id }})"
                                                       {{ $workflow->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $workflow->is_active ? __('equipment.active') : __('equipment.inactive') }}</label>
                                            </div>
                                        </td>
                                        <td>{{ $workflow->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">{{ __('equipment.no_workflows_found') }}</h5>
                                            <p class="text-muted">{{ __('equipment.create_new_workflow_hint') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $workflows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
