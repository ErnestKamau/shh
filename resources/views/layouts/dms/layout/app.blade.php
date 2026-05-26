@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('dms.dashboard') }}"><i class="mdi mdi-file-document-multiple"></i> Document Management</a>
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
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-file-document-multiple fa-3x"></i><br>
				<span class="text-lg text-bold">DOCUMENT MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="{{ route('dms.dashboard') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="{{ route('dms.types') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-folder-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">Document Types</span>
				</div>
			</a>
			<a href="{{ route('dms.active') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">Active Documents</span>
				</div>
			</a>
			<a href="{{ route('dms.archived') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-archive fa-fw mr-3"></span>
					<span class="menu-collapsed">Archived Documents</span>
				</div>
			</a>
			<a href="{{ route('dms.amendments') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-pencil fa-fw mr-3"></span>
					<span class="menu-collapsed">Amendments</span>
				</div>
			</a>
			<a href="{{ route('dms.reports') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-bar fa-fw mr-3"></span>
					<span class="menu-collapsed">Reports</span>
				</div>
			</a>
            <div class="list-group-item copyright-lims p-4 text-center">
                Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
            </div>
		<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-8 col-md-9 col-lg-10 py-3" id="main-container-body">
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
