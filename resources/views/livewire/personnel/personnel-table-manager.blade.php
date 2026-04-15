<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(!$embedded)
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
            <button class="btn btn-primary btn-sm" type="button" wire:click="openAddPersonnelModal">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search name, email, department, designation...">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary w-100" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-tune"></i> Filters
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters">
                        <i class="mdi mdi-refresh"></i> Clear
                    </button>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">Show {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($showAdvancedFilters)
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="small text-muted">Department</label>
                        <select class="form-control" wire:model.live="departmentFilter">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Designation</label>
                        <select class="form-control" wire:model.live="designationFilter">
                            <option value="">All Designations</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation['id'] }}">{{ $designation['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">License</label>
                        <select class="form-control" wire:model.live="licenseFilter">
                            <option value="">All Licenses</option>
                            @foreach($licenses as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Employed From</label>
                        <input type="date" class="form-control" wire:model.live="employmentDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Employed To</label>
                        <input type="date" class="form-control" wire:model.live="employmentDateTo">
                    </div>
                </div>
            @endif

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Actions</th>
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
                                <td>{{ $this->personnel->firstItem() + $loop->index }}</td>
                                <td nowrap style="width: 130px;">
                                    <div class="d-flex">
                                        <a class="btn btn-outline-success btn-sm mr-1" href="{{ route('view-personnel', ['id' => $item->id]) }}" title="View">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>

                                        @if(auth()->user()->CheckDeactivatePersonnel())
                                            <button type="button" class="btn btn-outline-danger btn-sm mr-1" wire:click="openStateModal({{ $item->id }})" title="{{ $activeTab === 'active' ? 'Deactivate' : 'Activate' }} Personnel">
                                                <i class="mdi {{ $activeTab === 'active' ? 'mdi-account-lock' : 'mdi-lock-open-variant' }}"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-outline-info btn-sm" wire:click="openResetPasswordModal({{ $item->id }})" title="Reset Password">
                                            <i class="mdi mdi-key-change"></i>
                                        </button>
                                    </div>
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
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted">No personnel found for selected filters.</td>
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
    @endif

    @if($showAddPersonnelModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Personnel</h4>
                        <button type="button" class="close" wire:click="closeAddPersonnelModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="savePersonnel">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Designation <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showDesignationDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($personnelForm['designation'] !== '')
                                                    @php($selectedDesignation = collect($designations)->firstWhere('id', (int) $personnelForm['designation']))
                                                    <span class="tag-badge">
                                                        {{ $selectedDesignation['name'] ?? '' }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearDesignation"></i>
                                                    </span>
                                                @endif
                                                <input type="text" wire:model.live="designationSearch" wire:keyup="searchDesignations" class="tag-input" placeholder="{{ $personnelForm['designation'] !== '' ? '' : 'Search designation...' }}" autocomplete="off">
                                            </div>
                                            @if($showDesignationDropdown && count($filteredDesignations) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredDesignations as $item)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectDesignation({{ $item['id'] }})">
                                                            {{ $item['name'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="personnelForm.first_name" placeholder="First Name..." />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Middle Name</label>
                                        <input type="text" class="form-control" wire:model="personnelForm.middle_name" placeholder="Middle Name..." />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Last Name</label>
                                        <input type="text" class="form-control" wire:model="personnelForm.last_name" placeholder="Last Name..." />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" wire:model="personnelForm.email" placeholder="Email..." />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Phone</label>
                                        <input type="text" class="form-control" wire:model="personnelForm.phone" placeholder="Phone..." />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">ID Number/Passport No <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="personnelForm.id_number" placeholder="ID Number..." />
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Employment Date</label>
                                        <input type="date" class="form-control" wire:model="personnelForm.employment_date" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Education Level</label>
                                        <div class="tag-select-container" wire:click="$set('showEducationLevelDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($personnelForm['educational_level'] !== '')
                                                    @php($selectedEducation = collect($educationLevels)->firstWhere('id', (int) $personnelForm['educational_level']))
                                                    <span class="tag-badge">
                                                        {{ $selectedEducation['name'] ?? '' }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearEducationLevel"></i>
                                                    </span>
                                                @endif
                                                <input type="text" wire:model.live="educationLevelSearch" wire:keyup="searchEducationLevels" class="tag-input" placeholder="{{ $personnelForm['educational_level'] !== '' ? '' : 'Search education level...' }}" autocomplete="off">
                                            </div>
                                            @if($showEducationLevelDropdown && count($filteredEducationLevels) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredEducationLevels as $item)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectEducationLevel({{ $item['id'] }})">
                                                            {{ $item['name'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Position <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showPositionDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($personnelForm['position'] !== '')
                                                    @php($selectedPosition = collect($positions)->firstWhere('id', (int) $personnelForm['position']))
                                                    <span class="tag-badge">
                                                        {{ $selectedPosition['name'] ?? '' }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearPosition"></i>
                                                    </span>
                                                @endif
                                                <input type="text" wire:model.live="positionSearch" wire:keyup="searchPositions" class="tag-input" placeholder="{{ $personnelForm['position'] !== '' ? '' : 'Search position...' }}" autocomplete="off">
                                            </div>
                                            @if($showPositionDropdown && count($filteredPositions) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredPositions as $item)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectPosition({{ $item['id'] }})">
                                                            {{ $item['name'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Department <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showDepartmentDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($personnelForm['department'] !== '')
                                                    @php($selectedDepartment = collect($departments)->firstWhere('id', (int) $personnelForm['department']))
                                                    <span class="tag-badge">
                                                        {{ $selectedDepartment['name'] ?? '' }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearDepartmentInput"></i>
                                                    </span>
                                                @endif
                                                <input type="text" wire:model.live="departmentSearchInput" wire:keyup="searchDepartmentsInput" class="tag-input" placeholder="{{ $personnelForm['department'] !== '' ? '' : 'Search department...' }}" autocomplete="off">
                                            </div>
                                            @if($showDepartmentDropdown && count($filteredDepartments) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredDepartments as $item)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectDepartmentInput({{ $item['id'] }})">
                                                            {{ $item['name'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Zone</label>
                                        <select class="form-control" wire:model="personnelForm.zone_id">
                                            <option value="">Select zone</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone['id'] }}">{{ $zone['key'] }}{{ $zone['value'] ? ' - '.$zone['value'] : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Lab Section</label>
                                        <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)">
                                            <div class="tag-select-input">
                                                @foreach(($personnelForm['lab_section_id'] ?? []) as $selectedStageId)
                                                    @php($selectedStage = collect($stages)->firstWhere('id', (int) $selectedStageId))
                                                    @if($selectedStage)
                                                        <span class="tag-badge">
                                                            {{ $selectedStage['name'] }}
                                                            <i class="mdi mdi-close-circle" wire:click.stop="removeLabSectionSelection({{ (int) $selectedStageId }})"></i>
                                                        </span>
                                                    @endif
                                                @endforeach
                                                <input type="text" wire:model.live="labSectionSearch" wire:keyup="searchLabSections" class="tag-input" placeholder="Search and select lab sections..." autocomplete="off">
                                            </div>
                                            @if($showLabSectionDropdown && count($filteredLabSections) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredLabSections as $stage)
                                                        <div class="tag-dropdown-item d-flex justify-content-between align-items-center" wire:click.stop="toggleLabSectionSelection({{ $stage['id'] }})">
                                                            <span>{{ $stage['name'] }}</span>
                                                            @if(in_array($stage['id'], $personnelForm['lab_section_id'] ?? [], true))
                                                                <i class="mdi mdi-check text-success"></i>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">User License <span class="text-danger">*</span></label>
                                        <div class="tag-select-container" wire:click="$set('showLicenseDropdown', true)">
                                            <div class="tag-select-input">
                                                @if($personnelForm['user_license'] !== '')
                                                    @php($selectedLicenseName = $licenses[$personnelForm['user_license']] ?? '')
                                                    <span class="tag-badge">
                                                        {{ $selectedLicenseName }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearLicense"></i>
                                                    </span>
                                                @endif
                                                <input type="text" wire:model.live="licenseSearch" wire:keyup="searchLicenses" class="tag-input" placeholder="{{ $personnelForm['user_license'] !== '' ? '' : 'Search license...' }}" autocomplete="off">
                                            </div>
                                            @if($showLicenseDropdown && count($filteredLicenses) > 0)
                                                <div class="tag-dropdown">
                                                    @foreach($filteredLicenses as $license)
                                                        <div class="tag-dropdown-item d-flex justify-content-between align-items-center {{ $license['disabled'] ? 'text-muted' : '' }}" @if(!$license['disabled']) wire:click.stop="selectLicense('{{ $license['key'] }}')" @endif>
                                                            <span>{{ $license['name'] }}</span>
                                                            <small>{{ $license['count'] }}/{{ $license['limit'] }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">
                                            <input type="checkbox" wire:model="personnelForm.active" /> Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="savePersonnel"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeAddPersonnelModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showStateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="mdi mdi-account-lock"></i> Update {{ $selectedPersonnelName }} State</h3>
                        <button type="button" class="close" wire:click="closeStateModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">State</label>
                            <select class="form-control" wire:model="stateAction">
                                <option value="active">Activate</option>
                                <option value="deactive">Deactivate</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="savePersonnelState"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeStateModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showResetPasswordModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="mdi mdi-key-change"></i> Reset {{ $selectedPersonnelName }} Password</h3>
                        <button type="button" class="close" wire:click="closeResetPasswordModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Password</label>
                            <input type="password" class="form-control" wire:model="newPassword">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Confirm Password</label>
                            <input type="password" class="form-control" wire:model="confirmPassword">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="resetPersonnelPassword"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeResetPasswordModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .tag-select-input:hover {
            border-color: #007bff;
        }

        .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #007bff;
            color: #fff;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .tag-badge i:hover {
            opacity: 1;
        }

        .tag-input {
            flex: 1;
            min-width: 140px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
        }

        .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 260px;
            overflow-y: auto;
            z-index: 1060;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -2px;
        }

        .tag-dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .tag-dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .tag-dropdown-item:last-child {
            border-bottom: none;
        }
    </style>

    @script
    <script>
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.tag-select-container')) {
                $wire.closeAddModalDropdowns();
            }
        });
    </script>
    @endscript
</div>
