<div class="card mb-3"><div class="card-header">Assignments</div><div class="card-body">
<form wire:submit="assign" class="form-inline mb-3"><select wire:model="assigned_to" class="form-control mr-2"><option value="">Select user...</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select><input wire:model="role_context" class="form-control mr-2" placeholder="Role context"><button class="btn btn-primary btn-sm">Assign</button></form>
<ul class="list-group">@foreach($request->assignments as $a)<li class="list-group-item">{{ $a->assignee?->name }} @if($a->is_active)<span class="badge badge-success">Active</span>@endif</li>@endforeach</ul>
</div></div>
