<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-chart-bar text-success"></i>
                                Document Reports & Analytics
                            </h2>
                            <p class="text-muted mb-0">Generate and view document management reports</p>
                        </div>
                        <button wire:click="generateReport" class="btn btn-primary">
                            <i class="mdi mdi-file-document"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Report Parameters
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Report Type</label>
                                <select wire:model.live="reportType" class="form-select">
                                    <option value="">Select Report Type</option>
                                    <option value="document_summary">Document Summary</option>
                                    <option value="expiry_report">Expiry Report</option>
                                    <option value="amendment_history">Amendment History</option>
                                    <option value="audit_log">Audit Log</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Document Type</label>
                                <select wire:model.live="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Results Placeholder -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="mdi mdi-chart-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">Select report parameters and click "Generate Report"</h4>
                        <p class="text-muted">Reports will be displayed here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
