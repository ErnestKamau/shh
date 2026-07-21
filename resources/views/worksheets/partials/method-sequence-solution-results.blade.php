{{-- Solution Results Table for Step 6 (Controls & Media) --}}
<div class="card border p-2 mb-2 bg-white shadow-sm position-relative stage-results-card" style="border-left: 3px solid #007bff !important; border-radius: 8px;">
    <div class="mb-2">
        <h5 class="mb-0 font-weight-bold stage-results-card-title"><i class="mdi mdi-flask-outline"></i> Solution Results (Controls & Media)</h5>
    </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="border-collapse: collapse; font-size: 0.8125rem;" id="solution-results-table-{{ $trackId }}">
                <thead style="background-color: #f1f5f9;">
                    <tr>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; vertical-align: middle;">Solution</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; vertical-align: middle;">Type</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; vertical-align: middle;">Result</th>
                        <th style="padding: 0.4rem 0.55rem; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; text-align: right; width: 72px; vertical-align: middle;">Action</th>
                    </tr>
                </thead>
                <tbody style="border-top: 1px solid #e2e8f0;">
                    @if (empty($solutions))
                        <tr>
                            <td colspan="4" class="text-center text-muted py-2" style="border-top: 1px solid #e2e8f0; font-size: 0.75rem;">
                                <i class="mdi mdi-information-outline"></i> No solutions to display
                            </td>
                        </tr>
                    @else
                        @foreach ($solutions as $solution)
                            <tr data-solution-id="{{ $solution['id'] }}" data-solution-type="{{ $solution['type'] }}" class="solution-result-row" style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <strong style="color: #1e293b; font-size: 0.8125rem;">{{ $solution['name'] ?? 'N/A' }}</strong>
                                </td>
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <span class="badge" style="background-color: {{ $solution['type'] === 'control' ? '#06b6d4' : '#10b981' }}; color: white; font-size: 0.625rem; font-weight: 700; padding: 0.15rem 0.4rem;">
                                        {{ ucfirst($solution['type']) }}
                                    </span>
                                </td>
                                <td style="padding: 0.4rem 0.55rem; vertical-align: middle;">
                                    <input type="text" 
                                           class="form-control form-control-sm result-input" 
                                           style="border-radius: 6px; border-color: #cbd5e1; font-size: 0.75rem; min-height: 30px; height: 30px;"
                                           placeholder="Enter result"
                                           data-solution-id="{{ $solution['id'] }}"
                                           data-solution-type="{{ $solution['type'] }}"
                                           data-result-nature="{{ $solution['result_nature'] ?? 'qualitative' }}"
                                           value="{{ $solution['result'] ?? '' }}">
                                </td>
                                <td style="padding: 0.4rem 0.55rem; text-align: right; vertical-align: middle;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary delete-solution-btn" 
                                            data-solution-id="{{ $solution['id'] }}"
                                            data-solution-type="{{ $solution['type'] }}"
                                            title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
</div>
