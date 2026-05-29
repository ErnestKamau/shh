@props([
    'wireModel' => 'caseFormData',
    'idSuffix' => 'cf',
])

@php
    $wm = $wireModel;
    $sid = $idSuffix;
@endphp

<!-- 1. SAMPLE INFORMATION -->
<div class="cf-card">
    <div class="cf-card-header" style="border-left: 4px solid #3b82f6;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-information-outline text-primary" style="font-size: 18px;"></i>
            1. Sample Information
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Lab No</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.lab_no">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">File No</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.file_no">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Date In</label>
                <input type="date" class="cf-form-control" wire:model="{{ $wm }}.date_in">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Client</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.client">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">No. of Samples</label>
                <input type="number" class="cf-form-control" wire:model="{{ $wm }}.no_of_samples">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Name of Analyst</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.name_of_analyst">
            </div>
            <div class="col-md-6 mb-3">
                <label class="cf-input-label">Analyst Signature</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.analyst_signature" placeholder="Type to sign">
            </div>
            <div class="col-md-12 mb-3">
                <label class="cf-input-label">Sample Condition</label>
                <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                        <input type="checkbox" class="custom-control-input" id="cf_sealed_{{ $sid }}" wire:model="{{ $wm }}.sample_condition_sealed">
                        <label class="custom-control-label" for="cf_sealed_{{ $sid }}">Sealed</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                        <input type="checkbox" class="custom-control-input" id="cf_labelled_{{ $sid }}" wire:model="{{ $wm }}.sample_condition_labelled">
                        <label class="custom-control-label" for="cf_labelled_{{ $sid }}">Labelled</label>
                    </div>
                    <div class="flex-grow-1" style="min-width: 250px;">
                        <input type="text" class="cf-form-control" placeholder="Condition remarks…" wire:model="{{ $wm }}.sample_condition_remark">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. SAMPLE SCREENING -->
<div class="cf-card">
    <div class="cf-card-header" style="border-left: 4px solid #6366f1;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-magnify text-indigo" style="font-size: 18px;"></i>
            2. Sample Screening
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label class="cf-input-label">Screening Date</label>
                <input type="date" class="cf-form-control" wire:model="{{ $wm }}.screening_date">
            </div>
            <div class="col-md-8 mb-3">
                <label class="cf-input-label">Screening Method</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.screening_method">
            </div>
        </div>

        <div class="cf-section-subtitle">Sample Types</div>
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="custom-control custom-checkbox custom-checkbox-modern">
                    <input type="checkbox" class="custom-control-input" id="sc_blood_{{ $sid }}" wire:model="{{ $wm }}.screening_sample_type_blood">
                    <label class="custom-control-label" for="sc_blood_{{ $sid }}">Blood</label>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="custom-control custom-checkbox custom-checkbox-modern">
                    <input type="checkbox" class="custom-control-input" id="sc_obj_blood_{{ $sid }}" wire:model="{{ $wm }}.screening_sample_type_object_with_blood">
                    <label class="custom-control-label" for="sc_obj_blood_{{ $sid }}">Object w/ Blood</label>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="custom-control custom-checkbox custom-checkbox-modern">
                    <input type="checkbox" class="custom-control-input" id="sc_semen_{{ $sid }}" wire:model="{{ $wm }}.screening_sample_type_semen">
                    <label class="custom-control-label" for="sc_semen_{{ $sid }}">Semen</label>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="custom-control custom-checkbox custom-checkbox-modern">
                    <input type="checkbox" class="custom-control-input" id="sc_obj_semen_{{ $sid }}" wire:model="{{ $wm }}.screening_sample_type_object_with_semen">
                    <label class="custom-control-label" for="sc_obj_semen_{{ $sid }}">Object w/ Semen</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="cf-input-label">Others</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.screening_sample_type_others">
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Result 1</label>
                <select class="cf-form-control" wire:model="{{ $wm }}.screening_results_1">
                    <option value="">— Select —</option>
                    <option value="Positive">Positive</option>
                    <option value="Negative">Negative</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="cf-input-label">Result 2</label>
                <select class="cf-form-control" wire:model="{{ $wm }}.screening_results_2">
                    <option value="">— Select —</option>
                    <option value="Positive">Positive</option>
                    <option value="Negative">Negative</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- 3. EXTRACTION & QUANTIFICATION -->
