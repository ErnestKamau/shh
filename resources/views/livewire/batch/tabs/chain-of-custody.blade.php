<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-sitemap"></i> Chain of custody</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="perPage" class="form-label mb-0 me-2 text-muted small">Show</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; border-radius: 6px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <div class="mb-3 px-3 pt-3">
                <input type="text"
                       wire:model.live="search"
                       class="form-control"
                       placeholder="Search by stage, user, worksheet, comments, or workflow activity…">
            </div>

            <div class="px-3 pb-3">
                @include('layouts.lab.partials.ls-ui.timeline.ls-custody-timeline', [
                    'events' => $custodyRecords->getCollection(),
                    'emptyMessage' => $search
                        ? 'No records match your search criteria.'
                        : 'There are no chain of custody records to display.',
                ])
            </div>

            @if($custodyRecords->total() > 0)
                <div class="d-flex justify-content-between align-items-center mt-1 px-3 pb-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $custodyRecords->firstItem() ?? 0 }} to {{ $custodyRecords->lastItem() ?? 0 }} of {{ $custodyRecords->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $custodyRecords->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
