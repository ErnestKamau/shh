<div class="container-fluid pup-root">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 pup-hero-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 1rem;">
                        <div class="d-flex align-items-center">
                            @if(!empty($this->user->photo))
                                <img src="{{ $this->user->photo }}" class="rounded-circle mr-3 pup-avatar" alt="{{ $this->user->name }}" />
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mr-3 pup-avatar-fallback">
                                    <i class="mdi mdi-account text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h2 class="mb-0">{{ $this->user->name }}</h2>
                                <p class="text-muted mb-0">{{ __('personnel.profile') ?? 'My Profile' }}</p>
                                <div class="mt-2">
                                    @if($this->user->two_factor_confirmed_at)
                                        <span class="badge badge-success">Two-Factor Enabled</span>
                                    @else
                                        <span class="badge badge-secondary">Two-Factor Not Enabled</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <button class="btn btn-outline-primary btn-sm" type="button" id="open-2fa-modal">
                                <i class="mdi mdi-shield-key-outline"></i>
                                {{ $this->user->two_factor_confirmed_at ? 'Manage Two-Factor' : 'Enable Two-Factor' }}
                            </button>
                            @if($activeTab === 'details')
                                <button class="btn btn-primary btn-sm" type="submit" form="userProfileForm" wire:loading.attr="disabled" wire:target="saveProfile">
                                    <span wire:loading.remove wire:target="saveProfile"><i class="mdi mdi-content-save"></i> Save Changes</span>
                                    <span wire:loading wire:target="saveProfile"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs personnel-detail-tabs">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" wire:click="setActiveTab('details')">
                        <i class="mdi mdi-account-details-outline mr-1"></i>{{ __('personnel.user_details') ?? 'User Details' }}
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'security' ? 'active' : '' }}" wire:click="setActiveTab('security')">
                        <i class="mdi mdi-shield-lock-outline mr-1"></i> Security
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3">
            <div class="detail-form-header mb-4">
                <div>
                    <h5 class="card-title mb-1">{{ $activeTab === 'security' ? 'Security' : (__('personnel.user_details') ?? 'User Details') }}</h5>
                    <p class="text-muted small mb-0">{{ __('personnel.details_intro') ?? 'Manage profile, employment, and signature information in one place.' }}</p>
                </div>
                @if($activeTab === 'details')
                    <span class="badge badge-light border">Step {{ $detailsStep }} of 3</span>
                @endif
            </div>

            @if($activeTab === 'details')
                <form id="userProfileForm" autocomplete="off" wire:submit.prevent="saveProfile">
                    <div class="detail-stepper mb-4">
                        <button type="button" class="detail-step {{ $detailsStep === 1 ? 'is-active' : ($detailsStep > 1 ? 'is-complete' : '') }}" wire:click="setDetailsStep(1)">
                            <span class="detail-step-index">1</span>
                            <span class="detail-step-copy">
                                <strong>{{ __('personnel.section_personal_information') ?? 'Personal Information' }}</strong>
                                <small>{{ __('personnel.signature_attachment') ?? 'Signature' }}</small>
                            </span>
                        </button>
                        <button type="button" class="detail-step {{ $detailsStep === 2 ? 'is-active' : ($detailsStep > 2 ? 'is-complete' : '') }}" wire:click="setDetailsStep(2)">
                            <span class="detail-step-index">2</span>
                            <span class="detail-step-copy">
                                <strong>{{ __('personnel.section_employment_details') ?? 'Employment Details' }}</strong>
                                <small>Designation / Lab / Sections</small>
                            </span>
                        </button>
                        <button type="button" class="detail-step {{ $detailsStep === 3 ? 'is-active' : ($detailsStep > 3 ? 'is-complete' : '') }}" wire:click="setDetailsStep(3)">
                            <span class="detail-step-index">3</span>
                            <span class="detail-step-copy">
                                <strong>Professional Recognition</strong>
                                <small>Gazette and career timeline</small>
                            </span>
                        </button>
                    </div>

                    @if($detailsStep === 1)
                        <div class="detail-section mb-4">
                            <div class="detail-section-header mb-3">
                                <h6 class="mb-1"><i class="mdi mdi-account-outline mr-1"></i> {{ __('personnel.section_personal_information') ?? 'Personal Information' }}</h6>
                                <small class="text-muted">{{ __('personnel.section_personal_information_hint') ?? 'Identity and contact details.' }}</small>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Photo</label><input type="file" wire:model="photoUpload" class="form-control" accept="image/*">@error('photoUpload')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.first_name') ?? 'First Name' }} *</label><input wire:model.live="detailsFirstName" class="form-control" required>@error('detailsFirstName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.middle_name') ?? 'Middle Name' }}</label><input wire:model.live="detailsMiddleName" class="form-control">@error('detailsMiddleName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.last_name') ?? 'Last Name' }}</label><input wire:model.live="detailsLastName" class="form-control">@error('detailsLastName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.email') ?? 'Email' }} *</label><input type="email" wire:model.live="detailsEmail" class="form-control" required>@error('detailsEmail')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.phone') ?? 'Phone' }}</label><input wire:model.live="detailsPhone" class="form-control">@error('detailsPhone')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.id_number_passport') ?? 'ID Number / Passport' }}</label><input wire:model.live="detailsIdNumber" class="form-control">@error('detailsIdNumber')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.date_of_birth') ?? 'Date of Birth' }}</label><input type="date" wire:model.live="detailsDateOfBirth" class="form-control">@error('detailsDateOfBirth')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>KRA PIN</label><input wire:model.live="detailsKraPin" class="form-control">@error('detailsKraPin')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>NSSF</label><input wire:model.live="detailsNssf" class="form-control">@error('detailsNssf')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4"><div class="form-group"><label>NHIF</label><input wire:model.live="detailsNhif" class="form-control">@error('detailsNhif')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            </div>

                            @include('livewire.personnel.partials.signature-pad', [
                                'signatureUploadProperty' => 'detailsSignatureUpload',
                                'signatureDataProperty' => 'detailsSignatureData',
                                'currentSignature' => $this->user->electronic_sig,
                                'canvasId' => 'personnelUserSignatureCanvas',
                                'uploadId' => 'personnelUserSignatureUpload',
                                'hiddenId' => 'personnelUserSignatureData',
                                'statusId' => 'personnelUserSignatureStatus',
                                'clearId' => 'clearPersonnelUserSignaturePad',
                                'placeholderId' => 'personnelUserSignaturePlaceholder',
                                'wrapId' => 'personnelUserSignatureCanvasWrap',
                            ])
                        </div>
                    @endif

                    @if($detailsStep === 2)
                        <div class="detail-section mb-4">
                            <div class="detail-section-header mb-3">
                                <h6 class="mb-1"><i class="mdi mdi-briefcase-outline mr-1"></i> {{ __('personnel.section_employment_details') ?? 'Employment Details' }}</h6>
                                <small class="text-muted">{{ __('personnel.section_employment_details_hint') ?? 'Role, department, and workplace assignment.' }}</small>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.employment_date') ?? 'Employment Date' }}</label><input type="date" wire:model.live="detailsEmploymentDate" class="form-control">@error('detailsEmploymentDate')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                                <div class="col-md-4">
                                    <label>{{ __('personnel.designation') ?? 'Designation' }} *</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showDesignationDropdown', true)">
                                        <div class="tag-select-input">
                                            @if($selectedDesignationId)
                                                @php $s = $this->designations->firstWhere('id', $selectedDesignationId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDesignation"></i></span>
                                            @endif
                                            <input class="tag-input" wire:model.live="designationSearch" wire:keyup="searchDesignation" placeholder="Search designation...">
                                        </div>
                                        @if($showDesignationDropdown)
                                            <div class="tag-dropdown">@foreach($this->designations->filter(fn($d)=>$designationSearch===''||stripos($d->name,$designationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDesignation('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                        @endif
                                    </div>
                                    @error('selectedDesignationId')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('personnel.education_level') ?? 'Education Level' }}</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showEducationDropdown', true)">
                                        <div class="tag-select-input">
                                            @if($selectedEducationId)
                                                @php $s = $this->educationLevels->firstWhere('id', $selectedEducationId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearEducation"></i></span>
                                            @endif
                                            <input class="tag-input" wire:model.live="educationSearch" wire:keyup="searchEducation" placeholder="Search education...">
                                        </div>
                                        @if($showEducationDropdown)
                                            <div class="tag-dropdown">@foreach($this->educationLevels->filter(fn($d)=>$educationSearch===''||stripos($d->name,$educationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectEducation('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('personnel.position') ?? 'Position' }}</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showPositionDropdown', true)">
                                        <div class="tag-select-input">
                                            @if($selectedPositionId)
                                                @php $s = $this->positions->firstWhere('id', $selectedPositionId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearPosition"></i></span>
                                            @endif
                                            <input class="tag-input" wire:model.live="positionSearch" wire:keyup="searchPosition" placeholder="Search position...">
                                        </div>
                                        @if($showPositionDropdown)
                                            <div class="tag-dropdown">@foreach($this->positions->filter(fn($d)=>$positionSearch===''||stripos($d->name,$positionSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectPosition('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                        @endif
                                    </div>
                                    @error('selectedPositionId')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('personnel.department') ?? 'Department' }}</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showDepartmentDropdown', true)">
                                        <div class="tag-select-input">
                                            @if($selectedDepartmentId)
                                                @php $s = $this->departments->firstWhere('id', $selectedDepartmentId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDepartment"></i></span>
                                            @endif
                                            <input class="tag-input" wire:model.live="departmentSearch" wire:keyup="searchDepartment" placeholder="Search department...">
                                        </div>
                                        @if($showDepartmentDropdown)
                                            <div class="tag-dropdown">@foreach($this->departments->filter(fn($d)=>$departmentSearch===''||stripos($d->name,$departmentSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDepartment('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label>{{ __('personnel.lab') ?? 'Lab' }}</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showLabDropdown', true)">
                                        <div class="tag-select-input">
                                            @foreach($selectedLabIds as $selectedLabId)
                                                @php $s = $this->labs->firstWhere('id', $selectedLabId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearLab(@js($selectedLabId))"></i></span>
                                            @endforeach
                                            <input class="tag-input" wire:model.live="labSearch" wire:keyup="searchLab" placeholder="Search labs...">
                                        </div>
                                        @if($showLabDropdown)
                                            <div class="tag-dropdown">
                                                @foreach($this->labs->filter(fn($l)=>$labSearch===''||stripos($l->name,$labSearch)!==false) as $lab)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectLab(@js($lab->id))">{{ $lab->name }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('selectedLabIds')<small class="text-danger">{{ $message }}</small>@enderror
                                    @error('selectedLabIds.*')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label>{{ __('personnel.lab_sections') ?? 'Lab Sections' }}</label>
                                    <div class="tag-select-container" wire:click.stop="$set('showLabSectionDropdown', true)">
                                        <div class="tag-select-input">
                                            @foreach($selectedLabSectionIds as $selectedSectionId)
                                                @php $s = $this->labSections->firstWhere('id', $selectedSectionId); @endphp
                                                <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearLabSection(@js($selectedSectionId))"></i></span>
                                            @endforeach
                                            <input class="tag-input" wire:model.live="labSectionSearch" wire:keyup="searchLabSection" placeholder="Search lab sections...">
                                        </div>
                                        @if($showLabSectionDropdown)
                                            <div class="tag-dropdown">
                                                @foreach($this->labSections->filter(fn($l)=>$labSectionSearch===''||stripos($l->name,$labSectionSearch)!==false||stripos((string)$l->code,$labSectionSearch)!==false) as $section)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectLabSection(@js($section->id))">
                                                        {{ $section->code }} - {{ $section->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('selectedLabSectionIds')<small class="text-danger">{{ $message }}</small>@enderror
                                    @error('selectedLabSectionIds.*')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($detailsStep === 3)
                        <div class="detail-section mb-1">
                            <div class="detail-section-header mb-3">
                                <h6 class="mb-1"><i class="mdi mdi-account-check-outline mr-1"></i> Professional Recognition</h6>
                                <small class="text-muted">Track gazette status and career timeline.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="mb-2 d-block">Analyst Gazette Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="pupAnalystIsGazzetted" wire:model.live="detailsAnalystIsGazzetted">
                                            <label class="form-check-label" for="pupAnalystIsGazzetted">Analyst is gazzetted</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Date of Gazzette</label>
                                        <input type="date" class="form-control" wire:model.live="detailsDateOfGazzette" @disabled(! $detailsAnalystIsGazzetted)>
                                        @error('detailsDateOfGazzette')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Gazzette No</label>
                                        <input type="text" class="form-control" wire:model.live="detailsGazzetteNo" @disabled(! $detailsAnalystIsGazzetted)>
                                        @error('detailsGazzetteNo')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start of Career</label>
                                        <input type="date" class="form-control" wire:model.live="detailsStartOfCareer">
                                        @error('detailsStartOfCareer')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="detail-step-footer mt-4">
                        <div class="detail-step-footer-copy text-muted small">Step {{ $detailsStep }} of 3</div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button type="button" class="btn btn-outline-secondary" wire:click="previousDetailsStep" @if($detailsStep === 1) disabled @endif>
                                <i class="mdi mdi-arrow-left"></i> Previous
                            </button>
                            @if($detailsStep < 3)
                                <button type="button" class="btn btn-primary" wire:click="nextDetailsStep">
                                    Next <i class="mdi mdi-arrow-right"></i>
                                </button>
                            @else
                                <button class="btn btn-primary detail-save-btn" type="submit" wire:loading.attr="disabled" wire:target="saveProfile">
                                    <span wire:loading.remove wire:target="saveProfile"><i class="mdi mdi-content-save"></i> Save</span>
                                    <span wire:loading wire:target="saveProfile"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            @if($activeTab === 'security')
                <div class="detail-section mb-4">
                    <div class="detail-section-header mb-3">
                        <h6 class="mb-1"><i class="mdi mdi-lock-outline mr-1"></i> Password</h6>
                        <small class="text-muted">Optionally update your account password.</small>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="pupUpdatePassword" wire:model.live="updatePassword">
                        <label class="form-check-label" for="pupUpdatePassword">Update password on next save</label>
                    </div>
                    @if($updatePassword)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" class="form-control" wire:model.live="password" autocomplete="new-password">
                                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Confirm Password *</label>
                                    <input type="password" class="form-control" wire:model.live="passwordConfirmation" autocomplete="new-password">
                                    @error('passwordConfirmation')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" wire:click="saveProfile" wire:loading.attr="disabled" wire:target="saveProfile">
                            <i class="mdi mdi-content-save"></i> Save Password
                        </button>
                    @endif
                </div>

                <div class="detail-section">
                    <div class="detail-section-header mb-3">
                        <h6 class="mb-1"><i class="mdi mdi-shield-key-outline mr-1"></i> Two-Factor Authentication</h6>
                        <small class="text-muted">If enabled, login requires an authenticator code.</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 1rem;">
                        <div>
                            <div class="font-weight-semibold">
                                @if($this->user->two_factor_confirmed_at)
                                    Enabled (Authenticator app)
                                @else
                                    Disabled (Email verification codes)
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" type="button" id="open-2fa-modal-2">
                            <i class="mdi mdi-shield-key-outline"></i>
                            {{ $this->user->two_factor_confirmed_at ? 'Manage Two-Factor' : 'Enable Two-Factor' }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('livewire.personnel.partials.user-profile-2fa-modals', ['user' => $this->user])

    <style>
        .pup-root .pup-hero-card { border-radius: 15px; }
        .pup-root .pup-avatar { width: 60px; height: 60px; object-fit: cover; }
        .pup-root .pup-avatar-fallback { width: 60px; height: 60px; flex-shrink: 0; }
        .pup-root .pup-avatar-fallback i { font-size: 1.8rem; }
        .pup-root .tab-card { border-radius: 14px; border: 0; box-shadow: 0 8px 25px rgba(0,0,0,.06); }
        .pup-root .tab-card-header { background: #fff; border-bottom: 1px solid #eef2f7; }
        .pup-root .personnel-detail-tabs .nav-link { border: 0; color: #64748b; font-weight: 600; background: transparent; }
        .pup-root .personnel-detail-tabs .nav-link.active { color: #0f172a; border-bottom: 2px solid #2563eb; }
        .pup-root .detail-form-header { display:flex; justify-content:space-between; align-items:center; gap: 12px; flex-wrap: wrap; }
        .pup-root .detail-stepper { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
        .pup-root .detail-step { border:1px solid #dbe3ef; border-radius:12px; background:#fff; padding:12px; text-align:left; display:flex; gap:10px; align-items:center; }
        .pup-root .detail-step.is-active { border-color:#93c5fd; background:#eff6ff; }
        .pup-root .detail-step.is-complete { border-color:#86efac; background:#f0fdf4; }
        .pup-root .detail-step-index { width:28px; height:28px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#e2e8f0; font-weight:700; }
        .pup-root .detail-step.is-active .detail-step-index { background:#2563eb; color:#fff; }
        .pup-root .detail-step-copy { display:flex; flex-direction:column; }
        .pup-root .detail-step-copy small { color:#64748b; }
        .pup-root .detail-section { border:1px solid #e2e8f0; border-radius:12px; padding:16px; background:#fff; }
        .pup-root .detail-step-footer { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
        .pup-root .tag-select-container { position:relative; }
        .pup-root .tag-select-input { min-height:38px; border:1px solid #ced4da; border-radius:.25rem; padding:4px 8px; display:flex; flex-wrap:wrap; gap:4px; background:#fff; }
        .pup-root .tag-badge { background:#eef2ff; color:#3730a3; border-radius:999px; padding:2px 8px; font-size:12px; display:inline-flex; align-items:center; gap:4px; }
        .pup-root .tag-input { border:0; outline:0; min-width:120px; flex:1; }
        .pup-root .tag-dropdown { position:absolute; z-index:20; left:0; right:0; top:calc(100% + 4px); max-height:220px; overflow:auto; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 10px 24px rgba(15,23,42,.08); }
        .pup-root .tag-dropdown-item { padding:8px 12px; cursor:pointer; }
        .pup-root .tag-dropdown-item:hover { background:#f8fafc; }
        .pup-root .signature-section { border-top:1px dashed #dbe3ef; margin-top:10px; padding-top:14px; }
        .pup-root .signature-card { border:1px solid #dbe3ef; border-radius:12px; background:linear-gradient(180deg,#fff 0%,#f8fbff 100%); padding:14px; }
        .pup-root .signature-label { color:#0f172a; font-weight:700; font-size:.82rem; text-transform:uppercase; }
        .pup-root .signature-preview { border:1px solid #e2e8f0; border-radius:10px; background:#fff; padding:8px; }
        .pup-root .signature-preview img { max-height:76px; border-radius:6px; border:1px solid #e2e8f0; }
        .pup-root .signature-canvas-wrap { position:relative; border:1px dashed #94a3b8; border-radius:10px; background:#fff; overflow:hidden; }
        .pup-root .signature-canvas-wrap canvas { display:block; width:100%; height:190px; cursor:crosshair; touch-action:none; }
        .pup-root .signature-canvas-placeholder { position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); color:#94a3b8; font-size:.9rem; pointer-events:none; font-style:italic; }
        .pup-root .signature-status { display:inline-flex; align-items:center; border-radius:999px; padding:2px 8px; font-size:.72rem; font-weight:700; border:1px solid transparent; }
        .pup-root .signature-status-empty { color:#9a3412; background:#fff7ed; border-color:#fed7aa; }
        .pup-root .signature-status-signed { color:#065f46; background:#ecfdf5; border-color:#a7f3d0; }
        @media (max-width: 768px) {
            .pup-root .detail-stepper { grid-template-columns: 1fr; }
        }
    </style>

    <script>
        (function () {
            if (window.__personnelUserSignaturePadInit) {
                return;
            }
            window.__personnelUserSignaturePadInit = true;

            let isDrawing = false;
            let hasSignatureStroke = false;

            function getPadNodes() {
                return {
                    canvas: document.getElementById('personnelUserSignatureCanvas'),
                    hiddenInput: document.getElementById('personnelUserSignatureData'),
                    clearBtn: document.getElementById('clearPersonnelUserSignaturePad'),
                    placeholder: document.getElementById('personnelUserSignaturePlaceholder'),
                    uploadInput: document.getElementById('personnelUserSignatureUpload'),
                    statusBadge: document.getElementById('personnelUserSignatureStatus'),
                    form: document.getElementById('userProfileForm'),
                };
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
                statusBadge.classList.toggle('signature-status-signed', isSigned);
                statusBadge.classList.toggle('signature-status-empty', !isSigned);
            }

            function syncHiddenSignature() {
                const { canvas, hiddenInput } = getPadNodes();
                if (!canvas || !hiddenInput) {
                    return;
                }
                hiddenInput.value = hasSignatureStroke ? canvas.toDataURL('image/png') : '';
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

                const root = canvas.closest('[wire\\:id]') || hiddenInput.closest('[wire\\:id]');
                if (root && window.Livewire && typeof Livewire.find === 'function') {
                    const component = Livewire.find(root.getAttribute('wire:id'));
                    if (component && typeof component.set === 'function') {
                        // false = update client snapshot only; included on the next save request (avoids race)
                        component.set('detailsSignatureData', hiddenInput.value, false);
                    }
                }
                setSignatureStatus(hasSignatureStroke);
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
                syncHiddenSignature();
            }

            function bindPad() {
                const { canvas, clearBtn, placeholder, uploadInput, form } = getPadNodes();
                if (!canvas || canvas.dataset.bound === '1') {
                    return;
                }
                canvas.dataset.bound = '1';
                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';

                const startDrawing = function (event) {
                    isDrawing = true;
                    const point = pointFromEvent(event, canvas);
                    ctx.beginPath();
                    ctx.moveTo(point.x, point.y);
                    hasSignatureStroke = true;
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    setSignatureStatus(true);
                    if (uploadInput) {
                        uploadInput.value = '';
                    }
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
                    syncHiddenSignature();
                };

                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', endDrawing);
                canvas.addEventListener('mouseleave', endDrawing);
                canvas.addEventListener('touchstart', startDrawing, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                canvas.addEventListener('touchend', endDrawing);
                canvas.addEventListener('touchcancel', endDrawing);

                if (clearBtn) {
                    clearBtn.addEventListener('click', clearPad);
                }
                if (uploadInput) {
                    uploadInput.addEventListener('change', function () {
                        if (uploadInput.files && uploadInput.files.length > 0) {
                            clearPad();
                        }
                    });
                }
                if (form && form.dataset.signatureSubmitBound !== '1') {
                    form.dataset.signatureSubmitBound = '1';
                    form.addEventListener('submit', syncHiddenSignature, true);
                }

                if (!window.__personnelUserSignatureSaveSyncBound) {
                    window.__personnelUserSignatureSaveSyncBound = true;
                    document.addEventListener('click', function (event) {
                        const trigger = event.target.closest('#userProfileForm [type="submit"], [wire\\:click="saveProfile"], [form="userProfileForm"]');
                        if (!trigger) {
                            return;
                        }
                        syncHiddenSignature();
                    }, true);
                }
                setSignatureStatus(hasSignatureStroke);
            }

            document.addEventListener('DOMContentLoaded', bindPad);
            document.addEventListener('livewire:navigated', bindPad);
            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && typeof Livewire.hook === 'function') {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => queueMicrotask(bindPad));
                    });
                }
            });

            const padObserver = new MutationObserver(function () {
                const canvas = document.getElementById('personnelUserSignatureCanvas');
                if (canvas && canvas.dataset.bound !== '1') {
                    bindPad();
                }
            });
            padObserver.observe(document.body, { childList: true, subtree: true });

            document.addEventListener('click', function (event) {
                if (event.target.closest('.tag-select-container') || event.target.closest('#personnelUserSignatureCanvasWrap') || event.target.closest('#clearPersonnelUserSignaturePad')) {
                    return;
                }
                if (!document.querySelector('.pup-root .tag-dropdown')) {
                    return;
                }
                const liveRoot = document.querySelector('.pup-root');
                if (!liveRoot || !window.Livewire) {
                    return;
                }
                const wireId = liveRoot.getAttribute('wire:id');
                if (!wireId) {
                    return;
                }
                const component = Livewire.find(wireId);
                if (component && typeof component.call === 'function') {
                    component.call('closeSelectDropdowns');
                }
            });
        })();
    </script>
</div>
