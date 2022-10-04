@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('customers-list') }}"><i class="mdi mdi-account-group"></i>CRM</a>
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
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">CRM</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="/crm-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">Customer Register</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Complaint Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
				$menuTotals = getComplaintWorkflowStages();
				$totals = array();
				// foreach($menuTotals  as $total){
				// 	$totals['$total'] = getComplaintsInWorkflow($loop->iteration-1);
				// }
				?>
				@foreach (getComplaintWorkflowStages() as $item)
				<a href="{{route('complaint-workflow',['stage'=>$item])}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}
						@if ($item == "All Complaints")
						<small class="float-right badge badge-pill {{ $item == "Samples Request Review" ? 'badge-danger' : 'badge-dark' }}">{{ getAllComplaints() }}</small>
						@endif
						@if($item != "All Complaints")
						<small class="float-right badge badge-pill {{ $item == "Samples Request Review" ? 'badge-danger' : 'badge-dark' }}">{{ getComplaintsInWorkflow($loop->iteration-1) ?? 0 }}</small>
						@endif
					</span>
				</a>
				@endforeach
			</div>
			<a href="/complaint-type/home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-message-cog fa-fw mr-3"></span>
					<span class="menu-collapsed">Complaint Type</span>
				</div>
			</a>
			<a href="/customer-feedback/home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-account fa-fw mr-3"></span>
					<span class="menu-collapsed">Customer Feedback</span>
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