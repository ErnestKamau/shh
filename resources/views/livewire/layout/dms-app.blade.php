@extends('layouts.dms.layout.app', ['select2' => true])

@section('title2')
<title>{{ $pageTitle ?? 'Document Management System' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [];
    
    // Always start with DMS Dashboard
    $breadcrumbItems[] = [
        'link' => route('dms.dashboard'),
        'name' => 'DMS',
        'icon' => null
    ];
    
    // Add specific page breadcrumbs based on component type
    if (isset($componentType)) {
        switch ($componentType) {
            case 'dashboard':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Dashboard',
                    'icon' => null
                ];
                break;
            case 'document-types':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Document Types',
                    'icon' => null
                ];
                break;
            case 'active-documents':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Active Documents',
                    'icon' => null
                ];
                break;
            case 'archived-documents':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Archived Documents',
                    'icon' => null
                ];
                break;
            case 'amendments':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Amendments',
                    'icon' => null
                ];
                break;
            case 'reports':
                $breadcrumbItems[] = [
                    'link' => '#',
                    'name' => 'Reports',
                    'icon' => null
                ];
                break;
        }
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    @include('livewire.dms.partials.page-shell-styles')

    <!-- Dynamic Livewire Component -->
    @if($componentType === 'dashboard')
        @livewire(\App\Livewire\DMS\Dashboard::class)
    @elseif($componentType === 'document-types')
        @livewire(\App\Livewire\DMS\DocumentTypeManager::class)
    @elseif($componentType === 'active-documents')
        @livewire(\App\Livewire\DMS\ActiveDocuments::class)
    @elseif($componentType === 'archived-documents')
        @livewire(\App\Livewire\DMS\ArchivedDocuments::class)
    @elseif($componentType === 'amendments')
        @livewire(\App\Livewire\DMS\AmendmentManager::class)
    @elseif($componentType === 'reports')
        @livewire(\App\Livewire\DMS\Reports::class)
    @endif
</main>
@endsection
