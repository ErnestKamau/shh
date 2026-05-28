<div class="rr-panel">
    <div class="rr-panel__head">
        <div class="rr-panel__icon"><i class="mdi mdi-timeline-clock-outline"></i></div>
        <div>
            <h3 class="rr-panel__title">Workflow Timeline</h3>
            <p class="rr-panel__sub">{{ $request->actions->count() }} recorded {{ Str::plural('action', $request->actions->count()) }}</p>
        </div>
    </div>
    <div class="rr-panel__body rr-panel__body--flush">
        @if($request->actions->isEmpty())
            <div class="rr-empty-state">
                <div class="rr-empty-state__icon"><i class="mdi mdi-timeline-outline"></i></div>
                <p class="rr-empty-state__text">No workflow actions recorded yet.</p>
            </div>
        @else
            <ul class="rr-timeline">
                @foreach($request->actions as $action)
                    @php
                        $actionIcon = match (strtolower((string) $action->action_type)) {
                            'approve', 'approved' => 'mdi-check-circle-outline',
                            'reject', 'return', 'returned' => 'mdi-arrow-u-left-top',
                            'assign', 'assigned' => 'mdi-account-arrow-right-outline',
                            'submit', 'submitted' => 'mdi-send-outline',
                            default => 'mdi-circle-medium',
                        };
                        $actionTone = match (strtolower((string) $action->action_type)) {
                            'approve', 'approved' => 'rr-timeline__dot--success',
                            'reject', 'return', 'returned' => 'rr-timeline__dot--warning',
                            'assign', 'assigned' => 'rr-timeline__dot--info',
                            default => 'rr-timeline__dot--neutral',
                        };
                    @endphp
                    <li class="rr-timeline__item">
                        <div class="rr-timeline__dot {{ $actionTone }}">
                            <i class="mdi {{ $actionIcon }}"></i>
                        </div>
                        <div class="rr-timeline__content">
                            <div class="rr-timeline__header">
                                <span class="rr-timeline__type">{{ ucwords(str_replace('_', ' ', $action->action_type)) }}</span>
                                <time class="rr-timeline__time">{{ $action->performed_at?->format('M j, Y · H:i') }}</time>
                            </div>
                            @if($action->from_stage || $action->to_stage)
                                <p class="rr-timeline__stages">
                                    @if($action->from_stage)
                                        <span>{{ $action->from_stage }}</span>
                                        <i class="mdi mdi-arrow-right"></i>
                                    @endif
                                    <span>{{ $action->to_stage ?: '—' }}</span>
                                </p>
                            @endif
                            @if($action->performer)
                                <p class="rr-timeline__actor">
                                    <i class="mdi mdi-account-outline"></i>
                                    {{ $action->performer->name }}
                                </p>
                            @endif
                            @if($action->comment)
                                <blockquote class="rr-timeline__comment">{{ $action->comment }}</blockquote>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
