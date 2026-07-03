<div class="workflow-board-panel-body flush-top px-0">
    @if($custodyEnteredLab ?? false)
        <div class="alert alert-info mx-3 mt-3 mb-0 small">
            Samples have entered the laboratory. Custody tracking continues on the linked batch view.
        </div>
    @endif

    @if($custodyTimeline->isEmpty())
        <p class="text-muted mb-0 py-3 px-3">No custody or audit events recorded yet.</p>
    @else
        <style>
            .request-custody-timeline {
                position: relative;
                padding-left: 30px;
                margin: 20px;
            }
            .request-custody-timeline::before {
                content: '';
                position: absolute;
                left: 7px;
                top: 0;
                bottom: 0;
                width: 2px;
                background-color: #e5e7eb;
            }
            .request-custody-timeline .timeline-event {
                position: relative;
                margin-bottom: 1.5rem;
            }
            .request-custody-timeline .timeline-event:last-child {
                margin-bottom: 0;
            }
            .request-custody-timeline .timeline-marker {
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
            .request-custody-timeline .timeline-marker.completed {
                background-color: #10b981;
                border-color: #10b981;
            }
            .request-custody-timeline .timeline-marker.pending {
                background-color: #f59e0b;
                border-color: #f59e0b;
            }
            .request-custody-timeline .timeline-card {
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 16px;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }
            .request-custody-timeline .timeline-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
                border-bottom: 1px solid #f3f4f6;
                padding-bottom: 8px;
                gap: 12px;
            }
            .request-custody-timeline .timeline-stage-title {
                font-weight: 600;
                font-size: 1rem;
                color: #111827;
                margin: 0;
            }
            .request-custody-timeline .timeline-tracking-stage {
                font-size: 0.85rem;
                color: #6b7280;
                background-color: #f3f4f6;
                padding: 2px 8px;
                border-radius: 4px;
                white-space: nowrap;
            }
            .request-custody-timeline .timeline-details-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
            }
            @media (min-width: 768px) {
                .request-custody-timeline .timeline-details-grid {
                    grid-template-columns: 1fr 1fr;
                }
            }
            .request-custody-timeline .detail-group {
                display: flex;
                flex-direction: column;
            }
            .request-custody-timeline .detail-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #6b7280;
                margin-bottom: 4px;
            }
            .request-custody-timeline .detail-value {
                font-size: 0.875rem;
                color: #374151;
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            .request-custody-timeline .detail-value i {
                font-size: 1.1em;
                color: #9ca3af;
            }
        </style>

        <div class="request-custody-timeline">
            @foreach($custodyTimeline as $event)
                <div class="timeline-event">
                    <div class="timeline-marker {{ $event->badge === 'success' ? 'completed' : 'pending' }}"></div>
                    <div class="timeline-card">
                        <div class="timeline-card-header">
                            <h6 class="timeline-stage-title">{{ $event->title }}</h6>
                            @if($event->subtitle)
                                <span class="timeline-tracking-stage">{{ $event->subtitle }}</span>
                            @endif
                        </div>

                        <div class="timeline-details-grid">
                            <div class="detail-group">
                                <span class="detail-label">User</span>
                                <span class="detail-value">
                                    <i class="mdi mdi-account-arrow-right"></i>
                                    {{ $event->user_name ?? '—' }}
                                    <span class="text-muted mx-1">&bull;</span>
                                    <i class="mdi mdi-calendar-clock"></i>
                                    {{ $event->occurred_at?->format('Y-m-d H:i') ?? '—' }}
                                </span>
                            </div>

                            @if($event->comment)
                                <div class="detail-group">
                                    <span class="detail-label">Comments</span>
                                    <span class="detail-value text-muted">
                                        <i class="mdi mdi-comment-text-outline"></i> {{ $event->comment }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
