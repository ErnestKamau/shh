@php
    $jobs = $this->jobs;
    $analysisNames = $this->analysisNamesByJob;
@endphp
<div class="row mb-4 mt-2">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                <div>
                    <h5 class="mb-0"><i class="mdi mdi-file-clock-outline"></i> Awaiting PO</h5>
                    <div class="small text-muted">
                        Samples with no purchase order cover. They stay out of worksheets, TAT and lab queues until a PO is applied.
                        Split jobs share the original job's test report, which is issued once every part is complete or cancelled.
                    </div>
                </div>
                <div style="min-width: 260px;">
                    <input type="search" class="form-control form-control-sm" placeholder="Search job number or customer" wire:model.live.debounce.400ms="search">
                </div>
            </div>
            <div class="card-body p-0">
                @if($errorMessage !== '')
                    <div class="alert alert-danger py-2 small m-3">{{ $errorMessage }}</div>
                @endif
                @if($successMessage !== '')
                    <div class="alert alert-success py-2 small m-3">{{ $successMessage }}</div>
                @endif

                @if($jobs->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-check-circle-outline" style="font-size: 2rem;"></i>
                        <div>No jobs are waiting for a purchase order.</div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Job</th>
                                    <th>Customer</th>
                                    <th>Split from</th>
                                    <th class="text-right">Samples</th>
                                    <th>Analyses</th>
                                    <th>Held for</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                    @php
                                        $heldDays = $this->heldForDays($job);
                                    @endphp
                                    <tr wire:key="awaiting-po-{{ $job->id }}">
                                        <td class="text-nowrap">
                                            <a href="{{ route('view-batch-details', ['batch' => $job->id, 'client' => 0, 'portal' => 0, 'status' => $job->status]) }}">{{ $job->batch_code }}</a>
                                        </td>
                                        <td>{{ $job->customer?->name ?? '—' }}</td>
                                        <td class="text-nowrap">
                                            @if($job->splitFrom)
                                                <a href="{{ route('view-batch-details', ['batch' => $job->splitFrom->id, 'client' => 0, 'portal' => 0, 'status' => 'All Samples']) }}">{{ $job->splitFrom->batch_code }}</a>
                                            @else
                                                <span class="text-muted">Whole job held</span>
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $job->samples_count }}</td>
                                        <td class="small">{{ $analysisNames[(string) $job->id] ?? '—' }}</td>
                                        <td class="text-nowrap">
                                            <span class="badge {{ $heldDays >= 14 ? 'badge-danger' : ($heldDays >= 7 ? 'badge-warning' : 'badge-light') }}">
                                                {{ $heldDays === 1 ? '1 day' : $heldDays.' days' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            @if($this->canApply)
                                                <button type="button" class="btn btn-sm btn-primary" wire:click="openApplyModal('{{ $job->id }}')" wire:loading.attr="disabled">
                                                    <i class="mdi mdi-file-certificate-outline"></i> Apply PO
                                                </button>
                                            @endif
                                            @if($this->canCancel)
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openCancelModal('{{ $job->id }}')" wire:loading.attr="disabled">
                                                    <i class="mdi mdi-close-circle-outline"></i> Cancel
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($showApplyModal && $this->selectedJob)
        @php
            $selectedJob = $this->selectedJob;
            $drawableOrders = $this->drawableOrders;
            $preview = $this->applyPreview;
        @endphp
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Apply PO to job {{ $selectedJob->batch_code }}</h5>
                        <button type="button" class="close" wire:click="closeApplyModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            {{ $selectedJob->samples_count }} sample(s) for {{ $selectedJob->customer?->name ?? 'this customer' }}.
                            Covered samples are released to Samples In Lab with the analysts assigned at acceptance.
                            Any the PO cannot cover move to a new Awaiting PO job.
                        </p>

                        @if($drawableOrders->isEmpty())
                            <div class="alert alert-warning small mb-0">
                                This customer has no active PO that can cover samples today.
                                @if($this->canCreatePurchaseOrders)
                                    <a href="{{ route('billing.customer-purchase-orders.create') }}" target="_blank" rel="noopener">Register the customer's PO</a> (or top up an existing one), then come back.
                                @else
                                    Ask finance to register or top up the customer's PO.
                                @endif
                            </div>
                        @else
                            <div class="form-group">
                                <label for="heldJobPurchaseOrder">Purchase order <span class="text-danger">*</span></label>
                                <select id="heldJobPurchaseOrder" class="form-control" wire:model.live="selectedPurchaseOrderId">
                                    <option value="">Select a PO…</option>
                                    @foreach($drawableOrders as $po)
                                        <option value="{{ $po->id }}">
                                            {{ $po->po_number }} · {{ $po->po_type?->label() }} · {{ (int) $po->remaining_total }} remaining{{ $po->valid_to ? ' · valid to '.$po->valid_to->format('d M Y') : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_purchase_order_id')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                                @error('sample_header_id')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                                @error('job')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            @if($preview !== null)
                                @if($preview['covered'] === 0)
                                    <div class="alert alert-danger small mb-0">
                                        This PO has no balance for any sample on this job. Top it up or add a matching line first.
                                    </div>
                                @elseif($preview['held'] === 0)
                                    <div class="alert alert-success small mb-0">
                                        All {{ $preview['covered'] }} sample(s) will be released to the lab.
                                    </div>
                                @else
                                    <div class="alert alert-warning small mb-0">
                                        {{ $preview['covered'] }} sample(s) will be released to the lab.
                                        {{ $preview['held'] }} sample(s) will move to a new Awaiting PO job.
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeApplyModal">Close</button>
                        <button type="button" class="btn btn-primary" wire:click="applyPurchaseOrder" wire:loading.attr="disabled" wire:target="applyPurchaseOrder"
                            @disabled($drawableOrders->isEmpty() || $selectedPurchaseOrderId === '' || ($preview['covered'] ?? 1) === 0)>
                            Apply PO and release
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showCancelModal && $this->selectedJob)
        @php
            $selectedJob = $this->selectedJob;
        @endphp
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel held job {{ $selectedJob->batch_code }}</h5>
                        <button type="button" class="close" wire:click="closeCancelModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Use this when the customer will not issue a PO for these {{ $selectedJob->samples_count }} sample(s).
                            The job is closed and taken out of the report group{{ $selectedJob->splitFrom ? ', so job '.$selectedJob->splitFrom->batch_code.'\'s test report no longer waits for it' : '' }}.
                            This cannot be undone.
                        </p>
                        <div class="form-group mb-0">
                            <label for="cancelHeldJobReason">Reason <span class="text-danger">*</span></label>
                            <textarea id="cancelHeldJobReason" class="form-control" rows="3" maxlength="1000" wire:model.defer="cancelReason" placeholder="e.g. Customer declined; samples returned"></textarea>
                            @error('reason')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            @error('job')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeCancelModal">Keep job</button>
                        <button type="button" class="btn btn-danger" wire:click="cancelHeldJob" wire:loading.attr="disabled" wire:target="cancelHeldJob">
                            Cancel held job
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
