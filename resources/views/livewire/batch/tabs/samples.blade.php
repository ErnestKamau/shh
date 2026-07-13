<div>
    <style>
        .sample-gw-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            text-decoration: none !important;
            margin: 0.15rem 0.25rem 0.15rem 0;
            white-space: nowrap;
        }
        .sample-gw-pill:hover {
            background: #dbeafe;
            color: #1e40af;
        }
        .sample-gw-pill--done {
            background: #ecfdf5;
            color: #047857;
            border-color: #d1fae5;
        }
        .sample-gw-pill--progress {
            background: #fff7ed;
            color: #c2410c;
            border-color: #ffedd5;
        }
        .sample-gw-timeline {
            list-style: none;
            margin: 0.75rem 0 0;
            padding: 0 0 0 0.25rem;
        }
        .sample-gw-timeline__item {
            display: flex;
            gap: 0.75rem;
            position: relative;
            padding-bottom: 1rem;
        }
        .sample-gw-timeline__item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 13px;
            top: 28px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .sample-gw-timeline__dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #e2e8f0;
            z-index: 1;
        }
        .sample-gw-timeline__dot--final {
            background: #d1fae5;
            color: #047857;
        }
        .sample-gw-timeline__card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            font-size: 0.8rem;
        }
        .sample-gw-timeline__title {
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.15rem;
        }
        .sample-gw-timeline__meta {
            color: #64748b;
            margin: 0;
            font-size: 0.75rem;
        }
        .sample-gw-holder {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            margin-bottom: 1rem;
            background: #fff;
        }
        .sample-gw-holder:last-child {
            margin-bottom: 0;
        }

        .batch-samples-panel.workflow-board-panel {
            overflow: visible;
        }

        .batch-samples-panel .workflow-board-panel-body {
            overflow-x: auto;
            overflow-y: visible;
        }
        .sample-gw-status {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
        }
        .sample-gw-status--in_progress { background: #fff7ed; color: #c2410c; }
        .sample-gw-status--completed { background: #ecfdf5; color: #047857; }
    </style>
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

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="mdi mdi-alert-circle"></i>
        <strong>Please fix the following before saving:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

    {{-- Incomplete Captured Results --}}
    @if(!empty($incompleteCapturedResults) && is_array($incompleteCapturedResults))
        <div class="mb-3">
            <button type="button"
                class="btn btn-danger btn-sm"
                wire:click="openIncompleteResultsModal">
                <i class="mdi mdi-alert"></i>
                Unfinished / missing results
                <span class="badge badge-light text-danger ml-1">{{ count($incompleteCapturedResults) }}</span>
            </button>
        </div>
    @endif

    @if($showIncompleteResultsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-alert"></i> Unfinished / missing results
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeIncompleteResultsModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @forelse($this->incompleteCapturedResultsGrouped as $sampleCode => $items)
                            <div class="mb-3">
                                <h6 class="font-weight-bold mb-2">{{ $sampleCode }}</h6>
                                <ul class="mb-0 pl-3 small">
                                    @foreach($items as $item)
                                        <li>
                                            {{ $item['analysis_type'] }} / {{ $item['parameter'] }}
                                            <span class="text-muted">({{ $item['status'] }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No missing results found.</p>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeIncompleteResultsModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sample Configuration Form (Livewire-driven) --}}
    <div class="workflow-board-panel batch-samples-panel">
        <div class="workflow-board-panel-header">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <h5><i class="mdi mdi-flask-outline"></i> Samples configuration</h5>
                <button type="button" wire:click="saveSamples" class="btn btn-danger btn-sm btn-action-sm text-white"
                    wire:loading.attr="disabled" wire:target="saveSamples" style="height: auto; min-height: 32px;">
                    <span wire:loading.remove wire:target="saveSamples"><i class="mdi mdi-content-save"></i> Save</span>
                    <span wire:loading wire:target="saveSamples"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                </button>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                @if(in_array($batch->status ?? '', ['Samples Reception', 'Samples En-Route']) || empty($sampleForms))
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
        <div class="workflow-board-panel-body flush-top">
        <div class="table-responsive">
            <table class="table table-bordered table-sm workflow-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all-samples" title="Select All">
                        </th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                        <th style="min-width: 100px;">Code</th>
                        <th style="min-width: 150px;">Matrix<sup class="text-danger">*</sup></th>
                        <th style="min-width: 150px;">Sample Type</th>
                        <th style="min-width: 120px;">Customer Sample ID</th>
                        <th style="min-width: 120px;">Lab<sup class="text-danger">*</sup></th>
                        <th style="min-width: 130px;">
                            Main Standard<sup class="text-danger">*</sup>
                        </th>
                        <th style="min-width: 130px;">
                            Secondary Standard
                        </th>
                        <th style="min-width: 120px;">Condition</th>
                        <th style="min-width: 150px;">
                            Sample Point
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('sample_point_id', null)" title="Add New Sample Point">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 150px;">
                            Sample photo
                        </th>
                        <th style="min-width: 150px;">Description</th>
                        <th style="min-width: 110px;">Disposal Date</th>
                        <th style="min-width: 120px;">
                            Storage
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('store_id', null)" title="Add New Storage Location">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 80px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sampleForms as $index => $sampleForm)
                    @php
                    $isEditing = $editingRowIndex === $index;
                    $isReadOnly = false;
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

                                @php $rowWorksheets = $this->worksheetsForSampleRow($index); @endphp
                                @if(count($rowWorksheets) > 0)
                                <button type="button" wire:click="openGroupedWorksheetsModal({{ $index }})"
                                    class="btn btn-sm btn-icon btn-light text-primary mx-1"
                                    title="Grouped worksheets ({{ count($rowWorksheets) }})">
                                    <i class="mdi mdi-folder-multiple-outline"></i>
                                </button>
                                @endif

                                {{-- Comment Button --}}
                                @if($sampleForm['id'])
                                <button type="button" wire:click="openCommentsModal('{{ $sampleForm['id'] }}')"
                                    class="btn btn-sm btn-icon btn-light text-success mx-1"
                                    title="Comments & Interpretations">
                                    <i class="mdi mdi-comment-text"></i>
                                </button>
                                @endif

                                {{-- Interlab Button --}}
                                @if($sampleForm['id'])
                                <button type="button"
                                    wire:click="openInterlabModal('{{ $sampleForm['id'] }}', '{{ $sampleForm['sample_code'] }}')"
                                    class="btn btn-sm btn-icon btn-light text-warning mx-1"
                                    title="Initiate Inter Lab Transfer">
                                    <i class="mdi mdi-swap-horizontal-bold"></i>
                                </button>
                                @endif

                                @if($isEditing)
                                <button type="button" wire:click="saveSample({{ $index }})"
                                    class="btn btn-sm btn-icon btn-light text-success mx-1" title="Save Row">
                                    <i class="mdi mdi-content-save"></i>
                                </button>
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

                        {{-- Matrix (analysis types) --}}
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
                                wire:click="$set('showAnalysisTypeDropdown.{{ $index }}', true)">
                                <div class="tag-select-input">
                                    @if(is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0)
                                    @foreach($analysisTypes as $type)
                                    @if(in_array($type['id'], $sampleForm['analysis_type_id']))
                                    <span class="tag-badge">
                                        {{ $type['name'] }}
                                        <i class="mdi mdi-close-circle"
                                            wire:click.stop="toggleAnalysisType({{ $index }}, '{{ $type['id'] }}')"></i>
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif

                                    <input type="text" wire:model.live="analysisTypeSearch" class="tag-input"
                                        placeholder="{{ (is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0) ? '' : 'Search matrix...' }}"
                                        autocomplete="off">
                                </div>

                                @if(isset($showAnalysisTypeDropdown[$index]) && $showAnalysisTypeDropdown[$index])
                                <div class="tag-dropdown" wire:click.outside="$set('showAnalysisTypeDropdown.{{ $index }}', false)">
                                    @php
                                    $filteredTypes = $this->getFilteredAnalysisTypes($index);
                                    @endphp
                                    @if(count($filteredTypes) > 0)
                                    @foreach($filteredTypes as $type)
                                    <div class="tag-dropdown-item"
                                        wire:click.stop="toggleAnalysisType({{ $index }}, '{{ $type['id'] }}')">
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
                                    <div class="p-3 text-center text-muted">No matrix options found</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @error("sampleForms.$index.analysis_type_id")
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                            @endif
                            @php $rowWorksheets = $this->worksheetsForSampleRow($index); @endphp
                            @if(count($rowWorksheets) > 0)
                                <div class="mt-1">
                                    @foreach($rowWorksheets as $ws)
                                        <a href="{{ $ws['capture_url'] }}"
                                           class="sample-gw-pill {{ $ws['run_status'] === 'completed' ? 'sample-gw-pill--done' : ($ws['run_status'] === 'in_progress' ? 'sample-gw-pill--progress' : '') }}"
                                           title="Open {{ $ws['holder_name'] }} — {{ implode(', ', $ws['analysis_names']) }}"
                                           target="_blank" rel="noopener">
                                            <i class="mdi mdi-folder-multiple-outline"></i>
                                            {{ Str::limit($ws['holder_name'], 22) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        {{-- Sample Type --}}
                        <td>
                            <select class="form-control form-control-sm modern-select"
                                wire:model.blur="sampleForms.{{ $index }}.sample_type_id">
                                <option value="">Select...</option>
                                @foreach($sampleTypes as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                            @error("sampleForms.$index.sample_type_id")
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </td>

                        {{-- Customer Sample ID --}}
                        <td>
                            <input type="text" class="form-control form-control-sm modern-input"
                                wire:model.blur="sampleForms.{{ $index }}.customer_sample_id"
                                placeholder="Customer sample ID...">
                        </td>

                        {{-- Lab Section --}}
                        <td>
                            @if($isReadOnly)
                            <input type="text" class="form-control form-control-sm readonly-input"
                                value="{{ collect($labSections)->firstWhere('id', $sampleForm['lab_id'])['name'] ?? '-' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select"
                                wire:model.blur="sampleForms.{{ $index }}.lab_id" required>
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

                        {{-- Main Standard --}}
                        <td>
                            <select class="form-control form-control-sm modern-select"
                                wire:model.blur="sampleForms.{{ $index }}.main_standard" required>
                                <option value="">Select...</option>
                                @foreach($standards as $std)
                                <option value="{{ $std['id'] }}">{{ $std['code'] }} - {{ $std['name'] }}</option>
                                @endforeach
                            </select>
                            @error("sampleForms.$index.main_standard")
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </td>

                        {{-- Secondary Standard --}}
                        <td>
                            <select class="form-control form-control-sm modern-select"
                                wire:model.blur="sampleForms.{{ $index }}.secondary_standard">
                                <option value="">Select...</option>
                                @foreach($standards as $std)
                                <option value="{{ $std['id'] }}">{{ $std['code'] }} - {{ $std['name'] }}</option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Condition --}}
                        <td>
                            @if($isReadOnly)
                            <input type="text" class="form-control form-control-sm readonly-input"
                                value="{{ collect($conditions)->firstWhere('id', $sampleForm['sample_condition_id'])['name'] ?? '-' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select"
                                wire:model.blur="sampleForms.{{ $index }}.sample_condition_id">
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
                                wire:model.blur="sampleForms.{{ $index }}.sample_point_id">
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

                        {{-- Sample photo --}}
                        <td>
                            @if($isReadOnly)
                                @if(!empty($sampleForm['photo_url']))
                                    <a href="{{ Storage::url($sampleForm['photo_url']) }}" target="_blank" class="btn btn-xs btn-outline-info" title="View Photo">
                                        <i class="mdi mdi-image"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                    <input type="file" class="form-control-file" style="font-size: 0.8rem; max-width: 150px;"
                                        wire:model="samplePhotos.{{ $index }}" accept="image/*">
                                    <div wire:loading wire:target="samplePhotos.{{ $index }}">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </div>
                                    @if(!empty($sampleForm['photo_url']))
                                        <a href="{{ Storage::url($sampleForm['photo_url']) }}" target="_blank" class="text-info" style="font-size: 0.8rem;">Current</a>
                                    @endif
                                </div>
                                @error('samplePhotos.'.$index) <small class="text-danger">{{ $message }}</small> @enderror
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

                        {{-- Disposal Date --}}
                        <td>
                            <input type="date" class="form-control form-control-sm modern-input"
                                wire:model="sampleForms.{{ $index }}.disposal_date" @if($isReadOnly) readonly
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
                                wire:model="sampleForms.{{ $index }}.store_id">
                                <option value="">Select...</option>
                                @foreach($storageLocations as $store)
                                <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                                @endforeach
                            </select>
                            @endif
                        </td>

                        {{-- Quantity --}}
                        <td>
                            <input type="number" class="form-control form-control-sm modern-input"
                                wire:model="sampleForms.{{ $index }}.quantity" step="0.01" min="0"
                                @if($isReadOnly) readonly style="background: #f8f9fa;" @endif>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16" class="text-center text-muted py-4">
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
                            <div class="tag-select-container" wire:click="$set('showSubUnitDropdown', true)">
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
                                <div class="tag-dropdown" wire:click.outside="$set('showSubUnitDropdown', false)">
                                    @php $filteredSubUnits = $this->getFilteredSubUnits(); @endphp
                                    @if(count($filteredSubUnits) > 0)
                                    @foreach($filteredSubUnits as $unit)
                                    <div class="tag-dropdown-item" wire:click.stop="selectSubUnit('{{ $unit['id'] }}')">
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
                            <div class="tag-select-container" wire:click="$set('showSampleTypeDropdown', true)">
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
                                <div class="tag-dropdown" wire:click.outside="$set('showSampleTypeDropdown', false)">
                                    @php $filteredSampleTypes = $this->getFilteredSampleTypes(); @endphp
                                    @if(count($filteredSampleTypes) > 0)
                                    @foreach($filteredSampleTypes as $type)
                                    <div class="tag-dropdown-item"
                                        wire:click.stop="selectSampleType('{{ $type['id'] }}')">
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
                            <div class="tag-select-container" wire:click="$set('showStagingAnalysisTypeDropdown', true)">
                                <div class="tag-select-input">
                                    @if(count($stagingForm['analysis_type_ids']) > 0)
                                    @foreach($analysisTypes as $type)
                                    @if(in_array($type['id'], $stagingForm['analysis_type_ids']))
                                    <span class="tag-badge">
                                        {{ $type['name'] }}
                                        <i class="mdi mdi-close-circle"
                                            wire:click.stop="toggleStagingAnalysisType('{{ $type['id'] }}')"></i>
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif

                                    <input type="text" wire:model.live="stagingAnalysisTypeSearch" class="tag-input"
                                        placeholder="{{ count($stagingForm['analysis_type_ids']) > 0 ? '' : 'Search analysis types...' }}"
                                        autocomplete="off">
                                </div>

                                @if($showStagingAnalysisTypeDropdown)
                                <div class="tag-dropdown" wire:click.outside="$set('showStagingAnalysisTypeDropdown', false)">
                                    @php $filteredStagingTypes = $this->getFilteredStagingAnalysisTypes(); @endphp
                                    @if(count($filteredStagingTypes) > 0)
                                    @foreach($filteredStagingTypes as $type)
                                    <div class="tag-dropdown-item"
                                        wire:click.stop="toggleStagingAnalysisType('{{ $type['id'] }}')">
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
    <div class="modal fade show d-block sample-parameters-modal" tabindex="-1" role="dialog"
        style="background: rgba(15, 23, 42, 0.45);" wire:keydown.escape.window="cancelViewParameters">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content sample-parameters-modal__content">
                <div class="modal-header border-0 sample-parameters-modal__header">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="mdi mdi-flask-outline text-primary"></i>
                            Parameters for sample
                        </h5>
                        <p class="text-muted small mb-0">
                            <span class="badge badge-light border font-weight-normal">{{ $selectedSampleCode }}</span>
                            @if(!empty($sampleParameters))
                                <span class="ml-1">{{ count($sampleParameters) }} parameter{{ count($sampleParameters) === 1 ? '' : 's' }}</span>
                            @endif
                        </p>
                    </div>
                    <button type="button" class="close" wire:click="cancelViewParameters" aria-label="Close">
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

                <div wire:loading.remove wire:target="viewParameters" class="modal-body sample-parameters-modal__body">
                    @if(!empty($sampleParameters))
                    <div class="table-responsive sample-parameters-modal__table-wrap">
                        <table class="table table-sm sample-parameters-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 100px;">Sample</th>
                                    <th style="min-width: 120px;">Analysis type</th>
                                    <th style="min-width: 150px;">Analyte</th>
                                    <th style="min-width: 80px;">Symbol</th>
                                    <th style="min-width: 100px;">Result</th>
                                    <th style="min-width: 145px;">Start date</th>
                                    <th style="min-width: 145px;">End date</th>
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
                                    <td class="sample-parameters-modal__analyte">
                                        <span class="font-weight-semibold d-block text-dark">{{ $param['analyte_name'] }}</span>
                                        @if(!empty($param['analyte_code']) && $param['analyte_code'] !== $param['analyte_name'])
                                            <small class="text-muted">{{ $param['analyte_code'] }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $param['result_reporting_symbol'] ?? '-' }}</td>
                                    <td style="min-width: 160px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text"
                                                class="form-control form-control-sm js-confirm-result"
                                                value="{{ $param['result'] ?? '' }}"
                                                placeholder="Result"
                                                data-row-id="{{ $id }}"
                                                data-analyte="{{ $param['analyte_name'] ?? 'analyte' }}"
                                                data-sample="{{ $param['sample_code'] ?? '' }}">
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
                                    <td style="min-width: 145px;">
                                        <input type="date"
                                            class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.start_analysis_date">
                                    </td>
                                    <td style="min-width: 145px;">
                                        <input type="date"
                                            class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.end_analysis_date">
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
                                            <button type="button" wire:click.stop="openEditStandardModal('{{ $id }}', 1)"
                                                class="btn btn-sm btn-link p-0 text-secondary ml-1"
                                                title="Edit Main Standard" style="line-height: 1;"
                                                wire:loading.attr="disabled">
                                                <i wire:loading.remove wire:target="openEditStandardModal('{{ $id }}', 1)"
                                                    class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                <i wire:loading wire:target="openEditStandardModal('{{ $id }}', 1)"
                                                    class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                            </button>
                                            @endif
                                        </div>
                                        @if($param['sec_standard_value'])
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <small class="text-muted">{{ $param['sec_standard_value'] }}</small>
                                            @if($param['sec_standard_id'])
                                            <button type="button" wire:click.stop="openEditStandardModal('{{ $id }}', 2)"
                                                class="btn btn-sm btn-link p-0 text-muted ml-1"
                                                title="Edit Secondary Standard" style="line-height: 1;"
                                                wire:loading.attr="disabled">
                                                <i wire:loading.remove wire:target="openEditStandardModal('{{ $id }}', 2)"
                                                    class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                <i wire:loading wire:target="openEditStandardModal('{{ $id }}', 2)"
                                                    class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                            </button>
                                            @endif
                                        </div>
                                        @endif
                                    </td>
                                    <td style="min-width: 110px;">
                                        <select class="form-control form-control-sm"
                                            wire:model="parametersForm.{{ $id }}.remark"
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
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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
                    <div class="alert alert-light border text-center py-5 mb-0">
                        <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 2.5rem;"></i>
                        <p class="mb-0 mt-2 text-muted">No captured results found for this sample.</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 sample-parameters-modal__footer">
                    <button type="button" class="btn btn-light" wire:click="cancelViewParameters">
                        <i class="mdi mdi-close"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary px-4" wire:click="saveParameters"
                        wire:loading.attr="disabled" wire:target="saveParameters">
                        <span wire:loading.remove wire:target="saveParameters">
                            <i class="mdi mdi-content-save"></i> Save changes
                        </span>
                        <span wire:loading wire:target="saveParameters">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Saving…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .sample-parameters-modal__content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }

        .sample-parameters-modal__header {
            padding: 1.25rem 1.5rem 0.75rem;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }

        .sample-parameters-modal__body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 0 1.5rem 1rem;
            background: #f8fafc;
        }

        .sample-parameters-modal__table-wrap {
            border-radius: 12px;
            border: 1px solid #e9ecef;
            background: #fff;
            overflow: auto;
            max-height: 62vh;
        }

        .sample-parameters-table {
            font-size: 0.8125rem;
            margin-bottom: 0;
        }

        .sample-parameters-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            vertical-align: middle;
        }

        .sample-parameters-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .sample-parameters-table tbody td {
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .sample-parameters-modal__analyte {
            min-width: 140px;
            max-width: 200px;
        }

        .sample-parameters-modal__analyte .font-weight-semibold {
            font-weight: 600;
            word-break: break-word;
        }

        .sample-parameters-modal__footer {
            padding: 1rem 1.5rem 1.25rem;
            background: #fff;
        }

        .sample-parameters-table .form-control-sm {
            border-radius: 8px;
            border-color: #e2e8f0;
        }

        .sample-parameters-table .form-control-sm:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }
    </style>
    @endif

    {{-- Comments & Interpretations Modal --}}
    @if($showCommentsModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
        wire:key="comments-modal-{{ $editingCommentsSampleId }}">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"
                x-data="{
                    initEditor(selector, field, initialHtml) {
                        if (typeof tinymce === 'undefined') {
                            return;
                        }
                        tinymce.remove(selector);
                        tinymce.init({
                            selector: selector,
                            menubar: false,
                            statusbar: false,
                            height: field === 'main_body' ? 220 : 160,
                            toolbar: 'bold italic underline | bullist numlist | forecolor',
                            plugins: 'lists textcolor',
                            setup: (editor) => {
                                editor.on('init', () => {
                                    editor.setContent(initialHtml || '');
                                });
                                editor.on('change blur', () => {
                                    editor.save();
                                    $wire.set('commentsForm.' + field, editor.getContent());
                                });
                            }
                        });
                    },
                    initAll() {
                        this.initEditor('#comments-header-editor', 'header_body', @js($commentsForm['header_body']));
                        this.initEditor('#comments-main-editor', 'main_body', @js($commentsForm['main_body']));
                        this.initEditor('#comments-notes-editor', 'notes_body', @js($commentsForm['notes_body']));
                    },
                    syncToWire() {
                        if (typeof tinymce === 'undefined') {
                            return;
                        }
                        tinymce.triggerSave();
                        $wire.set('commentsForm.header_body', tinymce.get('comments-header-editor')?.getContent() ?? '');
                        $wire.set('commentsForm.main_body', tinymce.get('comments-main-editor')?.getContent() ?? '');
                        $wire.set('commentsForm.notes_body', tinymce.get('comments-notes-editor')?.getContent() ?? '');
                    },
                    destroyEditors() {
                        if (typeof tinymce !== 'undefined') {
                            tinymce.remove('#comments-header-editor, #comments-main-editor, #comments-notes-editor');
                        }
                    },
                    saveComments() {
                        this.syncToWire();
                        $wire.saveComments();
                    },
                    closeModal() {
                        this.destroyEditors();
                        $wire.cancelComments();
                    }
                }"
                x-init="setTimeout(() => initAll(), 150)">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-document-edit"></i> Comments & Interpretations
                    </h5>
                    <button type="button" class="close" @click="closeModal()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <style>
                        .comments-interpretations-modal .tox-tinymce {
                            border-radius: 8px !important;
                            border: 1px solid #e2e8f0 !important;
                        }
                    </style>
                    <div class="comments-interpretations-modal" wire:ignore>
                        <div class="form-group">
                            <label>Comments</label>
                            <textarea id="comments-header-editor" class="form-control" rows="3"
                                placeholder="Comments..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Recommendations / Interpretations</label>
                            <textarea id="comments-main-editor" class="form-control" rows="4"
                                placeholder="Recommendations / Interpretations..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea id="comments-notes-editor" class="form-control" rows="3"
                                placeholder="Notes..."></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comments-batch-scope" class="control-label">Scope</label>
                        <select id="comments-batch-scope" wire:model.defer="commentsForm.batch_comment_scope" class="form-control">
                            <option value="1">Concatenate</option>
                            <option value="2">Overwrite</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm" wire:loading.attr="disabled"
                        @click="saveComments()">
                        <span wire:loading.remove wire:target="saveComments"><i class="mdi mdi-content-save"></i> Save</span>
                        <span wire:loading wire:target="saveComments"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" @click="closeModal()">Close</button>
                </div>
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
            min-height: 38px;
            padding: 4px 10px;
            background: #fff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .tag-select-input:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        .tag-select-input:focus-within {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .tag-badge:hover {
            background-color: #dbeafe;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 0.95rem;
            color: #1e40af;
            opacity: 0.7;
            transition: all 0.15s ease;
        }

        .tag-badge i:hover {
            opacity: 1;
            color: #ef4444;
        }

        .tag-input {
            flex: 1;
            min-width: 100px;
            border: none;
            outline: none;
            padding: 2px 4px;
            font-size: 0.85rem;
            background: transparent;
            color: #1e293b;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            max-height: 240px;
            overflow-y: auto;
            z-index: 9999; /* Float clearly over other table rows/modals */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 4px;
        }

        .tag-dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #334155;
            transition: all 0.15s ease;
            border-bottom: none;
            display: flex;
            align-items: center;
        }

        .tag-dropdown-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .tag-dropdown-create {
            background-color: #f8fafc;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            color: #3b82f6;
        }

        .tag-dropdown-create:hover {
            background-color: #eff6ff;
        }

        .tag-dropdown-divider {
            height: 1px;
            background-color: #e2e8f0;
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
                                x-init="
                                    initMCE();
                                    Livewire.on('reinit-mce', () => {
                                        setTimeout(() => initMCE(), 100);
                                    });
                                ">
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

    {{-- Grouped worksheets modal (per sample, from linked analysis types) --}}
    @if($showGroupedWorksheetsModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 1095;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="mdi mdi-folder-multiple-outline text-primary"></i>
                        Grouped worksheets — {{ $groupedWorksheetsModalSampleCode }}
                    </h5>
                    <button type="button" class="close" wire:click="closeGroupedWorksheetsModal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">
                        Pipelines linked via this sample's analysis types. Open a worksheet to capture results for the batch.
                    </p>
                    @foreach($groupedWorksheetsModalItems as $ws)
                        <div class="sample-gw-holder">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h6 class="mb-1 font-weight-bold">{{ $ws['holder_name'] }}</h6>
                                    <p class="text-muted small mb-0">
                                        <i class="mdi mdi-flask-outline"></i>
                                        {{ implode(' · ', $ws['analysis_names']) }}
                                        <span class="mx-1">·</span>
                                        {{ $ws['step_count'] }} {{ Str::plural('stage', $ws['step_count']) }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="sample-gw-status {{ $ws['run_status'] ? 'sample-gw-status--' . $ws['run_status'] : '' }}">
                                        {{ $ws['run_status_label'] }}
                                    </span>
                                    <a href="{{ $ws['capture_url'] }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                                        <i class="mdi mdi-clipboard-edit-outline"></i> Capture worksheet
                                    </a>
                                </div>
                            </div>
                            @if(!empty($ws['stages']))
                                <ul class="sample-gw-timeline">
                                    @foreach($ws['stages'] as $stage)
                                        <li class="sample-gw-timeline__item">
                                            <div class="sample-gw-timeline__dot {{ $loop->last ? 'sample-gw-timeline__dot--final' : '' }}">
                                                {{ $stage['sequence'] }}
                                            </div>
                                            <div class="sample-gw-timeline__card">
                                                <p class="sample-gw-timeline__title">{{ $stage['label'] }}</p>
                                                <p class="sample-gw-timeline__meta mb-0">
                                                    {{ ucwords(str_replace('_', ' ', $stage['item_type'])) }}
                                                    @if($stage['reference_name'] && $stage['reference_name'] !== '—')
                                                        — {{ $stage['reference_name'] }}
                                                    @endif
                                                    @if($stage['is_required'])
                                                        <span class="text-danger">· Required</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" wire:click="closeGroupedWorksheetsModal">Close</button>
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

    {{-- JavaScript for Sample Duplication --}}
    <script>
        function duplicateSelected() {
            const count = prompt("Enter number of duplicates", 1);
            if (count !== null && count > 0) {
                @this.call('duplicateSelectedSamples', parseInt(count));
            }
        }

        if (!window.__batchSamplesSelectAllBound) {
            window.__batchSamplesSelectAllBound = true;

            document.addEventListener('livewire:init', function () {
                document.addEventListener('change', function (event) {
                    const selectAllCheckbox = event.target.closest('#select-all-samples');
                    if (!selectAllCheckbox) {
                        return;
                    }

                    const shouldSelect = selectAllCheckbox.checked;
                    document.querySelectorAll('.sample-row-checkbox').forEach(function (checkbox) {
                        if (checkbox.checked !== shouldSelect) {
                            checkbox.click();
                        }
                    });
                });
            });
        }
    </script>

    @script
    <script>
        if (!window.__batchSamplesResultConfirmBound) {
            window.__batchSamplesResultConfirmBound = true;

            const askResultConfirmation = function (message, expected) {
                return new Promise(function (resolve) {
                    const existing = document.getElementById('js-result-confirm-overlay');
                    if (existing) {
                        existing.remove();
                    }

                    const overlay = document.createElement('div');
                    overlay.id = 'js-result-confirm-overlay';
                    overlay.setAttribute('role', 'dialog');
                    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:flex;align-items:center;justify-content:center;';
                    overlay.innerHTML =
                        '<div style="background:#fff;border-radius:12px;padding:1.25rem;width:min(420px,92vw);box-shadow:0 20px 40px rgba(0,0,0,.2);">' +
                            '<div style="font-weight:600;margin-bottom:.35rem;">Confirm result</div>' +
                            '<div class="js-result-confirm-message" style="color:#64748b;font-size:.875rem;margin-bottom:.75rem;"></div>' +
                            '<div class="js-result-confirm-error alert alert-danger py-2 px-3 mb-2 d-none" style="font-size:.85rem;"></div>' +
                            '<input type="text" class="form-control" id="js-result-confirm-input" autocomplete="off" placeholder="Re-enter result">' +
                            '<div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">' +
                                '<button type="button" class="btn btn-light" id="js-result-confirm-cancel">Cancel</button>' +
                                '<button type="button" class="btn btn-primary" id="js-result-confirm-ok">OK</button>' +
                            '</div>' +
                        '</div>';

                    overlay.querySelector('.js-result-confirm-message').textContent = message;
                    document.body.appendChild(overlay);

                    const input = overlay.querySelector('#js-result-confirm-input');
                    const errorEl = overlay.querySelector('.js-result-confirm-error');
                    const finish = function (value) {
                        overlay.remove();
                        resolve(value);
                    };
                    const showMismatch = function () {
                        errorEl.textContent = "Result confirmation didn't match captured result!";
                        errorEl.classList.remove('d-none');
                        input.value = '';
                        input.classList.add('is-invalid');
                        input.focus();
                    };
                    const tryAccept = function () {
                        const value = (input.value || '').trim();
                        if (value !== String(expected).trim()) {
                            showMismatch();
                            return;
                        }
                        finish(value);
                    };

                    overlay.querySelector('#js-result-confirm-cancel').addEventListener('click', function () {
                        finish(null);
                    });
                    overlay.querySelector('#js-result-confirm-ok').addEventListener('click', tryAccept);
                    overlay.addEventListener('click', function (event) {
                        if (event.target === overlay) {
                            finish(null);
                        }
                    });
                    input.addEventListener('input', function () {
                        input.classList.remove('is-invalid');
                        errorEl.classList.add('d-none');
                    });
                    input.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            tryAccept();
                        }
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            finish(null);
                        }
                    });

                    setTimeout(function () {
                        input.focus();
                    }, 0);
                });
            };

            const confirmResultInput = async function (el) {
                if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-confirm-result')) {
                    return;
                }

                if (el.dataset.confirming === '1') {
                    return;
                }

                const wireRoot = el.closest('[wire\\:id]');
                if (!wireRoot || typeof Livewire === 'undefined' || typeof Livewire.find !== 'function') {
                    return;
                }

                const component = Livewire.find(wireRoot.getAttribute('wire:id'));
                if (!component) {
                    return;
                }

                const current = (el.value || '').trim();
                const analyte = el.dataset.analyte || 'analyte';
                const sample = el.dataset.sample || '';
                const rowId = el.dataset.rowId;

                if (!rowId) {
                    return;
                }

                if (current === '') {
                    delete el.dataset.confirmedValue;
                    component.call('clearParameterResult', rowId);
                    return;
                }

                if (el.dataset.confirmedValue === current) {
                    return;
                }

                el.dataset.confirming = '1';

                try {
                    const confirmation = await askResultConfirmation(
                        'Please confirm the result for ' + analyte + (sample ? ' in sample ' + sample : '') + ':',
                        current
                    );

                    if (confirmation !== null && confirmation.trim() === current) {
                        el.dataset.confirmedValue = current;
                        component.call('applyConfirmedResult', rowId, current);
                    } else {
                        el.value = '';
                        delete el.dataset.confirmedValue;
                        component.call('clearParameterResult', rowId);
                    }
                } finally {
                    delete el.dataset.confirming;
                }
            };

            document.addEventListener('change', function (event) {
                confirmResultInput(event.target);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                const el = event.target;
                if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-confirm-result')) {
                    return;
                }

                event.preventDefault();
                confirmResultInput(el);
            });
        }
    </script>
    @endscript
</div>