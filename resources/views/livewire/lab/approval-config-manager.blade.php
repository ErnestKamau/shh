<div class="container-fluid">
    <style>
        .acm-card {
            border-radius: 18px;
            border: 1px solid #e8edf3;
            box-shadow: 0 10px 28px rgba(17, 24, 39, 0.05);
        }

        .acm-card .card-header {
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
        }

        .acm-widget {
            border-radius: 14px;
            border: 1px solid #e8edf3;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            overflow: hidden;
        }

        .acm-btn {
            border-radius: 999px;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .acm-list-item {
            border-radius: 12px;
            margin: 0;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
        }

        .acm-list-item + .acm-list-item {
            margin-top: 0.45rem;
        }

        .acm-list-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(17, 24, 39, 0.06);
        }

        .acm-list-item.active,
        .acm-list-item.active:hover,
        .acm-list-item.active:focus {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e3a8a;
            box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.45);
        }

        .acm-list-item.active .badge {
            filter: saturate(0.86);
        }

        .acm-active-subtext {
            color: #475569 !important;
        }

        .acm-table-wrap {
            border: 1px solid #e8edf3;
            border-radius: 14px;
            overflow: hidden;
        }

        .acm-stage-select {
            border: 2px solid #e9ecef !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #495057 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            min-width: 280px !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: left center, right 12px center !important;
            background-repeat: no-repeat, no-repeat !important;
            background-size: 100% 100%, 16px 16px !important;
            padding-right: 40px !important;
            background-color: #ffffff !important;
        }

        .acm-stage-select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            background-color: #ffffff !important;
            outline: none !important;
        }

        .acm-stage-select:hover {
            border-color: #007bff !important;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15) !important;
        }

        .acm-stage-select option {
            padding: 10px 16px;
            font-weight: 500;
            color: #495057;
        }



        .acm-approvals-card {
            overflow: hidden;
        }

        .acm-approvals-body {
            max-height: 560px;
            overflow-y: auto;
            padding: 0.55rem;
        }

        .acm-approvals-body::-webkit-scrollbar {
            width: 8px;
        }

        .acm-approvals-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .acm-actions-dropdown .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .acm-top-btn {
            border-radius: 10px;
            padding-left: 0.95rem;
            padding-right: 0.95rem;
        }

        .acm-actions-dropdown .dropdown-menu {
            border: 1px solid #dbe5f0;
            border-radius: 12px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .acm-actions-dropdown .dropdown-item {
            padding: 0.55rem 0.95rem;
            font-weight: 600;
        }

        .acm-actions-dropdown .dropdown-item i {
            width: 1rem;
            margin-right: 0.45rem;
        }

        .acm-actions-dropdown .dropdown-item.text-danger:hover {
            background: #fff1f2;
        }

        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        .acm-widget .font-weight-bold {
            word-break: break-word;
        }

        @media (max-width: 991.98px) {
            .acm-approvals-body {
                max-height: 420px;
            }
        }
    </style>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 acm-card">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="mb-1">
                            <i class="mdi mdi-clipboard-check-outline text-primary"></i>
                            Checklist Approval Configuration
                        </h2>
                        <p class="text-muted mb-0">Configure approval definitions per workflow stage without changing code.</p>
                    </div>
                    <button type="button" wire:click="openCreateApprovalModal" class="btn btn-outline-primary acm-btn acm-top-btn mt-3 mt-md-0">
                        <i class="mdi mdi-plus"></i> Add Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card h-100 acm-card acm-approvals-card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Approvals</h5>
                    <div class="form-group mb-0">
                        <x-searchable-select
                            wire:model.live="stageNameFilter"
                            :options="collect($stageOptions)->map(fn($stage) => ['id' => $stage, 'name' => $stage])"
                            placeholder="Search stages..."
                            wire:key="stage-filter-{{ md5($stageNameFilter) }}"
                        />
                    </div>
                </div>
                <div class="card-body acm-approvals-body" wire:key="approval-list-{{ md5($stageNameFilter) }}">
                    @if ($approvals->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="mdi mdi-information-outline d-block mb-2" style="font-size: 2rem;"></i>
                            No approvals configured for this stage.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($approvals as $approval)
                                <button type="button"
                                    wire:click="selectApproval('{{ $approval->id }}')"
                                    class="list-group-item list-group-item-action acm-list-item {{ $selectedApproval && $selectedApproval->id === $approval->id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-left">
                                            <div class="font-weight-bold">{{ $approval->name }}</div>
                                            <small class="{{ $selectedApproval && $selectedApproval->id === $approval->id ? 'acm-active-subtext' : 'text-muted' }}">
                                                {{ $approval->code }}
                                            </small>
                                        </div>
                                        <span class="badge badge-{{ $approval->is_active ? 'success' : 'secondary' }}">
                                            {{ $approval->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100 acm-card" wire:key="approval-details-{{ md5($stageNameFilter . '-' . ($selectedApproval?->id ?? 'none')) }}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-1">{{ $selectedApproval?->name ?? 'Approval Details' }}</h5>
                        <small class="text-muted">
                            {{ $selectedApproval?->stage_name ?? 'Select an approval to manage checklist items.' }}
                        </small>
                    </div>
                    @if ($selectedApproval)
                        <div class="dropdown acm-actions-dropdown mt-3 mt-md-0">
                            <button type="button" class="btn btn-outline-primary acm-btn acm-top-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-cog-outline"></i> Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button type="button" wire:click="openEditApprovalModal('{{ $selectedApproval->id }}')" class="dropdown-item">
                                    <i class="mdi mdi-pencil"></i>Edit Approval
                                </button>
                                <button type="button" wire:click="openCreateChecklistItemModal" class="dropdown-item">
                                    <i class="mdi mdi-format-list-checks"></i>Add Checklist Item
                                </button>
                                <div class="dropdown-divider"></div>
                                <button type="button"
                                    wire:click="deleteApproval('{{ $selectedApproval->id }}')"
                                    onclick="return confirm('Delete this approval and all checklist items?')"
                                    class="dropdown-item text-danger">
                                    <i class="mdi mdi-delete"></i>Delete Approval
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if (!$selectedApproval)
                        <div class="text-center text-muted py-5">
                            <i class="mdi mdi-clipboard-text-outline d-block mb-2" style="font-size: 3rem;"></i>
                            Select a stage approval from the left to manage its checklist items.
                        </div>
                    @else
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="acm-widget p-3 h-100">
                                    <div class="text-muted small">Code</div>
                                    <div class="font-weight-bold">{{ $selectedApproval->code }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <div class="acm-widget p-3 h-100">
                                    <div class="text-muted small">Display Order</div>
                                    <div class="font-weight-bold">{{ $selectedApproval->order }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <div class="acm-widget p-3 h-100">
                                    <div class="text-muted small">Checklist Items</div>
                                    <div class="font-weight-bold">{{ $selectedApproval->checklistItems->count() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive acm-table-wrap">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Order</th>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Options</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selectedApproval->checklistItems as $item)
                                        <tr>
                                            <td>{{ $item->order }}</td>
                                            <td>{{ $item->label }}</td>
                                            <td><span class="badge badge-info">{{ ucfirst($item->type) }}</span></td>
                                            <td>
                                                @if ($item->is_required)
                                                    <span class="badge badge-danger">Required</span>
                                                @else
                                                    <span class="text-muted">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->type === 'select' && !empty($item->options))
                                                    <small class="text-muted">{{ implode(', ', $item->options) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right" nowrap>
                                                <div class="d-inline-flex align-items-center">
                                                    <button type="button" wire:click="openEditChecklistItemModal('{{ $item->id }}')" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit checklist item">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        wire:click="deleteChecklistItem('{{ $item->id }}')"
                                                        onclick="return confirm('Delete this checklist item?')"
                                                        class="btn btn-sm rm-act-btn rm-act-btn--delete" title="Delete checklist item">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No checklist items configured yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($showApprovalModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.45);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 18px;">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingApprovalId ? 'Edit Approval' : 'Create Approval' }}</h5>
                        <button type="button" class="close" wire:click="closeApprovalModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stage Name</label>
                                    <x-searchable-select
                                        wire:model="approvalForm.stage_name"
                                        :options="collect($stageOptions)->map(fn($stage) => ['id' => $stage, 'name' => $stage])"
                                        placeholder="Search stages..."
                                        class="{{ $errors->has('approvalForm.stage_name') ? 'is-invalid' : '' }}"
                                    />
                                    @error('approvalForm.stage_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Code</label>
                                    <input type="text" wire:model="approvalForm.code" class="form-control @error('approvalForm.code') is-invalid @enderror" placeholder="e.g. analyst_approval">
                                    @error('approvalForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" wire:model="approvalForm.name" class="form-control @error('approvalForm.name') is-invalid @enderror" placeholder="Display name">
                                    @error('approvalForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order</label>
                                    <input type="number" wire:model="approvalForm.order" class="form-control @error('approvalForm.order') is-invalid @enderror" min="1">
                                    @error('approvalForm.order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" wire:model="approvalForm.is_active" class="form-check-input" id="approval-active">
                            <label class="form-check-label" for="approval-active">Active approval</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary acm-btn" wire:click="closeApprovalModal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary acm-btn" wire:click="saveApproval">Save Approval</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showChecklistItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.45);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 18px;">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingChecklistItemId ? 'Edit Checklist Item' : 'Create Checklist Item' }}</h5>
                        <button type="button" class="close" wire:click="closeChecklistItemModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Label</label>
                                    <input type="text" wire:model="itemForm.label" class="form-control @error('itemForm.label') is-invalid @enderror">
                                    @error('itemForm.label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order</label>
                                    <input type="number" wire:model="itemForm.order" class="form-control @error('itemForm.order') is-invalid @enderror" min="1">
                                    @error('itemForm.order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Input Type</label>
                                    <select wire:model.live="itemForm.type" class="form-control @error('itemForm.type') is-invalid @enderror">
                                        <option value="checkbox">Checkbox</option>
                                        <option value="text">Text</option>
                                        <option value="select">Select</option>
                                    </select>
                                    @error('itemForm.type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox" wire:model="itemForm.is_required" class="form-check-input" id="checklist-required">
                                    <label class="form-check-label" for="checklist-required">Required item</label>
                                </div>
                            </div>
                        </div>
                        @if ($itemForm['type'] === 'select')
                            <div class="form-group">
                                <label>Options</label>
                                <textarea wire:model="itemForm.options" rows="4" class="form-control @error('itemForm.options') is-invalid @enderror" placeholder="One option per line"></textarea>
                                @error('itemForm.options') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Each line becomes a dropdown option.</small>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary acm-btn" wire:click="closeChecklistItemModal">Cancel</button>
                        <button type="button" class="btn btn-outline-success acm-btn" wire:click="saveChecklistItem">Save Item</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>