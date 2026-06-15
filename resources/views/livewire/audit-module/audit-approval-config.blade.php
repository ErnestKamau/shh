<div>
    <style>
        .modern-search-bar {
            background: #ffffff;
            border-bottom: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-search-input {
            border: 1px solid #dadce0;
            border-radius: 24px;
            padding: 10px 16px 10px 44px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-search-input:focus {
            outline: none;
            border-color: #6D0A0E;
            box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.1);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
        }

        .modern-filter-select {
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-filter-select:focus {
            outline: none;
            border-color: #6D0A0E;
            box-shadow: 0 0 0 3px rgba(109, 10, 14, 0.1);
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .modern-table thead {
            background: #f8f9fa;
        }

        .modern-table thead th {
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5f6368;
            border-bottom: 1px solid #e8eaed;
            white-space: nowrap;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.15s ease;
        }

        .modern-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .modern-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .modern-table tbody tr:hover {
            background: #e8f0fe !important;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .modern-action-btn {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-right: 4px;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #6D0A0E;
        }

        .btn-modern {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modern-empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .modern-empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 16px;
        }

        .modern-empty-state p {
            color: #6c757d;
            font-size: 1rem;
            margin: 0;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
        }
    </style>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Module Selector -->
    @if($allowModuleSwitching)
    <div class="row mb-4 px-3">
        <div class="col-12">
            <ul class="nav nav-pills" style="gap: 12px;">
                <li class="nav-item">
                    <a class="nav-link {{ $module === 'audit' ? 'active' : '' }}" 
                       href="#" 
                       wire:click.prevent="$set('module', 'audit')"
                       style="border-radius: 24px; padding: 10px 28px; font-weight: 500; transition: all 0.2s; {{ $module === 'audit' ? 'background-color: #28a745; color: white; box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);' : 'background-color: #fff; color: #5f6368; border: 1px solid #dadce0;' }}">
                        <i class="mdi mdi-checkbox-marked-circle-outline mr-2"></i> Audit Module
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $module === 'risk' ? 'active' : '' }}" 
                       href="#" 
                       wire:click.prevent="$set('module', 'risk')"
                       style="border-radius: 24px; padding: 10px 28px; font-weight: 500; transition: all 0.2s; {{ $module === 'risk' ? 'background-color: #28a745; color: white; box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);' : 'background-color: #fff; color: #5f6368; border: 1px solid #dadce0;' }}">
                        <i class="mdi mdi-alert-circle-check-outline mr-2"></i> Risk Module
                    </a>
                </li>
            </ul>
        </div>
    </div>
    @endif

    <!-- Search Bar -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="position-relative">
                    <i class="mdi mdi-magnify modern-search-icon"></i>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           class="modern-search-input" 
                           placeholder="Search by user name or email...">
                </div>
            </div>
            <div class="col-md-4">
                <select wire:model.live="selectedStep" class="modern-filter-select">
                    <option value="">All Workflow Steps</option>
                    @foreach($workflowSteps as $stepNum => $stepName)
                    <option value="{{ $stepNum }}">Step {{ $stepNum }}: {{ $stepName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 text-right">
                <button wire:click="openModal()" class="btn btn-modern btn-primary">
                    <i class="mdi mdi-plus"></i> Add New
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Workflow Step</th>
                    <th>Role Type</th>
                    <th>User</th>
                    <th>ISO Role</th>
                    <th>Required</th>
                    <th>Approval Type</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvers as $approver)
                <tr>
                    <td style="font-weight: 500; color: #202124;">
                        <strong>Step {{ $approver->workflow_step }}</strong><br>
                        <small style="color: #5f6368;">{{ $workflowSteps[$approver->workflow_step] ?? 'N/A' }}</small>
                    </td>
                    <td>
                        @if($approver->role_type === 'approver')
                            <span class="modern-badge" style="background: #e3f2fd; color: #1976d2;">Approver</span>
                        @else
                            <span class="modern-badge" style="background: #e0f2f1; color: #00695c;">Verifier</span>
                        @endif
                    </td>
                    <td style="color: #202124;">
                        <strong>{{ $approver->user->name ?? 'N/A' }}</strong><br>
                        <small style="color: #5f6368;">{{ $approver->user->email ?? '' }}</small>
                    </td>
                    <td>
                        @if($approver->iso_role)
                            <span class="modern-badge" style="background: #f5f5f5; color: #5f6368;">{{ $approver->iso_role }}</span>
                        @else
                            <span style="color: #5f6368;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($approver->is_required)
                            <span class="modern-badge" style="background: #fee; color: #c33;">Required</span>
                        @else
                            <span class="modern-badge" style="background: #fff3cd; color: #856404;">Optional</span>
                        @endif
                    </td>
                    <td>
                        <span class="modern-badge" style="background: #e6f4ea; color: #137333;">{{ ucfirst($approver->approval_type) }}</span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button wire:click="openModal(null, {{ $approver->id }})" class="modern-action-btn" title="Edit" style="color: #d97706;">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button wire:click="delete({{ $approver->id }})" wire:confirm="Are you sure you want to delete approver '{{ $approver->user->name ?? 'N/A' }}'? This action cannot be undone." class="modern-action-btn" title="Delete" style="color: #c33;">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="modern-empty-state">
                        <i class="mdi mdi-account-check-outline"></i>
                        <p>No approvers configured yet. Click "Add New" to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="modern-pagination">
        {{ $approvers->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header {{ $isEdit ? 'bg-primary' : 'bg-success' }} text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $isEdit ? 'pencil' : 'plus' }}"></i> {{ $isEdit ? 'Edit' : 'Add' }} Approver/Verifier Configuration
                        </h5>
                        <button type="button" class="close text-white" wire:click="$set('showModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Workflow Step <span class="text-danger">*</span></label>
                                    <select wire:model="workflowStep" 
                                            class="form-control @error('workflowStep') is-invalid @enderror" 
                                            style="border-radius: 8px; border: 1px solid #dadce0;" required>
                                        <option value="">Select Step...</option>
                                        @foreach($workflowSteps as $stepNum => $stepName)
                                        <option value="{{ $stepNum }}">Step {{ $stepNum }}: {{ $stepName }}</option>
                                        @endforeach
                                    </select>
                                    @error('workflowStep') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Role Type <span class="text-danger">*</span></label>
                                    <select wire:model="roleType" 
                                            class="form-control @error('roleType') is-invalid @enderror" 
                                            style="border-radius: 8px; border: 1px solid #dadce0;" required>
                                        <option value="approver">Approver</option>
                                        <option value="verifier">Verifier</option>
                                    </select>
                                    @error('roleType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">User <span class="text-danger">*</span></label>
                                    <select wire:model="userId" 
                                            class="form-control @error('userId') is-invalid @enderror" 
                                            style="border-radius: 8px; border: 1px solid #dadce0;" required>
                                        <option value="">Select User...</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('userId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">ISO Role</label>
                                    <select wire:model="isoRole" 
                                            class="form-control @error('isoRole') is-invalid @enderror"
                                            style="border-radius: 8px; border: 1px solid #dadce0;">
                                        <option value="">Select ISO Role...</option>
                                        @foreach($isoRoles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    @error('isoRole') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Lead Auditor, Quality Manager, Top Management, or Auditee</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Approval Type <span class="text-danger">*</span></label>
                                    <select wire:model="approvalType" 
                                            class="form-control @error('approvalType') is-invalid @enderror" 
                                            style="border-radius: 8px; border: 1px solid #dadce0;" required>
                                        <option value="single">Single</option>
                                        <option value="multiple">Multiple</option>
                                    </select>
                                    @error('approvalType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Single: One approver needed. Multiple: All approvers must approve.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">&nbsp;</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" wire:model="isRequired" 
                                               class="form-check-input @error('isRequired') is-invalid @enderror" 
                                               id="isRequired">
                                        <label class="form-check-label" for="isRequired" style="color: #5f6368;">
                                            <strong>Approval Required</strong>
                                        </label>
                                        @error('isRequired') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted d-block">If checked, workflow cannot proceed without this approval.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn {{ $isEdit ? 'btn-primary' : 'btn-success' }}">
                            <i class="mdi mdi-content-save"></i> {{ $isEdit ? 'Save Changes' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
