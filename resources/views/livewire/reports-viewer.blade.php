<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
        <h6 class="mb-0 text-muted">
            <i class="mdi mdi-file-document-outline"></i> Reports & Orders
        </h6>
    </div>
    <div class="card-body p-0">
        <!-- Message Alert -->
        @if($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show m-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="p-3 border-bottom">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search reports...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Date From</label>
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Date To</label>
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm mt-4">
                            <i class="mdi mdi-filter-remove"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Batch Code</th>
                        <th>Sample Type</th>
                        <th>Reference Number</th>
                        <th>Approval Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $report->batch_code }}</span>
                            </td>
                            <td>{{ $report->sample_type }}</td>
                            <td>{{ $report->reference_number }}</td>
                            <td>{{ $report->approval_date ? \Carbon\Carbon::parse($report->approval_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                            <td class="text-center">
                                @if($report->batch_report_url)
                                    <button wire:click="downloadReport({{ $report->id }})" 
                                            class="btn btn-outline-primary btn-sm" 
                                            title="Download Report">
                                        <i class="mdi mdi-download"></i>
                                    </button>
                                @else
                                    <span class="text-muted">No Report</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p class="mt-2">No completed reports found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Orders Section -->
        <div class="border-top">
            <div class="p-3">
                <h6 class="mb-3">
                    <i class="mdi mdi-package-variant"></i> Active Orders
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Batch Code</th>
                                <th>Date Collected</th>
                                <th>Reference Number</th>
                                <th>Sample Type</th>
                                <th>Samples</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $order->batch_code }}</span>
                                    </td>
                                    <td>{{ $order->date_collected ? \Carbon\Carbon::parse($order->date_collected)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $order->reference_number }}</td>
                                    <td>{{ $order->sample_type }}</td>
                                    <td>{{ $order->samples }}</td>
                                    <td>
                                        <span class="badge bg-warning">{{ $order->status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="viewBatchDetails({{ $order->id }})" 
                                                class="btn btn-outline-primary btn-sm" 
                                                title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">
                                        <div class="text-muted">
                                            <i class="mdi mdi-information-outline"></i>
                                            <p class="mt-1 mb-0">No active orders found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

