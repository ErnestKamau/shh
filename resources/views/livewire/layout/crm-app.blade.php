@extends('layouts.crm.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? __('crm.crm_management') }}</title>
@endsection

@section('content2')
<main>
    @php
        $livewirePagesWithOwnBreadcrumb = in_array($componentType ?? '', [
            'dashboard',
            'customers',
            'complaints',
            'feedbacks',
            'customer-profile',
        ], true);
    @endphp
    @unless ($livewirePagesWithOwnBreadcrumb)
    <?php
    $breadcrumbItems = [];
    
    // Always start with CRM Home
    $breadcrumbItems[] = [
        'link' => route('crm.dashboard'),
        'name' => __('crm.module_name'),
        'icon' => null
    ];
    
    // Add Customer List
    if ($componentType === 'customers') {
        $breadcrumbItems[] = [
            'link' => route('livewire.customers'),
            'name' => __('crm.customer_list'),
            'icon' => null
        ];
    }
    
    // Add Dashboard
    if ($componentType === 'dashboard') {
        $breadcrumbItems[] = [
            'link' => route('crm.dashboard'),
            'name' => __('crm.dashboard'),
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
    @elseif($componentType === 'complaints')
        @livewire(\App\Livewire\Crm\Complaint\ComplaintList::class, ['stage' => $stage ?? null])
    @elseif($componentType === 'feedbacks')
        @livewire(\App\Livewire\Crm\Feedback\FeedbackList::class)
    @endif
</main>
@endsection

