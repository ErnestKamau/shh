@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
    <title>{{ $customer->name ?? 'Customer' }} | CRM</title>
@endsection

@section('content2')
    <main>
        <livewire:crm.customer.customer-show :customerId="$customerId" />
    </main>
@endsection

@section('script2')
@endsection

