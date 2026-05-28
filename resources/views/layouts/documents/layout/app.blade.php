@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="/documents/dashboard"><i class="mdi mdi-book-open-page-variant"></i>Documents</a>
</li>
@endsection

@section('title')
<style type="text/css">
	.tab-card {
		border: 1px solid #eee;
	}

	.tab-card-header {
		background: none;
	}

	/* Default mode */
	.tab-card-header>.nav-tabs {
		border: none;
		margin: 0px;
	}

	.tab-card-header>.nav-tabs>li {
		margin-right: 2px;
	}

	.tab-card-header>.nav-tabs>li>a {
		border: 0;
		border-bottom: 2px solid transparent;
		margin-right: 0;
		color: #737373;
		padding: 2px 15px;
	}

	.tab-card-header>.nav-tabs>li>a.show {
		border-bottom: 2px solid #007bff;
		color: #007bff;
	}

	.tab-card-header>.nav-tabs>li>a:hover {
		color: #007bff;
	}

	.tab-card .nav-link.active {
		background-color: #dadccd !important;
		border: 1px solid #cccebf !important;
	}

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	/* Make sure tabs look neat */
	.tab-card .nav-link.active {
		background-color: #dadccd !important;
		border: 1px solid #cccebf !important;
	}

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	}

	/* Mobile responsive adjustments */
	@media (max-width: 768px) {
		#sidebar-container {
			position: relative;
			height: auto;
			top: auto;
		}
	}
</style>
@yield('title2')
@endsection

@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-book-open-page-variant fa-3x"></i><br>
				<span class="text-lg text-bold">Documents</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="/documents/dashboard" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>

			<!-- COAs & Reports -->
			<a href="{{ route('documents.coas') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-certificate fa-fw mr-3"></span>
					<span class="menu-collapsed">COAs & Reports</span>
				</div>
			</a>
			
			<!-- Document Management -->
			<a href="#document-management-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple mr-3"></span>
					<span class="menu-collapsed">Document Management</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="document-management-menu" class="collapse sidebar-submenu">
				<a href="/documents" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-view-list"></i> All Documents</span>
				</a>
				<a href="/documents/create" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-plus"></i> Upload Document</span>
				</a>
			</div>

			<!-- Configuration -->
			<a href="#configuration-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cog mr-3"></span>
					<span class="menu-collapsed">Configuration</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="configuration-menu" class="collapse sidebar-submenu">
				<a href="/documents/types" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-tag"></i> Document Types</span>
				</a>
				<a href="/documents/notification-frequencies" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-bell"></i> Notification Frequencies</span>
				</a>
			</div>

			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-9 col-lg-10 py-3" id="main-container-body">
		@yield('content2')
	</div>
	<!-- Main Col END -->
</div>
<!-- body-row END -->
<livewire:a-i.ai-drawer :context="'general'" />
@endsection
