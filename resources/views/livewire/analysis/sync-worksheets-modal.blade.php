<div>
    @if($showModal)
        <div class="modal fade show d-block sync-worksheets-modal" tabindex="-1" style="background: rgba(15, 23, 42, 0.45);" wire:keydown.escape.window="closeModal">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content sync-worksheets-modal__content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title mb-1">
                                <i class="mdi mdi-sync text-success"></i>
                                Sync worksheets to samples
                            </h5>
                            <p class="text-muted small mb-0">
                                {{ $this->analysisType?->name ?? 'Analysis type' }}
                            </p>
                        </div>
                        <button type="button" class="close" wire:click="closeModal" @disabled($syncing)>
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body pt-3">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($message)
                            <div
                                wire:key="sync-worksheets-feedback-{{ md5($message) }}"
                                class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show"
                                role="alert"
                            >
                                {{ $message }}
                                <button type="button" class="btn-close" wire:click="$set('message', '')"></button>
                            </div>
                        @endif

                        <div class="sync-worksheets-info card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <div class="sync-worksheets-info__icon flex-shrink-0">
                                        <i class="mdi mdi-information-outline"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-2">What this action does</h6>
                                        <p class="text-muted small mb-2 mb-md-3">
                                            This updates <strong>existing captured results</strong> for the samples you select.
                                            Configuration is copied from this analysis type and its parameters:
                                            procedure worksheets, method sequences, formulas, grouped pipelines, and hybrid worksheets.
                                        </p>
                                        <ul class="small text-muted mb-3 ps-3">
                                            <li><strong>Grouped / hybrid</strong> — applied at analysis-type level to every parameter on the sample.</li>
                                            <li><strong>Procedure / formula / method sequence</strong> — applied per parameter, matching each analyte’s element configuration.</li>
                                        </ul>

                                        @php($summary = $this->configSummary)
                                        <div class="sync-worksheets-config row g-2">
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__pill">
                                                    <span class="label">Procedure (type)</span>
                                                    <span class="value">{{ $summary['procedure_name'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__pill">
                                                    <span class="label">Grouped pipeline</span>
                                                    <span class="value">{{ $summary['grouped_name'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__pill">
                                                    <span class="label">Hybrid worksheet</span>
                                                    <span class="value">{{ $summary['hybrid_name'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__stat">
                                                    <span class="value">{{ $summary['elements_with_procedure'] }}</span>
                                                    <span class="label">parameters with procedure</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__stat">
                                                    <span class="value">{{ $summary['elements_with_formula'] }}</span>
                                                    <span class="label">calculated formulas</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__stat">
                                                    <span class="value">{{ $summary['elements_with_method_sequence'] }}</span>
                                                    <span class="label">method sequences</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="sync-worksheets-config__stat">
                                                    <span class="value">{{ $summary['elements_with_log_entry'] ?? 0 }}</span>
                                                    <span class="label">log entry worksheets</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex align-items-center gap-2">
                                <i class="mdi mdi-map-marker-path text-primary"></i>
                                Workflow stages
                                <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-3">
                                Select one or more batch workflow stages. Samples linked to this analysis type in matching batches will appear below.
                            </p>
                            <div class="sync-worksheets-stages">
                                @foreach($this->workflowStageOptions as $stage)
                                    @php($isSelected = in_array($stage, $selectedWorkflowStatuses, true))
                                    <button
                                        type="button"
                                        class="sync-worksheets-stage-chip {{ $isSelected ? 'is-selected' : '' }}"
                                        wire:click="toggleWorkflowStatus(@js($stage))"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleWorkflowStatus,loadSampleRows,updatedSelectedWorkflowStatuses"
                                    >
                                        @if($isSelected)
                                            <i class="mdi mdi-check-circle"></i>
                                        @else
                                            <i class="mdi mdi-checkbox-blank-circle-outline"></i>
                                        @endif
                                        {{ getSampleWorkflowStageLabel($stage) }}
                                    </button>
                                @endforeach
                            </div>
                            @error('selectedWorkflowStatuses')
                                <span class="text-danger small d-block mt-2">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($selectedWorkflowStatuses !== [])
                            <div class="sync-worksheets-samples card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                                    <div>
                                        <h6 class="mb-0">Samples to sync</h6>
                                        <span class="text-muted small">
                                            @if($loadingSamples)
                                                Loading samples…
                                            @else
                                                {{ count($sampleRows) }} sample{{ count($sampleRows) === 1 ? '' : 's' }}
                                                @if($previewCapturedCount > 0)
                                                    · {{ $previewCapturedCount }} captured result{{ $previewCapturedCount === 1 ? '' : 's' }}
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                    @if(count($sampleRows) > 0)
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" wire:click="toggleSelectAllSamples(true)">
                                                Select all
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" wire:click="toggleSelectAllSamples(false)">
                                                Select none
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-0">
                                    <div wire:loading wire:target="toggleWorkflowStatus,loadSampleRows,updatedSelectedWorkflowStatuses" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="text-muted small mt-2 mb-0">Finding samples…</p>
                                    </div>

                                    <div wire:loading.remove wire:target="toggleWorkflowStatus,loadSampleRows,updatedSelectedWorkflowStatuses">
                                        @if(count($sampleRows) === 0)
                                            <div class="text-center text-muted py-5 px-3">
                                                <i class="mdi mdi-flask-empty-outline" style="font-size: 2.5rem;"></i>
                                                <p class="mb-0 mt-2">No samples found for the selected stages and this analysis type.</p>
                                            </div>
                                        @else
                                            <div class="table-responsive" style="max-height: 320px;">
                                                <table class="table table-hover table-sm mb-0 sync-worksheets-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 3rem;"></th>
                                                            <th>Sample code</th>
                                                            <th>Batch code</th>
                                                            <th>Batch status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sampleRows as $index => $row)
                                                            <tr wire:key="sync-sample-{{ $row['sample_detail_id'] }}">
                                                                <td class="align-middle">
                                                                    <input
                                                                        type="checkbox"
                                                                        class="form-check-input m-0"
                                                                        wire:model.live="selectedSampleIds.{{ $index }}"
                                                                    >
                                                                </td>
                                                                <td class="align-middle fw-semibold">{{ $row['sample_code'] }}</td>
                                                                <td class="align-middle">{{ $row['batch_code'] }}</td>
                                                                <td class="align-middle">
                                                                    <span class="badge badge-light text-dark border">
                                                                        {{ getSampleWorkflowStageLabel($row['batch_status']) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @error('selectedSampleIds')
                                <span class="text-danger small d-block mt-2">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeModal" @disabled($syncing)>
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-success px-4"
                            wire:click.prevent="syncWorksheets"
                            wire:loading.attr="disabled"
                            wire:target="syncWorksheets"
                            @if($syncing || $this->selectedSampleCount === 0 || count($selectedWorkflowStatuses) === 0) disabled @endif
                        >
                            <span wire:loading.remove wire:target="syncWorksheets">
                                <i class="mdi mdi-sync"></i>
                                Sync worksheets
                                @if($this->selectedSampleCount > 0)
                                    ({{ $this->selectedSampleCount }} sample{{ $this->selectedSampleCount === 1 ? '' : 's' }})
                                @endif
                            </span>
                            <span wire:loading wire:target="syncWorksheets">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Syncing…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .sync-worksheets-modal__content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        }

        .sync-worksheets-info {
            border-radius: 14px;
            background: linear-gradient(135deg, #ecfdf3 0%, #f6fffa 55%, #ffffff 100%);
            border: 1px solid rgba(25, 135, 84, 0.12);
        }

        .sync-worksheets-info__icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #198754;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.15);
        }

        .sync-worksheets-config__pill {
            background: #fff;
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            border: 1px solid #e9ecef;
            min-height: 100%;
        }

        .sync-worksheets-config__pill .label,
        .sync-worksheets-config__stat .label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
            margin-bottom: 0.15rem;
        }

        .sync-worksheets-config__pill .value,
        .sync-worksheets-config__stat .value {
            display: block;
            font-weight: 600;
            color: #212529;
            font-size: 0.9rem;
            word-break: break-word;
        }

        .sync-worksheets-config__stat {
            background: #fff;
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            border: 1px solid #e9ecef;
            text-align: center;
        }

        .sync-worksheets-config__stat .value {
            font-size: 1.25rem;
            color: #198754;
        }

        .sync-worksheets-stages {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .sync-worksheets-stage-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #495057;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sync-worksheets-stage-chip:hover {
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .sync-worksheets-stage-chip.is-selected {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .sync-worksheets-samples {
            border-radius: 14px;
            overflow: hidden;
        }

        .sync-worksheets-table thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 1;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6c757d;
            border-top: none;
        }
    </style>
</div>
