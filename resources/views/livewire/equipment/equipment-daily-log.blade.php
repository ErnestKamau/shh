<div>
    <style>
        .dl-tab-nav {
            display: flex;
            gap: 4px;
            padding: 0 1.5rem;
            border-bottom: 2px solid #e3e9f0;
            margin-bottom: 0;
        }
        .dl-tab-nav .dl-tab-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: color 0.15s ease, border-color 0.15s ease;
            margin-bottom: -2px;
        }
        .dl-tab-nav .dl-tab-item:hover {
            color: #343a40;
            border-bottom-color: #adb5bd;
            text-decoration: none;
        }
        .dl-tab-nav .dl-tab-item.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }
        .dl-tab-nav .dl-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 700;
            background: #e9ecef;
            color: #495057;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .dl-tab-nav .dl-tab-item.active .dl-tab-count {
            background: #007bff;
            color: #fff;
        }
        .dl-tab-panel {
            background: #fff;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 2px 8px rgba(22,28,34,0.06);
        }
        .dl-table thead th {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #495057;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            padding: 10px 12px;
        }
        .dl-table tbody td {
            font-size: 0.82rem;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .dl-table tbody tr:hover {
            background-color: #f0f5ff;
        }
        .dl-empty-state {
            padding: 60px 24px;
            text-align: center;
            color: #adb5bd;
        }
        .dl-empty-state .dl-empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #f0f5ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .dl-empty-state .dl-empty-icon i {
            font-size: 1.6rem;
            color: #007bff;
        }
        .dl-empty-state p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .dl-empty-state small {
            font-size: 0.78rem;
            color: #adb5bd;
        }
        .dl-no-equipment {
            padding: 60px 24px;
            text-align: center;
        }
        .dl-no-equipment i {
            font-size: 3rem;
            color: #dee2e6;
            display: block;
            margin-bottom: 12px;
        }
        .dl-no-equipment p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .dl-date-bar {
            background: #f8f9fa;
            border: 1px solid #e3e9f0;
            border-radius: 8px;
            padding: 10px 16px;
        }
        .dl-reading-input {
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .dl-reading-input:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.15);
        }
        .dl-input-saved {
            border-color: #28a745 !important;
            background-color: #f6fff8 !important;
        }
        .dl-save-btn {
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
    </style>

    <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
        <h4 class="mb-0 font-weight-bold" style="color:#212529; letter-spacing:-0.01em;">
            <i class="mdi mdi-notebook-check-outline text-primary mr-2"></i>Equipment Daily Log
        </h4>
        @php $totalEquipment = array_sum($freqCounts); @endphp
        <span class="badge badge-pill" style="background:#e9ecef; color:#495057; font-size:0.78rem; font-weight:600; padding:6px 12px;">
            {{ $totalEquipment }} {{ Str::plural('Equipment', $totalEquipment) }} Total
        </span>
    </div>

    @php
        $visibleTabs = array_filter($freqCounts, fn($count) => $count > 0);
    @endphp

    @if(empty($visibleTabs))
        {{-- No equipment at all --}}
        <div class="mx-4 mb-4 dl-tab-panel">
            <div class="dl-no-equipment">
                <i class="mdi mdi-notebook-outline"></i>
                <p class="font-weight-semibold mb-1" style="color:#495057;">No Daily Log Equipment Configured</p>
                <p class="mb-0">Enable "Requires Daily Log" on equipment items to see them here.</p>
            </div>
        </div>
    @else
        <div class="mx-4">

            {{-- Date Navigator --}}
            <div class="dl-date-bar d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center" style="gap:10px;">
                    <i class="mdi mdi-calendar-clock" style="font-size:1.1rem; color:#6c757d;"></i>
                    <span style="font-size:0.82rem; font-weight:600; color:#495057;">Log Date:</span>
                    <input type="date"
                           wire:model.lazy="logDate"
                           max="{{ now()->toDateString() }}"
                           class="form-control form-control-sm"
                           style="width:160px; border-radius:6px; font-size:0.82rem;">
                </div>
                @if($this->isToday())
                    <span class="badge badge-pill" style="background:#d4edda; color:#155724; font-size:0.75rem; padding:5px 10px;">
                        <i class="mdi mdi-pencil-outline mr-1"></i>Today — Entries Editable
                    </span>
                @else
                    <span class="badge badge-pill" style="background:#fff3cd; color:#856404; font-size:0.75rem; padding:5px 10px;">
                        <i class="mdi mdi-history mr-1"></i>Viewing {{ \Carbon\Carbon::parse($logDate)->format('D, M j Y') }}
                    </span>
                @endif
            </div>

            {{-- Sleek Tab Navigation --}}
            <nav class="dl-tab-nav">
                @foreach($freqLabels as $freq => $label)
                    @if($freqCounts[$freq] > 0)
                    <a href="#" wire:click.prevent="setFrequency({{ $freq }})"
                       class="dl-tab-item {{ $activeFrequency === $freq ? 'active' : '' }}">
                        {{ $label }}
                        <span class="dl-tab-count">{{ $freqCounts[$freq] }}</span>
                    </a>
                    @endif
                @endforeach
            </nav>

            {{-- Tab Content Panel --}}
            <div class="dl-tab-panel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 dl-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipment Name</th>
                                <th>Equipment No.</th>
                                <th>Make</th>
                                <th>Department</th>
                                <th>Assigned Employee</th>
                                <th>Nature</th>
                                <th>Expected</th>
                                <th>Tolerance</th>
                                @for($slot = 1; $slot <= $activeFrequency; $slot++)
                                    <th style="min-width:170px;">
                                        @if($activeFrequency === 1)
                                            Today's Reading
                                        @else
                                            Reading {{ $slot }}
                                        @endif
                                    </th>
                                @endfor
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipment as $index => $item)
                            @php
                                $expectedDisplay = '-';
                                if ($item->daily_log_value_type === 'constant') {
                                    $expectedDisplay = $item->daily_log_expected_value
                                        . ($item->daily_log_reporting_unit ? ' ' . $item->daily_log_reporting_unit : '');
                                } elseif ($item->daily_log_value_type === 'range') {
                                    $expectedDisplay = $item->daily_log_expected_min . ' – ' . $item->daily_log_expected_max
                                        . ($item->daily_log_reporting_unit ? ' ' . $item->daily_log_reporting_unit : '');
                                }
                                $toleranceDisplay = ($item->daily_log_tolerance && $item->daily_log_nature === 'quantitative')
                                    ? '±' . $item->daily_log_tolerance . '%'
                                    : '-';
                                $isEditable = $this->isToday();
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'daily-log']) }}"
                                       class="font-weight-semibold" style="color:#007bff;">
                                        {{ $item->name }}
                                    </a>
                                </td>
                                <td><code style="font-size:0.78rem; color:#495057;">{{ $item->equipment_number }}</code></td>
                                <td>{{ $item->make ?: '-' }}</td>
                                <td>{{ getInventoryDepartmentByid($item->assigned_department)->name ?? '-' }}</td>
                                <td>{{ optional(getUserById($item->assigned_employee_id))->name ?? '-' }}</td>
                                <td>
                                    @if($item->daily_log_nature)
                                    <span class="badge badge-pill"
                                          style="background:{{ $item->daily_log_nature === 'quantitative' ? '#e8f4fd' : '#fff3cd' }};
                                                 color:{{ $item->daily_log_nature === 'quantitative' ? '#0c63a4' : '#856404' }};
                                                 font-size:0.72rem; font-weight:600; padding:4px 8px;">
                                        {{ ucfirst($item->daily_log_nature) }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">{{ $expectedDisplay }}</td>
                                <td>{{ $toleranceDisplay }}</td>

                                {{-- Dynamic reading slots --}}
                                @for($slot = 1; $slot <= $activeFrequency; $slot++)
                                    @php
                                        $key = "{$item->id}_{$slot}";
                                        $isRange = $item->daily_log_value_type === 'range';
                                        $unit = $item->daily_log_reporting_unit ?? '';
                                    @endphp
                                    <td style="min-width:{{ $isRange ? '220px' : '170px' }};">
                                        @if($isEditable)
                                            @if($isRange)
                                                <div class="d-flex align-items-center" style="gap:4px;">
                                                    <input type="text"
                                                           wire:model.defer="entryValues.{{ $key }}_min"
                                                           class="form-control form-control-sm dl-reading-input {{ isset($savedFlags[$key]) ? 'dl-input-saved' : '' }}"
                                                           placeholder="Min{{ $unit ? ' ('.$unit.')' : '' }}"
                                                           style="max-width:80px; border-radius:5px;">
                                                    <span class="text-muted" style="font-size:0.8rem;">–</span>
                                                    <input type="text"
                                                           wire:model.defer="entryValues.{{ $key }}_max"
                                                           class="form-control form-control-sm dl-reading-input {{ isset($savedFlags[$key]) ? 'dl-input-saved' : '' }}"
                                                           placeholder="Max{{ $unit ? ' ('.$unit.')' : '' }}"
                                                           style="max-width:80px; border-radius:5px;">
                                                    <button wire:click="saveEntry({{ $item->id }}, {{ $slot }}, true)"
                                                            class="btn btn-sm {{ isset($savedFlags[$key]) ? 'btn-success' : 'btn-outline-secondary' }} dl-save-btn"
                                                            title="{{ isset($savedFlags[$key]) ? 'Saved' : 'Save' }}"
                                                            style="padding:3px 7px; border-radius:5px;">
                                                        <i class="mdi {{ isset($savedFlags[$key]) ? 'mdi-check-bold' : 'mdi-content-save-outline' }}"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center" style="gap:6px;">
                                                    <input type="text"
                                                           wire:model.defer="entryValues.{{ $key }}"
                                                           class="form-control form-control-sm dl-reading-input {{ isset($savedFlags[$key]) ? 'dl-input-saved' : '' }}"
                                                           placeholder="{{ $unit ?: 'Enter value' }}"
                                                           style="max-width:110px; border-radius:5px;">
                                                    <button wire:click="saveEntry({{ $item->id }}, {{ $slot }})"
                                                            class="btn btn-sm {{ isset($savedFlags[$key]) ? 'btn-success' : 'btn-outline-secondary' }} dl-save-btn"
                                                            title="{{ isset($savedFlags[$key]) ? 'Saved' : 'Save' }}"
                                                            style="padding:3px 7px; border-radius:5px;">
                                                        <i class="mdi {{ isset($savedFlags[$key]) ? 'mdi-check-bold' : 'mdi-content-save-outline' }}"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $val = $isRange
                                                    ? (($entryValues[$key.'_min'] ?? '') . ' – ' . ($entryValues[$key.'_max'] ?? ''))
                                                    : ($entryValues[$key] ?? null);
                                                $isEmpty = $isRange
                                                    ? (($entryValues[$key.'_min'] ?? '') === '' && ($entryValues[$key.'_max'] ?? '') === '')
                                                    : ($val === null || $val === '');
                                            @endphp
                                            @if(!$isEmpty)
                                                <span class="font-weight-semibold" style="color:#212529;">
                                                    {{ $val }}{{ $unit ? ' ' . $unit : '' }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:0.78rem; font-style:italic;">Not recorded</span>
                                            @endif
                                        @endif
                                    </td>
                                @endfor

                                <td class="text-center">
                                    @if($item->active)
                                        <span class="badge badge-pill" style="background:#d4edda; color:#155724; font-size:0.72rem; padding:4px 8px;">Active</span>
                                    @else
                                        <span class="badge badge-pill" style="background:#f8d7da; color:#721c24; font-size:0.72rem; padding:4px 8px;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'daily-log']) }}"
                                       title="View Equipment" style="border-radius:6px;">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="pb-4"></div>
</div>
