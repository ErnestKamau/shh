<div>
    {{-- Search and Export Bar --}}
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent"><i class="mdi mdi-magnify text-info"></i></span>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                            placeholder="Search batch code, sample code, client, reference...">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="mr-2 mb-0 small text-nowrap">Show:</label>
                    <select wire:model.live="perPage" class="form-control form-control-sm" style="width:auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-5 text-right">
                    <div class="btn-group mr-1" role="group" aria-label="Export">
                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-content-copy"></i> Copy
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" wire:click="exportToExcel">
                            <i class="mdi mdi-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="exportToCsv">
                            <i class="mdi mdi-file-delimited"></i> CSV
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="exportToPdf">
                            <i class="mdi mdi-file-pdf"></i> PDF
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-info btn-sm mr-1">
                        <i class="mdi mdi-upload"></i> Import
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-filter-variant"></i> Filters
                        @if(count(array_filter($filters)) > 0)
                            <span class="badge badge-danger ml-1">{{ count(array_filter($filters)) }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Filters --}}
    @if($showAdvancedFilters)
        <div class="card mb-2 animate__animated animate__fadeIn">
            <div class="card-header py-2 bg-light">
                <strong><i class="mdi mdi-filter-variant"></i> Advanced Filters</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="control-label small">Sample Type</label>
                            <select wire:model.live="filters.sample_type" class="form-control form-control-sm">
                                <option value="">All Types</option>
                                @foreach($sampleTypes as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label small">Priority</label>
                            <select wire:model.live="filters.priority" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="Normal">Normal</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label small">Receipt From</label>
                            <input wire:model.live="filters.receipt_date_from" type="date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label small">Receipt To</label>
                            <input wire:model.live="filters.receipt_date_to" type="date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="control-label small">Collected From</label>
                            <input wire:model.live="filters.date_collected_from" type="date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm mb-2 w-100">
                            <i class="mdi mdi-refresh"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sample Type Tabs --}}
    <div class="card mb-2">
        <div class="card-body py-1 px-2">
            <ul class="nav nav-tabs nav-tabs-sm" role="tablist" style="flex-wrap:nowrap;overflow-x:auto;">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}"
                        wire:click.prevent="setActiveTab('all')" href="#" style="white-space:nowrap;">
                        <i class="mdi mdi-view-grid"></i> All
                        <span class="badge {{ $activeTab === 'all' ? 'badge-secondary' : 'badge-primary' }} ml-1">{{ $allBatchesCount }}</span>
                    </a>
                </li>
                @foreach($sampleTypes as $sampleType)
                    @php
                        $tabKey = strtolower(str_replace([' ', '-', '_'], '', $sampleType->name));
                        $count  = $sampleTypeCounts[$sampleType->id] ?? 0;
                    @endphp
                    @if($count > 0)
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === $tabKey ? 'active' : '' }}"
                                wire:click.prevent="setActiveTab('{{ $tabKey }}')" href="#" style="white-space:nowrap;">
                                {{ $sampleType->name }}
                                <span class="badge {{ $activeTab === $tabKey ? 'badge-secondary' : 'badge-primary' }} ml-1">{{ $count }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive bg-white p-2 border rounded shadow-sm">
        <table class="table table-condensed table-bordered table-sm table-hover no-datatable mb-0" style="font-size: 12px;">
            <thead class="thead-light">
                <tr>
                    <th style="width:30px;">
                        <input type="checkbox" wire:click="selectAll" {{ count($selectedBatches) === count($batches->pluck('batch_code')->toArray()) && count($batches) > 0 ? 'checked' : '' }}>
                    </th>
                    <th nowrap>Submission No</th>
                    <th>
                        <a wire:click.prevent="sortBy('batch_code')" href="#" class="text-dark d-flex align-items-center justify-content-between">
                            Report Number
                            @if($sortField === 'batch_code')
                                <i class="mdi mdi-sort-{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }} ml-1"></i>
                            @else
                                <i class="mdi mdi-sort text-muted ml-1"></i>
                            @endif
                        </a>
                    </th>
                    @if(auth()->user()->CheckViewQcSample())
                        <th>Is QC</th>
                    @endif
                    <th>Lab No</th>
                    <th>Lab Sections</th>
                    <th nowrap>Sample Type</th>
                    <th>Invoice No</th>
                    <th>Stage</th>
                    <th>
                        <a wire:click.prevent="sortBy('crm_customer_id')" href="#" class="text-dark d-flex align-items-center justify-content-between">
                            Client
                            @if($sortField === 'crm_customer_id')
                                <i class="mdi mdi-sort-{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }} ml-1"></i>
                            @else
                                <i class="mdi mdi-sort text-muted ml-1"></i>
                            @endif
                        </a>
                    </th>
                    <th>Client / LPO Ref</th>
                    <th nowrap>
                        <a wire:click.prevent="sortBy('receipt_date')" href="#" class="text-dark d-flex align-items-center justify-content-between">
                            Receipt Date
                            @if($sortField === 'receipt_date')
                                <i class="mdi mdi-sort-{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }} ml-1"></i>
                            @else
                                <i class="mdi mdi-sort text-muted ml-1"></i>
                            @endif
                        </a>
                    </th>
                    <th nowrap>Date Collected</th>
                    <th nowrap>Target Date</th>
                    <th nowrap>Status Days</th>
                    <th>Samples</th>
                    <th>Lab</th>
                    <th>Routine</th>
                    <th>Freq.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $item)
                    @php
                        if ($item->get_target_date) {
                            $targetDateCarbon = \Carbon\Carbon::parse($item->get_target_date->date);
                            $now = \Carbon\Carbon::now();
                            $diff = $now->diffInDays($targetDateCarbon, false);
                            if ($targetDateCarbon->greaterThan($now)) {
                                $diff = 0 - abs($diff) - 1;
                            } else {
                                $diff = abs($diff) * -1;
                            }
                            $targetDateStr = $targetDateCarbon->format('Y-m-d');
                        } else {
                            $targetDateStr = '-';
                            $diff = 0;
                        }
                        $sampleCodes  = $item->samples->pluck('sample_code')->toArray();
                        $sampleStart  = $sampleCodes[0] ?? '';
                        $sampleCount  = count($sampleCodes);
                        $sampleEnd    = $sampleCount > 0 ? end($sampleCodes) : '';

                        $rowClass = '';
                        if ($item->current_account_status === 'Account Holder(Overdue)') {
                            $rowClass = 'overdue-bg-color';
                        } elseif ($item->current_account_status === 'Pay Upfront') {
                            $rowClass = 'upfront-bg-color';
                        } elseif ($item->in_ammendment_proccess) {
                            $rowClass = 'ammend-bg-color';
                        }
                    @endphp
                    <tr class="batch-row {{ $rowClass }} {{ $diff > 0 ? 'text-danger fw-bold' : '' }}">
                        <td>
                            <input type="checkbox" value="{{ $item->batch_code }}" wire:model.live="selectedBatches">
                        </td>
                        <td nowrap>
                            <span class="text-muted small">{{ $item->submission?->submission_number ?? '—' }}</span>
                        </td>
                        <td class="font-weight-bold">
                            <a href="{{ route('view-batch-details', ['batch' => $item->id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}">
                                {{ $item->batch_code }}
                            </a>
                        </td>
                        @if(auth()->user()->CheckViewQcSample())
                            <td class="text-center">
                                {!! $item->is_qc_batch == 1 ? '<i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>' : '-' !!}
                            </td>
                        @endif
                        <td style="max-width:200px;word-wrap:break-word;">{{ $sampleStart . ($sampleCount > 1 ? ' - ' . $sampleEnd : '') }}</td>
                        <td nowrap>{{ $item->getLabSectionsNames() }}</td>
                        <td nowrap>{{ $item->sample_type->name ?? '' }}</td>
                        <td>{{ $item->invoice_number }}</td>
                        <td style="min-width:160px;">{{ $item->status }}</td>
                        <td nowrap>{{ $item->client->name ?? '' }}</td>
                        @php
                            $ref = is_string($item->reference_number ?? null) ? trim((string) $item->reference_number) : '';
                            $refDisplay = ($ref !== '' && strtolower($ref) !== 'n/a') ? $ref : ($item->client->name ?? '—');
                        @endphp
                        <td nowrap>{{ $refDisplay }}</td>
                        <td nowrap>{{ $item->receipt_date ? date('Y-m-d', strtotime($item->receipt_date)) : '' }}</td>
                        <td nowrap>{{ $item->date_collected ? date('Y-m-d', strtotime($item->date_collected)) : '' }}</td>
                        <td nowrap>{{ $targetDateStr }}</td>
                        <td nowrap>{{ number_format($diff, 0) }} Day(s)</td>
                        <td>
                            <span class="badge badge-pill badge-info">{{ $sampleCount }}</span>
                        </td>
                        <td nowrap>{{ implode(', ', $item->labs(true)) }}</td>
                        <td>{{ $item->is_routine == 1 ? 'Yes' : 'No' }}</td>
                        <td>{{ $item->is_routine == 1 ? number_format($item->routine_frequency, 0) . ' days' : 'n/a' }}</td>
                        <td>
                            <a href="{{ route('view-batch-details', ['batch' => $item->id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}"
                                class="btn btn-primary btn-sm p-1">
                                <i class="mdi mdi-lead-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="20" class="text-center text-muted py-5">
                            <i class="mdi mdi-inbox-outline d-block mb-3" style="font-size:3rem;"></i>
                            <p class="font-weight-bold">No batches found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="mt-1 mb-2 small d-flex align-items-center">
        <span class="badge mr-1" style="width: 15px; height: 15px; background-color: #fdf2f2; border: 1px solid #f8b4b4;"></span> <span class="text-muted small py-0">Account Holder(Overdue)</span> &nbsp;&nbsp;
        <span class="badge mr-1" style="width: 15px; height: 15px; background-color: #f0fdf4; border: 1px solid #bbf7d0;"></span> <span class="text-muted small py-0">Account Pay Upfront</span> &nbsp;&nbsp;
        <span class="badge mr-1" style="width: 15px; height: 15px; background-color: #fefce8; border: 1px solid #fef08a;"></span> <span class="text-muted small py-0">Amended Batch</span>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3 bg-white p-3 border rounded shadow-sm">
        <div class="small text-muted font-weight-bold">
            Showing {{ $batches->firstItem() ?? 0 }} to {{ $batches->lastItem() ?? 0 }} of {{ $totalCount }} entries
        </div>
        <div>
            {{ $batches->links() }}
        </div>
    </div>

    <div wire:loading class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1000;">
        <div class="bg-white p-3 border rounded shadow-lg d-flex align-items-center">
            <span class="spinner-border spinner-border-sm text-primary mr-2" role="status"></span>
            <span class="small font-weight-bold">Processing...</span>
        </div>
    </div>

    <style>
        .overdue-bg-color { background-color: #fdf2f2 !important; }
        .upfront-bg-color { background-color: #f0fdf4 !important; }
        .ammend-bg-color { background-color: #fefce8 !important; }
        .batch-row:hover { filter: brightness(0.95); }
        .nav-tabs-sm .nav-link { font-size: 14px; padding: 0.5rem 0.75rem; }
    </style>
</div>
