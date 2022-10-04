@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('equipment-home') }}"><i class="mdi mdi-hammer-wrench"></i> Equipment Management</a>
</li>
@endsection

@section('title')
  <title>@yield('title')</title>
@endsection

@section('content')
@endsection