<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 col-sm-12">
                            <h3 class="mb-0 text-primary font-weight-bold">
                                <i class="mdi mdi-calendar-clock mr-2"></i> Equipment Maintenance Program
                            </h3>
                            <p class="text-muted mb-0">Manage and track GCLA annual, preventive, and service maintenance records.</p>
                            @if($maintenancePeriodLabel)
                                <span class="badge badge-light border mt-1" style="font-size: 0.85rem;">
                                    <i class="mdi mdi-calendar-range mr-1 text-primary"></i> Period: {{ $maintenancePeriodLabel }}
                                </span>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 text-md-right mt-3 mt-md-0">
                            @can('equipment.maintenance.view')
                                @if($activeTab === 'annual')
                                    <button wire:click="exportAnnual" class="btn btn-success shadow-sm rounded-pill px-4">
                                        <i class="mdi mdi-file-excel mr-1"></i> Export Annual Program
                                    </button>
                                @elseif($activeTab === 'preventive')
                                    <button wire:click="exportPreventive" class="btn btn-success shadow-sm rounded-pill px-4">
                                        <i class="mdi mdi-file-excel mr-1"></i> Export Preventive Program
                                    </button>
                                @elseif($activeTab === 'register')
                                    <button wire:click="exportRegister" class="btn btn-success shadow-sm rounded-pill px-4">
                                        <i class="mdi mdi-file-excel mr-1"></i> Export Register
                                    </button>
                                @elseif($activeTab === 'replacement' && $activePlanId)
                                    <button wire:click="exportReplacement" class="btn btn-success shadow-sm rounded-pill px-4">
                                        <i class="mdi mdi-file-excel mr-1"></i> Export Replacement Plan
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Tabs Container -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-bottom pb-3 pt-3">
                    <div class="row align-items-center">
                <!-- 4 Tabs - full row d-flex to align perfectly horizontally -->
                <div class="col-12 mb-3">
                    <div class="d-flex flex-row flex-wrap w-100 justify-content-between" style="gap: 12px;">
                        <button class="btn flex-fill py-2.5 font-weight-bold transition-all text-center {{ $activeTab === 'annual' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary border' }}"
                            wire:click="$set('activeTab', 'annual')" type="button" style="border-radius: 8px; font-size: 0.95rem;">
                            <i class="mdi mdi-calendar-clock mr-1"></i> Annual Program
                        </button>
                        <button class="btn flex-fill py-2.5 font-weight-bold transition-all text-center {{ $activeTab === 'preventive' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary border' }}"
                            wire:click="$set('activeTab', 'preventive')" type="button" style="border-radius: 8px; font-size: 0.95rem;">
                            <i class="mdi mdi-shield-check mr-1"></i> Preventive Program
                        </button>
                        <button class="btn flex-fill py-2.5 font-weight-bold transition-all text-center {{ $activeTab === 'register' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary border' }}"
                            wire:click="$set('activeTab', 'register')" type="button" style="border-radius: 8px; font-size: 0.95rem;">
                            <i class="mdi mdi-book-open mr-1"></i> Maintenance Register
                        </button>
                        <button class="btn flex-fill py-2.5 font-weight-bold transition-all text-center {{ $activeTab === 'replacement' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary border' }}"
                            wire:click="$set('activeTab', 'replacement')" type="button" style="border-radius: 8px; font-size: 0.95rem;">
                            <i class="mdi mdi-calendar-blank mr-1"></i> Replacement Plan
                        </button>
                    </div>
                </div>
                <!-- Search bar underneath (only for non-replacement tabs) -->
                @if($activeTab !== 'replacement')
                    <div class="col-12 text-right">
                        <div class="d-inline-block position-relative" style="width: 320px;">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-light border pr-4 rounded-pill py-2" placeholder="Search equipment by name or serial...">
                            <i class="mdi mdi-magnify position-absolute text-muted" style="right: 15px; top: 12px; font-size: 1.15rem;"></i>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body p-0 pb-3">
            <!-- TAB 1: ANNUAL MAINTENANCE PROGRAM -->
            @if($activeTab === 'annual')
                <div class="p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-sm-12">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                <span class="text-secondary font-weight-bold text-uppercase tracking-wide" style="font-size: 0.85rem;">Active Annual Program:</span>
                                <select class="form-control d-inline-block shadow-sm" wire:model.live="activeAnnualProgramId" style="max-width: 320px; border-radius: 8px;">
                                    @forelse($annualPrograms as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }} ({{ optional($program->program_date)->format('d M Y') }}) - {{ ucfirst($program->status) }}</option>
                                    @empty
                                        <option value="">No annual programs created</option>
                                    @endforelse
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="$set('showCreateAnnualProgramModal', true)" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus"></i> Create Annual Program
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-12 text-md-right mt-3 mt-md-0">
                            <div class="d-inline-flex align-items-center" style="gap:8px;">
                                <span class="text-muted small">Rows per page:</span>
                                <select wire:model.live="annualPerPage" class="form-control form-control-sm" style="width: 90px; border-radius: 8px;">
                                    @foreach($perPageOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="openAddAnnualItemModal" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus mr-1"></i> Add Equipment
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                @if($activeAnnualProgram)
                    <div class="mx-3 mt-3 p-3 border rounded bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
                            <div>
                                <h5 class="mb-1 text-dark font-weight-bold">
                                    <i class="mdi mdi-file-document-outline text-primary mr-1"></i>
                                    {{ $activeAnnualProgram->name }}
                                </h5>
                                <div class="text-muted small mb-1">
                                    <strong>Form Date:</strong> {{ optional($activeAnnualProgram->program_date)->format('d M Y') ?: '—' }}
                                </div>
                                <div class="text-muted small">
                                    <strong>Form Details:</strong> {{ $activeAnnualProgram->description ?: 'No description provided.' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $activeAnnualProgram->status === 'active' ? 'badge-success' : 'badge-secondary' }} px-3 py-2 mb-2 d-inline-block">
                                    {{ ucfirst($activeAnnualProgram->status) }}
                                </span>
                                <div class="text-muted small">Entries: {{ $activeAnnualProgram->maintenances_count }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="m-3 border rounded shadow-sm table-responsive bg-white">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="pl-4 py-3 font-weight-bold">Equipment Name</th>
                                <th class="py-3 font-weight-bold">Serial Number</th>
                                <th class="py-3 font-weight-bold">Location (Zones)</th>
                                <th class="py-3 font-weight-bold">Serviced Date</th>
                                <th class="py-3 font-weight-bold">Status</th>
                                <th class="py-3 font-weight-bold">Next Service</th>
                                <th class="py-3 font-weight-bold">Remark</th>
                                <th class="py-3 text-center font-weight-bold pr-4" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $equipment)
                                @php $latest = $equipment->annualMaintenances->first(); @endphp
                                <tr>
                                    <td class="pl-4 font-weight-semibold py-3">
                                        <a href="{{ route('view-equipment', ['equipmentId' => $equipment->id, 'from' => 'equipment-maintenance']) }}" class="text-primary font-weight-bold text-decoration-none">
                                            {{ $equipment->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-muted">{{ $equipment->serial_number ?? '—' }}</td>
                                    <td class="py-3"><span class="badge badge-info px-2.5 py-1 text-xs font-weight-semibold">{{ $equipment->zone_name }}</span></td>
                                    <td class="py-3 font-weight-bold">{{ $latest && $latest->serviced_date ? $latest->serviced_date->format('M d, Y') : '—' }}</td>
                                    <td class="py-3">
                                        @if($latest && $latest->status)
                                            @php
                                                $statusClass = 'badge-secondary';
                                                if (strtolower($latest->status) === 'active' || strtolower($latest->status) === 'completed') $statusClass = 'badge-success';
                                                elseif (strtolower($latest->status) === 'pending') $statusClass = 'badge-warning';
                                                elseif (strtolower($latest->status) === 'overdue' || strtolower($latest->status) === 'critical') $statusClass = 'badge-danger';
                                            @endphp
                                            <span class="badge {{ $statusClass }} px-2.5 py-1 text-xs font-weight-semibold">{{ ucfirst($latest->status) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-weight-bold text-primary">{{ $latest && $latest->next_service ? $latest->next_service->format('M d, Y') : '—' }}</td>
                                    <td class="py-3 text-truncate text-muted" style="max-width: 200px;" title="{{ $latest->remark ?? '' }}">{{ $latest->remark ?? '—' }}</td>
                                    <td class="py-3 text-center pr-4">
                                        @if($latest)
                                            @can('equipment.maintenance.edit')
                                                <button wire:click="openAddAnnualItemModal('{{ $latest->id }}')" class="btn btn-outline-secondary btn-sm rounded-circle p-2 mr-1" title="Edit">
                                                    <i class="mdi mdi-pencil" style="font-size: 1.05rem; line-height: 1;"></i>
                                                </button>
                                            @endcan
                                            @can('equipment.maintenance.delete')
                                                <button onclick="confirm('Remove this annual entry?') || event.stopImmediatePropagation()"
                                                        wire:click="deleteAnnualItem('{{ $latest->id }}')" class="btn btn-outline-danger btn-sm rounded-circle p-2 mr-1" title="Delete">
                                                    <i class="mdi mdi-trash-can" style="font-size: 1.05rem; line-height: 1;"></i>
                                                </button>
                                            @endcan
                                        @endif
                                        <a href="{{ route('view-equipment', ['equipmentId' => $equipment->id, 'from' => 'equipment-maintenance']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="mdi mdi-eye mr-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-wrench-outline display-4 d-block mb-2 text-light"></i>
                                        No equipment records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center px-3 pb-2">
                    <div>{{ $equipments->links() }}</div>
                    @can('equipment.maintenance.add')
                        <button wire:click="openAddAnnualItemModal" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="mdi mdi-plus mr-1"></i> Add Equipment to Annual Program
                        </button>
                    @endcan
                </div>

            <!-- TAB 2: PREVENTIVE MAINTENANCE PROGRAM -->
            @elseif($activeTab === 'preventive')
                @php
                    $quarters      = $maintenanceQuarters;
                    $allMonths     = $maintenanceMonths;
                @endphp

                <div class="p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-sm-12">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                <span class="text-secondary font-weight-bold text-uppercase tracking-wide" style="font-size: 0.85rem;">Active Preventive Program:</span>
                                <select class="form-control d-inline-block shadow-sm" wire:model.live="activePreventiveProgramId" style="max-width: 320px; border-radius: 8px;">
                                    @forelse($preventivePrograms as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }} ({{ optional($program->program_date)->format('d M Y') }}) - {{ ucfirst($program->status) }}</option>
                                    @empty
                                        <option value="">No preventive programs created</option>
                                    @endforelse
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="$set('showCreatePreventiveProgramModal', true)" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus"></i> Create Preventive Program
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-12 text-md-right mt-3 mt-md-0">
                            <div class="d-inline-flex align-items-center" style="gap:8px;">
                                <span class="text-muted small">Rows per page:</span>
                                <select wire:model.live="preventivePerPage" class="form-control form-control-sm" style="width: 90px; border-radius: 8px;">
                                    @foreach($perPageOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="openAddPreventiveItemModal" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus mr-1"></i> Add Equipment
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                @if($activePreventiveProgram)
                    <div class="mx-3 mt-3 p-3 border rounded bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
                            <div>
                                <h5 class="mb-1 text-dark font-weight-bold">
                                    <i class="mdi mdi-file-document-outline text-primary mr-1"></i>
                                    {{ $activePreventiveProgram->name }}
                                </h5>
                                <div class="text-muted small mb-1">
                                    <strong>Form Date:</strong> {{ optional($activePreventiveProgram->program_date)->format('d M Y') ?: '—' }}
                                </div>
                                <div class="text-muted small">
                                    <strong>Form Details:</strong> {{ $activePreventiveProgram->description ?: 'No description provided.' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $activePreventiveProgram->status === 'active' ? 'badge-success' : 'badge-secondary' }} px-3 py-2 mb-2 d-inline-block">
                                    {{ ucfirst($activePreventiveProgram->status) }}
                                </span>
                                <div class="text-muted small">Entries: {{ $activePreventiveProgram->maintenances_count }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @can('equipment.maintenance.edit')
                <div class="m-3 p-3 border rounded bg-white shadow-sm">
                    <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                        <span class="font-weight-bold text-secondary" style="font-size:.9rem; white-space:nowrap;">
                            <i class="mdi mdi-calendar-multiple-check mr-1 text-primary"></i>
                            Set Maintenance Month for All Equipment:
                        </span>
                        <select wire:model="bulkScheduledMonth"
                                class="form-control form-control-sm d-inline-block"
                                style="width:200px; border-radius:8px;">
                            <option value="">-- Select month --</option>
                            @foreach($allMonths as $bm)
                                <option value="{{ $bm->format('Y-m') }}">{{ $bm->format('F Y') }}</option>
                            @endforeach
                        </select>
                        <button wire:click="applyBulkScheduledMonth"
                                class="btn btn-primary btn-sm px-4"
                                style="border-radius:8px;">
                            <i class="mdi mdi-check-all mr-1"></i> Apply to All
                        </button>
                        <span class="text-muted small ml-2">
                            <i class="mdi mdi-information-outline mr-1"></i>
                            Only updates equipment not yet serviced this period.
                        </span>
                    </div>
                </div>
                @endcan

                <div class="mx-3 mb-3 p-3 border rounded bg-light shadow-sm d-flex align-items-center" style="gap:18px; font-size:.82rem;">
                    <span class="d-flex align-items-center" style="gap:5px;">
                        <span style="width:16px;height:16px;border-radius:3px;background:#dee2e6;display:inline-block;border:1px solid #ced4da;"></span>
                        <span class="text-muted">Not scheduled</span>
                    </span>
                    <span class="d-flex align-items-center" style="gap:5px;">
                        <span style="width:16px;height:16px;border-radius:3px;background:#b0bec5;display:inline-block;border:1px solid #90a4ae;"></span>
                        <span class="text-muted">Scheduled (pending)</span>
                    </span>
                    <span class="d-flex align-items-center" style="gap:5px;">
                        <span style="width:16px;height:16px;border-radius:3px;background:#43a047;display:inline-block;border:1px solid #388e3c;"></span>
                        <span class="text-muted">Serviced / Done</span>
                    </span>
                    <span class="text-muted ml-auto">
                        <i class="mdi mdi-cursor-pointer mr-1"></i> Click a month cell to schedule &bull; Click <strong>✓ Done</strong> to mark serviced
                    </span>
                </div>

                <div class="mx-3 mb-3 border rounded shadow-sm bg-white" style="border-radius: 15px !important; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="table mb-0 table-bordered"
                               style="border-collapse:collapse; font-size:.85rem; min-width:1100px;">

                            <thead style="background:#fff; color: #000; font-weight: bold;">
                                <tr>
                                    <th class="text-center py-2 align-middle" style="width: 40px; font-weight: bold;">S/No</th>
                                    <th class="py-2 align-middle" style="min-width:180px; font-weight: bold;">Equipment</th>
                                    <th class="py-2 align-middle" style="min-width:120px; font-weight: bold;">Model</th>
                                    <th class="py-2 align-middle" style="min-width:130px; font-weight: bold;">Serial No.</th>
                                    <th class="py-2 align-middle" style="min-width:120px; font-weight: bold;">GCLA code</th>
                                    <th class="py-2 align-middle" style="min-width:140px; font-weight: bold;">Location(Zone)</th>
                                    @foreach($quarters as $q)
                                        <th colspan="{{ count($q['months']) }}"
                                            class="py-2 text-center align-middle"
                                            style="font-weight: bold;">
                                            {{ $q['label'] }}
                                        </th>
                                    @endforeach
                                    <th class="py-2 text-center align-middle" style="min-width:100px; font-weight: bold;">Actions</th>
                                </tr>
                                <tr>
                                    <th class="py-2"></th>
                                    <th class="py-2"></th>
                                    <th class="py-2"></th>
                                    <th class="py-2"></th>
                                    <th class="py-2"></th>
                                    <th class="py-2"></th>
                                    @foreach($quarters as $q)
                                        @foreach($q['months'] as $m)
                                            <th class="py-1 text-center align-middle"
                                                style="font-weight: bold; min-width:40px; padding:6px 2px;">
                                                {{ $m->format('M') }}
                                            </th>
                                        @endforeach
                                    @endforeach
                                    <th class="py-2"></th>
                                </tr>
                            </thead>

                        <tbody>
                        @forelse($equipments as $equipment)
                            @php
                                $pm = $equipment->preventiveMaintenances->first();
                                $scheduledMonth = $pm?->scheduled_month;
                                $isServiced     = (bool) ($pm?->is_serviced ?? false);
                                $servicedDate   = $pm?->serviced_date;
                                $detailUrl      = route('view-equipment', [
                                    'equipmentId' => $equipment->id,
                                    'from'        => 'equipment-maintenance',
                                ]);
                            @endphp
                            <tr style="background:#fff;">
                                <td class="text-center py-2 align-middle">
                                    {{ (($equipments->currentPage() - 1) * $equipments->perPage()) + $loop->iteration }}
                                </td>
                                <td class="py-2 align-middle" style="font-weight:600;">
                                    <a href="{{ $detailUrl }}" class="text-dark text-decoration-none" style="font-size:.85rem;">
                                        {{ $equipment->name }}
                                    </a>
                                </td>
                                <td class="py-2 align-middle text-dark" style="font-size:.82rem;">
                                    {{ $equipment->model ?? '—' }}
                                </td>
                                <td class="py-2 align-middle text-dark" style="font-size:.82rem;">
                                    {{ $equipment->serial_number ?? '—' }}
                                </td>
                                <td class="py-2 align-middle text-dark" style="font-size:.82rem;">
                                    {{ $equipment->equipment_number ?? '—' }}
                                </td>
                                <td class="py-2 align-middle text-dark" style="font-size:.82rem;">
                                    {{ $equipment->zone_name ?: '—' }}
                                </td>

                                @foreach($quarters as $q)
                                    @foreach($q['months'] as $m)
                                        @php
                                            $isScheduled  = ($scheduledMonth === $m->month);
                                            $cellBg       = '#f5f5f5';
                                            $cellTitle    = 'Click to schedule maintenance in ' . $m->format('F Y');
                                            $cellContent  = '';

                                            if ($isScheduled && $isServiced) {
                                                $cellBg      = '#43a047';
                                                $cellTitle   = 'Serviced' . ($servicedDate ? ' on ' . $servicedDate->format('d M Y') : '') . ' — click to view maintenance log';
                                                $cellContent = '✓';
                                            } elseif ($isScheduled && !$isServiced) {
                                                $cellBg      = '#b0bec5';
                                                $cellTitle   = 'Scheduled for ' . $m->format('F Y') . ' — click to view maintenance log';
                                                $cellContent = '●';
                                            }
                                        @endphp
                                        <td class="align-middle text-center p-0"
                                            style="height:48px; vertical-align:middle; background: {{ $cellBg }};">
                                            @can('equipment.maintenance.edit')
                                                @if($isScheduled)
                                                    <a href="{{ $detailUrl }}"
                                                       title="{{ $cellTitle }}"
                                                       style="display:block; width:100%; height:100%; margin:0 auto;
                                                               line-height:48px; font-size:1.1rem;
                                                               font-weight:700; color:{{ $isServiced ? '#fff' : '#546e7a' }};
                                                               text-decoration:none; cursor:pointer;">
                                                        {{ $cellContent }}
                                                    </a>
                                                @else
                                                    <button wire:click="setScheduledMonth('{{ $equipment->id }}', '{{ $m->format('Y-m') }}')"
                                                            title="{{ $cellTitle }}"
                                                            style="display:block; width:100%; height:100%; margin:0 auto;
                                                                   background:transparent; border:none;
                                                                   cursor:pointer; transition:all .15s;"
                                                            onmouseover="this.style.background='#cfd8dc';"
                                                            onmouseout="this.style.background='transparent';">
                                                    </button>
                                                @endif
                                            @else
                                                <span style="display:block; width:100%; height:100%; margin:0 auto;
                                                             line-height:48px; font-size:1.1rem;
                                                             font-weight:700; color:{{ $isServiced ? '#fff' : '#546e7a' }};
                                                             text-align:center;">
                                                    {{ $cellContent }}
                                                </span>
                                            @endcan
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="py-2 text-center align-middle" style="padding-right: 8px; padding-left: 8px;">
                                    @if($scheduledMonth && !$isServiced)
                                        @can('equipment.maintenance.edit')
                                            <button wire:click="markServiced('{{ $equipment->id }}')"
                                                    class="btn btn-sm btn-success px-2 py-1 d-block w-100 mb-1"
                                                    style="border-radius:6px; font-size:.76rem; font-weight:600;"
                                                    title="Mark as serviced/maintained">
                                                <i class="mdi mdi-check-circle mr-1"></i> Mark Done
                                            </button>
                                        @endcan
                                    @endif
                                    @if($pm)
                                        @can('equipment.maintenance.edit')
                                            <button wire:click="openAddPreventiveItemModal('{{ $pm->id }}')"
                                                    class="btn btn-sm btn-outline-secondary px-2 py-1 d-block w-100 mb-1"
                                                    style="border-radius:6px; font-size:.76rem;">
                                                <i class="mdi mdi-pencil mr-1"></i> Edit
                                            </button>
                                        @endcan
                                    @endif
                                    <a href="{{ $detailUrl }}"
                                       class="btn btn-sm btn-outline-primary px-2 py-1 d-block w-100"
                                       style="border-radius:6px; font-size:.76rem;">
                                        <i class="mdi mdi-eye mr-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($quarters) * 3 + 7 }}" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-shield-outline" style="font-size:3rem; display:block; margin-bottom:8px; opacity:.3;"></i>
                                    No equipment records found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center px-3 pb-2">
                    <div>{{ $equipments->links() }}</div>
                    @can('equipment.maintenance.add')
                        <button wire:click="openAddPreventiveItemModal" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="mdi mdi-plus mr-1"></i> Add Equipment to Preventive Program
                        </button>
                    @endcan
                </div>

            <!-- TAB 3: MAINTENANCE REGISTER -->
            @elseif($activeTab === 'register')
                <div class="p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-sm-12">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                <span class="text-secondary font-weight-bold text-uppercase tracking-wide" style="font-size: 0.85rem;">Active Maintenance Register:</span>
                                <select class="form-control d-inline-block shadow-sm" wire:model.live="activeRegisterProgramId" style="max-width: 320px; border-radius: 8px;">
                                    @forelse($registerPrograms as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }} ({{ optional($program->program_date)->format('d M Y') }}) - {{ ucfirst($program->status) }}</option>
                                    @empty
                                        <option value="">No maintenance registers created</option>
                                    @endforelse
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="$set('showCreateRegisterProgramModal', true)" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus"></i> Create Maintenance Register
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-12 text-md-right mt-3 mt-md-0">
                            <div class="d-inline-flex align-items-center" style="gap:8px;">
                                <span class="text-muted small">Rows per page:</span>
                                <select wire:model.live="registerPerPage" class="form-control form-control-sm" style="width: 90px; border-radius: 8px;">
                                    @foreach($perPageOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="openAddRegisterItemModal" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus mr-1"></i> Add Equipment
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                @if($activeRegisterProgram)
                    <div class="mx-3 mt-3 p-3 border rounded bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
                            <div>
                                <h5 class="mb-1 text-dark font-weight-bold">
                                    <i class="mdi mdi-file-document-outline text-primary mr-1"></i>
                                    {{ $activeRegisterProgram->name }}
                                </h5>
                                <div class="text-muted small mb-1">
                                    <strong>Form Date:</strong> {{ optional($activeRegisterProgram->program_date)->format('d M Y') ?: '—' }}
                                </div>
                                <div class="text-muted small">
                                    <strong>Form Details:</strong> {{ $activeRegisterProgram->description ?: 'No description provided.' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $activeRegisterProgram->status === 'active' ? 'badge-success' : 'badge-secondary' }} px-3 py-2 mb-2 d-inline-block">
                                    {{ ucfirst($activeRegisterProgram->status) }}
                                </span>
                                <div class="text-muted small">Entries: {{ $activeRegisterProgram->registers_count }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="m-3 border rounded shadow-sm table-responsive bg-white">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="pl-4 py-3 font-weight-bold">Equipment Name</th>
                                <th class="py-3 font-weight-bold">Service Provider</th>
                                <th class="py-3 font-weight-bold">Type of Service</th>
                                <th class="py-3 text-right font-weight-bold">Cost (USD)</th>
                                <th class="py-3 text-right font-weight-bold">Cost (TZS)</th>
                                <th class="py-3 text-center font-weight-bold pr-4" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $equipment)
                                @php $latest = $equipment->maintenanceRegisters->first(); @endphp
                                <tr>
                                    <td class="pl-4 font-weight-semibold py-3">
                                        <a href="{{ route('view-equipment', ['equipmentId' => $equipment->id, 'from' => 'equipment-maintenance']) }}" class="text-primary font-weight-bold text-decoration-none">
                                            {{ $equipment->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-muted">{{ $latest->service_provider ?? '—' }}</td>
                                    <td class="py-3 text-muted">{{ $latest->service_type ?? '—' }}</td>
                                    <td class="py-3 text-right font-weight-bold text-success">
                                        {{ $latest && $latest->cost_usd !== null ? '$' . number_format($latest->cost_usd, 2) : '—' }}
                                    </td>
                                    <td class="py-3 text-right font-weight-bold text-secondary">
                                        {{ $latest && $latest->cost_tzs !== null ? number_format($latest->cost_tzs, 2) . ' TZS' : '—' }}
                                    </td>
                                    <td class="py-3 text-center pr-4">
                                        @if($latest)
                                            @can('equipment.maintenance.edit')
                                                <button wire:click="openAddRegisterItemModal('{{ $latest->id }}')" class="btn btn-outline-secondary btn-sm rounded-circle p-2 mr-1" title="Edit">
                                                    <i class="mdi mdi-pencil" style="font-size: 1.05rem; line-height: 1;"></i>
                                                </button>
                                            @endcan
                                            @can('equipment.maintenance.delete')
                                                <button onclick="confirm('Remove this maintenance register entry?') || event.stopImmediatePropagation()"
                                                        wire:click="deleteRegisterItem('{{ $latest->id }}')" class="btn btn-outline-danger btn-sm rounded-circle p-2 mr-1" title="Delete">
                                                    <i class="mdi mdi-trash-can" style="font-size: 1.05rem; line-height: 1;"></i>
                                                </button>
                                            @endcan
                                        @endif
                                        <a href="{{ route('view-equipment', ['equipmentId' => $equipment->id, 'from' => 'equipment-maintenance']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="mdi mdi-eye mr-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-book-open-outline display-4 d-block mb-2 text-light"></i>
                                        No equipment records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($equipments->isNotEmpty())
                            <tfoot class="bg-light font-weight-bold text-dark border-top-2 border-secondary">
                                <tr>
                                    <td colspan="3" class="pl-4 py-3 align-middle">
                                        <span class="text-uppercase text-secondary font-weight-bold letter-spacing-1">Total amount for both Cost(USD) and Cost(TZS)</span>
                                    </td>
                                    <td class="py-3 text-right text-success align-middle font-size-base">
                                        ${{ number_format($totalUsd, 2) }}
                                    </td>
                                    <td class="py-3 text-right text-secondary align-middle font-size-base">
                                        {{ number_format($totalTzs, 2) }} TZS
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center px-3 pb-2">
                    <div>{{ $equipments->links() }}</div>
                    @can('equipment.maintenance.add')
                        <button wire:click="openAddRegisterItemModal" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="mdi mdi-plus mr-1"></i> Add Equipment to Maintenance Register
                        </button>
                    @endcan
                </div>

            <!-- TAB 4: REPLACEMENT PLAN -->
            @elseif($activeTab === 'replacement')
                <div class="p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-sm-12">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                <span class="text-secondary font-weight-bold text-uppercase tracking-wide" style="font-size: 0.85rem;">Active Replacement Plan:</span>
                                <select class="form-control d-inline-block shadow-sm" wire:model.live="activePlanId" style="max-width: 280px; border-radius: 8px;">
                                    @forelse($plans as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->start_year }}/{{ $p->start_year+1 }} - {{ $p->end_year }}/{{ $p->end_year+1 }})</option>
                                    @empty
                                        <option value="">No plans created</option>
                                    @endforelse
                                </select>
                                @can('equipment.maintenance.add')
                                    <button wire:click="$set('showCreatePlanModal', true)" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm font-weight-bold">
                                        <i class="mdi mdi-plus"></i> Create Plan
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-12 text-md-right mt-3 mt-md-0">
                            @if($activePlanId)
                                @can('equipment.maintenance.add')
                                    <button wire:click="openAddPlanItemModal" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold py-2">
                                        <i class="mdi mdi-plus mr-1"></i> Add Equipment to Plan
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>

                @if($activePlan)
                    <div class="p-4 bg-white">
                        <h4 class="text-secondary font-weight-bold mb-4 pb-2 border-bottom text-uppercase tracking-wider" style="font-size: 1.15rem; letter-spacing: 0.5px;">
                            <i class="mdi mdi-file-document-box-outline mr-2 text-primary"></i>Equipment Investment /Replacement plan for {{ $activePlan->start_year }}/{{ $activePlan->start_year+1 }} – {{ $activePlan->end_year }}/{{ $activePlan->end_year+1 }}
                        </h4>
                        <div class="m-3 border rounded shadow-sm table-responsive bg-white">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="pl-4 py-3 font-weight-bold">Equipment Name</th>
                                        <th class="py-3 font-weight-bold">Location (Zone)</th>
                                        @foreach($planYears as $year)
                                            <th class="py-3 text-center font-weight-bold" style="min-width: 110px;">{{ $year }}</th>
                                        @endforeach
                                        <th class="py-3 font-weight-bold" style="min-width: 150px;">Remark</th>
                                        <th class="py-3 text-center font-weight-bold pr-4" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($planItems as $item)
                                        <tr>
                                            <td class="pl-4 font-weight-semibold py-3">
                                                @if($item->equipment_id)
                                                    <a href="{{ route('view-equipment', ['equipmentId' => $item->equipment_id, 'from' => 'equipment-maintenance']) }}" class="text-primary font-weight-bold text-decoration-none">
                                                        {{ $item->equipment_name }}
                                                    </a>
                                                @else
                                                    <span class="text-dark font-weight-semibold">{{ $item->equipment_name }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-muted">{{ $item->location ?? '—' }}</td>
                                            @foreach($planYears as $year)
                                                <td class="py-3 text-center">
                                                    @if($item->scheduled_year === $year)
                                                        <span class="badge badge-success px-3 py-1.5 text-xs font-weight-semibold rounded-pill shadow-sm">
                                                            <i class="mdi mdi-check mr-1"></i> Planned
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="py-3 text-muted text-truncate" style="max-width: 200px;" title="{{ $item->remark ?? '' }}">{{ $item->remark ?? '—' }}</td>
                                            <td class="py-3 text-center pr-4">
                                                @can('equipment.maintenance.edit')
                                                    <button wire:click="editPlanItem('{{ $item->id }}')" class="btn btn-outline-secondary btn-sm rounded-circle p-2 mr-2" title="Edit">
                                                        <i class="mdi mdi-pencil" style="font-size: 1.05rem; line-height: 1;"></i>
                                                    </button>
                                                @endcan
                                                @can('equipment.maintenance.delete')
                                                    <button onclick="confirm('Are you sure you want to remove this equipment from the plan?') || event.stopImmediatePropagation()"
                                                            wire:click="deletePlanItem('{{ $item->id }}')" class="btn btn-outline-danger btn-sm rounded-circle p-2" title="Delete">
                                                        <i class="mdi mdi-trash-can" style="font-size: 1.05rem; line-height: 1;"></i>
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($planYears) + 4 }}" class="text-center py-5 text-muted">
                                                <i class="mdi mdi-calendar-blank-outline display-4 d-block mb-2 text-light"></i>
                                                No equipment has been added to this replacement plan yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <!-- standard empty state when there are no plans -->
                    <div class="text-center py-5 my-5">
                        <i class="mdi mdi-calendar-alert display-2 d-block text-muted mb-3"></i>
                        <h4 class="font-weight-bold text-secondary">No Replacement Plans Created Yet</h4>
                        <p class="text-muted mb-4">Set up an Equipment Investment / Replacement plan to schedule equipment lifecycles and upgrades.</p>
                        @can('equipment.maintenance.add')
                            <button wire:click="$set('showCreatePlanModal', true)" class="btn btn-primary rounded-pill px-4 shadow-sm py-2.5 font-weight-bold">
                                <i class="mdi mdi-plus mr-1"></i> Create Equipment Replacement Plan
                            </button>
                        @endcan
                    </div>
                @endif
            @endif
        </div>
    </div>
        </div>
    </div>

    <!-- ADD/EDIT ANNUAL ITEM MODAL -->
    @if($showAddAnnualItemModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1060; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-pencil-plus mr-1"></i> {{ $editingAnnualItemId ? 'Edit Annual Item' : 'Add Equipment to Annual Program' }}
                        </h5>
                        <button type="button" wire:click="$set('showAddAnnualItemModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="saveAnnualItem">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="font-weight-bold text-secondary">Search & Select Equipment <span class="text-danger">*</span></label>
                                <input type="text" wire:model.live="annualEquipmentSearch" class="form-control" placeholder="Type equipment name or serial..." autocomplete="off">
                                @error('annualEquipmentId') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if(!empty($annualEquipmentSearchResults))
                                    <div class="position-absolute bg-white border rounded shadow-lg w-100" style="z-index: 1100; max-height: 200px; overflow-y: auto;">
                                        @foreach($annualEquipmentSearchResults as $eq)
                                            <button type="button" wire:click="selectAnnualEquipment('{{ $eq->id }}', '{{ addslashes($eq->name) }}')" class="btn btn-light btn-block text-left py-2 px-3 border-0 border-bottom">
                                                {{ $eq->name }}
                                                <span class="text-muted float-right">{{ $eq->serial_number ?? '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Selected Equipment</label>
                                <input type="text" wire:model="annualEquipmentName" class="form-control" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Serviced Date</label>
                                <input type="date" wire:model="annualServicedDate" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Status</label>
                                <input type="text" wire:model="annualRecordStatus" class="form-control" placeholder="e.g. completed, pending">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Next Service</label>
                                <input type="date" wire:model="annualNextService" class="form-control">
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary">Remark</label>
                                <textarea wire:model="annualRemark" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showAddAnnualItemModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ADD/EDIT PREVENTIVE ITEM MODAL -->
    @if($showAddPreventiveItemModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1060; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-pencil-plus mr-1"></i> {{ $editingPreventiveItemId ? 'Edit Preventive Item' : 'Add Equipment to Preventive Program' }}
                        </h5>
                        <button type="button" wire:click="$set('showAddPreventiveItemModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="savePreventiveItem">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="font-weight-bold text-secondary">Search & Select Equipment <span class="text-danger">*</span></label>
                                <input type="text" wire:model.live="preventiveEquipmentSearch" class="form-control" placeholder="Type equipment name or serial..." autocomplete="off">
                                @error('preventiveEquipmentId') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if(!empty($preventiveEquipmentSearchResults))
                                    <div class="position-absolute bg-white border rounded shadow-lg w-100" style="z-index: 1100; max-height: 200px; overflow-y: auto;">
                                        @foreach($preventiveEquipmentSearchResults as $eq)
                                            <button type="button" wire:click="selectPreventiveEquipment('{{ $eq->id }}', '{{ addslashes($eq->name) }}')" class="btn btn-light btn-block text-left py-2 px-3 border-0 border-bottom">
                                                {{ $eq->name }}
                                                <span class="text-muted float-right">{{ $eq->serial_number ?? '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Selected Equipment</label>
                                <input type="text" wire:model="preventiveEquipmentName" class="form-control" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Scheduled Month <span class="text-danger">*</span></label>
                                <select wire:model="preventiveScheduledMonth" class="form-control">
                                    <option value="">-- Select Month --</option>
                                    @foreach(range(1,12) as $m)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary d-block">Status</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="preventiveIsServiced" class="custom-control-input" wire:model="preventiveIsServiced">
                                    <label class="custom-control-label" for="preventiveIsServiced">Marked as serviced</label>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Serviced Date</label>
                                <input type="date" wire:model="preventiveServicedDate" class="form-control" {{ $preventiveIsServiced ? '' : 'disabled' }}>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary">Notes</label>
                                <textarea wire:model="preventiveNotes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showAddPreventiveItemModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ADD/EDIT REGISTER ITEM MODAL -->
    @if($showAddRegisterItemModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1060; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-pencil-plus mr-1"></i> {{ $editingRegisterItemId ? 'Edit Register Item' : 'Add Equipment to Maintenance Register' }}
                        </h5>
                        <button type="button" wire:click="$set('showAddRegisterItemModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="saveRegisterItem">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3 position-relative">
                                <label class="font-weight-bold text-secondary">Search & Select Equipment <span class="text-danger">*</span></label>
                                <input type="text" wire:model.live="registerEquipmentSearch" class="form-control" placeholder="Type equipment name or serial..." autocomplete="off">
                                @error('registerEquipmentId') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if(!empty($registerEquipmentSearchResults))
                                    <div class="position-absolute bg-white border rounded shadow-lg w-100" style="z-index: 1100; max-height: 200px; overflow-y: auto;">
                                        @foreach($registerEquipmentSearchResults as $eq)
                                            <button type="button" wire:click="selectRegisterEquipment('{{ $eq->id }}', '{{ addslashes($eq->name) }}')" class="btn btn-light btn-block text-left py-2 px-3 border-0 border-bottom">
                                                {{ $eq->name }}
                                                <span class="text-muted float-right">{{ $eq->serial_number ?? '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Selected Equipment</label>
                                <input type="text" wire:model="registerEquipmentName" class="form-control" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Year</label>
                                <input type="text" wire:model="registerYear" class="form-control" placeholder="e.g. 2026/2027">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Service Provider</label>
                                <input type="text" wire:model="registerServiceProvider" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Type of Service</label>
                                <input type="text" wire:model="registerServiceType" class="form-control">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-secondary">Cost (USD)</label>
                                        <input type="number" step="0.01" wire:model="registerCostUsd" class="form-control">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-secondary">Cost (TZS)</label>
                                        <input type="number" step="0.01" wire:model="registerCostTzs" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showAddRegisterItemModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ANNUAL PROGRAM MODAL -->
    @if($showCreateAnnualProgramModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-calendar-plus mr-1"></i> Create Annual Program
                        </h5>
                        <button type="button" wire:click="$set('showCreateAnnualProgramModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="createAnnualProgram">
                        <div class="modal-body p-4">
                            <p class="text-muted mb-3">Start with program details, then fill equipment-specific entries from this tab and equipment details.</p>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Program Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newAnnualProgramName" class="form-control rounded-lg @error('newAnnualProgramName') is-invalid @enderror" placeholder="e.g. Annual Calibration Program 2026">
                                @error('newAnnualProgramName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Program Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="newAnnualProgramDate" class="form-control rounded-lg @error('newAnnualProgramDate') is-invalid @enderror">
                                @error('newAnnualProgramDate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Description</label>
                                <textarea wire:model="newAnnualProgramDescription" rows="3" class="form-control rounded-lg @error('newAnnualProgramDescription') is-invalid @enderror" placeholder="Describe scope and intent of this annual program..."></textarea>
                                @error('newAnnualProgramDescription') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary d-block">Status <span class="text-danger">*</span></label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="annualStatusDraft" class="custom-control-input" wire:model="newAnnualProgramStatus" value="draft">
                                    <label class="custom-control-label" for="annualStatusDraft">Draft</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="annualStatusActive" class="custom-control-input" wire:model="newAnnualProgramStatus" value="active">
                                    <label class="custom-control-label" for="annualStatusActive">Active</label>
                                </div>
                                @error('newAnnualProgramStatus') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showCreateAnnualProgramModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- PREVENTIVE PROGRAM MODAL -->
    @if($showCreatePreventiveProgramModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-shield-check mr-1"></i> Create Preventive Program
                        </h5>
                        <button type="button" wire:click="$set('showCreatePreventiveProgramModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="createPreventiveProgram">
                        <div class="modal-body p-4">
                            <p class="text-muted mb-3">Start with program details, then schedule maintenance months for equipment in this program.</p>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Program Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newPreventiveProgramName" class="form-control rounded-lg @error('newPreventiveProgramName') is-invalid @enderror" placeholder="e.g. Preventive Maintenance Program FY2026">
                                @error('newPreventiveProgramName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Program Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="newPreventiveProgramDate" class="form-control rounded-lg @error('newPreventiveProgramDate') is-invalid @enderror">
                                @error('newPreventiveProgramDate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Description</label>
                                <textarea wire:model="newPreventiveProgramDescription" rows="3" class="form-control rounded-lg @error('newPreventiveProgramDescription') is-invalid @enderror" placeholder="Describe objectives and preventive maintenance approach..."></textarea>
                                @error('newPreventiveProgramDescription') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary d-block">Status <span class="text-danger">*</span></label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="preventiveStatusDraft" class="custom-control-input" wire:model="newPreventiveProgramStatus" value="draft">
                                    <label class="custom-control-label" for="preventiveStatusDraft">Draft</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="preventiveStatusActive" class="custom-control-input" wire:model="newPreventiveProgramStatus" value="active">
                                    <label class="custom-control-label" for="preventiveStatusActive">Active</label>
                                </div>
                                @error('newPreventiveProgramStatus') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showCreatePreventiveProgramModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MAINTENANCE REGISTER MODAL -->
    @if($showCreateRegisterProgramModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-book-open mr-1"></i> Create Maintenance Register
                        </h5>
                        <button type="button" wire:click="$set('showCreateRegisterProgramModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="createRegisterProgram">
                        <div class="modal-body p-4">
                            <p class="text-muted mb-3">Start with register metadata, then capture service records under this register.</p>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Register Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newRegisterProgramName" class="form-control rounded-lg @error('newRegisterProgramName') is-invalid @enderror" placeholder="e.g. Maintenance Register 2026">
                                @error('newRegisterProgramName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Register Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="newRegisterProgramDate" class="form-control rounded-lg @error('newRegisterProgramDate') is-invalid @enderror">
                                @error('newRegisterProgramDate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Description</label>
                                <textarea wire:model="newRegisterProgramDescription" rows="3" class="form-control rounded-lg @error('newRegisterProgramDescription') is-invalid @enderror" placeholder="Describe maintenance scope and record intent..."></textarea>
                                @error('newRegisterProgramDescription') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-secondary d-block">Status <span class="text-danger">*</span></label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="registerStatusDraft" class="custom-control-input" wire:model="newRegisterProgramStatus" value="draft">
                                    <label class="custom-control-label" for="registerStatusDraft">Draft</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="registerStatusActive" class="custom-control-input" wire:model="newRegisterProgramStatus" value="active">
                                    <label class="custom-control-label" for="registerStatusActive">Active</label>
                                </div>
                                @error('newRegisterProgramStatus') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showCreateRegisterProgramModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- 1. CREATE PLAN MODAL -->
    @if($showCreatePlanModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-calendar-plus mr-1"></i> Create Equipment Replacement Plan
                        </h5>
                        <button type="button" wire:click="$set('showCreatePlanModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="createPlan">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newPlanName" class="form-control rounded-lg @error('newPlanName') is-invalid @enderror" placeholder="e.g. Equipment Investment /Replacement plan">
                                @error('newPlanName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="row">
                                <div class="col-6 form-group">
                                    <label class="font-weight-bold text-secondary">Start Year <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="newPlanStartYear" class="form-control rounded-lg @error('newPlanStartYear') is-invalid @enderror" placeholder="e.g. 2025">
                                    @error('newPlanStartYear') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-6 form-group">
                                    <label class="font-weight-bold text-secondary">End Year <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="newPlanEndYear" class="form-control rounded-lg @error('newPlanEndYear') is-invalid @enderror" placeholder="e.g. 2030">
                                    @error('newPlanEndYear') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showCreatePlanModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. ADD/EDIT PLAN ITEM MODAL -->
    @if($showAddPlanItemModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-pencil-plus mr-1"></i> {{ $editingItemId ? 'Edit Plan Item' : 'Add Equipment to Plan' }}
                        </h5>
                        <button type="button" wire:click="$set('showAddPlanItemModal', false)" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="addPlanItem">
                        <div class="modal-body p-4">
                            <!-- Searchable Dropdown for Equipment Name -->
                            <div class="form-group mb-3 position-relative">
                                <label class="font-weight-bold text-secondary">Search & Select Equipment <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="mdi mdi-magnify"></i></span>
                                    </div>
                                    <input type="text" wire:model.live="equipmentSearch" class="form-control rounded-right-lg @error('planItemName') is-invalid @enderror" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" placeholder="Type name to search equipment..." autocomplete="off">
                                </div>
                                @error('planItemName') <span class="text-danger text-sm d-block mt-1">{{ $message }}</span> @enderror

                                @if(!empty($equipmentSearchResults))
                                    <div class="position-absolute bg-white border rounded shadow-lg w-100" style="z-index: 1100; max-height: 200px; overflow-y: auto; left: 0; right: 0; margin-top: 2px;">
                                        @foreach($equipmentSearchResults as $eq)
                                            <button type="button" wire:click="selectEquipment('{{ $eq->id }}', '{{ addslashes($eq->name) }}')" class="btn btn-light btn-block text-left py-2.5 px-3 border-bottom border-0 m-0 transition-all font-weight-semibold">
                                                <i class="mdi mdi-tools mr-2 text-primary"></i>{{ $eq->name }}
                                                @if($eq->serial_number)
                                                    <span class="text-muted text-xs float-right">S/N: {{ $eq->serial_number }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Selected Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="planItemName" class="form-control rounded-lg" readonly placeholder="Select an equipment from above search">
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Scheduled Replacement Year <span class="text-danger">*</span></label>
                                <select wire:model="planItemYear" class="form-control rounded-lg @error('planItemYear') is-invalid @enderror">
                                    <option value="">-- Choose Plan Year --</option>
                                    @if($activePlan)
                                        @foreach($activePlan->getYearsRange() as $yr)
                                            <option value="{{ $yr }}">{{ $yr }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('planItemYear') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Location (Zone)</label>
                                <select wire:model="planItemLocation" class="form-control rounded-lg @error('planItemLocation') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="">-- Choose Location (Zone) --</option>
                                    @foreach($zones as $z)
                                        @php
                                            $zVal = $z->value ?: $z->name ?: $z->key;
                                        @endphp
                                        <option value="{{ $zVal }}">{{ $zVal }} ({{ $z->key }})</option>
                                    @endforeach
                                </select>
                                @error('planItemLocation') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Remark</label>
                                <textarea wire:model="planItemRemark" class="form-control rounded-lg @error('planItemRemark') is-invalid @enderror" rows="3" placeholder="Add scheduling or budget remarks..."></textarea>
                                @error('planItemRemark') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" wire:click="$set('showAddPlanItemModal', false)" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" {{ empty($planItemName) ? 'disabled' : '' }}>Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
