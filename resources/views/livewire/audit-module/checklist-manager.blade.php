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
            border-color: #17a2b8;
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
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
            background: #e0f7fa !important;
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
            border-color: #17a2b8;
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

        .item-row {
            border: 1px solid #e8eaed;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            background: #ffffff;
        }

        .item-row:hover {
            background: #f8f9fa;
        }
    </style>

    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" style="margin: 16px 24px; border-radius: 8px;">
        {{ session('message') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="margin: 16px 24px; border-radius: 8px;">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <!-- Search Bar -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="mdi mdi-magnify modern-search-icon"></i>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           class="modern-search-input" 
                           placeholder="Search checklists...">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button wire:click="openModal()" class="btn btn-modern btn-primary">
                    <i class="mdi mdi-plus"></i> Add New Checklist
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Audit Type</th>
                    <th>ISO Standard</th>
                    <th>Workflows</th>
                    <th>Sample Types</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checklists as $checklist)
                <tr>
                    <td style="font-weight: 500; color: #202124;">
                        <strong>{{ $checklist->name }}</strong>
                        @if($checklist->description)
                        <br><small style="color: #5f6368;">{{ Str::limit($checklist->description, 50) }}</small>
                        @endif
                    </td>
                    <td><code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.875rem;">{{ $checklist->code }}</code></td>
                    <td style="color: #5f6368;">{{ $checklist->auditType?->name ?? 'N/A' }}</td>
                    <td style="color: #5f6368;">{{ $checklist->iso_standard ?? '-' }}</td>
                    <td>
                        @if($checklist->workflowActions->count() > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                            @foreach($checklist->workflowActions->take(2) as $action)
                            <span class="modern-badge" style="background: #f3e5f5; color: #7b1fa2; font-size: 11px;">{{ $action->name }}</span>
                            @endforeach
                            @if($checklist->workflowActions->count() > 2)
                            <span class="modern-badge" style="background: #f3e5f5; color: #7b1fa2; font-size: 11px;">+{{ $checklist->workflowActions->count() - 2 }}</span>
                            @endif
                        </div>
                        @else
                        <span style="color: #9e9e9e; font-size: 13px;">None</span>
                        @endif
                    </td>
                    <td>
                        @if($checklist->sampleTypes->count() > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                            @foreach($checklist->sampleTypes->take(2) as $type)
                            <span class="modern-badge" style="background: #e0f2f1; color: #00695c; font-size: 11px;">{{ $type->name }}</span>
                            @endforeach
                            @if($checklist->sampleTypes->count() > 2)
                            <span class="modern-badge" style="background: #e0f2f1; color: #00695c; font-size: 11px;">+{{ $checklist->sampleTypes->count() - 2 }}</span>
                            @endif
                        </div>
                        @else
                        <span style="color: #9e9e9e; font-size: 13px;">None</span>
                        @endif
                    </td>
                    <td>
                        <span class="modern-badge" style="background: #e3f2fd; color: #1976d2;">
                            {{ $checklist->items->count() }} items
                        </span>
                    </td>
                    <td>
                        <span class="modern-badge {{ $checklist->is_active ? 'badge-success' : 'badge-secondary' }}" style="background: {{ $checklist->is_active ? '#e6f4ea' : '#f5f5f5' }}; color: {{ $checklist->is_active ? '#137333' : '#5f6368' }};">
                            {{ $checklist->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button wire:click="openItemsModal({{ $checklist->id }})" class="modern-action-btn" title="Manage Items" style="color: #17a2b8;">
                                <i class="mdi mdi-format-list-bulleted"></i>
                            </button>
                            <button wire:click="openModal({{ $checklist->id }})" class="modern-action-btn" title="Edit" style="color: #d97706;">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button wire:click="toggleActive({{ $checklist->id }})" class="modern-action-btn" title="{{ $checklist->is_active ? 'Deactivate' : 'Activate' }}" style="color: {{ $checklist->is_active ? '#5f6368' : '#137333' }};">
                                <i class="mdi mdi-{{ $checklist->is_active ? 'close' : 'check' }}"></i>
                            </button>
                            <button wire:click="delete({{ $checklist->id }})" wire:confirm="Are you sure you want to delete '{{ $checklist->name }}'? This will also delete all checklist items. This action cannot be undone." class="modern-action-btn" title="Delete" style="color: #c33;">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="modern-empty-state">
                        <i class="mdi mdi-clipboard-list-outline"></i>
                        <p>No checklists found. Click "Add New Checklist" to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="modern-pagination">
        {{ $checklists->links() }}
    </div>

    <!-- Checklist Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <form wire:submit="save">
                    <div class="modal-header" style="border-bottom: 1px solid #e8eaed;">
                        <h5 class="modal-title" style="font-weight: 600;">{{ $isEdit ? 'Edit' : 'Add' }} Checklist</h5>
                        <button type="button" class="close" wire:click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Name <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" style="border-radius: 8px; border: 1px solid #dadce0;">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Code <span class="text-danger">*</span></label>
                                    <input wire:model="code" type="text" class="form-control @error('code') is-invalid @enderror" style="border-radius: 8px; border: 1px solid #dadce0;">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Audit Type</label>
                                    <select wire:model="audit_type_id" class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                        <option value="">Select Audit Type...</option>
                                        @foreach($auditTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">ISO Standard</label>
                            <input wire:model="iso_standard" type="text" class="form-control" placeholder="e.g., ISO 17025:2017" style="border-radius: 8px; border: 1px solid #dadce0;">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Workflows</label>
                                    <select wire:model="selected_workflow_actions" multiple class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                        @foreach($workflowActions as $action)
                                        <option value="{{ $action['id'] }}">{{ $action['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <small style="color: #5f6368;">Select applicable workflow actions for this checklist</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Sample Types</label>
                                    <select wire:model="selected_sample_types" multiple class="form-control" style="border-radius: 8px; border: 1px solid #dadce0;">
                                        @foreach($sampleTypes as $type)
                                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <small style="color: #5f6368;">Select applicable sample types for this checklist</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 500; color: #5f6368; margin-bottom: 8px;">Description</label>
                            <textarea wire:model="description" class="form-control" rows="3" style="border-radius: 8px; border: 1px solid #dadce0;"></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input wire:model="is_active" type="checkbox" class="custom-control-input" id="isActive">
                                <label class="custom-control-label" for="isActive" style="color: #5f6368;">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e8eaed;">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal" style="border-radius: 8px;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="border-radius: 8px;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Items Management Modal -->
    @if($showItemsModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid #e8eaed;">
                    <h5 class="modal-title" style="font-weight: 600;">Manage Checklist Items</h5>
                    <button type="button" class="close" wire:click="closeItemsModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; max-height: 70vh; overflow-y: auto;">
                    <!-- Add/Edit Item Form -->
                    <div class="card mb-3" style="border: 1px solid #e8eaed; border-radius: 8px;">
                        <div class="card-body">
                            <h6 style="font-weight: 600; margin-bottom: 16px;">{{ $editingItemIndex !== null ? 'Edit' : 'Add' }} Item</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size: 12px; font-weight: 500; color: #5f6368;">Item Number <span class="text-danger">*</span></label>
                                        <input wire:model="item_number" type="text" class="form-control form-control-sm" style="border-radius: 6px;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size: 12px; font-weight: 500; color: #5f6368;">ISO Clause</label>
                                        <input wire:model="iso_clause" type="text" class="form-control form-control-sm" style="border-radius: 6px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size: 12px; font-weight: 500; color: #5f6368;">Requirement <span class="text-danger">*</span></label>
                                        <input wire:model="requirement" type="text" class="form-control form-control-sm" style="border-radius: 6px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size: 12px; font-weight: 500; color: #5f6368;">Guidance</label>
                                        <textarea wire:model="guidance" class="form-control form-control-sm" rows="2" style="border-radius: 6px;"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size: 12px; font-weight: 500; color: #5f6368;">Evidence Required</label>
                                        <textarea wire:model="evidence_required" class="form-control form-control-sm" rows="2" style="border-radius: 6px;"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input wire:model="is_mandatory" type="checkbox" class="custom-control-input" id="isMandatory">
                                    <label class="custom-control-label" for="isMandatory" style="font-size: 13px;">Mandatory</label>
                                </div>
                            </div>
                            <div class="mt-2">
                                @if($editingItemIndex !== null)
                                <button wire:click="updateItem" type="button" class="btn btn-sm btn-primary" style="border-radius: 6px;">
                                    <i class="mdi mdi-check"></i> Update Item
                                </button>
                                <button wire:click="resetItemForm" type="button" class="btn btn-sm btn-secondary" style="border-radius: 6px;">
                                    Cancel
                                </button>
                                @else
                                <button wire:click="addItem" type="button" class="btn btn-sm btn-primary" style="border-radius: 6px;">
                                    <i class="mdi mdi-plus"></i> Add Item
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div>
                        <h6 style="font-weight: 600; margin-bottom: 16px;">Items ({{ count($items) }})</h6>
                        @forelse($items as $index => $item)
                        <div class="item-row">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <strong style="color: #202124; margin-right: 12px;">{{ $item['item_number'] }}</strong>
                                        @if($item['iso_clause'])
                                        <span class="badge badge-secondary" style="margin-right: 8px;">{{ $item['iso_clause'] }}</span>
                                        @endif
                                        @if($item['is_mandatory'])
                                        <span class="badge badge-danger" style="margin-right: 8px;">Mandatory</span>
                                        @endif
                                        @if(!($item['is_active'] ?? true))
                                        <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </div>
                                    <p style="margin: 0; color: #5f6368; font-size: 14px;"><strong>Requirement:</strong> {{ $item['requirement'] }}</p>
                                    @if($item['guidance'])
                                    <p style="margin: 4px 0 0 0; color: #6c757d; font-size: 13px;"><strong>Guidance:</strong> {{ $item['guidance'] }}</p>
                                    @endif
                                    @if($item['evidence_required'])
                                    <p style="margin: 4px 0 0 0; color: #6c757d; font-size: 13px;"><strong>Evidence:</strong> {{ $item['evidence_required'] }}</p>
                                    @endif
                                </div>
                                <div class="btn-group btn-group-sm ml-3">
                                    @if($index > 0)
                                    <button wire:click="moveItemUp({{ $index }})" class="btn btn-outline-secondary" title="Move Up">
                                        <i class="mdi mdi-arrow-up"></i>
                                    </button>
                                    @endif
                                    @if($index < count($items) - 1)
                                    <button wire:click="moveItemDown({{ $index }})" class="btn btn-outline-secondary" title="Move Down">
                                        <i class="mdi mdi-arrow-down"></i>
                                    </button>
                                    @endif
                                    <button wire:click="editItem({{ $index }})" class="btn btn-outline-warning" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button wire:click="deleteItem({{ $index }})" class="btn btn-outline-danger" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-muted">No items added yet. Add items using the form above.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e8eaed;">
                    <button type="button" class="btn btn-secondary" wire:click="closeItemsModal" style="border-radius: 8px;">Cancel</button>
                    <button wire:click="saveItems" type="button" class="btn btn-primary" style="border-radius: 8px;">
                        <i class="mdi mdi-content-save"></i> Save Items
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
