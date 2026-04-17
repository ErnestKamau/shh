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
</style>
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
				<i class="mdi mdi-flask fa-3x"></i><br>
				<span class="text-lg text-bold">LAB MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="{{ route('dashboard-lab') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
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
				@if($item == 'Samples In Lab')
				<a href="{{route('interLabTransferIndex')}}" class="list-group-item list-group-item-action bg-dark text-white">
					<div class="d-flex w-100 justify-content-between align-items-center">
						<span class="menu-collapsed">
							<i class="mdi mdi-circle-medium"></i> Inter Lab Transfer
						</span>
						<small class="badge badge-pill badge-success">{{getInterLabTotals()}}</small>
					</div>
				</a>
				@endif
				<a href="{{ route('sample-workflow', ['status'=>$item]) }}" class="list-group-item list-group-item-action bg-dark text-white">
					<div class="d-flex w-100 justify-content-between align-items-center">
						<span class="menu-collapsed">
							<i class="mdi mdi-circle-medium"></i>{{ $item }}
						</span>
						<small class="badge badge-pill {{ $item == "Samples Request Review" ? 'badge-danger' : 'badge-dark' }}">{{ $menuTotals[$item] ?? 0 }}</small>
					</div>
				</a>
				@endforeach

			</div>
			<a href="#billing-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Billing</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
		<div id="billing-menu" class="collapse sidebar-submenu">

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

		<a href="{{route('billing.tax-regime')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Tax Regime
					<small class="float-right badge badge-pill"></small></span>
			</a>

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

		</div>
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
			<a href="/analytes" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-molecule fa-fw mr-3"></span>
					<span class="menu-collapsed">Analytes</span>
				</div>
			</a>
			<a href="/labs" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-flask fa-fw mr-3"></span>
					<span class="menu-collapsed">Directorate Management</span>
				</div>
			</a>
			<a href="/sample-analysis-stages" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-sitemap fa-fw mr-3"></span>
					<span class="menu-collapsed">Labs</span>
				</div>
			</a>
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

			<a href="/reporting-units" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit fa-fw mr-3"></span>
					<span class="menu-collapsed">Reporting Units</span>
				</div>
			</a>
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
					<a href="{{ route('method-validation.registration') }}" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Method Registration</span>
					</a>
					<a href="{{ route('method-validation.data-review') }}" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Data Review & Analysis</span>
					</a>
				</div>
				<a href="{{ route('uncertainty-budgets.index') }}" class="bg-dark list-group-item list-group-item-action">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-calculator fa-fw mr-3"></span>
					<span class="menu-collapsed">Uncertainty Budget</span>
				</div>
			</a>

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

	{{-- <a href="/sample-analysis-stages" class="bg-dark list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-sitemap fa-fw mr-3"></span>
			<span class="menu-collapsed">Labs</span>
		</div>
	</a> --}}
	<a href="#configuration-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-cogs mr-3"></span>
			<span class="menu-collapsed">Configurations</span>
			<span class="submenu-icon ml-auto"></span>
		</div>
	</a>
	<div id="configuration-menu" class="collapse sidebar-submenu">



		<a href="{{route('sample-product-index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Products
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{route('sample_condition_index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Conditions
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{route('sample-type-category-index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Type Category
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{ route('module-pre-configs', ['config' => 'Zones', 'module' => 'Lab-Management']) }}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Zones
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="{{route('submission-forms.index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Submission Form Templates
				<small class="float-right badge badge-pill"></small></span>
		</a>
		
		<a href="{{route('templates.index')}}" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Report Templates
				<small class="float-right badge badge-pill"></small></span>
		</a>

	</div>

	<a href="/qualification-home" class="bg-dark hidden list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-file-certificate fa-fw mr-3"></span>
			<span class="menu-collapsed">Certifications</span>
		</div>
	</a>
	
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
	@yield('content2')
</div>
<!-- Main Col END -->
</div>
@endsection

@section('script')
@yield('script2')
@endsection
