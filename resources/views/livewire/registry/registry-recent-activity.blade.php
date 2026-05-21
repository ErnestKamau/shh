<div class="card">
    <div class="card-header">Recent Activity</div>
    <ul class="list-group list-group-flush">
        @forelse($activities as $item)
            <li class="list-group-item">
                <a href="{{ route('registry.requests.show', $item->id) }}">{{ $item->reference_no }}</a>
                — {{ $item->subject }}
                <small class="text-muted float-right">{{ $item->updated_at?->diffForHumans() }}</small>
            </li>
        @empty
            <li class="list-group-item text-muted">No recent activity.</li>
        @endforelse
    </ul>
</div>
