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
                                <td class="small">{{ $transferService->formatTransferDestinations($transfer) }}</td>
                                <td class="small">
                                    @if($transfer->submissionFormInstance)
                                        {{ $transfer->submissionFormInstance->form_number ?? $transfer->submissionFormInstance->title }}
                                    @elseif($transfer->sampleHeader)
                                        {{ $transfer->sampleHeader->batch_code }}
                                    @else
                                        —
                                    @endif
                                    @if($transfer->samples->isNotEmpty())
                                        <div class="text-muted">
                                            @foreach($transfer->samples as $transferSample)
                                                <div>
                                                    {{ $transferSample->sampleDetail?->sample_code ?? 'Sample' }}
                                                    →
                                                    {{ $transferSample->toZone?->key ?? $transferSample->toZone?->value ?? '—' }}
                                                </div>
                                            @endforeach
                                        </div>
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
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $this->modalTitle }}</h5>
                        <button type="button" class="close" wire:click="closeModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">{{ $this->modalSummary }}</p>

                        <div class="alert alert-info py-2 small">
                            Assign a destination zone for each sample. Samples may go to different labs in one transfer (full or partial).
                            Use the checkbox below to choose whether the final report is compiled from the parent (origin) zone or the receiving zone(s).
                        </div>

                        @if($this->showsTransferTypeSelector)
                            <div class="form-group">
                                <label class="control-label">Transfer type <span class="text-danger">*</span></label>
                                <select wire:model.live="transferType" class="form-control">
                                    <option value="full">Full — include every sample on this batch</option>
                                    <option value="partial">Partial — only checked samples</option>
                                </select>
                                @error('transferType') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="custom-control custom-checkbox mb-0">
                                <input type="checkbox" class="custom-control-input" wire:model="reportFromParentZone">
                                <span class="custom-control-label">Compile report from parent (origin) zone</span>
                            </label>
                            <small class="text-muted d-block mt-1">When unchecked, reporting follows the destination zone when all samples share one lab; otherwise the origin zone is used.</small>
                        </div>

                        @if($this->showsSampleAssignments)
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label class="control-label small mb-1">Apply zone to samples</label>
                                        <select wire:model="bulkToZoneId" class="form-control form-control-sm">
                                            <option value="">Select zone to apply…</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone['id'] }}">{{ $zone['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex flex-wrap" style="gap: 0.5rem;">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="applyBulkZoneToAll">
                                            Apply to {{ $this->showsSamplePartialCheckboxes ? 'all listed' : 'all' }}
                                        </button>
                                        @if($this->showsSamplePartialCheckboxes)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="applyBulkZoneToIncluded">
                                                Apply to checked
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            @if($this->showsSamplePartialCheckboxes)
                                                <th style="width: 3rem;"></th>
                                            @endif
                                            <th>Sample</th>
                                            @if($transferScope === 'request')
                                                <th>Request / batch</th>
                                            @endif
                                            <th>Current zone</th>
                                            <th>Destination zone <span class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sampleRows as $sample)
                                            @php
                                                $isActive = $this->showsSamplePartialCheckboxes
                                                    ? ($sampleIncluded[$sample['id']] ?? false)
                                                    : true;
                                            @endphp
                                            <tr wire:key="interzone-sample-{{ $sample['id'] }}" class="{{ ! $isActive ? 'text-muted' : '' }}">
                                                @if($this->showsSamplePartialCheckboxes)
                                                    <td class="align-middle">
                                                        <input type="checkbox"
                                                            class="form-check-input m-0"
                                                            wire:model.live="sampleIncluded.{{ $sample['id'] }}">
                                                    </td>
                                                @endif
                                                <td class="align-middle">
                                                    <strong>{{ $sample['sample_code'] }}</strong>
                                                </td>
                                                @if($transferScope === 'request')
                                                    <td class="align-middle small">
                                                        {{ $sample['instance_label'] }}
                                                        <div class="text-muted">{{ $sample['batch_code'] }}</div>
                                                    </td>
                                                @endif
                                                <td class="align-middle small">{{ $sample['current_zone_label'] }}</td>
                                                <td class="align-middle" style="min-width: 14rem;">
                                                    <select
                                                        wire:model="sampleToZone.{{ $sample['id'] }}"
                                                        class="form-control form-control-sm"
                                                        @disabled($this->showsSamplePartialCheckboxes && ! $isActive)
                                                    >
                                                        <option value="">Select zone…</option>
                                                        @foreach($zones as $zone)
                                                            <option value="{{ $zone['id'] }}">{{ $zone['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('sampleToZone.'.$sample['id'])
                                                        <span class="text-danger small d-block">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted small mb-0">No samples found to transfer. Create or receive samples on this request/batch first.</p>
                        @endif

                        <div class="form-group mt-3 mb-0">
                            <label class="control-label">Remarks</label>
                            <textarea wire:model="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="submitTransfer" wire:loading.attr="disabled" @disabled(! $this->showsSampleAssignments)>
                            <span wire:loading wire:target="submitTransfer" class="spinner-border spinner-border-sm mr-1"></span>
                            Confirm transfer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
