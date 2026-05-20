@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('equipment-dashboard') }}"><i class="mdi mdi-tools"></i> Equipment Management</a>
</li>
@endsection

@section('title')
  @yield('title2')
@endsection


@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-tools fa-3x"></i><br>
				<span class="text-lg text-bold">Equipment</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="{{ route('equipment-dashboard') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Dashboard</span>
				</div>
			</a>
			<a href="{{ route('equipment-home') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('equipment-home') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-tools fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment List</span>
				</div>
			</a>
			<a href="{{ route('equipment.monitoring') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('equipment.monitoring*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-monitor-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Monitoring</span>
				</div>
			</a>
			<a href="{{ route('equipment.maintenance') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('equipment.maintenance*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calendar-clock fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Maintenance</span>
				</div>
			</a>
			<a href="{{ route('equipment-disposal-home') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-delete-sweep fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Disposal</span>
				</div>
			</a>
			<a href="{{ route('equipment.asset-types.index') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted-type fa-fw mr-3"></span>
					<span class="menu-collapsed">Asset Types</span>
				</div>
			</a>
			<a href="{{ route('equipment.asset-locations.index') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">Asset Locations</span>
				</div>
			</a>
			{{--
			<a href="{{ route('equipment-checks') }}" class="bg-dark hidden list-group-item list-group-item-action {{ request()->routeIs('equipment-checks') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-notebook-check-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Checks</span>
				</div>
			</a>
			<a href="{{ route('equipment-daily-log') }}" class="bg-dark hidden list-group-item list-group-item-action {{ request()->routeIs('equipment-daily-log') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-notebook-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Daily Log</span>
				</div>
			</a>
			--}}
			<a href="{{ route('equipment-report-generate') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">Equipment Reports</span>
				</div>
			</a>


			<div class="list-group-item copyright-lims p-4 text-center text-white" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
			<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
<!-- sidebar-container END -->

<!-- MAIN -->
<div class="py-3" id="main-container-body">
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
