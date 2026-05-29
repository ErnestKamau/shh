<div class="grouped-results-capture p-3">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} mb-3 py-2 small">
            {{ $message }}
        </div>
    @endif

    @if(empty($parameters) || empty($samples))
        <div class="alert alert-light border text-center py-5 mb-0">
            <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 2.5rem;"></i>
            <p class="mb-0 mt-2 text-muted">No parameters or samples found for results capture on this pipeline.</p>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-1">Results capture matrix</h6>
                <p class="small text-muted mb-0">Enter a reporting symbol and result for each parameter and sample.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" wire:click="saveResults"
                wire:loading.attr="disabled" wire:target="saveResults">
                <span wire:loading.remove wire:target="saveResults">
                    <i class="mdi mdi-content-save"></i> Save results
                </span>
                <span wire:loading wire:target="saveResults">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    Saving…
                </span>
            </button>
        </div>

        <div class="gw-results-matrix-wrap table-responsive">
            <table class="table table-sm table-bordered gw-results-matrix mb-0">
                <thead>
                    <tr>
                        <th class="gw-results-matrix__sticky-col">Loci/Parameters</th>
                        @foreach($samples as $sample)
                            <th class="text-center">{{ $sample['sample_detail_code'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($parameters as $parameter)
                        @php $rowKey = $parameter['row_key']; @endphp
                        <tr wire:key="row-{{ $rowKey }}">
                            <td class="gw-results-matrix__sticky-col">
                                <span class="font-weight-semibold d-block">{{ $parameter['label'] }}</span>
                            </td>
                            @foreach($samples as $sample)
                                @php
                                    $sampleCode = $sample['sample_detail_code'];
                                    $cell = $cells[$rowKey][$sampleCode] ?? null;
                                @endphp
                                @if($cell)
                                    <td wire:key="cell-{{ $cell['captured_result_id'] }}" class="gw-results-matrix__cell">
                                        <div class="gw-results-matrix__cell-inputs">
                                            <select class="form-control form-control-sm gw-results-matrix__symbol"
                                                wire:model.lazy="cells.{{ $rowKey }}.{{ $sampleCode }}.reporting_symbol">
                                                <option value="">—</option>
                                                <option value="=">=</option>
                                                <option value="<">&lt;</option>
                                                <option value=">">&gt;</option>
                                                <option value="≤">≤</option>
                                                <option value="≥">≥</option>
                                                <option value="<=">&lt;=</option>
                                                <option value=">=">&gt;=</option>
                                            </select>
                                            <input type="text"
                                                class="form-control form-control-sm gw-results-matrix__result"
                                                wire:model.lazy="cells.{{ $rowKey }}.{{ $sampleCode }}.result"
                                                placeholder="Result">
                                        </div>
                                    </td>
                                @else
                                    <td class="text-muted text-center small">—</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
