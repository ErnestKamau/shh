<div>
    @if($workflowStatus === 'Samples Receiving' && $workflowSubTab === 'interzone_transfers')
        <div class="interzone-transfers-panel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="mb-1">Interzone transfers</h6>
                    <p class="text-muted small mb-0">History of request- and batch-level zone movements.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover workflow-table mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Request / batch</th>
                            <th>Report zone</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr wire:key="interzone-transfer-{{ $transfer->id }}">
                                <td class="small text-nowrap">{{ $transfer->transferred_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-light text-dark">
                                        {{ ucfirst($transfer->transfer_mode) }} / {{ ucfirst($transfer->transfer_scope) }}
                                    </span>
                                </td>
                                <td class="small">{{ $transfer->fromZone?->key ?? $transfer->fromZone?->value ?? '—' }}</td>
                                <td class="small">{{ $transfer->toZone?->key ?? $transfer->toZone?->value ?? '—' }}</td>
                                <td class="small">
                                    @if($transfer->submissionFormInstance)
                                        {{ $transfer->submissionFormInstance->form_number ?? $transfer->submissionFormInstance->title }}
                                    @elseif($transfer->sampleHeader)
                                        {{ $transfer->sampleHeader->batch_code }}
                                    @else
                                        —
                                    @endif
                                    @if($transfer->transfer_mode === \App\Models\Sampleworkflow\InterzoneTransfer::MODE_PARTIAL && $transfer->samples->isNotEmpty())
                                        <div class="text-muted">({{ $transfer->samples->count() }} sample(s))</div>
                                    @endif
                                </td>
                                <td class="small">{{ $transfer->report_from_parent_zone ? 'Parent zone' : 'Transferred zone' }}</td>
                                <td class="small">{{ $transfer->initiatedByUser?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No interzone transfers recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape.window="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $this->modalTitle }}</h5>
                        <button type="button" class="close" wire:click="closeModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            Select the destination zone. Use the checkbox to choose whether the final report is compiled from the parent (origin) zone or the zone receiving the work.
                        </div>

                        <div class="form-group">
                            <label class="control-label">Destination zone <span class="text-danger">*</span></label>
                            <select wire:model="toZoneId" class="form-control">
                                <option value="">Select zone…</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone['id'] }}">{{ $zone['label'] }}</option>
                                @endforeach
                            </select>
                            @error('toZoneId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="custom-control custom-checkbox mb-0">
                                <input type="checkbox" class="custom-control-input" wire:model="reportFromParentZone">
                                <span class="custom-control-label">Compile report from parent (origin) zone</span>
                            </label>
                            <small class="text-muted d-block mt-1">When unchecked, the report will be compiled from the transferred zone.</small>
                        </div>

                        @if($modalMode === 'partial_batch')
                            <div class="form-group mb-0">
                                <label class="control-label">Samples to transfer <span class="text-danger">*</span></label>
                                @forelse($batchSampleOptions as $sample)
                                    <label class="custom-control custom-checkbox d-block">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            wire:model="selectedSampleDetailIds"
                                            value="{{ $sample['id'] }}">
                                        <span class="custom-control-label">{{ $sample['sample_code'] }}</span>
                                    </label>
                                @empty
                                    <p class="text-muted small mb-0">No samples found on this batch.</p>
                                @endforelse
                                @error('selectedSampleDetailIds') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="form-group mt-3 mb-0">
                            <label class="control-label">Remarks</label>
                            <textarea wire:model="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="submitTransfer" wire:loading.attr="disabled">
                            <span wire:loading wire:target="submitTransfer" class="spinner-border spinner-border-sm mr-1"></span>
                            Confirm transfer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
