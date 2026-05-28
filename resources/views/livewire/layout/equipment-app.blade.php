@extends('layouts.equipment.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'Equipment Management' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [];
    
    // Always start with Equipment Management
    $breadcrumbItems[] = [
        'link' => route('equipment-home'),
        'name' => 'Equipment Management',
        'icon' => null
    ];
    
    // Add Equipment Manager if we're in equipment manager section
    if (isset($componentType) && $componentType === 'equipment-manager') {
        $breadcrumbItems[] = [
            'link' => route('equipment-home'),
            'name' => 'Equipment List',
            'icon' => null
        ];
    }

    if (isset($componentType) && $componentType === 'equipment-dashboard') {
        $breadcrumbItems[] = [
            'link' => route('equipment-dashboard'),
            'name' => 'Equipment Dashboard',
            'icon' => null
        ];
    }
    
    // Add Equipment Detail if we're in equipment detail section
    if (isset($componentType) && $componentType === 'equipment-detail') {
        $breadcrumbItems[] = [
            'link' => route('equipment-home'),
            'name' => 'Equipment List',
            'icon' => null
        ];
        if (isset($equipment) && $equipment) {
            $breadcrumbItems[] = [
                'link' => '#',
                'name' => $equipment->name,
                'icon' => null
            ];
        }
    }
    
    // Add Disposal Manager breadcrumb
    if (isset($componentType) && $componentType === 'disposal-manager') {
        $breadcrumbItems[] = [
            'link' => route('equipment-disposal-home'),
            'name' => 'Equipment Disposal',
            'icon' => null
        ];
    }
    
    // Add Disposal Detail breadcrumb
    if (isset($componentType) && $componentType === 'disposal-detail') {
        $breadcrumbItems[] = [
            'link' => route('equipment-disposal-home'),
            'name' => 'Equipment Disposal',
            'icon' => null
        ];
        if (isset($disposal) && $disposal) {
            $breadcrumbItems[] = [
                'link' => '#',
                'name' => 'Disposal Request #' . $disposal->id,
                'icon' => null
            ];
        }
    }

    // Add Workflow Manager breadcrumb
    if (isset($componentType) && $componentType === 'workflow-manager') {
        $breadcrumbItems[] = [
            'link' => route('equipment.disposal.workflow.index'),
            'name' => 'Workflow',
            'icon' => null
        ];
    }
    
    // Add Workflow Form breadcrumb
    if (isset($componentType) && $componentType === 'workflow-form') {
        $breadcrumbItems[] = [
            'link' => route('equipment.disposal.workflow.index'),
            'name' => 'Workflow',
            'icon' => null
        ];
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => 'Create',
            'icon' => null
        ];
    }

    // Add Equipment Checks breadcrumb
    /* Commented out as Equipment Checks page is hidden
    if (isset($componentType) && $componentType === 'equipment-checks') {
        $breadcrumbItems[] = [
            'link' => null,
            'name' => 'Equipment Checks',
            'icon' => null
        ];
    }
    */

    // Add Equipment Daily Log breadcrumb
    
    if (isset($componentType) && $componentType === 'equipment-daily-log') {
        $breadcrumbItems[] = [
            'link' => null,
            'name' => 'Equipment Daily Log',
            'icon' => null
        ];
    }

    // Add Monitoring breadcrumbs
    if (isset($componentType) && $componentType === 'monitoring') {
        $breadcrumbItems[] = [
            'link' => route('equipment.monitoring'),
            'name' => 'Equipment Monitoring',
            'icon' => null
        ];
    }

    // Add Maintenance breadcrumbs
    if (isset($componentType) && $componentType === 'equipment-maintenance') {
        $breadcrumbItems[] = [
            'link' => route('equipment.maintenance'),
            'name' => 'Equipment Maintenance',
            'icon' => null
        ];
    }

    if (isset($componentType) && $componentType === 'template-create') {
        $breadcrumbItems[] = [
            'link' => route('equipment.monitoring'),
            'name' => 'Equipment Monitoring',
            'icon' => null
        ];
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => 'Create Template',
            'icon' => null
        ];
    }

    if (isset($componentType) && $componentType === 'template-edit') {
        $breadcrumbItems[] = [
            'link' => route('equipment.monitoring'),
            'name' => 'Equipment Monitoring',
            'icon' => null
        ];
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => 'Edit Template',
            'icon' => null
        ];
    }

    if (isset($componentType) && str_starts_with($componentType, 'depreciation-')) {
        $breadcrumbItems[] = [
            'link' => route('equipment.depreciation.index'),
            'name' => 'Asset Depreciation',
            'icon' => null
        ];
        if ($componentType === 'depreciation-methods') {
            $breadcrumbItems[] = ['link' => '#', 'name' => 'Depreciation Methods', 'icon' => null];
        } elseif ($componentType === 'depreciation-reports') {
            $breadcrumbItems[] = ['link' => '#', 'name' => 'Depreciation Reports', 'icon' => null];
        } elseif ($componentType === 'depreciation-list') {
            $breadcrumbItems[] = ['link' => '#', 'name' => 'Depreciation List', 'icon' => null];
        }
    }

    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>
    
    <!-- Dynamic Livewire Component -->
    @if($componentType === 'equipment-manager')
        @livewire('equipment.equipment-manager')
    @elseif($componentType === 'equipment-dashboard')
        @livewire('equipment.equipment-dashboard')
    @elseif($componentType === 'equipment-detail')
        @livewire('equipment.equipment-detail', [
            'equipmentId' => $equipmentId, 
            'fromEquipmentChecks' => $fromEquipmentChecks ?? false,
            'fromEquipmentMaintenance' => $fromEquipmentMaintenance ?? false
        ])
    @elseif($componentType === 'disposal-manager')
        @livewire('equipment.disposal-manager')
    @elseif($componentType === 'disposal-detail')
        @livewire('equipment.disposal-detail', ['disposalId' => $disposalId])
    @elseif($componentType === 'workflow-manager')
        @livewire('equipment.workflow.workflow-manager')
    @elseif($componentType === 'workflow-form')
        @livewire('equipment.workflow.workflow-form', ['id' => $workflowId ?? null])
    @elseif($componentType === 'asset-type-manager')
        @livewire('equipment.assets.asset-type-manager')
    @elseif($componentType === 'asset-location-manager')
        @livewire('equipment.assets.asset-location-manager')
    {{-- Commented out as this page is hidden
    @elseif($componentType === 'equipment-checks')
        @livewire('equipment.equipment-checks')
    --}}
    @elseif($componentType === 'equipment-daily-log')
        @livewire('equipment.equipment-daily-log')
    @elseif($componentType === 'monitoring')
        @livewire('monitoring.monitoring-dashboard', ['activeSection' => 'equipment', 'module' => 'equipment'])
    @elseif($componentType === 'equipment-maintenance')
        @livewire('equipment.equipment-maintenance')
    @elseif($componentType === 'template-create')
        @livewire('monitoring.create-monitoring-template', ['templateType' => 'equipment', 'module' => 'equipment'])
    @elseif($componentType === 'template-edit')
        @livewire('monitoring.edit-monitoring-template', ['template' => $template, 'module' => 'equipment'])
    @elseif($componentType === 'depreciation-list')
        @livewire('equipment.depreciation.depreciation-list-manager')
    @elseif($componentType === 'depreciation-methods')
        @livewire('equipment.depreciation.depreciation-method-manager')
    @elseif($componentType === 'depreciation-reports')
        @livewire('equipment.depreciation.depreciation-reports-manager')
    @endif
</main>
@endsection

@section('script2')
@stack('script2')
@endsection

