<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Unprocessed Staging Data Section --}}
    @if(isset($batch->sample_detail_processed) && $batch->sample_detail_processed == 0 && isset($batch->stagingDetails) && $batch->stagingDetails->count() > 0)
        <div class="card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7);">
                <h5 class="mb-0"><i class="mdi mdi-clipboard-alert"></i> Unprocessed Staging Data</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Actions</th>
                                <th>Specimen Type</th>
                                <th>Company Sub Unit</th>
                                <th>Analysis Types</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->stagingDetails as $staging)
                                @if(!$staging->is_processed)
                                    <tr>
                                        <td>
                                            <button type="button" 
                                                    wire:click="assignSamples({{ $staging->id }})"
                                                    class="btn btn-sm btn-primary"
                                                    wire:loading.attr="disabled"
                                                    wire:target="assignSamples({{ $staging->id }})">
                                                <span wire:loading.remove wire:target="assignSamples({{ $staging->id }})">
                                                    <i class="mdi mdi-checkbox-multiple-marked"></i>  Assign Samples
                                                </span>
                                                <span wire:loading wire:target="assignSamples({{ $staging->id }})">
                                                    <i class="mdi mdi-loading mdi-spin"></i> Processing...
                                                </span>
                                            </button>
                                            
                                            <button type="button" 
                                                    wire:click="editStaging({{ $staging->id }})"
                                                    class="btn btn-sm btn-info">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            
                                            <button type="button" 
                                                    wire:click="confirmDeleteStaging({{ $staging->id }})"
                                                    class="btn btn-sm btn-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                        <td>{{ $batch->sample_type->name ?? 'N/A' }}</td>
                                        <td>{{ $staging->data_json['company_sub_unit_name'] ?? 'N/A' }}</td>
                                        <td>{{ $staging->data_json['analysis_type_names'] ?? 'N/A' }}</td>
                                        <td>{{ $staging->data_json['quantity'] ?? 1 }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Sample Configuration Form (Livewire-driven) --}}
    @if(!isset($batch->sample_detail_processed) || $batch->sample_detail_processed == 1)
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Samples Configuration</h5>
                
                @if(in_array($batch->status ?? '', ['Samples Reception', 'Samples En-Route']))
                    <div>
                        <button type="button" wire:click="addSample" class="btn btn-success btn-sm" wire:loading.attr="disabled">
                            <i class="mdi mdi-plus"></i> Add
                        </button>
                        <button type="button" wire:click="duplicateLastSample" class="btn btn-primary btn-sm ml-1" wire:loading.attr="disabled">
                            <i class="mdi mdi-content-duplicate"></i> Duplicate
                        </button>
                        <button type="button" wire:click="saveSamples" class="btn btn-danger btn-sm text-white ml-1" wire:loading.attr="disabled">
                            <span wire:loading.remove><i class="mdi mdi-content-save"></i> Save</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size: 13px;">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 100px; text-align: center;">Actions</th>
                            <th style="min-width: 100px;">Code</th>
                            <th style="min-width: 150px;">Analysis<sup class="text-danger">*</sup></th>
                            <th style="min-width: 120px;">Lab<sup class="text-danger">*</sup></th>
                            <th style="min-width: 120px;">Condition<sup class="text-danger">*</sup></th>
                            <th style="min-width: 150px;">Sample Point<sup class="text-danger">*</sup></th>
                            <th style="min-width: 150px;">Product<sup class="text-danger">*</sup></th>
                            <th style="min-width: 150px;">Description</th>
                            <th style="min-width: 100px;">Time Sampled</th>
                            <th style="min-width: 120px;">Main Std<sup class="text-danger">*</sup></th>
                            <th style="min-width: 120px;">Secondary Std</th>
                            <th style="min-width: 110px;">Disposal Date</th>
                            <th style="min-width: 120px;">Storage</th>
                            <th style="min-width: 80px;">Slot</th>
                            <th style="min-width: 80px;">Quantity</th>
                            <th style="min-width: 80px;">UoM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sampleForms as $index => $sampleForm)
                            @php
                                $isEditing = $editingRowIndex === $index;
                                $isReadOnly = !$isEditing;
                            @endphp
                            <tr wire:key="sample-row-{{ $index }}">
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- View Parameters Icon --}}
                                        @if($sampleForm['id'])
                                            <button type="button" wire:click="viewParameters('{{ $sampleForm['sample_code'] }}')" 
                                                    class="btn btn-sm btn-icon btn-light text-info mx-1" title="View Parameters">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Comment Button --}}
                                        @if($sampleForm['id'])
                                            <button type="button" wire:click="openCommentsModal({{ $sampleForm['id'] }})" 
                                                    class="btn btn-sm btn-icon btn-light text-success mx-1" title="Comments & Interpretations">
                                                <i class="mdi mdi-comment-text"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Interlab Button --}}
                                        @if($sampleForm['id'])
                                            <button type="button" wire:click="openInterlabModal({{ $sampleForm['id'] }}, '{{ $sampleForm['sample_code'] }}')" 
                                                    class="btn btn-sm btn-icon btn-light text-warning mx-1" title="Initiate Inter Lab Transfer">
                                                <i class="mdi mdi-swap-horizontal-bold"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Edit Icon --}}
                                        @if($isEditing)
                                            <button type="button" wire:click="cancelEditRow" 
                                                    class="btn btn-sm btn-icon btn-light text-secondary mx-1" title="Cancel Edit">
                                                <i class="mdi mdi-close"></i>
                                            </button>
                                        @else
                                            <button type="button" wire:click="editRow({{ $index }})" 
                                                    class="btn btn-sm btn-icon btn-light text-primary mx-1" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Delete Icon --}}
                                        <button type="button" wire:click="confirmDeleteSample({{ $index }})" 
                                                class="btn btn-sm btn-icon btn-light text-danger mx-1" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                                
                                {{-- Sample Code (readonly) --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           value="{{ $sampleForm['sample_code'] }}" 
                                           readonly style="background: #f5f5f5; font-weight: bold;">
                                </td>
                                
                                {{-- Analysis Types (Searchable Multi-Select) --}}
                                <td>
                                    @if($isReadOnly)
                                        <div class="form-control form-control-sm readonly-input" style="height: auto; min-height: 31px;">
                                            @if(is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0)
                                                @foreach($analysisTypes as $type)
                                                    @if(in_array($type['id'], $sampleForm['analysis_type_id']))
                                                        <span class="badge badge-info mr-1">{{ $type['name'] }}</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="tag-select-container" style="min-width: 200px;" 
                                             wire:click="$set('showAnalysisTypeDropdown.{{ $index }}', true)" 
                                             wire:click.outside="$set('showAnalysisTypeDropdown.{{ $index }}', false)">
                                            <div class="tag-select-input">
                                                {{-- Display selected analysis types as badges --}}
                                                @if(is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0)
                                                    @foreach($analysisTypes as $type)
                                                        @if(in_array($type['id'], $sampleForm['analysis_type_id']))
                                                            <span class="tag-badge">
                                                                {{ $type['name'] }}
                                                                <i class="mdi mdi-close-circle" 
                                                                   wire:click.stop="toggleAnalysisType({{ $index }}, {{ $type['id'] }})"></i>
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                
                                                {{-- Search Input --}}
                                                <input type="text" 
                                                       wire:model.live="analysisTypeSearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ (is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0) ? '' : 'Search analysis types...' }}"
                                                       autocomplete="off">
                                            </div>
                                            
                                            {{-- Dropdown --}}
                                            @if(isset($showAnalysisTypeDropdown[$index]) && $showAnalysisTypeDropdown[$index])
                                                <div class="tag-dropdown">
                                                    @php
                                                        $filteredTypes = $this->getFilteredAnalysisTypes($index);
                                                    @endphp
                                                    @if(count($filteredTypes) > 0)
                                                        @foreach($filteredTypes as $type)
                                                            <div class="tag-dropdown-item" 
                                                                 wire:click.stop="toggleAnalysisType({{ $index }}, {{ $type['id'] }})">
                                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                                    <span>{{ $type['name'] }} <small class="text-muted">({{ $type['code'] }})</small></span>
                                                                    @if(is_array($sampleForm['analysis_type_id']) && in_array($type['id'], $sampleForm['analysis_type_id']))
                                                                        <i class="mdi mdi-check text-success"></i>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="p-3 text-center text-muted">No analysis types found</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @error("sampleForms.$index.analysis_type_id") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Lab Section --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($labSections)->firstWhere('id', $sampleForm['lab_id'])['name'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.lab_id" required>
                                            <option value="">Select...</option>
                                            @foreach($labSections as $lab)
                                                <option value="{{ $lab['id'] }}">{{ $lab['code'] }} - {{ $lab['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error("sampleForms.$index.lab_id") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Condition --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($conditions)->firstWhere('id', $sampleForm['condition_id'])['name'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.condition_id" required>
                                            <option value="">Select...</option>
                                            @foreach($conditions as $condition)
                                                <option value="{{ $condition['id'] }}">{{ $condition['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error("sampleForms.$index.condition_id") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Sample Point --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($samplePoints)->firstWhere('id', $sampleForm['sample_point_id'])['name'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.sample_point_id" required>
                                            <option value="">Select...</option>
                                            @foreach($samplePoints as $point)
                                                <option value="{{ $point['id'] }}">{{ $point['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error("sampleForms.$index.sample_point_id") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Product --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($products)->firstWhere('id', $sampleForm['company_product_id'])['name'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.company_product_id" required>
                                            <option value="">Select...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error("sampleForms.$index.company_product_id") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Description --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm modern-input" 
                                           wire:model.defer="sampleForms.{{ $index }}.description" 
                                           placeholder="Description..."
                                           @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                                </td>
                                
                                {{-- Time Sampled --}}
                                <td>
                                    <input type="time" class="form-control form-control-sm modern-input" 
                                           wire:model.defer="sampleForms.{{ $index }}.time_sampled"
                                           @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                                </td>
                                
                                {{-- Main Standard --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($standards)->firstWhere('id', $sampleForm['main_standard'])['code'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.main_standard" required>
                                            <option value="">Select...</option>
                                            @foreach($standards as $std)
                                                <option value="{{ $std['id'] }}">{{ $std['code'] }}</option>
                                            @endforeach
                                        </select>
                                        @error("sampleForms.$index.main_standard") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    @endif
                                </td>
                                
                                {{-- Secondary Standard --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($standards)->firstWhere('id', $sampleForm['secondary_standard'])['code'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.secondary_standard">
                                            <option value="">Select...</option>
                                            @foreach($standards as $std)
                                                <option value="{{ $std['id'] }}">{{ $std['code'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                
                                {{-- Disposal Date --}}
                                <td>
                                    <input type="date" class="form-control form-control-sm modern-input" 
                                           wire:model.defer="sampleForms.{{ $index }}.disposal_date"
                                           @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                                </td>
                                
                                {{-- Storage --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ collect($storageLocations)->firstWhere('id', $sampleForm['storage_location'])['name'] ?? '-' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.storage_location">
                                            <option value="">Select...</option>
                                            @foreach($storageLocations as $store)
                                                <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                
                                {{-- Storage Slot --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm modern-input" 
                                           wire:model.defer="sampleForms.{{ $index }}.storage_slot" 
                                           placeholder="Slot..."
                                           @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                                </td>
                                
                                {{-- Quantity --}}
                                <td>
                                    <input type="number" class="form-control form-control-sm modern-input" 
                                           wire:model.defer="sampleForms.{{ $index }}.sample_quantity" 
                                           step="0.01" min="0"
                                           @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                                </td>
                                
                                {{-- Unit of Measure --}}
                                <td>
                                    @if($isReadOnly)
                                        <input type="text" class="form-control form-control-sm readonly-input" 
                                               value="{{ $sampleForm['unit_of_measure'] ?? 'ml' }}" 
                                               readonly>
                                    @else
                                        <select class="form-control form-control-sm modern-select" 
                                                wire:model.defer="sampleForms.{{ $index }}.unit_of_measure">
                                            @foreach($unitsOfMeasure as $unit)
                                                <option value="{{ $unit }}">{{ $unit }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="text-center text-muted py-4">
                                    <i class="mdi mdi-information-outline"></i> No samples configured yet. Click "Add" to create samples.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Delete Sample Confirmation Modal --}}
    @if($showDeleteSampleModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Sample</h5>
                        <button type="button" class="close" wire:click="cancelDeleteSample">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this sample? This action cannot be undone.</p>
                        @if(isset($sampleForms[$deletingSampleIndex]) && $sampleForms[$deletingSampleIndex]['id'])
                            <p class="text-warning"><i class="mdi mdi-alert"></i> This sample is saved in the database and will be permanently deleted.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDeleteSample">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteSample" wire:loading.attr="disabled">
                            <span wire:loading.remove>Delete</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Staging Modal --}}
    @if($showEditModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="updateStaging">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Staging Detail</h5>
                            <button type="button" class="close" wire:click="cancelEdit">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Company Sub Unit</label>
                                <input type="text" class="form-control" wire:model="stagingForm.company_sub_unit_name">
                                @error('stagingForm.company_sub_unit_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Analysis Types (Comma separated)</label>
                                <input type="text" class="form-control" wire:model="stagingForm.analysis_type_names">
                                @error('stagingForm.analysis_type_names') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control" wire:model="stagingForm.quantity" min="1">
                                @error('stagingForm.quantity') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancelEdit">Close</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>Save Changes</span>
                                <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Staging Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Staging Detail</h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this staging record? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteStaging" wire:loading.attr="disabled">
                            <span wire:loading.remove>Delete</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- View Parameters Modal (Phase 5) --}}
    @if($showParametersModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-chart-box"></i> Parameters for Sample: <strong>{{ $selectedSampleCode }}</strong>
                        </h5>
                        <button type="button" class="close text-white" wire:click="cancelViewParameters">
                            <span>&times;</span>
                        </button>
                    </div>
                    
                    {{-- Loading Indicator --}}
                    <div wire:loading wire:target="viewParameters" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading parameters...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading parameters...</p>
                    </div>
                    
                    <div wire:loading.remove wire:target="viewParameters" class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        @if(!empty($sampleParameters))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" style="font-size: 0.85rem;">
                                    <thead class="thead-dark sticky-top">
                                        <tr>
                                            <th style="min-width: 100px;">Sample</th>
                                            <th style="min-width: 100px;">Analysis Type</th>
                                            <th style="min-width: 150px;">Analyte</th>
                                            <th style="min-width: 80px;">Symbol</th>
                                            <th style="min-width: 100px;">Result</th>
                                            @if($uncertaintyRequired)
                                            <th style="min-width: 80px;">M.U.</th>
                                            @endif
                                            <th style="min-width: 150px;">Standard</th>
                                            <th style="min-width: 100px;">Remark</th>
                                            <th style="min-width: 100px;">Unit</th>
                                            <th style="min-width: 120px;">Operator</th>
                                            <th style="min-width: 120px;">Method</th>
                                            <th style="min-width: 120px;">LTM</th>
                                            <th style="min-width: 120px;">Equipment</th>
                                            <th style="min-width: 80px; text-align: center;">Sub.</th>
                                            <th style="min-width: 80px; text-align: center;">Accr.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($parametersForm as $id => $param)
                                            <tr wire:key="param-{{ $id }}">
                                                <td><strong>{{ $param['sample_code'] }}</strong></td>
                                                <td>{{ $param['analysis_type'] }}</td>
                                                <td>
                                                    <strong>{{ $param['analyte_code'] }}</strong><br>
                                                    <small class="text-muted">{{ $param['analyte_name'] }}</small>
                                                </td>
                                                <td>{{ $param['result_reporting_symbol'] ?? '-' }}</td>
                                                <td style="min-width: 120px;">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           wire:model.lazy="parametersForm.{{ $id }}.result"
                                                           x-data
                                                           x-on:change="
                                                                let val = $el.value;
                                                                if(val) {
                                                                    setTimeout(() => {
                                                                        let conf = prompt('Please confirm result for {{ $param['analyte_code'] }}:');
                                                                        if(conf != val) {
                                                                            alert('Result mismatch! Please re-enter.');
                                                                            $el.value = '';
                                                                            $el.dispatchEvent(new Event('change'));
                                                                        }
                                                                    }, 50);
                                                                }
                                                           "
                                                           placeholder="Result">
                                                </td>
                                                @if($uncertaintyRequired)
                                                <td style="min-width: 80px;">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           wire:model.defer="parametersForm.{{ $id }}.measure_uncertanity"
                                                           placeholder="M.U.">
                                                </td>
                                                @endif
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <small>{{ $param['standard_value'] }}</small>
                                                        <button type="button" wire:click.stop="openEditStandardModal({{ $id }}, 1)" class="btn btn-sm btn-link p-0 text-primary ml-1" title="Edit Main Standard" style="line-height: 1;" wire:loading.attr="disabled">
                                                            <i wire:loading.remove wire:target="openEditStandardModal({{ $id }}, 1)" class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                            <i wire:loading wire:target="openEditStandardModal({{ $id }}, 1)" class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                                        </button>
                                                    </div>
                                                    @if($param['sec_standard_value'])
                                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                                            <small class="text-info">{{ $param['sec_standard_value'] }}</small>
                                                            <button type="button" wire:click.stop="openEditStandardModal({{ $id }}, 2)" class="btn btn-sm btn-link p-0 text-info ml-1" title="Edit Secondary Standard" style="line-height: 1;" wire:loading.attr="disabled">
                                                                <i wire:loading.remove wire:target="openEditStandardModal({{ $id }}, 2)" class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                                <i wire:loading wire:target="openEditStandardModal({{ $id }}, 2)" class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="min-width: 110px;">
                                                    <select class="form-control form-control-sm" 
                                                            wire:model.defer="parametersForm.{{ $id }}.remark"
                                                            style="pointer-events: none; background-color: #e9ecef;">
                                                        <option value="">- Select -</option>
                                                        <option value="PASS">PASS</option>
                                                        <option value="FAIL">FAIL</option>
                                                    </select>
                                                </td>
                                                <td style="min-width: 120px;">
                                                    <select class="form-control form-control-sm" wire:model.defer="parametersForm.{{ $id }}.reporting_unit">
                                                        <option value="">- Unit -</option>
                                                        @foreach($modalLists['units'] as $unit)
                                                            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="min-width: 140px;">
                                                    <select class="form-control form-control-sm" wire:model.defer="parametersForm.{{ $id }}.operator_id">
                                                        <option value="">- Operator -</option>
                                                        @foreach($modalLists['operators'] as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="min-width: 140px;">
                                                    <select class="form-control form-control-sm" wire:model.defer="parametersForm.{{ $id }}.method_id">
                                                        <option value="">- Method -</option>
                                                        @foreach($modalLists['methods'] as $method)
                                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><small>{{ $param['ltm_method_name'] }}</small></td>
                                                <td style="min-width: 140px;">
                                                    <select class="form-control form-control-sm" wire:model.defer="parametersForm.{{ $id }}.equipment_id">
                                                        <option value="">- Equipment -</option>
                                                        @foreach($modalLists['equipments'] as $eq)
                                                            <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="custom-control custom-checkbox text-center">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               id="sub_{{ $id }}" 
                                                               wire:model.defer="parametersForm.{{ $id }}.subcontracted">
                                                        <label class="custom-control-label" for="sub_{{ $id }}"></label>
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="custom-control custom-checkbox text-center">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               id="accr_{{ $id }}" 
                                                               wire:model.defer="parametersForm.{{ $id }}.accredited">
                                                        <label class="custom-control-label" for="accr_{{ $id }}"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i> No captured results found for this sample.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelViewParameters">
                            <i class="mdi mdi-close"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveParameters">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Comments & Interpretations Modal --}}
    @if($showCommentsModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="saveComments">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-file-document-edit"></i> Comments & Interpretations
                            </h5>
                            <button type="button" class="close" wire:click="cancelComments">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Comments</label>
                                <textarea class="form-control" wire:model.defer="commentsForm.header_body" 
                                        placeholder="Comments..." rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Recommendations / Interpretations</label>
                                <textarea class="form-control" wire:model.defer="commentsForm.main_body" 
                                        placeholder="Recommendations / Interpretations..." rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea class="form-control" wire:model.defer="commentsForm.notes_body" 
                                        placeholder="Notes..." rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="" class="control-label">Scope</label>
                                <select wire:model.defer="commentsForm.batch_comment_scope" class="form-control">
                                    <option value="1">Concatenate</option>
                                    <option value="2">Overwrite</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info btn-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="mdi mdi-content-save"></i> Save</span>
                                <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                            </button>
                            <button type="button" class="btn btn-default btn-sm" wire:click="cancelComments">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Interlab Transfer Modal --}}
    @if($showInterlabModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="saveInterlabLog">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">
                                <i class="mdi mdi-swap-horizontal-bold"></i> Initiate Inter Lab Transfer
                                <small class="ml-2 text-muted">Sample: {{ $interlabSampleCode }}</small>
                            </h5>
                            <button type="button" class="close" wire:click="cancelInterlab">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>To Lab <sup class="text-danger">*</sup></label>
                                <select class="form-control" wire:model.defer="interlabForm.to_lab_section_id" required>
                                    <option value="">Select Lab...</option>
                                    @foreach($labSections as $lab)
                                        <option value="{{ $lab['id'] }}">{{ $lab['code'] }} - {{ $lab['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('interlabForm.to_lab_section_id') 
                                    <small class="text-danger">{{ $message }}</small> 
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Quantity <sup class="text-danger">*</sup></label>
                                <input type="number" class="form-control" 
                                    wire:model.defer="interlabForm.quantity" 
                                    placeholder="Quantity..." 
                                    step="0.01" min="0" required>
                                @error('interlabForm.quantity') 
                                    <small class="text-danger">{{ $message }}</small> 
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expected Date</label>
                                        <input type="date" class="form-control" 
                                            wire:model.defer="interlabForm.expected_date">
                                        @error('interlabForm.expected_date') 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Preliminary Date</label>
                                        <input type="date" class="form-control" 
                                            wire:model.defer="interlabForm.prelim_date">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea class="form-control" wire:model.defer="interlabForm.remarks" 
                                        placeholder="Remarks..." rows="3"></textarea>
                            </div>
                        </div>
                    <div class="modal-footer">
                            <button type="submit" class="btn btn-warning btn-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="mdi mdi-swap-horizontal-bold"></i> Initiate</span>
                                <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Initiating...</span>
                            </button>
                            <button type="button" class="btn btn-default btn-sm text-danger" wire:click="cancelInterlab">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <style>
        /* Modern Select Styling for Edit Mode */
        .modern-select {
            border: 2px solid #e9ecef !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            font-size: 13px !important;
            color: #495057 !important;
            transition: all 0.2s ease !important;
            background-color: #fff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }
        
        .modern-select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.15) !important;
            outline: none !important;
        }
        
        .modern-select:hover:not([readonly]) {
            border-color: #b8c5d6 !important;
        }
        
        /* Read-only input styling */
        .readonly-input {
            background-color: #f8f9fa !important;
            border: 1px solid #e9ecef !important;
            color: #495057 !important;
            cursor: not-allowed;
        }
        
        /* Analysis badge styling */
        .analysis-badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 6px;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        /* Table header styling */
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #495057;
            padding: 12px 8px;
        }
        
        /* Table row hover effect */
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Form control improvements */
        .form-control-sm.modern-input {
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        
        .form-control-sm.modern-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.15);
        }
        
        /* Edit button styling */
        .btn-link {
            text-decoration: none !important;
        }
        
        .btn-link:hover {
            opacity: 0.7;
        }
        
        /* Required field indicator */
        sup.text-danger {
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Scrollbar styling for table */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Tag Select Container Styling (for Analysis Type Dropdown) */
        .tag-select-container {
            position: relative;
            cursor: text;
        }
        
        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .tag-select-input:hover {
            border-color: #007bff;
        }
        
        .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
        
        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .tag-badge i:hover {
            opacity: 1;
        }
        
        .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
        }
        
        .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -2px;
        }
        
        .tag-dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .tag-dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .tag-dropdown-item:last-child {
            border-bottom: none;
        }
        
        .tag-dropdown-create {
            background-color: #f8f9fa;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tag-dropdown-create:hover {
            background-color: #e9ecef;
        }
        
        .tag-dropdown-divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 4px 0;
        }
    </style>

    <!-- Edit Standard Modal -->
    
    <div class="modal fade" id="editStandardModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Standard: {{ $editingStandardData['analyte_name'] }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>Updating this standard will affect the master setup for this analyte.</small>
                    </div>
                    
                    <div class="form-group mb-2">
                        <label class="text-muted">Previous Value</label>
                        <input type="text" class="form-control form-control-sm" readonly value="{{ $editingStandardData['previous_value'] }}">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="d-block">Standard Value Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" wire:model="editingStandardData.standard_value_type" value="1" id="svt_range">
                            <label class="form-check-label" for="svt_range">Use Range</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" wire:model="editingStandardData.standard_value_type" value="2" id="svt_value">
                            <label class="form-check-label" for="svt_value">Use Value</label>
                        </div>
                    </div>

                    @if($editingStandardData['standard_value_type'] == 1)
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Min</label>
                                    <input type="text" class="form-control" wire:model.defer="editingStandardData.min" placeholder="Min">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Max</label>
                                    <input type="text" class="form-control" wire:model.defer="editingStandardData.max" placeholder="Max">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <label>Value Type</label>
                            <select class="form-control" wire:model.defer="editingStandardData.standard_valuetype">
                                <option value="">- Select -</option>
                                @foreach($standardValueOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Limit Measure</label>
                                    <select class="form-control" wire:model.defer="editingStandardData.limit_measure">
                                        <option value="">- Choose -</option>
                                        <option value="Max">Max</option>
                                        <option value="Min">Min</option>
                                        <option value="less_than">&lt; (Less Than)</option>
                                        <option value="greater_than">&gt; (Greater Than)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Value</label>
                                    <input type="text" class="form-control" wire:model.defer="editingStandardData.value" placeholder="Value">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="saveStandardLimit">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    

    <script>
        console.log('Samples.blade.php script loaded');
        // alert('Samples script loaded'); // Uncomment if console is hard to reach
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized listener registered');
            Livewire.on('show-edit-standard-modal', () => {
                console.log('Event received: show-edit-standard-modal');
                alert('Event Received: show-edit-standard-modal');
                $('#editStandardModal').modal('show');
            });
            Livewire.on('hide-edit-standard-modal', () => {
                $('#editStandardModal').modal('hide');
            });
        });
    </script>
</div>




{{-- JavaScript bridge for assign samples modal (maintains compatibility with existing implementation) --}}
@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('openAssignModal', (event) => {
        const stagingId = event.stagingId;
        const headerId = event.headerId;
        
        // Call the existing JavaScript function if it exists
        if (typeof loadAssignmentData === 'function') {
            loadAssignmentData(stagingId, headerId);
        } else {
            console.error('loadAssignmentData function not found. Make sure the original JavaScript is loaded.');
        }
    });
});
</script>
@endpush



