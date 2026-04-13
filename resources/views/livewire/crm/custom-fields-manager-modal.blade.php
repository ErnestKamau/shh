<div>
    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="customFieldsManagerModal" tabindex="-1" role="dialog" aria-labelledby="customFieldsManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customFieldsManagerModalLabel">
                        Manage Custom Fields <br>
                        <small class="text-muted">Type: <strong>{{ $sampleTypeName }}</strong></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                @if($customerId && $sampleTypeId)
                    <div class="modal-body p-0">
                        <ul class="crm-tab-nav crm-tab-nav-justified" id="customFieldsTab" role="tablist">
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab === 'existing' ? 'active' : '' }}" wire:click.prevent="switchTab('existing')" href="#">
                                    <i class="mdi mdi-format-list-checks"></i> Choose Existing Fields
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab === 'create' ? 'active' : '' }}" wire:click.prevent="switchTab('create')" href="#">
                                    <i class="mdi mdi-plus-circle-outline"></i> Create New Field
                                </a>
                            </li>
                        </ul>
                        
                        <div class="p-3">
                            <!-- Loading State -->
                            <div wire:loading wire:target="loadFields, attachField, detachField, createField, switchTab" class="w-100 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Processing...</p>
                            </div>

                            <div wire:loading.remove wire:target="loadFields, attachField, detachField, createField, switchTab">
                                
                                @if(!$hasCategory)
                                    <div class="alert alert-info border-info mb-4">
                                        <strong><i class="mdi mdi-information-outline"></i> No Custom Field Category Exists</strong>
                                        <p class="mb-2 mt-1 small">
                                            This customer and sample type currently don't share a custom field category. 
                                            Creating or attaching the first field will automatically create a new category for them. 
                                            Please provide a name for this new category below:
                                        </p>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('newCategoryName') is-invalid @enderror" wire:model.defer="newCategoryName" placeholder="e.g. Microbiology Custom Fields">
                                            @error('newCategoryName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif

                                {{-- TAB: EXISTING FIELDS --}}
                                @if($activeTab === 'existing')
                                    <div class="mb-4">
                                        <h6 class="text-success border-bottom pb-2">Currently Attached Fields</h6>
                                        @if(empty($existingFields))
                                            <p class="text-muted small">No custom fields are currently attached to this Sample Type for the selected Customer.</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Label</th>
                                                            <th>Type</th>
                                                            <th width="100" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($existingFields as $field)
                                                            <tr>
                                                                <td>{{ $field['label'] }}</td>
                                                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'])) }}</td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm btn-outline-danger py-0" wire:click="detachField({{ $field['id'] }})" title="Remove">
                                                                        <i class="mdi mdi-minus-circle"></i> Remove
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <h6 class="text-primary border-bottom pb-2">Available Fields to Attach</h6>
                                        @if(empty($availableFields))
                                            <p class="text-muted small">No other custom fields are available in the system.</p>
                                        @else
                                            <div style="max-height: 250px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered table-hover">
                                                    <thead class="bg-light sticky-top">
                                                        <tr>
                                                            <th>Label</th>
                                                            <th>Type</th>
                                                            <th width="100" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($availableFields as $field)
                                                            <tr>
                                                                <td>{{ $field['label'] }}</td>
                                                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'])) }}</td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm btn-outline-primary py-0" wire:click="attachField('{{ $field['id'] }}')" title="Attach to this Sample Type">
                                                                        <i class="mdi mdi-plus-circle"></i> Add
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- TAB: CREATE NEW FIELD --}}
                                @if($activeTab === 'create')
                                    <div class="bg-light p-3 rounded border">
                                        <h6 class="text-primary mb-3"><i class="mdi mdi-plus-box"></i> Define a New Custom Field</h6>
                                        <p class="small text-muted mb-3">
                                            This will create a new custom field and automatically map it to the database for this specific customer and sample type. 
                                            If a field with the same name already exists in the system, you must attach it from the "Existing Fields" tab instead of creating a duplicate.
                                        </p>

                                        <form wire:submit.prevent="createField">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Field Label <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('newFieldLabel') is-invalid @enderror" wire:model.defer="newFieldLabel" placeholder="e.g. Batch Number">
                                                @error('newFieldLabel') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Field Type <span class="text-danger">*</span></label>
                                                <select class="form-control @error('newFieldType') is-invalid @enderror" wire:model.defer="newFieldType">
                                                    <option value="text">Short Text (Text Input)</option>
                                                    <option value="textarea">Long Text (Textarea)</option>
                                                    <option value="number">Numeric (Number Input)</option>
                                                    <option value="date_picker">Date (Date Picker)</option>
                                                    <option value="checkbox">Checkbox (Yes/No)</option>
                                                </select>
                                                @error('newFieldType') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="font-weight-bold">Help Text / Default Value / Placeholder</label>
                                                <input type="text" class="form-control" wire:model.defer="newFieldDefaultValue" placeholder="Optional hint to show inside the input field">
                                            </div>

                                            <div class="text-right mt-4">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="mdi mdi-check"></i> Create & Attach Field
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @else
                    <div class="modal-body">
                        <div class="alert alert-warning mb-0">
                            <strong><i class="mdi mdi-alert"></i> Missing Information:</strong> Please ensure you have selected a Client in Step 1 before managing custom fields.
                        </div>
                    </div>
                @endif
                
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
