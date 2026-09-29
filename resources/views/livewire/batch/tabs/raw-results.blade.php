<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="mb-0"><i class="mdi mdi-sync-alert"></i> Raw results</h5>
            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary btn-action-sm"
                        data-toggle="modal"
                        data-target="#process-results-modal">
                    <i class="mdi mdi-cog"></i> Process results
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top p-0">
            @if(isset($resultsBySample) && $resultsBySample->isNotEmpty())
                @foreach($resultsBySample as $sampleCode => $sampleResults)
                    <div class="border-bottom" data-sample-code="{{ $sampleCode }}">
                        <div class="px-3 py-2 bg-light d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                            <div class="font-weight-bold">
                                <i class="mdi mdi-flask-outline text-muted mr-1"></i>
                                {{ $sampleCode }}
                            </div>
                            <small class="text-muted">{{ $sampleResults->count() }} {{ \Illuminate\Support\Str::plural('test', $sampleResults->count()) }}</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover workflow-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Analyte</th>
                                        <th>Analysis Type</th>
                                        <th style="min-width: 140px;">Result</th>
                                        <th>Spec Limit</th>
                                        <th>Remark</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sampleResults as $result)
                                        @php
                                            $hasResult = $result->result !== null && $result->result !== '';
                                            $remarkLabel = format_result_remark($result->remark ?? null);
                                            $isFail = is_non_conforming_remark($result->remark ?? null);
                                            $isPass = is_conforming_remark($result->remark ?? null);
                                            $remarkBorder = $isFail ? 'border-danger' : ($isPass ? 'border-success' : 'border-secondary');
                                            $remarkText = $isFail ? 'text-danger' : ($isPass ? 'text-success' : '');
                                            $limitDisplay = $result->standard_limit_display
                                                ?? (filled($result->main_value) && ! in_array(trim((string) $result->main_value), ['NS', '-', '—', '–', 'N/A', 'n/a', 'NA'], true)
                                                    ? $result->main_value
                                                    : null);
                                            $unit = $result->effectiveReportingUnitName() ?: '—';
                                        @endphp
                                        <tr class="parameter-cell"
                                            data-analyte="{{ $result->analyte_code }}"
                                            data-sample="{{ $sampleCode }}"
                                            data-result-id="{{ $result->id }}">
                                            <td class="font-weight-bold">{{ $result->analyte_code ?? 'N/A' }}</td>
                                            <td>{{ $result->analysis_type->name ?? '—' }}</td>
                                            <td>
                                                <div class="form-control form-control-sm bg-light {{ $remarkBorder }} {{ $remarkText }}"
                                                     style="min-height: 31px; max-width: 180px;">
                                                    {{ $hasResult ? $result->result : '—' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    {{ $limitDisplay ?: ($hasResult ? 'No limit set' : '—') }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="{{ $remarkText }}">
                                                    {{ $remarkLabel !== '' ? $remarkLabel : '—' }}
                                                </small>
                                            </td>
                                            <td><small class="text-muted">{{ $unit }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 48px;"></i>
                    <h6 class="mt-3 text-muted">No Raw Results Found</h6>
                    <p class="text-muted mb-0"><small>There are no raw results to display for this batch</small></p>
                </div>
            @endif
        </div>
    </div>
</div>
