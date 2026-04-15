@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('mas.index') }}"><i class="mdi mdi-view-dashboard-variant"></i> {{ __('navigation.management_section') }}</a>
</li>
@endsection

@section('title')
	{{ $title ?? '' }} @yield('title2')
@endsection

@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div" style="background-color: #1a2a3a !important;">
				<i class="mdi mdi-shield-check fa-3x"></i><br>
				<span class="text-lg text-bold">AI Analytics</span>
			</div>
			
			<!-- Global Overview -->
			<a href="{{ route('mas.index') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.index') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-quilt fa-fw mr-3"></span>
					<span class="menu-collapsed">Global Overview</span>
				</div>
			</a>

			<!-- Lab Visuals Dropdown -->
			<a href="#labSubmenu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start {{ request()->routeIs('mas.lab.*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-flask fa-fw mr-3 text-success"></span>
					<span class="menu-collapsed">Lab Insights</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id='labSubmenu' class="collapse sidebar-submenu {{ request()->routeIs('mas.lab.*') ? 'show' : '' }}">
				<a href="{{ route('mas.lab.tat') }}" class="list-group-item list-group-item-action bg-dark text-white border-0 {{ request()->routeIs('mas.lab.tat') ? 'active' : '' }}">
					<span class="menu-collapsed">TAT Analysis</span>
				</a>
				<a href="{{ route('mas.lab.general') }}" class="list-group-item list-group-item-action bg-dark text-white border-0 {{ request()->routeIs('mas.lab.general') ? 'active' : '' }}">
					<span class="menu-collapsed">General Analytics</span>
				</a>
				<a href="{{ route('mas.lab.qc') }}" class="list-group-item list-group-item-action bg-dark text-white border-0 {{ request()->routeIs('mas.lab.qc') ? 'active' : '' }}">
					<span class="menu-collapsed">QC Analytics</span>
				</a>
			</div>

			<!-- Inventory Visuals -->
			<a href="{{ route('mas.inventory') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.inventory') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-package-variant-closed fa-fw mr-3 text-warning"></span>
					<span class="menu-collapsed">Inventory Health</span>
				</div>
			</a>

			<!-- CRM Visuals -->
			<a href="{{ route('mas.crm') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.crm') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-group fa-fw mr-3 text-info"></span>
					<span class="menu-collapsed">CRM Analytics</span>
				</div>
			</a>

			<!-- Risk Visuals -->
			<a href="{{ route('mas.risk') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.risk') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-alert-octagon fa-fw mr-3 text-danger"></span>
					<span class="menu-collapsed">Risk Metrics</span>
				</div>
			</a>

			<!-- AI Intelligence -->
			<a href="{{ route('mas.ai') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.ai') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-head-snowflake fa-fw mr-3 text-white"></span>
					<span class="menu-collapsed">AI Intelligence</span>
				</div>
			</a>

			<!-- Equipment Management -->
			<a href="{{ route('mas.equipment') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.equipment') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-tools fa-fw mr-3 text-warning"></span>
					<span class="menu-collapsed">Equipment</span>
				</div>
			</a>

			<!-- Personnel (HR) -->
			<a href="{{ route('mas.personnel') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.personnel') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-star fa-fw mr-3 text-primary"></span>
					<span class="menu-collapsed">Personnel</span>
				</div>
			</a>

			<!-- Quality Control -->
			<a href="{{ route('mas.qc') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.qc') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-check-decagram fa-fw mr-3 text-success"></span>
					<span class="menu-collapsed">Quality Control</span>
				</div>
			</a>

			<!-- Audit Log -->
			<a href="{{ route('mas.audit') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('mas.audit') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-history fa-fw mr-3 text-secondary"></span>
					<span class="menu-collapsed">Audit Log</span>
				</div>
			</a>

			<div class="list-group-item copyright-lims p-4 text-center text-white">
				AI Analytics v1.1 <br> {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
	</div>

	<!-- MAIN -->
	<div class="col-sm-8 col-md-9 col-lg-10 py-3" id="main-container-body" style="background-color: #f4f6f9;">
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
		{{ $slot ?? '' }} @yield('content2')
	</div>
</div>
@endsection

@section('script')
@yield('script2')
@endsection
