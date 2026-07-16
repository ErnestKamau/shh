                    @if($stageHeaders->count() == 0)
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> 
                            No method sequences found for this batch. Method sequences are available for samples that have analysis elements configured with stage headers.
                        </div>
                    @else
                        <div id="method-sequences-container"
                             data-batch-id="{{ $batch->id }}"
                             data-stage-headers='@json($stageHeadersPayload)'>
                            
                            <ul class="nav nav-tabs" id="sequence-tabs" role="tablist"></ul>
                            
                            <div class="tab-content pt-3" id="sequence-tabs-content"></div>
                        </div>
                    @endif

<!-- Create Run Modal -->
<div class="modal fade" id="create-run-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom-0 pb-0" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title d-flex align-items-center" style="font-weight: 700; color: #1e293b;">
                    <div class="bg-primary text-white rounded-circle p-2 mr-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="mdi mdi-plus" style="font-size: 18px;"></i>
                    </div>
                    Create New Run
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="font-size: 24px;">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Success Message -->
                <div id="create-run-success-alert" class="alert alert-success border-0 shadow-sm mb-4 d-none" style="background: #f0fdf4; border-left: 4px solid #22c55e !important; border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle-outline text-success mr-3" style="font-size: 24px;"></i>
                        <div>
                            <h6 class="mb-0" style="font-weight: 700; color: #15803d;">Run created successfully!</h6>
                        </div>
                    </div>
                </div>

                <!-- Guide Section -->
                <div class="alert alert-info border-0 shadow-sm mb-4" style="background: #f0f9ff; border-left: 4px solid #0ea5e9 !important; border-radius: 8px;">
                    <div class="d-flex">
                        <i class="mdi mdi-information-outline text-info mr-3" style="font-size: 24px;"></i>
                        <div>
                            <h6 class="mb-1" style="font-weight: 700; color: #0369a1;">How it works</h6>
                            <p class="mb-0 text-muted" style="font-size: 13px; line-height: 1.5;">
                                Select samples to group them into a new analysis run. You can pick samples from the current batch or eligible samples from other batches that share the same analyte and are currently in the lab.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="create-run-empty-notice" class="alert alert-warning border-0 shadow-sm mb-3 d-none" style="font-size: 13px; border-radius: 8px;"></div>

                <form id="create-run-form">
                    <input type="hidden" id="run-stage-header-id" name="stage_header_id">
                    
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="form-group">
                                <label style="font-weight: 600; color: #475569; display: flex; align-items: center;">
                                    <i class="mdi mdi-layers-outline text-primary mr-2"></i> Current Batch Samples
                                </label>
                                <p class="text-muted mb-2" style="font-size: 11px;">Select samples from this batch.</p>
                                <select id="run-samples-select" name="sample_ids[]" class="form-control" multiple>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: 600; color: #475569; display: flex; align-items: center;">
                                    <i class="mdi mdi-link-variant text-success mr-2"></i> Other Batches
                                </label>
                                <p class="text-muted mb-2" style="font-size: 11px;">Samples from other batches with status "In Lab".</p>
                                <select id="run-other-samples-select" name="sample_ids[]" class="form-control" multiple>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 p-4">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="font-weight: 600; border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4 d-flex align-items-center shadow-sm" id="save-run-btn" style="font-weight: 600; border-radius: 8px;">
                    <i class="mdi mdi-content-save mr-2"></i> Create Run
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Stage Data Modal -->
<div class="modal fade" id="edit-stage-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Stage Data</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-stage-form">
                    <input type="hidden" id="edit-track-id" name="track_id">
                    <div class="form-group">
                        <label><i class="mdi mdi-cog text-primary"></i> Equipment</label>
                        <select id="edit-equipment" name="equipment_ids[]" class="form-control" multiple>
                        </select>
                        <small class="form-text text-muted">Select equipment required for this stage</small>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask text-success"></i> Media</label>
                        <select id="edit-media" name="media_ids[]" class="form-control" multiple>
                        </select>
                        <small class="form-text text-muted">Select media/solutions required for this stage</small>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-test-tube text-info"></i> Controls</label>
                        <select id="edit-controls" name="controls_ids[]" class="form-control" multiple>
                        </select>
                        <small class="form-text text-muted">Select control samples required for this stage</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="save-stage-data-btn">
                    <i class="mdi mdi-content-save"></i> Save Changes
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Result Modal -->
<div class="modal fade" id="add-result-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-chart-line text-success"></i> Add Result</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-result-form">
                    <input type="hidden" id="result-track-id" name="track_id">
                    <div class="form-group">
                        <label><i class="mdi mdi-chart-line text-success"></i> Result</label>
                        <textarea id="result-input" name="result" class="form-control" rows="3" placeholder="Enter the test result..." required></textarea>
                        <small class="form-text text-muted">Enter the test result or reading</small>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-comment-text text-info"></i> Remarks</label>
                        <textarea id="remarks-input" name="remarks" class="form-control" rows="2" placeholder="Enter any remarks or notes..."></textarea>
                        <small class="form-text text-muted">Optional remarks or additional notes</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" id="save-result-btn">
                    <i class="mdi mdi-content-save"></i> Save Result
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Post Results Modal -->
<div id="post-results-modal" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-centered post-results-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: rgba(40, 167, 69, 0.85);">
                <h5 class="modal-title mb-0">
                    <i class="mdi mdi-upload"></i> Confirm Standards & Post Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <form id="post-results-form">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start-analysis-date" class="form-label font-weight-bold">Start Analysis Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start-analysis-date" name="start_analysis_date" required>
                            <small class="text-muted">Date when analysis started</small>
                        </div>
                        <div class="col-md-6">
                            <label for="end-analysis-date" class="form-label font-weight-bold">End Analysis Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end-analysis-date" name="end_analysis_date" required>
                            <small class="text-muted">Date when analysis ended</small>
                        </div>
                    </div>

                    <div class="alert alert-light border d-flex align-items-center justify-content-between flex-wrap mb-3">
                        <div class="mb-0">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                            <strong>Results to be posted</strong>
                        </div>
                        <div class="d-flex align-items-center mt-2 mt-md-0">
                            <input type="file" id="post-results-import-file" class="d-none" accept=".xlsx,.xls,.csv,.txt,.tsv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,text/plain" tabindex="-1">
                            <button type="button" class="btn btn-sm btn-outline-success" id="post-results-import-upload-btn" title="Excel (.xlsx, .xls) or CSV — first row must be headers">
                                <i class="mdi mdi-file-upload-outline"></i> Import file
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm no-datatable" id="post-results-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="select-all-results"></th>
                                    <th>Sample Code</th>
                                    <th>Analyte</th>
                                    <th>Result</th>
                                    <th>Reporting Symbol</th>
                                    <th>Standard</th>
                                    <th>Standard Limits</th>
                                    <th>Remark</th>
                                    <th>Method</th>
                                    <th>Reporting Unit</th>
                                </tr>
                            </thead>
                            <tbody id="post-results-table-body">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirm-post-results" disabled>
                    <i class="mdi mdi-check"></i> Yes, Post Results
                </button>
            </div>
        </div>
    </div>
</div>

@include('worksheets.partials.edit-standard-limit-modal')
