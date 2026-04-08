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
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    @if($componentType === 'personnel-dashboard')
        @livewire('personnel.personnel-dashboard')
    @elseif($componentType === 'personnel-list')
        @livewire('personnel.personnel-table-manager')
    @elseif($componentType === 'organizational-departments')
        @livewire('personnel.department-manager')
    @elseif($componentType === 'organizational-roles')
        @livewire('personnel.role-manager')
    @elseif($componentType === 'audit-logs')
        @livewire('personnel.audit-log-manager')
    @endif
</main>
@endsection

@section('script2')
@endsection
