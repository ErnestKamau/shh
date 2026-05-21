<div class="solution-preparation-manager container-fluid py-3">
    <div class="scd-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="scd-eyebrow mb-1">Stock monitoring · Preparation tracking</p>
                <h2 class="scd-title mb-0">Solution preparations</h2>
            </div>
            <a href="{{ route('solutions-preparation-create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus"></i> New preparation
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control scd-input" placeholder="Search prep # or batch...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-control scd-input">
                        <option value="">All statuses</option>
                        <option value="preparing">Preparing</option>
                        <option value="awaiting_approval">Awaiting approval</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="solutionFilter" class="form-control scd-input">
                        <option value="">All solutions</option>
                        @foreach($this->solutions as $sol)
                            <option value="{{ $sol->id }}">{{ $sol->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 scd-table">
                <thead class="table-light">
                    <tr>
                        <th>Preparation #</th>
                        <th>Solution</th>
                        <th>Status</th>
                        <th>Prepared</th>
                        <th>Qty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->preparations as $prep)
                        <tr>
                            <td><strong>{{ $prep->preparation_number }}</strong></td>
                            <td>{{ $prep->solution?->name }}</td>
                            <td>
                                @php
                                    $statusClass = match($prep->status) {
                                        'preparing' => 'scd-status--preparing',
                                        'awaiting_approval' => 'scd-status--awaiting',
                                        'completed' => 'scd-status--completed',
                                        default => 'scd-status--cancelled',
                                    };
                                @endphp
                                <span class="scd-status {{ $statusClass }}">{{ str_replace('_', ' ', $prep->status) }}</span>
                            </td>
                            <td>{{ $prep->prepared_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $prep->quantity_prepared }} {{ $prep->solution?->reportingUnit?->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('solutions-preparation-show', $prep->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-eye"></i> Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No preparations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $this->preparations->links() }}</div>
    </div>
    @include('livewire.lab.partials.scd-styles')
</div>
