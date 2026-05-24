@extends('layouts.app', ['select2' => true])

@section('module-name')
<li class="nav-item d-flex align-items-center">
	<a class="nav-link module-name" href="{{ route('registry.dashboard') }}"><i class="mdi mdi-email-multiple"></i> Registry</a>
</li>
@endsection

@section('title')
<style type="text/css">
	.tab-card { border: 1px solid #eee; }
	.tab-card-header { background: none; }
	.tab-card-header>.nav-tabs { border: none; margin: 0; }
	.tab-card-header>.nav-tabs>li>a { border: 0; border-bottom: 2px solid transparent; color: #737373; padding: 2px 15px; }
	.tab-card-header>.nav-tabs>li>a.active { border-bottom: 2px solid #1565C0; color: #1565C0; }
	.tab-card .nav-link.active { background-color: #dadccd !important; border: 1px solid #cccebf !important; }
	.sidebar-submenu .list-group-item { padding-left: 2rem; font-size: 0.9rem; }
	body > #message-section:not(#main-container-body #message-section) { display: none !important; }
	#main-container-body {
		height: calc(100vh - 56px);
		overflow-y: auto;
	}
	@media (max-width: 767.98px) {
		#main-container-body {
			height: auto;
			overflow-y: visible;
		}
	}
</style>
@include('layouts.registry.partials.rm-act-btn-styles')
<style type="text/css">
@include('layouts.registry.partials.status-badge-styles')
@include('layouts.registry.partials.stage-badge-styles')
</style>
@yield('title2')
@endsection

@section('content')
@php $currentRoute = request()->route()->getName(); @endphp
<div class="row" id="body-row">
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-email-multiple fa-3x"></i><br>
				<span class="text-lg text-bold">REGISTRY</span>
			</div>
			<a href="{{ route('registry.dashboard') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.dashboard' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			@can('registry.components.requests.view')
			<a href="{{ route('registry.requests.index') }}" class="bg-dark list-group-item list-group-item-action {{ in_array($currentRoute, ['registry.requests.index'], true) ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">Requests</span>
				</div>
			</a>
			<a href="{{ route('registry.approved.index') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.approved.index' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-check-all fa-fw mr-3"></span>
					<span class="menu-collapsed">Approved Requests</span>
				</div>
			</a>
			@endcan
			@can('registry.components.approval queue.view')
			<a href="{{ route('registry.approvals.index') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.approvals.index' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-check-decagram fa-fw mr-3"></span>
					<span class="menu-collapsed">Approval Queue</span>
				</div>
			</a>
			@endcan
			@can('registry.components.correspondence register.view')
			<a href="{{ route('registry.correspondence.index') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.correspondence.index' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-book-open-page-variant fa-fw mr-3"></span>
					<span class="menu-collapsed">Correspondence Register</span>
				</div>
			</a>
			@endcan
			@can('registry.components.workflow configuration.view')
			<a href="{{ route('registry.workflows.index') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.workflows.index' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-sitemap fa-fw mr-3"></span>
					<span class="menu-collapsed">Workflow Configuration</span>
				</div>
			</a>
			@endcan
			@can('registry.components.requests.add')
			<a href="{{ route('registry.requests.create') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'registry.requests.create' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-plus-circle fa-fw mr-3"></span>
					<span class="menu-collapsed">New Request</span>
				</div>
			</a>
			@endcan
			<div class="list-group-item copyright-lims p-4 text-center text-white">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-8 col-md-9 col-lg-10 py-3" id="main-container-body">
		<div id="message-section" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: auto; max-width: 600px;">
			@if ($errors->any())
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<ul class="mb-0">
					@foreach ($errors->all() as $error)
					<li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
					@endforeach
				</ul>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			@endif
			@if (\Session::has('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
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
