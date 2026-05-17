{{-- Solution Results Table for Step 6 (Controls & Media) --}}
<div class="card border p-3 mb-3 bg-white shadow-sm position-relative" style="border-left: 4px solid #007bff !important; border-radius: 8px;">
    <div class="mb-3">
        <h5 class="mb-2 font-weight-bold"><i class="mdi mdi-flask-outline"></i> Solution Results (Controls & Media)</h5>
    </div>
        <div class="table-responsive">
            <table class="table table-sm" style="border-collapse: collapse; font-size: 0.85rem;" id="solution-results-table-{{ $trackId }}">
                <thead style="background-color: #f1f5f9;">
                    <tr>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; vertical-align: middle;">Solution</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; vertical-align: middle;">Type</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; vertical-align: middle;">Result</th>
                        <th style="padding: 0.75rem; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.75rem; text-align: right; width: 80px; vertical-align: middle;">Action</th>
                    </tr>
                </thead>
                <tbody style="border-top: 1px solid #e2e8f0;">
                    @if (empty($solutions))
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4" style="border-top: 1px solid #e2e8f0;">
                                <i class="mdi mdi-information-outline"></i> No solutions to display
                            </td>
                        </tr>
                    @else
                        @foreach ($solutions as $solution)
                            <tr data-solution-id="{{ $solution['id'] }}" data-solution-type="{{ $solution['type'] }}" class="solution-result-row" style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <strong style="color: #1e293b; font-size: 0.9rem;">{{ $solution['name'] ?? 'N/A' }}</strong>
                                </td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <span class="badge" style="background-color: {{ $solution['type'] === 'control' ? '#06b6d4' : '#10b981' }}; color: white; font-size: 0.65rem; font-weight: 700; padding: 0.25rem 0.5rem;">
                                        {{ ucfirst($solution['type']) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <input type="text" 
                                           class="form-control form-control-sm result-input" 
                                           style="border-radius: 0.375rem; border-color: #cbd5e1; font-size: 0.85rem;"
                                           placeholder="Enter result"
                                           data-solution-id="{{ $solution['id'] }}"
                                           data-solution-type="{{ $solution['type'] }}"
                                           data-result-nature="{{ $solution['result_nature'] ?? 'qualitative' }}"
                                           value="{{ $solution['result'] ?? '' }}">
                                </td>
                                <td style="padding: 1rem; text-align: right; vertical-align: middle;">
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
