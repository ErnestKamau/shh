{{-- Sample Results Table for Step 6 --}}
<div class="card border p-2 mb-2 bg-white shadow-sm position-relative stage-results-card" style="border-left: 3px solid #007bff !important; border-radius: 8px;">
    <div class="mb-2">
        <h5 class="mb-0 font-weight-bold stage-results-card-title"><i class="mdi mdi-test-tube"></i> Sample Results</h5>
    </div>
        @if (!empty($samples))
            {{-- Information Bar --}}
            <div class="alert alert-light border mb-2" style="background-color: #eff6ff; border-color: #93c5fd; padding: 0.5rem 0.65rem; margin-bottom: 0.65rem;">
                <div class="row">
                    <div class="col-md-4 mb-1">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.625rem; letter-spacing: 0.04em; margin-bottom: 0.15rem;">Method</small>
                        <strong style="font-size: 0.8125rem; color: #1f2937; display: block;">{{ $samples[0]['method_name'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-1">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.625rem; letter-spacing: 0.04em; margin-bottom: 0.15rem;">Unit</small>
                        <strong style="font-size: 0.8125rem; color: #1f2937; display: block;">{{ $samples[0]['unit'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-1">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.625rem; letter-spacing: 0.04em; margin-bottom: 0.15rem;">Analyte/Parameter</small>
                        <strong style="font-size: 0.8125rem; color: #1f2937; display: block;">{{ $samples[0]['analyte_name'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm mb-0" style="border-collapse: collapse; font-size: 0.8125rem;" id="sample-results-table-{{ $trackId }}">
                <thead style="background-color: #f1f5f9;">
                    <tr>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; width: 20%; vertical-align: middle;">Sample</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; width: 20%; vertical-align: middle;">Reporting Symbol</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; width: 20%; vertical-align: middle;">Standard Limit</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; width: 20%; vertical-align: middle;">Result</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; width: 20%; vertical-align: middle;">Remark</th>
                    </tr>
                </thead>
                <tbody style="border-top: 1px solid #e2e8f0;">
                    @if (empty($samples))
                        <tr>
                            <td colspan="5" class="text-center text-muted py-2" style="border-top: 1px solid #e2e8f0; font-size: 0.75rem;">
                                <i class="mdi mdi-information-outline"></i> No samples to display
                            </td>
                        </tr>
                    @else
                        @foreach ($samples as $sample)
                            <tr class="sample-result-row" data-sample-id="{{ $sample['id'] }}" data-captured-result-id="{{ $sample['captured_result_id'] }}" data-track-id="{{ $trackId }}" style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                                {{-- Sample Column with Standard Badge --}}
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <strong style="display: block; color: #1e293b; font-size: 0.8125rem;">{{ $sample['sample_code'] ?? 'N/A' }}</strong>
                                    <span class="badge" style="background-color: #0891b2; color: white; font-size: 0.625rem; font-weight: 700; padding: 0.15rem 0.4rem; display: inline-block; margin-top: 0.25rem;">{{ $sample['standard_name'] ?? 'N/A' }}</span>
                                </td>
                                
                                {{-- Reporting Symbol Column (Dropdown) --}}
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <select class="form-control form-control-sm reporting-symbol-select" 
                                            style="border-radius: 6px; border-color: #cbd5e1; font-size: 0.75rem; min-height: 30px; height: 30px;"
                                            data-sample-id="{{ $sample['id'] }}"
                                            data-captured-result-id="{{ $sample['captured_result_id'] }}"
                                            data-track-id="{{ $trackId }}">
                                        <option value="">--- None ---</option>
                                        <option value="=" {{ ($sample['reporting_symbol'] ?? '') === '=' ? 'selected' : '' }}>=</option>
                                        <option value="<" {{ ($sample['reporting_symbol'] ?? '') === '<' ? 'selected' : '' }}>&lt;</option>
                                        <option value=">" {{ ($sample['reporting_symbol'] ?? '') === '>' ? 'selected' : '' }}>&gt;</option>
                                        <option value="≤" {{ ($sample['reporting_symbol'] ?? '') === '≤' ? 'selected' : '' }}>≤</option>
                                        <option value="≥" {{ ($sample['reporting_symbol'] ?? '') === '≥' ? 'selected' : '' }}>≥</option>
                                    </select>
                                </td>
                                
                                {{-- Standard Limit Column --}}
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    @php
                                        $displayLimit = $sample['standard_limit'] ?? $sample['standard_limit_text'] ?? '-';
                                    @endphp
                                    <div class="standard-limit-container" data-track-id="{{ $trackId }}">
                                        <div class="d-flex align-items-center">
                                            <span class="standard-limit-text text-muted small" style="font-size: 0.75rem; font-weight: 500;">
                                                {{ $displayLimit !== '' ? $displayLimit : '-' }}
                                            </span>
                                            <input type="hidden"
                                                   class="step6-standard-limit-value"
                                                   value="{{ $displayLimit !== '' ? $displayLimit : '' }}">
                                            <button type="button"
                                                    class="btn btn-link btn-sm p-0 ml-1 step6-edit-standard-btn"
                                                    data-result-id="{{ $sample['captured_result_id'] }}"
                                                    data-sample-code="{{ $sample['sample_code'] ?? '' }}"
                                                    data-analyte="{{ $sample['analyte_code'] ?? '' }}"
                                                    data-track-id="{{ $trackId }}"
                                                    data-captured-result-id="{{ $sample['captured_result_id'] }}"
                                                    data-toggle="modal"
                                                    data-target="#edit-standard-modal"
                                                    title="Edit Standard Limit">
                                                <i class="mdi mdi-pencil text-muted" style="font-size: 12px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Result Column (Editable) --}}
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <div class="position-relative">
                                        <input type="text" 
                                               class="form-control form-control-sm sample-result-input" 
                                               placeholder="Result"
                                               style="border-radius: 6px; border-color: #cbd5e1; font-size: 0.75rem; min-height: 30px; height: 30px;"
                                               data-sample-id="{{ $sample['id'] }}"
                                               data-captured-result-id="{{ $sample['captured_result_id'] }}"
                                               data-standard-limit="{{ $displayLimit !== '' ? $displayLimit : '' }}"
                                               data-track-id="{{ $trackId }}"
                                               data-required="true"
                                               data-field-name="Sample {{ $loop->iteration }} Result"
                                               value="{{ $sample['result'] ?? '' }}">
                                        <small class="text-danger d-none validation-error-msg" style="display:none; font-size: 0.6875rem;">
                                            <i class="mdi mdi-alert-circle"></i> Required
                                        </small>
                                    </div>
                                </td>
                                
                                {{-- Remark Column (Auto or Manual) --}}
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    @php
                                        $isAutoCalculated = !($sample['remark_is_manual'] ?? false);
                                    @endphp
                                    @if ($isAutoCalculated)
                                        <div class="remark-auto-container" data-sample-id="{{ $sample['id'] }}">
                                            <input type="text" 
                                                   class="form-control form-control-sm sample-remark-display" 
                                                   placeholder="Auto"
                                                   style="border-radius: 6px; border-color: #cbd5e1; background-color: #f1f5f9; color: #94a3b8; font-size: 0.75rem; font-style: italic; cursor: not-allowed; min-height: 30px; height: 30px;"
                                                   data-sample-id="{{ $sample['id'] }}"
                                                   data-is-auto="true"
                                                   value="{{ $sample['remark'] ?? '' }}"
                                                   readonly
                                                   title="Auto-calculated based on standard limits">
                                        </div>
                                    @else
                                        <div class="remark-manual-container" data-sample-id="{{ $sample['id'] }}">
                                            <select class="form-control form-control-sm sample-remark-dropdown"
                                                    style="border-radius: 6px; border-color: #cbd5e1; font-size: 0.75rem; min-height: 30px; height: 30px;"
                                                    data-sample-id="{{ $sample['id'] }}"
                                                    data-is-auto="false">
                                                <option value="">--- Select ---</option>
                                                <option value="PASS" {{ ($sample['remark'] ?? '') === 'PASS' ? 'selected' : '' }}>Conforming</option>
                                                <option value="FAIL" {{ ($sample['remark'] ?? '') === 'FAIL' ? 'selected' : '' }}>Non-conforming</option>
                                                <option value="REPEAT" {{ ($sample['remark'] ?? '') === 'REPEAT' ? 'selected' : '' }}>REPEAT</option>
                                                <option value="INCONCLUSIVE" {{ ($sample['remark'] ?? '') === 'INCONCLUSIVE' ? 'selected' : '' }}>INCONCLUSIVE</option>
                                                <option value="ERROR" {{ ($sample['remark'] ?? '') === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                                                <option value="PENDING" {{ ($sample['remark'] ?? '') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                            </select>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
</div>
