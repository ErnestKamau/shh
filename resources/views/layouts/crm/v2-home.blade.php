@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>CRM | New Module</title>
@endsection

@section('content2')
<main>
    <h2 class="p-4"><i class="mdi mdi-account-group"></i> CRM</h2>
    <p class="p-4"><a href="{{ route('customers-list') }}">Customer Register</a></p>
</main>
@endsection

@section('script2')
@endsection