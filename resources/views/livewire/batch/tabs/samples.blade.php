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
                            <th style="width: 30px;">👁</th>
                            <th style="width: 30px;">🗑</th>
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
                            <tr wire:key="sample-row-{{ $index }}">
                                {{-- View Parameters Icon --}}
                                <td class="text-center">
                                    @if($sampleForm['id'])
                                        <button type="button" wire:click="viewParameters('{{ $sampleForm['sample_code'] }}')" 
                                                class="btn btn-sm btn-link p-0" title="View Parameters">
                                            <i class="mdi mdi-eye text-info"></i>
                                        </button>
                                    @endif
                                </td>
                                
                                {{-- Delete Icon --}}
                                <td class="text-center">
                                    <button type="button" wire:click="confirmDeleteSample({{ $index }})" 
                                            class="btn btn-sm btn-link p-0 text-danger" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                                
                                {{-- Sample Code (readonly) --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           value="{{ $sampleForm['sample_code'] }}" 
                                           readonly style="background: #f5f5f5; font-weight: bold;">
                                </td>
                                
                                {{-- Analysis Types (Multiple Select) - wire:model triggers lab filtering --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model="sampleForms.{{ $index }}.analysis_type_id" 
                                            multiple required style="height: 60px;">
                                        @foreach($analysisTypes as $type)
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.analysis_type_id") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Lab Section --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.lab_id" required>
                                        <option value="">Select...</option>
                                        @foreach($labSections as $lab)
                                            <option value="{{ $lab['id'] }}">{{ $lab['code'] }} - {{ $lab['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.lab_id") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Condition --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.condition_id" required>
                                        <option value="">Select...</option>
                                        @foreach($conditions as $condition)
                                            <option value="{{ $condition['id'] }}">{{ $condition['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.condition_id") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Sample Point --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.sample_point_id" required>
                                        <option value="">Select...</option>
                                        @foreach($samplePoints as $point)
                                            <option value="{{ $point['id'] }}">{{ $point['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.sample_point_id") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Product --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.company_product_id" required>
                                        <option value="">Select...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.company_product_id") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Description --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           wire:model.defer="sampleForms.{{ $index }}.description" 
                                           placeholder="Description...">
                                </td>
                                
                                {{-- Time Sampled --}}
                                <td>
                                    <input type="time" class="form-control form-control-sm" 
                                           wire:model.defer="sampleForms.{{ $index }}.time_sampled">
                                </td>
                                
                                {{-- Main Standard --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.main_standard" required>
                                        <option value="">Select...</option>
                                        @foreach($standards as $std)
                                            <option value="{{ $std['id'] }}">{{ $std['code'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("sampleForms.$index.main_standard") 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </td>
                                
                                {{-- Secondary Standard --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.secondary_standard">
                                        <option value="">Select...</option>
                                        @foreach($standards as $std)
                                            <option value="{{ $std['id'] }}">{{ $std['code'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                {{-- Disposal Date --}}
                                <td>
                                    <input type="date" class="form-control form-control-sm" 
                                           wire:model.defer="sampleForms.{{ $index }}.disposal_date">
                                </td>
                                
                                {{-- Storage --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.storage_location">
                                        <option value="">Select...</option>
                                        @foreach($storageLocations as $store)
                                            <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                {{-- Storage Slot --}}
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           wire:model.defer="sampleForms.{{ $index }}.storage_slot" 
                                           placeholder="Slot...">
                                </td>
                                
                                {{-- Quantity --}}
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           wire:model.defer="sampleForms.{{ $index }}.sample_quantity" 
                                           step="0.01" min="0">
                                </td>
                                
                                {{-- Unit of Measure --}}
                                <td>
                                    <select class="form-control form-control-sm" 
                                            wire:model.defer="sampleForms.{{ $index }}.unit_of_measure">
                                        @foreach($unitsOfMeasure as $unit)
                                            <option value="{{ $unit }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="text-center text-muted py-4">
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
</div>

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
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    @if(!empty($sampleParameters))
                        @foreach($sampleParameters as $analysisName => $analytes)
                            <div class="mb-4">
                                <h6 class="bg-light p-2 border-bottom border-primary">
                                    <i class="mdi mdi-flask"></i> {{ $analysisName }}
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 15%;">Analyte Code</th>
                                                <th style="width: 40%;">Analyte Name</th>
                                                <th style="width: 15%;">Unit</th>
                                                <th style="width: 30%;">Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analytes as $analyte)
                                                <tr>
                                                    <td><strong>{{ $analyte['code'] }}</strong></td>
                                                    <td>{{ $analyte['name'] }}</td>
                                                    <td>{{ $analyte['unit'] }}</td>
                                                    <td><small>{{ $analyte['method'] }}</small></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> No parameters configured for this sample's analysis types.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelViewParameters">
                        <i class="mdi mdi-close"></i> Close
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
