<div class="container-fluid">
    @include('livewire.equipment.depreciation.partials.page-header', [
        'icon' => 'mdi-format-list-bulleted',
        'title' => 'Depreciation List',
        'description' => 'Assets with depreciation enabled across the laboratory',
    ])

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
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search by equipment name or number...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-control" wire:model.live="statusFilter">
                                    <option value="">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="fully_depreciated">Fully depreciated</option>
                                    <option value="disabled">Disabled</option>
                                </select>
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
                    @if($configs->total() > 0)
                        @include('livewire.equipment.depreciation.partials.table-pagination', ['paginator' => $configs, 'position' => 'controls'])
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle equipment-table">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Equipment</th>
                                    <th>Method</th>
                                    <th>Frequencies</th>
                                    <th>Capitalized</th>
                                    <th>Book Value</th>
                                    <th>Accumulated</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($configs as $config)
                                    <tr>
                                        <td>
                                            <a href="{{ route('view-equipment', $config->equipment_id) }}?tab=asset-depreciation" class="fw-bold text-primary">
                                                {{ $config->equipment?->equipment_number }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $config->equipment?->name }}</small>
                                        </td>
                                        <td>{{ $config->method?->name ?? '—' }}</td>
                                        <td>{{ $config->frequenciesLabel() }}</td>
                                        <td>{{ number_format((float) $config->capitalized_amount, 2) }} {{ $config->currency }}</td>
                                        <td>{{ number_format((float) ($config->current_book_value ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) $config->accumulated_depreciation, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2">
                                                {{ ucfirst(str_replace('_', ' ', $config->status?->value ?? $config->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('view-equipment', $config->equipment_id) }}?tab=asset-depreciation" class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                            @can('equipment.components.depreciation.recalculate')
                                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="recalculate('{{ $config->id }}')" title="Recalculate">
                                                    <i class="mdi mdi-refresh"></i>
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-finance text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="text-muted">No depreciating assets found</h5>
                                            <p class="text-muted mb-0">Enable depreciation on equipment via the add/edit wizard.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($configs->total() > 0)
                        @include('livewire.equipment.depreciation.partials.table-pagination', ['paginator' => $configs, 'position' => 'links'])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