<div class="cf-card">
    <div class="cf-card-header" style="border-left: 4px solid #06b6d4;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-test-tube text-cyan" style="font-size: 18px;"></i>
            3. Extraction & Quantification
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="row">
            <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                <div class="cf-section-subtitle">Extraction</div>
                <div class="form-group mb-3">
                    <label class="cf-input-label">Extraction Date</label>
                    <input type="date" class="cf-form-control" wire:model="{{ $wm }}.extraction_date">
                </div>
                <div class="form-group mb-2">
                    <label class="cf-input-label">Extraction Method</label>
                    <div class="d-flex flex-column" style="gap: 8px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="ext_chelex_{{ $sid }}" wire:model="{{ $wm }}.extraction_method_chelex">
                            <label class="custom-control-label" for="ext_chelex_{{ $sid }}">Chelex Method</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="ext_prepfiler_{{ $sid }}" wire:model="{{ $wm }}.extraction_method_prepfiler">
                            <label class="custom-control-label" for="ext_prepfiler_{{ $sid }}">Prepfiler Method</label>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="cf-input-label">Other Method</label>
                    <input type="text" class="cf-form-control" wire:model="{{ $wm }}.extraction_method_other">
                </div>
            </div>

            <div class="col-md-6 mb-3 pl-md-4">
                <div class="cf-section-subtitle">Quantification</div>
                <div class="form-group mb-3">
                    <label class="cf-input-label">Quantification Date</label>
                    <input type="date" class="cf-form-control" wire:model="{{ $wm }}.quantification_date">
                </div>
                <div class="form-group mb-2">
                    <label class="cf-input-label">Kit & Cycles</label>
                    <div class="d-flex flex-column" style="gap: 8px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="quant_cycles_{{ $sid }}" wire:model="{{ $wm }}.quantification_no_of_cycles_40">
                            <label class="custom-control-label" for="quant_cycles_{{ $sid }}">No. of Cycles (40)</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="quant_kit_{{ $sid }}" wire:model="{{ $wm }}.quantification_kit_used_quant_trio">
                            <label class="custom-control-label" for="quant_kit_{{ $sid }}">Kit: Quant Trio</label>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="cf-input-label">Remarks</label>
                    <input type="text" class="cf-form-control" wire:model="{{ $wm }}.quantification_remarks">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. PCR AMPLIFICATION & INJECTION -->
