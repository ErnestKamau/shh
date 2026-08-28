<div>
    @if($canApprove)
        <div class="d-inline-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button"
                class="btn btn-sm btn-success"
                wire:click="openApproveAndSendModal"
                title="Approve this quotation">
                <i class="mdi mdi-check-decagram"></i> Approve
            </button>
            <button type="button"
                class="btn btn-sm btn-outline-danger"
                wire:click="openRejectModal"
                title="Reject and return to preparation">
                <i class="mdi mdi-close-octagon-outline"></i> Reject
            </button>
        </div>
    @endif

    @if($showApproveAndSendModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto; z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Approve
                            @if($quoteNumber !== '')
                                {{ $quoteNumber }}
                            @else
                                quotation
                            @endif
                        </h5>
                        <button type="button" class="close" wire:click="closeApproveAndSendModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3" style="color:#334155;">
                            Approve marks this quotation complete. You can also send it to CRM contacts now, or approve only and send later.
                        </p>
                        <h6 class="font-weight-bold mb-2" style="color:#1e3a8a;">Who will receive this quotation</h6>
                        <p class="small text-muted mb-2">
                            Contacts with “Receive quotations” are pre-selected. Used when you choose Approve and send.
                        </p>
                        @if(count($approveRecipientOptions) === 0)
                            <div class="alert alert-warning py-2 small mb-3">
                                No customer contacts found for this quotation’s customer.
                            </div>
                        @else
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 2.5rem;"></th>
                                            <th>Contact</th>
                                            <th>Company unit</th>
                                            <th>Sampling location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approveRecipientOptions as $recipient)
                                            @php $checked = in_array((string) $recipient['id'], $approveRecipientContactIds, true); @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        @checked($checked)
                                                        wire:click="toggleApproveRecipient('{{ $recipient['id'] }}')">
                                                </td>
                                                <td>
                                                    <strong>{{ $recipient['name'] }}</strong>
                                                    <div class="small text-muted">{{ $recipient['email'] }}</div>
                                                    @if(!empty($recipient['receive_quotations']))
                                                        <span class="badge badge-light border small">Receives quotations</span>
                                                    @endif
                                                </td>
                                                <td>{{ $recipient['company_unit'] }}</td>
                                                <td>{{ $recipient['sampling_location'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="bq-detail-approve-send-email" wire:model="approveSendEmail">
                            <label class="form-check-label" for="bq-detail-approve-send-email">
                                Email quotation PDF to selected contacts (optional)
                            </label>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold" for="bq-detail-approve-comments">Comments (optional)</label>
                            <textarea id="bq-detail-approve-comments"
                                class="form-control form-control-sm"
                                rows="2"
                                wire:model="approvalDecisionComments"
                                placeholder="Optional note for the requester"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex flex-wrap justify-content-between" style="gap: 8px;">
                        <button type="button" class="btn btn-light" wire:click="closeApproveAndSendModal">Cancel</button>
                        <div class="d-flex flex-wrap" style="gap: 8px;">
                            <button type="button" class="btn btn-outline-success" wire:click="confirmApproveOnly" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="confirmApproveOnly">
                                    <i class="mdi mdi-check"></i> Approve only
                                </span>
                                <span wire:loading wire:target="confirmApproveOnly">Approving…</span>
                            </button>
                            <button type="button" class="btn btn-success" wire:click="confirmApproveAndSend" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="confirmApproveAndSend">
                                    <i class="mdi mdi-check-decagram"></i> Approve and send
                                </span>
                                <span wire:loading wire:target="confirmApproveAndSend">Approving…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showRejectModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject quotation</h5>
                        <button type="button" class="close" wire:click="closeRejectModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Provide a reason for returning this quotation to preparation.</p>
                        <textarea class="form-control"
                            rows="4"
                            wire:model="approvalDecisionComments"
                            placeholder="Rejection reason (required)"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeRejectModal">Cancel</button>
                        <button type="button" class="btn btn-outline-danger" wire:click="confirmReject" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmReject">Confirm reject</span>
                            <span wire:loading wire:target="confirmReject">Rejecting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
