@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Customer List | CRM</title>
@endsection

@section('content2')
<main>
    <livewire:crm.customer.customer-list />
</main>
@endsection

@section('script2')
@endsection