<div class="cf-card">
    <div class="cf-card-header" style="border-left: 4px solid #8b5cf6;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-dna text-purple" style="font-size: 18px;"></i>
            4. PCR Amplification & Injection
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="row">
            <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                <div class="cf-section-subtitle">PCR Amplification</div>
                <div class="form-group mb-3">
                    <label class="cf-input-label">PCR Amplification Date</label>
                    <input type="date" class="cf-form-control" wire:model="{{ $wm }}.pcr_amplification_date">
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">No. of Cycles</label>
                    <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 15px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="pcr_28_{{ $sid }}" wire:model="{{ $wm }}.pcr_no_of_cycles_28">
                            <label class="custom-control-label" for="pcr_28_{{ $sid }}">28</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="pcr_29_{{ $sid }}" wire:model="{{ $wm }}.pcr_no_of_cycles_29">
                            <label class="custom-control-label" for="pcr_29_{{ $sid }}">29</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="pcr_30_{{ $sid }}" wire:model="{{ $wm }}.pcr_no_of_cycles_30">
                            <label class="custom-control-label" for="pcr_30_{{ $sid }}">30</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="pcr_32_{{ $sid }}" wire:model="{{ $wm }}.pcr_no_of_cycles_32">
                            <label class="custom-control-label" for="pcr_32_{{ $sid }}">32</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Kit Used</label>
                    <div class="d-flex flex-column mt-2" style="gap: 8px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="kit_identifiler_{{ $sid }}" wire:model="{{ $wm }}.pcr_kit_used_identifiler_plus">
                            <label class="custom-control-label" for="kit_identifiler_{{ $sid }}">Identifiler Plus</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="kit_globalfiler_{{ $sid }}" wire:model="{{ $wm }}.pcr_kit_used_globalfiler">
                            <label class="custom-control-label" for="kit_globalfiler_{{ $sid }}">Globalfiler</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="kit_yfiler_{{ $sid }}" wire:model="{{ $wm }}.pcr_kit_used_yfiler_plus">
                            <label class="custom-control-label" for="kit_yfiler_{{ $sid }}">Yfiler Plus</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="cf-input-label">PCR Remarks</label>
                    <input type="text" class="cf-form-control" wire:model="{{ $wm }}.pcr_remarks">
                </div>
            </div>

            <div class="col-md-6 mb-3 pl-md-4">
                <div class="cf-section-subtitle">Injection & Interpretation</div>
                <div class="row">
                    <div class="col-6 form-group mb-3">
                        <label class="cf-input-label">Injection Date</label>
                        <input type="date" class="cf-form-control" wire:model="{{ $wm }}.injection_date">
                    </div>
                    <div class="col-6 form-group mb-3">
                        <label class="cf-input-label">Run ID</label>
                        <input type="text" class="cf-form-control" wire:model="{{ $wm }}.injection_run_id">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="custom-control custom-checkbox custom-checkbox-modern">
                        <input type="checkbox" class="custom-control-input" id="inj_inst_3500_{{ $sid }}" wire:model="{{ $wm }}.injection_instrument_3500">
                        <label class="custom-control-label font-weight-bold" for="inj_inst_3500_{{ $sid }}">Instrument: 3500 Genetic Analyzer</label>
                    </div>
                </div>

                <label class="cf-input-label mt-3">Control Results</label>
                <div class="row mb-3">
                    <div class="col-6 mb-2">
                        <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Positive</label>
                        <select class="cf-form-control" wire:model="{{ $wm }}.injection_result_positive">
                            <option value="">—</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Negative</label>
                        <select class="cf-form-control" wire:model="{{ $wm }}.injection_result_negative">
                            <option value="">—</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Ladder</label>
                        <select class="cf-form-control" wire:model="{{ $wm }}.injection_result_ladder">
                            <option value="">—</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="text-muted small font-weight-bold" style="font-size: 0.68rem; text-transform: uppercase;">Blank</label>
                        <select class="cf-form-control" wire:model="{{ $wm }}.injection_result_blank">
                            <option value="">—</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="cf-input-label">Run Result</label>
                    <select class="cf-form-control" wire:model="{{ $wm }}.injection_run">
                        <option value="">— Select —</option>
                        <option value="Pass">Pass</option>
                        <option value="Fail">Fail</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 5. REPORTING & REVIEW -->
