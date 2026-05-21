<div class="card mb-3"><div class="card-header">Workflow Timeline</div><ul class="list-group list-group-flush">
@foreach($request->actions as $action)<li class="list-group-item"><strong>{{ $action->action_type }}</strong> {{ $action->from_stage }} → {{ $action->to_stage }}<br><small>{{ $action->performer?->name }} — {{ $action->performed_at?->format('Y-m-d H:i') }}</small>@if($action->comment)<br><em>{{ $action->comment }}</em>@endif</li>@endforeach
</ul></div>
