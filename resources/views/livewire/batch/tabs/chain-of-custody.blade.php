<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-sitemap"></i> Chain of custody</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="perPage" class="form-label mb-0 me-2 text-muted small">Show</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; border-radius: 6px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by workflow stage, tracking stage, user, or comments...">
            </div>

            @if($custodyRecords->count() > 0)
                <style>
                    .custody-timeline {
                        position: relative;
                        padding-left: 30px;
                        margin: 20px 0;
                    }
                    .custody-timeline::before {
                        content: '';
                        position: absolute;
                        left: 7px;
                        top: 0;
                        bottom: 0;
                        width: 2px;
                        background-color: #e5e7eb;
                    }
                    .timeline-event {
                        position: relative;
                        margin-bottom: 1.5rem;
                    }
                    .timeline-event:last-child {
                        margin-bottom: 0;
                    }
                    .timeline-marker {
                        position: absolute;
                        left: -30px;
                        top: 4px;
                        width: 16px;
                        height: 16px;
                        border-radius: 50%;
                        background-color: #fff;
                        border: 2px solid #3b82f6;
                        z-index: 1;
                    }
                    .timeline-marker.completed {
                        background-color: #10b981;
                        border-color: #10b981;
                    }
                    .timeline-marker.pending {
                        background-color: #f59e0b;
                        border-color: #f59e0b;
                    }
                    .timeline-card {
                        background-color: #ffffff;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        padding: 16px;
                        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                    }
                    .timeline-card-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 12px;
                        border-bottom: 1px solid #f3f4f6;
                        padding-bottom: 8px;
                    }
                    .timeline-stage-title {
                        font-weight: 600;
                        font-size: 1rem;
                        color: #111827;
                        margin: 0;
                    }
                    .timeline-tracking-stage {
                        font-size: 0.85rem;
                        color: #6b7280;
                        background-color: #f3f4f6;
                        padding: 2px 8px;
                        border-radius: 4px;
                    }
                    .timeline-details-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 16px;
                    }
                    .detail-group {
                        display: flex;
                        flex-direction: column;
                    }
                    .detail-label {
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        color: #6b7280;
                        margin-bottom: 4px;
                    }
                    .detail-value {
                        font-size: 0.875rem;
                        color: #374151;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .detail-value i {
                        font-size: 1.1em;
                        color: #9ca3af;
                    }
                </style>

                <div class="custody-timeline">
                    @foreach($custodyRecords as $custody)
                        @php
                            $isCompleted = !empty($custody->moved_out_date);
                        @endphp
                        <div class="timeline-event">
                            <div class="timeline-marker {{ $isCompleted ? 'completed' : 'pending' }}"></div>
                            <div class="timeline-card">
                                <div class="timeline-card-header">
                                    <h6 class="timeline-stage-title">
                                        {{ $custody->workflow_stage ?? 'Unknown Stage' }}
                                    </h6>
                                    @if($custody->tracking_stage)
                                        <span class="timeline-tracking-stage">
                                            {{ $custody->tracking_stage->name }}
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="timeline-details-grid">
                                    <div class="detail-group">
                                        <span class="detail-label">Started</span>
                                        <span class="detail-value">
                                            <i class="mdi mdi-account-arrow-right"></i> {{ $custody->started_by->name ?? 'System' }}
                                            <span class="text-muted mx-1">&bull;</span>
                                            <i class="mdi mdi-calendar-clock"></i> {{ optional($custody->created_at)->format('Y-m-d H:i') ?? '-' }}
                                        </span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Completed</span>
                                        <span class="detail-value">
                                            @if($isCompleted)
                                                <i class="mdi mdi-account-check text-success"></i> 
                                                {{ $custody->completed_by->name ?? 'System' }}
                                                <span class="text-muted mx-1">&bull;</span>
                                                <i class="mdi mdi-calendar-check text-success"></i> 
                                                {{ $custody->moved_out_date }}
                                            @else
                                                <i class="mdi mdi-timer-sand text-warning"></i> 
                                                <span class="text-warning">In Progress</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $custodyRecords->firstItem() ?? 0 }} to {{ $custodyRecords->lastItem() ?? 0 }} of {{ $custodyRecords->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $custodyRecords->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Workflow Stage</th>
                                <th>Tracking Stage</th>
                                <th>Started By</th>
                                <th>Start Date</th>
                                <th>Completed By</th>
                                <th>Complete Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5 workflow-empty-state">
                                    <i class="mdi mdi-link-variant-off text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Chain of Custody Records</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No records match your search criteria
                                        @else
                                            There are no chain of custody records to display
                                        @endif
                                    </small></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
