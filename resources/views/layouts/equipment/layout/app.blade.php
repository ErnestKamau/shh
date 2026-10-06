@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('equipment-dashboard') }}"><i class="mdi mdi-tools"></i> {{ __('equipment.module_name') }}</a>
</li>
@endsection

@section('title')
  @include('layouts.equipment.partials.responsive-styles')
  @yield('title2')
@endsection


@section('content')
@php
	$depreciationMenuOpen = request()->routeIs('equipment.depreciation.*');
@endphp
<style>
	.sidebar-submenu .list-group-item { padding-left: 2rem; font-size: 0.9rem; }
</style>
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-tools fa-3x"></i><br>
				<span class="text-lg text-bold">{{ __('equipment.module_name') }}</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="{{ route('equipment-dashboard') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_dashboard') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment-home') }}" class="list-group-item list-group-item-action {{ request()->routeIs('equipment-home') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-tools fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_list') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment.monitoring') }}" class="list-group-item list-group-item-action {{ request()->routeIs('equipment.monitoring*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-monitor-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_monitoring') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment.maintenance') }}" class="list-group-item list-group-item-action {{ request()->routeIs('equipment.maintenance*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calendar-clock fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_maintenance') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment-disposal-home') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-delete-sweep fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_disposal') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment.asset-types.index') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted-type fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.asset_types') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment.asset-locations.index') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.asset_locations') }}</span>
				</div>
			</a>
			@can('equipment.components.depreciation.view')
			<a href="#asset-depreciation-menu" data-toggle="collapse" aria-expanded="{{ $depreciationMenuOpen ? 'true' : 'false' }}"
				class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-finance fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.asset_depreciation') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="asset-depreciation-menu" class="collapse sidebar-submenu {{ $depreciationMenuOpen ? 'show' : '' }}">
				<a href="{{ route('equipment.depreciation.index') }}" class="list-group-item list-group-item-action bg-dark text-white {{ request()->routeIs('equipment.depreciation.index') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> {{ __('equipment.depreciation_list') }}</span>
				</a>
				@can('equipment.components.depreciation.methods.view')
				<a href="{{ route('equipment.depreciation.methods.index') }}" class="list-group-item list-group-item-action bg-dark text-white {{ request()->routeIs('equipment.depreciation.methods.*') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> {{ __('equipment.depreciation_methods') }}</span>
				</a>
				@endcan
				<a href="{{ route('equipment.depreciation.reports.index') }}" class="list-group-item list-group-item-action bg-dark text-white {{ request()->routeIs('equipment.depreciation.reports.*') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> {{ __('equipment.reports') }}</span>
				</a>
			</div>
			@endcan
			{{-- Equipment Checks hidden by request --}}
			<a href="{{ route('equipment-daily-log') }}" class="list-group-item list-group-item-action {{ request()->routeIs('equipment-daily-log') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-notebook-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.equipment_daily_log') }}</span>
				</div>
			</a>
			<a href="{{ route('equipment-report-generate') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('equipment.reports') }}</span>
				</div>
			</a>


			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
			<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
<!-- sidebar-container END -->

<!-- MAIN -->
<div class="py-3 equipment-page" id="main-container-body">
		<div id="message-section" style="padding: 10px 10px 0px 10px !important">


			@if ($errors->any())
			<div class="alert alert-danger">
				<ul>
					@foreach ($errors->all() as $error)
					<li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
					@endforeach
				</ul>
			</div>
			@endif
			@if (\Session::has('success') || \Session::has('error'))
			@if (\Session::has('success'))
			<div class="alert alert-success center text-lg alert-callout">
				<i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
			</div>
			@endif
			@if (\Session::has('error'))
			<div class="alert alert-danger center text-lg alert-callout">
				<i class="fas fa-exclamation-triangle"></i> {{ Session::get('error') }}
			</div>
			@endif
			@endif
		</div>
		@yield('content2')
	</div>
	<!-- Main Col END -->
</div>
@endsection

@section('script')
  @yield('script2')
@endsection
