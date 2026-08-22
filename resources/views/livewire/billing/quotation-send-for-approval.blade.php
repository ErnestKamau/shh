<div>
    @if($showModal)
        <div class="modal fade show d-block ls-quotation-workflow-modal" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-share-circle"></i> Send for Approval
                        </h5>
                        <button type="button" class="close" wire:click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Send
                            @if($quoteNumber !== '')
                                quotation <strong>{{ $quoteNumber }}</strong>
                            @else
                                this quotation
                            @endif
                            for approval?
                        </p>
                        <p class="text-muted small mb-3">
                            Eligible approvers come from Billing → Approval configuration.
                            Email notifies them with the PDF only — approval must be completed in LIMS.
                        </p>

                        <h6 class="font-weight-bold mb-2">Personnel who can approve</h6>
                        <ul class="list-unstyled small mb-3 border rounded px-3 py-2" style="max-height: 160px; overflow-y: auto;">
                            @forelse($approverOptions as $manager)
                                <li class="py-1 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    <strong>{{ $manager['name'] }}</strong>
                                    @if(($manager['email'] ?? '') !== '')
                                        <span class="text-muted"> · {{ $manager['email'] }}</span>
                                    @endif
                                </li>
                            @empty
                                <li class="text-muted">No approvers configured.</li>
                            @endforelse
                        </ul>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="billing-approval-notify-email" wire:model="notifyEmail">
                            <label class="form-check-label" for="billing-approval-notify-email">
                                Email all listed personnel
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="billing-approval-notify-app" wire:model="notifyInApp">
                            <label class="form-check-label" for="billing-approval-notify-app">
                                Notify in app (bell) for all listed personnel
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button"
                            class="btn btn-sm btn-quotation-primary"
                            wire:click="confirm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirm">
                                <i class="mdi mdi-share-circle"></i> Confirm
                            </span>
                            <span wire:loading wire:target="confirm">Sending…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
