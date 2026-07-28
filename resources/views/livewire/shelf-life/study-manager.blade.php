<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                Shelf Life Studies
                            </h2>
                            <p class="text-muted mb-0">Manage stability studies, pull schedules, and time-series results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($unassignedShelfLifeJobs > 0)
        <div class="alert alert-warning">
            {{ $unassignedShelfLifeJobs }} shelf-life job(s) are waiting for study assignment. Open a study linked to the job batch to configure it.
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Code, title, job number, customer…">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="statusFilter" class="form-control">
                                <option value="">All statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Study type</label>
                            <select wire:model.live="studyTypeFilter" class="form-control">
                                <option value="">All types</option>
                                @foreach($studyTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Job</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Storage</th>
                                    <th>Pulls</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studies as $study)
                                    <tr>
                                        <td><code>{{ $study->code }}</code></td>
                                        <td>{{ $study->title }}</td>
                                        <td>{{ $study->sampleHeader?->batch_code ?? '—' }}</td>
                                        <td>{{ $study->customer?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $study->isAccelerated() ? 'warning' : 'info' }}">
                                                {{ $study->studyTypeLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $study->storageConditionDisplay() }}</td>
                                        <td>{{ $study->pullPoints->count() }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $study->status)) }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('shelf-life.studies.show', $study) }}" class="btn btn-sm btn-outline-primary">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No shelf life studies yet. Accept samples with “Shelf Life Testing” checked in the Analysis Acceptance modal.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $studies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
