<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <h2 class="p-4">
        @if (isset($this->user->photo) && $this->user->photo != '')
            <img src="{{ $this->user->photo }}" style="width: 100px" />
        @else
            <i class="mdi mdi-account"></i>
        @endif
        {{ $this->user->name }} | <small class="text-muted">Profile</small>
    </h2>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" wire:click="setActiveTab('roles')">Roles</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" wire:click="setActiveTab('details')">User Details</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'work_history' ? 'active' : '' }}" wire:click="setActiveTab('work_history')">Work History</button></li>
            </ul>
        </div>
        <div class="tab-content p-3">
            @if($activeTab === 'roles')
                <h5 class="card-title">
                    Roles
                    <button class="btn btn-outline-primary btn-sm float-right" type="button" wire:click="openAddRoleModal"><i class="mdi mdi-key-plus"></i></button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead><tr><th>#</th><th>Name</th><th>Description</th><th></th></tr></thead>
                        <tbody>
                            @foreach($this->user->roles as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->role->name }}</td>
                                    <td>{{ $item->role->description }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteRoleModal({{ $item->id }})" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($activeTab === 'details')
                <form autocomplete="off" action="{{ route('add-personnel', ['id'=>$this->user->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h5 class="card-title">User Details <button class="btn btn-outline-primary btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button></h5>
                    <input type="hidden" name="designation" value="{{ $selectedDesignationId }}">
                    <input type="hidden" name="educational_level" value="{{ $selectedEducationId }}">
                    <input type="hidden" name="position" value="{{ $selectedPositionId }}">
                    <input type="hidden" name="department" value="{{ $selectedDepartmentId }}">
                    <input type="hidden" name="user_license" value="{{ $selectedLicenseKey }}">
                    @foreach($selectedLabSectionIds as $labSectionId)
                        <input type="hidden" name="lab_section_id[]" value="{{ $labSectionId }}">
                    @endforeach

                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>First Name *</label><input name="first_name" class="form-control" value="{{ $this->user->first_name }}" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Middle Name</label><input name="middle_name" class="form-control" value="{{ $this->user->middle_name }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Last Name</label><input name="last_name" class="form-control" value="{{ $this->user->last_name }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" value="{{ $this->user->email }}" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ $this->user->phone }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>ID Number *</label><input name="id_number" class="form-control" value="{{ $this->user->id_number }}" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ $this->user->date_of_birth }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Employment Date</label><input type="date" name="employment_date" class="form-control" value="{{ $this->user->employment_date }}"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label>Designation *</label>
                            <div class="tag-select-container" wire:click="$set('showDesignationDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedDesignationId) @php($s = $this->designations->firstWhere('id',$selectedDesignationId)) <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDesignation"></i></span> @endif
                                    <input class="tag-input" wire:model.live="designationSearch" wire:keyup="searchDesignation" placeholder="Search designation...">
                                </div>
                                @if($showDesignationDropdown)
                                    <div class="tag-dropdown">@foreach($this->designations->filter(fn($d)=>$designationSearch===''||stripos($d->name,$designationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDesignation({{ $d->id }})">{{ $d->name }}</div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Education Level</label>
                            <div class="tag-select-container" wire:click="$set('showEducationDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedEducationId) @php($s = $this->educationLevels->firstWhere('id',$selectedEducationId)) <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearEducation"></i></span> @endif
                                    <input class="tag-input" wire:model.live="educationSearch" wire:keyup="searchEducation" placeholder="Search education...">
                                </div>
                                @if($showEducationDropdown)
                                    <div class="tag-dropdown">@foreach($this->educationLevels->filter(fn($d)=>$educationSearch===''||stripos($d->name,$educationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectEducation({{ $d->id }})">{{ $d->name }}</div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Position *</label>
                            <div class="tag-select-container" wire:click="$set('showPositionDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedPositionId) @php($s = $this->positions->firstWhere('id',$selectedPositionId)) <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearPosition"></i></span> @endif
                                    <input class="tag-input" wire:model.live="positionSearch" wire:keyup="searchPosition" placeholder="Search position...">
                                </div>
                                @if($showPositionDropdown)
                                    <div class="tag-dropdown">@foreach($this->positions->filter(fn($d)=>$positionSearch===''||stripos($d->name,$positionSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectPosition({{ $d->id }})">{{ $d->name }}</div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Department *</label>
                            <div class="tag-select-container" wire:click="$set('showDepartmentDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedDepartmentId) @php($s = $this->departments->firstWhere('id',$selectedDepartmentId)) <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDepartment"></i></span> @endif
                                    <input class="tag-input" wire:model.live="departmentSearch" wire:keyup="searchDepartment" placeholder="Search department...">
                                </div>
                                @if($showDepartmentDropdown)
                                    <div class="tag-dropdown">@foreach($this->departments->filter(fn($d)=>$departmentSearch===''||stripos($d->name,$departmentSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDepartment({{ $d->id }})">{{ $d->name }}</div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>User License *</label>
                            <div class="tag-select-container" wire:click="$set('showLicenseDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedLicenseKey) <span class="tag-badge">{{ getUserLicenses()[$selectedLicenseKey] ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearLicense"></i></span> @endif
                                    <input class="tag-input" wire:model.live="licenseSearch" wire:keyup="searchLicense" placeholder="Search license...">
                                </div>
                                @if($showLicenseDropdown)
                                    <div class="tag-dropdown">
                                        @foreach(getUserLicenses() as $k=>$n)
                                            @if($licenseSearch===''||stripos($n,$licenseSearch)!==false)
                                                <div class="tag-dropdown-item" wire:click.stop="selectLicense('{{ $k }}')">{{ $n }} {{ ($this->licenseCount[$k] ?? 0).'/'.mamboSawa($k.'s') }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Lab Section</label>
                            <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)">
                                <div class="tag-select-input">
                                    @foreach($selectedLabSectionIds as $sid) @php($s=$this->stages->firstWhere('id',$sid)) @if($s)<span class="tag-badge">{{ $s->name }}<i class="mdi mdi-close-circle" wire:click.stop="removeLabSection({{ $sid }})"></i></span>@endif @endforeach
                                    <input class="tag-input" wire:model.live="labSectionSearch" wire:keyup="searchLabSection" placeholder="Search lab section...">
                                </div>
                                @if($showLabSectionDropdown)
                                    <div class="tag-dropdown">@foreach($this->stages->filter(fn($d)=>$labSectionSearch===''||stripos($d->name,$labSectionSearch)!==false) as $s)<div class="tag-dropdown-item d-flex justify-content-between" wire:click.stop="toggleLabSection({{ $s->id }})"><span>{{ $s->name }}</span>@if(in_array($s->id,$selectedLabSectionIds,true))<i class="mdi mdi-check text-success"></i>@endif</div>@endforeach</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            @if($activeTab === 'work_history')
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead><tr><th>#</th><th>Department</th><th>Job Description</th><th>Start Date</th><th>End Date</th></tr></thead>
                        <tbody>
                            @foreach($this->user->work_history() as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->department_name }}</td>
                                    <td>{{ $item->position }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td>{!! trim($item->end_date) != '' ? $item->end_date : '<i class="mdi mdi-check-circle text-success"></i> Current' !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($showAddRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h4 class="modal-title"><i class="mdi mdi-key-plus"></i> Add User Role</h4><button type="button" class="close" wire:click="closeAddRoleModal"><span>&times;</span></button></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Roles</label>
                            <div class="tag-select-container" wire:click="$set('showRoleDropdown', true)">
                                <div class="tag-select-input">
                                    @foreach($selectedRoleIds as $roleId)
                                        @php($role = $this->allRoles->firstWhere('id', $roleId))
                                        @if($role)
                                            <span class="tag-badge">{{ $role->name }}<i class="mdi mdi-close-circle" wire:click.stop="removeSelectedRole({{ $roleId }})"></i></span>
                                        @endif
                                    @endforeach
                                    <input class="tag-input" wire:model.live="roleSearch" wire:keyup="searchRoles" placeholder="Search roles..." autocomplete="off">
                                </div>
                                @if($showRoleDropdown)
                                    <div class="tag-dropdown">
                                        @foreach($this->allRoles->filter(fn($r) => $roleSearch === '' || stripos($r->name, $roleSearch) !== false) as $role)
                                            <div class="tag-dropdown-item d-flex justify-content-between" wire:click.stop="toggleRoleSelection({{ $role->id }})">
                                                <span>{{ $role->name }}</span>
                                                @if(in_array($role->id, $selectedRoleIds, true))<i class="mdi mdi-check text-success"></i>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary" wire:click="addSelectedRoles"><i class="mdi mdi-content-save"></i> Save</button><button type="button" class="btn btn-default" wire:click="closeAddRoleModal">Close</button></div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> Confirm Delete</h4>
                        <button type="button" class="close" wire:click="closeDeleteRoleModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            Are you sure you want to remove role <strong>{{ $selectedRoleName }}</strong> from this user?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="removeRole"><i class="mdi mdi-delete"></i> Delete</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteRoleModal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .tag-select-container { position: relative; cursor: text; }
        .tag-select-input { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: 42px; padding: 6px 12px; background: #fff; border: 2px solid #e0e0e0; border-radius: 8px; }
        .tag-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background-color: #007bff; color: #fff; border-radius: 16px; font-size: 0.875rem; }
        .tag-input { flex: 1; min-width: 120px; border: none; outline: none; padding: 4px; font-size: 0.9rem; }
        .tag-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 2px solid #007bff; border-top: none; border-radius: 0 0 8px 8px; max-height: 250px; overflow-y: auto; z-index: 1060; }
        .tag-dropdown-item { padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
        .tag-dropdown-item:hover { background-color: #f8f9fa; }
    </style>
    @script
    <script>
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.tag-select-container')) {
                $wire.closeSelectDropdowns();
            }
        });
    </script>
    @endscript
</div>
