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
    @php
        $incompleteGrouped = $this->incompleteCapturedResultsGrouped;
        $incompleteResultCount = is_array($incompleteCapturedResults) ? count($incompleteCapturedResults) : 0;
    @endphp
    @if($incompleteResultCount > 0)
        <div class="mb-3">
            <button type="button"
                class="btn btn-danger btn-sm"
                wire:click="openIncompleteResultsModal"
                aria-haspopup="dialog">
                <i class="mdi mdi-alert"></i>
                Unfinished / missing results
                <span class="badge badge-light text-danger ml-1">{{ $incompleteResultCount }}</span>
            </button>
        </div>
    @endif

    @if($showIncompleteResultsModal)
        <div class="modal fade show d-block incomplete-results-overlay"
             tabindex="-1"
             role="dialog"
             aria-modal="true"
             aria-labelledby="incomplete-results-title"
             wire:keydown.escape.window="closeIncompleteResultsModal">
            <div class="modal-dialog modal-dialog-centered incomplete-results-dialog" role="document">
                <div class="modal-content incomplete-results-shell">
                    <div class="ls-ui-kit">
                        <div class="ls-modal-card ls-modal-card--wide incomplete-results-card"
                             data-ls-modal-type="info-dossier">
                            <div class="ls-modal-card__header">
                                <div>
                                    <p class="ls-modal-card__eyebrow">Results checklist</p>
                                    <h3 class="ls-modal-card__title" id="incomplete-results-title">
                                        Unfinished / missing results
                                    </h3>
                                    <p class="ls-modal-card__subtitle">
                                        Capture a value for each row below before verification or approval.
                                    </p>
                                </div>
                                <button type="button"
                                    class="ls-modal-card__close"
                                    wire:click="closeIncompleteResultsModal"
                                    aria-label="Close">
                                    <i class="mdi mdi-close" aria-hidden="true"></i>
                                </button>
                            </div>

                            <div class="ls-modal-card__body incomplete-results-body">
                                @if($incompleteResultCount === 0)
                                    @include('layouts.lab.partials.ls-ui.modals.ls-modal-empty', [
                                        'title' => 'No missing results',
                                        'text' => 'Every captured parameter in this batch has a result.',
                                        'fallbackIcon' => 'mdi-check-circle-outline',
                                    ])
                                @else
                                    @foreach($incompleteGrouped as $sampleCode => $items)
                                        @php
                                            $sampleItems = collect($items);
                                        @endphp
                                        <div class="ls-modal-panel">
                                            <div class="incomplete-results-panel-head">
                                                <h4 class="ls-modal-panel__title mb-0">
                                                    <i class="mdi mdi-flask-outline" aria-hidden="true"></i>
                                                    {{ format_sample_code($sampleCode) }}
                                                </h4>
                                            </div>

                                            <div class="ls-table-wrap incomplete-results-table-wrap">
                                                <table class="ls-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Lab section</th>
                                                            <th>Analysis type</th>
                                                            <th>Parameter</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sampleItems as $item)
                                                            @php
                                                                $statusLabel = (string) ($item['status'] ?? 'incomplete');
                                                                $isNoAttachment = strcasecmp($statusLabel, 'No attachment') === 0;
                                                                $labSectionLabel = trim((string) ($item['lab_section'] ?? ''));
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $labSectionLabel !== '' && $labSectionLabel !== 'N/A' ? $labSectionLabel : '—' }}</td>
                                                                <td>
                                                                    <span class="ls-table__stack-primary">{{ $item['analysis_type'] }}</span>
                                                                </td>
                                                                <td>{{ $item['parameter'] }}</td>
                                                                <td>
                                                                    <span class="ls-pill {{ $isNoAttachment ? 'ls-pill--inactive' : 'incomplete-results-status-pill' }}">
                                                                        {{ $statusLabel }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="ls-modal-card__footer ls-modal-card__footer--split">
                                <span class="small text-muted" style="font-size:0.7rem;">
                                    Read only · finish gaps in Raw Results
                                </span>
                                <div class="d-flex align-items-center" style="gap:0.45rem;">
                                    <button type="button"
                                        class="ls-btn"
                                        wire:click="closeIncompleteResultsModal">
                                        Close
                                    </button>
                                    @if(in_array($batch->status ?? '', ['Sample Verification', 'Sample Approval', 'Samples In Lab'], true))
                                        <button type="button"
                                            class="ls-btn ls-btn--accent"
                                            wire:click="closeIncompleteResultsModal"
                                            onclick="(function(){ var tab = document.getElementById('raw-results-tab'); if (tab) { tab.click(); } })()">
                                            <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                                            View results
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .incomplete-results-overlay {
                background-color: rgba(15, 23, 42, 0.45);
                z-index: 1050;
            }
            .incomplete-results-dialog {
                max-width: 40rem;
                width: calc(100% - 1.5rem);
                margin: 1rem auto;
            }
            .incomplete-results-shell {
                border: none;
                background: transparent;
                box-shadow: none;
            }
            .incomplete-results-card {
                max-width: none;
                width: 100%;
                max-height: calc(100vh - 3rem);
                box-shadow: 0 16px 48px rgba(15, 23, 42, 0.18);
            }
            .incomplete-results-body {
                overflow-y: auto;
                flex: 1 1 auto;
                min-height: 0;
                max-height: min(62vh, 36rem);
            }
            .incomplete-results-panel-head {
                margin-bottom: 0.55rem;
            }
            .incomplete-results-panel-head .ls-modal-panel__title {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }
            .incomplete-results-table-wrap {
                margin: 0;
                border-radius: 8px;
            }
            .incomplete-results-status-pill {
                background: #fee2e2;
                color: #b91c1c;
            }
        </style>
    @endif

    {{-- Sample Configuration Form (Livewire-driven) --}}
    <form wire:submit="saveSamples" class="workflow-board-panel batch-samples-panel">
        <div class="workflow-board-panel-header">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <h5><i class="mdi mdi-flask-outline"></i>
                    {{ (($batch->status ?? '') === 'Sample Verification' || ($batch->prelim_batch_status ?? '') === 'Sample Verification')
                        ? 'Verifications configuration'
                        : 'Samples configuration' }}
                </h5>
                <button type="submit" class="btn btn-danger btn-sm btn-action-sm text-white"
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
                @if(in_array($batch->status ?? '', ['Sample Verification', 'Sample Approval', 'Samples In Lab'], true))
                    <button type="button"
                        class="btn btn-outline-primary btn-sm btn-action-sm"
                        onclick="(function(){ var tab = document.getElementById('raw-results-tab'); if (tab) { tab.click(); } })()"
                        title="Open Raw Results to review without Capture Results">
                        <i class="mdi mdi-eye-outline"></i> View results
                    </button>
                @endif
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
        <div class="table-responsive samples-config-table-wrap">
            <table class="table table-bordered table-sm workflow-table samples-config-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th class="samples-sticky-col samples-sticky-col--check" style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all-samples" title="Select All">
                        </th>
                        <th class="samples-sticky-col samples-sticky-col--action" style="width: 160px; min-width: 160px; text-align: center;">Sample Action</th>
                        <th class="samples-sticky-col samples-sticky-col--id" style="min-width: 180px;">Sample ID</th>
                        <th style="min-width: 220px;">Analysis Type<sup class="text-danger">*</sup></th>
                        <th style="min-width: 150px;">Sample Type</th>
                        <th style="min-width: 260px;">
                            Specification<sup class="text-danger">*</sup>
                        </th>
                        <th style="min-width: 260px;">
                            Secondary Specification
                        </th>
                        <th style="min-width: 140px;">Lab section</th>
                        <th style="min-width: 120px;">Sample Condition</th>
                        <th style="min-width: 150px;">
                            Sampling Point
                            <button type="button" class="btn btn-xs btn-outline-primary ml-1"
                                wire:click="openAddModal('sample_point_id', null)" title="Add New Sampling Point">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </th>
                        <th style="min-width: 150px;">Sample Description</th>
                        <th style="min-width: 110px;">Disposal Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $standardSearchOptions = collect($standards ?? [])->map(function ($std) {
                            $id = (string) ($std['id'] ?? '');
                            if ($id === '') {
                                return null;
                            }
                            $code = trim((string) ($std['code'] ?? ''));

                            return [
                                'value' => $id,
                                'label' => $code !== '' ? $code : $id,
                            ];
                        })->filter()->values()->all();
                    @endphp
                    @forelse($sampleForms as $index => $sampleForm)
                    @php
                    $isEditing = $editingRowIndex === $index;
                    $isReadOnly = false;
                    @endphp
                    <tr wire:key="sample-row-{{ $index }}"
                        class="{{ in_array($index, $selectedRows) ? 'table-active' : '' }}">
                        {{-- Selection Checkbox --}}
                        <td class="text-center samples-sticky-col samples-sticky-col--check">
                            <input type="checkbox" wire:click="toggleRowSelection({{ $index }})" {{ in_array($index, $selectedRows) ? 'checked' : '' }} class="sample-row-checkbox">
                        </td>

                        <td class="text-center samples-sticky-col samples-sticky-col--action">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                {{-- View Parameters Icon --}}
                                @if($sampleForm['id'])
                                <button type="button" wire:click="viewSingleSampleParameters('{{ $sampleForm['sample_code'] }}')"
                                    class="btn btn-sm btn-icon btn-light text-info mx-1" title="Capture / view results">
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
                                    @if(trim(strip_tags($sampleForm['main_body'] ?? '')) !== ''
                                        || trim(strip_tags($sampleForm['notes_body'] ?? '')) !== '')
                                        <span class="badge badge-success interpretation-badge">!</span>
                                    @endif
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

                                {{-- Accelerated Shelf-Life Study Conditions --}}
                                @if($sampleForm['id'] && !empty($batch->is_shelf_life))
                                <button type="button"
                                    wire:click="openShelfLifeConditionsModal('{{ $sampleForm['id'] }}')"
                                    class="btn btn-sm btn-icon btn-light text-danger mx-1"
                                    title="Accelerated Shelf-Life Study Conditions">
                                    <i class="mdi mdi-thermometer-lines"></i>
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
                        <td class="samples-sticky-col samples-sticky-col--id" style="min-width: 180px;">
                            <div class="d-flex align-items-center" style="gap:6px;">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ format_sample_code($sampleForm['sample_code']) }}" readonly
                                    style="background: #f5f5f5; font-weight: bold; min-width: 160px;{{ !empty($sampleForm['is_ammendment']) ? ' border-color:#f59e0b;' : '' }}">
                                @if(!empty($sampleForm['is_ammendment']))
                                    <span class="badge badge-warning" title="Flagged for amendment">
                                        Amend V{{ $sampleForm['ammendment_number'] ?? $batch->amendmentVersion() }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Matrix (analysis types) --}}
                        <td class="samples-analysis-type-cell" style="vertical-align: top;">
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
                            <div class="tag-select-container samples-analysis-type-select" style="min-width: 180px;"
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

                                    <input type="text" wire:model.live="analysisTypeSearch"
                                        class="tag-input{{ (is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0 && empty($showAnalysisTypeDropdown[$index])) ? ' tag-input--collapsed' : '' }}"
                                        placeholder="{{ (is_array($sampleForm['analysis_type_id']) && count($sampleForm['analysis_type_id']) > 0) ? '' : 'Search analysis type...' }}"
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
                                    <div class="p-3 text-center text-muted">
                                        {{ trim((string) ($sampleForm['sample_type_id'] ?? ($batch->sample_type_id ?? ''))) === ''
                                            ? 'Select a sample type first'
                                            : 'No analysis type options found' }}
                                    </div>
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
                            <select class="form-control form-control-sm modern-select no-select2"
                                wire:model="sampleForms.{{ $index }}.sample_type_id">
                                <option value="">Select...</option>
                                @foreach($sampleTypes as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                            @error("sampleForms.$index.sample_type_id")
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </td>

                        {{-- Specification --}}
                        <td style="min-width: 260px;">
                            @php
                                $selectedSpecId = trim((string) ($sampleForm['main_standard'] ?? ''));
                            @endphp
                            <div class="sample-spec-search-wrap" wire:key="sample-spec-{{ $index }}-{{ $selectedSpecId }}">
                                @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                    'label' => null,
                                    'lsId' => 'sample-spec-'.$index,
                                    'lsName' => 'sample_main_standard_'.$index,
                                    'placeholder' => 'Select…',
                                    'options' => $standardSearchOptions,
                                    'selected' => $selectedSpecId !== '' ? $selectedSpecId : null,
                                    'required' => true,
                                    'wireModel' => 'sampleForms.'.$index.'.main_standard',
                                    'wireLive' => true,
                                    'disableSuccess' => true,
                                    'extraFieldClass' => 'mb-0',
                                ])
                            </div>
                            @error("sampleForms.$index.main_standard")
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </td>

                        {{-- Secondary Specification --}}
                        <td style="min-width: 260px;">
                            @php
                                $selectedSecondarySpecId = trim((string) ($sampleForm['secondary_standard'] ?? ''));
                            @endphp
                            <div class="sample-spec-search-wrap" wire:key="sample-secondary-spec-{{ $index }}-{{ $selectedSecondarySpecId }}">
                                @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                    'label' => null,
                                    'lsId' => 'sample-secondary-spec-'.$index,
                                    'lsName' => 'sample_secondary_standard_'.$index,
                                    'placeholder' => 'Select…',
                                    'options' => $standardSearchOptions,
                                    'selected' => $selectedSecondarySpecId !== '' ? $selectedSecondarySpecId : null,
                                    'wireModel' => 'sampleForms.'.$index.'.secondary_standard',
                                    'wireLive' => true,
                                    'disableSuccess' => true,
                                    'extraFieldClass' => 'mb-0',
                                ])
                            </div>
                        </td>

                        {{-- Lab section (from analysis type(s); multi-type samples show multiple) --}}
                        <td>
                            @php $rowLabSections = $this->labSectionLabelsForSampleIndex($index); @endphp
                            <div class="d-flex flex-wrap align-items-center" style="gap: 4px; min-width: 120px;"
                                title="Lab sections come from the sample's analysis types. Add/remove analysis types to change them.">
                                @forelse($rowLabSections as $sectionLabel)
                                    <span class="badge badge-light border font-weight-normal">{{ $sectionLabel }}</span>
                                @empty
                                    <span class="text-muted small">Select analysis type(s)</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Condition --}}
                        <td>
                            @if($isReadOnly)
                            <input type="text" class="form-control form-control-sm readonly-input"
                                value="{{ collect($conditions)->firstWhere('id', $sampleForm['sample_condition_id'])['name'] ?? '-' }}"
                                readonly>
                            @else
                            <select class="form-control form-control-sm modern-select no-select2"
                                wire:model="sampleForms.{{ $index }}.sample_condition_id">
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
                            <select class="form-control form-control-sm modern-select no-select2"
                                wire:model="sampleForms.{{ $index }}.sample_point_id">
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

                        {{-- Description --}}
                        <td>
                            <div class="d-flex align-items-start gap-2">
                                @php $rowDescription = $this->sampleRowDescription($index); @endphp
                                <div class="p-2 border rounded flex-grow-1" style="background: #f8f9fa; min-height: 31px; font-size: 0.875rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $rowDescription }}">
                                    @if($rowDescription !== '')
                                        {{ $rowDescription }}
                                    @else
                                        <span class="text-muted">No description</span>
                                    @endif
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">
                            <i class="mdi mdi-information-outline"></i> No items configured yet. Click "Add" to create
                            entries.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </form>

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
                                    <div class="p-2 text-center text-muted">
                                        {{ trim((string) ($stagingForm['sample_type_id'] ?? '')) === ''
                                            ? 'Select a specimen type first'
                                            : 'No analysis types found' }}
                                    </div>
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
    @php
        $parameterCarouselCodes = $parameterModalSampleCodes !== []
            ? $parameterModalSampleCodes
            : (filled($selectedSampleCode) ? [(string) $selectedSampleCode] : []);
        $parameterCarouselCount = count($parameterCarouselCodes);
        $parameterCarouselIndex = (int) $parameterModalSampleIndex;
        $canGoPrevParameterSample = $parameterCarouselCount > 1 && $parameterCarouselIndex > 0;
        $canGoNextParameterSample = $parameterCarouselCount > 1
            && $parameterCarouselIndex < ($parameterCarouselCount - 1);
        $prevParameterSampleCode = $canGoPrevParameterSample
            ? (string) ($parameterCarouselCodes[$parameterCarouselIndex - 1] ?? '')
            : '';
        $nextParameterSampleCode = $canGoNextParameterSample
            ? (string) ($parameterCarouselCodes[$parameterCarouselIndex + 1] ?? '')
            : '';
        $parameterSampleLabSections = collect($parameterLabSections ?? [])
            ->map(static fn ($label): string => trim((string) $label))
            ->filter()
            ->unique()
            ->values()
            ->all();
    @endphp
    <div class="modal fade show d-block sample-parameters-modal" tabindex="-1" role="dialog"
        style="background: rgba(15, 23, 42, 0.45);" wire:keydown.escape.window="cancelViewParameters">
        <button type="button"
            class="spm-ext-nav spm-ext-nav--left {{ $canGoPrevParameterSample ? 'is-active' : 'is-disabled' }}"
            @if($canGoPrevParameterSample) wire:click="previousParameterSample" @else disabled @endif
            wire:loading.attr="disabled"
            wire:target="previousParameterSample,nextParameterSample,switchParameterSample,viewParameters,viewSingleSampleParameters"
            title="{{ $prevParameterSampleCode !== '' ? 'Previous: '.format_sample_code($prevParameterSampleCode) : 'Previous sample' }}"
            aria-label="{{ $prevParameterSampleCode !== '' ? 'Previous sample '.format_sample_code($prevParameterSampleCode) : 'Previous sample' }}">
            <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
            @if($prevParameterSampleCode !== '')
                <span class="spm-ext-nav__cue">{{ format_sample_code($prevParameterSampleCode) }}</span>
            @endif
        </button>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content sample-parameters-modal__content ls-ui-kit">
                <div class="modal-header border-0 sample-parameters-modal__header">
                    <div class="sample-parameters-modal__header-main">
                        <div class="min-w-0">
                            <p class="sample-parameters-modal__eyebrow mb-0">Capture results</p>
                            <h5 class="modal-title sample-parameters-modal__title mb-1">
                                Parameters for sample
                            </h5>
                            <p class="sample-parameters-modal__subtitle mb-0">
                                <span class="sample-parameters-modal__code">{{ format_sample_code($selectedSampleCode) }}</span>
                                @if(!empty($sampleParameters))
                                    <span>{{ count($sampleParameters) }} parameter{{ count($sampleParameters) === 1 ? '' : 's' }}</span>
                                @endif
                                <span class="sample-parameters-modal__meta">
                                    {{ min($parameterCarouselIndex + 1, max($parameterCarouselCount, 1)) }} of {{ max($parameterCarouselCount, 1) }}
                                </span>
                            </p>
                        </div>
                        <div class="sample-parameters-modal__header-actions">
                            @if(in_array((string) $batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection'], true))
                                <div class="dropdown sample-parameters-modal__excel-actions"
                                     x-data="{ open: false }"
                                     @click.outside="open = false">
                                    <button type="button"
                                            class="btn btn-sm sample-parameters-modal__excel-btn"
                                            @click.stop="open = !open"
                                            :aria-expanded="open"
                                            aria-haspopup="true"
                                            title="Excel template and import">
                                        <i class="mdi mdi-file-excel-outline"></i>
                                        <span>Excel</span>
                                        <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow sample-parameters-modal__excel-menu"
                                         :class="{ 'show': open }"
                                         @click.stop>
                                        <a class="dropdown-item"
                                           href="{{ route('batch.request-test-results-excel', ['batch' => $batch->id]) }}">
                                            <i class="mdi mdi-download mr-2"></i> Download current data
                                        </a>
                                        <button type="button"
                                                class="dropdown-item"
                                                wire:click="openResultImportVersionsModal"
                                                @click="open = false">
                                            <i class="mdi mdi-history mr-2"></i> Version history
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <div class="sample-parameters-modal__excel-upload px-3 py-2">
                                            <div class="mb-0" id="sample-parameters-excel-import-form">
                                                <label class="small text-muted mb-1 d-block" for="sample-parameters-excel-import-file">
                                                    Upload completed Excel
                                                </label>
                                                <input type="file"
                                                       class="form-control-file form-control-sm mb-2"
                                                       id="sample-parameters-excel-import-file"
                                                       wire:model="parameterImportFile"
                                                       accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                                                @error('parameterImportFile')
                                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                                @enderror
                                                <p class="small text-muted mb-2 mb-0">
                                                    Set <code>Action=clear</code> to clear a result. Leave Result blank to skip.
                                                </p>
                                                <button type="button"
                                                        class="btn btn-sm btn-primary btn-block"
                                                        wire:click="importParameterResults"
                                                        wire:loading.attr="disabled"
                                                        wire:target="importParameterResults,parameterImportFile,updatedParameterImportFile">
                                                    <span wire:loading.remove wire:target="importParameterResults,parameterImportFile,updatedParameterImportFile">
                                                        <i class="mdi mdi-upload"></i> Import results
                                                    </span>
                                                    <span wire:loading wire:target="importParameterResults,parameterImportFile,updatedParameterImportFile">
                                                        <i class="mdi mdi-loading mdi-spin"></i> Importing...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <button type="button"
                                class="sample-parameters-modal__close"
                                wire:click="cancelViewParameters"
                                aria-label="Close">
                                <i class="mdi mdi-close" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Loading Indicator --}}
                <div wire:loading wire:target="viewParameters,viewParametersForSelected,viewSingleSampleParameters,switchParameterSample,nextParameterSample,previousParameterSample,importParameterResults,parameterImportFile" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading parameters...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading parameters...</p>
                </div>

                <div wire:loading.remove wire:target="viewParameters,viewParametersForSelected,viewSingleSampleParameters,switchParameterSample,nextParameterSample,previousParameterSample,importParameterResults,parameterImportFile" class="modal-body sample-parameters-modal__body">
                    <div class="sample-parameters-modal__sections mb-3">
                        <span class="sample-parameters-modal__sections-label">Lab sections</span>
                        @forelse($parameterSampleLabSections as $sectionLabel)
                            <span class="ls-pill ls-pill--info">{{ $sectionLabel }}</span>
                        @empty
                            <span class="sample-parameters-modal__sections-empty">Not assigned yet</span>
                        @endforelse
                    </div>
                    @if (session()->has('error'))
                    <div class="alert alert-danger border mb-3">
                        <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
                    </div>
                    @endif
                    @if (session()->has('success'))
                    <div class="alert alert-success border mb-3">
                        <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                    </div>
                    @endif
                    @if (session()->has('message'))
                    <div class="alert alert-success border mb-3">
                        <i class="mdi mdi-check-circle"></i> {{ session('message') }}
                    </div>
                    @endif
                    @if($parametersReadOnly)
                    <div class="alert alert-warning border mb-3">
                        <i class="mdi mdi-lock-outline"></i>
                        {{ app(\App\Services\Sampleworkflow\LabSectionResultAccess::class)->denyEditMessage(auth()->user()) }}
                        You can view parameters but cannot fill or save them.
                    </div>
                    @else
                        @if($this->hasNonEditableParameters)
                        <div class="alert alert-warning border mb-3">
                            <i class="mdi mdi-lock-outline"></i>
                            {{ $this->parametersDenyEditMessage }}
                            Rows outside your lab section(s) are view-only.
                        </div>
                        @endif
                    @endif

                    @if(!empty($sampleParameters))
                    @php
                        $parameterColspan = ($uncertaintyRequired ? 17 : 16);
                        $groupedParameters = $this->groupedParametersForm;
                        $reportingSymbols = $this->reportingSymbolOptions();
                    @endphp
                    <div class="table-responsive sample-parameters-modal__table-wrap">
                        <table class="table table-sm sample-parameters-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 100px;">Sample</th>
                                    <th style="min-width: 150px;">Analyte</th>
                                    <th style="min-width: 80px;">Symbol</th>
                                    <th style="min-width: 120px;">Result</th>
                                    <th style="min-width: 145px;">Start date</th>
                                    <th style="min-width: 145px;">End date</th>
                                    @if($uncertaintyRequired)
                                    <th style="min-width: 80px;">M.U.</th>
                                    @endif
                                    <th style="min-width: 110px;">Spec limit</th>
                                    <th style="min-width: 180px;">Remark</th>
                                    <th style="min-width: 100px;">Unit</th>
                                    <th style="min-width: 120px;">Operator</th>
                                    <th style="min-width: 200px;">Method</th>
                                    <th style="min-width: 120px;">LTM</th>
                                    <th style="min-width: 160px;">Equipment</th>
                                    <th style="min-width: 80px; text-align: center;">Sub.</th>
                                    <th style="min-width: 160px;">Sub. lab</th>
                                    <th style="min-width: 80px; text-align: center;">Accr.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedParameters as $analysisTypeName => $groupedParams)
                                <tr class="sample-parameters-group-row" wire:key="param-group-{{ md5((string) $analysisTypeName) }}">
                                    <td colspan="{{ $parameterColspan }}" class="sample-parameters-group-cell">
                                        <span class="sample-parameters-group-label">{{ $analysisTypeName }}</span>
                                        <span class="ls-pill ls-pill--inactive ml-1">{{ count($groupedParams) }}</span>
                                    </td>
                                </tr>
                                @foreach($groupedParams as $id => $param)
                                @php
                                    $mainStandardDisplay = trim((string) ($param['standard_value'] ?? ''));
                                    $secStandardDisplay = trim((string) ($param['sec_standard_value'] ?? ''));
                                    $showSecondaryStandard = $secStandardDisplay !== ''
                                        && strcasecmp($secStandardDisplay, $mainStandardDisplay) !== 0;
                                    $selectedSymbol = (string) ($parametersForm[$id]['result_reporting_symbol'] ?? $param['result_reporting_symbol'] ?? '');
                                    $rowCanEdit = ! $parametersReadOnly && (bool) ($param['can_edit'] ?? false);
                                    if (! array_key_exists('can_edit', $param)) {
                                        $rowCanEdit = ! $parametersReadOnly && $this->userCanEditParameterRow((string) $id);
                                    }
                                    $parametersDisabled = ! $rowCanEdit;
                                @endphp
                                <tr wire:key="param-{{ $id }}" class="{{ $parametersDisabled ? 'sample-parameters-row--readonly' : '' }}">
                                    <td><strong>{{ format_sample_code($param['sample_code']) }}</strong></td>
                                    <td class="sample-parameters-modal__analyte">
                                        <span class="font-weight-semibold d-block text-dark">{{ $param['analyte_name'] }}</span>
                                        @if(!empty($param['analyte_code']) && $param['analyte_code'] !== $param['analyte_name'])
                                            <small class="text-muted">{{ $param['analyte_code'] }}</small>
                                        @endif
                                    </td>
                                    <td style="min-width: 72px;">
                                        <select class="form-control form-control-sm"
                                            wire:model.live="parametersForm.{{ $id }}.result_reporting_symbol"
                                            @if($parametersDisabled) disabled @endif>
                                            @foreach($reportingSymbols as $symbolOption)
                                            <option value="{{ $symbolOption['value'] }}" @selected($selectedSymbol === (string) $symbolOption['value'])>
                                                {{ $symbolOption['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 120px;">
                                            <div class="input-group input-group-sm">
                                                <input type="text"
                                                class="form-control form-control-sm"
                                                wire:model.blur="parametersForm.{{ $id }}.result"
                                                placeholder="Result"
                                                @if($parametersDisabled) readonly disabled @endif>
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
                                        @php
                                            $typedResult = (string) ($parametersForm[$id]['result'] ?? '');
                                            $reportPreview = ! empty($param['report_scientific_notation'])
                                                ? \App\Support\ScientificNotation::format($typedResult)
                                                : null;
                                        @endphp
                                        @if($reportPreview)
                                            <small class="d-block text-muted mt-1">Report: {{ $reportPreview }}</small>
                                        @endif
                                    </td>
                                    <td style="min-width: 145px;">
                                        <input type="date"
                                            class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.start_analysis_date"
                                            @if($parametersDisabled) readonly disabled @endif>
                                    </td>
                                    <td style="min-width: 145px;">
                                        <input type="date"
                                            wire:key="end-date-{{ $id }}-{{ $parametersForm[$id]['end_analysis_date'] ?? 'empty' }}"
                                            class="form-control form-control-sm"
                                            wire:model="parametersForm.{{ $id }}.end_analysis_date"
                                            @if($parametersDisabled) readonly disabled @endif>
                                    </td>
                                    @if($uncertaintyRequired)
                                    <td style="min-width: 80px;">
                                        <input type="text" class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.measure_uncertanity"
                                            placeholder="M.U."
                                            @if($parametersDisabled) readonly disabled @endif>
                                    </td>
                                    @endif
                                    <td style="min-width: 110px; max-width: 130px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small>{{ $param['standard_value'] }}</small>
                                            @if($param['standard_id'] && ! $parametersDisabled)
                                            <button type="button" wire:click.stop="openEditStandardModal('{{ $id }}', 1)"
                                                class="btn btn-sm btn-link p-0 text-secondary ml-1"
                                                title="Edit Specification" style="line-height: 1;"
                                                wire:loading.attr="disabled">
                                                <i wire:loading.remove wire:target="openEditStandardModal('{{ $id }}', 1)"
                                                    class="mdi mdi-pencil" style="font-size: 12px;"></i>
                                                <i wire:loading wire:target="openEditStandardModal('{{ $id }}', 1)"
                                                    class="mdi mdi-loading mdi-spin" style="font-size: 12px;"></i>
                                            </button>
                                            @endif
                                        </div>
                                        @if($showSecondaryStandard)
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <small class="text-muted">{{ $param['sec_standard_value'] }}</small>
                                            @if($param['sec_standard_id'] && ! $parametersDisabled)
                                            <button type="button" wire:click.stop="openEditStandardModal('{{ $id }}', 2)"
                                                class="btn btn-sm btn-link p-0 text-muted ml-1"
                                                title="Edit Secondary Specification" style="line-height: 1;"
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
                                    <td style="min-width: 180px;">
                                        <select class="form-control form-control-sm"
                                            wire:model="parametersForm.{{ $id }}.remark"
                                            style="pointer-events: none; background-color: #e9ecef;"
                                            @if($parametersDisabled) disabled @endif>
                                            <option value="">- Select -</option>
                                            <option value="PASS">Conforming</option>
                                            <option value="FAIL">Non-Conforming</option>
                                        </select>
                                    </td>
                                    <td style="min-width: 120px;">
                                        @php
                                            $selectedUnitId = (string) ($parametersForm[$id]['reporting_unit'] ?? $param['reporting_unit'] ?? '');
                                        @endphp
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.reporting_unit"
                                            @if($parametersDisabled) disabled @endif>
                                            <option value="">- Unit -</option>
                                            @foreach($modalLists['units'] as $unit)
                                            <option value="{{ (string) $unit->id }}" @selected($selectedUnitId === (string) $unit->id)>
                                                {{ $unit->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 140px;">
                                        @forelse(($param['operator_names'] ?? []) as $operatorName)
                                            <span class="badge badge-light border mr-1 mb-1">{{ $operatorName }}</span>
                                        @empty
                                            <small class="text-muted d-block">
                                                {{ $param['operator_name'] ?? auth()->user()?->name ?? 'Current user' }}
                                            </small>
                                        @endforelse
                                    </td>
                                    <td style="min-width: 200px;">
                                        <select class="form-control form-control-sm"
                                            wire:model.defer="parametersForm.{{ $id }}.method_id"
                                            @if($parametersDisabled) disabled @endif>
                                            <option value="">- Method -</option>
                                            @foreach($modalLists['methods'] as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><small>{{ $param['ltm_method_name'] }}</small></td>
                                    <td style="min-width: 220px;">
                                        @php
                                            $selectedEquipmentIds = array_values(array_unique(array_map(
                                                'strval',
                                                $parametersForm[$id]['equipment_ids'] ?? ($param['equipment_ids'] ?? [])
                                            )));
                                            $equipmentOptions = collect($modalLists['equipments'] ?? [])
                                                ->mapWithKeys(fn ($eq) => [(string) $eq->id => (string) $eq->name])
                                                ->all();
                                        @endphp
                                        <div class="param-equipment-select2-wrap"
                                            wire:ignore
                                            wire:key="param-equipment-{{ $id }}"
                                            data-param-id="{{ $id }}"
                                            data-initial='@json($selectedEquipmentIds)'>
                                            @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
                                                'label' => null,
                                                'id' => 'param-equipment-'.$id,
                                                'name' => 'param_equipment_'.$id,
                                                'placeholder' => 'Select equipment…',
                                                'options' => $equipmentOptions,
                                                'selected' => $selectedEquipmentIds,
                                                'variant' => 'burgundy',
                                                'multiple' => true,
                                                'disabled' => (bool) $parametersDisabled,
                                                'wireIgnore' => false,
                                                'extraSelectClass' => 'param-equipment-select2 no-select2',
                                                'selectedValuesJson' => json_encode($selectedEquipmentIds),
                                            ])
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="custom-control custom-checkbox text-center">
                                            <input type="checkbox" class="custom-control-input" id="sub_{{ $id }}"
                                                wire:model.defer="parametersForm.{{ $id }}.subcontracted"
                                                @if($parametersDisabled) disabled @endif>
                                            <label class="custom-control-label" for="sub_{{ $id }}"></label>
                                        </div>
                                    </td>
                                    <td style="min-width: 160px;">
                                        @if(!empty($param['subcontracted_lab_name']))
                                            @php
                                                $subLabLabel = (string) $param['subcontracted_lab_name'];
                                                $isDispatchStatusLabel = in_array($subLabLabel, ['Awaiting dispatch', 'Dispatched'], true);
                                            @endphp
                                            <span class="badge {{ $isDispatchStatusLabel ? 'badge-warning' : 'badge-info' }} border text-wrap text-left" title="{{ $subLabLabel }}">
                                                <i class="mdi {{ $isDispatchStatusLabel ? 'mdi-clock-outline' : 'mdi-earth' }}"></i> {{ $subLabLabel }}
                                            </span>
                                        @elseif(!empty($parametersForm[$id]['subcontracted']) || !empty($param['subcontracted']))
                                            <span class="badge badge-warning border text-wrap text-left" title="Awaiting dispatch">
                                                <i class="mdi mdi-clock-outline"></i> Awaiting dispatch
                                            </span>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="custom-control custom-checkbox text-center">
                                            <input type="checkbox" class="custom-control-input" id="accr_{{ $id }}"
                                                wire:model.defer="parametersForm.{{ $id }}.accredited"
                                                @if($parametersDisabled) disabled @endif>
                                            <label class="custom-control-label" for="accr_{{ $id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border text-center py-5 mb-0">
                        <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 2.5rem;"></i>
                        <p class="mb-0 mt-2 text-muted">
                            @if($parametersSectionFiltered)
                                No parameters for your lab section on this sample.
                            @else
                                No captured results found for this sample.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 sample-parameters-modal__footer">
                    <button type="button" class="ls-btn" wire:click="cancelViewParameters">
                        Close
                    </button>
                    @if($this->hasEditableParameters && ! empty($sampleParameters))
                    <button type="button" class="ls-btn ls-btn--accent" wire:click="saveParameters"
                        wire:loading.attr="disabled" wire:target="saveParameters">
                        <span wire:loading.remove wire:target="saveParameters">
                            <i class="mdi mdi-content-save" aria-hidden="true"></i> Save changes
                        </span>
                        <span wire:loading wire:target="saveParameters">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Saving…
                        </span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        <button type="button"
            class="spm-ext-nav spm-ext-nav--right {{ $canGoNextParameterSample ? 'is-active' : 'is-disabled' }}"
            @if($canGoNextParameterSample) wire:click="nextParameterSample" @else disabled @endif
            wire:loading.attr="disabled"
            wire:target="previousParameterSample,nextParameterSample,switchParameterSample,viewParameters,viewSingleSampleParameters"
            title="{{ $nextParameterSampleCode !== '' ? 'Next: '.format_sample_code($nextParameterSampleCode) : 'Next sample' }}"
            aria-label="{{ $nextParameterSampleCode !== '' ? 'Next sample '.format_sample_code($nextParameterSampleCode) : 'Next sample' }}">
            @if($nextParameterSampleCode !== '')
                <span class="spm-ext-nav__cue">{{ format_sample_code($nextParameterSampleCode) }}</span>
            @endif
            <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

    @if($showResultImportVersionsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog"
            style="background: rgba(15, 23, 42, 0.45); z-index: 1060;"
            wire:keydown.escape.window="closeResultImportVersionsModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-history mr-1"></i> Result import versions
                        </h5>
                        <button type="button" class="close" wire:click="closeResultImportVersionsModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($resultImportVersions === [])
                            <p class="text-muted mb-0">No uploaded result versions yet. Download current data, edit, and import to create version 1.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Version</th>
                                            <th>Status</th>
                                            <th>Applied</th>
                                            <th>By</th>
                                            <th>Summary</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultImportVersions as $versionRow)
                                            <tr>
                                                <td><strong>v{{ $versionRow['version'] }}</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $versionRow['status'] === 'applied' ? 'success' : ($versionRow['status'] === 'rolled_back' ? 'warning' : 'secondary') }}">
                                                        {{ $versionRow['status'] }}
                                                    </span>
                                                    @if(!empty($versionRow['notes']))
                                                        <div class="small text-muted">{{ $versionRow['notes'] }}</div>
                                                    @endif
                                                </td>
                                                <td nowrap>{{ $versionRow['applied_at'] ?? '—' }}</td>
                                                <td>{{ $versionRow['user_name'] ?? '—' }}</td>
                                                <td>{{ $versionRow['summary'] }}</td>
                                                <td class="text-right" nowrap>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        wire:click="downloadResultImportVersion('{{ $versionRow['id'] }}')">
                                                        <i class="mdi mdi-download"></i>
                                                    </button>
                                                    @if($versionRow['status'] !== 'applied' && ! $parametersReadOnly)
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-warning"
                                                            wire:click="restoreResultImportVersion('{{ $versionRow['id'] }}')"
                                                            wire:confirm="Restore version {{ $versionRow['version'] }}? Live results will match that Excel.">
                                                            <i class="mdi mdi-restore"></i> Restore
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeResultImportVersionsModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showResultImportVersionsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog"
            style="background: rgba(15, 23, 42, 0.45); z-index: 1060;"
            wire:keydown.escape.window="closeResultImportVersionsModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-history mr-1"></i> Result import versions
                        </h5>
                        <button type="button" class="close" wire:click="closeResultImportVersionsModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($resultImportVersions === [])
                            <p class="text-muted mb-0">No uploaded result versions yet. Download current data, edit, and import to create version 1.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Version</th>
                                            <th>Status</th>
                                            <th>Applied</th>
                                            <th>By</th>
                                            <th>Summary</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultImportVersions as $versionRow)
                                            <tr>
                                                <td><strong>v{{ $versionRow['version'] }}</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $versionRow['status'] === 'applied' ? 'success' : ($versionRow['status'] === 'rolled_back' ? 'warning' : 'secondary') }}">
                                                        {{ $versionRow['status'] }}
                                                    </span>
                                                    @if(!empty($versionRow['notes']))
                                                        <div class="small text-muted">{{ $versionRow['notes'] }}</div>
                                                    @endif
                                                </td>
                                                <td nowrap>{{ $versionRow['applied_at'] ?? '—' }}</td>
                                                <td>{{ $versionRow['user_name'] ?? '—' }}</td>
                                                <td>{{ $versionRow['summary'] }}</td>
                                                <td class="text-right" nowrap>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        wire:click="downloadResultImportVersion('{{ $versionRow['id'] }}')">
                                                        <i class="mdi mdi-download"></i>
                                                    </button>
                                                    @if($versionRow['status'] !== 'applied' && ! $parametersReadOnly)
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-warning"
                                                            wire:click="restoreResultImportVersion('{{ $versionRow['id'] }}')"
                                                            wire:confirm="Restore version {{ $versionRow['version'] }}? Live results will match that Excel.">
                                                            <i class="mdi mdi-restore"></i> Restore
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeResultImportVersionsModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .sample-parameters-modal .modal-dialog {
            max-width: min(96vw, 1280px);
            width: calc(100% - 8.5rem);
            margin: 1.25rem auto;
        }

        .sample-parameters-modal .spm-ext-nav {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1065;
            width: 3.5rem;
            height: 7.5rem;
            border: 0;
            border-radius: 0.85rem;
            background: rgba(30, 41, 59, 0.88);
            color: #f8fafc;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
            transition: background 0.15s ease, transform 0.15s ease, opacity 0.15s ease;
            padding: 0.5rem 0.25rem;
        }

        .sample-parameters-modal .spm-ext-nav i {
            font-size: 2rem;
            line-height: 1;
        }

        .sample-parameters-modal .spm-ext-nav--left {
            left: 1.25rem;
        }

        .sample-parameters-modal .spm-ext-nav--right {
            right: 1.25rem;
        }

        .sample-parameters-modal .spm-ext-nav.is-active:hover {
            background: var(--color-primary, #6d0a0e);
            transform: translateY(-50%) scale(1.03);
        }

        .sample-parameters-modal .spm-ext-nav.is-active {
            background: var(--color-primary, #6d0a0e);
            color: #fff;
        }

        .sample-parameters-modal .spm-ext-nav.is-disabled,
        .sample-parameters-modal .spm-ext-nav:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .sample-parameters-modal .spm-ext-nav__cue {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: none;
            color: inherit;
            line-height: 1.2;
            white-space: nowrap;
            max-height: 5.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sample-parameters-modal__sections {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 0.7rem;
            border-radius: 10px;
            border: 1px solid var(--ls-blue-soft-border, #dbeafe);
            background: var(--ls-blue-soft, #eff6ff);
        }

        .sample-parameters-modal__sections-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ls-muted, #64748b);
            margin-right: 0.15rem;
        }

        .sample-parameters-modal__sections-empty {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        @media (max-width: 991.98px) {
            .sample-parameters-modal .modal-dialog {
                width: calc(100% - 5.5rem);
            }

            .sample-parameters-modal .spm-ext-nav {
                width: 2.75rem;
                height: 5.5rem;
            }

            .sample-parameters-modal .spm-ext-nav--left {
                left: 0.5rem;
            }

            .sample-parameters-modal .spm-ext-nav--right {
                right: 0.5rem;
            }

            .sample-parameters-modal .spm-ext-nav__cue {
                display: none;
            }
        }

        .sample-parameters-modal__content {
            border: none;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
            overflow: hidden;
            max-height: calc(100vh - 2.5rem);
            display: flex;
            flex-direction: column;
        }

        .sample-parameters-modal .sample-parameters-row--readonly td {
            background-color: #f8fafc;
        }

        .sample-parameters-modal .sample-parameters-row--readonly input:disabled,
        .sample-parameters-modal .sample-parameters-row--readonly select:disabled {
            cursor: not-allowed;
        }

        .btn-icon {
            position: relative;
        }

        .interpretation-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            font-size: 0.55rem;
            padding: 0.15rem 0.3rem;
            line-height: 1;
            border-radius: 50%;
        }

        .sample-parameters-modal__header {
            padding: 0.85rem 1.05rem !important;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--ls-accent, #8b1e2d) 0%, color-mix(in srgb, var(--ls-accent, #8b1e2d) 78%, #1e293b) 100%);
            color: #fff;
        }

        .sample-parameters-modal__header-main {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            width: 100%;
        }

        .sample-parameters-modal__eyebrow {
            margin: 0 0 0.2rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            opacity: 0.85;
            color: #fff;
        }

        .sample-parameters-modal__title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            color: #fff;
        }

        .sample-parameters-modal__subtitle {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .sample-parameters-modal__code {
            display: inline-flex;
            align-items: center;
            padding: 0.12rem 0.45rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-weight: 600;
            color: #fff;
        }

        .sample-parameters-modal__meta {
            opacity: 0.85;
        }

        .sample-parameters-modal__header-actions {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
        }

        .sample-parameters-modal__close {
            flex-shrink: 0;
            width: 1.75rem;
            height: 1.75rem;
            margin: 0;
            padding: 0;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 1;
            opacity: 1;
            text-shadow: none;
        }

        .sample-parameters-modal__close:hover {
            background: rgba(255, 255, 255, 0.28);
            color: #fff;
        }

        .sample-parameters-modal__excel-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            min-height: 34px;
            padding: 0.3rem 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.14);
            color: #fff !important;
            border-radius: 8px;
            font-weight: 600;
        }

        .sample-parameters-modal__excel-btn:hover,
        .sample-parameters-modal__excel-btn:focus {
            background: rgba(255, 255, 255, 0.24);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff !important;
        }

        .sample-parameters-modal__excel-btn .mdi {
            color: #fff !important;
            font-size: 1rem;
            line-height: 1;
        }

        .sample-parameters-modal__excel-menu {
            min-width: 270px;
            margin-top: 0.35rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.35rem 0;
            z-index: 1080;
        }

        .sample-parameters-modal__excel-menu .dropdown-item {
            font-size: 0.875rem;
            padding: 0.45rem 0.9rem;
        }

        .sample-parameters-modal__excel-upload label {
            color: #64748b !important;
            font-weight: 500;
        }

        .sample-parameters-modal__body {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.85rem 1.15rem;
            background: #f8fafc;
        }

        .sample-parameters-modal__table-wrap {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            overflow: auto;
            max-height: min(56vh, 560px);
            max-width: 100%;
        }

        .sample-parameters-group-cell {
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.5rem 0.75rem !important;
        }

        .sample-parameters-group-label {
            font-weight: 700;
            font-size: 0.72rem;
            color: var(--ls-ink, #0f172a);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .sample-parameters-table {
            font-size: 0.8125rem;
            margin-bottom: 0;
            min-width: 980px;
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

        .sample-parameters-table thead th:nth-child(1),
        .sample-parameters-table tbody td:nth-child(1) {
            position: sticky;
            left: 0;
            z-index: 3;
            background: #fff;
            box-shadow: 1px 0 0 #e2e8f0;
        }

        .sample-parameters-table thead th:nth-child(1) {
            background: #f1f5f9;
            z-index: 4;
        }

        .sample-parameters-table thead th:nth-child(2),
        .sample-parameters-table tbody td:nth-child(2) {
            position: sticky;
            left: 100px;
            z-index: 3;
            background: #fff;
            box-shadow: 1px 0 0 #e2e8f0;
        }

        .sample-parameters-table thead th:nth-child(2) {
            background: #f1f5f9;
            z-index: 4;
        }

        .sample-parameters-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .sample-parameters-table tbody tr:hover td:nth-child(1),
        .sample-parameters-table tbody tr:hover td:nth-child(2) {
            background: #f8fafc;
        }

        .sample-parameters-table tbody td {
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .sample-parameters-modal__analyte {
            min-width: 150px;
            max-width: 220px;
        }

        .sample-parameters-modal__analyte .font-weight-semibold {
            font-weight: 600;
            word-break: break-word;
        }

        .sample-parameters-modal__footer {
            padding: 0.75rem 1.05rem;
            background: #fafbfc;
            border-top: 1px solid var(--ls-border, #e2e8f0);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
        }

        .sample-parameters-table .form-control-sm {
            border-radius: 8px;
            border-color: #e2e8f0;
        }

        .sample-parameters-table .form-control-sm:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .param-equipment-select2-wrap {
            min-width: 200px;
            max-width: 320px;
        }

        .param-equipment-select2-wrap .ls-field {
            margin: 0;
            gap: 0;
        }

        .param-equipment-select2-wrap .select2-container {
            width: 100% !important;
        }

        .sample-spec-search-wrap {
            min-width: 240px;
            max-width: 320px;
            width: 100%;
        }

        .sample-spec-search-wrap .ls-field {
            margin: 0;
            gap: 0;
        }

        .samples-config-table .sample-spec-search-wrap .ls-combo__control {
            min-height: 32px;
        }

        /* Glass burgundy chips for equipment (LS ls-select2-multi-dropdown-search) */
        .sample-parameters-table .ls-select2-multi.ls-select2-burgundy .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin: 0 !important;
            padding: 0.12rem 0.4rem 0.12rem 0.3rem !important;
            font-size: 0.68rem !important;
            line-height: 1.2 !important;
            font-weight: 600;
            border-radius: 6px !important;
            color: var(--ls-accent, #8b1e2d) !important;
            background: color-mix(in srgb, var(--ls-accent, #8b1e2d) 18%, transparent) !important;
            border: 1px solid color-mix(in srgb, var(--ls-accent, #8b1e2d) 38%, transparent) !important;
            box-shadow:
                inset 0 1px 0 color-mix(in srgb, #ffffff 45%, transparent),
                0 1px 2px color-mix(in srgb, var(--ls-accent, #8b1e2d) 12%, transparent);
            backdrop-filter: blur(10px) saturate(1.25);
            -webkit-backdrop-filter: blur(10px) saturate(1.25);
        }

        .sample-parameters-table .ls-select2-multi.ls-select2-burgundy .select2-selection__choice__remove {
            color: color-mix(in srgb, var(--ls-accent, #8b1e2d) 75%, #64748b) !important;
            background: transparent !important;
        }

        .sample-parameters-table .ls-select2-multi.ls-select2-burgundy .select2-selection__choice__remove:hover {
            color: var(--ls-accent, #8b1e2d) !important;
        }

        .sample-parameters-table .ls-select2-multi .select2-container--default .select2-selection--multiple {
            min-height: 30px !important;
            border: 1.5px solid color-mix(in srgb, var(--ls-accent, #8b1e2d) 42%, #cbd5e1) !important;
            background: color-mix(in srgb, var(--ls-accent, #8b1e2d) 4%, #ffffff) !important;
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ls-accent, #8b1e2d) 8%, transparent);
        }

        .sample-parameters-table .ls-select2-multi .select2-container--default.select2-container--focus .select2-selection--multiple,
        .sample-parameters-table .ls-select2-multi .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: var(--ls-accent, #8b1e2d) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-accent, #8b1e2d) 18%, transparent) !important;
        }
    </style>
    @endif

    {{-- Comments & Interpretations Modal (Recommendations + Notes only; Remarks/SoC are auto from specifications) --}}
    @if($showCommentsModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
        wire:key="comments-modal-{{ $editingCommentsSampleId }}">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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
                        this.initEditor('#comments-main-editor', 'main_body', @js($commentsForm['main_body']));
                        this.initEditor('#comments-notes-editor', 'notes_body', @js($commentsForm['notes_body']));
                    },
                    syncToWire() {
                        if (typeof tinymce === 'undefined') {
                            return;
                        }
                        tinymce.triggerSave();
                        $wire.set('commentsForm.main_body', tinymce.get('comments-main-editor')?.getContent() ?? '');
                        $wire.set('commentsForm.notes_body', tinymce.get('comments-notes-editor')?.getContent() ?? '');
                    },
                    isEditorEmpty(editorId) {
                        if (typeof tinymce === 'undefined') {
                            return true;
                        }
                        const editor = tinymce.get(editorId);
                        if (!editor) {
                            return true;
                        }
                        const text = (editor.getContent({ format: 'text' }) || '').trim();
                        return text === '';
                    },
                    applyDefaults() {
                        this.syncToWire();
                        const hasContent = !this.isEditorEmpty('comments-notes-editor');
                        if (hasContent && !confirm('Replace current Notes with generated defaults? Recommendations will not be changed.')) {
                            return;
                        }
                        $wire.applyCommentDefaults();
                    },
                    destroyEditors() {
                        if (typeof tinymce !== 'undefined') {
                            tinymce.remove('#comments-main-editor, #comments-notes-editor');
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
                x-init="
                    setTimeout(() => initAll(), 150);
                    const stopListening = Livewire.on('comments-defaults-applied', (payload) => {
                        const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
                        if (typeof tinymce === 'undefined') {
                            return;
                        }
                        tinymce.get('comments-notes-editor')?.setContent(data.notesBody ?? '');
                        $wire.set('commentsForm.notes_body', data.notesBody ?? '');
                    });
                    return () => {
                        if (typeof stopListening === 'function') {
                            stopListening();
                        }
                        destroyEditors();
                    };
                ">
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
                    <p class="text-muted small mb-3">
                        Report Remarks (statement of conformity) are generated automatically from the sample specification pass/fail statements.
                    </p>
                    <div class="comments-interpretations-modal" wire:ignore>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:loading.attr="disabled"
                        wire:target="applyCommentDefaults"
                        @click="applyDefaults()">
                        <span wire:loading.remove wire:target="applyCommentDefaults">
                            <i class="mdi mdi-auto-fix"></i> Apply default notes
                        </span>
                        <span wire:loading wire:target="applyCommentDefaults">
                            <i class="mdi mdi-loading mdi-spin"></i> Applying...
                        </span>
                    </button>
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

    {{-- Accelerated Shelf-Life Study Conditions Modal --}}
    @if($showShelfLifeConditionsModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
        wire:key="shelf-life-conditions-modal-{{ $editingShelfLifeSampleId }}">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-thermometer-lines"></i> Accelerated Shelf-Life Study Conditions
                        @if($editingShelfLifeSampleCode !== '')
                            <small class="text-muted">— {{ format_sample_code($editingShelfLifeSampleCode) }}</small>
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="cancelShelfLifeConditions">
                        <span>&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveShelfLifeConditions">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Study Type</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.study_type"
                                        placeholder="Accelerated Shelf-Life Testing">
                                    @error('shelfLifeConditionsForm.study_type')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Accelerated Temperature</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.accelerated_temperature"
                                        placeholder="e.g. 45 ± 2 °C">
                                    @error('shelfLifeConditionsForm.accelerated_temperature')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Study Duration</label>
                                    <input type="number" min="0" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.study_duration_value"
                                        placeholder="e.g. 6">
                                    @error('shelfLifeConditionsForm.study_duration_value')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <select class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.study_duration_unit">
                                        <option value="days">Days</option>
                                        <option value="weeks">Weeks</option>
                                        <option value="months">Months</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Relative Humidity</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.relative_humidity"
                                        placeholder="e.g. 70 ± 5 % RH">
                                    @error('shelfLifeConditionsForm.relative_humidity')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Evaluation Type</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.evaluation_type"
                                        placeholder="e.g. Chemical, Microbial, Physical and Sensory Evaluation">
                                    @error('shelfLifeConditionsForm.evaluation_type')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sampling Frequency</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.sampling_frequency"
                                        placeholder="e.g. Weekly">
                                    @error('shelfLifeConditionsForm.sampling_frequency')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Declared Shelf Life</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.declared_shelf_life"
                                        placeholder="e.g. 6 Months">
                                    @error('shelfLifeConditionsForm.declared_shelf_life')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Storage Condition</label>
                                    <input type="text" class="form-control"
                                        wire:model.defer="shelfLifeConditionsForm.storage_condition"
                                        placeholder="e.g. 45 °C">
                                    @error('shelfLifeConditionsForm.storage_condition')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label>Notes</label>
                                    <textarea class="form-control" rows="3"
                                        wire:model.defer="shelfLifeConditionsForm.notes"
                                        placeholder="Optional notes..."></textarea>
                                    @error('shelfLifeConditionsForm.notes')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove><i class="mdi mdi-content-save"></i> Save</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                        </button>
                        <button type="button" class="btn btn-default btn-sm text-danger"
                            wire:click="cancelShelfLifeConditions">Cancel</button>
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

        /* Sticky identity columns on Samples configuration (checkbox / action / sample id) */
        .samples-config-table-wrap {
            overflow-x: auto;
            overflow-y: visible;
        }

        .samples-config-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .samples-config-table .samples-sticky-col {
            position: sticky;
            z-index: 2;
            background: #fff;
        }

        .samples-config-table thead .samples-sticky-col {
            z-index: 4;
            background: #f8f9fa;
        }

        .samples-config-table .samples-sticky-col--check {
            left: 0;
            width: 40px;
            min-width: 40px;
            max-width: 40px;
        }

        .samples-config-table .samples-sticky-col--action {
            left: 40px;
            width: 160px;
            min-width: 160px;
            max-width: 180px;
        }

        .samples-config-table .samples-sticky-col--id {
            left: 200px;
            min-width: 180px;
            box-shadow: 2px 0 4px -2px rgba(15, 23, 42, 0.18);
        }

        .samples-config-table tbody tr.table-active .samples-sticky-col,
        .samples-config-table tbody tr.table-active .samples-sticky-col--id {
            background: #e8f2ff;
        }

        .samples-config-table tbody tr:hover .samples-sticky-col {
            background: #f8fafc;
        }

        .samples-config-table tbody tr.table-active:hover .samples-sticky-col {
            background: #e8f2ff;
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
            gap: 4px;
            min-height: 31px;
            padding: 2px 6px;
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
            gap: 3px;
            padding: 2px 6px;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.25;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .tag-badge:hover {
            background-color: #dbeafe;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 0.85rem;
            color: #1e40af;
            opacity: 0.7;
            transition: all 0.15s ease;
        }

        .tag-badge i:hover {
            opacity: 1;
            color: #ef4444;
        }

        .tag-input {
            flex: 1 1 60px;
            min-width: 60px;
            border: none;
            outline: none;
            padding: 1px 2px;
            font-size: 0.8rem;
            line-height: 1.25;
            height: 22px;
            background: transparent;
            color: #1e293b;
        }

        /* Avoid an empty search row under selected pills in the samples table. */
        .samples-analysis-type-select .tag-input--collapsed {
            flex: 0 0 0;
            min-width: 0;
            width: 0;
            height: 0;
            padding: 0;
            margin: 0;
            border: 0;
            opacity: 0;
            pointer-events: none;
        }

        .samples-analysis-type-select .tag-select-input {
            align-content: flex-start;
        }

        td.samples-analysis-type-cell,
        td.samples-analysis-type-cell .samples-analysis-type-select {
            vertical-align: top;
        }

        td.samples-analysis-type-cell .sample-gw-pill {
            margin-top: 2px;
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
                        <h5 class="modal-title text-dark">Edit Specification: {{ $editingStandardData['analyte_name'] }}</h5>
                        <button type="button" class="close" wire:click="cancelEditStandardModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert" style="background-color: #f8f9fa; border-color: #dee2e6; color: #6c757d;">
                            <small>Updating this specification will affect the master setup for this analyte.</small>
                        </div>

                        <div class="form-group mb-2">
                            <label class="text-muted">Previous Value</label>
                            <input type="text" class="form-control form-control-sm" readonly
                                value="{{ $editingStandardData['previous_value'] }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="d-block">Specification Value Type</label>
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
                            <label>Specification Value <span class="text-danger">*</span></label>
                            <select class="form-control grey-input"
                                wire:model.live="editingStandardData.standard_valuetype">
                                <option value="">- Select -</option>
                                @foreach($standardValueOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        @php
                            $selectedSampleSv = collect($standardValueOptions)->firstWhere('id', $editingStandardData['standard_valuetype'] ?? null);
                            $sampleIsValueSelected = $selectedSampleSv && ($selectedSampleSv->code ?? '') === 'IsValue';
                        @endphp
                        @if($sampleIsValueSelected)
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Matrix Operator <span class="text-danger">*</span></label>
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
                                    <label>Actual Value <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control grey-input"
                                        wire:model.defer="editingStandardData.value" placeholder="Value">
                                </div>
                            </div>
                        </div>
                        @endif
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
        // Select2 must not own ordinary Livewire selects (see .cursor/rules frontend ownership).
        // Equipment multi-select is an explicit Select2 (wire:ignore) exception via ls-select2-multi-dropdown-search.
        const releaseLivewireSelects = () => {
            if (!window.jQuery || !window.jQuery.fn.select2) {
                return;
            }

            let destroyed = 0;

            window.jQuery('.batch-samples-panel select.no-select2').each(function () {
                const $select = window.jQuery(this);

                if ($select.hasClass('param-equipment-select2')
                    || $select.hasClass('ls-select2-multi-dropdown-search-el')) {
                    return;
                }

                if ($select.hasClass('select2-hidden-accessible')) {
                    try {
                        $select.select2('destroy');
                        destroyed += 1;
                    } catch (e) {
                        // Ignore already-destroyed instances.
                    }
                }

                $select.show().css('display', '');
            });

            if (destroyed > 0) {
                console.log('[Samples] destroyed Select2 wrappers:', destroyed);
            }
        };

        const clampLsSelect2Search = ($el) => {
            const $container = $el.next('.select2-container');
            $container.find('.select2-search--inline .select2-search__field').attr(
                'style',
                'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
            );
            $container.css({ maxWidth: '100%', overflow: 'hidden' });
        };

        const uniqueStringIds = (values) => {
            const seen = {};
            const out = [];
            (Array.isArray(values) ? values : (values ? [values] : [])).forEach((value) => {
                const id = String(value ?? '').trim();
                if (id === '' || seen[id]) {
                    return;
                }
                seen[id] = true;
                out.push(id);
            });
            return out;
        };

        const destroyParamEquipmentSelect2 = ($el) => {
            $el.off('change.paramEquipmentSelect2');
            $el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch');
            if ($el.data('select2') || $el.hasClass('select2-hidden-accessible')) {
                try {
                    $el.select2('destroy');
                } catch (e) {
                    // already destroyed
                }
            }
            // Remove orphaned containers left by Livewire morph / double init.
            $el.siblings('.select2-container').remove();
        };

        const wireLsMultiDropdownSearch = ($el) => {
            $el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch')
                .on('select2:open.lsDdSearch', function () {
                    clampLsSelect2Search($el);
                    const $dropdown = window.jQuery('.select2-container--open .select2-dropdown');
                    const select2Instance = $el.data('select2');
                    const syncNativeSearch = (q) => {
                        // Single-select uses .select2-search--dropdown (often visually hidden);
                        // multi-select uses inline search in the selection.
                        let $hidden = $dropdown.find('.select2-search--dropdown .select2-search__field');
                        if (!$hidden.length && select2Instance && select2Instance.$selection) {
                            $hidden = select2Instance.$selection.find('.select2-search__field');
                        }
                        if (!$hidden.length) {
                            $hidden = window.jQuery('.select2-container--open .select2-search--inline .select2-search__field');
                        }
                        if (!$hidden.length) {
                            return;
                        }
                        $hidden.val(q);
                        // Select2 listens to keyup on its native search field — do not call
                        // select2Instance.trigger('query') (conflicts with Livewire proxies).
                        $hidden.trigger('input').trigger('keyup');
                    };
                    const $existing = $dropdown.find('.ls-dd-search');
                    if ($existing.length) {
                        const $existingInput = $existing.find('input');
                        $existingInput.val('');
                        syncNativeSearch('');
                        $existingInput.trigger('focus');
                        return;
                    }
                    const $box = window.jQuery('<div class="ls-dd-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" placeholder="Search…" autocomplete="off"></div>');
                    $dropdown.prepend($box);
                    const $input = $box.find('input');
                    $input.on('input keyup', function () {
                        syncNativeSearch($input.val() || '');
                    });
                    setTimeout(function () {
                        $input.trigger('focus');
                    }, 0);
                })
                .on('select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch', function () {
                    clampLsSelect2Search($el);
                });
        };

        const readParamEquipmentInitial = ($el, $wrap) => {
            // Prefer the live select value when Select2 was already active (user edits).
            let initial = uniqueStringIds($el.val() || []);
            if (initial.length) {
                return initial;
            }

            try {
                initial = JSON.parse($wrap.attr('data-initial') || '[]');
            } catch (e) {
                initial = [];
            }

            return uniqueStringIds(initial);
        };

        const initParamEquipmentSelect2 = () => {
            if (!window.jQuery || !window.jQuery.fn.select2) {
                return;
            }

            const $modal = window.jQuery('.sample-parameters-modal');
            if (!$modal.length) {
                return;
            }

            const $dropdownParent = $modal.find('.modal-content').first().length
                ? $modal.find('.modal-content').first()
                : $modal;

            // Collapse Livewire morph leftovers: keep one select per wrap.
            $modal.find('.param-equipment-select2-wrap').each(function () {
                const $wrap = window.jQuery(this);
                const $selects = $wrap.find('select.param-equipment-select2');
                if ($selects.length <= 1) {
                    return;
                }
                $selects.slice(1).each(function () {
                    destroyParamEquipmentSelect2(window.jQuery(this));
                    window.jQuery(this).closest('.ls-field').remove();
                });
            });

            $modal.find('select.param-equipment-select2.ls-select2-multi-dropdown-search-el').each(function () {
                const $el = window.jQuery(this);
                const $wrap = $el.closest('.param-equipment-select2-wrap');
                const alreadyReady = $el.hasClass('select2-hidden-accessible')
                    && $el.data('select2')
                    && $el.next('.select2-container').length === 1
                    && $wrap.find('.select2-container').length === 1;

                // Healthy instance — leave it alone so user edits are not reset.
                if (alreadyReady) {
                    clampLsSelect2Search($el);
                    return;
                }

                const initial = readParamEquipmentInitial($el, $wrap);
                destroyParamEquipmentSelect2($el);

                $el.select2({
                    width: '100%',
                    placeholder: $el.data('placeholder') || 'Select equipment…',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $dropdownParent,
                    dropdownCssClass: 'ls-select2-dropdown-search',
                    templateResult: function (data) {
                        if (!data.id) {
                            return data.text;
                        }
                        const selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
                        const $row = window.jQuery('<span class="ls-select2-meta-row"><span class="ls-select2-check">' + (selected ? '✓' : '') + '</span><span class="ls-select2-meta-row__label"></span></span>');
                        $row.find('.ls-select2-meta-row__label').text(data.text);
                        return $row;
                    },
                    escapeMarkup: function (m) { return m; },
                });

                wireLsMultiDropdownSearch($el);
                clampLsSelect2Search($el);

                // Suppress Livewire sync while applying the initial selection.
                $el.data('paramEquipmentSuppressSync', true);
                $el.val(initial).trigger('change.select2');
                $el.data('paramEquipmentSuppressSync', false);
                $wrap.attr('data-initial', JSON.stringify(initial));

                $el.off('change.paramEquipmentSelect2').on('change.paramEquipmentSelect2', function () {
                    if ($el.data('paramEquipmentSuppressSync')) {
                        return;
                    }

                    const paramId = String($wrap.data('param-id') || '');
                    if (!paramId || !window.Livewire) {
                        return;
                    }

                    const vals = uniqueStringIds($el.val() || []);
                    if (JSON.stringify(($el.val() || []).map(String)) !== JSON.stringify(vals)) {
                        $el.data('paramEquipmentSuppressSync', true);
                        $el.val(vals).trigger('change.select2');
                        $el.data('paramEquipmentSuppressSync', false);
                    }

                    $wrap.attr('data-initial', JSON.stringify(vals));
                    const componentEl = $el.closest('[wire\\:id]')[0];
                    if (!componentEl) {
                        return;
                    }
                    const component = Livewire.find(componentEl.getAttribute('wire:id'));
                    if (component) {
                        component.set('parametersForm.' + paramId + '.equipment_ids', vals);
                    }
                });
            });
        };

        const syncParamSelectWidgets = () => {
            if (!window.jQuery || !window.jQuery.fn.select2) {
                return false;
            }

            releaseLivewireSelects();
            initParamEquipmentSelect2();
            return true;
        };

        const bootParamSelectWidgets = (attempt = 0) => {
            let done = false;
            try {
                done = syncParamSelectWidgets();
            } catch (e) {
                console.error('[Samples] Select2 boot error', e);
                done = false;
            }
            if (done || attempt >= 40) {
                return;
            }
            window.setTimeout(function () {
                bootParamSelectWidgets(attempt + 1);
            }, 50);
        };

        // Keep a stable window pointer so a single hook registration always calls the latest boot.
        window.__bootSamplesParamEquipmentSelect2 = bootParamSelectWidgets;

        if (!window.__samplesParamEquipmentSelect2Bound) {
            window.__samplesParamEquipmentSelect2Bound = true;
            document.addEventListener('livewire:navigated', () => {
                if (typeof window.__bootSamplesParamEquipmentSelect2 === 'function') {
                    window.__bootSamplesParamEquipmentSelect2();
                }
            });
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    if (typeof window.__bootSamplesParamEquipmentSelect2 === 'function') {
                        window.__bootSamplesParamEquipmentSelect2();
                    }
                });
            });
        }

        window.setTimeout(function () {
            bootParamSelectWidgets();
        }, 0);
    </script>
    @endscript
</div>