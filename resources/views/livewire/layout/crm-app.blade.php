@extends('layouts.crm.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'CRM Management' }}</title>
@endsection

@section('content2')
<main>
    @php
        $livewirePagesWithOwnBreadcrumb = in_array($componentType ?? '', [
            'dashboard',
            'customers',
            'complaints',
            'customer-profile',
        ], true);
    @endphp
    @unless ($livewirePagesWithOwnBreadcrumb)
    <?php
    $breadcrumbItems = [];
    
    // Always start with CRM Home
    $breadcrumbItems[] = [
        'link' => route('crm.dashboard'),
        'name' => 'CRM',
        'icon' => null
    ];
    
    // Add Customer List
    if ($componentType === 'customers') {
        $breadcrumbItems[] = [
            'link' => route('livewire.customers'),
            'name' => 'Customer List',
            'icon' => null
        ];
    }
    
    // Add Dashboard
    if ($componentType === 'dashboard') {
        $breadcrumbItems[] = [
            'link' => route('crm.dashboard'),
            'name' => 'Dashboard',
            'icon' => null
        ];
    }
    
    // Add Customer Profile if we have a customer
    if (isset($customer) && $customer) {
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => $customer->name,
            'icon' => null
        ];
    }
    
    // Add Complaint Stage if viewing complaints
    if ($componentType === 'complaints' && isset($stage) && $stage) {
        $breadcrumbItems[] = [
            'link' => route('crm.complaints-manager', ['stage' => $stage]),
            'name' => $stage,
            'icon' => null
        ];
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>
    @endunless
    
    <!-- Dynamic Livewire Component -->
    @if($componentType === 'dashboard')
        @livewire(\App\Livewire\CRM\Dashboard::class)
    @elseif($componentType === 'customers')
        @livewire(\App\Livewire\CRM\CustomerManager::class)
    @elseif($componentType === 'customer-profile')
        @livewire(\App\Livewire\CRM\CustomerProfile::class, ['customerId' => $customerId])
    @elseif($componentType === 'sample-points')
        @livewire(\App\Livewire\CRM\SamplePointManager::class)
    @elseif($componentType === 'areas')
        @livewire(\App\Livewire\CRM\AreaManager::class)
    @elseif($componentType === 'complaints')
        @livewire(\App\Livewire\Crm\Complaint\ComplaintList::class, ['stage' => $stage ?? null])
    @endif
</main>
@endsection

