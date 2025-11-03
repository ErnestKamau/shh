@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'CRM Management' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [];
    
    // Always start with CRM Home
    $breadcrumbItems[] = [
        'link' => route('livewire.customers'),
        'name' => 'CRM',
        'icon' => null
    ];
    
    // Add Customer List
    $breadcrumbItems[] = [
        'link' => route('livewire.customers'),
        'name' => 'Customer List',
        'icon' => null
    ];
    
    // Add Customer Profile if we have a customer
    if (isset($customer) && $customer) {
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => $customer->name,
            'icon' => null
        ];
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>
    
    <!-- Dynamic Livewire Component -->
    @if($componentType === 'customers')
        @livewire(\App\Livewire\CRM\CustomerManager::class)
    @elseif($componentType === 'customer-profile')
        @livewire(\App\Livewire\CRM\CustomerProfile::class, ['customerId' => $customerId])
    @elseif($componentType === 'sample-points')
        @livewire(\App\Livewire\CRM\SamplePointManager::class)
    @elseif($componentType === 'areas')
        @livewire(\App\Livewire\CRM\AreaManager::class)
    @endif
</main>
@endsection

