<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:32px;height:32px;background:#f3f4f6;flex-shrink:0;">
                <i class="mdi mdi-check-all" style="font-size:1.1rem;color:#4b5563;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Complaint Pending Closure</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Review resolution and close the complaint</small>
            </div>
        </div>
    </div>

    {{-- Closed State --}}
    @if($complaint->complaint_workflow > 4)
        <div class="card bg-light shadow-sm mb-4 border-success">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-check-decagram text-success mb-2" style="font-size: 3rem;"></i>
                <h5 class="text-success font-weight-bold">Complaint Closed</h5>
                <p class="text-muted mb-4">This complaint has been formally closed.</p>

                @if($resolution && $resolution->send_to_customer && $complaint->closure_sent_at)
                    <div class="alert alert-success mx-auto mb-3" style="max-width:600px;">
                        <i class="mdi mdi-email-check-outline mr-1"></i>
                        Closure report emailed to customer on {{ $complaint->closure_sent_at?->format('d M Y, H:i') }}.
                    </div>
                @endif

                @if($client_remarks)
                    <div class="text-left bg-white p-3 rounded border mx-auto" style="max-width: 600px;">
                        <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size:0.75rem;"><i class="mdi mdi-comment-quote-outline mr-1"></i> Internal Remarks:</label>
                        <p class="mb-0 font-italic text-dark">{{ $client_remarks }}</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Pending Closure Form --}}
        <div class="alert alert-info border-info mb-4">
            <i class="mdi mdi-information mr-1"></i>
            This complaint is in <strong>Complaint Pending Closure</strong> stage. Review the resolution summary below, then choose whether to send a copy to the customer before closing.
        </div>

        {{-- Resolution Summary --}}
        @if($resolution)
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="font-weight-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">Resolution Summary</h6>
                </div>
                <div class="card-body">
                    @if($resolution->cause_of_complaint)
                        <div class="mb-3">
                            <label class="font-weight-bold text-dark small text-uppercase">Cause of Complaint</label>
                            <div class="bg-light p-2 rounded border shadow-sm" style="font-size: 0.9rem;">{!! $resolution->cause_of_complaint !!}</div>
                        </div>
                    @endif
                    @if($resolution->action_taken && $resolution->action_taken !== 'See detailed resolution fields.')
                        <div class="mb-3">
                            <label class="font-weight-bold text-dark small text-uppercase">Immediate Action Taken</label>
                            <div class="bg-light p-2 rounded border shadow-sm" style="font-size: 0.9rem;">{!! $resolution->action_taken !!}</div>
                        </div>
                    @endif
                    @if($resolution->corrective_action_taken)
                        <div class="mb-3">
                            <label class="font-weight-bold text-dark small text-uppercase">Corrective Action Taken</label>
                            <div class="bg-light p-2 rounded border shadow-sm" style="font-size: 0.9rem;">{!! $resolution->corrective_action_taken !!}</div>
                        </div>
                    @endif
                    @if($resolution->car_no)
                        <div class="mb-3">
                            <label class="font-weight-bold text-dark small text-uppercase">CAR Number</label>
                            <div><span class="badge badge-primary px-3 py-1">{{ $resolution->car_no }}</span></div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Send to Customer Toggle --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="font-weight-bold text-dark mb-1">
                            <i class="mdi mdi-email-send-outline mr-2 text-primary"></i>
                            Send Closure Report to Customer?
                        </h6>
                        <small class="text-muted">If enabled, the ISO closure report will be emailed to the selected contacts when the complaint is closed.</small>
                    </div>
                    <div class="custom-control custom-switch ml-3">
                        <input type="checkbox" class="custom-control-input" id="send_to_customer_toggle"
                            wire:model="send_to_customer"
                            wire:change="saveSettings">
                        <label class="custom-control-label font-weight-bold" for="send_to_customer_toggle">
                            {{ $send_to_customer ? 'Yes — Send Report' : 'No — Close Directly' }}
                        </label>
                    </div>
                </div>

                {{-- Contact Selector (visible only when send = true) --}}
                @if($send_to_customer && count($closureContacts) > 0)
                    <div class="mt-4 pt-3 border-top">
                        <label class="font-weight-bold text-dark small text-uppercase mb-2">
                            <i class="mdi mdi-account-multiple-outline mr-1"></i>
                            Select Recipients
                            <span class="text-muted font-weight-normal ml-1">(Hold Ctrl for multiple)</span>
                        </label>
                        <div wire:ignore>
                            <select multiple id="closure_recipients_select" class="form-control @error('selectedContactIds') is-invalid @enderror" style="height: 120px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: inset 0 1px 2px rgba(0,0,0,0.075);">
                                @foreach($closureContacts as $contact)
                                    <option value="{{ $contact['id'] }}" wire:key="contact-{{ $contact['id'] }}">
                                        {{ $contact['first_name'] }} {{ $contact['last_name'] ?? '' }}
                                        @if(!empty($contact['email'])) — {{ $contact['email'] }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            {{ count($closureContacts) }} eligible contact(s) listed.
                        </small>
                    </div>
                @elseif($send_to_customer && count($closureContacts) === 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="mdi mdi-alert-outline mr-1"></i>
                        No contacts with report-receiving permission found for this client. The complaint can still be closed without sending.
                    </div>
                @endif
            </div>
        </div>

        {{-- Internal Remarks (optional) --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <label class="font-weight-bold text-dark mb-2">
                    <i class="mdi mdi-note-text-outline mr-1"></i>
                    Internal Closure Remarks
                    <small class="text-muted font-weight-normal ml-1">(optional)</small>
                </label>
                <textarea
                    class="form-control bg-light border-0"
                    wire:model.debounce.500ms="client_remarks"
                    wire:change="saveSettings"
                    rows="3"
                    placeholder="Any internal notes before closing..."
                    style="border-radius: 8px;"></textarea>
            </div>
        </div>

        {{-- Close Complaint Button --}}
        <div class="d-flex justify-content-end">
            <button
                type="button"
                class="btn btn-success px-5 font-weight-bold shadow rounded-pill"
                wire:click="initiateClose"
                wire:loading.attr="disabled"
                wire:target="initiateClose"
            >
                <span wire:loading.remove wire:target="initiateClose">
                    <i class="mdi mdi-lock-check mr-1"></i>
                    {{ $send_to_customer ? 'Send Report & Close Complaint' : 'Close Complaint' }}
                </span>
                <span wire:loading wire:target="initiateClose">
                    <i class="mdi mdi-loading mdi-spin mr-1"></i> Processing...
                </span>
            </button>
        </div>
    @endif

    @script
    <script>
        $wire.on('show-report-closure', () => {
            // This can be used for any other tab-specific reset if needed
        });
    </script>
    @endscript
</div>
