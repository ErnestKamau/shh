@extends('layouts.crm.layout.app', ['dataTable'=>false, 'select2'=>false])

@section('title2')
<title>Complaint Type | CRM</title>
@endsection

@section('content2')
<main>
	<livewire:crm.complaint.complaint-type-list :key="'complaint-type-list'" />
</main>
@endsection

@section('script2')
@endsection
