<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-key-change text-primary"></i>
                                {{ __('personnel.organizational_roles') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.roles_overview') }}</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" wire:click="openCreateModal">
                            <i class="mdi mdi-plus"></i> {{ __('personnel.add_role') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_roles') }}">
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-md-end rm-top-controls" style="gap: 10px">
                        <select class="form-control rm-per-page-select" wire:model.live="perPage">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-success rm-export-btn" wire:click="exportRoles">
                            <i class="mdi mdi-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <div style="border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(15,23,42,0.05)">
                <table class="table mb-0 rm-role-table">
                    <thead>
                        <tr style="background:linear-gradient(180deg,#f8fbff 0%,#f1f5f9 100%); color:#334155">
                            <th style="width:44px; font-weight:700; border-bottom:2px solid #e2e8f0">#</th>
                            <th style="font-weight:700; border-bottom:2px solid #e2e8f0">{{ __('personnel.name') }}</th>
                            <th style="font-weight:700; border-bottom:2px solid #e2e8f0">{{ __('personnel.description') }}</th>
                            <th style="width:64px; font-weight:700; border-bottom:2px solid #e2e8f0; text-align:center">{{ __('personnel.level') }}</th>
                            <th style="width:64px; font-weight:700; border-bottom:2px solid #e2e8f0; text-align:center">{{ __('personnel.active') }}</th>
                            <th style="width:140px; min-width:140px; border-bottom:2px solid #e2e8f0"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->roles as $item)
                            @php $isExpanded = in_array((string) $item->id, $expandedRoleRows, true); @endphp
                            <tr class="rm-role-row {{ $isExpanded ? 'rm-row-expanded' : '' }}"
                                wire:click="toggleRoleRow('{{ $item->id }}')">
                                <td style="vertical-align:middle; color:#94a3b8">{{ $this->roles->firstItem() + $loop->index }}</td>
                                <td style="vertical-align:middle; font-weight:600; color:#0f172a">
                                    <i class="mdi {{ $isExpanded ? 'mdi-chevron-down' : 'mdi-chevron-right' }} mr-1" style="color:#0284c7"></i>
                                    <i class="mdi mdi-shield-account-outline mr-1 text-primary"></i>{{ $item->name }}
                                </td>
                                <td style="vertical-align:middle; color:#64748b">{{ $item->description ?? '-' }}</td>
                                <td style="vertical-align:middle; text-align:center; color:#334155">{{ $item->level }}</td>
                                <td style="vertical-align:middle; text-align:center">{!! $item->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <td class="rm-actions-cell" style="padding:10px 14px; vertical-align:middle" onclick="event.stopPropagation()">
                                    <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                        wire:click="openEditModal('{{ $item->id }}')" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                        wire:click="openDeleteModal('{{ $item->id }}')" title="{{ __('personnel.delete') }}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>

                            @if($isExpanded)
                                @php
                                    $allModules    = $this->allPermissionsGroupedByModule;
                                    $assignedPerms = $this->getRolePermissionNames($item->id);
                                @endphp
                                <tr class="rm-perm-detail-row">
                                    <td colspan="6" class="p-0">
                                        <div class="rm-perm-wrap p-4" style="background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%); border-top:2px solid #dbeafe">

                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h6 class="mb-0" style="color:#0f172a; font-weight:700">
                                                    <i class="mdi mdi-shield-key-outline mr-1 text-primary"></i>
                                                    Permissions &mdash; {{ $item->name }}
                                                </h6>
                                                <span style="background:#e0f2fe; color:#0369a1; border-radius:999px; padding:3px 12px; font-weight:700">
                                                    {{ count($assignedPerms) }} assigned
                                                </span>
                                            </div>

                                            @if($allModules->isEmpty())
                                                <div class="text-muted small">No permissions found in the system.</div>
                                            @else
                                                {{-- Module tab cards --}}
                                                <div class="row rm-module-tabs mb-3">
                                                    @foreach($allModules as $moduleIndex => $module)
                                                        @php
                                                            $modulePaneId  = 'rm-role-'.$item->id.'-mod-'.$module['module_key'];
                                                            $moduleIcons   = [
                                                                'personnel'    => 'mdi-account-multiple',
                                                                'inventory'    => 'mdi-package-multiple',
                                                                'laboratory'   => 'mdi-flask',
                                                                'equipment'    => 'mdi-tools',
                                                                'dms'          => 'mdi-file-document-multiple',
                                                                'crm'          => 'mdi-account-box-multiple',
                                                                'tickets'      => 'mdi-ticket-multiple',
                                                                'system'       => 'mdi-cog',
                                                                'ai'           => 'mdi-brain',
                                                                'ai_analytics' => 'mdi-chart-line',
                                                                'analytics'    => 'mdi-chart-bar',
                                                                'audit'        => 'mdi-magnify',
                                                                'calendar'     => 'mdi-calendar',
                                                                'risk'         => 'mdi-alert-circle',
                                                                'matrix'       => 'mdi-grid',
                                                                'settings'     => 'mdi-cog-outline',
                                                                'general'      => 'mdi-dots-horizontal-circle',
                                                            ];
                                                            $moduleIcon    = $moduleIcons[strtolower($module['module_key'])] ?? 'mdi-package';
                                                            $isFirstTab    = $moduleIndex === 0;
                                                            $modPerms      = collect($module['resources'])->flatMap(fn($r) => array_values($r['permission_names']))->all();
                                                            $assignedCnt   = count(array_intersect($modPerms, $assignedPerms));
                                                            $totalCnt      = count($modPerms);
                                                        @endphp
                                                        <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-2">
                                                            <button type="button"
                                                                class="rm-module-tab-btn {{ $isFirstTab ? 'active' : '' }}"
                                                                data-role-id="{{ $item->id }}"
                                                                data-pane-id="{{ $modulePaneId }}"
                                                                style="width:100%; min-height:82px; padding:10px 8px; border:2px solid {{ $isFirstTab ? '#0284c7' : '#e2e8f0' }}; background:#fff; border-radius:10px; box-shadow:{{ $isFirstTab ? '0 4px 12px rgba(2,132,199,0.15)' : '0 1px 4px rgba(15,23,42,0.06)' }}; transition:all 0.2s ease; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; cursor:pointer">
                                                                <i class="mdi {{ $moduleIcon }}"
                                                                    style="color:{{ $isFirstTab ? '#0284c7' : '#64748b' }}"></i>
                                                                <span style="font-weight:700; color:{{ $isFirstTab ? '#0284c7' : '#334155' }}; text-align:center; line-height:1.2">
                                                                    {{ $module['module_label'] }}
                                                                </span>
                                                                <span style="font-weight:600; color:{{ $assignedCnt > 0 ? '#10b981' : '#94a3b8' }}">
                                                                    {{ $assignedCnt }}/{{ $totalCnt }}
                                                                </span>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- Module permission panels --}}
                                                <div class="rm-module-panels" style="border-top:1px solid #e5e7eb; padding-top:14px">
                                                    @foreach($allModules as $moduleIndex => $module)
                                                        @php
                                                            $modulePaneId = 'rm-role-'.$item->id.'-mod-'.$module['module_key'];
                                                            $modPerms     = collect($module['resources'])->flatMap(fn($r) => array_values($r['permission_names']))->all();
                                                            $allAssigned  = !empty($modPerms) && count(array_intersect($modPerms, $assignedPerms)) === count($modPerms);
                                                        @endphp
                                                        <div id="{{ $modulePaneId }}"
                                                            class="rm-module-pane {{ $moduleIndex !== 0 ? 'd-none' : '' }}"
                                                            data-role-id="{{ $item->id }}">

                                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                                <span style="font-weight:700; color:#0f172a">
                                                                    {{ $module['module_label'] }}
                                                                </span>
                                                                <button type="button"
                                                                    class="btn btn-sm {{ $allAssigned ? 'rm-deselect-btn' : 'rm-selectall-btn' }}"
                                                                    wire:click.stop="toggleModulePermissions('{{ $item->id }}', '{{ $module['module_key'] }}')"
                                                                    wire:loading.attr="disabled">
                                                                    <i class="mdi {{ $allAssigned ? 'mdi-checkbox-multiple-blank-outline' : 'mdi-checkbox-multiple-marked-outline' }} mr-1"></i>
                                                                    {{ $allAssigned ? __('personnel.deselect_all') : __('personnel.select_all') }}
                                                                </button>
                                                            </div>

                                                            <div class="row">
                                                                @foreach($module['resources'] as $resource)
                                                                    @foreach($resource['permission_names'] as $actionKey => $permissionName)
                                                                        @php $isGranted = in_array($permissionName, $assignedPerms, true); @endphp
                                                                        <div class="col-12 col-md-6 col-lg-4 mb-2">
                                                                            <div class="rm-perm-card {{ $isGranted ? 'rm-perm-card--granted' : '' }}"
                                                                                wire:click.stop="togglePermission('{{ $item->id }}', '{{ $permissionName }}')"
                                                                                wire:loading.attr="disabled"
                                                                                wire:loading.class="rm-perm-card--loading">
                                                                                <div class="d-flex align-items-center">
                                                                                    <span class="rm-perm-cb {{ $isGranted ? 'rm-perm-cb--on' : '' }}">
                                                                                        @if($isGranted)<i class="mdi mdi-check" style="color:#fff; line-height:1"></i>@endif
                                                                                    </span>
                                                                                    <span class="rm-perm-label">{{ $resource['resource_label'] }}</span>
                                                                                    <span class="rm-perm-action ml-auto">{{ ucfirst($actionKey) }}</span>
                                                                                </div>
                                                                                <small class="rm-perm-db">{{ $permissionName }}</small>
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
                                <td colspan="6" class="text-center text-muted py-4">{{ __('personnel.no_roles_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    {{ __('personnel.showing_to_of', ['from' => $this->roles->firstItem() ?? 0, 'to' => $this->roles->lastItem() ?? 0, 'total' => $this->roles->total()]) }}
                </small>
                {{ $this->roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if($showRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $editingRoleId ? 'pencil-outline' : 'plus' }}"></i>
                            {{ $editingRoleId ? __('personnel.edit_role') : __('personnel.add_role') }}
                        </h4>
                        <button type="button" class="close" wire:click="closeRoleModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="roleName" placeholder="{{ __('personnel.role_name') }}...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.description') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" wire:model="roleDescription" placeholder="{{ __('personnel.role_description') }}..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.level') }} <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control" wire:model="roleLevel">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label"><input type="checkbox" wire:model="roleActive"> {{ __('personnel.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveRole"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeRoleModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.delete_role') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            {{ __('personnel.confirm_delete_role', ['role' => $roleName]) }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteRole"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        /* Role row expansion */
        .rm-role-row { transition: background-color 0.18s ease; cursor: pointer; }
        .rm-role-row:hover { background: #f1f5f9 !important; }
        .rm-role-row.rm-row-expanded { background: #eef6ff !important; }
        .rm-perm-detail-row > td { background: transparent !important; padding: 0 !important; }

        .rm-top-controls {
            flex-wrap: nowrap;
        }

        .rm-per-page-select {
            width: 138px;
            min-width: 138px;
            max-width: 138px;
        }

        .rm-export-btn {
            min-width: 134px;
            white-space: nowrap;
        }

        .rm-actions-cell {
            width: 140px;
            min-width: 140px;
            white-space: nowrap;
        }

        /* Action buttons */
        .rm-act-btn { border-radius: var(--ls-radius-sm, 6px); padding: 0.3rem var(--ls-btn-pad-x-sm, 0.65rem); margin-right: 3px; font-size: var(--ls-text-sm, 0.75rem); }
        .rm-act-btn--edit  { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .rm-act-btn--edit:hover  { background: #dbeafe; border-color: #93c5fd; }
        .rm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .rm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }

        @media (max-width: 767.98px) {
            .rm-top-controls {
                justify-content: space-between;
            }

            .rm-per-page-select {
                width: 122px;
                min-width: 122px;
                max-width: 122px;
            }
        }

        /* Permission card */
        .rm-perm-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            padding: 9px 12px;
            cursor: pointer;
            transition: all 0.16s ease;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            user-select: none;
        }
        .rm-perm-card:hover {
            border-color: #86efac;
            box-shadow: 0 4px 10px rgba(16,185,129,0.13);
            transform: translateY(-1px);
        }
        .rm-perm-card.rm-perm-card--granted {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .rm-perm-card.rm-perm-card--granted:hover {
            border-color: #059669;
            box-shadow: 0 4px 10px rgba(16,185,129,0.2);
        }
        .rm-perm-card.rm-perm-card--loading {
            opacity: 0.55;
            pointer-events: none;
        }

        /* Checkbox visual */
        .rm-perm-cb {
            width: 17px;
            height: 17px;
            border-radius: 4px;
            border: 2px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            flex-shrink: 0;
            background: #fff;
            transition: all 0.14s ease;
        }
        .rm-perm-cb--on {
            background: #10b981;
            border-color: #10b981;
        }

        /* Permission text */
        .rm-perm-label {
            font-weight: 600;
            font-size: var(--ls-text-sm, 0.75rem);
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }
        .rm-perm-action {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 4px;
            padding: 1px 6px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .rm-perm-card--granted .rm-perm-action {
            background: #dcfce7;
            color: #166534;
        }
        .rm-perm-db {
            display: block;
            font-family: monospace;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
            word-break: break-all;
        }

        /* Select / Deselect All buttons */
        .rm-selectall-btn {
            border-radius: var(--ls-radius-sm, 6px);
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
            padding: 0.15rem 0.45rem;
        }
        .rm-selectall-btn:hover { background: #dcfce7; border-color: #86efac; }
        .rm-deselect-btn {
            border-radius: var(--ls-radius-sm, 6px);
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            color: #64748b;
            background: #f8fafc;
            padding: 0.15rem 0.45rem;
        }
        .rm-deselect-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
    </style>

    <script>
        (function () {
            function bindRmModuleTabs() {
                document.removeEventListener('click', handleRmTabClick);
                document.addEventListener('click', handleRmTabClick);
            }

            function handleRmTabClick(e) {
                var btn = e.target.closest('.rm-module-tab-btn');
                if (!btn) return;
                e.stopPropagation();

                var roleId = btn.dataset.roleId;
                var paneId = btn.dataset.paneId;

                // Deactivate all tabs for this role
                document.querySelectorAll('.rm-module-tab-btn[data-role-id="' + roleId + '"]').forEach(function (b) {
                    b.classList.remove('active');
                    b.style.borderColor = '#e2e8f0';
                    b.style.boxShadow = '0 1px 4px rgba(15,23,42,0.06)';
                    var spans = b.querySelectorAll('span');
                    var icon  = b.querySelector('i.mdi');
                    if (icon)    icon.style.color    = '#64748b';
                    if (spans[0]) spans[0].style.color = '#334155';
                });

                // Hide all panes for this role
                document.querySelectorAll('.rm-module-pane[data-role-id="' + roleId + '"]').forEach(function (p) {
                    p.classList.add('d-none');
                });

                // Activate clicked tab
                btn.classList.add('active');
                btn.style.borderColor = '#0284c7';
                btn.style.boxShadow   = '0 4px 12px rgba(2,132,199,0.15)';
                var spans = btn.querySelectorAll('span');
                var icon  = btn.querySelector('i.mdi');
                if (icon)    icon.style.color    = '#0284c7';
                if (spans[0]) spans[0].style.color = '#0284c7';

                // Show target pane
                var pane = document.getElementById(paneId);
                if (pane) pane.classList.remove('d-none');
            }

            document.addEventListener('DOMContentLoaded', bindRmModuleTabs);
            document.addEventListener('livewire:load',      bindRmModuleTabs);
            document.addEventListener('livewire:navigated', bindRmModuleTabs);
        })();
    </script>
</div>
