<div class="mt-4">
    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'active' ? 'active' : '' }}" wire:click="setActiveTab('active')">
                        <i class="mdi mdi-account"></i> Active Personnel
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'deactive' ? 'active' : '' }}" wire:click="setActiveTab('deactive')">
                        <i class="mdi mdi-account-lock"></i> Deactivated Personnel
                    </button>
                </li>
            </ul>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add-personnel">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search name, email, department, designation...">
                </div>
                <div class="col-md-3 ml-auto">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">Show {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Designation</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Department</th>
                            <th>JD</th>
                            <th>Lab Sections</th>
                            <th>Email</th>
                            <th>Employment Date</th>
                            <th>License Type</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->personnel as $item)
                            <tr>
                                <td nowrap style="width: 100px;">
                                    {{ $this->personnel->firstItem() + $loop->index }}
                                    <a class="btn btn-success btn-sm" href="{{ route('view-personnel', ['id' => $item->id]) }}" title="View">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>

                                    @if(auth()->user()->CheckDeactivatePersonnel())
                                        <span class="btn btn-danger btn-sm" data-toggle="modal" data-target="#lock-user-{{ $item->id }}" title="{{ $activeTab === 'active' ? 'Deactivate' : 'Activate' }} Personnel">
                                            <i class="mdi {{ $activeTab === 'active' ? 'mdi-account-lock' : 'mdi-lock-open-variant' }}"></i>
                                        </span>
                                    @endif

                                    <span class="btn btn-info btn-sm" data-toggle="modal" data-target="#reset-password-{{ $item->id }}" title="Reset Password">
                                        <i class="mdi mdi-key-change"></i>
                                    </span>
                                </td>
                                <td>{{ $item->designation }}</td>
                                <td>{{ $item->first_name }}</td>
                                <td>{{ $item->middle_name }}</td>
                                <td>{{ $item->last_name }}</td>
                                <td>{{ $item->department_name }}</td>
                                <td>{{ $item->position }}</td>
                                <td>{{ $item->labsectionname }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->employment_date }}</td>
                                <td>{{ $item->license_type }}</td>
                                <td class="text-small">{!! $item->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                            </tr>

                            <div id="lock-user-{{ $item->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <form class="modal-content" method="POST" action="{{ route('personnel-state', ['id' => $item->id]) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h3 class="modal-title">
                                                <i class="mdi mdi-account-lock"></i>
                                                {{ $item->active == 1 ? 'Deactivate' : 'Activate' }} {{ $item->first_name }} {{ $item->last_name }}
                                            </h3>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="control-label">State</label>
                                                <select name="state" class="form-control">
                                                    <option value="active" {{ $item->active == 1 ? 'selected' : '' }}>Activate</option>
                                                    <option value="deactive" {{ $item->active == 0 ? 'selected' : '' }}>Deactivate</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div id="reset-password-{{ $item->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <form action="{{ route('reset-personnel', ['id' => $item->id]) }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h3 class="modal-title">
                                                <i class="mdi mdi-key-change"></i> Reset {{ $item->first_name }} {{ $item->last_name }} Password
                                            </h3>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="control-label">Password</label>
                                                <input type="password" class="form-control" required name="password">
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Confirm Password</label>
                                                <input type="password" class="form-control" required name="con_password">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No personnel found for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Showing {{ $this->personnel->firstItem() ?? 0 }} to {{ $this->personnel->lastItem() ?? 0 }} of {{ $this->personnel->total() }} records
                </small>
                {{ $this->personnel->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
