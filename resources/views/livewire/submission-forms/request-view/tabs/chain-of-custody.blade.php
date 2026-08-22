<div class="workflow-board-panel-body flush-top px-0">
    @if($custodyEnteredLab ?? false)
        <div class="alert alert-info mx-3 mt-3 mb-0 small">
            Samples have entered the laboratory. Custody tracking continues on the linked batch view.
        </div>
    @endif

    <div class="px-3 pb-3">
        @include('layouts.lab.partials.ls-ui.timeline.ls-custody-timeline', [
            'events' => $custodyTimeline,
            'emptyMessage' => 'No custody or audit events recorded yet.',
            'rootClass' => 'ls-custody-timeline request-custody-timeline',
        ])
    </div>
</div>
