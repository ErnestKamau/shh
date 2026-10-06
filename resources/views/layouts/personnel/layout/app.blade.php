@extends('layouts.app')

@section('module-name')
	<li class="nav-item">
		<a class="nav-link module-name" href="{{ route('personnel-home') }}"><i class="mdi mdi-account-group"></i> {{ __('personnel.module_title') }}</a>
	</li>
@endsection

@section('title')
  @yield('title2')
@endsection


@section('content')
<style>
	#main-container-body .btn:not(.pm-act-btn):not(.rm-act-btn) {
		border-radius: 12px;
	}
</style>
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		@php
			$user = auth()->user();
			$canPersonnel = $user->can('personnel.personnel.view');
			$canDepartments = $user->can('personnel.departments.view');
			$canRoles = $user->can('personnel.roles.view');
			$canAuditTrail = $user->can('personnel.audit trail.view');
			$canPersonnelEdit = $user->can('personnel.personnel.edit');
			$canPersonnelConfigurations = $user->can('personnel.configurations.view');
			$canModulePreConfigsRoute = $user->can('personnel.module.access');
		@endphp
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">{{ __('personnel.module_title') }}</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->


			@if($canPersonnel)
			<a href="{{ route('personnel-home') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.dashboard') }}</span>
				</div>
			</a>
			@endif
			@if($canPersonnel)
			<a href="{{ route('personnel-list') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.personnel_list') }}</span>
				</div>
			</a>
			@endif
			@if($canDepartments)
			<a href="/organizational-departments" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.departments') }}</span>
				</div>
			</a>
			@endif
			@if($canRoles)
			<a href="/organizational-roles" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-key fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.roles') }}</span>
				</div>
			</a>
			@endif
			<!-- <a href="/organizational-locations" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">Organizational Structure</span>
				</div>
			</a> -->
			@if($canAuditTrail)
			<a href="{{ route('get-audit-logs') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-search fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.audit_trail') }}</span>
				</div>
			</a>
			@endif
			@if($canPersonnelEdit)
			<a href="{{ route('locked-accounts') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-lock fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.locked_accounts') }}</span>
				</div>
			</a>
			@endif
			@if($canPersonnelConfigurations || $canModulePreConfigsRoute)
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">{{ __('personnel.configuration') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
					$menuTotals = array(
						array('config' => 'Educational Levels', 'label' => 'Educational Levels'),
						array('config' => 'Job Description', 'label' => 'Job Description'),
						array('config' => 'Designation', 'label' => 'Designation'),
					);
				?>
				@if($canModulePreConfigsRoute)
				@foreach ($menuTotals as $item)
					<a href="{{ route('module-pre-configs', ['config'=>$item['config'], 'module'=>'Personnel-Management']) }}" class="list-group-item list-group-item-action">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item['label'] }}</span>
					</a>
				@endforeach
				@endif
				@if($canPersonnelConfigurations)
				<a href="{{ route('personnel-certification-home') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ __('personnel.certifications') }}</span>
				</a>
				@endif
			</div>
			@endif
			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
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