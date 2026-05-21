@if(auth()->user()?->can('registry.module.access'))
<div class="col-12 mb-4"><div class="card"><div class="card-header bg-primary text-white">Corporate Services</div><div class="card-body">
<div class="row text-center mb-3">@foreach(['open'=>'Open','pending_approval'=>'Pending Approvals','delayed'=>'Delayed','received_today'=>'Today'] as $k=>$l)<div class="col-3"><small>{{ $l }}</small><h5>{{ $kpis[$k] ?? 0 }}</h5></div>@endforeach</div>
<div class="d-flex flex-wrap gap-2 mb-2"><a href="{{ route('registry.dashboard') }}" class="btn btn-sm btn-outline-primary">Dashboard</a>@can('registry.components.requests.add')<a href="{{ route('registry.requests.create') }}" class="btn btn-sm btn-primary">New Request</a>@endcan</div>
<ul class="list-unstyled mb-0">@foreach($recent as $r)<li><a href="{{ route('registry.requests.show', $r->id) }}">{{ $r->reference_no }}</a> — {{ Str::limit($r->subject, 40) }}</li>@endforeach</ul>
</div></div></div>
@endif
