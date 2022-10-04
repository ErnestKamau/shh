@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('supplier-home') }}">
    <i class="mdi mdi-truck-fast"></i> 
    Supplier Management
  </a>
</li>
@endsection

@section('title')
  <title>@yield('title')</title>
@endsection

@section('content')
@endsection