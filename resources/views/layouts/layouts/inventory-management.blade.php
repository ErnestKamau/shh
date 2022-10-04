@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('inventory-home') }}"><i class="mdi mdi-package-variant"></i> Inventory Management</a>
</li>
@endsection

@section('title')
  <title>@yield('title')</title>
@endsection

@section('content')
@endsection