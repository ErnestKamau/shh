<div class="container-fluid checklist-modal-bg py-2">
<style>
    .checklist-modal-bg {
        background-color: #f8fafc;
        border-radius: 8px;
    }
    .checklist-hero {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 20px;
    }
    .checklist-hero-icon {
        background: #fff;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        color: #16a34a;
        font-size: 1.8rem;
    }
    .checklist-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
        background: #fff;
        overflow: hidden;
    }
    .checklist-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
    }
    .checklist-card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 20px;
    }
    .item-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .item-box.completed-box {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .item-box:hover:not(.completed-box) {
        border-color: #94a3b8;
    }
    .badge-modern {
        border-radius: 20px;
        padding: 5px 12px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }
    .badge-modern-pending {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    .badge-modern-success {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }
    .badge-modern-danger {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }
    .custom-control-modern .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
        margin-top: 0.1rem;
        cursor: pointer;
    }
    .custom-control-modern .form-check-label {
        margin-left: 0.4rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
    }
</style>
    @if (session()->has('success'))
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 8px;"><i class="mdi mdi-check-circle mr-2"></i>{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 8px;"><i class="mdi mdi-alert-circle mr-2"></i>{{ session('error') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="checklist-hero d-flex align-items-center gap-3" style="gap: 15px;">
                <div class="checklist-hero-icon">
                    <i class="mdi mdi-format-list-checks"></i>
                </div>
                <div>
                    <h4 class="mb-1 text-dark" style="font-weight: 700;">Approval Checklist</h4>
                    <p class="mb-0" style="color: #166534; font-size: 0.9rem;">
                        Sample: <strong class="text-dark">{{ $batchCode }}</strong> <span class="mx-2 text-muted">|</span> Stage: <strong class="text-dark">{{ $stageName }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @forelse ($approvals as $approval)
        @php
            $completedLog = $approval->approvalLogs->first();
            $isCompleted = $completedLog !== null;
        @endphp
        <div class="checklist-card mb-4">
            <div class="checklist-card-header d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-1 text-dark" style="font-weight: 600;">{{ $approval->name }}</h5>
                    <small class="text-muted" style="font-weight: 500;">{{ $approval->code }}</small>
                </div>
                @if ($isCompleted)
                    <span class="badge-modern badge-modern-{{ $completedLog->status === 'approved' ? 'success' : 'danger' }}">
                        <i class="mdi {{ $completedLog->status === 'approved' ? 'mdi-check-circle' : 'mdi-close-circle' }} mr-1"></i>
                        {{ ucfirst($completedLog->status) }}
                    </span>
                @else
                    <span class="badge-modern badge-modern-pending"><i class="mdi mdi-clock-outline mr-1"></i> Pending</span>
                @endif
            </div>
            <div class="card-body p-4">
                @if ($isCompleted)
                    <div class="alert mb-4" style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                        <div class="d-flex justify-content-between flex-wrap text-muted small mb-2">
                            <div>
                                <i class="mdi mdi-account mr-1"></i> <strong>Completed by:</strong>
                                <span class="text-dark">{{ $completedLog->approvedByUser->name ?? 'System' }}</span>
                            </div>
                            <div>
                                <i class="mdi mdi-calendar-clock mr-1"></i> <strong>Completed at:</strong>
                                <span class="text-dark">{{ optional($completedLog->approved_at)->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                        @if ($completedLog->remarks)
                            <div class="small text-muted mt-2 pt-2 border-top">
                                <strong>Remarks:</strong> <span class="text-dark">{{ $completedLog->remarks }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="row">
                    @foreach ($approval->checklistItems as $item)
                        <div class="col-md-6 mb-3">
                            <div class="item-box p-3 h-100 {{ $isCompleted ? 'completed-box' : '' }}">
                                <label class="d-block text-dark" style="font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">
                                    {{ $item->label }}
                                    @if ($item->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($item->type === 'checkbox')
                                    <div class="form-check custom-control-modern">
                                        <input type="checkbox"
                                            wire:model="responses.{{ $item->id }}"
                                            class="form-check-input shadow-sm @error('responses.' . $item->id) is-invalid @enderror"
                                            id="item-{{ $item->id }}"
                                            {{ $isCompleted ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="item-{{ $item->id }}">Confirmed / Completed</label>
                                        @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                @elseif ($item->type === 'select')
                                    <select wire:model="responses.{{ $item->id }}"
                                        class="form-control form-control-sm shadow-sm @error('responses.' . $item->id) is-invalid @enderror"
                                        style="border-radius: 6px; background-color: #f8fafc;"
                                        {{ $isCompleted ? 'disabled' : '' }}>
                                        <option value="">-- Select Option --</option>
                                        @foreach (($item->options ?? []) as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @else
                                    <input type="text"
                                        wire:model="responses.{{ $item->id }}"
                                        class="form-control form-control-sm shadow-sm @error('responses.' . $item->id) is-invalid @enderror"
                                        style="border-radius: 6px; background-color: #f8fafc;"
                                        {{ $isCompleted ? 'disabled' : '' }}>
                                    @error('responses.' . $item->id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group mt-2">
                    <label for="remarks-{{ $approval->id }}" class="text-dark" style="font-weight: 600; font-size: 0.9rem;">Remarks / Comments</label>
                    <textarea id="remarks-{{ $approval->id }}"
                        wire:model="remarks.{{ $approval->id }}"
                        rows="2"
                        class="form-control shadow-sm"
                        style="border-radius: 8px; background-color: #f8fafc;"
                        placeholder="Add any optional remarks or findings..."
                        {{ $isCompleted ? 'disabled' : '' }}></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top" style="gap: 10px;">
                    <button type="button"
                        wire:click="submitApproval('{{ $approval->id }}', 'rejected')"
                        class="btn btn-outline-danger btn-sm px-3 shadow-sm"
                        style="border-radius: 6px; font-weight: 500;"
                        {{ $isCompleted ? 'disabled' : '' }}>
                        <i class="mdi mdi-close-circle-outline"></i> Reject
                    </button>
                    <button type="button"
                        wire:click="submitApproval('{{ $approval->id }}', 'approved')"
                        class="btn btn-success btn-sm px-4 shadow-sm"
                        style="border-radius: 6px; font-weight: 500;"
                        {{ $isCompleted ? 'disabled' : '' }}>
                        <i class="mdi mdi-check-circle-outline"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="checklist-card">
            <div class="card-body text-center py-5">
                <div style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                    <i class="mdi mdi-information-outline text-muted" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="text-dark" style="font-weight: 600;">No Approvals Required</h5>
                <p class="text-muted">No active approvals are configured for this workflow stage.</p>
            </div>
        </div>
    @endforelse

    @if ($auditTrail->isNotEmpty())
        <div class="checklist-card mt-4">
            <div class="checklist-card-header">
                <h6 class="mb-0 text-dark" style="font-weight: 600;"><i class="mdi mdi-history mr-2"></i>Approval Audit Trail</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th class="border-top-0 text-muted" style="font-weight: 600;">Approval</th>
                                <th class="border-top-0 text-muted" style="font-weight: 600;">Status</th>
                                <th class="border-top-0 text-muted" style="font-weight: 600;">By</th>
                                <th class="border-top-0 text-muted" style="font-weight: 600;">At</th>
                                <th class="border-top-0 text-muted" style="font-weight: 600;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditTrail as $log)
                                <tr>
                                    <td class="align-middle text-dark font-weight-bold">{{ $log->approval->name ?? '-' }}</td>
                                    <td class="align-middle">
                                        <span class="badge-modern badge-modern-{{ $log->status === 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-muted">{{ $log->approvedByUser->name ?? 'System' }}</td>
                                    <td class="align-middle text-muted">{{ optional($log->approved_at)->format('Y-m-d H:i') }}</td>
                                    <td class="align-middle text-muted">{{ $log->remarks ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>