<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#f0fdf4;">
                <i class="mdi mdi-map-marker-outline text-success" style="font-size:1rem;"></i>
            </span>
            <div>
                @if($isEditingLabel && $labelColumn == 'sample_point_configurable_name')
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
                        {{ trim($customer->sample_point_configurable_name) != '' ? $customer->sample_point_configurable_name : 'Sample Collection Points' }}
                        <button type="button" class="btn btn-transparent text-info p-0 ml-1"
                            style="font-size:0.7rem;vertical-align:middle;"
                            wire:click="editLabel('sample_point_configurable_name')">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                    </small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">Registered sampling locations &amp;
                        active monitoring points</small>
                @endif
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="Search sample points..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <!-- Show Entries -->
            <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                <select wire:model.live="perPage" wire:key="per-page-select" class="custom-select custom-select-sm no-select2" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> Export to Excel
            </button>
            <button class="btn btn-add btn-sm" wire:click="openPointForm">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i>
        Loading...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>{{ trim($customer->unit_configurable_name) != "" ? $customer->unit_configurable_name : 'Unit' }}</th>
                <th>Status</th>
                <th style="min-width: 100px;">Actions</th>
            </tr>
        </x-slot:header>
                    @forelse($samplePoints as $point)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $point->name }}</td>
                            <td>{{ $point->crm_unit_name ?? ($point->unit->name ?? '-') }}</td>
                            <td>
                                @if($point->active == '1')
                                    <span class="crm-badge crm-badge-success">Active</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-edit btn-sm" title="Edit"
                                        wire:click="openPointForm({{ $point->id }})">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-crm.empty-state
                                    icon="mdi-map-marker-outline"
                                    message="No sampling locations registered for this client."
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($samplePoints->firstItem() ?? 0) . ' to ' . ($samplePoints->lastItem() ?? 0) . ' of ' . $samplePoints->total() . ' results'">
        {{ $samplePoints->links() }}
    </x-crm.pagination>

    @if($showForm)
        @livewire(\App\Livewire\Crm\Customer\SamplePointForm::class, [
            'customerId' => $customer->id,
            'pointId' => $editingPoint ? $editingPoint->id : null
        ], 'sample-point-form-' . ($editingPoint ? $editingPoint->id : 'new'))
    @endif
</div>