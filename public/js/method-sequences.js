/**
 * Method Sequences Management JavaScript
 */

(function($) {
    'use strict';

    const MethodSequences = {
        batchId: null,
        stageHeaders: [],
        activeStageHeaderId: null,
        runs: [],
        expandedRuns: [],
        expandedStages: [],
        initialized: false, // Guard against multiple initializations
        eventsBound: false,
        /** When set (grouped phased pipeline), only render TestStages with this order. */
        stageOrderFilter: null,
        /** Open first run + matching stage once after init / pipeline remount. */
        shouldAutoExpand: true,
        
        init: function() {
            console.log('[METHOD-SEQUENCES] init() called');

            const container = $('#method-sequences-container');
            if (container.length === 0) {
                return;
            }

            // Guard: prevent multiple initializations when container is present
            if (this.initialized) {
                console.log('[METHOD-SEQUENCES] Already initialized, skipping');
                return;
            }

            this.batchId = container.data('batch-id');
            this.stageHeaders = this.parseStageHeaders(container);
            const orderFilter = container.data('stage-order-filter');
            this.stageOrderFilter = (orderFilter !== undefined && orderFilter !== null && orderFilter !== '')
                ? parseInt(orderFilter, 10)
                : null;

            if (this.stageHeaders && this.stageHeaders.length > 0) {
                this.shouldAutoExpand = true;
                this.renderTabs();
                this.activeStageHeaderId = this.stageHeaders[0].id;
                this.loadRuns(this.activeStageHeaderId);
                this.bindEvents();
                this.initialized = true;
                console.log('[METHOD-SEQUENCES] init() complete, batchId:', this.batchId, 'stageOrderFilter:', this.stageOrderFilter);

                // Start polling for timer updates every 30 seconds
                // self.pollingInterval = setInterval(() => this.refreshActiveTab(), 30000);
            }
        },

        parseStageHeaders: function(container) {
            let stageHeaders = container.data('stageHeaders');

            if (Array.isArray(stageHeaders) && stageHeaders.length > 0) {
                return stageHeaders;
            }

            const raw = container.attr('data-stage-headers');
            if (!raw) {
                return [];
            }

            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                console.error('[METHOD-SEQUENCES] Failed to parse stage headers JSON', error, raw);
                return [];
            }
        },
        
        renderTabs: function() {
            const tabsHTML = this.stageHeaders.map((sh, index) => `
                <li class="nav-item">
                    <a class="nav-link ${index === 0 ? 'active' : ''}" 
                       data-toggle="tab" 
                       href="#tab-${sh.id}"
                       data-stage-header-id="${sh.id}">
                        ${sh.name}
                    </a>
                </li>
            `).join('');
            
            $('#sequence-tabs').html(tabsHTML);
            
            const contentHTML = this.stageHeaders.map((sh, index) => `
                <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" 
                     id="tab-${sh.id}"
                     data-stage-header-id="${sh.id}">
                    ${this.renderSequenceInfo(sh)}
                    <div class="runs-container" id="runs-${sh.id}">
                        <div class="text-center p-4">
                            <i class="mdi mdi-spin mdi-loading"></i> Loading runs...
                        </div>
                    </div>
                </div>
            `).join('');
            
            $('#sequence-tabs-content').html(contentHTML);
        },
        
        renderSequenceInfo: function(sh) {
            // Calculate total duration
            const totalHours = sh.stages_count * 24; // Assuming average, or calculate from actual stages
            const canEdit = sh.can_edit === true;
            const createRunButton = canEdit
                ? `<button class="btn btn-primary create-run-btn" data-stage-header-id="${sh.id}">
                                <i class="mdi mdi-plus"></i> Create Run
                            </button>`
                : `<span class="badge badge-light border text-muted" title="You can view this sequence but only your assigned lab section can create runs or start stages.">
                                <i class="mdi mdi-eye-outline"></i> View only
                            </span>`;
            
            return `
                <div class="sequence-info-card-modern">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center flex-wrap info-items">
                            <div class="info-item">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                <span class="info-label">Method</span>
                                <span class="info-value">${sh.method_name}</span>
                            </div>
                            <i class="mdi mdi-chevron-right mx-2"></i>
                            <div class="info-item">
                                <i class="mdi mdi-test-tube text-success"></i>
                                <span class="info-label">Analyte</span>
                                <span class="info-value">${sh.analyte_name}</span>
                            </div>
                            <i class="mdi mdi-chevron-right mx-2"></i>
                            <div class="info-item">
                                <i class="mdi mdi-water text-info"></i>
                                <span class="info-label">Sample Type</span>
                                <span class="info-value">${sh.sample_type_name}</span>
                            </div>
                            <i class="mdi mdi-chevron-right mx-2"></i>
                            <div class="info-item">
                                <i class="mdi mdi-calendar-clock text-warning"></i>
                                <span class="info-label">Duration</span>
                                <span class="info-value">${sh.total_days} days</span>
                            </div>
                            <i class="mdi mdi-chevron-right mx-2"></i>
                            <div class="info-item">
                                <i class="mdi mdi-format-list-numbered text-danger"></i>
                                <span class="info-label">Stages</span>
                                <span class="info-value">${sh.stages_count}</span>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0 ml-md-auto">
                            ${createRunButton}
                        </div>
                    </div>
                </div>
            `;
        },
        
        bindEvents: function() {
            const self = this;

            if (this.eventsBound) {
                return;
            }
            this.eventsBound = true;

            console.log('[METHOD-SEQUENCES] bindEvents() called');

            // Must off() before binding. initEditStandardLimitModal also uses
            // .methodSequences — calling it before off() left the pencil handler unbound.
            $(document).off('.methodSequences');
            this.editStandardModalInitialized = false;
            this.initEditStandardLimitModal();
            
            // Tab switch
            $(document).on('shown.bs.tab.methodSequences', '#sequence-tabs a[data-toggle="tab"]', function(e) {
                const stageHeaderId = $(e.target).attr('data-stage-header-id');
                self.activeStageHeaderId = stageHeaderId;
                self.shouldAutoExpand = true;
                self.loadRuns(stageHeaderId);
            });
            
            // Create run button
            $(document).on('click.methodSequences', '.create-run-btn', function() {
                const stageHeaderId = $(this).attr('data-stage-header-id');
                if (!self.canEditStageHeader(stageHeaderId)) {
                    toastr.error('You can only create runs for your assigned lab section(s).');
                    return;
                }
                self.showCreateRunModal(stageHeaderId);
            });
            
            // Save run - DELEGATED to prevent duplicate handlers
            $(document).on('click.methodSequences', '#save-run-btn', function(e) {
                console.log('[METHOD-SEQUENCES] #save-run-btn click handler fired');
                self.saveRun();
                return false; // Prevent event bubbling
            });
            
            // Toggle run expansion
            $(document).on('click.methodSequences', '.run-header', function() {
                const runId = $(this).attr('data-run-id');
                self.toggleRun(runId);
            });
            
            // Start stage
            $(document).on('click.methodSequences', '.start-stage-btn', function() {
                const trackId = $(this).attr('data-track-id');
                if (!self.canEditActiveStageHeader()) {
                    toastr.error('You can only start stages for your assigned lab section(s).');
                    return;
                }
                self.startStage(trackId);
            });
            
            // End stage
            $(document).on('click.methodSequences', '.end-stage-btn', function() {
                const trackId = $(this).attr('data-track-id');
                if (!self.canEditActiveStageHeader()) {
                    toastr.error('You can only end stages for your assigned lab section(s).');
                    return;
                }
                self.endStage(trackId);
            });
            
            // Edit stage
            $(document).on('click.methodSequences', '.edit-stage-btn', function() {
                const trackId = $(this).attr('data-track-id');
                if (!self.canEditActiveStageHeader()) {
                    toastr.error('You can only edit stages for your assigned lab section(s).');
                    return;
                }
                self.showEditStageModal(trackId);
            });
            
            // Save stage data
            $('#save-stage-data-btn').on('click', function() {
                self.saveStageData();
            });
            
            // Add result button
            $(document).on('click', '.add-result-btn', function() {
                const trackId = $(this).attr('data-track-id');
                self.showAddResultModal(trackId);
            });
            
            // Save result
            $('#save-result-btn').on('click', function() {
                self.saveResult();
            });
            
            // Post Results button (delegated — header button is outside wire:ignore and gets replaced by Livewire)
            $(document).on('click', '#post-results-btn', function(e) {
                e.preventDefault();
                self.showPostResultsModal();
            });

            // Confirm post results (delegated — survives modal re-renders)
            $(document).on('click', '#confirm-post-results', function(e) {
                e.preventDefault();
                self.confirmPostResults();
            });

            $(document).on('click', '#post-results-import-upload-btn', function() {
                $('#post-results-import-file').trigger('click');
            });

            $(document).on('change', '#post-results-import-file', function(e) {
                self.handlePostResultsImportFile(e.target);
            });

            $(document).on('input change', '.post-result-input', function() {
                self.recalculateRemark($(this).closest('tr'));
            });

            // Handle "Select All" Results Checkbox in Modal
            $(document).on('change', '#select-all-results', function() {
                const isChecked = $(this).prop('checked');
                $('.pt-select-cb').prop('checked', isChecked);
                self.validatePostButton();
            });

            // Date change triggers validation
            $(document).on('change', '#start-analysis-date, #end-analysis-date', function() {
                self.validatePostButton();
            });

            // Save Results button (multiple buttons, one per track table)
            $(document).on('click', '[id^="save-sample-results-btn-"]', function() {
                const trackId = $(this).attr('data-track-id');
                self.saveTrackSampleResults(trackId);
            });
            
            // Toggle stage details
            $(document).on('click.methodSequences', '.toggle-stage-details', function() {
                const trackId = $(this).attr('data-track-id');
                self.toggleStageDetails(trackId);
            });

            // Step navigation
            $(document).on('click.methodSequences', '.next-step-btn', function() {
                const trackId = $(this).attr('data-track-id');
                const nextStep = $(this).data('next');
                self.goToStep(trackId, nextStep);
            });

            $(document).on('click.methodSequences', '.prev-step-btn', function() {
                const trackId = $(this).attr('data-track-id');
                const prevStep = $(this).data('prev');
                self.goToStep(trackId, prevStep);
            });

            $(document).on('click.methodSequences', '.stepper-header .step-item', function() {
                const trackId = $(this).attr('data-track-id');
                const step = $(this).data('step');
                self.goToStep(trackId, step);
            });

            // Step 6: Handle result input changes for auto-calculating remarks
            $(document).on('change', '.sample-result-input', function() {
                const $input = $(this);
                const $row = $input.closest('tr.sample-result-row');
                // Use .attr() for UUID data-* values — jQuery .data() coerces leading-zero UUIDs to numbers.
                const sampleId = $input.attr('data-sample-id');
                const capturedResultId = $input.attr('data-captured-result-id');
                const trackId = $input.attr('data-track-id');
                const result = $input.val();
                const standardLimit = self.getStep6StandardLimit($row);

                if (!capturedResultId || !trackId) {
                    console.warn('Missing required data attributes for remark calculation');
                    return;
                }

                // Get current reporting symbol from dropdown (if it changed)
                const $symbolSelect = $row.find('.reporting-symbol-select');
                const reportingSymbol = $symbolSelect.length ? $symbolSelect.val() : null;

                // Calculate remark via AJAX
                self.calculateAndUpdateRemark(trackId, capturedResultId, result, standardLimit, reportingSymbol, sampleId);
            });

            // Step 6: Handle reporting symbol dropdown changes
            $(document).on('change', '.reporting-symbol-select', function() {
                const $select = $(this);
                const $row = $select.closest('tr.sample-result-row');
                // Use .attr() for UUID data-* values — jQuery .data() coerces leading-zero UUIDs to numbers.
                const sampleId = $select.attr('data-sample-id');
                const capturedResultId = $select.attr('data-captured-result-id');
                const trackId = $select.attr('data-track-id');
                const reportingSymbol = $select.val();

                // Get the current result value
                const $resultInput = $row.find('.sample-result-input');
                const result = $resultInput.val();
                const standardLimit = self.getStep6StandardLimit($row);

                if (!capturedResultId || !trackId || !result) {
                    return; // Don't recalculate if no result entered yet
                }

                // Recalculate remark with new reporting symbol
                self.calculateAndUpdateRemark(trackId, capturedResultId, result, standardLimit, reportingSymbol, sampleId);
            });

            // Step 6: Handle manual remark dropdown changes
            $(document).on('change', '.sample-remark-dropdown', function() {
                const $select = $(this);
                const sampleId = $select.data('sample-id');
                
                // Just store the value - it will be sent to backend on save
                $select.val($select.val());
            });

            // Add one-by-one solution/equipment rows
            // Global helper to render items (used by both form load and add handler)
            window.renderSolutionItem = function(trackId, type, item, itemName, resultNature) {
                const id = item && item.id ? String(item.id) : '';
                let safeName = itemName || 'N/A';
                if (typeof safeName === 'object' && safeName !== null) {
                    safeName = String(safeName);
                } else if (!safeName) {
                    safeName = 'N/A';
                }

                if (type === 'equipment') {
                    const listContainer = $(`#equipment-list-${trackId}`);
                    if (listContainer.find(`.equipment-card[data-id="${id}"]`).length > 0) {
                        alert('This equipment is already added.');
                        return;
                    }

                    const serial = item.serial || '';
                    const calibration = item.calibration || '';

                    listContainer.append(`
                        <div class="equipment-card border p-3 mb-3 bg-white shadow-sm position-relative" data-id="${id}" style="border-left: 4px solid #007bff !important; border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-primary font-weight-bold small mb-1">ITEM #${listContainer.children().length + 1}</div>
                                    <h5 class="mb-1 font-weight-bold equipment-name" title="${safeName}">${safeName}</h5>
                                    <div class="equipment-meta">
                                        <div class="equipment-meta-block">
                                            <div class="text-muted small">Equipment Number</div>
                                            <div class="font-weight-bold">${serial || 'N/A'}</div>
                                            <input type="hidden" class="equipment-serial-input" value="${serial}">
                                        </div>
                                        <div class="equipment-meta-block">
                                            <div class="text-muted small">Last Calibrated</div>
                                            <div class="font-weight-bold">${calibration || 'N/A'}</div>
                                            <input type="hidden" class="equipment-calibration-input" value="${calibration}">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-success small d-flex align-items-center">
                                        <i class="mdi mdi-circle mr-1" style="font-size: 8px;"></i> Verification Active
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-solution-item p-0" data-track-id="${trackId}" data-type="${type}" data-id="${id}">
                                    <i class="mdi mdi-delete-outline h4 mb-0"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    return;
                }

                if (type === 'controls') {
                    const listContainer = $(`#controls-list-${trackId}`);
                    if (listContainer.find(`.control-card-premium[data-id="${id}"]`).length > 0) {
                        alert('This control is already added.');
                        return;
                    }

                    const lot = item.preparation || '';
                    const expiry = item.expiry || '';
                    const cardIndex = listContainer.find('.control-card-premium').length + 1;

                    const addMoreBox = listContainer.find('.add-more-dashed-box').closest('.col-md-4');
                    $(`
                        <div class="col-md-4 mb-3 control-card-wrapper" data-id="${id}">
                            <div class="control-card-premium h-100" data-id="${id}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="control-type-label">Control #${cardIndex}</div>
                                    <button type="button" class="btn btn-link text-danger remove-solution-item p-0" data-track-id="${trackId}" data-type="${type}" data-id="${id}">
                                        <i class="mdi mdi-delete-outline h5 mb-0"></i>
                                    </button>
                                </div>
                                <div class="control-name-title" title="${safeName}">${safeName}</div>
                                
                                <div class="control-meta-grid">
                                    <div>
                                        <div class="meta-label">Lot Number</div>
                                        <div class="meta-value">${lot || 'N/A'}</div>
                                        <input type="hidden" class="solution-preparation" value="${lot}">
                                    </div>
                                    <div class="text-right">
                                        <div class="meta-label">Expiration</div>
                                        <div class="meta-value">${expiry || 'N/A'}</div>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="verified-badge">
                                        <i class="mdi mdi-check-decagram"></i> Verified
                                    </div>
                                    <div class="ref-number text-uppercase font-weight-bold" style="font-size: 0.75rem; color: #64748b;">
                                        Result Nature: ${resultNature === 'quantitative' ? 'Quantitative' : (resultNature === 'qualitative' ? 'Qualitative' : 'No Result')}
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).insertBefore(addMoreBox);
                    return;
                }

                if (type === 'media') {
                    const gridContainer = $(`#media-cards-grid-${trackId}`);
                    if (gridContainer.find(`.media-card-wrapper[data-id="${id}"]`).length > 0) {
                        alert('This media is already added.');
                        return;
                    }

                    const preparation = item && item.preparation ? String(item.preparation) : '';
                    const technician = item && item.remark ? String(item.remark) : 'N/A';
                    const expiry = item && item.expiry ? String(item.expiry) : '';
                    
                    gridContainer.find('.media-placeholder-wrapper').remove();

                    gridContainer.append(`
                        <div class="col-md-6 mb-3 media-card-wrapper" data-id="${id}" data-name="${safeName}">
                            <div class="media-card-premium d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="status-badge-verified">
                                        <i class="mdi mdi-check-circle mr-1"></i> Verified
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical h5 mb-0"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 8px;">
                                            <a class="dropdown-item py-2 edit-media-entry" href="#" data-track-id="${trackId}" data-id="${id}">
                                                <i class="mdi mdi-pencil-outline mr-2 text-primary"></i> Edit Entry
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item py-2 text-danger remove-solution-item" href="#" data-track-id="${trackId}" data-type="media" data-id="${id}">
                                                <i class="mdi mdi-delete-outline mr-2"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="media-name-title mb-auto" title="${safeName}">${safeName}</div>
                                
                                <div class="media-meta-grid mt-3">
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. Date:</span>
                                        <span class="media-meta-value">${preparation || 'N/A'}</span>
                                        <input type="hidden" class="solution-preparation" value="${preparation}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. No:</span>
                                        <span class="media-meta-value">${technician}</span>
                                        <input type="hidden" class="solution-preparation-number" value="${technician}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Expiry:</span>
                                        <span class="media-meta-value">${expiry || 'N/A'}</span>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Result Nature:</span>
                                        <span class="media-meta-value text-capitalize">${resultNature}</span>
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                                <div class="media-card-actions mt-auto pt-3 border-top">
                                    <div class="verified-badge-footer">
                                        <i class="mdi mdi-shield-check-outline mr-1"></i> Verified Entry
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Re-add placeholder if needed
                    if (gridContainer.find('.media-card-wrapper').length === 1) {
                        gridContainer.append(`
                            <div class="col-md-6 mb-3 media-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-water-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional media data</div>
                                    <div class="placeholder-text-sub">Add media using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    return;
                }

                if (type === 'diluents') {
                    const gridContainer = $(`#diluent-cards-grid-${trackId}`);
                    if (gridContainer.find(`.diluent-card-wrapper[data-id="${id}"]`).length > 0) {
                        alert('This diluent is already added.');
                        return;
                    }

                    const preparation = item && item.preparation ? String(item.preparation) : '';
                    const technician = item && item.remark ? String(item.remark) : 'N/A';
                    const expiry = item && item.expiry ? String(item.expiry) : '';

                    gridContainer.find('.diluent-placeholder-wrapper').remove();

                    gridContainer.append(`
                        <div class="col-md-6 mb-3 diluent-card-wrapper" data-id="${id}" data-name="${safeName}">
                            <div class="diluent-card-premium d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="status-badge-verified">
                                        <i class="mdi mdi-check-circle mr-1"></i> Verified
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical h5 mb-0"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 8px;">
                                            <a class="dropdown-item py-2 edit-diluent-entry" href="#" data-track-id="${trackId}" data-id="${id}">
                                                <i class="mdi mdi-pencil-outline mr-2 text-primary"></i> Edit Entry
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="media-name-title mb-auto" title="${safeName}">${safeName}</div>
                
                                <div class="media-meta-grid mt-3">
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. Date:</span>
                                        <span class="media-meta-value">${preparation || 'N/A'}</span>
                                        <input type="hidden" class="solution-preparation-date" value="${preparation}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. No:</span>
                                        <span class="media-meta-value">${technician}</span>
                                        <input type="hidden" class="solution-preparation-number" value="${technician}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Expiry:</span>
                                        <span class="media-meta-value">${expiry || 'N/A'}</span>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Result Nature:</span>
                                        <span class="media-meta-value text-capitalize">${resultNature}</span>
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                                <div class="media-card-actions mt-auto pt-3 border-top">
                                    <div class="verified-badge-footer">
                                        <i class="mdi mdi-shield-check-outline mr-1"></i> Verified Entry
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    if (gridContainer.find('.diluent-card-wrapper').length === 1) {
                        gridContainer.append(`
                            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-beaker-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    return;
                }
            };

            $(document).on('click', '.add-solution-item', function() {
                const trackId = $(this).attr('data-track-id');
                const type = $(this).data('type');

                const pickerId = (type === 'equipment')
                    ? `#equipment-picker-${trackId}`
                    : (type === 'media')
                        ? `#media-picker-${trackId}`
                        : (type === 'controls')
                            ? `#controls-picker-${trackId}`
                            : `#diluents-picker-${trackId}`;

                const $picker = $(pickerId);
                const selectedId = $picker.val();
                
                if (!selectedId) {
                    alert('Please select an item first.');
                    return;
                }

                // Build the item object from form data
                let item = { id: selectedId };
                let itemName = $picker.find('option:selected').text();
                const closeEntryPanel = function(entryType) {
                    const body = $(`#${entryType}-entry-body-${trackId}`);
                    const header = $(`.${entryType}-entry-header[data-track-id="${trackId}"]`);
                    const icon = header.find('.solution-entry-toggle-icon');
                    body.addClass('d-none');
                    header.attr('aria-expanded', 'false');
                    icon.removeClass('mdi-chevron-down').addClass('mdi-chevron-right');
                };

                if (type === 'equipment') {
                    item.serial = $(`#equipment-serial-${trackId}`).val() || '';
                    item.calibration = $(`#equipment-calibration-${trackId}`).val() || '';
                    // Render the item to the list
                    window.renderSolutionItem(trackId, type, item, itemName);
                    // Clear the form
                    $picker.val(null).trigger('change');
                    $(`#equipment-serial-${trackId}`).val('');
                    $(`#equipment-calibration-${trackId}`).val('');
                    // Auto-save equipment data
                    window.autoSaveStageData(trackId, 'equipment');
                } else if (type === 'controls') {
                    item.preparation = $(`#control-lot-${trackId}`).val() || '';
                    item.expiry = $(`#control-expiry-${trackId}`).val() || '';
                    item.result_nature = $(`#control-result-nature-${trackId}`).val() || '';
                    // Render the item to the list
                    window.renderSolutionItem(trackId, type, item, itemName, item.result_nature);
                    // Clear the form
                    $picker.val(null).trigger('change');
                    $(`#control-lot-${trackId}`).val('');
                    $(`#control-expiry-${trackId}`).val('');
                    $(`#control-result-nature-${trackId}`).val('');
                    closeEntryPanel('control');
                    // Auto-save controls data
                    window.autoSaveStageData(trackId, 'controls');
                } else if (type === 'media') {
                    item.preparation = $(`#media-prep-date-${trackId}`).val() || '';
                    item.remark = $(`#media-prep-no-${trackId}`).val() || '';
                    item.expiry = $(`#media-expiry-${trackId}`).val() || '';
                    item.result_nature = $(`#media-result-nature-${trackId}`).val() || '';
                    // Render the item to the list
                    window.renderSolutionItem(trackId, type, item, itemName, item.result_nature);
                    // Clear the form
                    $picker.val(null).trigger('change');
                    $(`#media-prep-date-${trackId}`).val('');
                    $(`#media-prep-no-${trackId}`).val('');
                    $(`#media-expiry-${trackId}`).val('');
                    $(`#media-result-nature-${trackId}`).val('');
                    closeEntryPanel('media');
                    // Auto-save media data
                    window.autoSaveStageData(trackId, 'media');
                } else if (type === 'diluents') {
                    item.preparation = $(`#diluent-prep-date-${trackId}`).val() || '';
                    item.remark = $(`#diluent-prep-no-${trackId}`).val() || '';
                    item.expiry = $(`#diluent-expiry-${trackId}`).val() || '';
                    item.result_nature = $(`#diluent-result-nature-${trackId}`).val() || '';
                    // Render the item to the list
                    window.renderSolutionItem(trackId, type, item, itemName, item.result_nature);
                    // Clear the form
                    $picker.val(null).trigger('change');
                    $(`#diluent-prep-date-${trackId}`).val('');
                    $(`#diluent-prep-no-${trackId}`).val('');
                    $(`#diluent-expiry-${trackId}`).val('');
                    $(`#diluent-result-nature-${trackId}`).val('');
                    // Close the collapsible panel
                    closeEntryPanel('diluent');
                    // Auto-save diluents data
                    window.autoSaveStageData(trackId, 'diluents');
                }
            });

            // Toggle solution entry forms
            $(document).on('click', '.solution-entry-header', function() {
                const trackId = $(this).attr('data-track-id');
                const entryType = $(this).data('entry-type');
                const pickerId = $(this).data('picker-id');
                const body = $(`#${entryType}-entry-body-${trackId}`);
                const icon = $(this).find('.solution-entry-toggle-icon');
                const isHidden = body.hasClass('d-none');

                // Toggle the form visibility
                body.toggleClass('d-none');
                
                // Update ARIA attribute for accessibility
                $(this).attr('aria-expanded', isHidden ? 'true' : 'false');
                
                // Animate chevron icon rotation
                icon.toggleClass('mdi-chevron-down', isHidden);
                icon.toggleClass('mdi-chevron-right', !isHidden);

                // Auto-focus first input when opened
                if (isHidden) {
                    const picker = $(`#${pickerId}-${trackId}`);
                    if (picker.length) {
                        setTimeout(() => {
                            picker.focus();
                        }, 100);
                    }
                }
            });

            $(document).on('keydown', '.solution-entry-header', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });

            $(document).on('click', '.open-solution-entry', function() {
                const trackId = $(this).attr('data-track-id');
                const entryType = $(this).data('entry-type');
                const pickerId = $(this).data('picker-id');
                const body = $(`#${entryType}-entry-body-${trackId}`);
                const header = $(`.${entryType}-entry-header[data-track-id="${trackId}"]`);

                if (body.hasClass('d-none')) {
                    header.trigger('click');
                }

                setTimeout(() => {
                    $(`#${pickerId}-${trackId}`).select2('open');
                }, 100);
            });

            $(document).on('keydown', '.open-solution-entry', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });

            // Edit media entry handler
            $(document).on('click', '.edit-media-entry', function(e) {
                e.preventDefault();
                const trackId = $(this).attr('data-track-id');
                const mediaId = $(this).data('id');
                const $card = $(this).closest('.media-card-wrapper');
                const prepDate = $card.find('.solution-preparation').val();
                const prepNo = $card.find('.solution-preparation-number').val();
                const resultNature = $card.find('.solution-result-nature').val();
                
                // Fill the registration panel with this card's data
                $(`#media-picker-${trackId}`).val(mediaId).trigger('change');
                $(`#media-prep-date-${trackId}`).val(prepDate);
                $(`#media-prep-no-${trackId}`).val(prepNo);
                $(`#media-result-nature-${trackId}`).val(resultNature);
                
                // Optional: scroll to the registration panel
                $(`.media-registration-panel`)[0].scrollIntoView({ behavior: 'smooth' });
                
                // Remove the card as we are now "editing" it in the form
                $card.remove();
                
                // Re-add placeholder if needed
                const grid = $(`#media-cards-grid-${trackId}`);
                if (grid.find('.media-card-wrapper').length === 1) {
                    grid.append(`
                        <div class="col-md-6 mb-3 media-placeholder-wrapper">
                            <div class="placeholder-card-dashed">
                                <div class="placeholder-icon-container">
                                    <i class="mdi mdi-water-outline"></i>
                                </div>
                                <div class="placeholder-text-main">Waiting for additional media data</div>
                                <div class="placeholder-text-sub">Add media using the form below</div>
                            </div>
                        </div>
                    `);
                }
            });

            // Auto-save utility functions
            window.collectSolutionItems = function(trackId, type) {
                const items = [];
                const normalizeItemId = function(raw) {
                    if (raw === undefined || raw === null) {
                        return null;
                    }
                    const id = String(raw).trim();
                    if (!id || id === '0' || id.toLowerCase() === 'nan') {
                        return null;
                    }
                    return id;
                };

                if (type === 'equipment') {
                    $(`#equipment-list-${trackId} .equipment-card[data-id]`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        items.push({
                            id: id,
                            serial: $(this).find('.equipment-serial-input').val() || '',
                            calibration: $(this).find('.equipment-calibration-input').val() || ''
                        });
                    });
                } else if (type === 'controls') {
                    $(`#controls-list-${trackId} .control-card-wrapper[data-id]`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        items.push({
                            id: id,
                            name: ($(this).attr('data-name') || $(this).find('.control-name-title').text() || '').trim(),
                            preparation: $(this).find('.solution-preparation').val() || '',
                            expiry: $(this).find('.solution-expiry').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                } else if (type === 'media') {
                    $(`#media-cards-grid-${trackId} .media-card-wrapper[data-id]`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        items.push({
                            id: id,
                            name: ($(this).attr('data-name') || $(this).find('.media-name-title').text() || '').trim(),
                            preparation: $(this).find('.solution-preparation').val() || '',
                            preparation_number: $(this).find('.solution-preparation-number').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                } else if (type === 'diluents') {
                    $(`#diluent-cards-grid-${trackId} .diluent-card-wrapper[data-id]`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        items.push({
                            id: id,
                            name: ($(this).attr('data-name') || $(this).find('.media-name-title').text() || '').trim(),
                            preparation_date: $(this).find('.solution-preparation-date').val() || '',
                            preparation_number: $(this).find('.solution-preparation-number').val() || '',
                            expiry: $(this).find('.solution-expiry').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                }

                return items;
            };

            window.autoSaveStageData = function(trackId, type) {
                // Collect items of this type from DOM
                const items = window.collectSolutionItems(trackId, type);

                // Build payload with only the relevant type being saved
                let payload = {};
                if (type === 'equipment') {
                    payload.equipment_items = items;
                } else if (type === 'controls') {
                    payload.controls_items = items;
                } else if (type === 'media') {
                    payload.media_items = items;
                } else if (type === 'diluents') {
                    payload.diluents_items = items;
                }

                // Auto-save via AJAX
                $.ajax({
                    url: `/method-sequences/tracks/${trackId}/update`,
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(`${type} auto-saved successfully`);
                    },
                    error: function(xhr) {
                        console.error(`Error auto-saving ${type}:`, xhr.responseJSON || xhr.statusText);
                    }
                });
            };

            $(document).on('click', '.remove-solution-item', function() {
                const $button = $(this);
                const type = $button.data('type');
                const trackId = $button.data('track-id');
                
                if (type === 'equipment') {
                    $button.closest('.equipment-card').remove();
                    // Auto-save after removal
                    window.autoSaveStageData(trackId, 'equipment');
                } else if (type === 'media') {
                    const grid = $button.closest('#media-cards-grid-' + trackId);
                    $button.closest('.media-card-wrapper').remove();
                    
                    // Update placeholders
                    const count = grid.find('.media-card-wrapper').length;
                    grid.find('.media-placeholder-wrapper').remove();
                    
                    if (count === 0) {
                        grid.append(`
                            <div class="col-md-6 mb-3 media-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-water-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional media data</div>
                                    <div class="placeholder-text-sub">Add media using the form below</div>
                                </div>
                            </div>
                        `);
                    } else if (count === 1) {
                        grid.append(`
                            <div class="col-md-6 mb-3 media-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-water-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional media data</div>
                                    <div class="placeholder-text-sub">Add media using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    // Auto-save after removal
                    window.autoSaveStageData(trackId, 'media');
                } else if (type === 'diluents') {
                    const grid = $(`#diluent-cards-grid-${trackId}`);
                    $button.closest('.diluent-card-wrapper').remove();
                    
                    // Update placeholders
                    const count = grid.find('.diluent-card-wrapper').length;
                    grid.find('.diluent-placeholder-wrapper').remove();
                    
                    if (count === 0) {
                        grid.append(`
                            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-beaker-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                                </div>
                            </div>
                        `);
                    } else if (count === 1) {
                        grid.append(`
                            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-beaker-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    // Auto-save after removal
                    window.autoSaveStageData(trackId, 'diluents');
                } else if (type === 'controls') {
                    $button.closest('.control-card-wrapper').remove();
                    // Auto-save after removal
                    window.autoSaveStageData(trackId, 'controls');
                } else {
                    $button.closest('tr').remove();
                }
            });
        },
        
        loadRuns: function(stageHeaderId, onComplete) {
            console.log('[METHOD-SEQUENCES] loadRuns() called with stageHeaderId:', stageHeaderId);
            const self = this;
            const container = $(`#runs-${stageHeaderId}`);
            
            $.ajax({
                url: `/method-sequences/${stageHeaderId}/runs?batch_id=${this.batchId}`,
                method: 'GET',
                success: function(runs) {
                    console.log('[METHOD-SEQUENCES] loadRuns GET success, received', runs.length, 'runs');
                    self.runs = runs;
                    const autoExpandTrackId = self.ensureDefaultExpansion(runs);
                    self.renderRuns(stageHeaderId, runs);
                    if (autoExpandTrackId) {
                        setTimeout(function() {
                            self.loadStageFormData(autoExpandTrackId);
                        }, 100);
                    }
                    // Call completion callback if provided
                    if (typeof onComplete === 'function') {
                        // Use setTimeout to ensure DOM is fully rendered
                        setTimeout(onComplete, 100);
                    }
                },
                error: function() {
                    container.html('<div class="alert alert-danger">Error loading runs</div>');
                }
            });
        },
        
        renderRuns: function(stageHeaderId, runs) {
            const container = $(`#runs-${stageHeaderId}`);
            
            if (runs.length === 0) {
                const emptyMessage = this.canEditStageHeader(stageHeaderId)
                    ? 'No runs created yet. Click "Create Run" to get started.'
                    : 'No runs created yet. Only analysts from the assigned lab section can create runs.';
                container.html(`<div class="alert alert-info">${emptyMessage}</div>`);
                return;
            }
            
            const totalRuns = runs.length;
            const html = runs.map((run, index) => {
                // Calculate chronological number (1-based)
                // Since runs are sorted desc (latest first), the last run in the array is #1
                const runNumber = totalRuns - index;
                return this.renderRun(run, runNumber);
            }).join('');
            container.html(html);

            // Re-init tooltips for dynamically rendered content
            if ($.fn.tooltip) {
                container.find('[data-toggle="tooltip"]').tooltip({
                    container: 'body',
                    boundary: 'window',
                    trigger: 'hover'
                });
            }
        },
        
        renderRun: function(run, runNumber) {
            const isExpanded = this.idInList(this.expandedRuns, run.id);
            const sampleCodes = this.getUniqueSampleCodes(run.track_records);
            const samplesCount = sampleCodes.length;
            const sampleDisplay = this.buildSampleDisplay(sampleCodes);
            const runStartMeta = this.getRunStartMeta(run.track_records);
            const runStatusMeta = this.getRunStatusMeta(run.track_records);
            const hasResultStage = this.hasResultStage(run.track_records);
            const statusBadge = runStatusMeta.status === 'completed'
                ? `<span class="badge badge-pill badge-primary run-status-pill">
                        <i class="mdi mdi-check-circle-outline"></i>
                        Completed
                   </span>`
                : (runStatusMeta.status === 'started'
                    ? `<span class="badge badge-pill badge-success run-status-pill">
                            <i class="mdi mdi-play-circle-outline"></i>
                            Started
                       </span>`
                    : `<span class="badge badge-pill badge-secondary run-status-pill">
                            <i class="mdi mdi-timer-sand"></i>
                            Not started
                       </span>`);

            const statusMeta = runStatusMeta.status === 'completed'
                ? `<div class="run-status-meta text-muted">
                        ${runStatusMeta.completedAt ? `<span><i class="mdi mdi-clock-check-outline"></i> ${this.formatDateTime(runStatusMeta.completedAt)}</span>` : ''}
                        ${runStatusMeta.completedBy ? `<span class="ml-2"><i class="mdi mdi-account-check-outline"></i> ${this.escapeHtml(runStatusMeta.completedBy)}</span>` : ''}
                   </div>`
                : (runStartMeta.started
                    ? `<div class="run-status-meta text-muted">
                            <span><i class="mdi mdi-clock-outline"></i> ${this.formatDateTime(runStartMeta.startedAt)}</span>
                            ${runStartMeta.startedBy ? `<span class="ml-2"><i class="mdi mdi-account"></i> ${this.escapeHtml(runStartMeta.startedBy)}</span>` : ''}
                       </div>`
                    : `<div class="run-status-meta text-muted">No stage started yet</div>`);
            
            return `
                <div class="run-item mb-3" data-run-id="${run.id}">
                    <div class="run-header" data-run-id="${run.id}">
                        <div class="d-flex w-100 align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center flex-wrap">
                                <span class="mr-2">
                                    <i class="mdi mdi-${isExpanded ? 'chevron-up' : 'chevron-down'}"></i>
                                </span>
                                <span class="run-title mr-2">
                                    <strong>${samplesCount} sample${samplesCount !== 1 ? 's' : ''}</strong>
                                </span>
                                <div class="run-samples-inline">
                                    ${sampleDisplay}
                                </div>
                                <span class="ml-2">${statusBadge}</span>
                                ${hasResultStage ? `<span class="badge badge-pill badge-info ml-2 run-result-pill" data-toggle="tooltip" title="This run contains at least one result stage">
                                    <i class="mdi mdi-chart-line"></i> Result step
                                </span>` : ''}
                            </div>

                            <div class="text-right mt-2 mt-md-0">
                                <div class="run-created-meta text-muted">
                                    Created by ${run.user ? this.escapeHtml(run.user.name) : 'Unknown'} on ${this.formatDate(run.created_at)}
                                </div>
                                ${statusMeta}
                            </div>
                        </div>
                    </div>
                    ${isExpanded ? this.renderRunBody(run, sampleCodes) : ''}
                </div>
            `;
        },

        buildSampleDisplay: function(sampleCodes) {
            const maxShown = 3;
            const shown = sampleCodes.slice(0, maxShown);
            const remaining = sampleCodes.length - shown.length;
            const fullList = sampleCodes.join('\n');

            const chips = shown.map(code => {
                const safe = this.escapeHtml(code);
                return `<span class="run-sample-chip">${safe}</span>`;
            }).join('');

            const more = remaining > 0
                ? `<span class="run-sample-more" data-toggle="tooltip" data-html="true" title="${this.escapeHtml(fullList).replace(/\n/g, '<br>')}">... +${remaining}</span>`
                : '';

            const tooltipAll = sampleCodes.length > 0
                ? ` data-toggle="tooltip" data-html="true" title="${this.escapeHtml(fullList).replace(/\n/g, '<br>')}"`
                : '';

            return `<span class="run-samples-wrapper"${tooltipAll}>${chips}${more}</span>`;
        },

        hasResultStage: function(trackRecords) {
            if (!Array.isArray(trackRecords)) return false;
            return trackRecords.some(t => t && t.test_stage && t.test_stage.is_result_stage);
        },

        getRunStartMeta: function(trackRecords) {
            if (!Array.isArray(trackRecords) || trackRecords.length === 0) {
                return { started: false, startedAt: null, startedBy: null };
            }

            const startedTracks = trackRecords
                .filter(t => t && t.started_at)
                .sort((a, b) => new Date(a.started_at) - new Date(b.started_at));

            if (startedTracks.length === 0) {
                return { started: false, startedAt: null, startedBy: null };
            }

            const first = startedTracks[0];
            return {
                started: true,
                startedAt: first.started_at,
                startedBy: first.user ? first.user.name : null
            };
        },

        getRunStatusMeta: function(trackRecords) {
            if (!Array.isArray(trackRecords) || trackRecords.length === 0) {
                return { status: 'not_started', completedAt: null, completedBy: null };
            }

            const hasStarted = trackRecords.some(t => t && t.started_at);
            if (!hasStarted) {
                return { status: 'not_started', completedAt: null, completedBy: null };
            }

            const activeStatuses = ['running', 'overdue'];
            const hasActive = trackRecords.some(t => t && activeStatuses.includes(String(t.status || '').toLowerCase()));
            if (hasActive) {
                return { status: 'started', completedAt: null, completedBy: null };
            }

            const completedTracks = trackRecords.filter(t => t && (t.ended_at || String(t.status || '').toLowerCase() === 'completed'));
            const allCompleted = completedTracks.length === trackRecords.length;
            if (!allCompleted) {
                return { status: 'started', completedAt: null, completedBy: null };
            }

            // Pick the latest ended_at as the run completion timestamp
            const endedTracks = completedTracks
                .filter(t => t.ended_at)
                .sort((a, b) => new Date(b.ended_at) - new Date(a.ended_at));

            const latest = endedTracks.length ? endedTracks[0] : null;
            const completedAt = latest ? latest.ended_at : null;
            const completedBy = latest
                ? (latest.endedBy && latest.endedBy.name ? latest.endedBy.name : (latest.user && latest.user.name ? latest.user.name : null))
                : null;

            return { status: 'completed', completedAt, completedBy };
        },

        escapeHtml: function(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        
        renderRunBody: function(run, sampleCodes) {
            return `
                <div class="run-body">
                    <div class="mb-3">
                        <strong>Samples:</strong> ${sampleCodes.join(', ')}
                    </div>
                    <div class="stages-list">
                        <h6>Test Stages</h6>
                        ${this.renderStages(run.track_records)}
                    </div>
                </div>
            `;
        },
        
        renderStages: function(trackRecords) {
            // Group by test stage
            const stageGroups = {};
            trackRecords.forEach(track => {
                const stageId = track.test_stage_id;
                if (!stageGroups[stageId]) {
                    stageGroups[stageId] = {
                        testStage: track.test_stage,
                        tracks: []
                    };
                }
                stageGroups[stageId].tracks.push(track);
            });

            // Ensure stable ordering of tracks within a stage group.
            // We use the first track as the "representative" for expansion and stepper DOM IDs,
            // so it must be deterministic across reloads (start/end/save results).
            Object.values(stageGroups).forEach(group => {
                group.tracks.sort((a, b) => {
                    const aSample = a.sample_detail_id ?? 0;
                    const bSample = b.sample_detail_id ?? 0;
                    if (aSample !== bSample) return aSample - bSample;
                    const aId = a.id ?? 0;
                    const bId = b.id ?? 0;
                    return aId - bId;
                });
            });
            
            // Render timeline - sort by stage order
            let stages = Object.values(stageGroups).sort((a, b) => {
                return (a.testStage.order ?? 999) - (b.testStage.order ?? 999);
            });

            // Grouped phased pipeline: optionally show a single TestStage by order.
            if (this.stageOrderFilter !== null && !Number.isNaN(this.stageOrderFilter)) {
                stages = stages.filter(group => Number(group.testStage?.order) === Number(this.stageOrderFilter));
            }

            if (stages.length === 0) {
                return `<div class="alert alert-warning mb-0 py-2 px-3 small">No test stage matched this pipeline step.</div>`;
            }

            return `
                <div class="stages-timeline">
                    ${stages.map((group, index) => this.renderStageInTimeline(group, index, stages.length)).join('')}
                </div>
            `;
        },
        
        renderStageInTimeline: function(group, index, totalStages) {
            const stage = group.testStage;
            const firstTrack = group.tracks[0];
            const isExpanded = this.idInList(this.expandedStages, firstTrack.id);
            const statusBadge = this.getStatusBadge(firstTrack);
            const statusClass = firstTrack.status; // pending, running, completed
            
            // Build compact step info
            const stepInfo = this.buildStepInfo(firstTrack, stage);
            
            return `
                <div class="timeline-item ${statusClass}">
                    <div class="timeline-marker">
                        <div class="timeline-circle ${statusClass}">
                            <span>${stage.order}</span>
                        </div>
                        ${index < totalStages - 1 ? '<div class="timeline-line"></div>' : ''}
                    </div>
                    <div class="timeline-content">
                        <div class="stage-card">
                            <div class="stage-header-row toggle-stage-details" data-track-id="${firstTrack.id}" style="cursor:pointer;">
                                <div class="stage-info">
                                    <h6 class="stage-title">${stage.stage_name}</h6>
                                    <small class="text-muted">Day ${stage.order} • ${stage.duration_hours || 0}h duration</small>
                                    ${stepInfo}
                                </div>
                                <div class="stage-status">
                                    ${statusBadge}
                                </div>
                                <div class="stage-actions" onclick="event.stopPropagation();">
                                    ${this.renderStageActions(firstTrack)}
                                </div>
                            </div>
                            ${isExpanded ? this.renderStageDetails(firstTrack) : ''}
                        </div>
                    </div>
                </div>
            `;
        },
        
        buildStepInfo: function(track, stage) {
            const badges = [];
            
            // Result step flag
            if (stage.is_result_stage) {
                badges.push(`<span class="step-info-badge result-step"><i class="mdi mdi-chart-line"></i> Result step</span>`);
            }
            
            const isCompleted = !!track.ended_at || track.status === 'completed';

            // Started / Completed status
            if (track.started_at && !isCompleted) {
                badges.push(`<span class="step-info-badge started"><i class="mdi mdi-play-circle"></i> Started</span>`);
                
                // Time info
                const timeInfo = this.getStepTimeInfo(track, stage);
                if (timeInfo.elapsed) {
                    badges.push(`<span class="step-info-badge time"><i class="mdi mdi-clock-outline"></i> ${timeInfo.elapsed}</span>`);
                }
                if (timeInfo.remaining) {
                    const isOverdue = String(timeInfo.remaining).includes('overdue');
                    badges.push(`<span class="step-info-badge ${isOverdue ? 'overdue' : 'time'}"><i class="mdi mdi-timer-sand"></i> ${timeInfo.remaining}</span>`);
                }
                
                // User info
                if (track.user && track.user.name) {
                    badges.push(`<span class="step-info-badge user"><i class="mdi mdi-account"></i> ${this.escapeHtml(track.user.name)}</span>`);
                }
            } else if (track.started_at && isCompleted) {
                badges.push(`<span class="step-info-badge completed"><i class="mdi mdi-check-circle"></i> Completed</span>`);

                const timeInfo = this.getStepTimeInfo(track, stage);
                if (timeInfo.elapsed) {
                    badges.push(`<span class="step-info-badge time"><i class="mdi mdi-clock-outline"></i> ${timeInfo.elapsed}</span>`);
                }
            } else {
                badges.push(`<span class="step-info-badge not-started"><i class="mdi mdi-timer-sand"></i> Not started</span>`);
            }
            
            // Ended info
            if (track.ended_at) {
                const endedByName = (track.endedBy && track.endedBy.name)
                    ? track.endedBy.name
                    : (track.user && track.user.name ? track.user.name : null);

                if (endedByName) {
                    badges.push(`<span class="step-info-badge user"><i class="mdi mdi-account-check"></i> Ended by ${this.escapeHtml(endedByName)}</span>`);
                }
            }
            
            return `<div class="step-info-badges">${badges.join('')}</div>`;
        },
        
        getStepTimeInfo: function(track, stage) {
            const result = { elapsed: null, remaining: null };
            
            if (!track.started_at) return result;
            
            const started = new Date(track.started_at);
            const endTime = track.ended_at ? new Date(track.ended_at) : new Date();
            const elapsedMs = endTime - started;
            
            // Calculate elapsed time
            const elapsedHours = Math.floor(elapsedMs / (1000 * 60 * 60));
            const elapsedMinutes = Math.floor((elapsedMs % (1000 * 60 * 60)) / (1000 * 60));
            
            if (elapsedHours > 0 || elapsedMinutes > 0) {
                result.elapsed = `${elapsedHours}h ${elapsedMinutes}m elapsed`;
            }
            
            // If stage is ended, don't show remaining/overdue timer.
            if (track.ended_at) {
                return result;
            }

            // Calculate remaining time
            let expectedEnd = null;
            if (track.expected_end_at) {
                expectedEnd = new Date(track.expected_end_at);
            } else if (stage.duration_hours) {
                expectedEnd = new Date(started.getTime() + (stage.duration_hours * 60 * 60 * 1000));
            }
            
            if (expectedEnd) {
                const now = new Date();
                const remainingMs = expectedEnd - now;
                if (remainingMs > 0) {
                    const remainingHours = Math.floor(remainingMs / (1000 * 60 * 60));
                    const remainingMinutes = Math.floor((remainingMs % (1000 * 60 * 60)) / (1000 * 60));
                    result.remaining = `${remainingHours}h ${remainingMinutes}m remaining`;
                } else {
                    const overdueHours = Math.floor(Math.abs(remainingMs) / (1000 * 60 * 60));
                    const overdueMinutes = Math.floor((Math.abs(remainingMs) % (1000 * 60 * 60)) / (1000 * 60));
                    result.remaining = `${overdueHours}h ${overdueMinutes}m overdue`;
                }
            }
            
            return result;
        },
        
        renderStageActions: function(track) {
            const canEdit = this.canEditActiveStageHeader();
            const toggleDetails = `
                    <button class="btn btn-sm btn-outline-secondary toggle-stage-details" data-track-id="${track.id}">
                        <i class="mdi mdi-chevron-down"></i>
                    </button>
                `;

            if (!canEdit) {
                return toggleDetails;
            }

            if (track.status === 'pending') {
                return `
                    <button class="btn btn-sm btn-success start-stage-btn" data-track-id="${track.id}">
                        <i class="mdi mdi-play"></i> Start
                    </button>
                    <button class="btn btn-sm btn-outline-primary edit-stage-btn" data-track-id="${track.id}">
                        <i class="mdi mdi-pencil"></i> Edit
                    </button>
                    ${toggleDetails}
                `;
            } else if (track.status === 'running') {
                return `
                    <button class="btn btn-sm btn-danger end-stage-btn" data-track-id="${track.id}">
                        <i class="mdi mdi-stop"></i> End
                    </button>
                    <button class="btn btn-sm btn-outline-primary edit-stage-btn" data-track-id="${track.id}">
                        <i class="mdi mdi-pencil"></i> Edit
                    </button>
                    ${toggleDetails}
                `;
            } else {
                return toggleDetails;
            }
        },

        canEditStageHeader: function(stageHeaderId) {
            const stageHeader = (this.stageHeaders || []).find(function(sh) {
                return String(sh.id) === String(stageHeaderId);
            });

            return !!(stageHeader && stageHeader.can_edit === true);
        },

        canEditActiveStageHeader: function() {
            return this.canEditStageHeader(this.activeStageHeaderId);
        },
        
        renderStageDetails: function(track) {
            const self = this;
            const stage = track.test_stage;
            const isResultStage = stage.is_result_stage;
            const isStageLocked = this.isTrackLocked(track);
            const lockReason = this.getTrackLockReason(track);
            const endedByName = this.escapeHtml(this.getEndedByName(track));
            console.log('DEBUG: renderStageDetails stage.controls_required:', stage.controls_required);
            
            return `
                <div class="stage-details-stepper-form ${isStageLocked ? 'stage-locked' : ''}" id="stepper-form-${track.id}" data-stage-locked="${isStageLocked ? '1' : '0'}" data-lock-reason="${lockReason || ''}" data-controls-config='${JSON.stringify(stage.controls_required || [])}' data-media-config='${JSON.stringify(stage.media_required || [])}' data-stage-id='${track.id}' data-track-id='${track.id}' data-test-stage='${JSON.stringify(stage)}'>
                    <!-- Stepper Header -->
                    <div class="stepper-header mb-3">
                        <div class="step-item active" data-step="1" data-track-id="${track.id}">
                            <div class="step-circle">1</div>
                            <div class="step-label text-uppercase">Basic Info</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="2" data-track-id="${track.id}">
                            <div class="step-circle">2</div>
                            <div class="step-label text-uppercase">Equipment</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="3" data-track-id="${track.id}">
                            <div class="step-circle">3</div>
                            <div class="step-label text-uppercase">Controls</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="4" data-track-id="${track.id}">
                            <div class="step-circle">4</div>
                            <div class="step-label text-uppercase">Media</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="5" data-track-id="${track.id}">
                            <div class="step-circle">5</div>
                            <div class="step-label text-uppercase">Diluents</div>
                        </div>
                        ${track.test_stage && track.test_stage.is_result_stage ? `
                        <div class="step-line"></div>
                        <div class="step-item" data-step="6" data-track-id="${track.id}">
                            <div class="step-circle">6</div>
                            <div class="step-label text-uppercase">Results</div>
                        </div>
                        ` : ''}
                    </div>

                    <div class="stepper-content">
                        <div class="alert alert-secondary stage-locked-banner ${isStageLocked ? '' : 'd-none'} mb-3 py-2 px-3" style="font-size:0.85rem;">
                            <i class="mdi mdi-lock-outline mr-1"></i>
                            <span class="stage-locked-banner-text">${this.escapeHtml(this.stageLockBannerText(lockReason))}</span>
                        </div>
                        <!-- Step 1: Basic Info -->
                        <div class="step-pane active" id="step-pane-1-${track.id}">
                            <h4 class="step-title mb-1">Basic Stage Information</h4>
                            <p class="step-description mb-3">Review timing and analyst information for this stage.</p>
                            
                            <div class="info-card p-3 mb-3" style="background: #f8faff; border: 1px dashed #d0d7e7; border-radius: 8px;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">Start Date & Time</label>
                                            <div class="input-group">
                                                <input type="datetime-local" class="form-control" id="start-date-${track.id}" 
                                                       value="${track.started_at ? new Date(track.started_at).toISOString().slice(0,16) : ''}"
                                                       readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-light"><i class="mdi mdi-lock-outline"></i></span>
                                                </div>
                                            </div>
                                            <small class="text-info"><i class="mdi mdi-information-outline"></i> Automatically set when stage is started</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">End Date & Time</label>
                                            <div class="input-group">
                                                <input type="datetime-local" class="form-control" id="end-date-${track.id}" 
                                                       value="${track.ended_at ? new Date(track.ended_at).toISOString().slice(0,16) : ''}"
                                                       readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-light"><i class="mdi mdi-lock-outline"></i></span>
                                                </div>
                                            </div>
                                            <small class="text-info"><i class="mdi mdi-information-outline"></i> Automatically set when stage is ended</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">Started By (Analyst)</label>
                                            <input type="text" class="form-control" value="${track.user ? track.user.name : 'Not started'}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">Ended By (Analyst)</label>
                                            <input type="text" class="form-control" id="ended-by-name-${track.id}" value="${endedByName}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-md-12 text-center">
                                        <div class="badge badge-soft-primary px-2 py-1">
                                            <i class="mdi mdi-clock-fast mr-1"></i> Expected Duration: ${stage.duration_hours || 0} hours
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back" onclick="methodSequences.cancelEdit('${track.id}')">
                                    <i class="mdi mdi-close mr-1"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-nav-continue next-step-btn" data-track-id="${track.id}" data-next="2">
                                    Continue to Equipment <i class="mdi mdi-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Equipment -->
                        <div class="step-pane" id="step-pane-2-${track.id}">
                            <h4 class="step-title mb-1">Equipment Selection</h4>
                            <p class="step-description mb-3">Please register the laboratory equipment used for this assessment cycle. Each entry requires an equipment number and calibration status.</p>
                            
                            <div class="equipment-registration-card p-3 mb-3" style="background: #f8faff; border: 1px dashed #d0d7e7; border-radius: 8px;">
                                <div class="form-group mb-2">
                                    <label class="form-label font-weight-bold">Equipment Name</label>
                                    <select class="form-control" id="equipment-picker-${track.id}">
                                        <option></option>
                                    </select>
                                    <small class="text-muted">e.g., Centrifuge Model X</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">Equipment No</label>
                                            <input type="text" class="form-control" id="equipment-serial-${track.id}" placeholder="Equipment Number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="form-label font-weight-bold">Calibration</label>
                                            <input type="date" class="form-control" id="equipment-calibration-${track.id}">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-block btn-sm font-weight-bold add-solution-item" data-track-id="${track.id}" data-type="equipment">
                                    <i class="mdi mdi-plus-circle-outline mr-1"></i> Add Equipment
                                </button>
                            </div>

                            <div id="equipment-list-${track.id}" class="mb-3">
                                <!-- Cards will be rendered here -->
                            </div>
                            
                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="1">
                                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Basic Info
                                </button>
                                <button type="button" class="btn btn-nav-continue next-step-btn" data-track-id="${track.id}" data-next="3">
                                    Continue to Controls <i class="mdi mdi-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Controls -->
                        <div class="step-pane" id="step-pane-3-${track.id}">
                            <h4 class="step-title mb-1">Lab Run Controls</h4>
                            <p class="step-description mb-3">Select and configure the controls used for this stage. Each entry requires a lot number and expiration date for validation.</p>

                            <div class="section-title-with-icon">
                                <i class="mdi mdi-layers-outline"></i>
                                <span>Currently Added Controls</span>
                            </div>

                            <div id="controls-list-${track.id}" class="row mb-3">
                                <!-- Cards will be rendered here -->
                                <div class="col-md-4 mb-2">
                                    <div class="add-more-dashed-box open-solution-entry"
                                         data-track-id="${track.id}" data-entry-type="control" data-picker-id="controls"
                                         role="button" tabindex="0">
                                        <i class="mdi mdi-plus-circle-outline"></i>
                                        <span class="font-weight-bold">Add more controls...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="registration-panel-premium p-0 overflow-hidden mb-3">
                                <div class="control-entry-header solution-entry-header d-flex align-items-center px-3 py-2"
                                     data-track-id="${track.id}" data-entry-type="control" data-picker-id="controls"
                                     role="button" tabindex="0" aria-expanded="false" aria-controls="control-entry-body-${track.id}" style="cursor: pointer;">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 22px; height: 22px;">
                                        <i class="mdi mdi-plus" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h5 class="font-weight-bold mb-0">New Control Entry</h5>
                                    <i class="mdi mdi-chevron-right text-muted solution-entry-toggle-icon ml-auto"></i>
                                </div>

                                <div id="control-entry-body-${track.id}" class="d-none px-3 pb-3">
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <div class="premium-input-group">
                                            <label>Control Name</label>
                                            <select class="form-control premium-input" id="controls-picker-${track.id}">
                                                <option></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="premium-input-group">
                                            <label>Lot Number</label>
                                            <input type="text" class="form-control premium-input" id="control-lot-${track.id}" placeholder="Enter lot number">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="premium-input-group">
                                            <label>Expiration Date</label>
                                            <input type="date" class="form-control premium-input" id="control-expiry-${track.id}">
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <div class="premium-input-group">
                                            <label>Result Nature</label>
                                            <select class="form-control premium-input" id="control-result-nature-${track.id}">
                                                <option value="">Select Result Nature</option>
                                                <option value="quantitative">Quantitative</option>
                                                <option value="qualitative">Qualitative</option>
                                                <option value="none">No Result</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="register-btn-premium mt-1 add-solution-item" data-track-id="${track.id}" data-type="controls">
                                    <i class="mdi mdi-check-circle-outline"></i> Register Control Item
                                </button>
                                
                                <p class="registration-helper-text">
                                    Please ensure all control parameters are verified against the manufacturer's specification<br>
                                    before finalizing this step.
                                </p>
                                </div>
                            </div>

                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="2">
                                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Equipment
                                </button>
                                <button type="button" class="btn btn-nav-continue next-step-btn" data-track-id="${track.id}" data-next="4">
                                    Continue to Media <i class="mdi mdi-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Media -->
                        <div class="step-pane" id="step-pane-4-${track.id}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h4 class="step-title mb-0">Uninoculated Media</h4>
                            </div>
                            <p class="step-description mb-3">Document the preparation and verification of uninoculated growth media to ensure sterile baseline conditions for the assessment.</p>
                            
                            <!-- Media Cards Grid -->
                            <div class="row mb-3" id="media-cards-grid-${track.id}">
                                <!-- Media cards will be rendered here -->
                            </div>

                            <!-- Add New Media Entry Panel -->
                            <div class="media-registration-panel p-0 overflow-hidden">
                                <div class="media-registration-title media-entry-header solution-entry-header px-3 py-2"
                                     data-track-id="${track.id}" data-entry-type="media" data-picker-id="media"
                                     role="button" tabindex="0" aria-expanded="false" aria-controls="media-entry-body-${track.id}" style="cursor: pointer;">
                                    <span>Add New Media Entry</span>
                                    <i class="mdi mdi-chevron-right text-muted solution-entry-toggle-icon ml-auto"></i>
                                </div>
                                <div id="media-entry-body-${track.id}" class="d-none px-3 pb-3">
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <div class="premium-input-group">
                                            <label class="form-label font-weight-bold">Media Name</label>
                                            <select class="form-control premium-input" id="media-picker-${track.id}">
                                                <option></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="premium-input-group">
                                            <label class="form-label font-weight-bold">Preparation Date</label>
                                            <input type="date" class="form-control premium-input" id="media-prep-date-${track.id}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="premium-input-group">
                                            <label class="form-label font-weight-bold">Preparation No.</label>
                                            <input type="text" class="form-control premium-input" id="media-prep-no-${track.id}" placeholder="Enter preparation number">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="premium-input-group">
                                            <label class="form-label font-weight-bold">Expiry Date</label>
                                            <input type="date" class="form-control premium-input" id="media-expiry-${track.id}">
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <div class="premium-input-group">
                                            <label class="form-label font-weight-bold">Result Nature</label>
                                            <select class="form-control premium-input" id="media-result-nature-${track.id}">
                                                <option value="">Select Result Nature</option>
                                                <option value="quantitative">Quantitative</option>
                                                <option value="qualitative">Qualitative</option>
                                                <option value="none">No Result</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold add-solution-item" data-track-id="${track.id}" data-type="media" style="background: #0061e0; border: none; border-radius: 6px;">
                                        <i class="mdi mdi-plus mr-1"></i> Add Media
                                    </button>
                                </div>
                                </div>
                            </div>

                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="3">
                                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Controls
                                </button>
                                <button type="button" class="btn btn-nav-continue next-step-btn" data-track-id="${track.id}" data-next="5">
                                    Continue to Diluents <i class="mdi mdi-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 5: Diluents -->
                        <div class="step-pane" id="step-pane-5-${track.id}">
                            <h4 class="step-title mb-1">Uninoculated Diluents</h4>
                            <p class="step-description mb-3">Document preparation and verification details for uninoculated diluents.</p>
                            
                            <!-- Diluent Cards Grid Container -->
                            <div class="row mb-3" id="diluent-cards-grid-${track.id}">
                                <!-- Placeholder card will be inserted here initially -->
                                <div class="col-md-6 mb-2 diluent-placeholder-wrapper">
                                    <div class="placeholder-card-dashed">
                                        <div class="placeholder-icon-container">
                                            <i class="mdi mdi-beaker-outline"></i>
                                        </div>
                                        <div class="placeholder-text-main">Waiting for additional diluent data</div>
                                        <div class="placeholder-text-sub">Add diluent using the form below</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Diluent Entry Panel (Collapsible) -->
                            <div class="media-registration-panel p-0 overflow-hidden">
                                <!-- Panel Header (Toggle) -->
                                <div class="media-registration-title diluent-entry-header solution-entry-header px-3 py-2"
                                     data-track-id="${track.id}" data-entry-type="diluent" data-picker-id="diluents"
                                     role="button" tabindex="0" aria-expanded="false" aria-controls="diluent-entry-body-${track.id}" style="cursor: pointer;">
                                    <span>Add New Diluent Entry</span>
                                    <i class="mdi mdi-chevron-right text-muted solution-entry-toggle-icon ml-auto"></i>
                                </div>
                                
                                <!-- Form Body (initially hidden) -->
                                <div id="diluent-entry-body-${track.id}" class="d-none px-3 pb-3">
                                    <div class="form-group mb-2">
                                        <label class="form-label font-weight-bold">Diluent Name</label>
                                        <select class="form-control premium-input" id="diluents-picker-${track.id}">
                                            <option></option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="form-label font-weight-bold">Preparation Date</label>
                                        <input type="date" class="form-control premium-input" id="diluent-prep-date-${track.id}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="form-label font-weight-bold">Result Nature</label>
                                        <select class="form-control premium-input" id="diluent-result-nature-${track.id}">
                                            <option value="">Select Result Nature</option>
                                            <option value="no_result">No Result</option>
                                            <option value="qualitative">Qualitative</option>
                                            <option value="quantitative">Quantitative</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="form-label font-weight-bold">Preparation No.</label>
                                        <input type="text" class="form-control premium-input" id="diluent-prep-no-${track.id}" placeholder="e.g., P-001-2026">
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-block btn-sm font-weight-bold add-solution-item" data-track-id="${track.id}" data-type="diluents">
                                        <i class="mdi mdi-plus-circle-outline mr-1"></i> Add Diluent
                                    </button>
                                </div>
                            </div>

                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="4">
                                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Media
                                </button>
                                <div class="ml-auto d-flex gap-2">
                                    <button type="button" class="btn btn-nav-continue save-stage-details-btn" onclick="methodSequences.saveStageDetails('${track.id}')">
                                        Save Stage Details <i class="mdi mdi-content-save ml-1"></i>
                                    </button>
                                    ${isResultStage ? `
                                    <button type="button" class="btn btn-nav-continue next-step-btn" data-track-id="${track.id}" data-next="6">
                                        Proceed to Results <i class="mdi mdi-arrow-right ml-1"></i>
                                    </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Results (only for result stages) -->
                        ${track.test_stage && track.test_stage.is_result_stage ? `
                        <div class="step-pane" id="step-pane-6-${track.id}">
                            <h4 class="step-title mb-1">Capture Stage Results</h4>
                            <p class="step-description mb-3">Enter results for controls/media and samples.</p>
                            
                            <!-- Solution Results Container (dynamically loaded) -->
                            <div id="solution-results-container-${track.id}">
                                <div class="text-center text-muted py-2"><i class="mdi mdi-loading mdi-spin"></i> Loading solution results...</div>
                            </div>
                            
                            <!-- Sample Results Container (dynamically loaded) -->
                            <div id="sample-results-container-${track.id}" class="mt-3">
                                <div class="text-center text-muted py-2"><i class="mdi mdi-loading mdi-spin"></i> Loading sample results...</div>
                            </div>
                            
                            <div class="stepper-actions d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="5">
                                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Diluents
                                </button>
                                <button type="button" class="btn btn-nav-continue save-results-btn" onclick="methodSequences.saveResultsData('${track.id}')">
                                    Save Results <i class="mdi mdi-content-save ml-1"></i>
                                </button>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        },
        
        getStatusBadge: function(track) {
            if (track.status === 'pending') {
                return '<span class="badge badge-secondary timer-badge">Pending</span>';
            } else if (track.status === 'completed') {
                return '<span class="badge badge-success timer-badge">Completed</span>';
            } else if (track.status === 'running') {
                // Calculate remaining time
                const duration = track.test_stage.duration_hours || 0;
                const started = new Date(track.started_at);
                const expectedEnd = new Date(started.getTime() + duration * 3600000);
                const now = new Date();
                const remainingMs = expectedEnd - now;
                const remainingHours = remainingMs / 3600000;
                
                if (remainingHours < 0) {
                    return `<span class="badge badge-danger timer-badge"><i class="mdi mdi-timer-alert"></i> Overtime (${Math.abs(remainingHours).toFixed(1)}h over)</span>`;
                } else if (remainingHours < 4) {
                    return `<span class="badge badge-warning timer-badge"><i class="mdi mdi-timer-sand"></i> ${remainingHours.toFixed(1)}h remaining</span>`;
                } else {
                    return `<span class="badge badge-success timer-badge"><i class="mdi mdi-timer"></i> ${remainingHours.toFixed(1)}h remaining</span>`;
                }
            }
            return '';
        },
        
        getUniqueSampleCodes: function(trackRecords) {
            const codes = new Set();
            trackRecords.forEach(track => {
                if (track.sample_detail && track.sample_detail.sample_code) {
                    codes.add(track.sample_detail.sample_code);
                }
            });
            return Array.from(codes);
        },

        normalizeSampleList: function(list) {
            if (!list) {
                return [];
            }
            if (Array.isArray(list)) {
                return list;
            }
            if (typeof list === 'object') {
                return Object.values(list);
            }
            return [];
        },

        initRunSampleSelect: function(selector, samples, placeholder, valueKey, labelKey) {
            const $select = $(selector);
            if ($select.data('select2')) {
                $select.select2('destroy');
            }

            $select.empty().append('<option></option>');
            samples.forEach(function(sample) {
                if (!sample || !sample[valueKey]) {
                    return;
                }
                const label = sample[labelKey] || sample.sample_code || sample.id;
                $select.append($('<option></option>').attr('value', sample[valueKey]).text(label));
            });

            $select.select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%',
                dropdownParent: $('#create-run-modal'),
            });
        },
        
        showCreateRunModal: function(stageHeaderId) {
            const self = this;
            $('#run-stage-header-id').val(stageHeaderId);
            
            // Reset modal state
            $('#create-run-success-alert').addClass('d-none');
            $('#save-run-btn').prop('disabled', false).show();
            $('#create-run-form').show();
            $('#create-run-empty-notice').addClass('d-none').text('');
            
            // Show loading state
            const $currentSelect = $('#run-samples-select');
            const $otherSelect = $('#run-other-samples-select');
            if ($currentSelect.data('select2')) {
                $currentSelect.select2('destroy');
            }
            if ($otherSelect.data('select2')) {
                $otherSelect.select2('destroy');
            }
            $currentSelect.html('<option>Loading...</option>');
            $otherSelect.html('<option>Loading...</option>');
            
            // Load samples for this stage header
            $.ajax({
                url: `/sample-workflow/batch/${this.batchId}/method-sequences/${stageHeaderId}/samples`,
                method: 'GET',
                success: function(data) {
                    const current = self.normalizeSampleList(data.current);
                    const other = self.normalizeSampleList(data.other);

                    self.initRunSampleSelect(
                        '#run-samples-select',
                        current,
                        current.length ? 'Select samples from this batch' : 'No eligible samples in this batch',
                        'id',
                        'sample_code'
                    );

                    self.initRunSampleSelect(
                        '#run-other-samples-select',
                        other,
                        other.length ? 'Select samples from other batches' : 'No eligible samples from other batches',
                        'id',
                        'display_code'
                    );

                    if (data.messages && data.messages.current_empty) {
                        $('#create-run-empty-notice')
                            .text(data.messages.current_empty)
                            .removeClass('d-none');
                    }

                    $('#create-run-modal').modal('show');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Error loading eligible samples';
                    alert(message);
                }
            });
        },
        
        saveRun: function() {
            const self = this;
            console.log('[METHOD-SEQUENCES] saveRun() called');
            const stageHeaderId = $('#run-stage-header-id').val();
            if (!this.canEditStageHeader(stageHeaderId)) {
                toastr.error('You can only create runs for your assigned lab section(s).');
                return;
            }
            const currentIds = $('#run-samples-select').val() || [];
            const otherIds = $('#run-other-samples-select').val() || [];
            const allSampleIds = [...currentIds, ...otherIds];

            if (allSampleIds.length === 0) {
                alert('Please select at least one sample.');
                return;
            }

            const formData = {
                stage_header_id: $('#run-stage-header-id').val(),
                sample_ids: allSampleIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            console.log('[METHOD-SEQUENCES] Sending AJAX POST to /method-sequences/runs with data:', formData);
            $.ajax({
                url: '/method-sequences/runs',
                method: 'POST',
                data: formData,
                success: function(response) {
                    console.log('[METHOD-SEQUENCES] AJAX success response:', response);
                    if (response.success) {
                        // Show success message in modal
                        $('#create-run-success-alert').removeClass('d-none');
                        $('#save-run-btn').prop('disabled', true).hide();
                        $('#create-run-form').hide();
                        
                        // Refresh runs in background
                        console.log('[METHOD-SEQUENCES] Calling loadRuns with stage_header_id:', formData.stage_header_id);
                        self.loadRuns(formData.stage_header_id);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[METHOD-SEQUENCES] AJAX error:', error, xhr);
                    alert('Error creating run');
                }
            });
        },
        
        idInList: function(list, id) {
            const needle = String(id);
            return Array.isArray(list) && list.some(item => String(item) === needle);
        },

        /**
         * After init / pipeline remount, open the latest run and the filtered (or first) stage.
         * Returns the track id that should have form data loaded, or null.
         */
        ensureDefaultExpansion: function(runs) {
            if (!this.shouldAutoExpand || !Array.isArray(runs) || runs.length === 0) {
                return null;
            }

            this.shouldAutoExpand = false;

            if (this.expandedRuns.length === 0) {
                this.expandedRuns = [String(runs[0].id)];
            }

            if (this.expandedStages.length > 0) {
                return String(this.expandedStages[0]);
            }

            const expandedRun = runs.find(run => this.idInList(this.expandedRuns, run.id)) || runs[0];
            const tracks = Array.isArray(expandedRun.track_records) ? expandedRun.track_records : [];
            if (tracks.length === 0) {
                return null;
            }

            let track = null;
            if (this.stageOrderFilter !== null && !Number.isNaN(this.stageOrderFilter)) {
                track = tracks.find(t => Number(t.test_stage?.order) === Number(this.stageOrderFilter));
            }

            if (!track) {
                track = tracks.find(t => t.status === 'running')
                    || [...tracks].sort((a, b) => (a.test_stage?.order ?? 999) - (b.test_stage?.order ?? 999))[0];
            }

            if (!track) {
                return null;
            }

            this.expandedStages = [String(track.id)];
            return String(track.id);
        },

        toggleRun: function(runId) {
            runId = String(runId);
            const index = this.expandedRuns.findIndex(id => String(id) === runId);
            if (index > -1) {
                this.expandedRuns.splice(index, 1);
            } else {
                // Accordion behavior: close other runs
                this.expandedRuns = [runId];
            }
            this.loadRuns(this.activeStageHeaderId);
        },
        
        toggleStageDetails: function(trackId) {
            trackId = String(trackId);
            const index = this.expandedStages.findIndex(id => String(id) === trackId);
            if (index > -1) {
                this.expandedStages.splice(index, 1);
            } else {
                // Accordion behavior: close other stages
                this.expandedStages = [trackId];
                // Load form data when expanding
                setTimeout(() => {
                    this.loadStageFormData(trackId);
                }, 100);
            }
            this.loadRuns(this.activeStageHeaderId);
        },
        
        startStage: function(trackId) {
            console.log('[METHOD-SEQUENCES] startStage() called with trackId:', trackId);
            const self = this;
            if (!confirm('Are you sure you want to start this stage? This will automatically set the current date and time as the start time and assign you as the analyst who started.')) {
                return;
            }
            
            console.log('[METHOD-SEQUENCES] Sending POST to /method-sequences/tracks/' + trackId + '/start');
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/start`,
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    console.log('[METHOD-SEQUENCES] startStage AJAX success:', response);
                    if (response.success) {
                        // Re-expand stage and reload form data after starting
                        self.loadRuns(self.activeStageHeaderId, function() {
                            // Re-add to expandedStages to keep it expanded
                            if (!self.idInList(self.expandedStages, trackId)) {
                                self.expandedStages = [String(trackId)];
                            }
                            // Re-render the stage to show expanded view with form
                            self.loadRuns(self.activeStageHeaderId);
                            // Load and populate form data
                            setTimeout(() => {
                                self.loadStageFormData(trackId);
                            }, 100);
                        });
                        alert('Stage started and timestamped successfully!');
                    } else {
                        alert('Error starting stage: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    console.error('[METHOD-SEQUENCES] startStage AJAX error:', xhr);
                    let errorMessage = 'Error starting stage';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Keep default error message
                        }
                    }
                    alert(errorMessage);
                }
            });
        },
        
        endStage: function(trackId) {
            const self = this;
            if (!confirm('Are you sure you want to end this stage? This will automatically set the current date and time as the end time and assign you as the analyst who ended.')) {
                return;
            }
            
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/end`,
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        // Re-expand stage and reload form data after ending
                        self.loadRuns(self.activeStageHeaderId, function() {
                            // Re-add to expandedStages to keep it expanded
                            if (!self.idInList(self.expandedStages, trackId)) {
                                self.expandedStages = [String(trackId)];
                            }
                            // Re-render the stage to show expanded view with form
                            self.loadRuns(self.activeStageHeaderId);
                            // Load and populate form data
                            setTimeout(() => {
                                self.loadStageFormData(trackId);
                            }, 100);
                        });
                        alert('Stage ended and timestamped successfully!');
                    } else {
                        alert('Error ending stage: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error ending stage';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Keep default error message
                        }
                    }
                    alert(errorMessage);
                }
            });
        },
        
        showEditStageModal: function(trackId) {
            const self = this;
            $('#edit-track-id').val(trackId);
            
            // Load edit data for this track
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/edit-data`,
                method: 'GET',
                success: function(data) {
                    console.log('Edit data received:', data); // Debug log
                    
                    // Convert current values to strings for comparison, handle undefined/null cases
                    const currentEquipment = (data.current.equipment || []).map(id => String(id));
                    const currentMedia = (data.current.media || []).map(id => String(id));
                    const currentControls = (data.current.controls || []).map(id => String(id));
                    
                    // Populate equipment dropdown
                    const equipmentOptions = data.equipment.map(eq => 
                        `<option value="${eq.id}">${eq.name}</option>`
                    ).join('');
                    $('#edit-equipment').html(equipmentOptions).select2({
                        placeholder: 'Select Equipment',
                        allowClear: true
                    });
                    
                    // Set selected values for equipment
                    if (currentEquipment.length > 0) {
                        $('#edit-equipment').val(currentEquipment).trigger('change');
                    }
                    
                    // Populate media dropdown
                    const mediaOptions = data.media.map(med => 
                        `<option value="${med.id}">${med.name}</option>`
                    ).join('');
                    $('#edit-media').html(mediaOptions).select2({
                        placeholder: 'Select Media',
                        allowClear: true
                    });
                    
                    // Set selected values for media
                    if (currentMedia.length > 0) {
                        $('#edit-media').val(currentMedia).trigger('change');
                    }
                    
                    // Populate controls dropdown
                    const controlsOptions = data.controls.map(ctrl => 
                        `<option value="${ctrl.id}">${ctrl.name}</option>`
                    ).join('');
                    $('#edit-controls').html(controlsOptions).select2({
                        placeholder: 'Select Controls',
                        allowClear: true
                    });
                    
                    // Set selected values for controls
                    if (currentControls.length > 0) {
                        $('#edit-controls').val(currentControls).trigger('change');
                    }
                    
                    $('#edit-stage-modal').modal('show');
                },
                error: function() {
                    alert('Error loading edit data');
                }
            });
        },
        
        saveStageData: function() {
            const self = this;
            const trackId = $('#edit-track-id').val();
            const formData = {
                equipment_ids: $('#edit-equipment').val(),
                media_ids: $('#edit-media').val(),
                controls_ids: $('#edit-controls').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/update`,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#edit-stage-modal').modal('hide');
                        self.loadRuns(self.activeStageHeaderId);
                        alert('Stage data updated successfully!');
                    }
                },
                error: function() {
                    alert('Error updating stage data');
                }
            });
        },
        
        showAddResultModal: function(trackId) {
            $('#result-track-id').val(trackId);
            $('#result-input').val('');
            $('#remarks-input').val('');
            $('#add-result-modal').modal('show');
        },
        
        saveResult: function() {
            const self = this;
            const trackId = $('#result-track-id').val();
            const result = $('#result-input').val();
            const remarks = $('#remarks-input').val();
            
            if (!result.trim()) {
                alert('Please enter a result');
                return;
            }
            
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/result`,
                method: 'POST',
                data: {
                    result: result,
                    remarks: remarks,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#add-result-modal').modal('hide');
                        self.loadRuns(self.activeStageHeaderId);
                        alert('Result saved successfully!');
                    }
                },
                error: function() {
                    alert('Error saving result');
                }
            });
        },
        
        showPostResultsModal: function() {
            const batchId = this.batchId;
            
            // Load tracking results
            $.ajax({
                url: `/method-sequences/batch/${batchId}/tracking-results`,
                method: 'GET',
                                              success: function(response) {
                    $('#post-results-import-feedback').remove();
                    $('#post-results-import-file').val('');
                    const today = new Date().toISOString().slice(0, 10);
                    $('#start-analysis-date').val(today);
                    $('#end-analysis-date').val(today);
                    MethodSequences.renderPostResultsTable(response.records, response.all_units);
                    $('#post-results-modal').modal('show');
                    MethodSequences.validatePostButton();
                },
                error: function() {
                    alert('Error loading tracking results');
                }
            });
        },
        
        normalizePostResultsMatchKey: function(value) {
            return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
        },

        normalizePostResultsHeaderKey: function(value) {
            return String(value || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, ' ')
                .replace(/ /g, '_');
        },

        mapPostResultsImportHeaders: function(headerCells) {
            const aliases = {
                track_id: ['track_id', 'trackid', 'id'],
                sample_code: ['sample_code', 'sample', 'code', 'samplecode'],
                analyte: ['analyte', 'analyte_name', 'parameter'],
                result: ['result', 'value', 'reading'],
                reporting_symbol: ['reporting_symbol', 'symbol'],
                main_low: ['main_low', 'low', 'limit_low'],
                main_high: ['main_high', 'high', 'limit_high'],
                remark: ['remark', 'remarks', 'flag'],
                method: ['method', 'method_name', 'method_id'],
                reporting_unit: ['reporting_unit', 'unit', 'units'],
            };
            const normalizedHeaders = headerCells.map(h => this.normalizePostResultsHeaderKey(h));
            const col = {};
            Object.keys(aliases).forEach(field => {
                const keys = aliases[field];
                for (let idx = 0; idx < normalizedHeaders.length; idx++) {
                    if (keys.includes(normalizedHeaders[idx])) {
                        col[field] = idx;
                        break;
                    }
                }
            });
            return col;
        },

        buildPostResultsRowMatchIndex: function() {
            const index = { byTrackId: {}, bySampleAnalyte: {}, bySampleOnly: {} };
            $('#post-results-table-body tr.post-result-row').each(function() {
                const $tr = $(this);
                const tid = String($tr.data('track-id'));
                const sc = MethodSequences.normalizePostResultsMatchKey($tr.data('sample-code'));
                const an = MethodSequences.normalizePostResultsMatchKey($tr.data('analyte-name'));
                index.byTrackId[tid] = $tr;
                const saKey = sc + '\u0001' + an;
                if (!index.bySampleAnalyte[saKey]) {
                    index.bySampleAnalyte[saKey] = [];
                }
                index.bySampleAnalyte[saKey].push($tr);
                if (!index.bySampleOnly[sc]) {
                    index.bySampleOnly[sc] = [];
                }
                index.bySampleOnly[sc].push($tr);
            });
            return index;
        },

        findPostResultsRowForImport: function(index, record, colMap) {
            if (colMap.track_id !== undefined) {
                const tid = String(record[colMap.track_id] || '').trim();
                if (tid && index.byTrackId[tid]) {
                    return index.byTrackId[tid];
                }
            }
            const sc = colMap.sample_code !== undefined
                ? this.normalizePostResultsMatchKey(record[colMap.sample_code])
                : '';
            const an = colMap.analyte !== undefined
                ? this.normalizePostResultsMatchKey(record[colMap.analyte])
                : '';
            if (sc && an) {
                const k = sc + '\u0001' + an;
                const list = index.bySampleAnalyte[k];
                if (list && list.length === 1) {
                    return list[0];
                }
                if (list && list.length > 1) {
                    return { ambiguous: true, list };
                }
            }
            if (sc) {
                const list = index.bySampleOnly[sc];
                if (list && list.length === 1) {
                    return list[0];
                }
                if (list && list.length > 1) {
                    return { ambiguous: true, list };
                }
            }
            return null;
        },

        normalizeReportingSymbolImport: function(raw) {
            let s = String(raw || '').trim();
            if (!s) {
                return '';
            }
            if (s.toLowerCase() === 'none' || s === '--- None ---') {
                return 'none';
            }
            const map = {
                '<=': '≤',
                '>=': '≥',
                '≤': '≤',
                '≥': '≥',
            };
            if (map[s]) {
                return map[s];
            }
            const lower = s.toLowerCase();
            if (lower === 'lte') {
                return '≤';
            }
            if (lower === 'gte') {
                return '≥';
            }
            return s;
        },

        normalizeReportingSymbolValue: function(raw) {
            const symbol = String(raw || '').trim();
            if (!symbol || symbol.toLowerCase() === 'none' || symbol === '--- None ---') {
                return 'none';
            }
            return symbol;
        },

        renderReportingSymbolOptions: function(currentSymbol) {
            const selected = this.normalizeReportingSymbolValue(currentSymbol);
            const options = [
                { value: 'none', label: '--- None ---' },
                { value: '=', label: '=' },
                { value: '<', label: '<' },
                { value: '>', label: '>' },
                { value: '≤', label: '≤' },
                { value: '≥', label: '≥' },
            ];

            return options.map((option) => {
                const isSelected = selected === option.value ? 'selected' : '';
                return `<option value="${option.value}" ${isSelected}>${option.label}</option>`;
            }).join('');
        },

        formatRemarkCellFromImportValue: function(val) {
            const t = String(val || '').trim();
            if (!t) {
                return null;
            }
            const u = t.toUpperCase();
            if (u === 'PASS' || u === 'PASSED' || u === 'CONFORMING' || u === 'COMPLIANT') {
                return '<span class="badge badge-success">Conforming</span>';
            }
            if (u === 'FAIL' || u === 'FAILED' || u === 'NON-CONFORMING' || u === 'NON-COMPLIANT') {
                return '<span class="badge badge-danger">Non-Conforming</span>';
            }
            return '<span class="badge badge-info">' + this.escapeHtml(t) + '</span>';
        },

        selectOptionByValueOrText: function($select, raw) {
            if (raw === undefined || raw === null || raw === '') {
                return;
            }
            const v = String(raw).trim();
            const $byVal = $select.find('option').filter(function() {
                return String($(this).val()) === v;
            }).first();
            if ($byVal.length) {
                $select.val($byVal.val());
                return;
            }
            const needle = v.toLowerCase();
            $select.find('option').each(function() {
                const $o = $(this);
                if ($o.text().trim().toLowerCase() === needle) {
                    $select.val($o.attr('value'));
                    return false;
                }
            });
        },

        applyImportedValuesToPostResultRow: function($row, record, colMap) {
            if (colMap.result !== undefined && record[colMap.result] !== undefined) {
                $row.find('.post-result-input').val(formatScientificForInput(String(record[colMap.result])));
            }
            if (colMap.reporting_symbol !== undefined && record[colMap.reporting_symbol] !== undefined) {
                const sym = this.normalizeReportingSymbolImport(record[colMap.reporting_symbol]);
                if (sym !== '') {
                    const $sel = $row.find('.reporting-symbol');
                    $sel.val(sym);
                    if ($sel.val() !== sym) {
                        this.selectOptionByValueOrText($sel, sym);
                    }
                }
            }
            const trackId = $row.data('track-id');
            if (colMap.main_low !== undefined && record[colMap.main_low] !== undefined) {
                const $in = $row.find('input[name="main_low[' + trackId + ']"]');
                if ($in.length) {
                    $in.val(record[colMap.main_low]);
                }
            }
            if (colMap.main_high !== undefined && record[colMap.main_high] !== undefined) {
                const $in = $row.find('input[name="main_high[' + trackId + ']"]');
                if ($in.length) {
                    $in.val(record[colMap.main_high]);
                }
            }
            if (colMap.method !== undefined && record[colMap.method] !== undefined) {
                this.selectOptionByValueOrText($row.find('.method-select'), record[colMap.method]);
            }
            if (colMap.reporting_unit !== undefined && record[colMap.reporting_unit] !== undefined) {
                this.selectOptionByValueOrText($row.find('.unit-select'), record[colMap.reporting_unit]);
            }
            this.recalculateRemark($row);
            if (colMap.remark !== undefined && record[colMap.remark] !== undefined) {
                const html = this.formatRemarkCellFromImportValue(record[colMap.remark]);
                if (html) {
                    $row.find('.remark-cell').html(html);
                }
            }
        },

        showPostResultsImportFeedback: function(html) {
            let $el = $('#post-results-import-feedback');
            if (!$el.length) {
                $el = $('<div id="post-results-import-feedback" class="small mb-2" role="status" aria-live="polite"></div>');
                $('#post-results-table-body').closest('.table-responsive').before($el);
            }
            $el.html(html);
        },

        handlePostResultsImportFile: function(inputEl) {
            const file = inputEl.files && inputEl.files[0];
            if (!file) {
                return;
            }
            const token = $('meta[name="csrf-token"]').attr('content');
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', token);
            $.ajax({
                url: '/method-sequences/import-post-results-sheet',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    inputEl.value = '';
                    if (response.success && response.rows && response.rows.length) {
                        MethodSequences.applyPostResultsImportFromRows(response.rows);
                    } else {
                        alert(response.message || 'Could not read file.');
                    }
                },
                error: function(xhr) {
                    inputEl.value = '';
                    let msg = 'Error importing file.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors && xhr.responseJSON.errors.file) {
                            msg = xhr.responseJSON.errors.file[0];
                        }
                    }
                    alert(msg);
                }
            });
        },

        applyPostResultsImportFromRows: function(rows) {
            if (!rows || !rows.length) {
                alert('The file has no rows.');
                return;
            }
            const asArrays = rows.map(function(row) {
                const arr = Array.isArray(row) ? row : Object.values(row);
                return arr.map(function(c) {
                    return (c === null || c === undefined) ? '' : String(c);
                });
            });
            const nonEmpty = asArrays.filter(function(r) {
                return r.some(function(c) {
                    return String(c).trim() !== '';
                });
            });
            if (nonEmpty.length < 2) {
                alert('Need a header row and at least one data row.');
                return;
            }
            const colMap = this.mapPostResultsImportHeaders(nonEmpty[0]);
            if (colMap.track_id === undefined && colMap.sample_code === undefined) {
                alert('Could not find Track ID or Sample Code column. Expected headers like: Track ID, Sample Code, Analyte, Result, …');
                return;
            }
            if (colMap.track_id === undefined && colMap.sample_code !== undefined && colMap.analyte === undefined) {
                const indexOnly = this.buildPostResultsRowMatchIndex();
                const multiSample = Object.keys(indexOnly.bySampleOnly).filter(function(k) {
                    return indexOnly.bySampleOnly[k].length > 1;
                });
                if (multiSample.length > 0) {
                    alert('This table has multiple rows per sample. Add an Analyte column (or use Track ID) so each import row matches one table row.');
                    return;
                }
            }
            const index = this.buildPostResultsRowMatchIndex();
            let applied = 0;
            let skipped = 0;
            const ambiguous = [];
            const notFound = [];
            for (let r = 1; r < nonEmpty.length; r++) {
                const record = nonEmpty[r];
                if (!record.some(function(c) {
                    return String(c).trim() !== '';
                })) {
                    continue;
                }
                const match = this.findPostResultsRowForImport(index, record, colMap);
                if (match && match.ambiguous) {
                    skipped++;
                    const label = colMap.sample_code !== undefined ? record[colMap.sample_code] : '';
                    ambiguous.push(String(label).trim() || ('row ' + (r + 1)));
                    continue;
                }
                if (!match) {
                    skipped++;
                    const label = colMap.sample_code !== undefined ? record[colMap.sample_code] : '';
                    notFound.push(String(label).trim() || ('row ' + (r + 1)));
                    continue;
                }
                this.applyImportedValuesToPostResultRow(match, record, colMap);
                applied++;
            }
            let msg = '<span class="text-success"><i class="mdi mdi-check"></i> Applied <strong>' + applied + '</strong> row(s).</span>';
            if (skipped) {
                msg += ' <span class="text-warning">' + skipped + ' row(s) skipped.</span>';
            }
            if (ambiguous.length) {
                msg += '<div class="text-warning mt-1">Ambiguous sample match (multiple analytes): ' + this.escapeHtml(ambiguous.slice(0, 8).join(', '))
                    + (ambiguous.length > 8 ? '…' : '') + '</div>';
            }
            if (notFound.length) {
                msg += '<div class="text-danger mt-1">No table row for: ' + this.escapeHtml(notFound.slice(0, 8).join(', '))
                    + (notFound.length > 8 ? '…' : '') + '</div>';
            }
            this.showPostResultsImportFeedback(msg);
        },

        renderPostResultsTable: function(trackingRecords, allUnits) {
            const tbody = $('#post-results-table-body');
            tbody.empty();
            
            trackingRecords.forEach(track => {
                const sampleCode = track.captured_result.sample.sample_code;
                const analyteName = track.captured_result.analyte.name;
                const row = `
                    <tr data-track-id="${track.id}"
                        data-sample-code="${this.escapeHtml(sampleCode)}"
                        data-analyte-name="${this.escapeHtml(analyteName)}"
                        data-main-standard-id="${track.main_standard ? track.main_standard.id : ''}"
                        data-main-standard-analyte-id="${track.main_standard_analyte ? track.main_standard_analyte.id : ''}"
                        class="post-result-row">
                        <td style="width: 40px; text-align: center; vertical-align: middle;">
                            <input type="checkbox" class="pt-select-cb" data-track-id="${track.id}" checked 
                                   style="width: 18px; height: 18px; cursor: pointer; display: inline-block !important; visibility: visible !important; opacity: 1 !important;">
                        </td>
                        <td><strong>${this.escapeHtml(sampleCode)}</strong></td>
                        <td>${this.escapeHtml(analyteName)}</td>
                        <td><input type="text" class="form-control form-control-sm post-result-input" name="result[${track.id}]" value="${this.escapeHtml(formatScientificForInput(track.result ?? ''))}"></td>
                        <td>
                            <select class="form-control form-control-sm reporting-symbol" name="reporting_symbol[${track.id}]">
                                ${this.renderReportingSymbolOptions(track.reporting_symbol)}
                            </select>
                        </td>
                        <td>${track.main_standard ? track.main_standard.name : 'N/A'}</td>
                        <td>
                            ${this.renderEditableStandardLimits(track)}
                        </td>
                        <td class="remark-cell">${this.calculateRemark(track)}</td>
                        <td>
                            <select class="form-control form-control-sm method-select" name="method[${track.id}]">
                                ${this.renderMethodOptions(track.method)}
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm unit-select" name="unit[${track.id}]">
                                ${this.renderUnitOptions(track.reporting_unit, allUnits)}
                            </select>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            // Re-initialize select-all state
            $('#select-all-results').prop('checked', true);

            // Bind change events for limit editing and remark recalculation
            $('.standard-limit-input').on('change', function() {
                const row = $(this).closest('tr');
                MethodSequences.recalculateRemark(row);
            });

            // Bind checkbox events
            $('.pt-select-cb').on('change', function() {
                MethodSequences.validatePostButton();
            });

            // Initial validation call to set button state on render
            this.validatePostButton();
        },
        
        renderEditableStandardLimits: function(track) {
            let html = '';
            
            const analytes = [
                { sa: track.main_standard_analyte, standard: track.main_standard },
                { sa: track.sec_standard_analyte, standard: track.sec_standard },
                { sa: track.third_standard_analyte, standard: track.third_standard }
            ];

            analytes.forEach(item => {
                if (item.sa) {
                    const sa = item.sa;
                    const standardName = item.standard ? item.standard.name : 'Standard';
                    
                    if (sa.standard_value_type === 'is_range' || sa.standard_value_type === 'IsRange') {
                        html += `
                            <div class="limit-group">
                                <input type="number" class="form-control form-control-sm standard-limit-input" 
                                       name="main_low[${track.id}]" value="${sa.low}" step="any" placeholder="Low">
                                <input type="number" class="form-control form-control-sm standard-limit-input" 
                                       name="main_high[${track.id}]" value="${sa.high}" step="any" placeholder="High">
                            </div>
                        `;
                    } else if (sa.standard_value_type === 'is standardvalue' || sa.standard_value_type === 'is_standard_value' || sa.standard_value_type === 'Is_standard_value' || sa.value_type === 'standardvalue') {
                        let val = 'N/A';
                        if (sa.standard_value) {
                            if (sa.standard_value.name === 'Is Value') {
                                val = (sa.standard_is_value || 'N/A') + (sa.value_type || '');
                            } else {
                                val = sa.standard_value.name;
                            }
                        } else {
                            val = (sa.standard_is_value || 'N/A') + (sa.value_type || '');
                        }
                        html += `<div class="small">${val}</div>`;
                    } else {
                        html += `<div class="small">${sa.standard_is_value || 'N/A'}</div>`;
                    }
                }
            });
            
            return html || 'N/A';
        },
        
        calculateRemark: function(track) {
            // Prioritize remark from database/backend
            if (track.remark && track.remark !== '') {
                if (track.remark === 'FAIL') {
                    return '<span class="badge badge-danger">Non-Conforming</span>';
                } else if (track.remark === 'PASS') {
                    return '<span class="badge badge-success">Conforming</span>';
                } else {
                    return '<span class="badge badge-secondary">' + track.remark + '</span>';
                }
            }

            // Fallback to calculation (for edited limits)
            const result = parseFloat(track.result);
            const remarks = [];
            
            if (track.main_standard_analyte) {
                const remark = this.getResultRemark(track.main_standard_analyte, result);
                remarks.push(remark);
            }
            
            if (track.sec_standard_analyte) {
                const remark = this.getResultRemark(track.sec_standard_analyte, result);
                remarks.push(remark);
            }
            
            if (track.third_standard_analyte) {
                const remark = this.getResultRemark(track.third_standard_analyte, result);
                remarks.push(remark);
            }
            
            // Return FAIL if any fail, otherwise PASS
            if (remarks.includes('FAIL')) {
                return '<span class="badge badge-danger">Non-Conforming</span>';
            } else if (remarks.includes('PASS')) {
                return '<span class="badge badge-success">Conforming</span>';
            } else {
                return '<span class="badge badge-secondary">-</span>';
            }
        },
        
        getResultRemark: function(standardAnalyte, result) {
            if (standardAnalyte.standard_value_type === 'is_range') {
                if (result >= standardAnalyte.low && result <= standardAnalyte.high) {
                    return 'PASS';
                } else {
                    return 'FAIL';
                }
            }
            // Add other standard value type logic as needed
            return '-';
        },
        
        recalculateRemark: function(row) {
            // Recalculate remark when limits are edited
            const trackId = row.data('track-id');
            const result = parseFloat(row.find('.post-result-input').val());
            const mainLow = parseFloat(row.find('input[name="main_low[' + trackId + ']"]').val());
            const mainHigh = parseFloat(row.find('input[name="main_high[' + trackId + ']"]').val());
            
            let remark = '-';
            if (!isNaN(result) && !isNaN(mainLow) && !isNaN(mainHigh)) {
                if (result >= mainLow && result <= mainHigh) {
                    remark = '<span class="badge badge-success">Conforming</span>';
                } else {
                    remark = '<span class="badge badge-danger">Non-Conforming</span>';
                }
            }
            
            row.find('.remark-cell').html(remark);
        },
        
        renderMethodOptions: function(method) {
            if (!method) return '<option value="">Select Method</option>';
            return `<option value="${method.id}" selected>${method.name}</option>`;
        },
        
        renderUnitOptions: function(selectedUnit, allUnits) {
            let html = '<option value="">Select Unit</option>';
            if (allUnits && allUnits.length > 0) {
                allUnits.forEach(unit => {
                    const isSelected = selectedUnit && (selectedUnit.id == unit.id || selectedUnit.name == unit.name) ? 'selected' : '';
                    html += `<option value="${unit.id}" ${isSelected}>${unit.name}</option>`;
                });
            } else if (selectedUnit) {
                html += `<option value="${selectedUnit.id}" selected>${selectedUnit.name}</option>`;
            }
            return html;
        },
        
        confirmPostResults: function() {
            const batchId = this.batchId;
            const trackingData = [];
            const token = $('meta[name="csrf-token"]').attr('content');
            const startDate = $('#start-analysis-date').val();
            const endDate = $('#end-analysis-date').val();
            
            // Collect ONLY checked rows
            $('#post-results-table-body tr.post-result-row').each(function() {
                const row = $(this);
                const checkbox = row.find('.pt-select-cb');
                
                if (checkbox.is(':checked')) {
                    const trackId = row.data('track-id');
                    const mainStandardId = row.data('main-standard-id') || null;
                    const mainStandardAnalyteId = row.data('main-standard-analyte-id') || null;
                    const mainLow = row.find('input[name="main_low[' + trackId + ']"]').val();
                    const mainHigh = row.find('input[name="main_high[' + trackId + ']"]').val();
                    const standardLimitText = row.find('td:eq(6)').text().trim();
                    const mainValue = (mainLow !== '' && mainLow != null && mainHigh !== '' && mainHigh != null)
                        ? `${mainLow} - ${mainHigh}`
                        : standardLimitText;

                    trackingData.push({
                        track_id: trackId,
                        result: row.find('.post-result-input').val(),
                        reporting_symbol: row.find('.reporting-symbol').val() === 'none' ? '' : row.find('.reporting-symbol').val(),
                        method_id: row.find('.method-select').val(),
                        reporting_unit_id: row.find('.unit-select').val(),
                        remark: row.find('.remark-cell').text(),
                        main_value: mainValue,
                        main_standard_id: mainStandardId,
                        main_standard_analyte_id: mainStandardAnalyteId,
                        main_limit_low: mainLow,
                        main_limit_high: mainHigh,
                        sec_limit_low: row.find('input[name="sec_low[' + trackId + ']"]').val(),
                        sec_limit_high: row.find('input[name="sec_high[' + trackId + ']"]').val(),
                        third_limit_low: row.find('input[name="third_low[' + trackId + ']"]').val(),
                        third_limit_high: row.find('input[name="third_high[' + trackId + ']"]').val(),
                    });
                }
            });
            
            if (trackingData.length === 0) {
                alert('Please select at least one result to post.');
                return;
            }
            
            if (!startDate || !endDate) {
                alert('Please provide both Analysis Start and End dates.');
                return;
            }
            
            const btn = $('#confirm-post-results');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Posting...');
            
            $.ajax({
                url: '/method-sequences/post-results',
                method: 'POST',
                data: {
                    batch_id: batchId,
                    start_analysis_date: startDate,
                    end_analysis_date: endDate,
                    tracking_data: trackingData,
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        $('#post-results-modal').modal('hide');
                        alert('Results posted successfully!');
                        location.reload(); // Refresh to show updated results
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error posting results';
                    alert(message);
                    btn.prop('disabled', false).html(originalText);
                }
            });
        },

        validatePostButton: function() {
            const checkedCount = $('.pt-select-cb:checked').length;
            const startDate = $('#start-analysis-date').val();
            const endDate = $('#end-analysis-date').val();
            const postBtn = $('#confirm-post-results');
            
            const isValid = checkedCount > 0 && startDate && endDate;
            postBtn.prop('disabled', !isValid);
        },
        
        refreshActiveTab: function() {
            if (this.activeStageHeaderId) {
                this.loadRuns(this.activeStageHeaderId);
            }
        },
        
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        },
        
        formatDateTime: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        },

        getEndedByName: function(track) {
            if (!track) {
                return 'Not ended';
            }
            if (track.endedBy && track.endedBy.name) {
                return track.endedBy.name;
            }
            if (track.ended_by && track.ended_by.name) {
                return track.ended_by.name;
            }
            return 'Not ended';
        },

        refreshStep1BasicInfo: function(trackId, track) {
            if (!track) {
                return;
            }
            if (track.started_at) {
                $(`#start-date-${trackId}`).val(new Date(track.started_at).toISOString().slice(0, 16));
            }
            if (track.ended_at) {
                $(`#end-date-${trackId}`).val(new Date(track.ended_at).toISOString().slice(0, 16));
            }
            const $endedBy = $(`#ended-by-name-${trackId}`);
            if ($endedBy.length) {
                $endedBy.val(this.getEndedByName(track));
            }
        },

        applyStageLock: function(trackId, isLocked, lockReason) {
            const $form = $(`#stepper-form-${trackId}`);
            if (!$form.length) {
                return;
            }
            const reason = lockReason || (isLocked ? ($form.attr('data-lock-reason') || 'completed') : '');
            $form.toggleClass('stage-locked', isLocked);
            $form.attr('data-stage-locked', isLocked ? '1' : '0');
            $form.attr('data-lock-reason', reason || '');
            $form.find('.stage-locked-banner').toggleClass('d-none', !isLocked);
            $form.find('.stage-locked-banner-text').text(this.stageLockBannerText(reason));
            $form.find('button.save-stage-details-btn, button.save-results-btn, button.add-solution-item, button.next-step-btn[data-next="6"]').toggle(!isLocked);
            $form.find('button.delete-solution-btn, button.remove-equipment-btn, button.remove-solution-item').toggle(!isLocked);

            $form.find('input:not([readonly]), select, textarea').each(function() {
                const $el = $(this);
                if ($el.closest('.step-pane').attr('id') === `step-pane-1-${trackId}`) {
                    return;
                }
                $el.prop('disabled', isLocked);
            });

            $form.find('select').each(function() {
                const $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.prop('disabled', isLocked).trigger('change.select2');
                }
            });
        },

        isTrackLocked: function(track) {
            return this.getTrackLockReason(track) !== null;
        },

        getTrackLockReason: function(track) {
            if (!this.canEditActiveStageHeader()) {
                return 'view_only';
            }

            if (track && (track.status === 'completed' || !!track.ended_at)) {
                return 'completed';
            }

            return null;
        },

        stageLockBannerText: function(reason) {
            if (reason === 'view_only') {
                return 'View only — you can only edit stages for your assigned lab section(s) or tests assigned to you.';
            }

            return 'This stage has been completed and is read-only.';
        },
        
        // New functions for the inline form
        loadStageFormData: function(trackId) {
            const self = this;
            
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/edit-data`,
                method: 'GET',
                success: function(response) {
                    if (response.success !== false) {
                        self.populateStageForm(trackId, response);
                        if (response.track && response.track.test_stage && response.track.test_stage.is_result_stage) {
                            self.loadResultsForStep6(trackId);
                        }
                    }
                },
                error: function() {
                    console.error('Error loading stage form data');
                }
            });
        },
        
        populateStageForm: function(trackId, data) {
            const stage = data.track ? data.track.test_stage : {};
            console.log('DEBUG: populateStageForm stage.controls_required:', stage ? stage.controls_required : 'null');
            const isResultStage = !!(stage && stage.is_result_stage);

            console.groupCollapsed('MethodSequences.populateStageForm payload');
            console.log('trackId:', trackId);
            console.log('equipment:', data.equipment);
            console.log('media:', data.media);
            console.log('controls:', data.controls);
            console.log('diluents:', data.diluents);
            console.log('current items:', data.current_items);
            console.groupEnd();

            const normalizeItems = function(list) {
                if (!list) return [];
                if (Array.isArray(list)) return list;
                if (typeof list === 'object') {
                    if (Array.isArray(list.data)) {
                        return list.data;
                    }
                    if (typeof list.id !== 'undefined' && typeof list.name !== 'undefined') {
                        return [list];
                    }
                    return Object.values(list).filter(function(value) {
                        return value && typeof value === 'object';
                    });
                }
                return [];
            };

            const buildIdNameMap = function(list) {
                const map = {};
                const items = normalizeItems(list);
                items.forEach(function(item) {
                    if (!item || typeof item.id === 'undefined') {
                        return;
                    }
                    let name = item.name || '';
                    // Simple conversion to string
                    if (typeof name === 'object' && name !== null) {
                        name = String(name);
                    } else {
                        name = String(name);
                    }
                    map[String(item.id)] = name;
                });
                return map;
            };

            const equipmentMap = buildIdNameMap(data.equipment);
            const mediaMap = buildIdNameMap(data.media);
            const controlsMap = buildIdNameMap(data.controls);
            const diluentsMap = buildIdNameMap(data.diluents);

            const initPicker = function($select, placeholder) {
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({ placeholder: placeholder, allowClear: true, width: '100%' });
            };

            const populatePicker = function($select, items) {
                $select.empty().append('<option></option>');
                const options = normalizeItems(items);
                console.log('populatePicker data for', $select.attr('id'), options);
                options.forEach(function(item) {
                    if (!item) {
                        return;
                    }
                    // Ensure name is safe string - convert objects to readable form
                    let safeName = item.name || '';
                    if (typeof safeName === 'object' && safeName !== null) {
                        safeName = String(safeName);
                    } else {
                        safeName = String(safeName);
                    }
                    
                    const option = $('<option></option>')
                        .attr('value', item.id)
                        .text(safeName);
                    
                    // Add equipment-specific data attributes
                    if (item.equipment_number) {
                        option.attr('data-equipment-no', item.equipment_number);
                    }
                    if (item.last_calibration_date) {
                        option.attr('data-calibration-date', item.last_calibration_date);
                    }
                    
                    // Add media-specific data attributes
                    if (item.latest_prep_date) {
                        option.attr('data-latest-prep-date', item.latest_prep_date);
                    }
                    if (item.latest_prep_number) {
                        option.attr('data-latest-prep-no', item.latest_prep_number);
                    }
                    
                    // Add controls-specific data attributes
                    if (item.batch_number) {
                        option.attr('data-batch-number', item.batch_number);
                    }
                    if (item.expiry_date) {
                        option.attr('data-expiry-date', item.expiry_date);
                    }
                    
                    $select.append(option);
                });
            };

            const renderRow = function(trackIdLocal, type, item, name, rNature) {
                const id = item && item.id ? String(item.id) : '';
                let safeName = name || 'N/A';
                // Simple string conversion - don't overthink it
                if (typeof safeName === 'object' && safeName !== null) {
                    safeName = String(safeName);
                } else if (!safeName) {
                    safeName = 'N/A';
                }
                
                // Get result nature from the test stage configuration
                let resultNature = rNature || 'None';
                if (type === 'media' && (!rNature || rNature === 'None')) {
                    if (stage && stage.media_required) {
                        let mediaConfig = stage.media_required;
                        if (typeof mediaConfig === 'string') {
                            try { mediaConfig = JSON.parse(mediaConfig); } catch(e) {}
                        }
                        if (Array.isArray(mediaConfig)) {
                            const config = mediaConfig.find(m => String(m.id) === id);
                            if (config && config.result_nature) {
                                resultNature = config.config_result_nature || config.result_nature;
                            }
                        }
                    }
                }
                
                if (type === 'equipment') {
                    const listContainer = $(`#equipment-list-${trackIdLocal}`);
                    if (listContainer.find(`.equipment-card[data-id="${id}"]`).length > 0) return;

                    const serial = item.serial || '';
                    const calibration = item.calibration || '';

                    listContainer.append(`
                        <div class="equipment-card border p-3 mb-3 bg-white shadow-sm position-relative" data-id="${id}" style="border-left: 4px solid #007bff !important; border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-primary font-weight-bold small mb-1">ITEM #${listContainer.children().length + 1}</div>
                                    <h5 class="mb-1 font-weight-bold equipment-name" title="${safeName}">${safeName}</h5>
                                    <div class="equipment-meta">
                                        <div class="equipment-meta-block">
                                            <div class="text-muted small">Equipment Number</div>
                                            <div class="font-weight-bold">${serial || 'N/A'}</div>
                                            <input type="hidden" class="equipment-serial-input" value="${serial}">
                                        </div>
                                        <div class="equipment-meta-block">
                                            <div class="text-muted small">Last Calibrated</div>
                                            <div class="font-weight-bold">${calibration || 'N/A'}</div>
                                            <input type="hidden" class="equipment-calibration-input" value="${calibration}">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-success small d-flex align-items-center">
                                        <i class="mdi mdi-circle mr-1" style="font-size: 8px;"></i> Verification Active
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-solution-item p-0" data-track-id="${trackIdLocal}" data-type="${type}" data-id="${id}">
                                    <i class="mdi mdi-delete-outline h4 mb-0"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    return;
                }

                if (type === 'controls') {
                    const listContainer = $(`#controls-list-${trackIdLocal}`);
                    if (listContainer.find(`.control-card-premium[data-id="${id}"]`).length > 0) return;

                    const lot = item.preparation || '';
                    const expiry = item.expiry || '';
                    const cardIndex = listContainer.find('.control-card-premium').length + 1;

                    // Insert before the "Add more" box
                    const addMoreBox = listContainer.find('.add-more-dashed-box').closest('.col-md-4');
                    $(`
                        <div class="col-md-4 mb-3 control-card-wrapper" data-id="${id}">
                            <div class="control-card-premium h-100" data-id="${id}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="control-type-label">Control #${cardIndex}</div>
                                    <button type="button" class="btn btn-link text-danger remove-solution-item p-0" data-track-id="${trackIdLocal}" data-type="${type}" data-id="${id}">
                                        <i class="mdi mdi-delete-outline h5 mb-0"></i>
                                    </button>
                                </div>
                                <div class="control-name-title" title="${safeName}">${safeName}</div>
                                
                                <div class="control-meta-grid">
                                    <div>
                                        <div class="meta-label">Lot Number</div>
                                        <div class="meta-value">${lot || 'N/A'}</div>
                                        <input type="hidden" class="solution-preparation" value="${lot}">
                                    </div>
                                    <div class="text-right">
                                        <div class="meta-label">Expiration</div>
                                        <div class="meta-value">${expiry || 'N/A'}</div>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="verified-badge">
                                        <i class="mdi mdi-check-decagram"></i> Verified
                                    </div>
                                    <div class="ref-number text-uppercase font-weight-bold" style="font-size: 0.75rem; color: #64748b;">
                                        Result Nature: ${resultNature === 'quantitative' ? 'Quantitative' : (resultNature === 'qualitative' ? 'Qualitative' : 'No Result')}
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).insertBefore(addMoreBox);
                    return;
                }

                if (type === 'media') {
                    const gridContainer = $(`#media-cards-grid-${trackIdLocal}`);
                    if (gridContainer.find(`.media-card-wrapper[data-id="${id}"]`).length > 0) return;

                    const preparation = item && item.preparation ? String(item.preparation) : '';
                    const technician = item && (item.preparation_number || item.remark) ? String(item.preparation_number || item.remark) : 'N/A';
                    const expiry = item && item.expiry ? String(item.expiry) : '';
                    
                    gridContainer.find('.media-placeholder-wrapper').remove();

                    gridContainer.append(`
                        <div class="col-md-6 mb-3 media-card-wrapper" data-id="${id}">
                            <div class="media-card-premium d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="status-badge-verified">
                                        <i class="mdi mdi-check-circle mr-1"></i> Verified
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical h5 mb-0"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 8px;">
                                            <a class="dropdown-item py-2 edit-media-entry" href="#" data-track-id="${trackIdLocal}" data-id="${id}">
                                                <i class="mdi mdi-pencil-outline mr-2 text-primary"></i> Edit Entry
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item py-2 text-danger remove-solution-item" href="#" data-track-id="${trackIdLocal}" data-type="media" data-id="${id}">
                                                <i class="mdi mdi-delete-outline mr-2"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="media-name-title mb-auto" title="${safeName}">${safeName}</div>
                                
                                <div class="media-meta-grid mt-3">
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. Date:</span>
                                        <span class="media-meta-value">${preparation || 'N/A'}</span>
                                        <input type="hidden" class="solution-preparation" value="${preparation}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. No:</span>
                                        <span class="media-meta-value">${technician}</span>
                                        <input type="hidden" class="solution-preparation-number" value="${technician}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Expiry:</span>
                                        <span class="media-meta-value">${expiry || 'N/A'}</span>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Result Nature:</span>
                                        <span class="media-meta-value text-capitalize">${resultNature}</span>
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                                <div class="media-card-actions mt-auto pt-3 border-top">
                                    <div class="verified-badge-footer">
                                        <i class="mdi mdi-shield-check-outline mr-1"></i> Verified Entry
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Re-add placeholder if needed (to keep the 2nd slot UI look if only 1 item)
                    if (gridContainer.find('.media-card-wrapper').length === 1) {
                        gridContainer.append(`
                            <div class="col-md-6 mb-3 media-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-water-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional media data</div>
                                    <div class="placeholder-text-sub">Add media using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    return;
                }

                if (type === 'diluents') {
                    const gridContainer = $(`#diluent-cards-grid-${trackIdLocal}`);
                    if (gridContainer.find(`.diluent-card-wrapper[data-id="${id}"]`).length > 0) return;

                    const preparation = item && item.preparation_date ? String(item.preparation_date) : '';
                    const prepNo = item && item.preparation_number ? String(item.preparation_number) : 'N/A';
                    const expiry = item && item.expiry ? String(item.expiry) : '';
                    
                    gridContainer.find('.diluent-placeholder-wrapper').remove();

                    gridContainer.append(`
                        <div class="col-md-6 mb-3 diluent-card-wrapper" data-id="${id}">
                            <div class="media-card-premium d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="status-badge-verified">
                                        <i class="mdi mdi-check-circle mr-1"></i> Verified
                                    </div>
                                    <button type="button" class="btn btn-link text-danger remove-solution-item p-0" data-track-id="${trackIdLocal}" data-type="diluents" data-id="${id}">
                                        <i class="mdi mdi-delete-outline h5 mb-0"></i>
                                    </button>
                                </div>
                                <div class="media-name-title mb-auto" title="${safeName}">${safeName}</div>
                                
                                <div class="media-meta-grid mt-3">
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. Date:</span>
                                        <span class="media-meta-value">${preparation || 'N/A'}</span>
                                        <input type="hidden" class="solution-preparation-date" value="${preparation}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Prep. No:</span>
                                        <span class="media-meta-value">${prepNo}</span>
                                        <input type="hidden" class="solution-preparation-number" value="${prepNo}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Expiry:</span>
                                        <span class="media-meta-value">${expiry || 'N/A'}</span>
                                        <input type="hidden" class="solution-expiry" value="${expiry}">
                                    </div>
                                    <span class="media-meta-divider">|</span>
                                    <div class="media-meta-item">
                                        <span class="media-meta-label">Result Nature:</span>
                                        <span class="media-meta-value text-capitalize">${resultNature}</span>
                                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                                    </div>
                                </div>
                                <div class="media-card-actions mt-auto pt-3 border-top">
                                    <div class="verified-badge-footer">
                                        <i class="mdi mdi-shield-check-outline mr-1"></i> Verified Entry
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Re-add placeholder if needed (to keep the 2nd slot UI look if only 1 item)
                    if (gridContainer.find('.diluent-card-wrapper').length === 1) {
                        gridContainer.append(`
                            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                                <div class="placeholder-card-dashed">
                                    <div class="placeholder-icon-container">
                                        <i class="mdi mdi-beaker-outline"></i>
                                    </div>
                                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                                </div>
                            </div>
                        `);
                    }
                    return;
                }

                const table = $(`#${type}-table-${trackIdLocal} tbody`);
                if (table.find(`tr[data-id="${id}"]`).length > 0) return;

                const preparation = item && item.preparation ? String(item.preparation) : '';
                const result = item && item.result ? String(item.result) : '';
                const remark = item && item.remark ? String(item.remark) : '';

                table.append(`
                    <tr data-id="${id}">
                        <td>${safeName}</td>
                        <td><input type="text" class="form-control form-control-sm solution-preparation" value="${preparation}"></td>
                        ${isResultStage ? `<td><input type="text" class="form-control form-control-sm solution-result" value="${result}"></td>` : ''}
                        ${isResultStage ? `<td><input type="text" class="form-control form-control-sm solution-remark" value="${remark}"></td>` : ''}
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-solution-item" data-track-id="${trackIdLocal}" data-type="${type}" data-id="${id}">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </td>
                    </tr>
                `);
            };

            const equipmentPicker = $(`#equipment-picker-${trackId}`);
            const mediaPicker = $(`#media-picker-${trackId}`);
            const controlsPicker = $(`#controls-picker-${trackId}`);
            const diluentsPicker = $(`#diluents-picker-${trackId}`);

            populatePicker(equipmentPicker, data.equipment);
            populatePicker(mediaPicker, data.media);
            populatePicker(controlsPicker, data.controls);
            populatePicker(diluentsPicker, data.diluents);

            initPicker(equipmentPicker, 'Select equipment');
            initPicker(mediaPicker, 'Select uninoculated media');
            initPicker(controlsPicker, 'Select control');
            initPicker(diluentsPicker, 'Select uninoculated diluent');

            // Auto-populate equipment fields when equipment is selected
            equipmentPicker.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const equipmentNo = selectedOption.attr('data-equipment-no') || '';
                const calibrationDate = selectedOption.attr('data-calibration-date') || '';
                
                $(`#equipment-serial-${trackId}`).val(equipmentNo);
                $(`#equipment-calibration-${trackId}`).val(calibrationDate);
            });

            // Auto-populate diluent fields when diluent is selected
            diluentsPicker.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const selectedId = $(this).val();
                const latestPrepDate = selectedOption.attr('data-latest-prep-date') || '';
                const latestPrepNo = selectedOption.attr('data-latest-prep-no') || '';
                
                console.log('Diluent Selected:', {
                    id: selectedId,
                    name: selectedOption.text(),
                    latestPrepDate: latestPrepDate,
                    latestPrepNo: latestPrepNo
                });

                $(`#diluent-prep-date-${trackId}`).val(latestPrepDate);
                $(`#diluent-prep-no-${trackId}`).val(latestPrepNo);
                
                // Get Result Nature from test stage config if available
                const form = $(`#stepper-form-${trackId}`);
                const diluentsConfig = form.data('diluents-config') || [];
                let resultNature = '';
                
                if (Array.isArray(diluentsConfig)) {
                    const diluentItem = diluentsConfig.find(d => String(d.id) === String(selectedId));
                    if (diluentItem && diluentItem.result_nature) {
                        resultNature = diluentItem.result_nature;
                    }
                }
                
                $(`#diluent-result-nature-${trackId}`).val(resultNature || 'no_result');
            });

            // Auto-populate controls fields when control is selected
            controlsPicker.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const selectedId = $(this).val();
                const batchNumber = selectedOption.attr('data-batch-number') || '';
                const expiryDate = selectedOption.attr('data-expiry-date') || '';
                
                // Get Result Nature from controls config
                const form = $(`#stepper-form-${trackId}`);
                const controlsConfig = form.data('controls-config') || [];
                let resultNature = '';
                
                if (Array.isArray(controlsConfig)) {
                    const controlItem = controlsConfig.find(c => String(c.id) === String(selectedId));
                    if (controlItem && controlItem.result_nature) {
                        resultNature = controlItem.result_nature;
                    }
                }
                
                $(`#control-lot-${trackId}`).val(batchNumber);
                $(`#control-expiry-${trackId}`).val(expiryDate);
                $(`#control-result-nature-${trackId}`).val(resultNature);
            });

            // Auto-populate media fields when media is selected
            mediaPicker.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const selectedId = $(this).val();
                const latestPrepDate = selectedOption.attr('data-latest-prep-date') || '';
                const latestPrepNo = selectedOption.attr('data-latest-prep-no') || '';
                const expiryDate = selectedOption.attr('data-expiry-date') || '';
                
                console.log('Media Selected:', {
                    id: selectedId,
                    name: selectedOption.text(),
                    latestPrepDate: latestPrepDate,
                    latestPrepNo: latestPrepNo
                });

                $(`#media-prep-date-${trackId}`).val(latestPrepDate);
                $(`#media-prep-no-${trackId}`).val(latestPrepNo);
                $(`#media-expiry-${trackId}`).val(expiryDate);
                
                // Get Result Nature from media config
                const form = $(`#stepper-form-${trackId}`);
                const mediaConfig = form.data('media-config') || [];
                let resultNature = '';
                
                if (Array.isArray(mediaConfig)) {
                    const mediaItem = mediaConfig.find(m => String(m.id) === String(selectedId));
                    if (mediaItem && mediaItem.result_nature) {
                        resultNature = mediaItem.result_nature;
                    }
                }
                
                $(`#media-result-nature-${trackId}`).val(resultNature);
            });

            $(`#equipment-list-${trackId}`).empty();
            
            const mediaGrid = $(`#media-cards-grid-${trackId}`);
            mediaGrid.empty().append(`
                <div class="col-md-6 mb-3 media-placeholder-wrapper">
                    <div class="placeholder-card-dashed">
                        <div class="placeholder-icon-container">
                            <i class="mdi mdi-water-outline"></i>
                        </div>
                        <div class="placeholder-text-main">Waiting for additional media data</div>
                        <div class="placeholder-text-sub">Add media using the form below</div>
                    </div>
                </div>
            `);
            
            // For controls, we keep the "Add more" box but empty other items
            const controlsList = $(`#controls-list-${trackId}`);
            controlsList.find('.control-card-wrapper').remove();
            
            $(`#diluents-table-${trackId} tbody`).empty();

            const currentItems = (data.current_items || {});
            const current = (data.current || {});

            (currentItems.equipment || []).forEach(function(item) {
                renderRow(trackId, 'equipment', item, item.name || equipmentMap[String(item.id)] || String(item.id));
            });
            (currentItems.media || []).forEach(function(item) {
                console.log('DEBUG: media current item:', item);
                let rNature = item.result_nature || 'none';
                
                // If not available in item, try to get from config
                if (!item.result_nature) {
                    let mediaConfigList = (stage && stage.media_required) ? stage.media_required : [];
                    if (typeof mediaConfigList === 'string') {
                        try { mediaConfigList = JSON.parse(mediaConfigList); } catch(e) {}
                        if (typeof mediaConfigList === 'string') {
                            try { mediaConfigList = JSON.parse(mediaConfigList); } catch(e) {}
                        }
                    }
                    const mediaConfig = (Array.isArray(mediaConfigList) ? mediaConfigList : []).find(m => String(m.id) === String(item.id));
                    rNature = mediaConfig ? (mediaConfig.result_nature || 'none') : 'none';
                }
                
                const mediaName = (item.name ? String(item.name) : null) || mediaMap[String(item.id)] || String(item.id);
                renderRow(trackId, 'media', item, mediaName, rNature);
            });
            (currentItems.controls || []).forEach(function(item) {
                const cid = String(item.id);
                let rNature = item.result_nature || 'none';
                
                // If not available in item, try to get from config
                if (!item.result_nature) {
                    let configList = (stage && stage.controls_required) ? stage.controls_required : [];
                    if (typeof configList === 'string') {
                        try { configList = JSON.parse(configList); } catch(e) {}
                        if (typeof configList === 'string') {
                            try { configList = JSON.parse(configList); } catch(e) {}
                        }
                    }
                    const config = (Array.isArray(configList) ? configList : []).find(c => String(c.id) === cid);
                    rNature = config ? (config.result_nature || 'none') : 'none';
                }
                
                const controlName = (item.name ? String(item.name) : null) || controlsMap[cid] || cid;
                renderRow(trackId, 'controls', item, controlName, rNature);
            });
            (currentItems.diluents || []).forEach(function(item) {
                renderRow(trackId, 'diluents', item, diluentsMap[String(item.id)] || String(item.id), item.result_nature);
            });

            if ((!currentItems.equipment || currentItems.equipment.length === 0) && current.equipment) {
                (current.equipment || []).forEach(function(id) {
                    renderRow(trackId, 'equipment', { id: id }, equipmentMap[String(id)] || String(id));
                });
            }
            if ((!currentItems.media || currentItems.media.length === 0) && current.media) {
                (current.media || []).forEach(function(id) {
                    const mid = String(id);
                    const mediaName = mediaMap[mid] || mid;
                    let rNature = 'none';
                    
                    // Try to get result_nature from config
                    let mediaConfigList = (stage && stage.media_required) ? stage.media_required : [];
                    if (typeof mediaConfigList === 'string') {
                        try { mediaConfigList = JSON.parse(mediaConfigList); } catch(e) {}
                        if (typeof mediaConfigList === 'string') {
                            try { mediaConfigList = JSON.parse(mediaConfigList); } catch(e) {}
                        }
                    }
                    const mediaConfig = (Array.isArray(mediaConfigList) ? mediaConfigList : []).find(m => String(m.id) === String(id));
                    rNature = mediaConfig ? (mediaConfig.result_nature || 'none') : 'none';
                    
                    // Look up media metadata from data.media array
                    let mediaMetadata = {};
                    if (data && data.media && Array.isArray(data.media)) {
                        const mediaData = data.media.find(m => String(m.id) === mid);
                        if (mediaData) {
                            mediaMetadata = {
                                preparation: mediaData.batch_prepared_date || '',
                                preparation_number: mediaData.latest_prep_number || mediaData.current_batch_number || mediaData.batch_number || '',
                                expiry: mediaData.expiry_date || mediaData.batch_expiry_date || ''
                            };
                        }
                    }
                    
                    const itemObj = {
                        id: id,
                        preparation: mediaMetadata.preparation || (data.notes ? data.notes.media_preparation_used : ''),
                        preparation_number: mediaMetadata.preparation_number,
                        expiry: mediaMetadata.expiry
                    };
                    
                    renderRow(trackId, 'media', itemObj, mediaName, rNature);
                });
            }
            if ((!currentItems.controls || currentItems.controls.length === 0) && current.controls) {
                (current.controls || []).forEach(function(id) {
                    const cid = String(id);
                    const config = (stage.controls_required || []).find(c => String(typeof c === 'object' ? c.id : c) === cid);
                    const nature = config ? (config.result_nature || 'none') : 'none';
                    const controlName = controlsMap[cid] || cid;
                    
                    // Look up controls metadata from data.controls array
                    let controlMetadata = {};
                    if (data && data.controls && Array.isArray(data.controls)) {
                        const controlData = data.controls.find(c => String(c.id) === cid);
                        if (controlData) {
                            controlMetadata = {
                                preparation: controlData.current_batch_number || controlData.batch_number || '',
                                expiry: controlData.batch_expiry_date || controlData.expiry_date || ''
                            };
                        }
                    }
                    
                    const itemObj = {
                        id: id,
                        preparation: controlMetadata.preparation || (data.notes ? data.notes.control_used : ''),
                        expiry: controlMetadata.expiry
                    };
                    
                    renderRow(trackId, 'controls', itemObj, controlName, nature);
                });
            }
            if ((!currentItems.diluents || currentItems.diluents.length === 0) && current.diluents) {
                (current.diluents || []).forEach(function(id) {
                    const diluentName = diluentsMap[String(id)] || String(id);
                    let rNature = 'none';
                    
                    // Try to get result_nature from config
                    let diluentConfigList = (stage && stage.diluents_required) ? stage.diluents_required : [];
                    if (typeof diluentConfigList === 'string') {
                        try { diluentConfigList = JSON.parse(diluentConfigList); } catch(e) {}
                        if (typeof diluentConfigList === 'string') {
                            try { diluentConfigList = JSON.parse(diluentConfigList); } catch(e) {}
                        }
                    }
                    const diluentConfig = (Array.isArray(diluentConfigList) ? diluentConfigList : []).find(d => String(d.id) === String(id));
                    rNature = diluentConfig ? (diluentConfig.result_nature || 'none') : 'none';
                    
                    // Look up diluent metadata from data.diluents array
                    let diluentMetadata = {};
                    const did = String(id);
                    if (data && data.diluents && Array.isArray(data.diluents)) {
                        const diluentData = data.diluents.find(d => String(d.id) === did);
                        if (diluentData) {
                            diluentMetadata = {
                                preparation: diluentData.batch_prepared_date || diluentData.latest_prep_date || '',
                                preparation_number: diluentData.current_batch_number || diluentData.latest_prep_number || diluentData.batch_number || '',
                                expiry: diluentData.batch_expiry_date || ''
                            };
                        }
                    }
                    
                    const itemObj = {
                        id: id,
                        preparation: diluentMetadata.preparation || (data.notes ? data.notes.diluent_used : ''),
                        preparation_number: diluentMetadata.preparation_number,
                        expiry: diluentMetadata.expiry
                    };
                    
                    renderRow(trackId, 'diluents', itemObj, diluentName, rNature);
                });
            }

            if (data.track) {
                this.refreshStep1BasicInfo(trackId, data.track);
                this.applyStageLock(trackId, this.isTrackLocked(data.track), this.getTrackLockReason(data.track));
            }
        },

        goToStep: function(trackId, step) {
            const form = $(`#stepper-form-${trackId}`);
            form.find('.step-item').removeClass('active');
            form.find(`.step-item[data-step="${step}"]`).addClass('active');
            
            form.find('.step-pane').removeClass('active');
            form.find(`#step-pane-${step}-${trackId}`).addClass('active');
            
            // Load results when navigating to step 6
            if (step === 6) {
                this.loadResultsForStep6(trackId);
            }
        },
        
        loadResultsForStep6: function(trackId) {
            const self = this;
            
            // Load solution results (media + controls)
            const solutionContainer = $(`#solution-results-container-${trackId}`);
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/solution-results`,
                method: 'GET',
                success: function(response) {
                    solutionContainer.html(response);
                },
                error: function() {
                    solutionContainer.html('<div class="alert alert-warning"><i class="mdi mdi-alert-circle"></i> Error loading solution results</div>');
                }
            });
            
            // Load sample results
            const sampleContainer = $(`#sample-results-container-${trackId}`);
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/sample-results`,
                method: 'GET',
                success: function(response) {
                    sampleContainer.html(response);
                    self.applyStep6ViewOnlyState(trackId);
                },
                error: function() {
                    sampleContainer.html('<div class="alert alert-warning"><i class="mdi mdi-alert-circle"></i> Error loading sample results</div>');
                }
            });
        },

        getStep6StandardLimit: function($row) {
            const hiddenValue = $row.find('.step6-standard-limit-value').val();
            if (hiddenValue !== undefined && hiddenValue !== null && String(hiddenValue).trim() !== '') {
                return String(hiddenValue).trim();
            }

            const textValue = $row.find('.standard-limit-text').text().trim();
            if (textValue && textValue !== '-') {
                return textValue;
            }

            const inputValue = $row.find('.sample-result-input').data('standard-limit');
            return inputValue || null;
        },

        setStep6StandardLimit: function($row, standardLimit) {
            const value = standardLimit || '';
            $row.find('.standard-limit-text').text(value || '-');
            $row.find('.step6-standard-limit-value').val(value);
            $row.find('.sample-result-input').data('standard-limit', value).attr('data-standard-limit', value);
        },

        applyStep6ViewOnlyState: function(trackId) {
            if (this.canEditActiveStageHeader()) {
                return;
            }

            const $container = $(`#sample-results-container-${trackId}`);
            $container.find('.step6-edit-standard-btn').remove();
            $container.find('.sample-result-input, .reporting-symbol-select, .sample-remark-dropdown').prop('disabled', true);
        },

        initEditStandardLimitModal: function() {
            const self = this;

            if (this.editStandardModalInitialized) {
                return;
            }
            this.editStandardModalInitialized = true;

            const syncEditStandardModalSections = function() {
                const valueType = $('#edit-standard-form input[name="value_type"]:checked').val() || 'use_value';
                const isRange = valueType === 'range';
                $('#esl-range-section').toggle(isRange);
                $('#esl-use-value-section').toggle(!isRange);

                const selectedCode = $('#esl_standard_value_id option:selected').data('code') || '';
                const showIsValueFields = !isRange && selectedCode === 'IsValue';
                $('#esl-is-value-fields').toggle(showIsValueFields);

                if (!showIsValueFields) {
                    $('#esl_matrix_operator').val('');
                    $('#esl_matrix_value').val('');
                }
            };

            const resetEditStandardFormFields = function() {
                $('#esl_value_type_use_value').prop('checked', true);
                $('#esl_range_low').val('');
                $('#esl_range_high').val('');
                $('#esl_standard_value_id').val('');
                $('#esl_matrix_operator').val('');
                $('#esl_matrix_value').val('');
                syncEditStandardModalSections();
            };

            const populateEditStandardForm = function(existingText) {
                resetEditStandardFormFields();

                if (!existingText || existingText === 'No limit set' || existingText === '-') {
                    return;
                }

                const typedMatch = existingText.match(/^(min|max)\s+(\d+(?:\.\d+)?)$/i);
                if (typedMatch) {
                    $('#esl_value_type_use_value').prop('checked', true);
                    const isValueOption = $('#esl_standard_value_id option').filter(function () {
                        return String($(this).data('code')) === 'IsValue';
                    }).first();
                    if (isValueOption.length) {
                        $('#esl_standard_value_id').val(isValueOption.val());
                    }
                    $('#esl_matrix_operator').val(typedMatch[1].toLowerCase());
                    $('#esl_matrix_value').val(typedMatch[2]);
                    syncEditStandardModalSections();
                    return;
                }

                const reversedMatch = existingText.match(/^(\d+(?:\.\d+)?)\s+(min|max)$/i);
                if (reversedMatch) {
                    $('#esl_value_type_use_value').prop('checked', true);
                    const isValueOptionReversed = $('#esl_standard_value_id option').filter(function () {
                        return String($(this).data('code')) === 'IsValue';
                    }).first();
                    if (isValueOptionReversed.length) {
                        $('#esl_standard_value_id').val(isValueOptionReversed.val());
                    }
                    $('#esl_matrix_operator').val(reversedMatch[2].toLowerCase());
                    $('#esl_matrix_value').val(reversedMatch[1]);
                    syncEditStandardModalSections();
                    return;
                }

                const compareMatch = existingText.match(/^([<>])\s*(\d+(?:\.\d+)?)$/);
                if (compareMatch) {
                    $('#esl_value_type_use_value').prop('checked', true);
                    const isValueOptionCompare = $('#esl_standard_value_id option').filter(function () {
                        return String($(this).data('code')) === 'IsValue';
                    }).first();
                    if (isValueOptionCompare.length) {
                        $('#esl_standard_value_id').val(isValueOptionCompare.val());
                    }
                    $('#esl_matrix_operator').val(compareMatch[1] === '<' ? 'less_than' : 'greater_than');
                    $('#esl_matrix_value').val(compareMatch[2]);
                    syncEditStandardModalSections();
                    return;
                }

                if (/^\d+(?:\.\d+)?\s*-\s*\d+(?:\.\d+)?$/i.test(existingText)) {
                    const parts = existingText.split(/\s*-\s*/);
                    $('#esl_value_type_range').prop('checked', true);
                    $('#esl_range_low').val(parts[0] || '');
                    $('#esl_range_high').val(parts[1] || '');
                    syncEditStandardModalSections();
                    return;
                }

                const needle = existingText.toLowerCase();
                const codeOption = $('#esl_standard_value_id option').filter(function () {
                    const code = String($(this).data('code') || '').toLowerCase();
                    const name = String($(this).text() || '').toLowerCase();
                    return code === needle
                        || name === needle
                        || name.indexOf('(' + needle + ')') !== -1
                        || name.indexOf(needle) !== -1;
                }).first();

                if (codeOption.length) {
                    $('#esl_value_type_use_value').prop('checked', true);
                    $('#esl_standard_value_id').val(codeOption.val()).trigger('change');
                    syncEditStandardModalSections();
                }
            };

            const applyEditStandardSettings = function(data) {
                if (!data) {
                    return;
                }

                const valueType = data.value_type || 'use_value';
                const hasSelection = valueType === 'range'
                    ? !!(data.range_low || data.range_high)
                    : !!(data.standard_value_id);

                // Do not wipe a text-matched preselection with an empty API payload.
                if (!hasSelection) {
                    return;
                }

                if (valueType === 'range') {
                    $('#esl_value_type_range').prop('checked', true);
                    $('#esl_range_low').val(data.range_low || '');
                    $('#esl_range_high').val(data.range_high || '');
                } else {
                    $('#esl_value_type_use_value').prop('checked', true);
                    // trigger('change') keeps Select2 in sync after async load
                    $('#esl_standard_value_id').val(String(data.standard_value_id || '')).trigger('change');
                    $('#esl_matrix_operator').val(data.matrix_operator || '').trigger('change');
                    $('#esl_matrix_value').val(data.matrix_value || '');
                }

                syncEditStandardModalSections();
            };

            $(document).on('change.methodSequences', '#edit-standard-form .esl-value-type, #esl_standard_value_id', function () {
                syncEditStandardModalSections();
            });

            $(document).on('click.methodSequences', '.step6-edit-standard-btn', function(event) {
                event.preventDefault();
                event.stopPropagation();

                if (!self.canEditActiveStageHeader()) {
                    toastr.error('You can only edit standard limits for your assigned lab section(s).');
                    return false;
                }

                const $btn = $(this);
                const $row = $btn.closest('tr.sample-result-row');
                // Prefer .attr() so UUID data-* values are not coerced by jQuery .data()
                const resultId = $btn.attr('data-result-id') || '';
                const sampleCode = $btn.attr('data-sample-code') || '';
                const analyte = $btn.attr('data-analyte') || '';
                const trackId = $btn.attr('data-track-id') || '';
                const standardValueId = $btn.attr('data-standard-value-id') || '';
                const existingText = ($btn.attr('data-standard-limit-text')
                    || $row.find('.standard-limit-text').text()
                    || '').trim();

                const $form = $('#edit-standard-form');
                if ($form.length && $form[0]) {
                    $form[0].reset();
                }
                $('#standard_result_id').val(resultId || '');
                $('#standard_sample_code').val(sampleCode || '');
                $('#standard_analyte').val(analyte || '');
                $('#standard_step6_track_id').val(trackId || '');
                resetEditStandardFormFields();

                // Prefer explicit standard_value_id from the row (most reliable for ABSENT etc.)
                if (standardValueId && $(`#esl_standard_value_id option[value="${standardValueId}"]`).length) {
                    $('#esl_value_type_use_value').prop('checked', true);
                    $('#esl_standard_value_id').val(String(standardValueId)).trigger('change');
                    syncEditStandardModalSections();
                } else {
                    populateEditStandardForm(existingText);
                }

                if (resultId) {
                    $.ajax({
                        url: `/captured-results/get-standard-settings/${resultId}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.success && response.data) {
                                applyEditStandardSettings(response.data);
                                // Re-sync Select2 after async apply (modal may already be shown)
                                const selectedId = $('#esl_standard_value_id').val();
                                if (selectedId) {
                                    $('#esl_standard_value_id').val(String(selectedId)).trigger('change');
                                }
                            }
                        }
                    });
                }

                const $modal = $('#edit-standard-modal');
                if ($modal.length) {
                    $modal.appendTo('body').modal('show');
                }
            });

            $('#edit-standard-modal').on('shown.bs.modal', function () {
                const $modal = $(this);
                $modal.find('select.no-select2').each(function () {
                    const $select = $(this);
                    const currentVal = $select.val();
                    if ($.fn.select2) {
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }
                        $select.select2({
                            width: '100%',
                            dropdownParent: $modal,
                            minimumResultsForSearch: $select.is('#esl_standard_value_id') ? 0 : Infinity,
                        });
                        if (currentVal) {
                            $select.val(currentVal).trigger('change');
                        }
                    }
                });
                syncEditStandardModalSections();
            });

            $('#edit-standard-form').on('submit.methodSequences', function(e) {
                e.preventDefault();
                const $form = $(this);
                const valueType = $form.find('input[name="value_type"]:checked').val() || 'use_value';

                if (valueType === 'range') {
                    if (!$('#esl_range_low').val() || !$('#esl_range_high').val()) {
                        alert('Please enter both low and high values for the range.');
                        return;
                    }
                } else if (!$('#esl_standard_value_id').val()) {
                    alert('Please select a standard value.');
                    return;
                } else {
                    const selectedCode = $('#esl_standard_value_id option:selected').data('code') || '';
                    if (selectedCode === 'IsValue') {
                        if (!$('#esl_matrix_operator').val() || !$('#esl_matrix_value').val()) {
                            alert('Please enter Matrix Operator and Actual Value for IsValue.');
                            return;
                        }
                    }
                }

                const submitBtn = $form.find('button[type="submit"]');
                const trackId = $('#standard_step6_track_id').val();
                const capturedResultId = $('#standard_result_id').val();
                submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');

                $.ajax({
                    url: '/captured-results/update-standard-limit',
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Standard');

                        if (!response.success) {
                            alert('Error: ' + (response.message || 'Unable to save standard limit.'));
                            return;
                        }

                        $('#edit-standard-modal').modal('hide');

                        const $row = $(`.sample-result-row[data-captured-result-id="${capturedResultId}"]`);
                        if (response.standard_limit && $row.length) {
                            self.setStep6StandardLimit($row, response.standard_limit);

                            const result = $row.find('.sample-result-input').val();
                            const reportingSymbol = $row.find('.reporting-symbol-select').val();
                            const sampleId = $row.data('sample-id');

                            if (trackId && result) {
                                self.calculateAndUpdateRemark(
                                    trackId,
                                    capturedResultId,
                                    result,
                                    response.standard_limit,
                                    reportingSymbol,
                                    sampleId
                                );
                            }
                        }

                        if (trackId && capturedResultId) {
                            $.ajax({
                                url: `/method-sequence-runs/tracks/${trackId}/step6-standard-limit`,
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'Content-Type': 'application/json',
                                },
                                data: JSON.stringify({
                                    captured_result_id: capturedResultId,
                                    standard_limit: response.standard_limit || null,
                                }),
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Standard');
                        const message = (xhr.responseJSON && xhr.responseJSON.message) || xhr.responseText;
                        alert('Failed to save standard limit: ' + message);
                    }
                });
            });
        },

        /**
         * Save Track Sample Results to staging table (TrackSampleResult)
         * DOES NOT save to CapturedResult - that happens only on POST
         */
        saveTrackSampleResults: function(trackId) {
            const self = this;
            const $button = $(`#save-sample-results-btn-${trackId}`);
            const $table = $(`#sample-results-table-${trackId}`);

            // Validate results are entered
            if (!$table.length) {
                alert('Sample results table not found');
                return;
            }

            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            const originalText = $button.html();
            $button.html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');

            try {
                // Collect sample results from table
                const sampleResults = [];
                const mediaResults = [];
                const controlResults = [];

                // Collect from sample results rows
                $table.find('.sample-result-row').each(function() {
                    const $row = $(this);
                    const capturedResultId = $row.attr('data-captured-result-id');
                    const result = $row.find('.sample-result-input').val();
                    const reportingSymbol = $row.find('.reporting-symbol-select').val();
                    const standardLimit = self.getStep6StandardLimit($row);
                    const rawNumericResult = $row.find('.sample-raw-numeric-result').val(); // Extract from hidden field
                    const $remarkContainer = $row.find('[class*="remark-"][class*="-container"]');
                    const $remarkInput = $remarkContainer.find('input, select');
                    const remark = $remarkInput.val();
                    const isAuto = $remarkContainer.find('[data-is-auto]').data('is-auto');

                    if (result && result.trim() !== '') {
                        sampleResults.push({
                            captured_result_id: capturedResultId,
                            raw_numeric_result: rawNumericResult ? parseFloat(rawNumericResult) : null,
                            result: result,
                            reporting_symbol: reportingSymbol || '',
                            standard_limit: standardLimit,
                            remark: remark || '',
                            is_auto_calculated: isAuto
                        });
                    }
                });

                // If no results to save, warn user
                if (sampleResults.length === 0) {
                    alert('Please enter at least one sample result');
                    $button.prop('disabled', false).html(originalText);
                    return;
                }

                // POST to backend
                $.ajax({
                    url: `/method-sequences/tracks/${trackId}/save-results`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                    },
                    data: JSON.stringify({
                        sample_results: sampleResults,
                        media_results: mediaResults,
                        control_results: controlResults
                    }),
                    success: function(response) {
                        // Show success message
                        const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
                                <i class="mdi mdi-check-circle"></i>
                                <strong>Results Saved!</strong> ${response.message || 'Results saved to staging area.'}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `;
                        $table.before(alertHtml);

                        // Auto-dismiss after 5 seconds
                        setTimeout(() => {
                            $('.alert-success').fadeOut(function() { $(this).remove(); });
                        }, 5000);
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        let errorMsg = 'Error saving results';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        const alertHtml = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
                                <i class="mdi mdi-alert-circle"></i>
                                <strong>Error!</strong> ${errorMsg}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `;
                        $table.before(alertHtml);
                        console.error('Save error:', error, xhr);
                    },
                    complete: function() {
                        // Re-enable button
                        $button.prop('disabled', false).html(originalText);
                    }
                });
            } catch (e) {
                console.error('Error collecting results:', e);
                alert('Error: ' + e.message);
                $button.prop('disabled', false).html(originalText);
            }
        },
        
        /**
         * Calculate and update remark for a sample result in Step 6
         */
        calculateAndUpdateRemark: function(trackId, capturedResultId, result, standardLimit, reportingSymbol, sampleId) {
            const self = this;
            const $row = $(`.sample-result-row[data-sample-id="${sampleId}"]`);
            const $remarkContainer = $row.find('[class*="remark-"][class*="-container"]');
            
            // Show loading state on remark field
            const $remarkInput = $remarkContainer.find('input');
            const originalValue = $remarkInput.val();
            $remarkInput.prop('disabled', true).css('opacity', '0.6');
            
            // Call the calculate remark endpoint
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/step6-remark`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                },
                data: JSON.stringify({
                    captured_result_id: capturedResultId,
                    result: result,
                    standard_limit: standardLimit,
                    reporting_symbol: reportingSymbol,
                }),
                success: function(response) {
                    // Update remark field with calculated value
                    if (response.is_auto_calculated) {
                        // Auto-calculated: update and make readonly
                        $remarkInput.val(response.remark || '-');
                        $remarkInput.prop('readonly', true);
                    } else {
                        // Manual: enable for user input but don't overwrite if empty
                        $remarkInput.prop('readonly', false);
                        // Only set if response has a value or remark was empty
                        if (response.remark && !originalValue) {
                            $remarkInput.val(response.remark);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error calculating remark:', error);
                    // Restore original state on error
                    $remarkInput.val(originalValue);
                },
                complete: function() {
                    // Re-enable the input
                    $remarkInput.prop('disabled', false).css('opacity', '1');
                }
            });
        },
        
        /**
         * Phase 3: Validate Step 6 results before submission
         */
        validateStep6Results: function(trackId) {
            const errors = [];
            let hasErrors = false;

            // Get all sample result inputs
            const $sampleInputs = $(`.sample-result-row[data-track-id="${trackId}"] .sample-result-input`);
            
            $sampleInputs.each(function() {
                const $input = $(this);
                const value = $input.val();
                const fieldName = $input.data('field-name') || 'Sample Result';
                const isRequired = $input.data('required') === true || $input.data('required') === 'true';
                
                // Check if required and empty
                if (isRequired && (!value || value.trim() === '')) {
                    hasErrors = true;
                    // Add error styling
                    $input.addClass('is-invalid').css('border-color', '#dc3545');
                    $input.parent().find('.validation-error-msg').removeClass('d-none').show();
                    errors.push(fieldName + ' is required');
                } else {
                    // Clear error styling
                    $input.removeClass('is-invalid').css('border-color', '');
                    $input.parent().find('.validation-error-msg').addClass('d-none').hide();
                }
            });

            return {
                valid: !hasErrors,
                errors: errors,
                errorCount: errors.length
            };
        },
        
        /**
         * Show validation errors in a summary
         */
        showValidationErrors: function(validationResult) {
            if (validationResult.errorCount === 0) return;

            const errorList = validationResult.errors.map(e => '• ' + e).join('\n');
            alert('Please fix the following errors:\n\n' + errorList);
        },
        
        loadResultsForStage: function(trackId) {
            this.loadResultsForStep6(trackId);
        },
        
        loadMediaForResults: function(trackId) {
            const self = this;
            
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/media`,
                method: 'GET',
                success: function(response) {
                    self.renderMediaResults(trackId, response);
                },
                error: function() {
                    console.error('Error loading media results');
                }
            });
        },
        
        loadControlsForResults: function(trackId) {
            const self = this;
            
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/controls`,
                method: 'GET',
                success: function(response) {
                    self.renderControlResults(trackId, response);
                },
                error: function() {
                    console.error('Error loading control results');
                }
            });
        },
        
        renderResultsTable: function(trackId, data) {
            const container = $(`#results-container-${trackId}`);
            
            if (!data.sample_results || data.sample_results.length === 0) {
                container.html('<div class="alert alert-info">No samples found for this stage</div>');
                return;
            }
            
            let html = `
                <div class="results-table">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sample Code</th>
                                <th>Parameter</th>
                                <th>Method</th>
                                <th>Unit</th>
                                <th>Symbol</th>
                                <th>Result</th>
                                <th>Standard Limit</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.sample_results.forEach(function(sample) {
                html += `
                    <tr>
                        <td>${sample.sample_code}</td>
                        <td>${sample.parameter}</td>
                        <td>
                            <select class="form-control form-control-sm method-select" data-captured-result-id="${sample.captured_result_id}">
                                <option value="">Select method</option>
                                <!-- Methods will be loaded separately -->
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm unit-select" data-captured-result-id="${sample.captured_result_id}">
                                <option value="">Select unit</option>
                                <!-- Units will be loaded separately -->
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm reporting-symbol" 
                                   data-captured-result-id="${sample.captured_result_id}" 
                                   value="${sample.reporting_symbol || ''}" placeholder="Symbol">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm result-input" 
                                   data-captured-result-id="${sample.captured_result_id}" 
                                   value="${sample.result || ''}" placeholder="Result">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm standard-limit" 
                                   data-captured-result-id="${sample.captured_result_id}" 
                                   value="${sample.standard_limit || ''}" placeholder="Limit">
                        </td>
                        <td>
                            <span class="remark-display" data-captured-result-id="${sample.captured_result_id}">${sample.remark || '-'}</span>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            container.html(html);
            
            // Load methods and units for each sample
            this.loadMethodsAndUnitsForResults(data.sample_results);
        },
        
        renderMediaResults: function(trackId, data) {
            const container = $(`#results-container-${trackId}`);
            const existingHtml = container.html();
            
            let mediaHtml = `
                <div class="media-controls-section">
                    <h6 class="section-title"><i class="mdi mdi-flask"></i> Media Results</h6>
                    <div class="media-controls-grid">
            `;
            
            if (data.media && data.media.length > 0) {
                data.media.forEach(function(media) {
                    const existingResult = data.existing_results && data.existing_results.find(r => r.media_id == media.id);
                    mediaHtml += `
                        <div class="media-control-item">
                            <span class="media-control-label">${media.name}</span>
                            <select class="form-control form-control-sm media-control-select" data-media-id="${media.id}">
                                <option value="">Select result</option>
                                <option value="Growth" ${existingResult && existingResult.result === 'Growth' ? 'selected' : ''}>Growth</option>
                                <option value="No-Growth" ${existingResult && existingResult.result === 'No-Growth' ? 'selected' : ''}>No Growth</option>
                            </select>
                        </div>
                    `;
                });
            } else {
                mediaHtml += '<div class="alert alert-info">No media configured for this stage</div>';
            }
            
            mediaHtml += `
                    </div>
                </div>
            `;
            
            container.html(existingHtml + mediaHtml);
        },
        
        renderControlResults: function(trackId, data) {
            const container = $(`#results-container-${trackId}`);
            const existingHtml = container.html();
            
            let controlHtml = `
                <div class="media-controls-section">
                    <h6 class="section-title"><i class="mdi mdi-test-tube"></i> Control Results</h6>
                    <div class="media-controls-grid">
            `;
            
            if (data.controls && data.controls.length > 0) {
                data.controls.forEach(function(control) {
                    const existingResult = data.existing_results && data.existing_results.find(r => r.control_id == control.id);
                    controlHtml += `
                        <div class="media-control-item">
                            <span class="media-control-label">${control.name}</span>
                            <select class="form-control form-control-sm control-result-select" data-control-id="${control.id}">
                                <option value="">Select result</option>
                                <option value="Growth" ${existingResult && existingResult.result === 'Growth' ? 'selected' : ''}>Growth</option>
                                <option value="No-Growth" ${existingResult && existingResult.result === 'No-Growth' ? 'selected' : ''}>No Growth</option>
                            </select>
                        </div>
                    `;
                });
            } else {
                controlHtml += '<div class="alert alert-info">No controls configured for this stage</div>';
            }
            
            controlHtml += `
                    </div>
                </div>
            `;
            
            container.html(existingHtml + controlHtml);
        },
        
        loadMethodsAndUnitsForResults: function(sampleResults) {
            const self = this;
            
            // Load methods
            $.ajax({
                url: '/api/methods',
                method: 'GET',
                success: function(methods) {
                    sampleResults.forEach(function(sample) {
                        const select = $(`select.method-select[data-captured-result-id="${sample.captured_result_id}"]`);
                        methods.forEach(function(method) {
                            select.append(`<option value="${method.id}">${method.name}</option>`);
                        });
                        if (sample.method_id) {
                            select.val(sample.method_id);
                        }
                    });
                }
            });
            
            // Load reporting units
            $.ajax({
                url: '/api/reporting-units',
                method: 'GET',
                success: function(units) {
                    sampleResults.forEach(function(sample) {
                        const select = $(`select.unit-select[data-captured-result-id="${sample.captured_result_id}"]`);
                        units.forEach(function(unit) {
                            select.append(`<option value="${unit.id}">${unit.name}</option>`);
                        });
                        if (sample.reporting_unit_id) {
                            select.val(sample.reporting_unit_id);
                        }
                    });
                }
            });
        },

        saveStageDetails: function(trackId) {
            const self = this;

            // Keep UUID/string inventory IDs intact — never parseInt (destroys UUIDs).
            const normalizeItemId = function(raw) {
                if (raw === undefined || raw === null) {
                    return null;
                }
                const id = String(raw).trim();
                if (!id || id === '0' || id.toLowerCase() === 'nan') {
                    return null;
                }
                return id;
            };

            const collectItems = function(type) {
                const rows = [];
                if (type === 'equipment') {
                    $(`#equipment-list-${trackId} .equipment-card`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        rows.push({
                            id: id,
                            serial: $(this).find('.equipment-serial-input').val() || '',
                            calibration: $(this).find('.equipment-calibration-input').val() || ''
                        });
                    });
                } else if (type === 'controls') {
                    $(`#controls-list-${trackId} .control-card-wrapper`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        const $card = $(this);
                        const name = $(this).attr('data-name') ||
                                   $card.find('.control-name-title').text() ||
                                   'Control ' + id;
                        rows.push({
                            id: id,
                            name: String(name).trim(),
                            is_mandatory: '1',
                            preparation: $card.find('.solution-preparation').val() || '',
                            expiry: $card.find('.solution-expiry').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                } else if (type === 'media') {
                    $(`#media-cards-grid-${trackId} .media-card-wrapper`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        const $card = $(this);
                        const name = $(this).attr('data-name') ||
                                   $card.find('.media-name-title').text() ||
                                   'Media ' + id;
                        rows.push({
                            id: id,
                            name: String(name).trim(),
                            is_mandatory: '1',
                            preparation: $(this).find('.solution-preparation').val() || '',
                            preparation_number: $(this).find('.solution-preparation-number').val() || '',
                            expiry: $(this).find('.solution-expiry').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                } else if (type === 'diluents') {
                    $(`#diluent-cards-grid-${trackId} .diluent-card-wrapper`).each(function() {
                        const id = normalizeItemId($(this).attr('data-id'));
                        if (!id) return;
                        const $card = $(this);
                        const name = $(this).attr('data-name') ||
                                   $card.find('.media-name-title').text() ||
                                   'Diluent ' + id;
                        rows.push({
                            id: id,
                            name: String(name).trim(),
                            is_mandatory: '1',
                            preparation_date: $(this).find('.solution-preparation-date').val() || '',
                            preparation_number: $(this).find('.solution-preparation-number').val() || '',
                            expiry: $(this).find('.solution-expiry').val() || '',
                            result_nature: $(this).find('.solution-result-nature').val() || ''
                        });
                    });
                }
                return rows;
            };

            const equipmentItems = collectItems('equipment');
            const mediaItems = collectItems('media');
            const controlsItems = collectItems('controls');
            const diluentsItems = collectItems('diluents');

            // STEP 5 VALIDATION: Ensure at least one item is added
            const totalItems = equipmentItems.length + mediaItems.length + controlsItems.length + diluentsItems.length;
            if (totalItems === 0) {
                alert('Please add at least one equipment, media, control, or diluent item');
                return;
            }

            if ($(`#stepper-form-${trackId}`).attr('data-stage-locked') === '1') {
                alert('This stage has been completed and cannot be edited.');
                return;
            }

            const idsFromItems = function(items) {
                return (items || []).map(function(i) {
                    return i.id;
                }).filter(Boolean);
            };

            const formData = {
                equipment_items: equipmentItems,
                media_items: mediaItems,
                controls_items: controlsItems,
                diluents_items: diluentsItems,
                equipment_ids: idsFromItems(equipmentItems),
                media_ids: idsFromItems(mediaItems),
                controls_ids: idsFromItems(controlsItems),
                diluents_ids: idsFromItems(diluentsItems),
                started_at: $(`#start-date-${trackId}`).val() || null,
                ended_at: $(`#end-date-${trackId}`).val() || null,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Save stage data only (no results)
            self.submitStageData(trackId, formData);
        },
        
        /**
         * Save results data (Step 6 only)
         */
        saveResultsData: function(trackId) {
            const self = this;

            if ($(`#stepper-form-${trackId}`).attr('data-stage-locked') === '1') {
                alert('This stage has been completed and cannot be edited.');
                return;
            }

            const $sampleTable = $(`#sample-results-table-${trackId}`);
            const $solutionTable = $(`#solution-results-table-${trackId}`);
            if (!$sampleTable.length && !$solutionTable.length) {
                self.loadResultsForStep6(trackId);
                setTimeout(function() {
                    self.saveResultsData(trackId);
                }, 600);
                return;
            }
            
            // Collect sample results from the loaded Step 6 table
            // Use .attr() for UUID data-* values — jQuery .data() coerces leading-zero UUIDs to numbers.
            const sampleResults = [];
            $(`#sample-results-table-${trackId} tbody tr.sample-result-row`).each(function() {
                const capturedResultId = $(this).attr('data-captured-result-id');
                const $resultInput = $(this).find('.sample-result-input');
                const result = $resultInput.val();
                
                // Get reporting symbol from dropdown
                const $symbolSelect = $(this).find('.reporting-symbol-select');
                const reportingSymbol = $symbolSelect.length ? $symbolSelect.val() : null;
                
                // Get remark from auto-calculated, dropdown, or text input (Scenario 3)
                const $remarkContainer = $(this).find('[class*="remark-"][class*="-container"]');
                let remark = '';
                
                const $remarkInput = $remarkContainer.find('input.sample-remark-display');
                const $remarkSelect = $remarkContainer.find('select.sample-remark-dropdown');
                const $remarkTextInput = $remarkContainer.find('input.sample-remark-text-input');
                
                if ($remarkInput.length) {
                    remark = $remarkInput.val(); // Auto-calculated readonly
                } else if ($remarkTextInput.length) {
                    remark = $remarkTextInput.val(); // Scenario 3: manual text input
                } else if ($remarkSelect.length) {
                    remark = $remarkSelect.val(); // Manual dropdown
                }
                
                sampleResults.push({
                    captured_result_id: capturedResultId,
                    raw_numeric_result: $(this).find('.sample-raw-numeric-result').val() ? parseFloat($(this).find('.sample-raw-numeric-result').val()) : null,
                    result: result,
                    remark: remark,
                    reporting_symbol: reportingSymbol,
                    track_id: trackId
                });
            });

            // Collect media and control results separately
            const mediaResults = [];
            const controlResults = [];
            
            $(`#solution-results-table-${trackId} tbody tr.solution-result-row`).each(function() {
                const solutionId = $(this).attr('data-solution-id');
                const solutionType = $(this).attr('data-solution-type');
                const $resultInput = $(this).find('.result-input');
                const resultNature = $resultInput.attr('data-result-nature') || 'none';
                const result = $resultInput.val();
                
                // Build result object
                const resultObject = {
                    id: solutionId,
                    result_nature: resultNature,
                    result: result
                };
                
                // Route to correct array based on type
                if (solutionType === 'control') {
                    resultObject.control_id = solutionId;
                    controlResults.push(resultObject);
                } else if (solutionType === 'media') {
                    resultObject.media_id = solutionId;
                    mediaResults.push(resultObject);
                }
            });
            
            // Validate that results are provided
            if (sampleResults.length === 0 && mediaResults.length === 0 && controlResults.length === 0) {
                alert('No results to save');
                return;
            }
            
            // Submit results
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/save-results`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                },
                data: JSON.stringify({
                    sample_results: sampleResults,
                    media_results: mediaResults,
                    control_results: controlResults
                }),
                success: function(response) {
                    if (response.success) {
                        alert('Results saved successfully!');
                        // Keep stage expanded and re-hydrate equipment/media/controls + Step 6 values.
                        // Plain loadRuns() re-renders empty form panes without populateStageForm.
                        if (!self.idInList(self.expandedStages, trackId)) {
                            self.expandedStages = [String(trackId)];
                        }
                        self.loadRuns(self.activeStageHeaderId, function() {
                            if (!self.idInList(self.expandedStages, trackId)) {
                                self.expandedStages = [String(trackId)];
                            }
                            self.loadRuns(self.activeStageHeaderId);
                            setTimeout(function() {
                                self.loadStageFormData(trackId);
                                setTimeout(function() {
                                    self.goToStep(trackId, 6);
                                }, 150);
                            }, 100);
                        });
                    } else {
                        alert('Error saving results: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving results';
                    alert(message);
                }
            });
        },
        
        /**
         * Phase 3: Save Step 6 results with validation
         * Collects results, remarks, and reporting symbols from DOM and saves them via AJAX
         */
        saveStep6Results: function(trackId, stageData) {
            const self = this;
            
            // Collect sample results from the loaded Step 6 table
            // Use .attr() for UUID data-* values — jQuery .data() coerces leading-zero UUIDs to numbers.
            const sampleResults = [];
            $(`#sample-results-table-${trackId} tbody tr.sample-result-row`).each(function() {
                const capturedResultId = $(this).attr('data-captured-result-id');
                const $resultInput = $(this).find('.sample-result-input');
                const result = $resultInput.val();
                
                // Get reporting symbol from dropdown
                const $symbolSelect = $(this).find('.reporting-symbol-select');
                const reportingSymbol = $symbolSelect.length ? $symbolSelect.val() : null;
                
                // Get remark from auto-calculated, dropdown, or text input (Scenario 3)
                const $remarkContainer = $(this).find('[class*="remark-"][class*="-container"]');
                let remark = '';
                
                const $remarkInput = $remarkContainer.find('input.sample-remark-display');
                const $remarkSelect = $remarkContainer.find('select.sample-remark-dropdown');
                const $remarkTextInput = $remarkContainer.find('input.sample-remark-text-input');
                
                if ($remarkInput.length) {
                    remark = $remarkInput.val(); // Auto-calculated readonly
                } else if ($remarkTextInput.length) {
                    remark = $remarkTextInput.val(); // Scenario 3: manual text input
                } else if ($remarkSelect.length) {
                    remark = $remarkSelect.val(); // Manual dropdown
                }
                
                sampleResults.push({
                    captured_result_id: capturedResultId,
                    raw_numeric_result: $(this).find('.sample-raw-numeric-result').val() ? parseFloat($(this).find('.sample-raw-numeric-result').val()) : null,
                    result: result,
                    remark: remark,
                    reporting_symbol: reportingSymbol,
                    track_id: trackId
                });
            });

            // Collect solution results (media, controls, diluents)
            const solutionResults = [];
            $(`#solution-results-table-${trackId} tbody tr.solution-result-row`).each(function() {
                const solutionId = $(this).attr('data-solution-id');
                const solutionType = $(this).attr('data-solution-type');
                const $resultInput = $(this).find('.result-input');
                const result = $resultInput.val();
                
                solutionResults.push({
                    id: solutionId,
                    type: solutionType,
                    result: result
                });
            });
            
            // Save sample results first
            if (sampleResults.length > 0) {
                $.ajax({
                    url: `/method-sequence-runs/tracks/${trackId}/save-sample-results`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                    },
                    data: JSON.stringify({
                        sample_results: sampleResults
                    }),
                    success: function(response) {
                        // After saving sample results, save solution results if any
                        if (solutionResults.length > 0) {
                            self.saveSolutionResults(trackId, solutionResults, stageData);
                        } else {
                            // No solution results, just submit stage data
                            self.submitStageData(trackId, stageData);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error saving sample results:', error);
                        alert('Error saving results. Please try again.');
                    }
                });
            } else if (solutionResults.length > 0) {
                // No sample results but have solution results
                self.saveSolutionResults(trackId, solutionResults, stageData);
            } else {
                // No results to save, just submit stage data
                self.submitStageData(trackId, stageData);
            }
        },

        /**
         * Save solution results to their respective tables
         */
        saveSolutionResults: function(trackId, solutionResults, stageData) {
            const self = this;
            
            $.ajax({
                url: `/method-sequence-runs/tracks/${trackId}/save-solution-results`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                },
                data: JSON.stringify({
                    solution_results: solutionResults
                }),
                success: function(response) {
                    // After saving solution results, submit stage data
                    self.submitStageData(trackId, stageData);
                },
                error: function(xhr, status, error) {
                    console.error('Error saving solution results:', error);
                    alert('Error saving solution results. Please try again.');
                }
            });
        },
        
        saveStageResults: function(trackId, stageData) {
            const self = this;
            
            // Collect sample results (use .attr() so UUID data-* values stay intact)
            const sampleResults = [];
            $(`#results-container-${trackId} .results-table tbody tr`).each(function() {
                const capturedResultId = $(this).find('select.method-select').attr('data-captured-result-id');
                sampleResults.push({
                    captured_result_id: capturedResultId,
                    result: $(this).find('.result-input').val(),
                    method_id: $(this).find('.method-select').val(),
                    reporting_unit_id: $(this).find('.unit-select').val(),
                    reporting_symbol: $(this).find('.reporting-symbol').val(),
                    standard_limit: $(this).find('.standard-limit').val()
                });
            });
            
            // Collect media results
            const mediaResults = [];
            $(`#results-container-${trackId} .media-control-select`).each(function() {
                const mediaId = $(this).attr('data-media-id');
                const result = $(this).val();
                if (result) {
                    mediaResults.push({
                        media_id: mediaId,
                        result: result
                    });
                }
            });
            
            // Collect control results
            const controlResults = [];
            $(`#results-container-${trackId} .control-result-select`).each(function() {
                const controlId = $(this).attr('data-control-id');
                const result = $(this).val();
                if (result) {
                    controlResults.push({
                        control_id: controlId,
                        result: result
                    });
                }
            });
            
            const resultData = {
                ...stageData,
                sample_results: sampleResults,
                media_results: mediaResults,
                control_results: controlResults
            };
            
            self.submitStageData(trackId, resultData);
        },
        
        submitStageData: function(trackId, data) {
            const self = this;
            
            $.ajax({
                url: `/method-sequences/tracks/${trackId}/update`,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        alert('Stage details saved successfully!');
                        if (response.track) {
                            self.refreshStep1BasicInfo(trackId, response.track);
                            self.applyStageLock(
                                trackId,
                                self.isTrackLocked(response.track),
                                self.getTrackLockReason(response.track)
                            );
                        }
                        // Re-expand stage and reload form data after saving
                        self.loadRuns(self.activeStageHeaderId, function() {
                            // Re-add to expandedStages to keep it expanded
                            if (!self.idInList(self.expandedStages, trackId)) {
                                self.expandedStages = [String(trackId)];
                            }
                            // Re-render the stage to show expanded view with form
                            self.loadRuns(self.activeStageHeaderId);
                            // Load and populate form data
                            setTimeout(() => {
                                self.loadStageFormData(trackId);
                            }, 100);
                        });
                    } else {
                        alert('Error saving stage details: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving stage details';
                    alert(message);
                }
            });
        },
        
        cancelEdit: function(trackId) {
            // Simply reload the runs to close the expanded form
            this.loadRuns(this.activeStageHeaderId);
        },
        
        isStageOverdue: function(track) {
            if (track.status !== 'running' || !track.started_at || !track.test_stage) {
                return false;
            }
            
            const durationHours = track.test_stage.duration_hours || 0;
            const started = new Date(track.started_at);
            const expectedEnd = new Date(started.getTime() + durationHours * 3600000);
            const now = new Date();
            
            return now > expectedEnd;
        },
    };

    /**
     * Utility Functions for Scenario 2 & Range Result Formatting
     */

    /**
     * Format result value with scientific notation and decimal rounding
     * @param {number} raw - Raw numeric value
     * @param {string} reportingSymbol - Symbol (<, >, =, >=, <=, or empty)
     * @param {boolean} useScientificNotation - Whether to use scientific notation
     * @param {number|null} significantFigures - Significant figures for rounding
     * @param {number|null} decimalPlaces - Decimal places for rounding
     * @returns {string} Formatted result
     */
    function formatResultValue(raw, reportingSymbol, useScientificNotation, significantFigures, decimalPlaces) {
        if (raw === null || raw === undefined) {
            return '';
        }

        let formatted = '';
        const symbol = reportingSymbol && reportingSymbol !== 'none' ? reportingSymbol.trim() : '';

        if (useScientificNotation) {
            // For microbiology tests
            if (raw >= 10) {
                formatted = toScientificNotation(raw, significantFigures);
            } else if (raw < 10 && symbol === '') {
                formatted = '<10';
            } else {
                formatted = raw.toString();
            }
        } else {
            // For non-scientific tests: apply decimal places and/or significant figures
            if (significantFigures !== null && decimalPlaces !== null) {
                formatted = roundToSignificantFiguresAndDecimals(raw, significantFigures, decimalPlaces);
            } else if (decimalPlaces !== null) {
                formatted = parseFloat(raw).toFixed(decimalPlaces);
            } else if (significantFigures !== null) {
                formatted = roundToSignificantFigures(raw, significantFigures);
            } else {
                formatted = raw.toString();
            }
        }

        // Prepend reporting symbol if provided
        if (symbol !== '') {
            return symbol + ' ' + formatted;
        }

        return formatted;
    }

    /**
     * Calculate PASS/FAIL remark for display before save
     * @param {number} raw - Raw numeric value
     * @param {string} standardValue - Standard threshold value
     * @param {string} valueType - Comparison type (Max, Min, less_than, greater_than)
     * @returns {string} 'PASS' or 'FAIL'
     */
    function calculateRemarkDisplay(raw, standardValue, valueType) {
        if (!standardValue || standardValue === '') {
            return '-';
        }

        // Convert standard if it's in scientific notation
        const actualStdValue = convertIfScientific(standardValue);
        valueType = (valueType || 'max').toLowerCase();

        switch (valueType) {
            case 'max':
                return (raw <= actualStdValue) ? 'PASS' : 'FAIL';
            case 'min':
                return (raw >= actualStdValue) ? 'PASS' : 'FAIL';
            case 'less_than':
                return (raw < actualStdValue) ? 'PASS' : 'FAIL';
            case 'greater_than':
                return (raw > actualStdValue) ? 'PASS' : 'FAIL';
            default:
                return '-';
        }
    }

    /**
     * Check if result qualifies as "Not Detected"
     * @param {number} raw - Raw numeric value
     * @param {string} reportingSymbol - Reporting symbol
     * @param {number|null} lod - Limit of Detection
     * @param {boolean} nonDetectableFlag - Non-detectable flag
     * @returns {boolean} True if result should be "Not Detected"
     */
    function isNotDetectable(raw, reportingSymbol, lod, nonDetectableFlag) {
        if (!nonDetectableFlag) {
            return false;
        }

        const symbol = reportingSymbol && reportingSymbol !== 'none' ? reportingSymbol.trim() : '';

        // Condition 1: Symbol < and raw == 1
        if (symbol === '<' && raw === 1) {
            return true;
        }

        // Condition 2: raw < LOD
        if (lod !== null && lod !== undefined && raw < lod) {
            return true;
        }

        // Condition 3: raw <= 0
        if (raw <= 0) {
            return true;
        }

        return false;
    }

    /**
     * Convert number to scientific notation string
     * @param {number} value - Numeric value
     * @param {number|null} significantFigures - Number of significant figures
     * @returns {string} Scientific notation (e.g., "8.0 × 10^1")
     */
    function toScientificNotation(value, significantFigures) {
        if (value === 0) {
            return '0';
        }

        const sigFigs = significantFigures || 2;
        const exponent = Math.floor(Math.log10(Math.abs(value)));
        const mantissa = value / Math.pow(10, exponent);
        const rounded = mantissa.toFixed(sigFigs - 1);

        return rounded + ' × 10^' + exponent;
    }

    function toSuperscriptDigits(exponent) {
        const superscripts = {
            '0': '⁰',
            '1': '¹',
            '2': '²',
            '3': '³',
            '4': '⁴',
            '5': '⁵',
            '6': '⁶',
            '7': '⁷',
            '8': '⁸',
            '9': '⁹',
            '-': '⁻'
        };

        return String(exponent).split('').map((char) => superscripts[char] || char).join('');
    }

    function normalizeSuperscriptExponentToAscii(text) {
        const asciiDigits = {
            '⁰': '0',
            '¹': '1',
            '²': '2',
            '³': '3',
            '⁴': '4',
            '⁵': '5',
            '⁶': '6',
            '⁷': '7',
            '⁸': '8',
            '⁹': '9',
            '⁻': '-'
        };

        return String(text || '').replace(/[⁰¹²³⁴⁵⁶⁷⁸⁹⁻]+/g, (match) =>
            match.split('').map((char) => asciiDigits[char] || char).join('')
        );
    }

    function formatScientificForInput(value) {
        if (value === null || value === undefined) {
            return '';
        }

        let text = String(value).trim();
        if (!text) {
            return '';
        }

        text = text.replace(/([x*])\s*10\^(-?\d+)/gi, function (_m, _mul, exponent) {
            return ' × 10' + toSuperscriptDigits(exponent);
        });

        text = text.replace(/×\s*10\^(-?\d+)/gi, function (_m, exponent) {
            return '×10' + toSuperscriptDigits(exponent);
        });

        text = text.replace(/(\d+(?:\.\d+)?)\s*e([+-]?\d+)/gi, function (_m, mantissa, exponent) {
            return mantissa + ' × 10' + toSuperscriptDigits(exponent);
        });

        return text;
    }

    /**
     * Parse scientific notation string to numeric value
     * Handles formats: "1.0×10⁶", "1.0*10^6", "1.0e6"
     * @param {string|number} value - Scientific notation string or number
     * @returns {number} Numeric value
     */
    function convertIfScientific(value) {
        if (typeof value !== 'string') {
            return parseFloat(value);
        }

        value = normalizeSuperscriptExponentToAscii(value.trim());

        // Pattern: "1.0×10⁶" or "1.0*10^6"
        const match1 = value.match(/(\d+\.?\d*)\s*[×*x]\s*10\s*[\^]?(-?\d+)/i);
        if (match1) {
            const mantissa = parseFloat(match1[1]);
            const exponent = parseInt(match1[2]);
            return mantissa * Math.pow(10, exponent);
        }

        // Pattern: "1.0e6"
        const match2 = value.match(/(\d+\.?\d*)e([+-]?\d+)/i);
        if (match2) {
            const mantissa = parseFloat(match2[1]);
            const exponent = parseInt(match2[2]);
            return mantissa * Math.pow(10, exponent);
        }

        return parseFloat(value);
    }

    /**
     * Round number to significant figures
     * @param {number} value - The value to round
     * @param {number} significantFigures - Number of significant figures
     * @returns {string} Rounded value as string
     */
    function roundToSignificantFigures(value, significantFigures) {
        if (value === 0) {
            return '0';
        }

        const exponent = Math.floor(Math.log10(Math.abs(value)));
        const rounded = parseFloat((value).toPrecision(significantFigures));

        return rounded.toString();
    }

    /**
     * Round to both significant figures and decimal places
     * Applies the more restrictive rounding requirement
     * @param {number} value - The value to round
     * @param {number} significantFigures - Number of significant figures
     * @param {number} decimalPlaces - Number of decimal places
     * @returns {string} Rounded value as string
     */
    function roundToSignificantFiguresAndDecimals(value, significantFigures, decimalPlaces) {
        // First round to decimal places
        const byDecimals = parseFloat(value.toFixed(decimalPlaces));

        // Apply significant figures logic
        if (byDecimals === 0) {
            return byDecimals.toFixed(decimalPlaces);
        }

        const exponent = Math.floor(Math.log10(Math.abs(byDecimals)));
        const bySignificant = parseFloat((byDecimals).toPrecision(significantFigures));

        // Return with decimal places precision
        return bySignificant.toFixed(decimalPlaces);
    }
    
    // Expose globally for worksheets init and inline onclick handlers
    window.MethodSequences = MethodSequences;
    window.methodSequences = MethodSequences;

    // Fallback init when worksheets inline bootstrap is not present
    $(document).ready(function() {
        if ($('#method-sequences-container').length > 0 && typeof window.initMethodSequencesWidget === 'function') {
            window.initMethodSequencesWidget();
        } else if ($('#method-sequences-container').length > 0) {
            MethodSequences.init();
        }
    });
    
    // Expose formatting utilities for accessible use
    window.resultFormatting = {
        formatResultValue: formatResultValue,
        formatScientificForInput: formatScientificForInput,
        calculateRemarkDisplay: calculateRemarkDisplay,
        isNotDetectable: isNotDetectable,
        toScientificNotation: toScientificNotation,
        convertIfScientific: convertIfScientific,
        roundToSignificantFigures: roundToSignificantFigures,
        roundToSignificantFiguresAndDecimals: roundToSignificantFiguresAndDecimals
    };

})(jQuery);

