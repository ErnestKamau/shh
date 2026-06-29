<div>
    @teleport('body')
    <div class="modal fade" id="feedbackCorrectiveActionModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="mdi mdi-clipboard-check-outline text-primary mr-1"></i>
                            Feedback Corrective Action
                        </h5>
                        @if($feedback)
                            <small class="text-muted">
                                {{ $reference_no ?: 'Draft' }} | {{ $feedback->code ?? ('FB' . str_pad($feedback->id, 4, '0', STR_PAD_LEFT)) }}
                                | {{ $feedback->customer->name ?? $feedback->received_from }}
                            </small>
                        @endif
                    </div>
                    <button type="button" class="close" wire:click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($feedback)
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="small text-muted text-uppercase font-weight-bold">Status</label>
                                <select class="form-control" wire:model="status">
                                    @foreach(\App\Models\CRM\FeedbackCorrectiveAction::statuses() as $statusOption)
                                        <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase font-weight-bold">Assigned To</label>
                                <select class="form-control" wire:model="assigned_to">
                                    <option value="">Select user</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted text-uppercase font-weight-bold">Feedback Context</label>
                                <div class="border rounded bg-light px-3 py-2 small">
                                    <div><strong>Service Ref:</strong> {{ $feedback->service_reference_no ?: 'N/A' }}</div>
                                    <div><strong>Overall Rating:</strong> {{ $feedback->rating_overall ?: 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small text-muted text-uppercase font-weight-bold">Summary Notes</label>
                            <textarea class="form-control" rows="3" wire:model="summary_notes" placeholder="Summarize the corrective action handling for this feedback."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-uppercase font-weight-bold text-muted">Issue And Action Items</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addItem">
                                <i class="mdi mdi-plus"></i> Add Item
                            </button>
                        </div>

                        @foreach($items as $index => $item)
                            <div class="border rounded p-3 mb-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="badge {{ ($item['source_type'] ?? '') === \App\Models\CRM\FeedbackCorrectiveActionItem::SOURCE_LOW_RATING ? 'badge-warning' : 'badge-danger' }}">
                                            {{ ($item['source_type'] ?? '') === \App\Models\CRM\FeedbackCorrectiveActionItem::SOURCE_LOW_RATING ? 'Low Rating Trigger' : 'Reported Issue' }}
                                        </span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeItem({{ $index }})">
                                        <i class="mdi mdi-delete-outline"></i>
                                    </button>
                                </div>

                                <input type="hidden" wire:model="items.{{ $index }}.id">
                                <input type="hidden" wire:model="items.{{ $index }}.source_type">
                                <input type="hidden" wire:model="items.{{ $index }}.evaluation_metric_id">

                                <div class="form-group">
                                    <label class="small text-muted text-uppercase font-weight-bold">Issue Summary</label>
                                    <textarea class="form-control" rows="2" wire:model="items.{{ $index }}.issue_summary"></textarea>
                                    @error('items.' . $index . '.issue_summary') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small text-muted text-uppercase font-weight-bold">Root Cause</label>
                                            <textarea class="form-control" rows="3" wire:model="items.{{ $index }}.root_cause"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small text-muted text-uppercase font-weight-bold">Corrective Action Plan</label>
                                            <textarea class="form-control" rows="3" wire:model="items.{{ $index }}.corrective_action_plan"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="small text-muted text-uppercase font-weight-bold">Responsible User</label>
                                            <select class="form-control" wire:model="items.{{ $index }}.responsible_user_id">
                                                <option value="">Select user</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="small text-muted text-uppercase font-weight-bold">Target Date</label>
                                            <input type="date" class="form-control" wire:model="items.{{ $index }}.target_date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="small text-muted text-uppercase font-weight-bold">Completion Date</label>
                                            <input type="date" class="form-control" wire:model="items.{{ $index }}.completion_date">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small text-muted text-uppercase font-weight-bold">Effectiveness Notes</label>
                                    <textarea class="form-control" rows="2" wire:model="items.{{ $index }}.effectiveness_notes"></textarea>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center p-5">
                            <i class="mdi mdi-loading mdi-spin display-4 text-primary"></i>
                            <p class="mt-3 text-muted">Loading corrective action...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="close">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save-outline mr-1"></i> Save Corrective Action</span>
                        <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-feedback-corrective-action-modal', () => {
                $('#feedbackCorrectiveActionModal').modal('show');
            });

            Livewire.on('hide-feedback-corrective-action-modal', () => {
                $('#feedbackCorrectiveActionModal').modal('hide');
            });
        });
    </script>
</div>
