@extends('layouts.app')

@section('module-name')
	<li class="nav-item">
		<a class="nav-link module-name" href="{{ route('personnel-home') }}"><i class="mdi mdi-account-group"></i> Personnel Management</a>
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
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">PERSONNEL MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->


			<a href="{{ route('personnel-home') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="{{ route('personnel-list') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted fa-fw mr-3"></span>
					<span class="menu-collapsed">Personnel List</span>
				</div>
			</a>
			<a href="/organizational-departments" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Departments</span>
				</div>
			</a>
			<a href="/organizational-roles" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-key fa-fw mr-3"></span>
					<span class="menu-collapsed">Roles</span>
				</div>
			</a>
			<!-- <a href="/organizational-locations" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">Organizational Structure</span>
				</div>
			</a> -->
			<a href="{{ route('get-audit-logs') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-search fa-fw mr-3"></span>
					<span class="menu-collapsed">Audit Trail</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Configurations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
					$menuTotals = array("Educational Levels", "Job Description", "Designation");
				?>
				@foreach ($menuTotals as $item)
					<a href="{{ route('module-pre-configs', ['config'=>$item, 'module'=>'Personnel-Management']) }}" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}</span>
					</a>
				@endforeach
					<a href="{{ route('personnel-certification-home') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Certifications</span>
					</a>
			</div>
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