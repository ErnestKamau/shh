<div class="workflow-board-panel-body flush-top px-0">
    @if($custodyTimeline->isEmpty())
        <p class="text-muted mb-0 py-3">No custody or audit events recorded yet.</p>
    @else
        
        
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($custodyTimeline as $event)
                        <tr>
                            <td class="text-capitalize">{{ $event->source }}</td>
                            <td>
                                {{ $event->title }}
                                @if($event->subtitle)
                                    <br><small class="text-muted">{{ $event->subtitle }}</small>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $event->user_name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $event->occurred_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $event->comment ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
