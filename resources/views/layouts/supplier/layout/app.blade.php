@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('lab-home') }}"><i class="mdi mdi-truck-fast"></i> Supplier Management</a>
</li>
@endsection

@section('title')
  @yield('title2')
@endsection
@section('content')
<div class="d-flex" id="main-wrapper">
  <div id="main-sidebar">
    <div id="main-sidebar-header">
      <i class="mdi mdi-flask fa-3x"></i><br>
      <span class="text-lg text-bold">SUPPLIER MANAGEMENT</span>
    </div>
    <div id="main-sidebar-body">
      <a class="sd-item" href="/suppliers-list">
        <div class="icon text-info"><i class="mdi mdi-truck-fast"></i></div>
        <div class="text">Suppliers</div>
      </a>
      <a class="sd-item" href="/supplier-categories">
        <div class="icon"><i class="mdi mdi-format-list-bulleted-type"></i></div>
        <div class="text">Supplier Categories</div>
      </a>
    </div>
    <div id="sd-copyright">
      Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
    </div>
  </div>
  <div class="flex-fill" id="main-body-content">
    @yield('content2')
  </div>
</div>
@endsection

@section('script')
  @yield('script2')
@endsection