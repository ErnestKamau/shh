@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
@endphp

@if($paginator->total() > 0)
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap sm-list-pagination" style="gap: 12px;">
                <span class="text-muted small">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }}
                    of {{ $paginator->total() }} {{ $entryLabel ?? 'entries' }}
                </span>
                <div>
                    {{ $paginator->links() }}
                </div>
            </div>
        </div>
    </div>
@endif
