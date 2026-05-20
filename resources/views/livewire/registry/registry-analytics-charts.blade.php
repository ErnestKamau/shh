<div class="card mb-3">
    <div class="card-header">Requests by Category</div>
    <div class="card-body">
        @forelse($byCategory as $row)
            <div class="d-flex justify-content-between border-bottom py-1">
                <span>{{ $row['category'] }}</span>
                <strong>{{ $row['total'] }}</strong>
            </div>
        @empty
            <p class="text-muted mb-0">No data.</p>
        @endforelse
    </div>
</div>
