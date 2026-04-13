@extends('layouts.crm.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'CRM Management' }}</title>
@endsection

@section('content2')
<main>
    <!-- Dynamic Livewire Component -->
    @if($componentType === 'customers')
        @livewire(\App\Livewire\Crm\Customer\CustomerList::class)
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

