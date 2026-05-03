@extends('layouts.configuration.layout.app')

@section('title2')
<title>{{ __('system.system_admin_dashboard') }}</title>
@endsection
@section('content2')
@livewire('system.admin-dashboard')
@endsection
