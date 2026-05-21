<div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-2">
        @can('registry.components.requests.add')
        <a href="{{ route('registry.requests.create') }}" class="btn btn-primary btn-sm">Create Request</a>
        @endcan
        @can('registry.components.approval queue.view')
        <a href="{{ route('registry.approvals.index') }}" class="btn btn-outline-primary btn-sm">Pending Approvals</a>
        @endcan
        @can('registry.components.correspondence register.view')
        <a href="{{ route('registry.correspondence.index') }}" class="btn btn-outline-secondary btn-sm">Correspondence Register</a>
        @endcan
    </div>
</div>
