@php
    $editStandardLookupValues = \App\StandardValue::query()
        ->where('status', 1)
        ->orderBy('name')
        ->get(['id', 'name', 'code']);
@endphp
<style>
    #edit-standard-modal .modal-content,
    #edit-standard-modal .modal-body {
        overflow: visible;
    }
    #edit-standard-modal .esl-config-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    #edit-standard-modal .esl-config-card__header {
        padding: 0.65rem 0.85rem;
        font-weight: 600;
        color: #fff;
        background: #64748b;
    }
    #edit-standard-modal .esl-config-card__body {
        padding: 0.85rem;
        background: #fff;
    }
</style>
<div id="edit-standard-modal" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="edit-standard-form">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="mdi mdi-pencil"></i> Edit Standard Limit
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="result_id" id="standard_result_id">
                <input type="hidden" name="sample_code" id="standard_sample_code">
                <input type="hidden" name="analyte" id="standard_analyte">
                <input type="hidden" id="standard_step6_track_id" value="">

                <div class="form-group mb-3">
                    <label class="control-label d-block">Value Type <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input esl-value-type" type="radio" name="value_type" id="esl_value_type_range" value="range">
                        <label class="form-check-label" for="esl_value_type_range">
                            <i class="mdi mdi-range"></i> Range
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input esl-value-type" type="radio" name="value_type" id="esl_value_type_use_value" value="use_value" checked>
                        <label class="form-check-label" for="esl_value_type_use_value">
                            <i class="mdi mdi-numeric"></i> Use Value
                        </label>
                    </div>
                </div>

                <div id="esl-range-section" class="esl-config-card mb-3" style="display:none; border-color:#007bff;">
                    <div class="esl-config-card__header" style="background:#007bff;">
                        <i class="mdi mdi-range"></i> Range Configuration
                    </div>
                    <div class="esl-config-card__body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="control-label">Low Value <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="range_low" id="esl_range_low" placeholder="Enter low value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="control-label">High Value <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="range_high" id="esl_range_high" placeholder="Enter high value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="esl-use-value-section" class="esl-config-card mb-0">
                    <div class="esl-config-card__header">
                        <i class="mdi mdi-numeric"></i> Standard Value Configuration
                    </div>
                    <div class="esl-config-card__body">
                        <div class="form-group mb-3">
                            <label class="control-label">Standard Value <span class="text-danger">*</span></label>
                            <select class="form-control no-select2" name="standard_value_id" id="esl_standard_value_id">
                                <option value="">Select standard value...</option>
                                @foreach($editStandardLookupValues as $lookupValue)
                                    <option value="{{ $lookupValue->id }}" data-code="{{ $lookupValue->code }}">
                                        {{ $lookupValue->name }} ({{ $lookupValue->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="esl-is-value-fields" style="display:none;">
                            <div class="alert alert-info py-2">
                                <i class="mdi mdi-information"></i> Additional configuration for the selected standard value.
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="control-label">Matrix Operator <span class="text-danger">*</span></label>
                                        <select class="form-control no-select2" name="matrix_operator" id="esl_matrix_operator">
                                            <option value="">Select Operator</option>
                                            <option value="max">Max</option>
                                            <option value="min">Min</option>
                                            <option value="greater_than">> (Greater Than)</option>
                                            <option value="less_than">< (Less Than)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="control-label">Actual Value <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="matrix_value" id="esl_matrix_value" placeholder="Enter actual value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info btn-sm">
                    <i class="mdi mdi-check-circle"></i> Save Standard
                </button>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
