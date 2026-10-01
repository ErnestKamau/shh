@php
    $coverage = $this->coverage;
    $purchaseOrder = $coverage['purchase_order'] ?? null;
    $requirementLabel = match ($coverage['requirement'] ?? null) {
        \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED => 'PO required',
        \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_SKIPPABLE => 'PO optional (walk-in)',
        default => 'PO optional',
    };
    $enquiry = $this->enquiry;
    $recentChanges = $this->recentChanges;
@endphp
<div class="row mb-3">
    <div class="col-12">
        @if($coverage !== null)
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    @if($errorMessage !== '')
                        <div class="alert alert-danger py-2 small mb-2">{{ $errorMessage }}</div>
                    @endif
                    @if($successMessage !== '')
                        <div class="alert alert-success py-2 small mb-2">{{ $successMessage }}</div>
                    @endif

                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px 16px;">
                            <div>
                                <div class="small text-muted text-uppercase">Purchase order</div>
                                <div class="font-weight-bold">
                                    <i class="mdi mdi-file-certificate-outline" aria-hidden="true"></i>
                                    @if($purchaseOrder !== null)
                                        @if($this->canViewPurchaseOrders)
                                            <a href="{{ route('billing.customer-purchase-orders.show', $purchaseOrder->id) }}" target="_blank" rel="noopener">{{ $purchaseOrder->po_number }}</a>
                                        @else
                                            {{ $purchaseOrder->po_number }}
                                        @endif
                                        <span class="badge badge-light ml-1">{{ $purchaseOrder->po_type?->label() ?? '' }}</span>
                                    @elseif(filled($enquiry?->client_po_number))
                                        {{ $enquiry->client_po_number }}
                                    @elseif($enquiry?->po_skipped)
                                        <span class="text-muted">PO skipped</span>
                                    @else
                                        <span class="text-muted">No PO</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge {{ ($coverage['requirement'] ?? '') === \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED ? 'badge-info' : 'badge-secondary' }}">{{ $requirementLabel }}</span>
                            @if($coverage['requested'] > 0)
                                @if($coverage['uncovered'] === 0)
                                    <span class="badge badge-success">All {{ $coverage['requested'] }} sample(s) covered</span>
                                @else
                                    <span class="badge badge-warning">{{ $coverage['covered'] }} of {{ $coverage['requested'] }} sample(s) covered</span>
                                @endif
                            @endif
                            @if($coverage['reserved'] > 0)
                                <span class="small text-muted">{{ $coverage['reserved'] }} reserved on the PO</span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            @if($recentChanges->isNotEmpty())
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggleHistory">
                                    <i class="mdi mdi-history"></i> {{ $showHistory ? 'Hide' : 'Show' }} PO changes ({{ $recentChanges->count() }})
                                </button>
                            @endif
                            @if($this->canChange)
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openChangeModal" wire:loading.attr="disabled" wire:target="openChangeModal">
                                    <i class="mdi mdi-swap-horizontal"></i> Change PO
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($coverage['uncovered'] > 0)
                        <table class="table table-sm small mt-3 mb-1">
                            <thead>
                                <tr>
                                    <th>Samples</th>
                                    <th class="text-right">Requested</th>
                                    <th class="text-right">Covered</th>
                                    <th>Why not covered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coverage['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-right">{{ $row['requested'] }}</td>
                                        <td class="text-right {{ $row['uncovered'] > 0 ? 'text-warning font-weight-bold' : '' }}">{{ $row['covered'] }}</td>
                                        <td class="text-muted">{{ $row['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="small text-muted">
                            Reception is not blocked. Samples that are not covered will be held as Awaiting PO when the job is created.
                        </div>
                    @endif

                    @if($showHistory && $recentChanges->isNotEmpty())
                        <table class="table table-sm small mt-3 mb-0">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Reason</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentChanges as $change)
                                    <tr wire:key="po-change-{{ $change->id }}">
                                        <td class="text-nowrap">{{ $change->created_at?->format('d M Y H:i') }}</td>
                                        <td>{{ $change->from_po_number ?: '—' }}</td>
                                        <td>{{ $change->to_po_number ?: '—' }}</td>
                                        <td>{{ $change->reason }}</td>
                                        <td>{{ $change->changer?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if($showChangeModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change PO</h5>
                        <button type="button" class="close" wire:click="closeChangeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Current PO: <strong>{{ $enquiry?->client_po_number ?: 'none' }}</strong>.
                            The reservation moves to the new PO and the change is recorded with your reason.
                        </p>

                        @include('livewire.commercial.partials.enquiry-po-capture', ['poNumberInputId' => 'changePoNumber'])

                        @if($this->poCaptureAcceptsFile)
                            <div class="form-group">
                                <label for="changePoDocument">PO document (optional)</label>
                                <input type="file" id="changePoDocument" class="form-control-file" accept=".pdf,.png,.jpg,.jpeg" wire:model="poDocument">
                                <div wire:loading wire:target="poDocument" class="small text-muted mt-1">Uploading…</div>
                                @error('file')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group mb-0">
                            <label for="changePoReason">Reason <span class="text-danger">*</span></label>
                            <textarea id="changePoReason" class="form-control" rows="3" maxlength="1000" wire:model.defer="changeReason" placeholder="e.g. Customer sent a corrected PO"></textarea>
                            @error('reason')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeChangeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="submitChange" wire:loading.attr="disabled" wire:target="submitChange,poDocument">
                            Change PO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