<div class="cf-card">
    <div class="cf-card-header" style="border-left: 4px solid #10b981;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-checkbox-marked-circle-outline text-success" style="font-size: 18px;"></i>
            5. Reporting & Manager Review
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="row">
            <div class="col-md-6 mb-3 pr-md-4" style="border-right: 1px solid #e2e8f0;">
                <div class="cf-section-subtitle">Reporting Details</div>
                <div class="form-group mb-3">
                    <label class="cf-input-label">Draft Report Date</label>
                    <input type="date" class="cf-form-control" wire:model="{{ $wm }}.reporting_draft_report_date">
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Review Status</label>
                    <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="rep_reviewed_{{ $sid }}" wire:model="{{ $wm }}.reporting_reviewed">
                            <label class="custom-control-label" for="rep_reviewed_{{ $sid }}">Reviewed</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="rep_corrected_{{ $sid }}" wire:model="{{ $wm }}.reporting_corrected">
                            <label class="custom-control-label" for="rep_corrected_{{ $sid }}">Corrected</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Attachments</label>
                    <div class="d-flex flex-column mt-2" style="gap: 8px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="att_real_time_{{ $sid }}" wire:model="{{ $wm }}.reporting_attachment_real_time_data">
                            <label class="custom-control-label" for="att_real_time_{{ $sid }}">Real Time Data</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="att_converge_{{ $sid }}" wire:model="{{ $wm }}.reporting_attachment_converge">
                            <label class="custom-control-label" for="att_converge_{{ $sid }}">Converge</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="att_stat_{{ $sid }}" wire:model="{{ $wm }}.reporting_attachment_statistical_analysis">
                            <label class="custom-control-label" for="att_stat_{{ $sid }}">Statistical Analysis</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="cf-input-label">Reporting Remarks</label>
                    <textarea class="cf-form-control" wire:model="{{ $wm }}.reporting_remarks" rows="2"></textarea>
                </div>
            </div>

            <div class="col-md-6 mb-3 pl-md-4">
                <div class="cf-section-subtitle">Manager's Review & Verification</div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Manager Review Type</label>
                    <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="mgr_tech_{{ $sid }}" wire:model="{{ $wm }}.manager_review_technical">
                            <label class="custom-control-label" for="mgr_tech_{{ $sid }}">Technical</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="mgr_admin_{{ $sid }}" wire:model="{{ $wm }}.manager_review_administrative">
                            <label class="custom-control-label" for="mgr_admin_{{ $sid }}">Administrative</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Manager's Verification</label>
                    <div class="d-flex align-items-center mt-2 flex-wrap" style="gap: 20px;">
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="mgr_verified_{{ $sid }}" wire:model="{{ $wm }}.manager_comments_verified">
                            <label class="custom-control-label" for="mgr_verified_{{ $sid }}">Verified</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-checkbox-modern">
                            <input type="checkbox" class="custom-control-input" id="mgr_not_verified_{{ $sid }}" wire:model="{{ $wm }}.manager_comments_not_verified">
                            <label class="custom-control-label" for="mgr_not_verified_{{ $sid }}">Not Verified</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 form-group mb-3">
                        <label class="cf-input-label">Manager Date</label>
                        <input type="date" class="cf-form-control" wire:model="{{ $wm }}.manager_date">
                    </div>
                    <div class="col-6 form-group mb-3">
                        <label class="cf-input-label">Manager Name</label>
                        <input type="text" class="cf-form-control" wire:model="{{ $wm }}.manager_name">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="cf-input-label">Manager Signature</label>
                    <input type="text" class="cf-form-control" wire:model="{{ $wm }}.manager_signature" placeholder="Type to sign">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 6. AUTHENTICATION -->
<div class="cf-card mb-0">
    <div class="cf-card-header" style="border-left: 4px solid #f59e0b;">
        <h6 class="cf-card-title">
            <i class="mdi mdi-account-check-outline text-warning" style="font-size: 18px;"></i>
            6. Authentication
        </h6>
    </div>
    <div class="cf-card-body">
        <div class="form-group mb-3">
            <label class="cf-input-label">Reviewer's Comments on the Report</label>
            <textarea class="cf-form-control" wire:model="{{ $wm }}.reviewer_comments" rows="2"></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="cf-input-label">Reviewer Name</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.reviewer_name">
            </div>
            <div class="col-md-4 mb-3">
                <label class="cf-input-label">Reviewer Signature</label>
                <input type="text" class="cf-form-control" wire:model="{{ $wm }}.reviewer_signature" placeholder="Type to sign">
            </div>
            <div class="col-md-4 mb-3">
                <label class="cf-input-label">Reviewer Date</label>
                <input type="date" class="cf-form-control" wire:model="{{ $wm }}.reviewer_date">
            </div>
        </div>
    </div>
</div>
