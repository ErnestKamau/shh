<div class="findings-popover" style="max-width: 700px; max-height: 500px; overflow-y: auto;">
    <div class="mb-3">
        <h6 class="mb-2" style="color: #1a73e8; font-weight: 600;">
            <i class="mdi mdi-alert-circle"></i> Audit Findings - {{ $audit->audit_number }}
        </h6>
        <div class="small text-muted">
            <strong>Title:</strong> {{ $audit->title }} | 
            <strong>Total Findings:</strong> {{ $findings->count() }}
        </div>
    </div>
    
    @if($findings->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead style="background: #f5f5f5; position: sticky; top: 0;">
                <tr>
                    <th style="font-size: 11px; width: 60px;">#</th>
                    <th style="font-size: 11px;">Description</th>
                    <th style="font-size: 11px; width: 100px;">Category</th>
                    <th style="font-size: 11px; width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($findings as $finding)
                <tr>
                    <td style="font-size: 11px;">{{ $finding->finding_number ?? $finding->order_index ?? $loop->iteration }}</td>
                    <td style="font-size: 11px;">
                        <div style="max-width: 300px;">
                            <strong>{{ Str::limit($finding->observation ?? 'N/A', 60) }}</strong>
                            @if($finding->iso_clause)
                            <br><small class="text-muted">ISO Clause: {{ $finding->iso_clause }}</small>
                            @endif
                        </div>
                    </td>
                    <td style="font-size: 11px;">
                        <span class="badge badge-sm badge-info">{{ $finding->finding_category_name ?? ($finding->findingCategory?->name ?? 'N/A') }}</span>
                    </td>
                    <td style="font-size: 11px;">
                        @php
                            $statusName = $finding->status_name ?? 'Open';
                            $statusBadge = match(strtolower($statusName)) {
                                'closed', 'resolved' => 'success',
                                'in progress', 'in_progress' => 'info',
                                'open' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-sm badge-{{ $statusBadge }}">{{ $statusName }}</span>
                    </td>
                </tr>
                @if($finding->objective_evidence || $finding->requirement)
                <tr style="background: #fafafa;">
                    <td colspan="4" style="font-size: 10px; padding: 8px;">
                        @if($finding->requirement)
                        <div class="mb-1"><strong>Requirement:</strong> {{ Str::limit($finding->requirement, 150) }}</div>
                        @endif
                        @if($finding->objective_evidence)
                        <div class="mb-1"><strong>Evidence:</strong> {{ Str::limit($finding->objective_evidence, 150) }}</div>
                        @endif
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-muted py-4">
        <i class="mdi mdi-information-outline" style="font-size: 48px; opacity: 0.3;"></i>
        <p class="mt-2">No findings recorded for this audit yet.</p>
    </div>
    @endif
    
    <!-- Summary -->
    @if($findings->count() > 0)
    <div class="border-top pt-2 mt-2">
        <div class="row text-center">
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">Total</div>
                <div class="h6 mb-0" style="font-size: 14px;">{{ $findings->count() }}</div>
            </div>
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">Open</div>
                <div class="h6 mb-0" style="font-size: 14px;">
                    {{ $findings->filter(function($f) { return strtolower($f->status_name ?? $f->status ?? 'open') === 'open'; })->count() }}
                </div>
            </div>
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">Closed</div>
                <div class="h6 mb-0" style="font-size: 14px;">
                    {{ $findings->filter(function($f) { return in_array(strtolower($f->status_name ?? $f->status ?? ''), ['closed', 'resolved']); })->count() }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

