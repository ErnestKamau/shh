<div class="container-fluid">
    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-1">
                                <i class="mdi mdi-clipboard-check-outline text-primary"></i>
                                Approval Checklist
                            </h3>
                            <p class="text-muted mb-0">Sample: <strong>{{ $sampleId }}</strong> | Stage: <strong>{{ $stageName }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @forelse ($approvals as $approval)
        @php
            $completedLog = $approval->approvalLogs->first();
            $isCompleted = $completedLog !== null;
        @endphp
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-1">{{ $approval->name }}</h5>
                    <small class="text-muted">{{ $approval->code }}</small>
                </div>
                @if ($isCompleted)
                    <span class="badge badge-{{ $completedLog->status === 'approved' ? 'success' : 'danger' }} p-2">
                        {{ ucfirst($completedLog->status) }}
                    </span>
                @else
                    <span class="badge badge-info p-2">Pending</span>
                @endif
            </div>
            <div class="card-body">
                @if ($isCompleted)
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div>
                                <strong>Completed by:</strong>
                                {{ $completedLog->approvedByUser->name ?? 'System' }}
                            </div>
                            <div>
                                <strong>Completed at:</strong>
                                {{ optional($completedLog->approved_at)->format('Y-m-d H:i') }}
                            </div>
                        </div>
                        @if ($completedLog->remarks)
                            <div class="mt-2"><strong>Remarks:</strong> {{ $completedLog->remarks }}</div>
                        @endif
                    </div>
                @endif

                <div class="row">
                    @foreach ($approval->checklistItems as $item)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100 {{ $isCompleted ? 'bg-light' : '' }}">
                                <label class="font-weight-bold d-block">
                                    {{ $item->label }}
                                    @if ($item->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($item->type === 'checkbox')
                                    <div class="form-check mt-2">
                                        <input type="checkbox"
                                            wire:model="responses.{{ $item->id }}"
                                            class="form-check-input @error('responses.' . $item->id) is-invalid @enderror"
                                            id="item-{{ $item->id }}"
                                            {{ $isCompleted ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="item-{{ $item->id }}">Completed</label>
                                        @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                @elseif ($item->type === 'select')
                                    <select wire:model="responses.{{ $item->id }}"
                                        class="form-control mt-2 @error('responses.' . $item->id) is-invalid @enderror"
                                        {{ $isCompleted ? 'disabled' : '' }}>
                                        <option value="">Select option</option>
                                        @foreach (($item->options ?? []) as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @else
                                    <input type="text"
                                        wire:model="responses.{{ $item->id }}"
                                        class="form-control mt-2 @error('responses.' . $item->id) is-invalid @enderror"
                                        {{ $isCompleted ? 'disabled' : '' }}>
                                    @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <label for="remarks-{{ $approval->id }}" class="font-weight-bold">Remarks</label>
                    <textarea id="remarks-{{ $approval->id }}"
                        wire:model="remarks.{{ $approval->id }}"
                        rows="3"
                        class="form-control"
                        placeholder="Add optional remarks"
                        {{ $isCompleted ? 'disabled' : '' }}></textarea>
                </div>

                <div class="d-flex justify-content-end mt-3" style="gap: 0.75rem;">
                    <button type="button"
                        wire:click="submitApproval('{{ $approval->id }}', 'rejected')"
                        class="btn btn-outline-danger"
                        {{ $isCompleted ? 'disabled' : '' }}>
                        <i class="mdi mdi-close-circle-outline"></i> Reject
                    </button>
                    <button type="button"
                        wire:click="submitApproval('{{ $approval->id }}', 'approved')"
                        class="btn btn-success"
                        {{ $isCompleted ? 'disabled' : '' }}>
                        <i class="mdi mdi-check-circle-outline"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="mdi mdi-information-outline d-block mb-2" style="font-size: 3rem;"></i>
                No active approvals are configured for this workflow stage.
            </div>
        </div>
    @endforelse

    @if ($auditTrail->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Approval Audit Trail</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Approval</th>
                                <th>Status</th>
                                <th>By</th>
                                <th>At</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditTrail as $log)
                                <tr>
                                    <td>{{ $log->approval->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $log->status === 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->approvedByUser->name ?? 'System' }}</td>
                                    <td>{{ optional($log->approved_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $log->remarks ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>