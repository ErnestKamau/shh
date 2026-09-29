@if(($showApproveQuotationModal ?? false) || ($showSendQuotationModal ?? false))
    @php
        $isSendOnlyModal = (bool) ($showSendQuotationModal ?? false) && ! (bool) ($showApproveQuotationModal ?? false);
        $modalCloseMethod = $isSendOnlyModal ? 'closeSendApprovedQuotationModal' : 'closeApproveQuotationModal';
        $modalConfirmMethod = $isSendOnlyModal ? 'confirmSendApprovedQuotationToCustomer' : 'confirmApproveQuotationAndSend';
    @endphp
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto; z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($isSendOnlyModal)
                            Send quotation {{ $quotationHeader->quote_number ?? '' }}?
                        @else
                            Approve quotation {{ $quotationHeader->quote_number ?? '' }}?
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="{{ $modalCloseMethod }}" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" style="color:#334155;">
                        @if($isSendOnlyModal)
                            Choose which CRM contacts receive this quotation. Portal delivery is used automatically for portal-capable contacts.
                            Email is optional and sends the PDF attachment only (no Accept/View links — acceptance is via portal or LIMS).
                        @else
                            Approving marks this quotation as fit to send and sends it to the selected contacts.
                            Portal delivery is automatic when eligible; email is optional (PDF attachment only).
                        @endif
                    </p>

                    <h6 class="font-weight-bold mb-2" style="color:#1e3a8a;">Who will receive this quotation</h6>
                    <p class="small text-muted mb-2">
                        Contacts with “Receive quotations” are pre-selected. Company unit comes from CRM.
                    </p>

                    @if(count($approveRecipientOptions) === 0)
                        <div class="alert alert-warning py-2 small mb-3">
                            No customer contacts found. The primary enquiry contact will be used if available.
                        </div>
                    @else
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 2.5rem;"></th>
                                        <th>Contact</th>
                                        <th>Company unit</th>
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
                                                @if(!empty($recipient['receive_quotations']))
                                                    <span class="badge badge-light border small">Receives quotations</span>
                                                @endif
                                            </td>
                                            <td>{{ $recipient['company_unit'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="approve-send-email" wire:model="approveSendEmail">
                        <label class="form-check-label" for="approve-send-email">
                            Email quotation PDF to selected contacts (optional)
                        </label>
                    </div>

                    @unless($isSendOnlyModal)
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold" for="approve-quotation-comments">Comments (optional)</label>
                            <textarea id="approve-quotation-comments"
                                class="form-control form-control-sm"
                                rows="2"
                                wire:model="approvalDecisionComments"
                                placeholder="Optional note for the requester"></textarea>
                        </div>
                    @endunless
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="{{ $modalCloseMethod }}">Cancel</button>
                    <button type="button"
                        class="btn btn-success"
                        wire:click="{{ $modalConfirmMethod }}"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="{{ $modalConfirmMethod }}">
                            <i class="mdi {{ $isSendOnlyModal ? 'mdi-send' : 'mdi-check-decagram' }}"></i>
                            {{ $isSendOnlyModal ? 'Send to customer' : 'Approve and send' }}
                        </span>
                        <span wire:loading wire:target="{{ $modalConfirmMethod }}">
                            {{ $isSendOnlyModal ? 'Sending…' : 'Approving…' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
