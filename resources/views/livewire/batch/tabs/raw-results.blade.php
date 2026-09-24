<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="mb-0"><i class="mdi mdi-sync-alert"></i> Raw results</h5>
            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                @if(in_array($batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection'], true))
                    <a href="{{ route('batch.request-test-worksheet-pdf', ['batch' => $batch->id]) }}"
                       class="btn btn-sm btn-outline-secondary btn-action-sm" target="_blank">
                        <i class="mdi mdi-file-pdf-box"></i> Generate PDF
                    </a>
                    <a href="{{ route('batch.request-test-worksheet-print', ['batch' => $batch->id]) }}"
                       class="btn btn-sm btn-outline-secondary btn-action-sm" target="_blank">
                        <i class="mdi mdi-printer"></i> Print PDF
                    </a>
                @endif
                <button type="button"
                        class="btn btn-sm btn-outline-secondary btn-action-sm"
                        data-toggle="modal"
                        data-target="#process-results-modal">
                    <i class="mdi mdi-cog"></i> Process results
                </button>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top p-0">
    <div class="table-responsive">
        <table class="table table-hover workflow-table mb-0" id="captured-results-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 120px;">Sample Code</th>
                    @if(isset($rawResults) && count($rawResults) > 0)
                        @php
                            $uniqueAnalytes = $rawResults->groupBy('analyte_code')->keys();
                        @endphp
                        @foreach($uniqueAnalytes as $analyte)
                            <th class="text-center" style="min-width: 200px;">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="font-weight-bold">{{ $analyte }}</span>
                                    <small class="text-muted">Result</small>
                                </div>
                            </th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody id="captured-results-tbody">
                @if(isset($rawResults) && count($rawResults) > 0)
                    @php
                        $groupedBySample = $rawResults->groupBy(function($item) {
                            return $item->sample->sample_code;
                        });
                    @endphp
                    @foreach($groupedBySample as $sampleCode => $sampleResults)
                        <tr data-sample-code="{{ $sampleCode }}">
                            <td class="font-weight-bold text-center">{{ $sampleCode }}</td>
                            @foreach($uniqueAnalytes as $analyte)
                                @php
                                    $matches = $sampleResults->where('analyte_code', $analyte)->values();
                                    // Prefer a row that already has a captured value when duplicates share analyte_code.
                                    $result = $matches->first(function ($row) {
                                        return $row->result !== null && $row->result !== '';
                                    }) ?? $matches->first();
                                @endphp
                                <td class="parameter-cell" data-analyte="{{ $analyte }}" data-sample="{{ $sampleCode }}">
                                    @if($result)
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
                                        @endphp
                                        <div class="result-input-container mb-2">
                                            <div class="form-control form-control-sm bg-light {{ $remarkBorder }} {{ $remarkText }}"
                                                 style="min-height: 31px;"
                                                 title="{{ $remarkLabel !== '' ? $remarkLabel : '' }}">
                                                {{ $hasResult ? $result->result : '—' }}
                                            </div>
                                        </div>
                                        <div class="standard-limit-container">
                                            <span class="standard-limit-text text-muted small">
                                                @if($limitDisplay)
                                                    {{ $limitDisplay }}
                                                @elseif($hasResult)
                                                    No limit set
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <div class="result-input-container mb-2">
                                            <div class="form-control form-control-sm bg-light border-secondary text-muted"
                                                 style="min-height: 31px;">—</div>
                                        </div>
                                        <div class="standard-limit-container">
                                            <span class="standard-limit-text text-muted small">—</span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100" class="text-center py-5">
                            <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 48px;"></i>
                            <h6 class="mt-3 text-muted">No Raw Results Found</h6>
                            <p class="text-muted mb-0"><small>There are no raw results to display for this batch</small></p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
        </div>
    </div>
</div>
