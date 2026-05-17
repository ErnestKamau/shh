{{-- Sample Results Table for Step 6 --}}
<div class="card border p-3 mb-3 bg-white shadow-sm position-relative" style="border-left: 4px solid #007bff !important; border-radius: 8px;">
    <div class="mb-3">
        <h5 class="mb-2 font-weight-bold"><i class="mdi mdi-test-tube"></i> Sample Results</h5>
    </div>
        @if (!empty($samples))
            {{-- Information Bar --}}
            <div class="alert alert-light border mb-4" style="background-color: #eff6ff; border-color: #93c5fd; padding: 1rem;">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Method</small>
                        <strong style="font-size: 0.9rem; color: #1f2937; display: block;">{{ $samples[0]['method_name'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Unit</small>
                        <strong style="font-size: 0.9rem; color: #1f2937; display: block;">{{ $samples[0]['unit'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block" style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Analyte/Parameter</small>
                        <strong style="font-size: 0.9rem; color: #1f2937; display: block;">{{ $samples[0]['analyte_name'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm" style="border-collapse: collapse; font-size: 0.85rem;" id="sample-results-table-{{ $trackId }}">
                <thead style="background-color: #f1f5f9;">
                    <tr>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; width: 20%; vertical-align: middle;">Sample</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; width: 20%; vertical-align: middle;">Reporting Symbol</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; width: 20%; vertical-align: middle;">Standard Limit</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; width: 20%; vertical-align: middle;">Result</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; width: 20%; vertical-align: middle;">Remark</th>
                    </tr>
                </thead>
                <tbody style="border-top: 1px solid #e2e8f0;">
                    @if (empty($samples))
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4" style="border-top: 1px solid #e2e8f0;">
                                <i class="mdi mdi-information-outline"></i> No samples to display
                            </td>
                        </tr>
                    @else
                        @foreach ($samples as $sample)
                            <tr class="sample-result-row" data-sample-id="{{ $sample['id'] }}" data-captured-result-id="{{ $sample['captured_result_id'] }}" data-track-id="{{ $trackId }}" style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                                {{-- Sample Column with Standard Badge --}}
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <strong style="display: block; color: #1e293b; font-size: 0.9rem;">{{ $sample['sample_code'] ?? 'N/A' }}</strong>
                                    <span class="badge" style="background-color: #0891b2; color: white; font-size: 0.65rem; font-weight: 700; padding: 0.25rem 0.5rem; display: inline-block; margin-top: 0.5rem;">{{ $sample['standard_name'] ?? 'N/A' }}</span>
                                </td>
                                
                                {{-- Reporting Symbol Column (Dropdown) --}}
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <select class="form-control form-control-sm reporting-symbol-select" 
                                            style="border-radius: 0.375rem; border-color: #cbd5e1; font-size: 0.85rem;"
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
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">
                                        @if ($sample['standard_limit_type'] === 'range')
                                            {{ $sample['limit_low'] ?? '' }} - {{ $sample['limit_high'] ?? '' }}
                                        @elseif ($sample['standard_limit_type'] === 'value')
                                            {{ $sample['limit_value'] ?? '-' }}
                                        @else
                                            {{ $sample['standard_limit_text'] ?? '-' }}
                                        @endif
                                    </span>
                                </td>
                                
                                {{-- Result Column (Editable) --}}
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <div class="position-relative">
                                        <input type="text" 
                                               class="form-control form-control-sm sample-result-input" 
                                               placeholder="Result"
                                               style="border-radius: 0.375rem; border-color: #cbd5e1; font-size: 0.85rem;"
                                               data-sample-id="{{ $sample['id'] }}"
                                               data-captured-result-id="{{ $sample['captured_result_id'] }}"
                                               data-standard-limit="{{ $sample['standard_limit_text'] }}"
                                               data-track-id="{{ $trackId }}"
                                               data-required="true"
                                               data-field-name="Sample {{ $loop->iteration }} Result"
                                               value="{{ $sample['result'] ?? '' }}">
                                        <small class="text-danger d-none validation-error-msg" style="display:none; font-size: 0.75rem;">
                                            <i class="mdi mdi-alert-circle"></i> Required
                                        </small>
                                    </div>
                                </td>
                                
                                {{-- Remark Column (Auto or Manual) --}}
                                <td style="padding: 1rem; vertical-align: middle;">
                                    @php
                                        $isAutoCalculated = !($sample['remark_is_manual'] ?? false);
                                    @endphp
                                    @if ($isAutoCalculated)
                                        <div class="remark-auto-container" data-sample-id="{{ $sample['id'] }}">
                                            <input type="text" 
                                                   class="form-control form-control-sm sample-remark-display" 
                                                   placeholder="Auto"
                                                   style="border-radius: 0.375rem; border-color: #cbd5e1; background-color: #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-style: italic; cursor: not-allowed;"
                                                   data-sample-id="{{ $sample['id'] }}"
                                                   data-is-auto="true"
                                                   value="{{ $sample['remark'] ?? '' }}"
                                                   readonly
                                                   title="Auto-calculated based on standard limits">
                                        </div>
                                    @else
                                        <div class="remark-manual-container" data-sample-id="{{ $sample['id'] }}">
                                            <select class="form-control form-control-sm sample-remark-dropdown"
                                                    style="border-radius: 0.375rem; border-color: #cbd5e1; font-size: 0.85rem;"
                                                    data-sample-id="{{ $sample['id'] }}"
                                                    data-is-auto="false">
                                                <option value="">--- Select ---</option>
                                                <option value="PASS" {{ ($sample['remark'] ?? '') === 'PASS' ? 'selected' : '' }}>PASS</option>
                                                <option value="FAIL" {{ ($sample['remark'] ?? '') === 'FAIL' ? 'selected' : '' }}>FAIL</option>
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
