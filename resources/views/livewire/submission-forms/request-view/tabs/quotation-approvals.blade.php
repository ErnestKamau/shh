<div class="p-3">
    @if($quotationHeader === null)
        <p class="text-muted mb-0">No quotation is linked to this request yet. Build one from Process Enquiry.</p>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3" style="gap: 12px;">
            <div>
                <h6 class="mb-1">Quotation {{ $quotationHeader->quote_number }}</h6>
                @if((int) $quotationHeader->is_approved === 1)
                    <span class="badge badge-success">
                        @if(($quotationApproverName ?? '') !== '')
                            Reviewed By: {{ $quotationApproverName }}
                        @else
                            Approved
                        @endif
                    </span>
                @elseif($quotationPendingApproval)
                    <span class="badge badge-warning">Pending approval</span>
                    @if($quotationHeader->approvedByUser)
                        <div class="small text-muted mt-1">
                            Assigned approver: {{ $quotationHeader->approvedByUser->name }}
                        </div>
                    @endif
                @endif
            </div>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <a href="{{ route('quotation.preview', ['id' => $quotationHeader->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-file-eye-outline"></i> Preview
                </a>
                @if(! empty($quotationHeader->upload_url))
                    <a href="{{ route('quotation.preview.pdf', ['id' => $quotationHeader->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="mdi mdi-file-pdf-box"></i> PDF
                    </a>
                @endif
            </div>
        </div>

        @if($canApproveQuotation)
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold" for="quotation-approval-comments">Comments</label>
                    <textarea id="quotation-approval-comments" class="form-control form-control-sm" rows="3" wire:model="approvalDecisionComments" placeholder="Required when rejecting"></textarea>
                    @error('approvalDecisionComments') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <button
                        type="button"
                        class="btn btn-sm btn-success"
                        wire:click="approveEnquiryQuotation"
                        wire:loading.attr="disabled"
                        wire:target="approveEnquiryQuotation,rejectEnquiryQuotation"
                    >
                        <span wire:loading.remove wire:target="approveEnquiryQuotation">
                            <i class="mdi mdi-check-decagram"></i> Approve quotation
                        </span>
                        <span wire:loading wire:target="approveEnquiryQuotation">
                            <i class="mdi mdi-loading mdi-spin"></i> Approving…
                        </span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        wire:click="rejectEnquiryQuotation"
                        wire:loading.attr="disabled"
                        wire:target="approveEnquiryQuotation,rejectEnquiryQuotation"
                    >
                        <span wire:loading.remove wire:target="rejectEnquiryQuotation">
                            <i class="mdi mdi-close-circle-outline"></i> Reject &amp; return
                        </span>
                        <span wire:loading wire:target="rejectEnquiryQuotation">
                            <i class="mdi mdi-loading mdi-spin"></i> Rejecting…
                        </span>
                    </button>
                </div>
            </div>
        @elseif($quotationPendingApproval)
            <div class="alert alert-warning py-2 small">
                Waiting for the assigned lab manager to approve this quotation.
            </div>
        @endif

        @if($quotationApprovedReadyToSend)
            <div class="border rounded p-3 mb-3">
                <h6 class="mb-2">Send to customer</h6>
                <div class="d-flex flex-wrap mb-2" style="gap: 1rem;">
                    @if(strtolower((string) ($commercialEnquiry?->source_channel ?? '')) === 'portal')
                        <label class="small mb-0 d-flex align-items-center">
                            <input type="checkbox" class="mr-2" wire:model="sendPortal"> Send to portal
                        </label>
                    @endif
                    <label class="small mb-0 d-flex align-items-center">
                        <input type="checkbox" class="mr-2" wire:model="sendEmail"> Email PDF to customer
                    </label>
                </div>
                <button type="button" class="btn btn-sm btn-primary" wire:click="sendApprovedQuotationToCustomer" wire:loading.attr="disabled">
                    <i class="mdi mdi-send"></i> Send to customer
                </button>
            </div>
        @endif
    @endif
</div>
