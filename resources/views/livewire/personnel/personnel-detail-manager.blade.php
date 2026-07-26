<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @if(isset($this->user->photo) && $this->user->photo != '')
                                <img src="{{ $this->user->photo }}" class="rounded-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;" />
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mr-3" style="width: 60px; height: 60px; flex-shrink: 0;">
                                    <i class="mdi mdi-account text-white" style="font-size: 1.8rem;"></i>
                                </div>
                            @endif
                            <div>
                                <h2 class="mb-0">{{ $this->user->name }}</h2>
                                <p class="text-muted mb-0">{{ __('personnel.profile') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs personnel-detail-tabs">
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" wire:click="setActiveTab('roles')"><i class="mdi mdi-shield-account-outline mr-1"></i>{{ __('personnel.roles') }}</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" wire:click="setActiveTab('details')"><i class="mdi mdi-account-details-outline mr-1"></i>{{ __('personnel.user_details') }}</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'work_history' ? 'active' : '' }}" wire:click="setActiveTab('work_history')"><i class="mdi mdi-timeline-text-outline mr-1"></i>{{ __('personnel.work_history') }}</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'certifications' ? 'active' : '' }}" wire:click="setActiveTab('certifications')"><i class="mdi mdi-certificate-outline mr-1"></i>{{ __('personnel.certifications') }}</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'capability_matrix' ? 'active' : '' }}" wire:click="setActiveTab('capability_matrix')"><i class="mdi mdi-view-grid-plus-outline mr-1"></i>{{ __('personnel.capability_matrix') }}</button></li>
                <li class="nav-item"><button class="nav-link {{ $activeTab === 'attachments' ? 'active' : '' }}" wire:click="setActiveTab('attachments')"><i class="mdi mdi-paperclip mr-1"></i>Attachments</button></li>
            </ul>
        </div>
        <div class="tab-content p-3">
            @php
                $tabTitleMap = [
                    'roles' => __('personnel.roles'),
                    'details' => __('personnel.user_details'),
                    'work_history' => __('personnel.work_history'),
                    'certifications' => __('personnel.certifications'),
                    'capability_matrix' => __('personnel.capability_matrix'),
                    'attachments' => 'Attachments',
                ];
                $activeTabTitle = $tabTitleMap[$activeTab] ?? __('personnel.user_details');
            @endphp

            <div class="detail-form-header mb-4">
                <div>
                    <h5 class="card-title mb-1">{{ $activeTabTitle }}</h5>
                    <p class="text-muted small mb-0">{{ __('personnel.details_intro') }}</p>
                </div>

                @if($activeTab === 'roles')
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" type="button" wire:click="expandAllRoleDetails">
                            <i class="mdi mdi-arrow-expand-vertical mr-1"></i> {{ __('personnel.expand_all') }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" type="button" wire:click="collapseAllRoleDetails">
                            <i class="mdi mdi-arrow-collapse-vertical mr-1"></i> {{ __('personnel.collapse_all') }}
                        </button>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" type="button" wire:click="openAddRoleModal"><i class="mdi mdi-key-plus mr-1"></i> {{ __('personnel.add_role_group') }}</button>
                    </div>
                @elseif($activeTab === 'details')
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="badge badge-light border">Step {{ $detailsStep }} of 3</span>
                    </div>
                @elseif($activeTab === 'certifications')
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill cert-add-btn" wire:click="openCertificationModal">
                        <i class="mdi mdi-plus"></i> {{ __('personnel.add') }}
                    </button>
                @elseif($activeTab === 'work_history')
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill cert-add-btn" wire:click="openWorkHistoryModal">
                        <i class="mdi mdi-plus"></i> {{ __('personnel.add') }}
                    </button>
                @endif
            </div>

            @if($activeTab === 'roles')
                <div class="row mb-3 align-items-center">
                    <div class="col-md-7 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background:#f8fafc; border-right:0; border-radius:8px 0 0 8px;"><i class="mdi mdi-magnify text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control" style="border-left:0; border-radius:0 8px 8px 0; font-size:13px;"
                                wire:model.live.debounce.300ms="rolesSearch"
                                placeholder="{{ __('personnel.search_roles') }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select class="form-control form-control-sm" style="border-radius:8px; font-size:13px;" wire:model.live="rolesPerPage">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @php
                    $rolesData = $this->rolesPage;
                    $rolesTotal = $this->rolesTotal;
                    $rolesCurrentPage = $this->getPage('rolesPage');
                    $rolesTotalPages = (int) ceil($rolesTotal / $rolesPerPage);
                    $rolesFrom = $rolesTotal > 0 ? (($rolesCurrentPage - 1) * $rolesPerPage) + 1 : 0;
                    $rolesTo = min($rolesCurrentPage * $rolesPerPage, $rolesTotal);
                @endphp
                <div style="border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(15,23,42,0.05);">
                    <table class="table mb-0" style="font-size:13px;">
                        <thead>
                            <tr style="background:linear-gradient(180deg,#f8fbff 0%,#f1f5f9 100%); color:#334155;">
                                <th style="padding:12px 14px; font-weight:700; border-bottom:2px solid #e2e8f0;">{{ __('personnel.name') }}</th>
                                <th style="padding:12px 14px; font-weight:700; border-bottom:2px solid #e2e8f0;">{{ __('personnel.description') }}</th>
                                <th style="width:60px; padding:12px 14px; border-bottom:2px solid #e2e8f0;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rolesData as $i => $item)
                                @php $isExpanded = in_array((string) $item->id, $expandedRoleRows, true); @endphp
                                <tr class="role-main-row {{ $isExpanded ? 'row-expanded' : '' }}"
                                    style="border-bottom:1px solid #f1f5f9; transition:background 0.2s; cursor:pointer;"
                                    wire:click="toggleRoleDetails('{{ $item->id }}')"
                                >
                                    <td style="padding:12px 14px; vertical-align:middle; font-weight:600; color:#0f172a;">
                                        <i class="mdi {{ $isExpanded ? 'mdi-chevron-down' : 'mdi-chevron-right' }} mr-1" style="color:#0284c7;"></i>
                                        <i class="mdi mdi-shield-account-outline mr-1 text-primary"></i>{{ $item->name }}
                                    </td>
                                    <td style="padding:12px 14px; vertical-align:middle; color:#64748b;">{{ $item->description ?? '-' }}</td>
                                    <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                                        <button type="button" class="btn btn-sm" style="border-radius:8px; border:1px solid #fecdd3; color:#e11d48; background:#fff5f7; padding:4px 8px;"
                                            wire:click.stop="openDeleteRoleModal('{{ $item->id }}')" title="{{ __('personnel.delete') }}">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>

                                @if($isExpanded)
                                    <tr id="role-permissions-{{ $item->id }}">
                                        <td colspan="4" class="p-0">
                                            <div class="permission-detail-wrap p-4" style="background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%); border-top:2px solid #dbeafe;">
                                                <h6 class="mb-3" style="color:#0f172a; font-weight:700;">{{ __('personnel.permissions') }}</h6>
                                                @php $permissionModules = $this->getRolePermissionModules((string) $item->id); @endphp
                                                @if($permissionModules->isEmpty())
                                                    <div class="text-muted small">{{ __('personnel.no_data_available') }}</div>
                                                @else
                                                    <div class="row module-tabs mb-4">
                                                        @foreach($permissionModules as $moduleIndex => $module)
                                                            @php
                                                                $modulePaneId = 'role-'.$item->id.'-module-'.$module['module_key'];
                                                                $moduleIcons = [
                                                                    'personnel'  => 'mdi-account-multiple',
                                                                    'inventory'  => 'mdi-package-multiple',
                                                                    'laboratory' => 'mdi-flask',
                                                                    'equipment'  => 'mdi-tools',
                                                                    'dms'        => 'mdi-file-document-multiple',
                                                                    'crm'        => 'mdi-account-box-multiple',
                                                                    'tickets'    => 'mdi-ticket-multiple',
                                                                    'system'     => 'mdi-cog',
                                                                    'ai'         => 'mdi-brain',
                                                                    'analytics'  => 'mdi-chart-line',
                                                                    'calendar'   => 'mdi-calendar',
                                                                    'audit'      => 'mdi-magnify',
                                                                    'risk'       => 'mdi-alert-circle',
                                                                    'matrix'     => 'mdi-grid',
                                                                    'settings'   => 'mdi-cog',
                                                                ];
                                                                $moduleIcon = $moduleIcons[strtolower($module['module_key'])] ?? 'mdi-package';
                                                            @endphp
                                                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                                                @php $isActiveModule = $moduleIndex === 0; @endphp
                                                                <button type="button"
                                                                    class="module-tab-btn {{ $isActiveModule ? 'active' : '' }}"
                                                                    data-module-id="{{ $modulePaneId }}"
                                                                    style="width:100%; height:90px; padding:12px 8px; border:2px solid {{ $isActiveModule ? '#0284c7' : '#e2e8f0' }}; background:#fff; border-radius:12px; box-shadow:{{ $isActiveModule ? '0 4px 12px rgba(2,132,199,0.15)' : '0 1px 4px rgba(15,23,42,0.06)' }}; transition:all 0.2s ease; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; cursor:pointer;">
                                                                    <i class="mdi {{ $moduleIcon }}" style="font-size:22px; color:{{ $isActiveModule ? '#0284c7' : '#64748b' }};"></i>
                                                                    <span style="font-size:12px; font-weight:600; color:{{ $isActiveModule ? '#0284c7' : '#334155' }}; text-align:center; line-height:1.2;">{{ $module['module_label'] }}</span>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="module-content-panels" style="border-top:1px solid #e5e7eb; padding-top:16px;">
                                                        @foreach($permissionModules as $moduleIndex => $module)
                                                            @php $modulePaneId = 'role-'.$item->id.'-module-'.$module['module_key']; @endphp
                                                            <div id="{{ $modulePaneId }}" class="module-pane {{ $moduleIndex !== 0 ? 'd-none' : '' }}">
                                                                <div class="row">
                                                                    @foreach($module['resources'] as $resource)
                                                                        @foreach($resource['permission_names'] as $actionKey => $permissionName)
                                                                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                                                                <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:12px 14px; box-shadow:0 1px 4px rgba(15,23,42,0.05);">
                                                                                    <div class="d-flex align-items-center mb-1">
                                                                                        <span style="width:8px; height:8px; border-radius:50%; background:#10b981; display:inline-block; margin-right:8px; flex-shrink:0;"></span>
                                                                                        <span style="font-weight:600; font-size:13px; color:#0f172a;">{{ $resource['resource_label'] }}</span>
                                                                                    </div>
                                                                                    <small style="display:block; word-break:break-all; font-size:11px; color:#94a3b8; font-family:monospace;">{{ $permissionName }}</small>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('personnel.no_roles_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                    <small class="text-muted">{{ __('personnel.showing_to_of', ['from' => $rolesFrom, 'to' => $rolesTo, 'total' => $rolesTotal]) }}</small>
                    <div class="d-flex" style="gap:4px;">
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:6px;" wire:click="setPage(1, 'rolesPage')" @if($rolesCurrentPage <= 1) disabled @endif>&laquo;</button>
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:6px;" wire:click="previousPage('rolesPage')" @if($rolesCurrentPage <= 1) disabled @endif>&lsaquo;</button>
                        <span class="btn btn-sm btn-light disabled" style="border-radius:6px;">{{ $rolesCurrentPage }} / {{ $rolesTotalPages ?: 1 }}</span>
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:6px;" wire:click="nextPage('rolesPage')" @if($rolesCurrentPage >= $rolesTotalPages) disabled @endif>&rsaquo;</button>
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:6px;" wire:click="setPage($rolesTotalPages, 'rolesPage')" @if($rolesCurrentPage >= $rolesTotalPages) disabled @endif>&raquo;</button>
                    </div>
                </div>
            @endif

            @if($activeTab === 'details')
                <form id="userDetailsForm" autocomplete="off" wire:submit.prevent="saveUserDetails">
                    <div class="detail-stepper mb-4">
                        <button type="button" class="detail-step {{ $detailsStep === 1 ? 'is-active' : ($detailsStep > 1 ? 'is-complete' : '') }}" wire:click="setDetailsStep(1)">
                            <span class="detail-step-index">1</span>
                            <span class="detail-step-copy">
                                <strong>{{ __('personnel.section_personal_information') }}</strong>
                                <small>{{ __('personnel.signature_attachment') }}</small>
                            </span>
                        </button>
                        <button type="button" class="detail-step {{ $detailsStep === 2 ? 'is-active' : ($detailsStep > 2 ? 'is-complete' : '') }}" wire:click="setDetailsStep(2)">
                            <span class="detail-step-index">2</span>
                            <span class="detail-step-copy">
                                <strong>{{ __('personnel.section_employment_details') }}</strong>
                                <small>{{ __('personnel.designation') }} / {{ __('personnel.department') }} / {{ __('personnel.lab') }}</small>
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

                    @if($errors->any())
                        <div class="alert alert-danger detail-step-alert" role="alert">
                            <div class="font-weight-semibold mb-1">Please resolve these items before continuing.</div>
                            @foreach($errors->all() as $errorMessage)
                                <div class="small">{{ $errorMessage }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if($detailsStep === 1)
                    <div class="detail-section mb-4">
                        <div class="detail-section-header mb-3">
                            <h6 class="mb-1"><i class="mdi mdi-account-outline mr-1"></i> {{ __('personnel.section_personal_information') }}</h6>
                            <small class="text-muted">{{ __('personnel.section_personal_information_hint') }}</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.first_name') }} *</label><input wire:model.live="detailsFirstName" class="form-control" required>@error('detailsFirstName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.middle_name') }}</label><input wire:model.live="detailsMiddleName" class="form-control">@error('detailsMiddleName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.last_name') }}</label><input wire:model.live="detailsLastName" class="form-control">@error('detailsLastName')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.email') }} *</label><input type="email" wire:model.live="detailsEmail" class="form-control" required>@error('detailsEmail')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.phone') }}</label><input wire:model.live="detailsPhone" class="form-control">@error('detailsPhone')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.id_number_passport') }}</label><input wire:model.live="detailsIdNumber" class="form-control">@error('detailsIdNumber')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.date_of_birth') }}</label><input type="date" wire:model.live="detailsDateOfBirth" class="form-control">@error('detailsDateOfBirth')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                        </div>

                        <div class="signature-section mt-2">
                            <div class="signature-section-head d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1"><i class="mdi mdi-draw-pen mr-1"></i>{{ __('personnel.signature_attachment') }}</h6>
                                    <small class="text-muted">{{ __('personnel.signature_section_intro') }}</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="signature-card h-100">
                                        <label class="signature-label d-block">{{ __('personnel.upload_signature') }}</label>
                                        <input type="file" id="personnelSignatureUpload" wire:model="detailsSignatureUpload" class="form-control" accept="image/*">
                                        <small class="text-muted d-block mt-2">{{ __('personnel.accepted_signature_formats') }}</small>
                                        @error('detailsSignatureUpload')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                                        @if(!empty($this->user->electronic_sig))
                                            <div class="signature-preview mt-3">
                                                <small class="text-muted d-block mb-1">{{ __('personnel.current_signature') }}</small>
                                                <img src="{{ $this->user->electronic_sig }}" alt="{{ __('personnel.current_signature') }}" class="img-fluid">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="signature-card h-100">
                                        <label class="signature-label d-block">{{ __('personnel.sign_using_pad') }}</label>
                                        <div class="signature-canvas-wrap" id="personnelSignatureCanvasWrap" wire:ignore>
                                            <canvas id="personnelSignatureCanvas" width="620" height="190"></canvas>
                                            <span class="signature-canvas-placeholder" id="personnelSignaturePlaceholder">{{ __('personnel.sign_here') }}</span>
                                        </div>
                                        <input type="hidden" id="personnelSignatureData" wire:model="detailsSignatureData">
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                <small class="text-muted">{{ __('personnel.signature_draw_overrides_upload') }}</small>
                                                <span
                                                    id="personnelSignatureStatus"
                                                    class="signature-status signature-status-empty"
                                                    aria-live="polite"
                                                    data-signed-label="Signed"
                                                    data-unsigned-label="Not signed"
                                                >Not signed</span>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearPersonnelSignaturePad">
                                                <i class="mdi mdi-eraser"></i> {{ __('personnel.clear') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($detailsStep === 2)
                    <div class="detail-section mb-4">
                        <div class="detail-section-header mb-3">
                            <h6 class="mb-1"><i class="mdi mdi-briefcase-outline mr-1"></i> {{ __('personnel.section_employment_details') }}</h6>
                            <small class="text-muted">{{ __('personnel.section_employment_details_hint') }}</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>{{ __('personnel.employment_date') }}</label><input type="date" wire:model.live="detailsEmploymentDate" class="form-control">@error('detailsEmploymentDate')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                            <div class="col-md-4">
                                <label>{{ __('personnel.designation') }} *</label>
                                <div class="tag-select-container" wire:click="$set('showDesignationDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($selectedDesignationId)
                                            @php $s = $this->designations->firstWhere('id', $selectedDesignationId); @endphp
                                            <span class="tag-badge">{{ $s->display_name ?? $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDesignation"></i></span>
                                        @endif
                                        <input class="tag-input" wire:model.live="designationSearch" wire:keyup="searchDesignation" placeholder="{{ __('personnel.search_designation') }}">
                                    </div>
                                    @if($showDesignationDropdown)
                                        <div class="tag-dropdown">@foreach($this->designations->filter(fn($d)=>$designationSearch===''||stripos($d->display_name ?? $d->name,$designationSearch)!==false||stripos($d->name,$designationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDesignation('{{ $d->id }}')">{{ $d->display_name ?? $d->name }}</div>@endforeach</div>
                                    @endif
                                </div>
                                @error('selectedDesignationId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label>{{ __('personnel.education_level') }}</label>
                                <div class="tag-select-container" wire:click="$set('showEducationDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($selectedEducationId)
                                            @php $s = $this->educationLevels->firstWhere('id', $selectedEducationId); @endphp
                                            <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearEducation"></i></span>
                                        @endif
                                        <input class="tag-input" wire:model.live="educationSearch" wire:keyup="searchEducation" placeholder="{{ __('personnel.search_education_level') }}">
                                    </div>
                                    @if($showEducationDropdown)
                                        <div class="tag-dropdown">@foreach($this->educationLevels->filter(fn($d)=>$educationSearch===''||stripos($d->name,$educationSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectEducation('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                    @endif
                                </div>
                                @error('selectedEducationId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label>{{ __('personnel.position') }} *</label>
                                <div class="tag-select-container" wire:click="$set('showPositionDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($selectedPositionId)
                                            @php $s = $this->positions->firstWhere('id', $selectedPositionId); @endphp
                                            <span class="tag-badge">{{ $s->display_name ?? $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearPosition"></i></span>
                                        @endif
                                        <input class="tag-input" wire:model.live="positionSearch" wire:keyup="searchPosition" placeholder="{{ __('personnel.search_position') }}">
                                    </div>
                                    @if($showPositionDropdown)
                                        <div class="tag-dropdown">@foreach($this->positions->filter(fn($d)=>$positionSearch===''||stripos($d->display_name ?? $d->name,$positionSearch)!==false||stripos($d->name,$positionSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectPosition('{{ $d->id }}')">{{ $d->display_name ?? $d->name }}</div>@endforeach</div>
                                    @endif
                                </div>
                                @error('selectedPositionId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label>{{ __('personnel.department') }} *</label>
                                <div class="tag-select-container" wire:click="$set('showDepartmentDropdown', true)">
                                    <div class="tag-select-input">
                                        @if($selectedDepartmentId)
                                            @php $s = $this->departments->firstWhere('id', $selectedDepartmentId); @endphp
                                            <span class="tag-badge">{{ $s->name ?? 'Selected department' }}<i class="mdi mdi-close-circle" wire:click.stop="clearDepartment"></i></span>
                                        @endif
                                        <input class="tag-input" wire:model.live="departmentSearch" wire:keyup="searchDepartment" placeholder="{{ __('personnel.search_department') }}">
                                    </div>
                                    @if($showDepartmentDropdown)
                                        <div class="tag-dropdown">@foreach($this->departments->filter(fn($d)=>$departmentSearch===''||stripos($d->name,$departmentSearch)!==false) as $d)<div class="tag-dropdown-item" wire:click.stop="selectDepartment('{{ $d->id }}')">{{ $d->name }}</div>@endforeach</div>
                                    @endif
                                </div>
                                @error('selectedDepartmentId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label>{{ __('personnel.lab') }}</label>
                                <div class="tag-select-container" wire:click="$set('showLabDropdown', true)">
                                    <div class="tag-select-input">
                                        @foreach($selectedLabIds as $selectedLabId)
                                            @php $s = $this->labs->firstWhere('id', $selectedLabId); @endphp
                                            <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearLab(@js($selectedLabId))"></i></span>
                                        @endforeach
                                        <input class="tag-input" wire:model.live="labSearch" wire:keyup="searchLab" placeholder="{{ __('personnel.search_labs') }}">
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
                                <label>{{ __('personnel.lab_sections') }}</label>
                                <div class="tag-select-container" wire:click="$set('showLabSectionDropdown', true)">
                                    <div class="tag-select-input">
                                        @foreach($selectedLabSectionIds as $selectedSectionId)
                                            @php $s = $this->labSections->firstWhere('id', $selectedSectionId); @endphp
                                            <span class="tag-badge">{{ $s->name ?? '' }}<i class="mdi mdi-close-circle" wire:click.stop="clearLabSection(@js($selectedSectionId))"></i></span>
                                        @endforeach
                                        <input class="tag-input" wire:model.live="labSectionSearch" wire:keyup="searchLabSection" placeholder="Search lab sections...">
                                    </div>
                                    @if($showLabSectionDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->labSections->filter(fn($l)=>$labSectionSearch===''||stripos($l->name,$labSectionSearch)!==false||stripos((string)($l->code ?? ''),$labSectionSearch)!==false) as $section)
                                                <div class="tag-dropdown-item {{ in_array($section->id, $selectedLabSectionIds, true) ? 'tag-dropdown-item-selected' : '' }}"
                                                     wire:click.stop="selectLabSection(@js($section->id))">
                                                    {{ $section->code }} - {{ $section->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No lab sections found. Create lab sections under Lab Sections management.</div>
                                            @endforelse
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
                            <small class="text-muted">Track gazette status and career timeline for automatic experience visibility.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="mb-2 d-block">Analyst Gazette Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="analystIsGazzetted" wire:model.live="detailsAnalystIsGazzetted">
                                        <label class="form-check-label" for="analystIsGazzetted">Analyst is gazzetted</label>
                                    </div>
                                    @error('detailsAnalystIsGazzetted')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4" id="dateOfGazzetteWrap">
                                <div class="form-group">
                                    <label>Date of Gazzette</label>
                                    <input type="date" id="dateOfGazzetteInput" class="form-control" wire:model.live="detailsDateOfGazzette">
                                    @error('detailsDateOfGazzette')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4" id="gazzetteNoWrap">
                                <div class="form-group">
                                    <label>Gazzette No</label>
                                    <input type="text" id="gazzetteNoInput" class="form-control" wire:model.live="detailsGazzetteNo">
                                    @error('detailsGazzetteNo')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start of Career</label>
                                    <input type="date" id="startOfCareerInput" class="form-control" wire:model.live="detailsStartOfCareer">
                                    @error('detailsStartOfCareer')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Years of Experience</label>
                                    <div id="experienceYearsPreview" class="form-control bg-light d-flex align-items-center">--</div>
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
                                <button class="btn btn-primary detail-save-btn" type="submit"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            @if($activeTab === 'work_history')
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="workHistorySearch" placeholder="{{ __('personnel.search_department') }}">
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" wire:model.live="workHistoryPerPage">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @php
                    $whData = $this->workHistoryPage;
                    $whTotal = $this->workHistoryTotal;
                    $whCurrentPage = $this->getPage('workPage');
                    $whTotalPages = (int) ceil($whTotal / $workHistoryPerPage);
                    $whFrom = $whTotal > 0 ? (($whCurrentPage - 1) * $workHistoryPerPage) + 1 : 0;
                    $whTo = min($whCurrentPage * $workHistoryPerPage, $whTotal);
                @endphp
                @if($whData->isEmpty())
                    <div class="text-center py-5">
                        <i class="mdi mdi-briefcase-clock-outline" style="font-size: 3rem; color: #cbd5e0;"></i>
                        <h5 class="mt-3 text-muted">{{ __('personnel.no_work_history_found') }}</h5>
                        <p class="text-muted small mb-3">Add department and job description changes for this person.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" wire:click="openWorkHistoryModal">
                            <i class="mdi mdi-plus"></i> {{ __('personnel.add') }}
                        </button>
                    </div>
                @else
                    <div class="table-responsive bg-light p-3">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('personnel.department') }}</th>
                                    <th>{{ __('personnel.job_description') }}</th>
                                    <th>{{ __('personnel.start_date') }}</th>
                                    <th>{{ __('personnel.end_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($whData as $i => $item)
                                    <tr>
                                        <td>{{ $whFrom + $i }}</td>
                                        <td>{{ $item->department_name }}</td>
                                        <td>{{ $item->position }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{!! trim($item->end_date) != '' ? $item->end_date : '<i class="mdi mdi-check-circle text-success"></i> '.__('personnel.current') !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">{{ __('personnel.showing_to_of', ['from' => $whFrom, 'to' => $whTo, 'total' => $whTotal]) }}</small>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" wire:click="setPage(1, 'workPage')" @if($whCurrentPage <= 1) disabled @endif>&laquo;</button>
                            <button class="btn btn-sm btn-outline-secondary" wire:click="previousPage('workPage')" @if($whCurrentPage <= 1) disabled @endif>&lsaquo;</button>
                            <span class="btn btn-sm btn-light disabled">{{ $whCurrentPage }} / {{ $whTotalPages ?: 1 }}</span>
                            <button class="btn btn-sm btn-outline-secondary" wire:click="nextPage('workPage')" @if($whCurrentPage >= $whTotalPages) disabled @endif>&rsaquo;</button>
                            <button class="btn btn-sm btn-outline-secondary" wire:click="setPage($whTotalPages, 'workPage')" @if($whCurrentPage >= $whTotalPages) disabled @endif>&raquo;</button>
                        </div>
                    </div>
                @endif
            @endif

            @if($activeTab === 'certifications')
                @if($this->personnelCertifications->isEmpty())
                    <div class="text-center py-5 capability-empty-card">
                        <i class="mdi mdi-file-certificate-outline capability-empty-icon"></i>
                        <h5 class="mt-3 text-muted mb-1">{{ __('personnel.no_certifications_uploaded_yet') }}</h5>
                        <p class="text-muted small mb-0">{{ __('personnel.add_certification_records_hint') }}</p>
                    </div>
                @else
                    <div class="table-responsive bg-light p-3">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('personnel.title') }}</th>
                                    <th>{{ __('personnel.certifying_body') }}</th>
                                    <th>{{ __('personnel.validity_period') }}</th>
                                    <th>{{ __('personnel.attachment') }}</th>
                                    <th style="width: 120px;">{{ __('personnel.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->personnelCertifications as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->certifying_body }}</td>
                                        <td>
                                            <span class="d-block">{{ $item->valid_from ? \Carbon\Carbon::parse($item->valid_from)->format('d M Y') : '-' }}</span>
                                            <small class="text-muted">{{ __('personnel.to') }} {{ $item->valid_to ? \Carbon\Carbon::parse($item->valid_to)->format('d M Y') : __('personnel.no_expiry') }}</small>
                                        </td>
                                        <td>
                                            @if(!empty($item->attachment_path))
                                                <a href="{{ asset('storage/' . $item->attachment_path) }}" target="_blank" class="btn btn-sm pm-act-btn pm-act-btn--info">
                                                    <i class="mdi mdi-paperclip"></i> {{ __('personnel.view') }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openCertificationModal('{{ $item->id }}')" title="{{ __('personnel.edit') }}"><i class="mdi mdi-pencil"></i></button>
                                            <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteCertificationModal('{{ $item->id }}')" title="{{ __('personnel.delete') }}"><i class="mdi mdi-delete"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            @if($activeTab === 'capability_matrix')
                @if($this->capabilityRows->isEmpty())
                    <div class="text-center py-5 capability-empty-card">
                        <i class="mdi mdi-view-grid-outline capability-empty-icon"></i>
                        <h5 class="mt-3 text-muted mb-1">{{ __('personnel.no_capability_mapping_found') }}</h5>
                        <p class="text-muted small mb-0">{{ __('personnel.no_capability_mapping_hint') }}</p>
                    </div>
                @else
                    <div class="table-responsive bg-light p-3">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>{{ __('personnel.matrix') }}</th>
                                    <th>{{ __('personnel.competency_area') }}</th>
                                    <th>{{ __('personnel.competency_type') }}</th>
                                    <th>{{ __('personnel.competency') }}</th>
                                    <th>{{ __('personnel.required_proficiency') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->capabilityRows as $row)
                                    <tr>
                                        <td>{{ $row->matrix_label }}</td>
                                        <td>{{ $row->competency_area ?: '-' }}</td>
                                        <td>{{ $row->competency_type ?: '-' }}</td>
                                        <td>{{ $row->competency_description ?: '-' }}</td>
                                        <td>
                                            <span class="badge badge-pill capability-badge" style="background-color: {{ $row->proficiency_color ?: '#64748b' }}; color: #fff;">
                                                {{ $row->proficiency_code ? $row->proficiency_code . ' - ' : '' }}{{ $row->proficiency_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            @if($activeTab === 'attachments')
                @livewire('personnel.tabs.personnel-attachments-tab', ['user' => $this->user], key('personnel-attachments-'.$this->user->id))
            @endif
        </div>
    </div>

    @if($showAddRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h4 class="modal-title"><i class="mdi mdi-key-plus"></i> {{ __('personnel.add_user_role') }}</h4><button type="button" class="close" wire:click="closeAddRoleModal"><span>&times;</span></button></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('personnel.select_roles') }}</label>
                            <div class="tag-select-container" wire:click="$set('showRoleDropdown', true)">
                                <div class="tag-select-input">
                                    @foreach($selectedRoleIds as $roleId)
                                        @php $role = $this->allRoles->firstWhere('id', $roleId); @endphp
                                        @if($role)
                                            <span class="tag-badge">{{ $role->name }}<i class="mdi mdi-close-circle" wire:click.stop="removeSelectedRole('{{ $roleId }}')"></i></span>
                                        @endif
                                    @endforeach
                                    <input class="tag-input" wire:model.live="roleSearch" wire:keyup="searchRoles" placeholder="{{ __('personnel.search_roles') }}" autocomplete="off">
                                </div>
                                @if($showRoleDropdown)
                                    <div class="tag-dropdown">
                                        @foreach($this->allRoles->filter(fn($r) => $roleSearch === '' || stripos($r->name, $roleSearch) !== false) as $role)
                                            <div class="tag-dropdown-item d-flex justify-content-between" wire:click.stop="toggleRoleSelection('{{ $role->id }}')">
                                                <span>{{ $role->name }}</span>
                                                @if(in_array($role->id, $selectedRoleIds, true))<i class="mdi mdi-check text-success"></i>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary" wire:click="addSelectedRoles"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button><button type="button" class="btn btn-default" wire:click="closeAddRoleModal">{{ __('personnel.close') }}</button></div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.confirm_delete') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteRoleModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            {{ __('personnel.confirm_remove_role', ['role' => $selectedRoleName]) }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="removeRole"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteRoleModal">{{ __('personnel.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showWorkHistoryModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Work History</h4>
                        <button type="button" class="close" wire:click="closeWorkHistoryModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Record a department / job description assignment for this person.</p>
                        <div class="form-group">
                            <label>{{ __('personnel.department') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('workHistoryDepartmentId') is-invalid @enderror" wire:model="workHistoryDepartmentId">
                                <option value="">Select department...</option>
                                @foreach($this->departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('workHistoryDepartmentId')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('personnel.job_description') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('workHistoryJobId') is-invalid @enderror" wire:model="workHistoryJobId">
                                <option value="">Select job description...</option>
                                @foreach($this->designations as $job)
                                    <option value="{{ $job->id }}">{{ $job->name }}</option>
                                @endforeach
                            </select>
                            @error('workHistoryJobId')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="work_history_is_current" wire:model.live="workHistoryIsCurrent">
                                <label class="form-check-label" for="work_history_is_current">{{ __('personnel.current') }} assignment</label>
                            </div>
                            <small class="text-muted">Ends the previous open assignment and updates this person's department / job description.</small>
                        </div>
                        @if(! $workHistoryIsCurrent)
                            <div class="form-group mb-0">
                                <label>{{ __('personnel.end_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('workHistoryEndDate') is-invalid @enderror" wire:model="workHistoryEndDate">
                                @error('workHistoryEndDate')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveWorkHistory"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeWorkHistoryModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered certification-modal-shell">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingPersonnelCertificationId ? 'pencil' : 'plus' }}"></i> {{ $editingPersonnelCertificationId ? __('personnel.edit_certification') : __('personnel.add_certification') }}</h4>
                        <button type="button" class="close" wire:click="closeCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="cert-modal-intro mb-3">
                            <p class="mb-0 text-muted">{{ __('personnel.capture_certification_intro') }}</p>
                        </div>
                        <div class="form-group">
                            <label class="cert-modal-label">{{ __('personnel.title') }}</label>
                            <input type="text" class="form-control cert-modal-input" wire:model="certificationTitle" placeholder="{{ __('personnel.certification_title_placeholder') }}">
                            @error('certificationTitle')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="cert-modal-label">{{ __('personnel.certifying_body') }}</label>
                            <input type="text" class="form-control cert-modal-input" wire:model="certificationBody" placeholder="{{ __('personnel.certifying_body_placeholder') }}">
                            @error('certificationBody')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="cert-modal-label">{{ __('personnel.valid_from') }}</label>
                                    <input type="date" class="form-control cert-modal-input" wire:model="certificationValidFrom">
                                    @error('certificationValidFrom')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="cert-modal-label">{{ __('personnel.valid_to') }}</label>
                                    <input type="date" class="form-control cert-modal-input" wire:model="certificationValidTo">
                                    @error('certificationValidTo')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="cert-modal-label">{{ __('personnel.attachment') }}</label>
                            <input type="file" class="form-control cert-modal-input" wire:model="certificationAttachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            @if($certificationExistingAttachmentPath)
                                <small class="text-muted d-block mt-2">{{ __('personnel.current_file') }}: {{ basename($certificationExistingAttachmentPath) }}</small>
                            @endif
                            @error('certificationAttachment')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveCertification"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeCertificationModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.confirm_delete') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">{{ __('personnel.confirm_delete_certification_simple') }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteCertification"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteCertificationModal">{{ __('personnel.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .personnel-detail-tabs {
            border-bottom: none;
            gap: 8px;
            flex-wrap: wrap;
        }

        .personnel-detail-tabs .nav-link {
            border: 1px solid #dbe3ef;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            color: #334155;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 8px 14px;
            transition: all 0.2s ease;
        }

        .personnel-detail-tabs .nav-link:hover {
            border-color: #0ea5e9;
            color: #0f172a;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.16);
            transform: translateY(-1px);
        }

        .personnel-detail-tabs .nav-link.active {
            border-color: #0284c7;
            color: #ffffff;
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 55%, #0ea5e9 100%);
            box-shadow: 0 8px 18px rgba(2, 132, 199, 0.28);
        }

        .role-permission-table tbody tr.role-main-row td { vertical-align: middle; }
        .role-permission-table tbody tr.role-main-row { transition: background-color 0.2s ease; }
        .role-permission-table tbody tr.role-main-row:hover { background: #f1f5f9; }
        .role-permission-table tbody tr.role-main-row.row-expanded { background: #eef6ff; }
        .permission-detail-wrap { background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%); border-top: 1px solid #dbeafe; }
        .module-card-tabs .module-summary-card {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            min-height: 116px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .module-card-tabs .module-summary-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            border-color: #10b981;
        }

        .module-card-tabs .module-summary-card.active {
            background: #ecfdf5;
            border-color: #10b981;
            box-shadow: inset 0 0 0 1px #10b981;
        }

        .module-kicker {
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #6b7280;
        }
        
        .badge-block {
            border-radius: 6px !important;
            font-weight: 600 !important;
            letter-spacing: 0.3px !important;
        }
        
        .badge-success {
            background: #10b981 !important;
        }
        
        .badge-secondary {
            background: #9ca3af !important;
        }

        .module-content-panels {
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
        }

        .permission-info-card {
            height: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .permission-info-card:hover {
            border-color: #86efac;
            box-shadow: 0 6px 14px rgba(22, 163, 74, 0.1);
        }

        .permission-dot-badge {
            color: #10b981;
            font-size: 1rem;
            line-height: 1;
        }

        .permission-db-name {
            display: block;
            font-family: monospace;
            color: #6b7280;
            font-size: 0.78rem;
            word-break: break-word;
        }

        .tag-select-container {
            position: relative;
            cursor: text;
            width: 100%;
            overflow: visible;
            z-index: 1;
        }

        .tag-select-container.dropdown-open {
            z-index: 1100;
        }

        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            width: 100%;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
            overflow: hidden;
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
            max-width: 100%;
            padding: 4px 10px;
            background-color: #007bff;
            color: #fff;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

        .tag-badge i:hover {
            opacity: 1;
        }

        .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
            background: transparent;
        }

        .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
            margin-top: 6px;
        }

        .tag-select-container.tag-dropdown-up .tag-dropdown {
            top: auto;
            bottom: calc(100% + 6px);
            margin-top: 0;
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

        .detail-form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .detail-save-btn {
            border-radius: 8px;
            padding-left: 14px;
            padding-right: 14px;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.18);
        }

        .detail-stepper {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .detail-step {
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

        .detail-step:hover {
            border-color: #93c5fd;
            box-shadow: 0 10px 24px rgba(14, 165, 233, 0.12);
            transform: translateY(-1px);
        }

        .detail-step.is-active {
            border-color: #0284c7;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
            box-shadow: 0 14px 28px rgba(2, 132, 199, 0.16);
        }

        .detail-step.is-complete {
            border-color: #86efac;
            background: linear-gradient(135deg, #ecfdf5 0%, #f8fffb 100%);
        }

        .detail-step-index {
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

        .detail-step.is-active .detail-step-index {
            background: #0284c7;
            color: #ffffff;
        }

        .detail-step.is-complete .detail-step-index {
            background: #16a34a;
            color: #ffffff;
        }

        .detail-step-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .detail-step-copy strong {
            font-size: 0.9rem;
            color: #0f172a;
        }

        .detail-step-copy small {
            color: #64748b;
            font-size: 0.77rem;
        }

        .detail-step-footer {
            border-top: 1px solid #e2e8f0;
            background: #fbfdff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
        }

        .detail-step-footer-copy {
            font-weight: 600;
        }

        .detail-step-alert {
            border-radius: 10px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            margin-bottom: 14px;
            padding: 10px 12px;
        }

        .detail-section {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            padding: 16px 16px 4px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .detail-section-header h6 {
            font-weight: 700;
            color: #0f172a;
        }

        .detail-section .form-group label,
        .detail-section > .row > div > label {
            color: #334155;
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.2px;
            margin-bottom: 6px;
        }

        .signature-section {
            border-top: 1px dashed #dbe3ef;
            margin-top: 10px;
            padding-top: 14px;
        }

        .signature-section-head h6 {
            font-weight: 700;
            color: #0f172a;
        }

        .signature-card {
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 14px;
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.05);
        }

        .signature-label {
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .signature-preview {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            padding: 8px;
        }

        .signature-preview img {
            max-height: 76px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .signature-canvas-wrap {
            position: relative;
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            background: #ffffff;
            overflow: hidden;
        }

        .signature-canvas-wrap canvas {
            display: block;
            width: 100%;
            height: 190px;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-canvas-placeholder {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: #94a3b8;
            font-size: 0.9rem;
            pointer-events: none;
            font-style: italic;
        }

        .signature-status {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .signature-status-empty {
            color: #9a3412;
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .signature-status-signed {
            color: #065f46;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .cert-add-btn {
            padding-left: 14px;
            padding-right: 14px;
            font-weight: 600;
        }

        .certification-modal-shell .modal-content {
            border-radius: 14px;
            border: 1px solid #dbe3ef;
            box-shadow: 0 20px 48px rgba(2, 6, 23, 0.24);
            overflow: hidden;
        }

        .certification-modal-shell .modal-header {
            background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
            border-bottom: 1px solid #dbe3ef;
        }

        .certification-modal-shell .modal-header .modal-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.06rem;
        }

        .cert-modal-intro {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .cert-modal-label {
            color: #334155;
            font-weight: 700;
            font-size: 0.83rem;
            letter-spacing: 0.2px;
            margin-bottom: 6px;
        }

        .cert-modal-input {
            border-radius: 10px;
            border: 1px solid #d1d9e6;
            background: #ffffff;
            min-height: 40px;
        }

        .cert-modal-input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.14);
        }

        .certification-modal-shell .modal-footer {
            border-top: 1px solid #e2e8f0;
            background: #fbfdff;
        }

        .capability-empty-card {
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .capability-empty-icon {
            font-size: 3.1rem;
            color: #94a3b8;
        }

        .capability-badge {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            padding: 5px 10px;
        }

        .pm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .pm-act-btn:last-child {
            margin-right: 0;
        }

        .pm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .pm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .pm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .pm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        .pm-act-btn--info {
            border: 1px solid #bae6fd;
            color: #0369a1;
            background: #f0f9ff;
        }

        .pm-act-btn--info:hover {
            background: #e0f2fe;
            border-color: #7dd3fc;
        }

        @media (max-width: 767.98px) {
            .detail-form-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-stepper {
                grid-template-columns: 1fr;
            }

            .detail-step-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .detail-step-footer > div {
                width: 100%;
            }

            .detail-save-btn {
                width: 100%;
            }
        }

        @media (max-width: 991.98px) and (min-width: 768px) {
            .detail-stepper {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
    <script>
        (function () {
            if (window.__personnelSignaturePadInit) {
                return;
            }
            window.__personnelSignaturePadInit = true;

            let isDrawing = false;
            let hasSignatureStroke = false;

            function getPadNodes() {
                const canvas = document.getElementById('personnelSignatureCanvas');
                const hiddenInput = document.getElementById('personnelSignatureData');
                const clearBtn = document.getElementById('clearPersonnelSignaturePad');
                const placeholder = document.getElementById('personnelSignaturePlaceholder');
                const uploadInput = document.getElementById('personnelSignatureUpload');
                const form = canvas ? canvas.closest('form') : null;

                return { canvas, hiddenInput, clearBtn, placeholder, uploadInput, form };
            }

            function pointFromEvent(event, canvas) {
                const rect = canvas.getBoundingClientRect();
                const source = event.touches && event.touches[0] ? event.touches[0] : event;
                return {
                    x: (source.clientX - rect.left) * (canvas.width / rect.width),
                    y: (source.clientY - rect.top) * (canvas.height / rect.height),
                };
            }

            function pushSignatureToLivewire(value) {
                const { canvas, hiddenInput } = getPadNodes();
                const root = (canvas && canvas.closest('[wire\\:id]'))
                    || (hiddenInput && hiddenInput.closest('[wire\\:id]'))
                    || document.querySelector('[wire\\:id]');

                if (!root || !window.Livewire || typeof Livewire.find !== 'function') {
                    return;
                }

                const component = Livewire.find(root.getAttribute('wire:id'));
                if (!component || typeof component.set !== 'function') {
                    return;
                }

                // false = update client snapshot only; included on the next save request (avoids race)
                component.set('detailsSignatureData', value, false);
            }

            function syncHiddenSignature() {
                const { canvas, hiddenInput } = getPadNodes();
                if (!canvas || !hiddenInput) {
                    return;
                }

                if (hasSignatureStroke) {
                    hiddenInput.value = canvas.toDataURL('image/png');
                } else {
                    hiddenInput.value = '';
                }

                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                pushSignatureToLivewire(hiddenInput.value);

                setSignatureStatus(hasSignatureStroke);
            }

            function setSignatureStatus(isSigned) {
                const statusBadge = document.getElementById('personnelSignatureStatus');
                if (!statusBadge) {
                    return;
                }

                const signedLabel = statusBadge.getAttribute('data-signed-label') || 'Signed';
                const unsignedLabel = statusBadge.getAttribute('data-unsigned-label') || 'Not signed';
                statusBadge.textContent = isSigned ? signedLabel : unsignedLabel;
                statusBadge.classList.toggle('signature-status-signed', isSigned);
                statusBadge.classList.toggle('signature-status-empty', !isSigned);
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
                    clearBtn.addEventListener('click', function () {
                        clearPad();
                    });
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
                    form.addEventListener('submit', function () {
                        syncHiddenSignature();
                    }, true);
                }

                // Also sync before any save button that targets this form / Livewire method
                if (!window.__personnelSignatureSaveSyncBound) {
                    window.__personnelSignatureSaveSyncBound = true;
                    document.addEventListener('click', function (event) {
                        const trigger = event.target.closest('#userDetailsForm [type="submit"], [wire\\:click="saveUserDetails"], .detail-save-btn');
                        if (!trigger) {
                            return;
                        }
                        syncHiddenSignature();
                    }, true);
                }

                setSignatureStatus(hasSignatureStroke);
            }

            document.addEventListener('DOMContentLoaded', bindPad);
            document.addEventListener('livewire:load', bindPad);
            document.addEventListener('livewire:navigated', bindPad);
            document.addEventListener('livewire:update', bindPad);

            // Livewire v3: canvas is conditionally rendered — watch for it entering the DOM
            const padObserver = new MutationObserver(function () {
                const canvas = document.getElementById('personnelSignatureCanvas');
                if (canvas && canvas.dataset.bound !== '1') {
                    bindPad();
                }
            });
            padObserver.observe(document.body, { childList: true, subtree: true });

            // Livewire v3 hook — re-bind after each component commit (tab switch re-renders)
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

        document.addEventListener('click', function (event) {
            const isSignaturePadClick = event.target.closest('#personnelSignatureCanvasWrap') || event.target.closest('#clearPersonnelSignaturePad');
            const isModuleTabClick = event.target.closest('.module-tab-btn');
            const isTagSelectClick = event.target.closest('.tag-select-container');
            // Avoid racing Livewire step/tab/save actions with a second closeSelectDropdowns request.
            const isWireActionClick = event.target.closest(
                '.detail-step-footer button, .detail-step, .detail-save-btn, [wire\\:click], [wire\\:submit], button[type="submit"], a'
            );

            if (
                !isTagSelectClick &&
                !isSignaturePadClick &&
                !isModuleTabClick &&
                !isWireActionClick &&
                document.querySelector('.tag-dropdown')
            ) {
                $wire.closeSelectDropdowns();
            }

            // Module tab switching — pure JS, no Livewire round-trip
            if (isModuleTabClick) {
                event.preventDefault();
                event.stopPropagation();

                const btn = isModuleTabClick;
                const wrap = btn.closest('.permission-detail-wrap');
                if (!wrap) return;

                // Deactivate all tab buttons in this wrap
                wrap.querySelectorAll('.module-tab-btn').forEach(function (b) {
                    b.classList.remove('active');
                    b.style.borderColor = '#e2e8f0';
                    b.querySelector('.mdi').style.color = '#64748b';
                    b.querySelector('span').style.color = '#334155';
                });

                // Activate clicked button
                btn.classList.add('active');
                btn.style.borderColor = '#0284c7';
                btn.querySelector('.mdi').style.color = '#0284c7';
                btn.querySelector('span').style.color = '#0284c7';

                // Hide all panes
                wrap.querySelectorAll('.module-pane').forEach(function (p) {
                    p.classList.add('d-none');
                });

                // Show target pane
                const targetId = btn.getAttribute('data-module-id');
                if (targetId) {
                    const targetPane = document.getElementById(targetId);
                    if (targetPane) {
                        targetPane.classList.remove('d-none');
                    }
                }
            }
        });

        (function () {
            function applyDropdownDirection() {
                const containers = document.querySelectorAll('.tag-select-container');
                if (!containers.length) {
                    return;
                }

                containers.forEach(function (container) {
                    const dropdown = container.querySelector('.tag-dropdown');
                    if (!dropdown) {
                        container.classList.remove('tag-dropdown-up');
                        container.classList.remove('dropdown-open');
                        return;
                    }

                    container.classList.add('dropdown-open');
                    container.classList.remove('tag-dropdown-up');

                    const input = container.querySelector('.tag-select-input');
                    const anchorRect = (input || container).getBoundingClientRect();
                    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                    const spaceBelow = viewportHeight - anchorRect.bottom;
                    const spaceAbove = anchorRect.top;
                    const preferredHeight = Math.min(dropdown.scrollHeight, 250);

                    if (spaceBelow < preferredHeight && spaceAbove > spaceBelow) {
                        container.classList.add('tag-dropdown-up');
                    }
                });
            }

            function queueApplyDropdownDirection() {
                requestAnimationFrame(applyDropdownDirection);
            }

            document.addEventListener('click', queueApplyDropdownDirection);
            document.addEventListener('keyup', queueApplyDropdownDirection);
            document.addEventListener('input', queueApplyDropdownDirection);
            window.addEventListener('resize', queueApplyDropdownDirection);
            window.addEventListener('scroll', queueApplyDropdownDirection, true);
            document.addEventListener('DOMContentLoaded', queueApplyDropdownDirection);
            document.addEventListener('livewire:load', queueApplyDropdownDirection);
            document.addEventListener('livewire:navigated', queueApplyDropdownDirection);
            document.addEventListener('livewire:update', queueApplyDropdownDirection);

            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && typeof Livewire.hook === 'function') {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            queueMicrotask(queueApplyDropdownDirection);
                        });
                    });
                }
            });
        })();

        (function () {
            function bindCareerFields() {
                const gazzettedCheckbox = document.getElementById('analystIsGazzetted');
                const gazzetteWrap = document.getElementById('dateOfGazzetteWrap');
                const gazzetteInput = document.getElementById('dateOfGazzetteInput');
                const gazzetteNoWrap = document.getElementById('gazzetteNoWrap');
                const gazzetteNoInput = document.getElementById('gazzetteNoInput');
                const startCareerInput = document.getElementById('startOfCareerInput');
                const experiencePreview = document.getElementById('experienceYearsPreview');

                if (!startCareerInput || !experiencePreview) {
                    return;
                }

                function renderGazzetteDateVisibility() {
                    if (!gazzettedCheckbox || !gazzetteWrap || !gazzetteNoWrap) {
                        return;
                    }

                    const isChecked = gazzettedCheckbox.checked;
                    gazzetteWrap.style.display = isChecked ? '' : 'none';
                    gazzetteNoWrap.style.display = isChecked ? '' : 'none';

                    if (!isChecked && gazzetteInput) {
                        gazzetteInput.value = '';
                    }

                    if (!isChecked && gazzetteNoInput) {
                        gazzetteNoInput.value = '';
                    }
                }

                function renderExperienceYears() {
                    const value = startCareerInput.value;
                    if (!value) {
                        experiencePreview.textContent = '--';
                        return;
                    }

                    // Support both datetime-local (YYYY-MM-DDTHH:mm) and loose date formats.
                    let startDate = new Date(value);
                    if (Number.isNaN(startDate.getTime())) {
                        startDate = new Date(String(value).replace(' ', 'T'));
                    }

                    if (Number.isNaN(startDate.getTime())) {
                        experiencePreview.textContent = '--';
                        return;
                    }

                    const now = new Date();
                    if (startDate > now) {
                        experiencePreview.textContent = '0 years';
                        return;
                    }

                    let years = now.getFullYear() - startDate.getFullYear();
                    let months = now.getMonth() - startDate.getMonth();
                    const dayDiff = now.getDate() - startDate.getDate();

                    if (dayDiff < 0) {
                        months -= 1;
                    }

                    if (months < 0) {
                        years -= 1;
                        months += 12;
                    }

                    years = Math.max(0, years);
                    months = Math.max(0, months);

                    if (years === 0 && months === 0) {
                        experiencePreview.textContent = '0 months';
                        return;
                    }

                    const parts = [];
                    if (years > 0) {
                        parts.push(years + ' year' + (years === 1 ? '' : 's'));
                    }

                    if (months > 0) {
                        parts.push(months + ' month' + (months === 1 ? '' : 's'));
                    }

                    experiencePreview.textContent = parts.join(' ');
                }

                if (gazzettedCheckbox && gazzettedCheckbox.dataset.bound !== '1') {
                    gazzettedCheckbox.dataset.bound = '1';
                    gazzettedCheckbox.addEventListener('change', renderGazzetteDateVisibility);
                }

                if (startCareerInput.dataset.bound !== '1') {
                    startCareerInput.dataset.bound = '1';
                    startCareerInput.addEventListener('input', renderExperienceYears);
                    startCareerInput.addEventListener('change', renderExperienceYears);
                }

                renderGazzetteDateVisibility();
                renderExperienceYears();
            }

            document.addEventListener('DOMContentLoaded', bindCareerFields);
            document.addEventListener('livewire:load', bindCareerFields);
            document.addEventListener('livewire:navigated', bindCareerFields);
            document.addEventListener('livewire:update', bindCareerFields);

            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && typeof Livewire.hook === 'function') {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            queueMicrotask(bindCareerFields);
                        });
                    });
                }
            });
        })();
    </script>
</div>
