@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('customers-list') }}"><i class="mdi mdi-account-group"></i>{{ __('crm.module_name') }}</a>
</li>
@endsection

@section('title')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/crm.css') }}">
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

	/* Tab styles overridden by imara-lims.css for protocol compliance */

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	}
</style>
@yield('title2')
@endsection


@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">{{ __('crm.module_name') }}</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="{{ route('crm-dashboard') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.dashboard') }}</span>
				</div>
			</a>
			<a href="/crm-home" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.customer_register') }}</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.complaint_workflow') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				@foreach (getComplaintWorkflowStages() as $item)
				@php
					$complaintCountBadge = $item === 'All Complaints'
						? getAllComplaints()
						: (getComplaintsInWorkflow(getComplaintWorkflowMenuItems()[$item] ?? 0) ?? 0);
					$complaintBadgeClass = $item === 'Samples Request Review' ? 'badge-danger' : 'badge-dark';
				@endphp
				<a href="{{ route('crm.complaints-manager', ['stage' => $item]) }}" class="list-group-item list-group-item-action crm-sidebar-complaint-stage-link">
					<div class="d-flex w-100 align-items-center justify-content-between menu-collapsed" style="gap: 0.5rem;">
						<span class="text-truncate d-flex align-items-center min-w-0">
							<i class="mdi mdi-circle-medium flex-shrink-0"></i>
							<span>{{ translateComplaintWorkflowStage($item) }}</span>
						</span>
						<small class="badge badge-pill {{ $complaintBadgeClass }} flex-shrink-0 crm-sidebar-complaint-count">{{ $complaintCountBadge }}</small>
					</div>
				</a>
				@endforeach
			</div>
			<a href="{{ route('complaint-type-home') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-message-cog fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.complaint_type') }}</span>
				</div>
			</a>
			<a href="{{ route('crm-batch-reports') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.batch_reports') }}</span>
				</div>
			</a>
			<a href="{{ route('feedback-home') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-account fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.customer_feedback') }}</span>
				</div>
			</a>
			<a href="{{ route('feedback-config') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cog-refresh-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.feedback_configuration') }}</span>
				</div>
			</a>

			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
				{{ __('crm.copyright') }} {{ date('Y') }} <span class="text-red">{{ __('crm.imara_lims') }}</span>
			</div>
			<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="py-3 crm-main-content" id="main-container-body">
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
<livewire:a-i.ai-drawer :context="'crm'" />
@endsection

@section('script')
@yield('script2')
@endsection