@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('dashboard-lab') }}"><i class="mdi mdi-flask"></i> Lab Management</a>
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

	/* Ensure lab module pages can scroll inside the fixed sidebar/header layout */
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
@yield('title2')
@endsection


@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		@php
			$user = auth()->user();
			$canLabDashboard = $user->can('laboratory.components.dashboard.view');
			$canAllSamples = $user->can('laboratory.components.all samples.view');
			$canInterLabLogs = $user->can('laboratory.components.inter-lab-logs.view');
			$canProformaInvoices = $user->can('laboratory.components.proforma invoices.view');
			$canTaxRegime = $user->can('laboratory.components.tax regime.view');
			$canQuotation = $user->can('laboratory.components.quotation.view');
			$canQc = $user->can('laboratory.components.qc sample.view');
			$canAnalytes = $user->can('laboratory.components.analytes.view');
			$canLabs = $user->can('laboratory.components.labs.view');
			$canSampleTrackingStages = $user->can('laboratory.components.sample-tracking-stages.view');
			$canSampleTypes = $user->can('laboratory.components.sample-types.view');
			$canRftForms = $user->can('laboratory.components.rft form.view');
			$canMethodValidationRegistration = $user->can('laboratory.components.method-validation.registration.view');
			$canMethodValidationDataReview = $user->can('laboratory.components.method-validation.data-review.view');
			$canUncertaintyBudget = $user->can('laboratory.components.uncertainty-budget.view');
			$canStockMonitoring = $user->can('laboratory.components.stock-monitoring.view');
			$canConfigRouteAccess = $user->can('laboratory.module.access');
			$canProducts = $user->can('crm.components.products.view');
			$canLabReports = $user->can('laboratory.components.lab-reports.view');
			$canReportingUnits = $user->can('laboratory.components.reporting-units.view');
		@endphp
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-flask fa-3x"></i><br>
				<span class="text-lg text-bold">LAB MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			@if($canLabDashboard)
			<a href="{{ route('dashboard-lab') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			@endif
			@if($canAllSamples || $canInterLabLogs)
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Sample Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
                $menuTotals = getSampleWorkFLowTotals();
                ?>
				@foreach (getSampleWorflowStages() as $item)
				@if($item == 'Samples In Lab' && $canInterLabLogs)
				<a href="{{route('interLabTransferIndex')}}" class="list-group-item list-group-item-action bg-dark text-white">
					<div class="d-flex w-100 justify-content-between align-items-center">
						<span class="menu-collapsed">
							<i class="mdi mdi-circle-medium"></i> Inter Lab Transfer
						</span>
						<small class="badge badge-pill badge-success">{{getInterLabTotals()}}</small>
					</div>
				</a>
				@endif
				@if($canAllSamples)
				<a href="{{ route('sample-workflow', ['status'=>$item]) }}" class="list-group-item list-group-item-action bg-dark text-white">
					<div class="d-flex w-100 justify-content-between align-items-center">
						<span class="menu-collapsed">
							<i class="mdi mdi-circle-medium"></i>{{ $item }}
						</span>
						<small class="badge badge-pill {{ $item == "Samples Request Review" ? 'badge-danger' : 'badge-dark' }}">{{ $menuTotals[$item] ?? 0 }}</small>
					</div>
				</a>
				@if($item == 'All Samples')
				<a href="{{ route('sample-submission-requests.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<div class="d-flex w-100 justify-content-between align-items-center">
						<span class="menu-collapsed">
							<i class="mdi mdi-circle-medium"></i> Submission Requests
						</span>
						<small class="badge badge-pill badge-dark">{{ $submissionRequestTotals }}</small>
					</div>
				</a>
				@endif
				@endif
				@endforeach

			</div>
			@endif
			@if($canProformaInvoices || $canTaxRegime || $canQuotation)
			<a href="#billing-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Billing</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
		<div id="billing-menu" class="collapse sidebar-submenu">
			@if($canProformaInvoices)
			<a href="{{route('billing.invoices')}}" class="list-group-item list-group-item-action bg-dark text-white">
				<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sales Orders
					<small class="float-right badge badge-pill"></small></span>
			</a>

			<a href="{{route('billing.invoicable-items')}}" class="list-group-item list-group-item-action bg-dark text-white">
				<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Invoicable Items
					<small class="float-right badge badge-pill"></small></span>
			</a>

		<a href="{{route('billing.dynamics-customers')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Dynamics Customers
				<small class="float-right badge badge-pill"></small></span>
		</a>

		<a href="{{route('billing.currencies')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Currencies
				<small class="float-right badge badge-pill"></small></span>
		</a>
			@endif

			@if($canTaxRegime)
		<a href="{{route('billing.tax-regime')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Tax Regime
					<small class="float-right badge badge-pill"></small></span>
			</a>
			@endif

			@if($canQuotation)
			<a href="#quotation-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" mdi mdi-clipboard-text-outline mr-3"></span>
					<span class="menu-collapsed">Quotations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="quotation-menu" class="collapse sidebar-submenu">
				<a href="{{route('quotation-index')}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> All Quotes
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="{{route('quotation-index',['stage'=>'Quote In Preparation'])}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Quotes In Preparation
						<small class="float-right badge badge-pill"></small></span>
				</a>
				
				<a href="{{route('quotation-index',['stage'=>'Quote In Approval'])}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Quotes In Approval
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="{{route('quotation-index',['stage'=>'Quote Complete'])}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Finalised Quotes
						<small class="float-right badge badge-pill"></small></span>
				</a>
			</div>
			@endif

		</div>
			@endif
			@if($canQc)
			<a href="#qc-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark hidden list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-certificate-outline mr-3"></span>
					<span class="menu-collapsed">Qc Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="qc-workflow-menu" class="collapse sidebar-submenu">
				<!-- <a href="" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Dashboard
						<small class="float-right badge badge-pill"></small></span>
				</a> -->
				<a href="{{route('qcWorkflowIndex')}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Qc History
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="{{ route('showUnProcessed') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Awaiting Processing
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="{{ route('qc-reports') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Qc Reports
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="{{route('qc_configuration_index')}}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Configurations
						<small class="float-right badge badge-pill"></small></span>
				</a>

			</div>
			@endif
			@if($canAnalytes)
			<a href="/analytes" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-molecule fa-fw mr-3"></span>
					<span class="menu-collapsed">Analytes</span>
				</div>
			</a>
			@endif
			@if($canLabs)
			<a href="/labs" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-flask fa-fw mr-3"></span>
					<span class="menu-collapsed">Directorate Management</span>
				</div>
			</a>
			@endif
			@if($canSampleTrackingStages)
			<a href="/sample-analysis-stages" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-sitemap fa-fw mr-3"></span>
					<span class="menu-collapsed">Labs</span>
				</div>
			</a>
			@endif
			@if($canSampleTypes)
			<a href="{{ route('livewire.sample-types') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-test-tube fa-fw mr-3"></span>
					<span class="menu-collapsed">Sample Types</span>
				</div>
			</a>
			<a href="{{ route('formulars.index') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calculator fa-fw mr-3"></span>
					<span class="menu-collapsed">Formulas</span>
				</div>
			</a>
			<a href="{{ route('livewire.standards') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-scale-balance fa-fw mr-3"></span>
					<span class="menu-collapsed">Standards</span>
				</div>
			</a>
			<a href="{{ route('livewire.report-formats') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Report Formats</span>
				</div>
			</a>
			<a href="{{ route('remedies.index') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-medical-bag fa-fw mr-3"></span>
					<span class="menu-collapsed">Remedies</span>
				</div>
			</a>
			<a href="{{ route('ratings.index') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-box-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Key Configurations</span>
				</div>
			</a>
			@endif

			@if($canReportingUnits)
			<a href="/reporting-units" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit fa-fw mr-3"></span>
					<span class="menu-collapsed">Reporting Units</span>
				</div>
			</a>
			@endif
			@if($canMethodValidationRegistration || $canMethodValidationDataReview)
				<a href="#method-validation-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-clipboard-check-outline mr-3"></span>
						<span class="menu-collapsed">Method Validation</span>
						<span class="submenu-icon ml-auto"></span>
					</div>
				</a>
				<div id="method-validation-menu" class="collapse sidebar-submenu">
					<a href="/analysis-methods" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Methods</span>
					</a>
					@if($canMethodValidationRegistration)
					<a href="{{ route('method-validation.registration') }}" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Method Registration</span>
					</a>
					@endif
					@if($canMethodValidationDataReview)
					<a href="{{ route('method-validation.data-review') }}" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Data Review & Analysis</span>
					</a>
					@endif
				</div>
				@endif
				@if($canUncertaintyBudget)
				<a href="{{ route('uncertainty-budgets.index') }}" class="bg-dark list-group-item list-group-item-action">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-calculator fa-fw mr-3"></span>
					<span class="menu-collapsed">Uncertainty Budget</span>
				</div>
			</a>
			@endif

			@if($canStockMonitoring)
			<a href="#stock-monitoring-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Solutions Monitoring</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="stock-monitoring-menu" class="collapse sidebar-submenu">

				<a href="{{route('stock-monitoring-categories')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Categories
				<small class="float-right badge badge-pill"></small></span>
			</a>
			<a href="{{route('stock_management_index')}}" class="list-group-item list-group-item-action bg-dark text-white">
				<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Solutions Management
					<small class="float-right badge badge-pill"></small></span>
			</a>
			<a href="{{route('solution-movement-index')}}" class="bg-dark list-group-item list-group-item-action">
				<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Solutions Movement
					<small class="float-right badge badge-pill"></small></span>
			</a>


	</div>
			@endif

	{{-- <a href="/sample-analysis-stages" class="bg-dark list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-sitemap fa-fw mr-3"></span>
			<span class="menu-collapsed">Labs</span>
		</div>
	</a> --}}
	@if($canProducts || $canSampleTypes || $canConfigRouteAccess || $canRftForms)
	<a href="#configuration-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-cogs mr-3"></span>
			<span class="menu-collapsed">Configurations</span>
			<span class="submenu-icon ml-auto"></span>
		</div>
	</a>
	<div id="configuration-menu" class="collapse sidebar-submenu">



		@if($canProducts)
		<a href="{{route('sample-product-index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Products
				<small class="float-right badge badge-pill"></small></span>
		</a>
		@endif
		@if($canSampleTypes)
		<a href="{{route('sample_condition_index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Conditions
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{route('sample-type-category-index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Type Category
				<small class="float-right badge badge-pill"></small></span>
		</a>
		@endif
		@if($canConfigRouteAccess)
		<a href="{{ route('module-pre-configs', ['config' => 'Zones', 'module' => 'Lab-Management']) }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Zone
				<small class="float-right badge badge-pill"></small></span>
		</a>
		@endif
		@if($canRftForms)
		<a href="{{route('submission-forms.index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Submission Form Templates
				<small class="float-right badge badge-pill"></small></span>
		</a>

		<a href="{{ route('supporting-documents.templates.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Supporting Documents
				<small class="float-right badge badge-pill"></small></span>
		</a>
		
		<a href="{{route('templates.index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Report Templates
				<small class="float-right badge badge-pill"></small></span>
		</a>
		@endif

	</div>
	@endif

	<a href="/qualification-home" class="bg-dark hidden list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-file-certificate fa-fw mr-3"></span>
			<span class="menu-collapsed">Certifications</span>
		</div>
	</a>
	
	@if($canLabReports)
	<a href="#report-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-cogs mr-3"></span>
			<span class="menu-collapsed">Reports</span>
			<span class="submenu-icon ml-auto"></span>
		</div>
	</a>
	<div id="report-menu" class="collapse sidebar-submenu">

		<a href="{{ route('lab-reports-home') }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Lab Reports
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{ route('lab-report-tat') }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> TAT Reports
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{ route('lab-report-disposal') }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Disposal Reports
				<small class="float-right badge badge-pill"></small></span>
		</a>
		
	</div>
	@endif

	<div class="list-group-item copyright-lims p-4 text-center text-white">
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
	@php
		$currentRouteName = optional(request()->route())->getName();
		$disableDynamicSubmissionForms = \Illuminate\Support\Str::startsWith((string) $currentRouteName, 'submission-forms.instances.');
		$pageSectionForms = collect();
		$pageButtonForms  = collect();

		if ($currentRouteName && !$disableDynamicSubmissionForms) {
			$cacheKey = 'sf_page_forms_' . $currentRouteName;
			$formsForCurrentPage = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($currentRouteName) {
				return \App\Models\SubmissionForm::query()
					->where('is_published', true)
					->where('is_active', true)
					->where(function ($q) use ($currentRouteName) {
						$q->whereNull('target_pages')
						  ->orWhereJsonLength('target_pages', 0)
						  ->orWhereJsonContains('target_pages', $currentRouteName);
					})
					->orderBy('name')
					->get();
			});

			$pageSectionForms = $formsForCurrentPage->where('placement_mode', 'page_section')->values();
			$pageButtonForms  = $formsForCurrentPage->where('placement_mode', 'button_trigger')->values();
		}

		// Split page_section forms: those for before/after content slots are rendered in PHP.
		// Forms with other slots (after_breadcrumb, etc.) are rendered via JS after page load.
		$phpBeforeForms = $pageSectionForms->filter(function ($f) use ($currentRouteName) {
			$slot = $f->placement_slot[$currentRouteName]['slot_id'] ?? '';
			return $slot === 'before_page_content' || $slot === '';
		})->values();

		$phpAfterForms = $pageSectionForms->filter(function ($f) use ($currentRouteName) {
			$slot = $f->placement_slot[$currentRouteName]['slot_id'] ?? '';
			return $slot === 'after_page_content';
		})->values();

		$jsSectionForms = $pageSectionForms->filter(function ($f) use ($currentRouteName) {
			$slot = $f->placement_slot[$currentRouteName]['slot_id'] ?? '';
			return $slot !== '' && $slot !== 'before_page_content' && $slot !== 'after_page_content';
		})->values();

		// Split button forms: those with trigger IDs go to JS binding; rest go to the dropdown
		$dropdownButtonForms = $pageButtonForms->filter(function ($f) use ($currentRouteName) {
			$triggers = $f->trigger_button_ids[$currentRouteName] ?? [];
			return empty($triggers);
		})->values();

		$jsButtonForms = $pageButtonForms->filter(function ($f) use ($currentRouteName) {
			$triggers = $f->trigger_button_ids[$currentRouteName] ?? [];
			return !empty($triggers);
		})->values();
	@endphp

	{{-- ── Before-content page_section forms ─────────────────────────────────── --}}
	@if($phpBeforeForms->count() > 0)
		<div class="px-3 pt-2" id="sf-before-content-forms">
			@foreach($phpBeforeForms as $embeddedForm)
				@include('submission-forms.partials.page-section-card', [
					'form'    => $embeddedForm,
					'context' => 'before_page_content',
				])
			@endforeach
		</div>
	@endif

	{{-- Anchor for JS-injected forms that target slots inside the content area --}}
	<div id="sf-js-form-host" style="display:none;"></div>

	@yield('content2')

	{{-- ── After-content page_section forms ──────────────────────────────────── --}}
	@if($phpAfterForms->count() > 0)
		<div class="px-3 pb-2" id="sf-after-content-forms">
			@foreach($phpAfterForms as $embeddedForm)
				@include('submission-forms.partials.page-section-card', [
					'form'    => $embeddedForm,
					'context' => 'after_page_content',
				])
			@endforeach
		</div>
	@endif

	{{-- ── Global Page Forms dropdown (button_trigger forms with no specific binding) ── --}}
	{{-- Hidden: Page Forms dropdown removed from UI (forms are placed inline on pages) --}}
	@if(false && $dropdownButtonForms->count() > 0)
		<div class="page-form-launcher dropdown">
			<button class="btn btn-primary dropdown-toggle" type="button" id="pageFormLauncher"
			        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="mdi mdi-file-document-plus"></i> Page Forms
			</button>
			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="pageFormLauncher">
				@foreach($dropdownButtonForms as $buttonForm)
					{{--
					<a class="dropdown-item" href="{{ route('submission-forms.instances.create', $buttonForm) }}">
						<i class="mdi mdi-file-document-edit-outline mr-1"></i>
						{{ $buttonForm->name }}
					</a>
					--}}
					<a class="dropdown-item sf-open-inline-form"
					   href="#"
					   data-form-name="{{ e($buttonForm->name) }}"
					   {{-- data-fill-url="{{ route('submission-forms.instances.create', $buttonForm) }}" --}}
					   data-launch-url="{{ route('submission-forms.instances.launch-inline', $buttonForm) }}">
						<i class="mdi mdi-file-document-edit-outline mr-1"></i>
						{{ $buttonForm->name }}
					</a>
				@endforeach
			</div>
		</div>

		<style>
			.page-form-launcher { position: fixed; right: 20px; bottom: 20px; z-index: 1050; }
		</style>
	@endif

	{{-- ── Smart placement JS ────────────────────────────────────────────────── --}}
	@if($phpBeforeForms->count() > 0 || $phpAfterForms->count() > 0 || $dropdownButtonForms->count() > 0 || $jsSectionForms->count() > 0 || $jsButtonForms->count() > 0)
	<style>
		/* Inline form modal */
		.sf-inline-modal .sf-inline-dialog {
			max-width: 78vw;
			width: 78vw;
			margin: 5vh auto;
		}
		.sf-inline-modal .sf-inline-content {
			height: 84vh;
			border: 0;
			border-radius: 6px;
			overflow: hidden;
			box-shadow: 0 10px 40px rgba(0,0,0,.35);
			display: flex;
			flex-direction: column;
			position: relative;
		}
		.sf-inline-modal .sf-inline-body {
			flex: 1 1 auto;
			overflow: hidden;
			position: relative;
		}
		.sf-inline-modal .sf-inline-loading {
			position: absolute;
			inset: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #fff;
			z-index: 2;
		}
		.sf-inline-modal .sf-inline-frame {
			width: 100%;
			height: 100%;
			border: 0;
			display: block;
			opacity: 0;
			transition: opacity .2s ease;
		}
		.sf-inline-modal .sf-inline-frame.is-ready {
			opacity: 1;
		}
		.sf-inline-modal .sf-inline-close {
			position: absolute;
			top: 8px;
			right: 12px;
			z-index: 10;
			background: rgba(0,0,0,.45);
			color: #fff;
			border: 0;
			border-radius: 50%;
			width: 32px;
			height: 32px;
			font-size: 20px;
			line-height: 1;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: .8;
			transition: opacity .15s;
		}
		.sf-inline-modal .sf-inline-close:hover { opacity: 1; }
		@media (max-width: 768px) {
			.sf-inline-modal .sf-inline-dialog { max-width: 100vw; width: 100vw; margin: 0; }
			.sf-inline-modal .sf-inline-content { height: 100vh; border-radius: 0; }
		}
	</style>
	@php
		$jsSectionFormsData = $jsSectionForms->map(function ($f) use ($currentRouteName) {
			$slotId = $f->placement_slot[$currentRouteName]['slot_id'] ?? '';
			return [
				'id'           => $f->id,
				'name'         => $f->name,
				'description'  => $f->description,
				'display_mode' => $f->display_mode ?? 'expanded',
				'slot_id'      => $slotId,
				'selector'     => \App\Services\PageLayoutRegistry::getSelectorForSlot($currentRouteName, $slotId),
				// Old navigation URL (kept for rollback): route('submission-forms.instances.create', $f)
				'launch_url'   => route('submission-forms.instances.launch-inline', $f),
			];
		})->values()->all();
		$jsButtonFormsData = $jsButtonForms->map(function ($f) use ($currentRouteName) {
			return [
				'id'          => $f->id,
				'name'        => $f->name,
				'description' => $f->description,
				// Old navigation URL (kept for rollback): route('submission-forms.instances.create', $f)
				'launch_url'  => route('submission-forms.instances.launch-inline', $f),
				'triggers'    => $f->trigger_button_ids[$currentRouteName] ?? [],
			];
		})->values()->all();
	@endphp
	<script>
	(function () {
		if (window.__sfInlineRuntimeInitialized === true) {
			return;
		}
		window.__sfInlineRuntimeInitialized = true;

		var currentRoute   = @json($currentRouteName);
		var jsSectionForms = @json($jsSectionFormsData);
		var jsButtonForms  = @json($jsButtonFormsData);
		var csrfToken      = @json(csrf_token());
		var launchState = window.__sfInlineLaunchState || {
			inProgress: false,
			lastLaunchAt: 0,
			activeXhr: null,
		};
		window.__sfInlineLaunchState = launchState;
		var listenersBound = window.__sfInlineLauncherBound === true;

		// ── Helpers ───────────────────────────────────────────────────────────
		function esc(s) {
			return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
		}

		function buildSectionCard(form) {
			var isCollapsible = form.display_mode === 'collapsible';
			var collapseId    = 'sf-collapse-' + form.id;
			var desc = form.description
				? '<p class="text-muted small mb-2">' + esc(form.description.substring(0, 120)) + '</p>'
				: '';
			var body = '<div class="' + (isCollapsible ? 'collapse' : '') + '" id="' + collapseId + '">'
				+ desc
				+ '<button type="button" class="btn btn-sm btn-primary sf-open-inline-form"'
				+ ' data-form-name="' + esc(form.name) + '" data-launch-url="' + esc(form.launch_url) + '">'
				+ '<i class="mdi mdi-pencil-plus mr-1"></i>Fill Form</button>'
				+ '</div>';

			var header = isCollapsible
				? '<div class="card-header py-2 d-flex align-items-center justify-content-between">'
					+ '<strong class="small">' + esc(form.name) + '</strong>'
					+ '<button class="btn btn-sm btn-outline-secondary py-0 px-2" type="button"'
					+ ' data-toggle="collapse" data-target="#' + collapseId + '"'
					+ ' aria-expanded="false">'
					+ '<i class="mdi mdi-chevron-down"></i></button>'
					+ '</div>'
				: '<div class="card-header py-2"><strong class="small">' + esc(form.name) + '</strong></div>';

			return '<div class="card sf-injected-card mb-2 shadow-sm">' + header + '<div class="card-body py-2">' + body + '</div></div>';
		}

		function insertAfterSlot(slotId, selector, html) {
			// 1. Explicit data-sf-slot attribute — most precise, always wins
			var anchor = document.querySelector('[data-sf-slot="' + slotId + '"]');
			// 2. CSS selector fallback — auto-detects position without per-page HTML changes
			if (!anchor && selector) {
				anchor = document.querySelector(selector);
			}
			if (!anchor) {
				// Nothing found — show at top of content area
				var host = document.getElementById('sf-js-form-host');
				if (host) {
					host.style.display = '';
					host.insertAdjacentHTML('beforeend', '<div class="px-3 pt-2">' + html + '</div>');
				}
				return;
			}
			var wrapper = document.createElement('div');
			wrapper.className = 'px-3 pt-2 sf-slot-injection';
			wrapper.innerHTML = html;
			anchor.parentNode.insertBefore(wrapper, anchor.nextSibling);
		}

		// ── Inject section forms at their declared slots ───────────────────────
		document.addEventListener('DOMContentLoaded', function () {
			jsSectionForms.forEach(function (form) {
				insertAfterSlot(form.slot_id, form.selector, buildSectionCard(form));
			});

			if (!listenersBound) {
				// Inline launcher for dropdown and PHP-rendered cards.
				document.addEventListener('click', function (e) {
					var launcher = e.target.closest('.sf-open-inline-form');
					if (!launcher) return;
					e.preventDefault();
					launchAndOpenInlineForm({
						name: launcher.getAttribute('data-form-name') || 'Submission Form',
						launch_url: launcher.getAttribute('data-launch-url') || ''
					}, launcher);
				});

				// ── Bind button-trigger forms via event delegation ─────────────────
				if (jsButtonForms.length > 0) {
					document.addEventListener('click', function (e) {
						jsButtonForms.forEach(function (form) {
							(form.triggers || []).forEach(function (triggerId) {
								var el = e.target.closest('[data-sf-trigger="' + triggerId + '"]');
								if (el) {
									launchAndOpenInlineForm(form, el);
								}
							});
						});
					});
				}

				window.__sfInlineLauncherBound = true;
			}
		});

		function launchAndOpenInlineForm(form, sourceEl) {
			if (!form.launch_url) return;
			var now = Date.now();
			if (launchState.inProgress || (now - launchState.lastLaunchAt) < 700) return;
			launchState.inProgress = true;
			launchState.lastLaunchAt = now;

			if (sourceEl) {
				sourceEl.setAttribute('disabled', 'disabled');
				sourceEl.classList.add('disabled');
			}

			launchState.activeXhr = $.ajax({
				url: form.launch_url,
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				success: function (resp) {
					if (!resp || !resp.success || !resp.fill_url) {
						return;
					}
					openFormModal({ name: form.name, fill_url: resp.fill_url });
				},
				error: function (xhr) {
					if (xhr && xhr.statusText === 'abort') {
						return;
					}
					var msg = 'Unable to open form right now. Please try again.';
					if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
						msg = xhr.responseJSON.message;
					}
					alert(msg);
				},
				complete: function () {
					launchState.inProgress = false;
					launchState.activeXhr = null;
					if (sourceEl) {
						sourceEl.removeAttribute('disabled');
						sourceEl.classList.remove('disabled');
					}
				}
			});
		}

		// ── Lightweight form launcher modal ────────────────────────────────────
		function openFormModal(form) {
			var existing = document.getElementById('sf-button-modal');
			if (existing) existing.remove();
			if (!form.fill_url) return;

			// Append ?inline=1 so the fill view uses the bare layout (no nav/sidebar)
			var inlineUrl = form.fill_url + (form.fill_url.indexOf('?') === -1 ? '?' : '&') + 'inline=1';

			var modal = document.createElement('div');
			modal.id = 'sf-button-modal';
			modal.innerHTML = [
				'<div class="modal fade sf-inline-modal" tabindex="-1" role="dialog">',
				  '<div class="modal-dialog modal-xl modal-dialog-centered sf-inline-dialog" role="document">',
				    '<div class="modal-content sf-inline-content">',
				      '<button type="button" class="sf-inline-close" data-dismiss="modal" aria-label="Close">',
				        '<span aria-hidden="true">&times;</span>',
				      '</button>',
				      '<div class="sf-inline-body">',
				        '<div class="sf-inline-loading">',
				          '<div class="text-center text-muted">',
				            '<span class="spinner-border text-primary" role="status" aria-hidden="true"></span>',
				            '<div class="mt-2">Loading form...</div>',
				          '</div>',
				        '</div>',
				        '<iframe src="' + esc(inlineUrl) + '" class="sf-inline-frame" title="Fill ' + esc(form.name) + '"></iframe>',
				      '</div>',
				    '</div>',
				  '</div>',
				'</div>',
			].join('');

			document.body.appendChild(modal);
			var modalEl = $(modal).find('.modal');
			var frame = modal.querySelector('.sf-inline-frame');
			var loader = modal.querySelector('.sf-inline-loading');

			function hideIframeLimsChrome() {
				if (!frame) return;
				try {
					var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
					if (!doc) return;

					var navToRemove = doc.querySelector('nav.navbar.fixed-top');
					if (navToRemove) {
						navToRemove.remove();
					}

					var styleId = 'sf-inline-hide-lims-style';
					if (!doc.getElementById(styleId)) {
						var style = doc.createElement('style');
						style.id = styleId;
						style.textContent = [
							'#main-app-header { display: none !important; }',
							'nav.navbar.fixed-top { display: none !important; }',
							'#app > nav.navbar { display: none !important; }',
							'#sidebar-container { display: none !important; }',
							'body { padding-top: 0 !important; }',
							'#app, #app > main { margin-top: 0 !important; padding-top: 0 !important; }',
							'#main-container-body { margin-left: 0 !important; width: 100% !important; padding-top: 0 !important; }',
							'#body-row { margin-left: 0 !important; margin-right: 0 !important; }'
						].join('\\n');
						(doc.head || doc.documentElement).appendChild(style);
					}
				} catch (e) {
					// Ignore DOM access errors; iframe can still render the form normally.
				}
			}

			if (frame && loader) {
				frame.addEventListener('load', function () {
					hideIframeLimsChrome();
					loader.style.display = 'none';
					frame.classList.add('is-ready');
				}, { once: true });

				setTimeout(function () {
					if (loader.style.display !== 'none') {
						loader.style.display = 'none';
						frame.classList.add('is-ready');
					}
				}, 10000);
			}

			modalEl.modal({ backdrop: true, keyboard: true });
			modalEl.modal('show');
			modalEl.on('hidden.bs.modal', function () {
				if (frame) {
					frame.setAttribute('src', 'about:blank');
				}
				modal.remove();
			});
		}
	}());
	</script>
	@endif
</div>
<!-- Main Col END -->
</div>
	<livewire:a-i.ai-drawer :context="'lab'" />
@endsection

@section('script')
<script>
	(function () {
		function unlockStuckScroll() {
			var hasOpenModal = document.querySelector('.modal.show');

			if (!hasOpenModal) {
				document.body.classList.remove('modal-open');
				document.body.style.removeProperty('overflow');
				document.body.style.removeProperty('padding-right');
			}
		}

		document.addEventListener('DOMContentLoaded', unlockStuckScroll);
		document.addEventListener('hidden.bs.modal', unlockStuckScroll);

		document.addEventListener('livewire:initialized', function () {
			unlockStuckScroll();
			Livewire.hook('morph.updated', unlockStuckScroll);
		});
	})();
</script>
@yield('script2')
@endsection
