<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 sm-list-table-card" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title mb-0">{{ $tableTitle }}</h5>
                @if(! empty($tableSubtitle))
                    <p class="text-muted mb-0 mt-1 small">{{ $tableSubtitle }}</p>
                @endif
            </div>
            <div class="card-body p-4 pt-3">
                {{ $body }}
            </div>
        </div>
    </div>
</div>
