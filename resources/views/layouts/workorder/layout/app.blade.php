@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('customers-list') }}"><i class="mdi mdi-briefcase-outline"></i> Workorder Management</a>
</li>
@endsection
@section('title')
{{-- <style type="text/css">
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
	}
</style> --}}
<style type="text/css">
	.topology-row{
		clear: both !important;
		font-size: 11px;
		cursor: pointer;
	}

	.topology-row.add-new .topology-text{
		border: none !important;
	}

	.topology-row .topology-text{
		clear: both !important;
		font-weight: 300;
		color: #121212;
		border-bottom: 1px dashed #eee;
	}

	.topology-row .topology-text .text{
		padding: 5px 10px;
	}

	.topology-row .topology-text .left-icon{
		padding: 5px 8px;
		margin-right: 5px;
		float: left;
	}

	.topology-row .topology-text .right-icon{
		padding: 4px 6px;
		float: right;
	}

	.topology-row .topology-text .left-icon:hover, .topology-row .topology-text .right-icon:hover{
		background-color: rgba(0,0,0,0.1);
	}

	.topology-row .topology-body{
		display: none;
	}

	.topology-row .topology-body.visible{
		display: unset !important;
		clear: both !important;
	}

	.topology-body .topology-row{
		margin-left: 22px;
	}

</style>
<?php
	$STATUSES = getWorkOrderStatusCount();
?>
@yield('title2')
@endsection
@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-briefcase-outline fa-3x"></i><br>
				<span class="text-lg text-bold">WORKORDER MANAGEMENT</span>
			</div>
			<a href="/workorder-home" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="/workorders" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-text fa-fw mr-3"></span>
					<span class="menu-collapsed">WorkOrders</span>
				</div>
			</a>
			<a href="/workorders/REQUEST" class="list-group-item list-group-item-action" >
				<div class="w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-check-multiple fa-fw mr-3 text-info"></span>
					<span class="menu-collapsed">Requests</span>
					<span class="float-right pull-right badge badge-info badge-pill">{{ $STATUSES['REQUEST'] }}</span>
				</div>
			</a>
			<a href="/workorders/REQUEST_REJECTION" class="list-group-item list-group-item-action">
				<div class="w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-alert fa-fw mr-3 text-danger"></span>
					<span class="menu-collapsed">Rejections</span>
					<span class="float-right pull-right badge badge-danger badge-pill">{{ $STATUSES['REQUEST_REJECTION'] }}</span>
				</div>
			</a>
			<a href="/topology" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-lan fa-fw mr-3"></span>
					<span class="menu-collapsed">Topology</span>
				</div>
			</a>
			<a href="/workorder-services" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-toolbox-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Services</span>
				</div>
			</a>
			<a href="/working-schedules" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calendar-arrow-right fa-fw mr-3"></span>
					<span class="menu-collapsed">Working Schedule</span>
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
	<div class="col-sm-8 col-lg-10 py-3" id="main-container-body">
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