@extends('layouts.app')

@section('module-name')
<li class="nav-item d-flex align-items-center">
	<a class="nav-link module-name" href="{{ route('audit.dashboard') }}"><i class="mdi mdi-clipboard-check-multiple"></i> Audit & CAPA</a>
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
		border-bottom: 2px solid var(--sys-primary-color);
		color: var(--sys-primary-color);
	}

	.tab-card-header>.nav-tabs>li>a:hover {
		color: var(--sys-primary-color);
	}

	.tab-card .nav-link.active {
		background-color: #dadccd !important;
		border: 1px solid #cccebf !important;
	}

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	}

	body > #message-section:not(#main-container-body #message-section) {
		display: none !important;
	}

	/* Audit Sidebar Styles */
	.sidebar-submenu .list-group-item {
		padding-left: 2rem;
		font-size: 0.9rem;
	}

	.sidebar-separator {
		background-color: #1a1a2e !important;
		color: #6c757d;
		font-size: 0.75rem;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		padding: 0.5rem 1rem;
		border: none;
	}

	.badge-overdue {
		background-color: #dc3545 !important;
		animation: pulse 2s infinite;
	}

	@keyframes pulse {
		0% { opacity: 1; }
		50% { opacity: 0.7; }
		100% { opacity: 1; }
	}

	.workflow-count {
		min-width: 24px;
		text-align: center;
	}

	/* Shared Report Styles */
	.report-card {
		border: none;
		border-radius: 12px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		overflow: hidden;
	}

	.report-header {
		background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
		color: white;
		padding: 20px 24px;
		border: none;
	}

	.report-header h4 {
		color: white;
		font-size: 1.35rem;
		font-weight: 600;
		margin: 0;
	}

	.filter-card {
		background: #f8f9fa;
		padding: 20px;
		border-radius: 8px;
		margin-bottom: 1.5rem;
	}

	.modern-table-wrapper {
		background: #fff;
		border-radius: 8px;
		overflow-x: auto;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
	}

	.modern-table {
		width: 100%;
		border-collapse: collapse;
		margin: 0;
	}

	.modern-table thead {
		background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
	}

	.modern-table thead th {
		padding: 16px 20px;
		text-align: left;
		font-weight: 600;
		font-size: 0.875rem;
		color: white;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		white-space: nowrap;
	}

	.modern-table tbody tr {
		border-bottom: 1px solid #f1f3f4;
		transition: background-color 0.15s ease;
	}

	.modern-table tbody tr:nth-child(even) {
		background: #f8f9fa;
	}

	.modern-table tbody tr:hover {
		background: #e8f0fe !important;
	}

	.modern-table tbody td {
		padding: 16px 20px;
		font-size: 0.9rem;
		color: #202124;
		vertical-align: middle;
	}

	.modern-badge {
		padding: 6px 12px;
		border-radius: 6px;
		font-size: 0.75rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.btn-modern {
		border-radius: 8px;
		font-weight: 500;
		padding: 0.5rem 1.25rem;
		transition: all 0.3s ease;
	}

	.btn-modern:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
	}

	/* KPI Card Styles (Dashboard Pattern) */
	.kpi-card {
		background: #ffffff;
		border: 1px solid #e5e7eb;
		border-radius: 10px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		transition: all 0.3s ease;
		overflow: hidden;
		min-height: 100px;
	}

	.kpi-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(0,0,0,0.15);
	}

	.kpi-card-body {
		padding: 1rem;
	}

	.kpi-card-content {
		display: flex;
		flex-direction: column;
		height: 100%;
		justify-content: space-between;
	}

	.kpi-card-row {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.kpi-card-value {
		font-size: 2rem;
		font-weight: 700;
		margin-bottom: 0;
		color: #2d3748;
		line-height: 1;
	}

	.kpi-card-label {
		font-size: 0.95rem;
		font-weight: 500;
		color: #6b7280;
		margin-bottom: 0;
	}

	.kpi-card-icon {
		font-size: 2rem;
	}

	.kpi-card-icon i {
		font-size: 2rem;
	}
</style>
@yield('title2')
@endsection

@section('content')
@php
	$workflowTotals = getAuditWorkflowTotals();
	$currentRoute = request()->route()->getName();
	$currentStatus = request()->get('status', 'All Audit');
@endphp

<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block col-sm-4 col-md-3 col-lg-2">
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-clipboard-check-multiple fa-3x"></i><br>
				<span class="text-lg text-bold">AUDIT & CAPA</span>
			</div>

			<!-- Dashboard -->
			<a href="{{ route('audit.dashboard') }}" class="list-group-item list-group-item-action {{ $currentRoute === 'audit.dashboard' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>

		<!-- Audit Management Section -->
		<!-- Audit Workflow (Status-based) -->
		<a href="#audit-workflow-menu" data-toggle="collapse" aria-expanded="{{ str_contains($currentRoute, 'audit.audits') || str_contains($currentRoute, 'audit.nc') || str_contains($currentRoute, 'audit.capa') ? 'true' : 'false' }}"
			   class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple mr-3"></span>
					<span class="menu-collapsed">Audit Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="audit-workflow-menu" class="collapse sidebar-submenu {{ str_contains($currentRoute, 'audit.audits') || str_contains($currentRoute, 'audit.nc') || str_contains($currentRoute, 'audit.capa') ? 'show' : '' }}">
				@php
					$workflowSteps = getAuditWorkflowSteps();
				@endphp
				@foreach($workflowSteps as $stepNum => $stepName)
					<a href="{{ route('audit.audits.index', ['status' => $stepName]) }}" 
					   class="list-group-item list-group-item-action {{ ($currentStatus === $stepName) ? 'active' : '' }}">
						<span class="menu-collapsed">
							{{ $stepName }}
							<small class="float-right badge badge-pill badge-secondary workflow-count">
								{{ $workflowTotals[$stepNum] ?? 0 }}
							</small>
						</span>
					</a>
				@endforeach
			</div>

			<!-- Create New Audit -->
			<a href="{{ route('audit.audits.create') }}" class="list-group-item list-group-item-action {{ $currentRoute === 'audit.audits.create' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-plus-circle mr-3"></span>
					<span class="menu-collapsed">New Audit</span>
				</div>
			</a>

		<!-- Reports Section -->
		<a href="{{ route('audit.reports.index') }}" class="list-group-item list-group-item-action {{ $currentRoute === 'audit.reports.index' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart mr-3"></span>
					<span class="menu-collapsed">Reports & KPIs</span>
				</div>
			</a>

		<!-- Configuration Section -->
		<a href="#config-menu" data-toggle="collapse" aria-expanded="{{ str_contains($currentRoute, 'audit.config') ? 'true' : 'false' }}"
			   class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cogs mr-3"></span>
					<span class="menu-collapsed">Settings</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="config-menu" class="collapse sidebar-submenu {{ str_contains($currentRoute, 'audit.config') ? 'show' : '' }}">
				<a href="{{ route('audit.config.audit-types') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Audit Types</span>
				</a>
				<a href="{{ route('audit.config.audit-statuses') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Audit Statuses</span>
				</a>
				<a href="{{ route('audit.config.workflow-actions') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Workflow Actions</span>
				</a>
				<a href="{{ route('audit.config.workflow-action-rules') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Workflow Action Rules</span>
				</a>
				<a href="{{ route('audit.config.finding-categories') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Finding Categories</span>
				</a>
				<a href="{{ route('audit.config.risk-levels') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Risk Levels</span>
				</a>
				<a href="{{ route('audit.config.severity-scales') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Severity Scales</span>
				</a>
				<a href="{{ route('audit.config.likelihood-scales') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Likelihood Scales</span>
				</a>
				<a href="{{ route('audit.config.rca-methods') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> RCA Methods</span>
				</a>
				<a href="{{ route('audit.config.capa-categories') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> CAPA Categories</span>
				</a>
				<a href="{{ route('audit.config.compliance-statuses') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Compliance Statuses</span>
				</a>
				<a href="{{ route('audit.config.email-templates.index') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Email Templates</span>
				</a>
				<a href="{{ route('audit.config.verification-results') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Verification Results</span>
				</a>
				<a href="{{ route('audit.config.approval-config') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Approval Configuration</span>
				</a>
			</div>

			<div class="list-group-item copyright-lims p-4 text-center">
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
		</div>
		@yield('content2')
	</div>
	<!-- Main Col END -->
</div>
<livewire:a-i.ai-drawer :context="'audit'" />
@endsection

@section('script')
<script>
	// Backward compatibility — global SweetAlert2 toasts handle notifications
	function showToast(message, type = 'success') {
		if (typeof window.showAppToast === 'function') {
			window.showAppToast(type === 'success' ? 'success' : 'error', message);
		}
	}
</script>

@yield('script2')

@endsection
