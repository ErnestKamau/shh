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

    if (isset($componentType) && $componentType === 'daily-log') {
        $breadcrumbItems[] = [
            'link' => null,
            'name' => 'Daily Log',
            'icon' => null
        ];
    }

    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>
    
    <!-- Dynamic Livewire Component -->
    @if($componentType === 'equipment-manager')
        @livewire('equipment.equipment-manager')
    @elseif($componentType === 'equipment-dashboard')
        @livewire('equipment.equipment-dashboard')
    @elseif($componentType === 'equipment-detail')
        @livewire('equipment.equipment-detail', ['equipmentId' => $equipmentId, 'fromDailyLog' => $fromDailyLog ?? false])
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
    @elseif($componentType === 'daily-log')
        @livewire('equipment.equipment-daily-log')
    @endif
</main>
@endsection

