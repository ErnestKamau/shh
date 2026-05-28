@props(['paginator', 'position' => 'controls'])

@if($paginator->total() > 0)
    @if($position === 'controls')
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 12px;">
            <span class="text-muted">
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} entries
            </span>
            <div class="d-flex align-items-center">
                <label for="depreciationPerPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                <select wire:model.live="perPage" id="depreciationPerPage" class="form-select form-select-sm" style="width: auto;">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-center mt-3">
            {{ $paginator->links() }}
        </div>
    @endif
@endif
