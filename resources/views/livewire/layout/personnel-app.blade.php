@extends('layouts.personnel.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'Personnel Management' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [
        [
            'link' => route('personnel-home'),
            'name' => 'Personnel Management',
            'icon' => null,
        ],
    ];

    if (isset($componentType) && $componentType === 'personnel-dashboard') {
        $breadcrumbItems[] = [
            'link' => route('personnel-home'),
            'name' => 'Dashboard',
            'icon' => null,
        ];
    }

    if (isset($componentType) && $componentType === 'personnel-list') {
        $breadcrumbItems[] = [
            'link' => route('personnel-list'),
            'name' => 'Personnel List',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'personnel-detail') {
        $detailUser = \App\User::find($userId);
        $breadcrumbItems[] = [
            'link' => route('personnel-list'),
            'name' => 'Personnel Profile',
            'icon' => null,
        ];
        if ($detailUser) {
            $breadcrumbItems[] = [
                'link' => route('view-personnel', ['id' => $userId]),
                'name' => $detailUser->name,
                'icon' => null,
            ];
        }
    }
    if (isset($componentType) && $componentType === 'organizational-departments') {
        $breadcrumbItems[] = [
            'link' => route('show-organizational-departments'),
            'name' => 'Organizational Departments',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'organizational-roles') {
        $breadcrumbItems[] = [
            'link' => route('organizational-roles'),
            'name' => 'Roles',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'audit-logs') {
        $breadcrumbItems[] = [
            'link' => route('get-audit-logs'),
            'name' => 'Audit Trails',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'locked-accounts-manager') {
        $breadcrumbItems[] = [
            'link' => route('locked-accounts'),
            'name' => 'Locked Accounts',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'personnel-configurations') {
        $breadcrumbItems[] = [
            'link' => route('module-pre-configs', ['config' => $config, 'module' => $module]),
            'name' => $config . ' Configurations',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'personnel-certifications') {
        $breadcrumbItems[] = [
            'link' => route('personnel-certification-home'),
            'name' => 'Certifications',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'zones') {
        $breadcrumbItems[] = [
            'link' => route('module-pre-configs', ['config' => $config, 'module' => $module]),
            'name' => 'Zone',
            'icon' => null,
        ];
    }
    if (isset($componentType) && $componentType === 'job-responsibility') {
        $designation = \App\ModulePreConfigs::find($designationId);
        if ($designation) {
            $breadcrumbItems[] = [
                'link' => route('module-pre-configs', ['config' => 'Job Description', 'module' => 'Personnel-Management']),
                'name' => 'Job Designation',
                'icon' => null,
            ];
            $breadcrumbItems[] = [
                'link' => route('showResponsibility', ['id' => $designationId]),
                'name' => $designation->name . ' Responsibilities',
                'icon' => null,
            ];
        }
    }
    if (isset($componentType) && $componentType === 'organizational-role-detail') {
        $role = \Spatie\Permission\Models\Role::query()->where('guard_name', 'web')->find($roleId);
        if ($role) {
            $breadcrumbItems[] = [
                'link' => route('organizational-roles'),
                'name' => 'Roles',
                'icon' => null,
            ];
            $breadcrumbItems[] = [
                'link' => route('view-organizational-role', ['id' => $roleId]),
                'name' => $role->name,
                'icon' => null,
            ];
        }
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    @if($componentType === 'personnel-dashboard')
        @livewire('personnel.personnel-dashboard')
    @elseif($componentType === 'personnel-list')
        @livewire('personnel.personnel-table-manager')
    @elseif($componentType === 'personnel-detail')
        @livewire('personnel.personnel-detail-manager', ['userId' => $userId])
    @elseif($componentType === 'organizational-departments')
        @livewire('personnel.department-manager')
    @elseif($componentType === 'organizational-roles')
        @livewire('personnel.role-manager')
    @elseif($componentType === 'audit-logs')
        @livewire('personnel.audit-log-manager')
    @elseif($componentType === 'locked-accounts-manager')
        @livewire('personnel.locked-accounts-manager')
    @elseif($componentType === 'personnel-configurations')
        @livewire('personnel.configuration-manager', ['config' => $config, 'module' => $module])
    @elseif($componentType === 'zones')
        @livewire('personnel.zones.configuration-manager', ['module' => $module])
    @elseif($componentType === 'personnel-certifications')
        @livewire('personnel.certification-manager')
    @elseif($componentType === 'job-responsibility')
        @livewire('personnel.job-responsibility-manager', ['designationId' => $designationId])
    @elseif($componentType === 'organizational-role-detail')
        @livewire('personnel.role-detail-manager', ['roleId' => $roleId])
    @endif
</main>
@endsection

@section('script2')
@endsection
