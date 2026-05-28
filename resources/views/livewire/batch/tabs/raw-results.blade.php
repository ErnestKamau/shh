<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-sync-alert"></i> Raw results</h5>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary btn-action-sm"
                    data-toggle="modal"
                    data-target="#process-results-modal">
                <i class="mdi mdi-cog"></i> Process results
            </button>
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
                                    $result = $sampleResults->where('analyte_code', $analyte)->first();
                                @endphp
                                <td class="parameter-cell" data-analyte="{{ $analyte }}" data-sample="{{ $sampleCode }}">
                                    @if($result)
                                        <!-- Result Input Field -->
                                        <div class="result-input-container mb-2">
                                            <div class="input-group input-group-sm">
                                                <input type="text" 
                                                       class="form-control result-input {{ $result->remark == 'FAIL' ? 'border-danger' : ($result->remark == 'PASS' ? 'border-success' : 'border-secondary') }}" 
                                                       value="{{ $result->result }}" 
                                                       data-result-id="{{ $result->id }}"
                                                       data-sample-code="{{ $sampleCode }}"
                                                       data-analyte="{{ $analyte }}"
                                                       placeholder="Enter result...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-sm parameter-settings-btn" 
                                                            type="button" 
                                                            data-result-id="{{ $result->id }}"
                                                            data-toggle="modal" 
                                                            data-target="#parameter-settings-modal">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Standard Limit Display -->
                                        <div class="standard-limit-container">
                                            <div class="d-flex align-items-center">
                                                <span class="standard-limit-text text-muted small">
                                                    @if($result->main_value)
                                                        {{ $result->main_value }}
                                                    @else
                                                        No limit set
                                                    @endif
                                                </span>
                                                <button class="btn btn-link btn-sm p-0 ml-1 edit-standard-btn" 
                                                        type="button"
                                                        data-result-id="{{ $result->id }}"
                                                        data-toggle="modal" 
                                                        data-target="#edit-standard-modal">
                                                    <i class="mdi mdi-pencil text-muted" style="font-size: 12px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Empty Cell for Missing Parameter -->
                                        <div class="result-input-container mb-2">
                                            <div class="input-group input-group-sm">
                                                <input type="text" 
                                                       class="form-control result-input border-secondary" 
                                                       value="" 
                                                       data-sample-code="{{ $sampleCode }}"
                                                       data-analyte="{{ $analyte }}"
                                                       placeholder="Enter result...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-sm parameter-settings-btn" 
                                                            type="button" 
                                                            data-sample-code="{{ $sampleCode }}"
                                                            data-analyte="{{ $analyte }}"
                                                            data-toggle="modal" 
                                                            data-target="#parameter-settings-modal">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Empty Standard Limit -->
                                        <div class="standard-limit-container">
                                            <div class="d-flex align-items-center">
                                                <span class="standard-limit-text text-muted small">No limit set</span>
                                                <button class="btn btn-link btn-sm p-0 ml-1 edit-standard-btn" 
                                                        type="button"
                                                        data-sample-code="{{ $sampleCode }}"
                                                        data-analyte="{{ $analyte }}"
                                                        data-toggle="modal" 
                                                        data-target="#edit-standard-modal">
                                                    <i class="mdi mdi-pencil text-muted" style="font-size: 12px;"></i>
                                                </button>
                                            </div>
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
