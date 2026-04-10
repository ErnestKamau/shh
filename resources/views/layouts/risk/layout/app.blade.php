@extends('layouts.app', ['select2' => true])

@section('module-name')
<li class="nav-item d-flex align-items-center">
	<a class="nav-link module-name" href="{{ route('risk.dashboard') }}"><i class="mdi mdi-alert-octagon"></i> Risk Management</a>
</li>
@endsection

@section('title')
<style type="text/css">
	.select2-container .select2-selection--single {
		height: 38px !important;
		padding: 5px;
	}
	.select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 36px !important;
	}
	/* Fix for Select2 inside Modal */
	.select2-container {
		z-index: 100000 !important;
	}
	.select2-dropdown {
		z-index: 100001 !important;
	}

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
		border-bottom: 2px solid #dc3545;
		color: #dc3545;
	}

	.tab-card-header>.nav-tabs>li>a:hover {
		color: #dc3545;
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

	/* Risk Sidebar Styles */
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
</style>
@yield('title2')
@endsection

@section('content')
@php
	$currentRoute = request()->route()->getName();
	$currentStatus = request()->get('status', 'All Risks');
	$workflowTotals = getRiskWorkflowTotals();
@endphp

<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-alert-octagon fa-3x"></i><br>
				<span class="text-lg text-bold">RISK MANAGEMENT</span>
			</div>

			<!-- Dashboard -->
			<a href="{{ route('risk.dashboard') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'risk.dashboard' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>

		<!-- Risk Workflow Section -->
		<a href="#risk-workflow-menu" data-toggle="collapse" aria-expanded="{{ str_contains($currentRoute, 'risk.risks') ? 'true' : 'false' }}"
			   class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple mr-3"></span>
					<span class="menu-collapsed">Risk Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="risk-workflow-menu" class="collapse sidebar-submenu {{ str_contains($currentRoute, 'risk.risks') ? 'show' : '' }}">
				@php
					$workflowSteps = getRiskWorkflowSteps();
				@endphp
				@foreach($workflowSteps as $stepNum => $stepName)
					<a href="{{ route('risk.risks.index', ['status' => $stepName]) }}" 
					   class="list-group-item list-group-item-action bg-dark text-white {{ ($currentStatus === $stepName) ? 'active' : '' }}">
						<span class="menu-collapsed">
							{{ $stepName }}
							<small class="float-right badge badge-pill badge-secondary workflow-count">
								{{ $workflowTotals[$stepNum] ?? 0 }}
							</small>
						</span>
					</a>
				@endforeach
			</div>

			<!-- Create New Risk -->
			<a href="{{ route('risk.risks.create') }}" class="bg-dark list-group-item list-group-item-action {{ $currentRoute === 'risk.risks.create' ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-plus-circle mr-3"></span>
					<span class="menu-collapsed">New Risk</span>
				</div>
			</a>

		<!-- Configuration Section -->
		<a href="#config-menu" data-toggle="collapse" aria-expanded="{{ str_contains($currentRoute, 'risk.config') || str_contains($currentRoute, 'risk.assessment') || str_contains($currentRoute, 'risk.settings') ? 'true' : 'false' }}"
			   class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cogs mr-3"></span>
					<span class="menu-collapsed">Settings</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="config-menu" class="collapse sidebar-submenu {{ str_contains($currentRoute, 'risk.config') || str_contains($currentRoute, 'risk.assessment') || str_contains($currentRoute, 'risk.settings') ? 'show' : '' }}">
				<a href="{{ route('risk.config.risk-categories') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'risk-categories') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Risk Categories</span>
				</a>
				<a href="{{ route('risk.config.risk-sources') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'risk-sources') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Risk Sources</span>
				</a>
				<a href="{{ route('risk.config.risk-statuses') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'risk-statuses') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Risk Statuses</span>
				</a>
				<a href="{{ route('risk.config.treatment-types') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'treatment-types') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Treatment Types</span>
				</a>
				<a href="{{ route('risk.assessment.likelihood-scales') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'likelihood-scales') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Likelihood Scales</span>
				</a>
				<a href="{{ route('risk.assessment.severity-scales') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'severity-scales') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Severity Scales</span>
				</a>
				<a href="{{ route('risk.settings.show', 'evaluation_result') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'evaluation_result') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Evaluation Results</span>
				</a>
				<a href="{{ route('risk.config.workflow-approvers') }}" class="list-group-item list-group-item-action bg-dark text-white {{ str_contains($currentRoute, 'workflow-approvers') ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Workflow Approvers</span>
				</a>
			</div>

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
		</div>
		@yield('content2')
	</div>
	<!-- Main Col END -->
</div>
@endsection

@section('script')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	// Toast notification function (matching audit module)
	function showToastNotification(message, type = 'info') {
		let container = document.getElementById('toast-container');
		if (!container) {
			container = document.createElement('div');
			container.id = 'toast-container';
			container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
			document.body.appendChild(container);
		}

		const toast = document.createElement('div');
		toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
		toast.style.cssText = 'margin-bottom: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
		toast.setAttribute('role', 'alert');
		
		const icons = {
			success: 'mdi-check-circle',
			error: 'mdi-alert-circle',
			warning: 'mdi-alert',
			info: 'mdi-information'
		};
		
		toast.innerHTML = `
			<i class="mdi ${icons[type] || icons.info}"></i> ${message}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		`;
		
		container.appendChild(toast);
		
		setTimeout(() => {
			toast.classList.remove('show');
			setTimeout(() => {
				if (toast.parentElement) {
					toast.parentElement.removeChild(toast);
				}
			}, 150);
		}, 5000);
	}

	window.showToastNotification = showToastNotification;

	// Session flash messages - convert to toast notifications
	@if (\Session::has('success'))
		setTimeout(function() {
			showToastNotification(@json(Session::get('success')), 'success');
		}, 300);
	@endif
	@if (\Session::has('error'))
		setTimeout(function() {
			showToastNotification(@json(Session::get('error')), 'error');
		}, 300);
	@endif
	@if (\Session::has('warning'))
		setTimeout(function() {
			showToastNotification(@json(Session::get('warning')), 'warning');
		}, 300);
	@endif
	@if (\Session::has('info'))
		setTimeout(function() {
			showToastNotification(@json(Session::get('info')), 'info');
		}, 300);
	@endif

	// Livewire notification listener
	document.addEventListener('livewire:init', function() {
		Livewire.on('notify', (event) => {
			const data = event[0] || event;
			showToastNotification(data.message || 'Notification', data.type || 'info');
		});
	});

	// Backward compatibility - keep showToast function
	function showToast(message, type = 'success') {
		showToastNotification(message, type === 'success' ? 'success' : 'error');
	}
</script>
@yield('script2')
@endsection


