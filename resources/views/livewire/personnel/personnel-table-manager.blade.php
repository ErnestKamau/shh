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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-group text-primary"></i>
                                {{ __('personnel.personnel_module') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.personnel_overview') }}</p>
                        </div>
                        <button class="btn btn-outline-primary" type="button" wire:click="openAddPersonnelModal">
                            <i class="mdi mdi-plus"></i> {{ __('personnel.add_personnel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'active' ? 'active' : '' }}" wire:click="setActiveTab('active')">
                        <i class="mdi mdi-account"></i> {{ __('personnel.active_personnel_tab') }}
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'deactive' ? 'active' : '' }}" wire:click="setActiveTab('deactive')">
                        <i class="mdi mdi-account-lock"></i> {{ __('personnel.deactivated_personnel_tab') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="mb-3 ptm-toolbar-strip-wrap">
                <div class="ptm-toolbar-strip d-flex align-items-center flex-nowrap">
                    <div class="ptm-search-wrap">
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_personnel') }}">
                    </div>

                    <button type="button" class="btn btn-success ptm-btn-export" wire:click="exportToExcel">
                        <i class="mdi mdi-file-excel-outline"></i> Export to Excel
                    </button>

                    <span class="ptm-show-label">{{ __('personnel.show') }}</span>
                    <select class="form-control no-select2 ptm-show-select" wire:model.live="perPage" aria-label="{{ __('personnel.show') }}">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline-primary ptm-toolbar-btn" wire:click="toggleAdvancedFilters">
                        <i class="mdi mdi-tune"></i> {{ __('personnel.filters') }}
                    </button>

                    <button type="button" class="btn btn-outline-secondary ptm-toolbar-btn" wire:click="clearFilters">
                        <i class="mdi mdi-refresh"></i> {{ __('personnel.clear') }}
                    </button>
                </div>
            </div>

            @if($showAdvancedFilters)
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="small text-muted">{{ __('personnel.department') }}</label>
                        <select class="form-control" wire:model.live="departmentFilter">
                            <option value="">{{ __('personnel.all_departments') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">{{ __('personnel.designation') }}</label>
                        <select class="form-control" wire:model.live="designationFilter">
                            <option value="">{{ __('personnel.all_designations') }}</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation['id'] }}">{{ $designation['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">{{ __('personnel.license') }}</label>
                        <select class="form-control" wire:model.live="licenseFilter">
                            <option value="">{{ __('personnel.all_licenses') }}</option>
                            @foreach($licenses as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">{{ __('personnel.employed_from') }}</label>
                        <input type="date" class="form-control" wire:model.live="employmentDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">{{ __('personnel.employed_to') }}</label>
                        <input type="date" class="form-control" wire:model.live="employmentDateTo">
                    </div>
                </div>
            @endif

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>{{ __('personnel.no') }}</th>
                            <th>{{ __('personnel.actions') }}</th>
                            <th>{{ __('personnel.designation') }}</th>
                            <th>{{ __('personnel.first_name') }}</th>
                            <th>{{ __('personnel.middle_name') }}</th>
                            <th>{{ __('personnel.last_name') }}</th>
                            <th>{{ __('personnel.department') }}</th>
                            <th>{{ __('personnel.jd') }}</th>
                            <th>{{ __('personnel.lab_sections') }}</th>
                            <th>{{ __('personnel.email') }}</th>
                            <th>{{ __('personnel.employment_date') }}</th>
                            <th>{{ __('personnel.license_type') }}</th>
                            <th>{{ __('personnel.active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->personnel as $item)
                            <tr>
                                <td>{{ $this->personnel->firstItem() + $loop->index }}</td>
                                <td nowrap style="width: 130px;">
                                    <div class="d-flex">
                                        <a class="btn btn-sm rm-act-btn rm-act-btn--view" href="{{ route('view-personnel', ['id' => $item->id]) }}" title="{{ __('personnel.view') }}">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>

                                        @if(auth()->user()->CheckDeactivatePersonnel())
                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click='openStateModal(@js($item->id))' title="{{ $activeTab === 'active' ? __('personnel.deactivate_personnel') : __('personnel.activate_personnel') }}">
                                                <i class="mdi {{ $activeTab === 'active' ? 'mdi-account-lock' : 'mdi-lock-open-variant' }}"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click='openResetPasswordModal(@js($item->id))' title="{{ __('personnel.reset_password') }}">
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
                                <td colspan="13" class="text-center text-muted">{{ __('personnel.no_personnel_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    {{ __('personnel.showing_to_of', ['from' => $this->personnel->firstItem() ?? 0, 'to' => $this->personnel->lastItem() ?? 0, 'total' => $this->personnel->total()]) }}
                </small>
                {{ $this->personnel->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    @endif

    @if($showAddPersonnelModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable ptm-modern-modal-shell">
                <div class="modal-content">
                    <div class="modal-header ptm-modern-modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-account-plus-outline"></i> {{ __('personnel.add_personnel_title') }}</h4>
                        <button type="button" class="close" wire:click="closeAddPersonnelModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body ptm-modern-modal-body">
                        <div class="ptm-modal-intro mb-3">
                            <strong>{{ __('personnel.add_personnel') }}</strong>
                            <div class="text-muted small">Add personnel through a guided flow covering profile, employment, recognition, access, and signature.</div>
                        </div>
                        <form wire:submit.prevent="savePersonnel">
                            @if($errors->any())
                                <div class="alert alert-danger ptm-validation-summary">
                                    <div class="font-weight-semibold mb-1">Please resolve the highlighted form issues.</div>
                                    @foreach($errors->all() as $error)
                                        <div class="small">{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="ptm-stepper mb-4">
                                <button type="button" class="ptm-step {{ $addPersonnelStep === 1 ? 'is-active' : ($addPersonnelStep > 1 ? 'is-complete' : '') }}" wire:click="goToAddPersonnelStep(1)">
                                    <span class="ptm-step-index">1</span>
                                    <span class="ptm-step-copy">
                                        <strong>Profile</strong>
                                        <small>Identity and contact</small>
                                    </span>
                                </button>
                                <button type="button" class="ptm-step {{ $addPersonnelStep === 2 ? 'is-active' : ($addPersonnelStep > 2 ? 'is-complete' : '') }}" wire:click="goToAddPersonnelStep(2)">
                                    <span class="ptm-step-index">2</span>
                                    <span class="ptm-step-copy">
                                        <strong>Employment</strong>
                                        <small>Designation and role</small>
                                    </span>
                                </button>
                                <button type="button" class="ptm-step {{ $addPersonnelStep === 3 ? 'is-active' : ($addPersonnelStep > 3 ? 'is-complete' : '') }}" wire:click="goToAddPersonnelStep(3)">
                                    <span class="ptm-step-index">3</span>
                                    <span class="ptm-step-copy">
                                        <strong>Recognition</strong>
                                        <small>Career milestones</small>
                                    </span>
                                </button>
                                <button type="button" class="ptm-step {{ $addPersonnelStep === 4 ? 'is-active' : '' }}" wire:click="goToAddPersonnelStep(4)">
                                    <span class="ptm-step-index">4</span>
                                    <span class="ptm-step-copy">
                                        <strong>Access</strong>
                                        <small>Assignment and signature</small>
                                    </span>
                                </button>
                            </div>

                            @if($addPersonnelStep === 1)
                                <div class="ptm-modal-stack-section">
                                    <div class="ptm-modal-section">
                                        <div class="ptm-modal-section-title">
                                            <i class="mdi mdi-account-outline"></i> Personal Information
                                        </div>
                                        <div class="ptm-section-intro">Collect the base identity and contact details used throughout personnel records.</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.first_name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" wire:model="personnelForm.first_name" placeholder="{{ __('personnel.first_name') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.middle_name') }}</label>
                                                    <input type="text" class="form-control" wire:model="personnelForm.middle_name" placeholder="{{ __('personnel.middle_name') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.last_name') }}</label>
                                                    <input type="text" class="form-control" wire:model="personnelForm.last_name" placeholder="{{ __('personnel.last_name') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.email') }} <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" wire:model="personnelForm.email" placeholder="{{ __('personnel.email') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.phone') }}</label>
                                                    <input type="text" class="form-control" wire:model="personnelForm.phone" placeholder="{{ __('personnel.phone') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.id_number_passport') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" wire:model="personnelForm.id_number" placeholder="{{ __('personnel.id_number_passport') }}..." />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label class="control-label">{{ __('personnel.date_of_birth') }}</label>
                                                    <input type="date" class="form-control" wire:model="personnelForm.date_of_birth" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($addPersonnelStep === 2)
                                <div class="ptm-modal-stack-section">
                                    <div class="ptm-modal-section">
                                        <div class="ptm-modal-section-title">
                                            <i class="mdi mdi-briefcase-outline"></i> Employment Details
                                        </div>
                                        <div class="ptm-section-intro">Map the personnel member to their designation, education level, position, and department.</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.employment_date') }}</label>
                                                    <input type="date" class="form-control" wire:model="personnelForm.employment_date" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.add_modal_designation') }} <span class="text-danger">*</span></label>
                                                    <div class="tag-select-container" wire:click="$set('showDesignationDropdown', true)">
                                                        <div class="tag-select-input">
                                                            @if($personnelForm['designation'] !== '')
                                                                @php($selectedDesignation = collect($designations)->firstWhere('id', $personnelForm['designation']))
                                                                <span class="tag-badge">
                                                                    {{ $selectedDesignation['name'] ?? '' }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearDesignation"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text" wire:model.live="designationSearch" wire:keyup="searchDesignations" class="tag-input" placeholder="{{ $personnelForm['designation'] !== '' ? '' : __('personnel.search_designation') }}" autocomplete="off">
                                                        </div>
                                                        @if($showDesignationDropdown && count($filteredDesignations) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($filteredDesignations as $item)
                                                                    <div class="tag-dropdown-item" wire:click.stop='selectDesignation(@js($item["id"]))'>
                                                                        {{ $item['name'] }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.education_level') }}</label>
                                                    <div class="tag-select-container" wire:click="$set('showEducationLevelDropdown', true)">
                                                        <div class="tag-select-input">
                                                            @if($personnelForm['educational_level'] !== '')
                                                                @php($selectedEducation = collect($educationLevels)->firstWhere('id', $personnelForm['educational_level']))
                                                                <span class="tag-badge">
                                                                    {{ $selectedEducation['name'] ?? '' }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearEducationLevel"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text" wire:model.live="educationLevelSearch" wire:keyup="searchEducationLevels" class="tag-input" placeholder="{{ $personnelForm['educational_level'] !== '' ? '' : __('personnel.search_education_level') }}" autocomplete="off">
                                                        </div>
                                                        @if($showEducationLevelDropdown && count($filteredEducationLevels) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($filteredEducationLevels as $item)
                                                                    <div class="tag-dropdown-item" wire:click.stop='selectEducationLevel(@js($item["id"]))'>
                                                                        {{ $item['name'] }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.position') }} <span class="text-danger">*</span></label>
                                                    <div class="tag-select-container" wire:click="$set('showPositionDropdown', true)">
                                                        <div class="tag-select-input">
                                                            @if($personnelForm['position'] !== '')
                                                                @php($selectedPosition = collect($positions)->firstWhere('id', $personnelForm['position']))
                                                                <span class="tag-badge">
                                                                    {{ $selectedPosition['name'] ?? '' }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearPosition"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text" wire:model.live="positionSearch" wire:keyup="searchPositions" class="tag-input" placeholder="{{ $personnelForm['position'] !== '' ? '' : __('personnel.search_position') }}" autocomplete="off">
                                                        </div>
                                                        @if($showPositionDropdown && count($filteredPositions) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($filteredPositions as $item)
                                                                    <div class="tag-dropdown-item" wire:click.stop='selectPosition(@js($item["id"]))'>
                                                                        {{ $item['name'] }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label class="control-label">{{ __('personnel.department') }} <span class="text-danger">*</span></label>
                                                    <div class="tag-select-container" wire:click="$set('showDepartmentDropdown', true)">
                                                        <div class="tag-select-input">
                                                            @if($personnelForm['department'] !== '')
                                                                @php($selectedDepartment = collect($departments)->firstWhere('id', $personnelForm['department']))
                                                                <span class="tag-badge">
                                                                    {{ $selectedDepartment['name'] ?? '' }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearDepartmentInput"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text" wire:model.live="departmentSearchInput" wire:keyup="searchDepartmentsInput" class="tag-input" placeholder="{{ $personnelForm['department'] !== '' ? '' : __('personnel.search_department') }}" autocomplete="off">
                                                        </div>
                                                        @if($showDepartmentDropdown && count($filteredDepartments) > 0)
                                                            <div class="tag-dropdown">
                                                                @foreach($filteredDepartments as $item)
                                                                    <div class="tag-dropdown-item" wire:click.stop='selectDepartmentInput(@js($item["id"]))'>
                                                                        {{ $item['name'] }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($addPersonnelStep === 3)
                                <div class="ptm-modal-stack-section">
                                    <div class="ptm-modal-section">
                                        <div class="ptm-modal-section-title">
                                            <i class="mdi mdi-account-check-outline"></i> Professional Recognition
                                        </div>
                                        <div class="ptm-section-intro">Capture gazette status and career start so the system can show current experience at a glance.</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group ptm-check-group">
                                                    <label class="control-label d-block">Analyst Gazette Status</label>
                                                    <label class="ptm-check-card mb-0">
                                                        <input type="checkbox" wire:model.live="personnelForm.analyst_is_gazzetted">
                                                        <span>Analyst is gazzetted</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Date of Gazzette</label>
                                                    <input type="date" class="form-control" wire:model="personnelForm.date_of_gazzette" @if(!$personnelForm['analyst_is_gazzetted']) disabled @endif />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Start of Career</label>
                                                    <input type="date" class="form-control" wire:model.live="personnelForm.start_of_career" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label class="control-label">Years of Experience</label>
                                                    <div class="form-control ptm-readonly-value d-flex align-items-center">{{ $this->experienceYearsPreview }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($addPersonnelStep === 4)
                                <div class="ptm-modal-stack-section mb-3">
                                    <div class="ptm-modal-section">
                                        <div class="ptm-modal-section-title">
                                            <i class="mdi mdi-shield-lock-outline"></i> Access and Assignment
                                        </div>
                                        <div class="ptm-section-intro">Assign platform access, organization structure, and operational placement before saving the profile.</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.user_license') }} <span class="text-danger">*</span></label>
                                                    <div class="tag-select-container" wire:click="$set('showLicenseDropdown', true)">
                                                        <div class="tag-select-input">
                                                            @if($personnelForm['user_license'] !== '')
                                                                @php($selectedLicenseName = $licenses[$personnelForm['user_license']] ?? '')
                                                                <span class="tag-badge">
                                                                    {{ $selectedLicenseName }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearLicense"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text" wire:model.live="licenseSearch" wire:keyup="searchLicenses" class="tag-input" placeholder="{{ $personnelForm['user_license'] !== '' ? '' : __('personnel.search_license') }}" autocomplete="off">
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
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.zone') }}</label>
                                                    <select class="form-control" wire:model.live="personnelForm.zone_id">
                                                        <option value="">{{ __('personnel.select_zone') }}</option>
                                                        @foreach($zones as $zone)
                                                            <option value="{{ $zone['id'] }}">{{ $zone['key'] }}{{ $zone['value'] ? ' - '.$zone['value'] : '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.directorate') }}</label>
                                                    <select class="form-control" wire:model.live="personnelForm.directorate_id">
                                                        <option value="">{{ __('personnel.search_directorates') }}</option>
                                                        @foreach($this->availableDirectorates as $directorate)
                                                            <option value="{{ $directorate['id'] }}">{{ $directorate['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">{{ __('personnel.lab') }}</label>
                                                    <select class="form-control" wire:model.live="personnelForm.lab_id">
                                                        <option value="">{{ __('personnel.search_labs') }}</option>
                                                        @foreach($this->availableLabs as $lab)
                                                            <option value="{{ $lab['id'] }}">{{ $lab['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0 ptm-check-group">
                                                    <label class="control-label d-block">{{ __('personnel.active') }}</label>
                                                    <label class="ptm-check-card mb-0">
                                                        <input type="checkbox" wire:model="personnelForm.active" />
                                                        <span>{{ __('personnel.active') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ptm-modal-stack-section">
                                    <div class="ptm-modal-section">
                                        <div class="ptm-modal-section-title">
                                            <i class="mdi mdi-draw-pen"></i> Signature Attachment
                                        </div>
                                        <div class="ptm-section-intro">Upload a signature image or draw one directly in the pad. A drawn signature takes priority over an uploaded file.</div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="ptm-signature-card h-100">
                                                    <label class="control-label d-block">{{ __('personnel.upload_signature') }}</label>
                                                    <input type="file" class="form-control" wire:model="signatureUpload" accept="image/*">
                                                    <small class="text-muted d-block mt-2">{{ __('personnel.accepted_signature_formats') }}</small>
                                                    <div class="ptm-signature-upload-state mt-3">
                                                        @if($signatureUpload)
                                                            <div class="small text-success font-weight-semibold">Signature file selected</div>
                                                            <img src="{{ $signatureUpload->temporaryUrl() }}" alt="Signature preview" class="img-fluid ptm-signature-preview-img mt-2">
                                                        @else
                                                            <div class="small text-muted">No file selected yet.</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="ptm-signature-card h-100">
                                                    <label class="control-label d-block">{{ __('personnel.sign_using_pad') }}</label>
                                                    <div class="ptm-signature-canvas-wrap" id="addPersonnelSignatureCanvasWrap">
                                                        <canvas id="addPersonnelSignatureCanvas" width="620" height="190"></canvas>
                                                        <span class="ptm-signature-placeholder" id="addPersonnelSignaturePlaceholder">{{ __('personnel.sign_here') }}</span>
                                                    </div>
                                                    <input type="hidden" id="addPersonnelSignatureData" value="{{ $signatureData }}">
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                                            <small class="text-muted">{{ __('personnel.signature_draw_overrides_upload') }}</small>
                                                            <span id="addPersonnelSignatureStatus" class="ptm-signature-status {{ $signatureData !== '' ? 'is-signed' : '' }}">{{ $signatureData !== '' ? 'Signed' : 'Not signed' }}</span>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAddPersonnelSignaturePad">
                                                            <i class="mdi mdi-eraser"></i> {{ __('personnel.clear') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer ptm-modern-modal-footer">
                        <div class="ptm-step-footer-copy text-muted small">
                            Step {{ $addPersonnelStep }} of 4
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button type="button" class="btn btn-outline-secondary" wire:click="previousAddPersonnelStep" @if($addPersonnelStep === 1) disabled @endif>
                                <i class="mdi mdi-arrow-left"></i> Previous
                            </button>
                            @if($addPersonnelStep < 4)
                                <button type="button" class="btn btn-primary" wire:click="nextAddPersonnelStep">
                                    Next <i class="mdi mdi-arrow-right"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" wire:click="savePersonnel"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeAddPersonnelModal">{{ __('personnel.close') }}</button>
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
                        <h3 class="modal-title"><i class="mdi mdi-account-lock"></i> {{ __('personnel.update_state', ['name' => $selectedPersonnelName]) }}</h3>
                        <button type="button" class="close" wire:click="closeStateModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.status') }}</label>
                            <select class="form-control" wire:model="stateAction">
                                <option value="active">{{ __('personnel.activate_personnel') }}</option>
                                <option value="deactive">{{ __('personnel.deactivate_personnel') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="savePersonnelState"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeStateModal">{{ __('personnel.close') }}</button>
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
                        <h3 class="modal-title"><i class="mdi mdi-key-change"></i> {{ __('personnel.reset_password_of', ['name' => $selectedPersonnelName]) }}</h3>
                        <button type="button" class="close" wire:click="closeResetPasswordModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.password') }}</label>
                            <input type="password" class="form-control" wire:model="newPassword">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.confirm_password') }}</label>
                            <input type="password" class="form-control" wire:model="confirmPassword">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="resetPersonnelPassword"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeResetPasswordModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .ptm-modern-modal-shell .modal-content {
            border-radius: 14px;
            border: 1px solid #dbe3ef;
            overflow: hidden;
            box-shadow: 0 20px 48px rgba(2, 6, 23, 0.24);
        }

        .ptm-modern-modal-header {
            background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
            border-bottom: 1px solid #dbe3ef;
        }

        .ptm-modern-modal-header .modal-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
        }

        .ptm-modern-modal-body {
            background: #fbfdff;
        }

        .ptm-modal-intro {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .ptm-validation-summary {
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fff7f7;
        }

        .ptm-stepper {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .ptm-step {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            transition: all 0.2s ease;
        }

        .ptm-step:hover {
            border-color: #93c5fd;
            box-shadow: 0 10px 24px rgba(14, 165, 233, 0.12);
            transform: translateY(-1px);
        }

        .ptm-step.is-active {
            border-color: #0284c7;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
            box-shadow: 0 14px 28px rgba(2, 132, 199, 0.16);
        }

        .ptm-step.is-complete {
            border-color: #86efac;
            background: linear-gradient(135deg, #ecfdf5 0%, #f8fffb 100%);
        }

        .ptm-step-index {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            flex: 0 0 34px;
        }

        .ptm-step.is-active .ptm-step-index {
            background: #0284c7;
            color: #ffffff;
        }

        .ptm-step.is-complete .ptm-step-index {
            background: #16a34a;
            color: #ffffff;
        }

        .ptm-step-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .ptm-step-copy strong {
            font-size: 0.9rem;
            color: #0f172a;
        }

        .ptm-step-copy small {
            color: #64748b;
            font-size: 0.77rem;
        }

        .ptm-modal-section {
            height: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            padding: 14px 14px 4px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .ptm-modal-section-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ptm-section-intro {
            color: #64748b;
            font-size: 0.83rem;
            margin-bottom: 14px;
        }

        .ptm-modern-modal-shell .control-label {
            color: #334155;
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.2px;
            margin-bottom: 6px;
        }

        .ptm-modern-modal-shell .form-control {
            border-radius: 10px;
            border: 1px solid #d1d9e6;
            min-height: 40px;
        }

        .ptm-modern-modal-shell .form-control:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.14);
        }

        .ptm-readonly-value {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            color: #0f172a;
            font-weight: 600;
        }

        .ptm-modern-modal-shell .tag-select-input {
            border-radius: 10px;
            border-color: #d1d9e6;
        }

        .ptm-modern-modal-shell .tag-select-input:focus-within {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.14);
        }

        .ptm-modern-modal-footer {
            border-top: 1px solid #e2e8f0;
            background: #fbfdff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .ptm-step-footer-copy {
            font-weight: 600;
        }

        .ptm-check-group {
            min-height: 100%;
        }

        .ptm-check-card {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
            padding: 10px 14px;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            font-weight: 600;
            color: #334155;
        }

        .ptm-check-card input {
            width: 16px;
            height: 16px;
        }

        .ptm-signature-card {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 14px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
        }

        .ptm-signature-canvas-wrap {
            position: relative;
            border: 1px dashed #94a3b8;
            border-radius: 12px;
            background: #ffffff;
            overflow: hidden;
        }

        .ptm-signature-canvas-wrap canvas {
            display: block;
            width: 100%;
            height: 190px;
            cursor: crosshair;
            touch-action: none;
        }

        .ptm-signature-placeholder {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: #94a3b8;
            font-size: 0.9rem;
            font-style: italic;
            pointer-events: none;
        }

        .ptm-signature-status {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }

        .ptm-signature-status.is-signed {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .ptm-signature-preview-img {
            max-height: 110px;
            border-radius: 10px;
            border: 1px solid #dbe3ef;
            background: #ffffff;
            padding: 6px;
        }

        .ptm-toolbar-strip-wrap {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .ptm-toolbar-strip {
            gap: 8px;
            min-width: 920px;
        }

        .ptm-search-wrap {
            flex: 1 1 auto;
            min-width: 340px;
        }

        .ptm-btn-export {
            min-width: 165px;
            height: 38px;
            font-weight: 600;
        }

        .ptm-toolbar-strip .ptm-show-select.form-control {
            width: 74px !important;
            min-width: 74px !important;
            max-width: 74px !important;
            flex: 0 0 74px !important;
            height: 38px;
            padding: 0.2rem 0.45rem;
            text-align: center;
            text-align-last: center;
        }

        .ptm-show-label {
            font-size: 0.82rem;
            color: #475569;
            font-weight: 600;
            white-space: nowrap;
        }

        .ptm-toolbar-btn {
            min-width: 100px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .ptm-btn-export,
        .ptm-show-select,
        .ptm-toolbar-btn {
            flex: 0 0 auto;
        }

        .rm-act-btn { border-radius: 7px; padding: 4px 8px; margin-right: 3px; font-size: 12px; }
        .rm-act-btn:last-child { margin-right: 0; }
        .rm-act-btn--edit  { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .rm-act-btn--edit:hover  { background: #dbeafe; border-color: #93c5fd; }
        .rm-act-btn--view  { border: 1px solid #bbf7d0; color: #15803d; background: #f0fdf4; }
        .rm-act-btn--view:hover  { background: #dcfce7; border-color: #86efac; }
        .rm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .rm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }

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

        @media (max-width: 991.98px) {
            .ptm-stepper {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .ptm-stepper {
                grid-template-columns: 1fr;
            }

            .ptm-modern-modal-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .ptm-modern-modal-footer > div,
            .ptm-modern-modal-footer > button {
                width: 100%;
            }
        }
    </style>

    @script
    <script>
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.tag-select-container')) {
                $wire.closeAddModalDropdowns();
            }
        });

        (function () {
            if (window.__addPersonnelSignaturePadInit) {
                return;
            }
            window.__addPersonnelSignaturePadInit = true;

            let isDrawing = false;
            let hasSignatureStroke = false;

            function getPadNodes() {
                const canvas = document.getElementById('addPersonnelSignatureCanvas');
                const hiddenInput = document.getElementById('addPersonnelSignatureData');
                const clearBtn = document.getElementById('clearAddPersonnelSignaturePad');
                const placeholder = document.getElementById('addPersonnelSignaturePlaceholder');
                const statusBadge = document.getElementById('addPersonnelSignatureStatus');

                return { canvas, hiddenInput, clearBtn, placeholder, statusBadge };
            }

            function pointFromEvent(event, canvas) {
                const rect = canvas.getBoundingClientRect();
                const source = event.touches && event.touches[0] ? event.touches[0] : event;
                return {
                    x: (source.clientX - rect.left) * (canvas.width / rect.width),
                    y: (source.clientY - rect.top) * (canvas.height / rect.height),
                };
            }

            function setSignatureStatus(isSigned) {
                const { statusBadge } = getPadNodes();
                if (!statusBadge) {
                    return;
                }

                statusBadge.textContent = isSigned ? 'Signed' : 'Not signed';
                statusBadge.classList.toggle('is-signed', isSigned);
            }

            function syncHiddenSignature(canvas) {
                const { hiddenInput, placeholder } = getPadNodes();
                if (!hiddenInput || !canvas) {
                    return;
                }

                if (hasSignatureStroke) {
                    const data = canvas.toDataURL('image/png');
                    hiddenInput.value = data;
                    if (window.Livewire && typeof window.Livewire.find === 'function') {
                        $wire.set('signatureData', data);
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                } else {
                    hiddenInput.value = '';
                    if (window.Livewire && typeof window.Livewire.find === 'function') {
                        $wire.set('signatureData', '');
                    }
                    if (placeholder) {
                        placeholder.style.display = 'block';
                    }
                }

                setSignatureStatus(hasSignatureStroke);
            }

            function loadExistingSignature(canvas, ctx) {
                const { hiddenInput, placeholder } = getPadNodes();
                if (!hiddenInput || !hiddenInput.value || !hiddenInput.value.startsWith('data:image/')) {
                    hasSignatureStroke = false;
                    if (placeholder) {
                        placeholder.style.display = 'block';
                    }
                    setSignatureStatus(false);
                    return;
                }

                const image = new Image();
                image.onload = function () {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
                    hasSignatureStroke = true;
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    setSignatureStatus(true);
                };
                image.src = hiddenInput.value;
            }

            function clearPad() {
                const { canvas, placeholder } = getPadNodes();
                if (!canvas) {
                    return;
                }

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasSignatureStroke = false;
                if (placeholder) {
                    placeholder.style.display = 'block';
                }
                syncHiddenSignature(canvas);
            }

            function bindPad() {
                const { canvas, clearBtn } = getPadNodes();
                if (!canvas || canvas.dataset.bound === '1') {
                    return;
                }

                canvas.dataset.bound = '1';
                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';

                loadExistingSignature(canvas, ctx);

                const startDrawing = function (event) {
                    isDrawing = true;
                    const point = pointFromEvent(event, canvas);
                    ctx.beginPath();
                    ctx.moveTo(point.x, point.y);
                    hasSignatureStroke = true;
                    setSignatureStatus(true);
                    event.preventDefault();
                };

                const draw = function (event) {
                    if (!isDrawing) {
                        return;
                    }
                    const point = pointFromEvent(event, canvas);
                    ctx.lineTo(point.x, point.y);
                    ctx.stroke();
                    event.preventDefault();
                };

                const endDrawing = function () {
                    if (!isDrawing) {
                        return;
                    }
                    isDrawing = false;
                    syncHiddenSignature(canvas);
                };

                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', endDrawing);
                canvas.addEventListener('mouseleave', endDrawing);
                canvas.addEventListener('touchstart', startDrawing, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                canvas.addEventListener('touchend', endDrawing);
                canvas.addEventListener('touchcancel', endDrawing);

                if (clearBtn && clearBtn.dataset.bound !== '1') {
                    clearBtn.dataset.bound = '1';
                    clearBtn.addEventListener('click', function () {
                        clearPad();
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', bindPad);
            document.addEventListener('livewire:load', bindPad);
            document.addEventListener('livewire:navigated', bindPad);
            document.addEventListener('livewire:update', bindPad);

            const observer = new MutationObserver(function () {
                const canvas = document.getElementById('addPersonnelSignatureCanvas');
                if (canvas && canvas.dataset.bound !== '1') {
                    bindPad();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });

            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && typeof Livewire.hook === 'function') {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            queueMicrotask(bindPad);
                        });
                    });
                }
            });
        })();
    </script>
    @endscript
</div>
