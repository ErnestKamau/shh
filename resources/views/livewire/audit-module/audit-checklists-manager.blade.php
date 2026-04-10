<div>
    @php
        $isClosed = $audit->status_name === 'Closed';
    @endphp
    <!-- Add Checklist Button -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="mdi mdi-format-list-checks"></i> Audit Checklists</h5>
                @if(!$isClosed)
                <button type="button" wire:click="openAddChecklistModal" 
                        class="btn btn-primary"
                        style="border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                    <i class="mdi mdi-plus"></i> Add Checklist
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Checklist Modal -->
    @if($showAddChecklistModal)
    <div class="modal fade show" id="addChecklistModal" tabindex="-1" role="dialog" style="display: block;" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus-circle"></i> Add New Checklist
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeAddChecklistModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit="addChecklist">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Checklist Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="checklistName" 
                                           class="form-control @error('checklistName') is-invalid @enderror" 
                                           placeholder="Enter checklist name">
                                    @error('checklistName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ISO Standard (Optional)</label>
                                    <input type="text" wire:model="checklistIsoStandard" 
                                           class="form-control" 
                                           placeholder="e.g., ISO 17025, ISO 9001">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description (Optional)</label>
                                    <textarea wire:model="checklistDescription" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Enter a brief description of the checklist"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddChecklistModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="addChecklist">
                        <i class="mdi mdi-content-save"></i> Add Checklist
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    @if($audit->checklists->count() > 0)
        <!-- Checklists and Items -->
        @foreach($audit->checklists as $checklist)
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="mdi mdi-format-list-checks"></i> 
                        {{ $checklist->name }}
                        @if($checklist->iso_standard)
                        <small class="text-muted">({{ $checklist->iso_standard }})</small>
                        @endif
                    </h5>
                    @if($checklist->description)
                    <small class="text-muted">{{ $checklist->description }}</small>
                    @endif
                </div>
                @if(!$isClosed)
                <div class="d-flex align-items-center" style="gap: 0.5rem;">
                    <button type="button" wire:click="openAddItemModal({{ $checklist->id }})" 
                            class="btn btn-sm btn-success" 
                            style="border-radius: var(--border-radius-sm, 8px); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                        <i class="mdi mdi-plus"></i> Add Item
                    </button>
                    <button type="button" wire:click="confirmRemoveChecklist({{ $checklist->id }})" 
                            class="btn btn-sm btn-danger"
                            style="border-radius: var(--border-radius-sm, 8px); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                        <i class="mdi mdi-delete"></i> Remove
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px; padding: 0.75rem;">#</th>
                                <th style="width: 120px; padding: 0.75rem;">ISO Clause</th>
                                <th style="padding: 0.75rem;">Requirement / Audit Question</th>
                                <th style="width: 150px; padding: 0.75rem;">Evidence Required</th>
                                <th style="width: 120px; padding: 0.75rem;">Compliance</th>
                                <th style="width: 150px; padding: 0.75rem 1rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $checklistItems = $allItems->filter(fn($item) => $item['checklist']->id === $checklist->id)->values();
                            @endphp
                            @foreach($checklistItems as $index => $itemData)
                            @php
                                $item = $itemData['item'];
                                $response = $itemData['response'];
                                $complianceStatus = $response ? $response->compliance_status : 'pending';
                                
                                // Get badge class from configuration
                                $statusConfig = $complianceStatuses->firstWhere('code', $complianceStatus);
                                $badgeClass = $statusConfig ? ($statusConfig->badge_class ?? 'secondary') : 'secondary';
                                $statusName = $statusConfig ? $statusConfig->name : ucfirst(str_replace('_', ' ', $complianceStatus));
                            @endphp
                            @php
                                $rowClass = '';
                                if ($statusConfig) {
                                    $rowClass = match($badgeClass) {
                                        'success' => 'table-success',
                                        'danger' => 'table-danger',
                                        'warning' => 'table-warning',
                                        default => '',
                                    };
                                }
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="padding: 0.75rem;">
                                    {{ $item->item_number }}
                                    @if($item->is_mandatory)
                                    <span class="badge badge-danger" title="Mandatory">M</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if($item->iso_clause)
                                    <code>{{ $item->iso_clause }}</code>
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    <strong>{{ $item->requirement }}</strong>
                                    @if($item->guidance)
                                    <br><small class="text-muted">{{ Str::limit($item->guidance, 100) }}</small>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    <small>{{ Str::limit($item->evidence_required ?? 'N/A', 80) }}</small>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge badge-{{ $badgeClass }}">
                                        {{ $statusName }}
                                    </span>
                                    @if($response && $response->requires_follow_up)
                                    <br><small class="text-danger"><i class="mdi mdi-flag"></i> Follow-up</small>
                                    @endif
                                </td>
                            <td style="padding-left: 1rem; padding-right: 1rem;">
                                @if(!$isClosed)
                                <div class="d-flex align-items-center justify-content-end" style="gap: 0.5rem;">
                                    <button wire:click="openItemModal({{ $item->id }})" 
                                            class="btn btn-sm btn-primary"
                                            style="border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; white-space: nowrap;">
                                        <i class="mdi mdi-pencil"></i> {{ $response ? 'Edit' : 'Record' }}
                                    </button>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            </tr>
                            @endforeach
                            @if($checklistItems->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No items found in this checklist.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state text-center py-5">
                    <i class="mdi mdi-format-list-checks" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No checklists have been added to this audit yet.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Checklist Item Response Modal -->
    @if($selectedItem)
    <div class="modal fade show" id="itemResponseModal" tabindex="-1" role="dialog" style="display: block;" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-document-edit"></i> Record Compliance - Item {{ $selectedItem->item_number }}
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeItemModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form wire:submit="saveItemResponse">
                        <!-- Item Information -->
                        <div class="card mb-3">
                            <div class="card-body bg-light">
                                <h6 class="card-title">Checklist Item Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>ISO Clause:</strong> 
                                        <code>{{ $selectedItem->iso_clause ?? 'N/A' }}</code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Item Number:</strong> {{ $selectedItem->item_number }}
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <strong>Requirement:</strong>
                                        <p>{{ $selectedItem->requirement }}</p>
                                    </div>
                                    @if($selectedItem->guidance)
                                    <div class="col-md-12">
                                        <strong>Guidance:</strong>
                                        <p class="text-muted">{{ $selectedItem->guidance }}</p>
                                    </div>
                                    @endif
                                    @if($selectedItem->evidence_required)
                                    <div class="col-md-12">
                                        <strong>Evidence Required:</strong>
                                        <p class="text-muted">{{ $selectedItem->evidence_required }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Compliance Status -->
                        <div class="form-group">
                            <label>Compliance Status <span class="text-danger">*</span></label>
                            <select wire:model="compliance_status" class="form-control @error('compliance_status') is-invalid @enderror">
                                <option value="">Select compliance status...</option>
                                @foreach($complianceStatuses as $status)
                                <option value="{{ $status->code }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                            @error('compliance_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Audit Question -->
                        <div class="form-group">
                            <label>Audit Question Asked</label>
                            <textarea wire:model="audit_question" class="form-control" rows="2" 
                                      placeholder="Enter the specific question that was asked during the audit"></textarea>
                        </div>

                        <!-- Evidence Collected -->
                        <div class="form-group">
                            <label>Evidence Collected</label>
                            <textarea wire:model="evidence_collected" class="form-control" rows="3" 
                                      placeholder="Describe the objective evidence gathered (documents, records, observations, interviews, etc.)"></textarea>
                        </div>

                        <!-- Observation -->
                        <div class="form-group">
                            <label>Observation</label>
                            <textarea wire:model="observation" class="form-control" rows="3" 
                                      placeholder="What was observed during the audit?"></textarea>
                        </div>

                        <!-- Findings -->
                        <div class="form-group">
                            <label>Findings / Issues</label>
                            <textarea wire:model="findings" class="form-control" rows="3" 
                                      placeholder="Record any findings, non-conformances, or issues identified"></textarea>
                        </div>

                        <!-- Auditor Notes -->
                        <div class="form-group">
                            <label>Auditor Notes</label>
                            <textarea wire:model="auditor_notes" class="form-control" rows="3" 
                                      placeholder="Additional notes or comments from the auditor"></textarea>
                        </div>

                        <!-- Follow-up -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="requires_follow_up" 
                                       wire:model="requires_follow_up">
                                <label class="custom-control-label" for="requires_follow_up">
                                    Requires Follow-up
                                </label>
                            </div>
                        </div>

                        @if($requires_follow_up)
                        <div class="form-group">
                            <label>Follow-up Notes</label>
                            <textarea wire:model="follow_up_notes" class="form-control" rows="2" 
                                      placeholder="Describe what follow-up actions are required"></textarea>
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeItemModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveItemResponse">
                        <i class="mdi mdi-content-save"></i> Save Response
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Add Checklist Item Modal -->
    @if($showAddItemModal && $selectedChecklistIdForItem)
    <div class="modal fade show" id="addItemModal" tabindex="-1" role="dialog" style="display: block;" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus-circle"></i> Add Checklist Item
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeAddItemModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form wire:submit="addChecklistItem">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Item Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="item_number" 
                                           class="form-control @error('item_number') is-invalid @enderror" 
                                           placeholder="e.g., 1, 1.1, 2.3">
                                    @error('item_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ISO Clause (Optional)</label>
                                    <input type="text" wire:model="iso_clause" 
                                           class="form-control" 
                                           placeholder="e.g., 8.3, 6.2, 7.5">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Requirement / Audit Question <span class="text-danger">*</span></label>
                                    <textarea wire:model="requirement" 
                                              class="form-control @error('requirement') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Enter the requirement or audit question"></textarea>
                                    @error('requirement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Guidance (Optional)</label>
                                    <textarea wire:model="guidance" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="Enter guidance or instructions for auditors"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Evidence Required (Optional)</label>
                                    <textarea wire:model="evidence_required" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="Describe what evidence should be collected"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_mandatory" 
                                               wire:model="is_mandatory">
                                        <label class="custom-control-label" for="is_mandatory">
                                            Mandatory Item
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddItemModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="addChecklistItem">
                        <i class="mdi mdi-content-save"></i> Add Item
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Delete Checklist Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" id="deleteChecklistModal" tabindex="-1" role="dialog" style="display: block;" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="close text-white" wire:click="cancelDelete" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove the checklist <strong>"{{ $checklistToDeleteName }}"</strong> from this audit?</p>
                    <p class="text-muted mb-0"><small>This will remove the checklist from the audit but will not delete the checklist itself. All checklist item responses will also be removed.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="removeChecklist">
                        <i class="mdi mdi-delete"></i> Remove Checklist
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

@section('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('notify', function (data) {
            const type = data.type || 'info';
            const message = data.message || '';
            
            if (typeof toastr !== 'undefined') {
                if (type === 'success') {
                    toastr.success(message);
                } else if (type === 'error') {
                    toastr.error(message);
                } else if (type === 'warning') {
                    toastr.warning(message);
                } else {
                    toastr.info(message);
                }
            } else {
                alert(message);
            }
        });

        // Close modals on backdrop click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'itemResponseModal') {
                @this.call('closeItemModal');
            }
            if (e.target.id === 'addChecklistModal') {
                @this.call('closeAddChecklistModal');
            }
            if (e.target.id === 'addItemModal') {
                @this.call('closeAddItemModal');
            }
            if (e.target.id === 'deleteChecklistModal' || e.target.classList.contains('modal-backdrop')) {
                @this.call('cancelDelete');
            }
        });
    });
</script>
@endsection
