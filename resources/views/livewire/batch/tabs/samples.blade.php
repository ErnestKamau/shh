<div>
    <div wire:ignore>
        <script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
    </div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="mdi mdi-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    {{-- Missing Worksheet Results Alert (for parameters with worksheets but no worksheet data) --}}
    @if(!empty($missingWorksheetParameters) && is_array($missingWorksheetParameters))
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <i class="mdi mdi-alert-decagram"></i>
            <strong>Missing worksheet results detected:</strong>
            <ul class="mb-0 mt-1">
                @foreach($missingWorksheetParameters as $item)
                    <li>
                        {{ $item['worksheet_name'] ?? 'Worksheet' }} &mdash;
                        {{ $item['parameter_name'] ?? 'Parameter' }}
                    </li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Incomplete Captured Results Alert (no numeric/text result or "No attachment") --}}
    @if(!empty($incompleteCapturedResults) && is_array($incompleteCapturedResults))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="mdi mdi-alert"></i>
            <strong>Unfinished / missing results detected:</strong>
            <ul class="mb-0 mt-1">
                @foreach($incompleteCapturedResults as $item)
                    <li>
                        Item {{ $item['sample_code'] ?? 'N/A' }} &mdash;
                        {{ $item['analysis_type'] ?? 'Analysis' }} /
                        {{ $item['parameter'] ?? 'Parameter' }}
                        ({{ $item['status'] ?? 'incomplete' }})
                    </li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Unprocessed Staging Data Section --}}
    @if(isset($batch->sample_detail_processed) && $batch->sample_detail_processed == 0)
    <div class="workflow-board-panel mb-4">
        <div class="workflow-board-panel-header"
            style="background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%); border-bottom: 1px solid #fcd34d;">
            <h5><i class="mdi mdi-clipboard-alert"></i> Unprocessed staging data</h5>
            <button type="button" wire:click="addStaging" class="btn btn-success btn-sm btn-action-sm">
                <i class="mdi mdi-plus"></i> Add staging record
            </button>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <div class="table-responsive">
                <table class="table table-sm workflow-table">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Specimen Type</th>
                            <th>Company Sub Unit</th>
                            <th>Analysis Types</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batch->stagingDetails as $staging)
                        @if(!$staging->is_processed)
                        <tr>
                            <td>
                                <button type="button" wire:click="assignSamples({{ $staging->id }})"
                                    class="btn btn-sm btn-primary" wire:loading.attr="disabled"
                                    wire:target="assignSamples({{ $staging->id }})">
                                    <span wire:loading.remove wire:target="assignSamples({{ $staging->id }})">
                                        <i class="mdi mdi-checkbox-multiple-marked"></i> Assign Samples
                                    </span>
                                    <span wire:loading wire:target="assignSamples({{ $staging->id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i> Processing...
                                    </span>
                                </button>

                                <button type="button" wire:click="editStaging({{ $staging->id }})"
                                    class="btn btn-sm btn-info">
                                    <i class="mdi mdi-pencil"></i>
                                </button>

                                <button type="button" wire:click="confirmDeleteStaging({{ $staging->id }})"
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
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No staging records found. Click "Add Staging
                                Record" to create one.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Sample Configuration Form (Livewire-driven) --}}
    @if(!isset($batch->sample_detail_processed) || $batch->sample_detail_processed == 1)
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <h5><i class="mdi mdi-flask-outline"></i> Samples configuration</h5>
                <button type="button" wire:click="saveSamples" class="btn btn-danger btn-sm btn-action-sm text-white"
                    wire:loading.attr="disabled" style="height: auto; min-height: 32px;">
                    <span wire:loading.remove><i class="mdi mdi-content-save"></i> Save</span>
                    <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                </button>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                @if(in_array($batch->status ?? '', ['Samples Reception', 'Samples En-Route']))
                <button type="button" wire:click="addSample" class="btn btn-success btn-sm btn-action-sm"
                    wire:loading.attr="disabled">
                    <i class="mdi mdi-plus"></i> Add
                </button>
                @endif
                <button type="button" onclick="duplicateSelected()" class="btn btn-primary btn-sm btn-action-sm"
                    wire:loading.attr="disabled">
                    <i class="mdi mdi-content-duplicate"></i> Duplicate
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top" style="overflow-x: auto;">
        <div class="table-responsive">
            <table class="table table-bordered table-sm workflow-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all-samples" title="Select All">
                        </th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                        <th style="min-width: 100px;">Code</th>
                        <th style="min-width: 150px;">Analysis<sup class="text-danger">*</sup></th>
                        <th style="min-width: 120px;">Lab<sup class="text-danger">*</sup></th>
                        <th style="min-width: 120px;">Condition<sup class="text-danger">*</sup></th>
                        <th style="min-width: 150px;">
                            Sample Point<sup class="text-danger">*</sup>
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('sample_point_id', null)" title="Add New Sample Point">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 150px;">
                            Product<sup class="text-danger">*</sup>
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('company_product_id', null)" title="Add New Product">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 150px;">Description</th>
                        <th style="min-width: 100px;">Time Sampled</th>
                        <th style="min-width: 120px;">
                            Main Std<sup class="text-danger">*</sup>
                        </th>
                        <th style="min-width: 120px;">
                            Secondary Std
                        </th>
                        <th style="min-width: 110px;">Disposal Date</th>
                        <th style="min-width: 120px;">
                            Storage
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('store_id', null)" title="Add New Storage Location">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 80px;">Slot</th>
                        <th style="min-width: 80px;">Quantity</th>
                        <th style="min-width: 80px;">
                            UoM
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('reporting_unit_id', null)" title="Add New Unit of Measure">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sampleForms as $index => $sampleForm)
                    @php
                    $isEditing = $editingRowIndex === $index;
                    $isReadOnly = !$isEditing;
                    @endphp
                    <tr wire:key="sample-row-{{ $index }}"
                        class="{{ in_array($index, $selectedRows) ? 'table-active' : '' }}">
                        {{-- Selection Checkbox --}}
                        <td class="text-center">
                            <input type="checkbox" wire:click="toggleRowSelection({{ $index }})" {{ in_array($index, $selectedRows) ? 'checked' : '' }} class="sample-row-checkbox">
                        </td>

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
                                    class="btn btn-sm btn-icon btn-light text-success mx-1"
                                    title="Comments & Interpretations">
                                    <i class="mdi mdi-comment-text"></i>
                                </button>
                                @endif

                                {{-- Interlab Button --}}
                                @if($sampleForm['id'])
                                <button type="button"
                                    wire:click="openInterlabModal({{ $sampleForm['id'] }}, '{{ $sampleForm['sample_code'] }}')"
                                    class="btn btn-sm btn-icon btn-light text-warning mx-1"
                                    title="Initiate Inter Lab Transfer">
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
                                value="{{ $sampleForm['sample_code'] }}" readonly
                                style="background: #f5f5f5; font-weight: bold;">
                        </td>

                        {{-- Analysis Types (Searchable Multi-Select) --}}
                        <td>
                            @if($isReadOnly)
                            <div class="form-control form-control-sm readonly-input"
                                style="height: auto; min-height: 31px;">
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
                                    <input type="text" wire:model.live="analysisTypeSearch" class="tag-input"
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
                                            <span>{{ $type['name'] }} <small
                                                    class="text-muted">({{ $type['code'] }})</small></span>
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
                                value="{{ collect($conditions)->firstWhere('id', $sampleForm['sample_condition_id'])['name'] ?? '-' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select"
                                wire:model.defer="sampleForms.{{ $index }}.sample_condition_id" required>
                                <option value="">Select...</option>
                                @foreach($conditions as $condition)
                                <option value="{{ $condition['id'] }}">{{ $condition['name'] }}</option>
                                @endforeach
                            </select>
                            @error("sampleForms.$index.sample_condition_id")
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
                            <div class="d-flex align-items-start gap-2">
                                <div class="p-2 border rounded flex-grow-1" style="background: #f8f9fa; min-height: 31px; font-size: 0.875rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {!! $sampleForm['comments'] ?? '<span class="text-muted">No comments</span>' !!}
                                </div>
                                @if(!$isReadOnly)
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openCommentModal({{ $index }})">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                @endif
                            </div>
                        </td>

                        {{-- Time Sampled --}}
                        <td>
                            <input type="time" class="form-control form-control-sm modern-input"
                                wire:model.defer="sampleForms.{{ $index }}.time_sampled" @if($isReadOnly) readonly
                                style="background: #f8f9fa;" @endif>
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
                                wire:model.defer="sampleForms.{{ $index }}.disposal_date" @if($isReadOnly) readonly
                                style="background: #f8f9fa;" @endif>
                        </td>

                        {{-- Storage --}}
                        <td>
                            @if($isReadOnly)
                            <input type="text" class="form-control form-control-sm readonly-input"
                                value="{{ collect($storageLocations)->firstWhere('id', $sampleForm['store_id'])['name'] ?? '-' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select"
                                wire:model.defer="sampleForms.{{ $index }}.store_id">
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
                                wire:model.defer="sampleForms.{{ $index }}.store_slot_id" placeholder="Slot..."
                                @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                        </td>

                        {{-- Quantity --}}
                        <td>
                            <input type="number" class="form-control form-control-sm modern-input"
                                wire:model.defer="sampleForms.{{ $index }}.quantity" step="0.01" min="0"
                                @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                        </td>

                        {{-- Unit of Measure --}}
                        <td>
                            @if($isReadOnly)
                            <input type="text" class="form-control form-control-sm readonly-input"
                                value="{{ collect($unitsOfMeasure)->firstWhere('id', $sampleForm['reporting_unit_id'])['name'] ?? '' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select"
                                wire:model.defer="sampleForms.{{ $index }}.reporting_unit_id">
                                <option value="">Select...</option>
                                @foreach($unitsOfMeasure as $unit)
                                <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                @endforeach
                            </select>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="18" class="text-center text-muted py-4">
                            <i class="mdi mdi-information-outline"></i> No items configured yet. Click "Add" to create
                            entries.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
    @endif

    {{-- Delete Sample Confirmation Modal --}}
    @if($showDeleteSampleModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="position: relative;">
                @if($toastMessage)
                    <div class="position-absolute" style="top: 10px; right: 10px; left: 10px; z-index: 2050;">
                        <div class="alert alert-{{ $toastType }} alert-dismissible fade show shadow-sm mb-2" role="alert">
                            <i class="mdi mdi-information-outline"></i> {{ $toastMessage }}
                            <button type="button" class="close" aria-label="Close" wire:click="$set('toastMessage', '')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                @endif
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sample</h5>
                    <button type="button" class="close" wire:click="cancelDeleteSample">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this sample? This action cannot be undone.</p>
                    @if(isset($sampleForms[$deletingSampleIndex]) && $sampleForms[$deletingSampleIndex]['id'])
                    <p class="text-warning"><i class="mdi mdi-alert"></i> This sample is saved in the database and will
                        be permanently deleted.</p>
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
            <div class="modal-content" style="position: relative;">
                @if($toastMessage)
                    <div class="position-absolute" style="top: 10px; right: 10px; left: 10px; z-index: 2050;">
                        <div class="alert alert-{{ $toastType }} alert-dismissible fade show shadow-sm mb-2" role="alert">
                            <i class="mdi mdi-information-outline"></i> {{ $toastMessage }}
                            <button type="button" class="close" aria-label="Close" wire:click="$set('toastMessage', '')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                @endif
                <form wire:submit.prevent="updateStaging">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingStagingId ? 'Edit' : 'Add' }} Staging Detail</h5>
                        <button type="button" class="close" wire:click="cancelEdit">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Company Sub Unit (Searchable) --}}
                        <div class="form-group mb-3">
                            <label class="form-label">Company Sub Unit <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click="$set('showSubUnitDropdown', true)"
                                wire:click.outside="$set('showSubUnitDropdown', false)">
                                <div class="tag-select-input">
                                    @if($stagingForm['company_sub_unit_name'])
                                    <span class="tag-badge">
                                        {{ $stagingForm['company_sub_unit_name'] }}
                                        <i class="mdi mdi-close-circle"
                                            wire:click.stop="$set('stagingForm.company_sub_unit_id', null); $set('stagingForm.company_sub_unit_name', '')"></i>
                                    </span>
                                    @endif

                                    <input type="text" wire:model.live="subUnitSearch" class="tag-input"
                                        placeholder="{{ $stagingForm['company_sub_unit_name'] ? '' : 'Search sub units...' }}"
                                        autocomplete="off">
                                </div>

                                @if($showSubUnitDropdown)
                                <div class="tag-dropdown">
                                    @php $filteredSubUnits = $this->getFilteredSubUnits(); @endphp
                                    @if(count($filteredSubUnits) > 0)
                                    @foreach($filteredSubUnits as $unit)
                                    <div class="tag-dropdown-item" wire:click.stop="selectSubUnit({{ $unit['id'] }})">
                                        {{ $unit['name'] }}
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="p-2 text-center text-muted">No sub units found</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @error('stagingForm.company_sub_unit_id') <span
                                class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Specimen Type / Sample Type (Searchable) --}}
                        <div class="form-group mb-3">
                            <label class="form-label">Specimen Type <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click="$set('showSampleTypeDropdown', true)"
                                wire:click.outside="$set('showSampleTypeDropdown', false)">
                                <div class="tag-select-input">
                                    @if($stagingForm['sample_type_name'])
                                    <span class="tag-badge">
                                        {{ $stagingForm['sample_type_name'] }}
                                        <i class="mdi mdi-close-circle"
                                            wire:click.stop="$set('stagingForm.sample_type_id', null); $set('stagingForm.sample_type_name', '')"></i>
                                    </span>
                                    @endif

                                    <input type="text" wire:model.live="sampleTypeSearch" class="tag-input"
                                        placeholder="{{ $stagingForm['sample_type_name'] ? '' : 'Search specimen types...' }}"
                                        autocomplete="off">
                                </div>

                                @if($showSampleTypeDropdown)
                                <div class="tag-dropdown">
                                    @php $filteredSampleTypes = $this->getFilteredSampleTypes(); @endphp
                                    @if(count($filteredSampleTypes) > 0)
                                    @foreach($filteredSampleTypes as $type)
                                    <div class="tag-dropdown-item"
                                        wire:click.stop="selectSampleType({{ $type['id'] }})">
                                        {{ $type['name'] }}
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="p-2 text-center text-muted">No specimen types found</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @error('stagingForm.sample_type_id') <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Analysis Types (Searchable Multi-Select) --}}
                        <div class="form-group mb-3">
                            <label class="form-label">Analysis Types <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click="$set('showStagingAnalysisTypeDropdown', true)"
                                wire:click.outside="$set('showStagingAnalysisTypeDropdown', false)">
                                <div class="tag-select-input">
                                    @if(count($stagingForm['analysis_type_ids']) > 0)
                                    @foreach($analysisTypes as $type)
                                    @if(in_array($type['id'], $stagingForm['analysis_type_ids']))
                                    <span class="tag-badge">
                                        {{ $type['name'] }}
                                        <i class="mdi mdi-close-circle"
                                            wire:click.stop="toggleStagingAnalysisType({{ $type['id'] }})"></i>
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif

                                    <input type="text" wire:model.live="stagingAnalysisTypeSearch" class="tag-input"
                                        placeholder="{{ count($stagingForm['analysis_type_ids']) > 0 ? '' : 'Search analysis types...' }}"
                                        autocomplete="off">
                                </div>

                                @if($showStagingAnalysisTypeDropdown)
                                <div class="tag-dropdown">
                                    @php $filteredStagingTypes = $this->getFilteredStagingAnalysisTypes(); @endphp
                                    @if(count($filteredStagingTypes) > 0)
                                    @foreach($filteredStagingTypes as $type)
                                    <div class="tag-dropdown-item"
                                        wire:click.stop="toggleStagingAnalysisType({{ $type['id'] }})">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span>{{ $type['name'] }} <small
                                                    class="text-muted">({{ $type['code'] }})</small></span>
                                            @if(in_array($type['id'], $stagingForm['analysis_type_ids']))
                                            <i class="mdi mdi-check text-success"></i>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="p-2 text-center text-muted">No analysis types found</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @error('stagingForm.analysis_type_ids') <span
                                class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" class="form-control" wire:model="stagingForm.quantity" min="1">
                            @error('stagingForm.quantity') <span class="text-danger small">{{ $message }}</span>
                            @enderror
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
                    <button type="button" class="btn btn-danger" wire:click="deleteStaging"
                        wire:loading.attr="disabled">
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
                        <i class="mdi mdi-chart-box"></i> Parameters for Sample:
                        <strong>{{ $selectedSampleCode }}</strong>
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

                <div wire:loading.remove wire:target="viewParameters" class="modal-body"
                    style="max-height: 75vh; overflow-y: auto;">
                    @if(!empty($sampleParameters))
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-hover"
                            style="font-size: 0.85rem;">
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
                                    <td style="min-width: 160px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm"
                                                wire:model.lazy="parametersForm.{{ $id }}.result" x-data
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
                                            @if(!empty($param['batch_attachment_url']) && strcasecmp($param['result'] ?? '', 'as attached') === 0)
                                            <div class="input-group-append">
                                                <a href="{{ $param['batch_attachment_url'] }}" target="_blank"
                                                    class="btn btn-outline-dark btn-sm"
                                                    data-toggle="tooltip"
                                                    title="View attached result">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
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
                                            @if($param['standard_id'])
                                            <button type="button" wire:click.stop="openEditStandardModal({{ $id }}, 1)"
                                                class="btn btn-sm btn-link p-0 text-secondary ml-1"
                                                title="Edit Main Standard" style="line-height: 1;"
                                                wire:loading.attr="disabled">
                                                <i wire:loading.remove wire:target="openEditStandardModal({{ $id }}, 1)"
                                                    class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                <i wire:loading wire:target="openEditStandardModal({{ $id }}, 1)"
                                                    class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                            </button>
                                            @endif
                                        </div>
                                        @if($param['sec_standard_value'])
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <small class="text-muted">{{ $param['sec_standard_value'] }}</small>
                                            @if($param['sec_standard_id'])
                                            <button type="button" wire:click.stop="openEditStandardModal({{ $id }}, 2)"
                                                class="btn btn-sm btn-link p-0 text-muted ml-1"
                                                title="Edit Secondary Standard" style="line-height: 1;"
                                                wire:loading.attr="disabled">
                                                <i wire:loading.remove wire:target="openEditStandardModal({{ $id }}, 2)"
                                                    class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                <i wire:loading wire:target="openEditStandardModal({{ $id }}, 2)"
                                                    class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                            </button>
                                            @endif
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
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.reporting_unit">
                                            <option value="">- Unit -</option>
                                            @foreach($modalLists['units'] as $unit)
                                            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.operator_id">
                                            <option value="">- Operator -</option>
                                            @foreach($modalLists['operators'] as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.method_id">
                                            <option value="">- Method -</option>
                                            @foreach($modalLists['methods'] as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><small>{{ $param['ltm_method_name'] }}</small></td>
                                    <td style="min-width: 140px;">
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.equipment_id">
                                            <option value="">- Equipment -</option>
                                            @foreach($modalLists['equipments'] as $eq)
                                            <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="custom-control custom-checkbox text-center">
                                            <input type="checkbox" class="custom-control-input" id="sub_{{ $id }}"
                                                wire:model.defer="parametersForm.{{ $id }}.subcontracted">
                                            <label class="custom-control-label" for="sub_{{ $id }}"></label>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="custom-control custom-checkbox text-center">
                                            <input type="checkbox" class="custom-control-input" id="accr_{{ $id }}"
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
                            <input type="number" class="form-control" wire:model.defer="interlabForm.quantity"
                                placeholder="Quantity..." step="0.01" min="0" required>
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
                                    <input type="date" class="form-control" wire:model.defer="interlabForm.prelim_date">
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
                        <button type="button" class="btn btn-default btn-sm text-danger"
                            wire:click="cancelInterlab">Cancel</button>
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

        /* Edit Standard Modal - Grey Styling */
        .grey-input {
            border-color: #ced4da !important;
        }

        .grey-input:focus {
            border-color: #6c757d !important;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25) !important;
        }

        /* Grey radio button focus */
        .form-check-input:focus {
            border-color: #6c757d !important;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15) !important;
        }

        .form-check-input:checked {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
        }
    </style>

    <!-- Edit Standard Modal -->

    @if($showEditStandardModal)
    <div class="modal fade show" tabindex="-1" role="dialog"
        style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1600;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveStandardLimit">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title text-dark">Edit Standard: {{ $editingStandardData['analyte_name'] }}</h5>
                        <button type="button" class="close" wire:click="cancelEditStandardModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert" style="background-color: #f8f9fa; border-color: #dee2e6; color: #6c757d;">
                            <small>Updating this standard will affect the master setup for this analyte.</small>
                        </div>

                        <div class="form-group mb-2">
                            <label class="text-muted">Previous Value</label>
                            <input type="text" class="form-control form-control-sm" readonly
                                value="{{ $editingStandardData['previous_value'] }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="d-block">Standard Value Type</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                    wire:model.live="editingStandardData.standard_value_type" value="1" id="svt_range">
                                <label class="form-check-label" for="svt_range">Use Range</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                    wire:model.live="editingStandardData.standard_value_type" value="2" id="svt_value">
                                <label class="form-check-label" for="svt_value">Use Value</label>
                            </div>
                        </div>

                        @if($editingStandardData['standard_value_type'] == 1)
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Min</label>
                                    <input type="text" class="form-control grey-input"
                                        wire:model.defer="editingStandardData.min" placeholder="Min">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Max</label>
                                    <input type="text" class="form-control grey-input"
                                        wire:model.defer="editingStandardData.max" placeholder="Max">
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="form-group">
                            <label>Value Type</label>
                            <select class="form-control grey-input"
                                wire:model.defer="editingStandardData.standard_valuetype">
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
                                    <select class="form-control grey-input"
                                        wire:model.defer="editingStandardData.limit_measure">
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
                                    <input type="text" class="form-control grey-input"
                                        wire:model.defer="editingStandardData.value" placeholder="Value">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary"
                            wire:click="cancelEditStandardModal">Close</button>
                        <button type="submit" class="btn btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign Samples Modal -->
    <!-- Livewire Assign Samples Modal -->
    @if($showAssignSamplesModal)
    <style>
        .modal-xxl { max-width: 95%; }
        .tox-tinymce { border-radius: 8px !important; }
    </style>
    <div class="modal fade show" tabindex="-1" role="dialog"
        style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; position: relative;">
                @if($toastMessage)
                    <div class="position-absolute" style="top: 10px; right: 10px; left: 10px; z-index: 2050;">
                        <div class="alert alert-{{ $toastType }} alert-dismissible fade show shadow-sm mb-2" role="alert">
                            <i class="mdi mdi-information-outline"></i> {{ $toastMessage }}
                            <button type="button" class="close" aria-label="Close" wire:click="$set('toastMessage', '')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                @endif
                <div class="modal-header"
                    style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.8) 100%); border-radius: 15px 15px 0 0; border-bottom: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);">
                    <h5 class="modal-title" style="color: #495057; font-weight: 600;">
                        <i class="mdi mdi-clipboard-check text-primary"></i> Assign Samples
                    </h5>
                    <button type="button" class="close" wire:click="$set('showAssignSamplesModal', false)"
                        style="color: #495057; opacity: 0.7;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">

                    @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Batch & Customer Summary -->
                    <div class="workflow-board-filter-nested mb-3">
                        <div class="p-1">
                            {{-- Primary header: Lab No. + Customer --}}
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
                                <div class="mb-2 mb-md-0">
                                    <div class="text-muted small fw-bold text-uppercase">Lab No.</div>
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                        {{ $assignBatchCode }}
                                    </div>
                                </div>
                                <div class="text-md-right">
                                    <div class="text-muted small fw-bold text-uppercase">Customer</div>
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                        {{ $assignCustomer }}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            {{-- Secondary meta: Specimen, Sub Unit, Analysis --}}
                            <div class="row text-center mt-3">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <div class="text-muted small fw-bold text-uppercase">Specimen Type</div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                            {{ $assignSampleTypeName }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <div class="text-muted small fw-bold text-uppercase">Company Sub Unit</div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                            {{ $assignCompanySubUnitName }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <div class="text-muted small fw-bold text-uppercase">Analysis Types</div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                            {{ $assignAnalysisTypeNames }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Add New Sample Point Section -->
                    <div class="workflow-board-panel mb-3">
                        <div class="workflow-board-panel-header py-2">
                            <h6 class="mb-0" style="font-size: 0.9rem; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 8px;">
                                <i class="mdi mdi-plus-circle text-muted"></i> Add new sample point
                            </h6>
                        </div>
                        <div class="workflow-board-panel-body flush-top">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong>Sample Area</strong></label>
                                        <div class="input-group searchable-select-container" wire:click.away="$set('showAssignAreaDropdown', false)">
                                            <input type="text"
                                                class="form-control"
                                                placeholder="Search area..."
                                                wire:model.live="assignAreaSearch"
                                                autocomplete="off"
                                                wire:focus="$set('showAssignAreaDropdown', true)"
                                                wire:click="$set('showAssignAreaDropdown', true)">
                                            <div class="input-group-append">
                                                <button type="button"
                                                        class="btn btn-outline-primary"
                                                        wire:click="$set('showAddAreaModal', true)">
                                                    <i class="mdi mdi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="searchable-dropdown" style="max-height: 220px; overflow-y: auto; {{ $showAssignAreaDropdown ? '' : 'display:none;' }}">
                                            @foreach($assignAvailableAreas as $area)
                                                @php
                                                    $matches = !$assignAreaSearch || str_contains(strtolower($area['name']), strtolower($assignAreaSearch));
                                                    $isSelectedArea = in_array($area['id'], $assignNewAreaIds ?? [], true);
                                                @endphp
                                                @if($matches)
                                                    <div class="dropdown-item d-flex justify-content-between align-items-center"
                                                        wire:click="selectAssignArea({{ $area['id'] }})"
                                                        style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                        <span>{{ $area['name'] }}</span>
                                                        @if($isSelectedArea)
                                                            <i class="mdi mdi-check text-primary"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if(!empty($assignNewAreaIds))
                                            <div class="selected-items mt-2">
                                                @foreach($assignAvailableAreas as $area)
                                                    @if(in_array($area['id'], $assignNewAreaIds ?? [], true))
                                                        <span class="badge bg-info me-1 mb-1">
                                                            {{ $area['name'] }}
                                                            <i class="mdi mdi-close-circle ms-1"
                                                               wire:click="removeAssignArea({{ $area['id'] }})"
                                                               style="cursor: pointer;"></i>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label"><strong>Sample Point</strong></label>
                                        <div class="input-group searchable-select-container" wire:click.away="$set('showAssignPointDropdown', false)">
                                            <input type="text"
                                                class="form-control"
                                                placeholder="Search point..."
                                                wire:model.live="assignPointSearch"
                                                autocomplete="off"
                                                wire:focus="$set('showAssignPointDropdown', true)"
                                                wire:click="$set('showAssignPointDropdown', true)">
                                            <div class="input-group-append">
                                                <button type="button"
                                                        class="btn btn-outline-primary"
                                                        wire:click="$set('showAddPointModal', true)">
                                                    <i class="mdi mdi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="searchable-dropdown" style="max-height: 220px; overflow-y: auto; {{ $showAssignPointDropdown ? '' : 'display:none;' }}">
                                            @foreach($assignAvailablePoints as $point)
                                                @php
                                                    $matches = !$assignPointSearch || str_contains(strtolower($point['name']), strtolower($assignPointSearch));
                                                    $isSelected = in_array($point['id'], $assignNewPointIds ?? [], true);
                                                @endphp
                                                @if($matches)
                                                    <div class="dropdown-item d-flex justify-content-between align-items-center"
                                                        wire:click="selectAssignPoint({{ $point['id'] }})"
                                                        style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                        <span>{{ $point['name'] }}</span>
                                                        @if($isSelected)
                                                            <i class="mdi mdi-check text-primary"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if(!empty($assignNewPointIds))
                                            <div class="selected-items mt-2">
                                                @foreach($assignAvailablePoints as $point)
                                                    @if(in_array($point['id'], $assignNewPointIds ?? [], true))
                                                        <span class="badge bg-info me-1 mb-1">
                                                            {{ $point['name'] }}
                                                            <i class="mdi mdi-close-circle ms-1"
                                                               wire:click="removeAssignPoint({{ $point['id'] }})"
                                                               style="cursor: pointer;"></i>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <button wire:click="addCustomerSamplePoint" class="btn btn-primary btn-block"
                                            @if(empty($assignNewAreaIds) || empty($assignNewPointIds)) disabled @endif
                                            wire:loading.attr="disabled" wire:target="addCustomerSamplePoint">
                                            <i class="mdi mdi-plus"></i> Add to Customer
                                        </button>
                                        <div wire:loading wire:target="addCustomerSamplePoint"
                                            class="text-center text-primary small mt-1">
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span> Adding...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Information Table -->
                    <div class="workflow-board-panel mb-0">
                        <div class="workflow-board-panel-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                            <h6 class="mb-0" style="font-size: 0.9rem; font-weight: 600; color: #334155;">
                                <i class="mdi mdi-map-marker-multiple text-muted"></i> Sample points assignment
                            </h6>
                            <div>
                                <span class="badge badge-info mr-2">Total Qty: {{ $assignTotalQty }}</span>
                                <span class="badge badge-secondary mr-2">Assigned: {{ $assignCurrentTotalQty }}</span>
                                @if($assignTotalQty > 0)
                                    @php
                                        $remainingQty = max($assignTotalQty - $assignCurrentTotalQty, 0);
                                        $remainingClass = $assignCurrentTotalQty > $assignTotalQty ? 'badge-danger' : 'badge-success';
                                    @endphp
                                    <span class="badge {{ $remainingClass }}">Remaining: {{ $remainingQty }}</span>
                                @endif
                            </div>
                        </div>
                        @if($assignQtyError)
                            <div class="alert alert-danger mb-0">
                                <i class="mdi mdi-alert-circle"></i> {{ $assignQtyError }}
                            </div>
                        @endif
                        <div class="workflow-board-panel-body p-0">
                            <div class="table-responsive" 
                                x-data="{
                                    initMCE() {
                                        let tries = 0;
                                        const runner = () => {
                                            if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                                                tinymce.remove('.assign-comment-editor');
                                                tinymce.init({
                                                    selector: '.assign-comment-editor',
                                                    menubar: false,
                                                    statusbar: false,
                                                    height: 120,
                                                    toolbar: 'bold italic underline | bullist numlist | forecolor',
                                                    plugins: 'lists textcolor',
                                                    setup: function (editor) {
                                                        editor.on('change blur', function () {
                                                            editor.save();
                                                            var content = editor.getContent();
                                                            var pointId = document.getElementById(editor.id).getAttribute('data-point-id');
                                                            @this.set('assignComments.' + pointId, content);
                                                        });
                                                    }
                                                });
                                            } else {
                                                tries++;
                                                if(tries < 50) { 
                                                    setTimeout(runner, 200);
                                                }
                                            }
                                        };
                                        runner();
                                    }
                                }" 
                                x-init="initMCE()">
                                <table class="table table-hover workflow-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">Select</th>
                                            <th>Sample Point</th>
                                            <th style="width: 100px;">Quantity</th>
                                            <th style="width: 600px;">Sample Comments</th>
                                            <th style="width: 120px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assignAreas as $area)
                                        <tr>
                                            <td colspan="5" class="bg-light font-weight-bold pl-4">
                                                <i class="mdi mdi-map-marker text-primary"></i> Area:
                                                {{ $area['name'] }}
                                            </td>
                                        </tr>
                                        @foreach($area['sample_points'] as $index => $point)
                                        <tr>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="assign_point_{{ $point['id'] }}"
                                                        wire:model.live="assignSelectedPoints.{{ $point['id'] }}"
                                                        value="1">
                                                    <label class="custom-control-label"
                                                        for="assign_point_{{ $point['id'] }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $point['name'] }}</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="assignQuantities.{{ $point['id'] }}"
                                                    style="width: 80px;" min="1"
                                                    {{ empty($assignSelectedPoints[$point['id']]) ? 'disabled' : '' }}>
                                            </td>
                                            <td>
                                                <div wire:ignore>
                                                    <textarea class="form-control form-control-sm assign-comment-editor" 
                                                        id="assign_comment_{{ $point['id'] }}" 
                                                        data-point-id="{{ $point['id'] }}"></textarea>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No sample points available for this
                                                unit.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background-color: #f8f9fa; border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-secondary"
                        wire:click="$set('showAssignSamplesModal', false)">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="performAssignment"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="performAssignment" class="spinner-border spinner-border-sm"
                            role="status" aria-hidden="true"></span>
                        Assign Samples
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    @endif

    {{-- Rich Text Comment Modal --}}
    @if($showCommentModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1100;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Edit Sample Comment</h5>
                    <button type="button" class="close text-white" wire:click="$set('showCommentModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div wire:ignore 
                         x-data="{
                            initEditor() {
                                if (typeof tinymce !== 'undefined') {
                                    tinymce.remove('#comment-editor-main');
                                    tinymce.init({
                                        selector: '#comment-editor-main',
                                        menubar: false,
                                        statusbar: false,
                                        height: 300,
                                        toolbar: 'bold italic underline | bullist numlist | forecolor',
                                        plugins: 'lists textcolor',
                                        setup: function (editor) {
                                            editor.on('change blur', function () {
                                                editor.save();
                                                @this.set('tempCommentContent', editor.getContent());
                                            });
                                        }
                                    });
                                }
                            }
                         }" 
                         x-init="setTimeout(() => initEditor(), 100)">
                        <textarea id="comment-editor-main" class="form-control">{{ $tempCommentContent }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCommentModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveComment">Save Comment</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add New Sample Area Modal --}}
    @if($showAddAreaModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveNewArea">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="mdi mdi-plus"></i> Add New Sample Area</h5>
                        <button type="button" class="close text-white" wire:click="$set('showAddAreaModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Area Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newAreaCode"
                                placeholder="Enter area code, e.g. CR-01" required>
                            @error('newAreaCode') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Area Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newAreaName"
                                placeholder="Enter area name" required>
                            @error('newAreaName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" wire:model="newAreaDescription"
                                placeholder="Optional description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showAddAreaModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Area</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Add New Sample Point Modal --}}
    @if($showAddPointModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveNewPoint">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="mdi mdi-plus"></i> Add New Sample Point</h5>
                        <button type="button" class="close text-white" wire:click="$set('showAddPointModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Sample Point Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newPointCode"
                                placeholder="Enter point code, e.g. SP-01" required>
                            @error('newPointCode') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Sample Point Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newPointName"
                                placeholder="Enter point name" required>
                            @error('newPointName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showAddPointModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Point</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Add New Product Modal --}}
    @if($showAddProductModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveNewProduct">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="mdi mdi-plus"></i> Add New Product</h5>
                        <button type="button" class="close text-white" wire:click="$set('showAddProductModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newProductName"
                                placeholder="Enter product name" required>
                            @error('newProductName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showAddProductModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Product</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Add New Unit of Measure Modal --}}
    @if($showAddUomModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Unit of Measure</h5>
                    <button type="button" class="close" wire:click="$set('showAddUomModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>UoM Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="newUomName"
                            placeholder="e.g. ml, kg, L">
                        @error('newUomName') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        wire:click="$set('showAddUomModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveNewUom">Save UoM</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add New Storage Modal --}}
    @if($showAddStorageModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveNewStorage">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="mdi mdi-plus"></i> Add New Storage Location</h5>
                        <button type="button" class="close text-white" wire:click="$set('showAddStorageModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Storage Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newStorageName"
                                placeholder="Enter storage name" required>
                            @error('newStorageName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showAddStorageModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Storage</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
{{-- JavaScript for Sample Duplication --}}
<script>
    // Function to handle duplicate button click
    function duplicateSelected() {
        const count = prompt("Enter number of duplicates", 1);
        if (count !== null && count > 0) {
            @this.call('duplicateSelectedSamples', parseInt(count));
        }
    }

    // Select All functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all-samples');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.sample-row-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.click(); // Trigger Livewire event
                });
            });
        }
    });
</script>