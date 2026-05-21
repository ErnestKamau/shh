<div class="card mb-3">
    <div class="card-header">Pending Tasks</div>
    <ul class="list-group list-group-flush">
        @forelse($pending as $item)
            <li class="list-group-item d-flex justify-content-between">
                <a href="{{ route('registry.requests.show', $item->id) }}">{{ $item->reference_no }}</a>
                <span class="badge badge-warning">{{ $item->status }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">No pending tasks.</li>
        @endforelse
    </ul>
</div>
