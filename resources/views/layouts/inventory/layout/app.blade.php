@extends('layouts.app', ['dataTable' => $dataTable ?? false, 'select2' => $select2 ?? false, 'datePicker' => $datePicker ?? false])

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('inventory-home') }}"><i class="mdi mdi-package-variant"></i> {{ inventoryLabel('module_name', 'Inventory Management') }}</a>
</li>
<li class="nav-item pt-1">
	<div class="btn-group mt-2">
		<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="mdi mdi-map-marker"></i>
			@if (getCurrentUserLocation())
				{{ inventoryLabel('location', 'Location') }} <small class="text-muted"> > </small> {{ getCurrentUserLocation()->name }}
			@else
				{{ inventoryLabel('select_location', 'Select Location') }}
			@endif
		</button>
		<div class="dropdown-menu" id="location-selector">
			@foreach (viewableLocations() as $key=>$item)
				@if(isset($item->level))
					<a class="dropdown-item" href="{{ route('set-user-location', ['id'=>$item->id]) }}">
						{{ $key }}
					</a>
				@else
					@foreach ($item as $key1=>$item1)
						@if(isset($item1->level))
							<a class="dropdown-item" href="{{ route('set-user-location', ['id'=>$item1->id]) }}">
								{{ $key }} <small class="text-muted"> > </small> {{ $key1 }}
							</a>
						@else
							@foreach ($item1 as $key2=>$item2)
								@if(isset($item2->level))
									<a class="dropdown-item" href="{{ route('set-user-location', ['id'=>$item2->id]) }}">
										{{ $key }} <small class="text-muted"> > </small> {{ $key1 }} <small class="text-muted"> > </small> {{ $key2 }}
									</a>
								@else
									@foreach ($item2 as $key3=>$item3)
										<a class="dropdown-item" href="{{ route('set-user-location', ['id'=>$item3->id]) }}">
											{{ $key }} <small class="text-muted"> > </small> {{ $key1 }} <small class="text-muted"> > </small> {{ $key2 }} <small class="text-muted"> > </small> {{ $key3 }}
										</a>
									@endforeach
								@endif
							@endforeach
						@endif
					@endforeach
					<div class="dropdown-divider"></div>
				@endif
			@endforeach
		</div>
	</div>
</li>
@endsection

@section('alerts')
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		<i class="mdi mdi-bell-ring"></i>
		<?php
			$alertsArray = array();
			$alerters = 0;
			$restockAlerts = getRestockNotifications(true);
			$alerts = 0;

			if(intval($restockAlerts['count']) > 0){
				$alerters+=1;
				$alerts += intval($restockAlerts['count']);
				$alertsArray['Restock'] = $restockAlerts;
			}
		?>
		{{ inventoryLabel('alerts', 'Alerts') }} {!! $alerts > 0 ? '<small class="badge badge-danger badge">'.number_format($alerts).' '.($alerts == 10 ? '+' : '').'</small>' : '' !!}
	</a>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 350px; overflow-x: hidden; text-overflow: ellipsis ">
		@if(count($alertsArray) === 0)
			<span class="dropdown-item text-muted"><i class="mdi mdi-information-outline"></i> {{ inventoryLabel('no_alerts', 'No Alerts') }}</span>
		@endif
		@foreach ($alertsArray as $alert=>$data)
			@if($data['count'] > 0)
				<span class="dropdown-header" style="text-overflow: ellipsis; whitespace: nowrap">{{ inventoryLabel('restock_alerts', 'Restock') }} {{ inventoryLabel('alerts', 'Alerts') }}</span>
				@foreach ($data['items'] as $item)
					<a class="dropdown-item" href="{{ $item['alert_url'] }}" style="overflow: hidden; text-overflow:ellipsis">
						<small>{{ $item['url_name'] }}</small>
					</a>
				@endforeach
				@if($loop->iteration != $alerters)
					<div class="dropdown-divider"></div>
				@endif
			@endif
		@endforeach
		<div class="m-2 mt-4">
			<a href="{{ route('send-restock-notifications') }}" class="btn btn-success btn-sm btn-block">{{ inventoryLabel('send_reorder_notifications', 'Send Re-order Notifications') }}</a>
		</div>
	</div>
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

	.tab-card-header > .nav-tabs {
		border: none;
		margin: 0;
	}

	.tab-card-header > .nav-tabs > li {
		margin-right: 2px;
	}

	.tab-card-header > .nav-tabs > li > a {
		border: 0;
		border-bottom: 2px solid transparent;
		margin-right: 0;
		color: #737373;
		padding: 2px 15px;
	}

	.tab-card-header > .nav-tabs > li > a.show {
		border-bottom: 2px solid var(--sys-primary-color);
		color: var(--sys-primary-color);
	}

	.tab-card-header > .nav-tabs > li > a:hover {
		color: var(--sys-primary-color);
	}

	.tab-card .nav-link.active {
		background-color: var(--color-primary-soft, #f1f5f9) !important;
		border: 1px solid var(--color-border, #e2e8f0) !important;
		color: var(--color-primary) !important;
	}

	.tab-card-header > .tab-content {
		padding-bottom: 0;
	}

	#main-container-body {
		height: calc(100dvh - 56px);
		overflow-y: auto;
		overflow-x: hidden;
	}

	@media (max-width: 991.98px) {
		#main-container-body {
			height: auto;
			min-height: calc(100dvh - 56px);
			overflow-y: visible;
			overflow-x: hidden;
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}
	}

	.btn-outline-primary {
		color: var(--sys-primary-color) !important;
		border-color: var(--sys-primary-color) !important;
	}

	.btn-outline-primary:hover {
		background-color: var(--sys-primary-color) !important;
		color: #fff !important;
	}

	.text-primary {
		color: var(--sys-primary-color) !important;
	}
</style>
@include('layouts.lab.partials.lab-panel-theme-styles')
@include('layouts.lab.partials.lab-surface-theme-styles')
@include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
@include('layouts.inventory.partials.theme-overrides')
@include('layouts.inventory.partials.responsive-styles')
@yield('title2')
@endsection


@section('content')
@php
	$inventoryRouteName = optional(request()->route())->getName();
	$inventoryStage = request()->route('stage');
	$inventoryStageValue = is_string($inventoryStage) ? trim($inventoryStage) : '';
	$purchaseWorkflowStages = getRequisitionWorkflow();
	$storeWorkflowStages = getRequestToStoreWorkflow();
	$loanLendStages = ['Lend', 'Loan'];
	$isInPurchaseWorkflow = in_array($inventoryStageValue, $purchaseWorkflowStages, true)
		&& in_array($inventoryRouteName, ['go_to_stage', 'view-request-details'], true);
	$isInStoreWorkflow = in_array($inventoryStageValue, $storeWorkflowStages, true)
		&& in_array($inventoryRouteName, ['go_to_stage', 'view-request-details'], true);
	$isInLoanLendWorkflow = in_array($inventoryStageValue, $loanLendStages, true)
		&& in_array($inventoryRouteName, ['go_to_stage', 'view-request-details'], true);
	$isInventoryConfigActive = request()->routeIs(
		'module-pre-configs',
		'view-currency-conversions',
		'view-uom-conversions'
	);
	$pendingApprovalItems = collect();
	if (getCurrentUserLocation()) {
		try {
			$pendingApprovalItems = pendingApprovals();
		} catch (\Throwable $exception) {
			$pendingApprovalItems = collect();
		}
	}
	$pendingApprovalCount = $pendingApprovalItems->count();
@endphp
@include('layouts.lab.partials.lab-chrome-slice1-styles')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-package-variant fa-3x"></i><br>
				<span class="text-lg text-bold">{{ inventoryLabel('module_name', 'Inventory Management') }}</span>
			</div>

			<a href="{{ route('inventory-home') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-home') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('dashboard', 'Dashboard') }}</span>
				</div>
			</a>
			<a href="{{ route('my-approvals') }}" class="list-group-item list-group-item-action {{ request()->routeIs('my-approvals') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-draw fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('approval_requests', 'Approval Requests') }}
						@if($pendingApprovalCount > 0)
							<small class="float-right badge badge-danger mt-1 ml-3">{{ number_format($pendingApprovalCount) }}</small>
						@endif
					</span>
				</div>
			</a>
			<a href="#request-to-order" data-toggle="collapse" aria-expanded="{{ $isInPurchaseWorkflow ? 'true' : 'false' }}" class="list-group-item list-group-item-action flex-column align-items-start {{ $isInPurchaseWorkflow ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('request_to_order', 'Request to Order') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-order" class="collapse sidebar-submenu {{ $isInPurchaseWorkflow ? 'show' : '' }}">
				<?php
					$menuTotals = getRequisitionWorkflowTotals();
				?>
				@foreach (getRequisitionWorkflow() as $item)
					<a href="{{ route('go_to_stage', ['stage'=>$item]) }}" class="list-group-item list-group-item-action {{ $inventoryStageValue === $item ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ getInventoryWorkflowStageLabel($item) }}
						@if(($menuTotals[$item] ?? 0) > 0)
							<small class="float-right badge badge-pill">{{ $menuTotals[$item] }}</small>
						@endif
					</span>
					</a>
				@endforeach
			</div>
			<a href="#request-to-store" data-toggle="collapse" aria-expanded="{{ $isInStoreWorkflow ? 'true' : 'false' }}" class="list-group-item list-group-item-action flex-column align-items-start {{ $isInStoreWorkflow ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('request_to_store', 'Request to Store') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-store" class="collapse sidebar-submenu {{ $isInStoreWorkflow ? 'show' : '' }}">
				@foreach (getRequestToStoreWorkflow() as $item)
					<a href="{{ route('go_to_stage', ['stage'=>$item]) }}" class="list-group-item list-group-item-action {{ $inventoryStageValue === $item ? 'active' : '' }}">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ getInventoryWorkflowStageLabel($item) }}
						@if(($menuTotals[$item] ?? 0) > 0)
							<small class="float-right badge badge-pill">{{ $menuTotals[$item] }}</small>
						@endif
					</span>
					</a>
				@endforeach
			</div>
			@if(isETCU())
				<a href="#loan-lend" data-toggle="collapse" aria-expanded="{{ $isInLoanLendWorkflow ? 'true' : 'false' }}" class="list-group-item list-group-item-action flex-column align-items-start {{ $isInLoanLendWorkflow ? 'active' : '' }}">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-file-tree mr-3"></span>
						<span class="menu-collapsed">{{ inventoryLabel('loan_lend', 'Loan/Lend') }}</span>
						<span class="submenu-icon ml-auto"></span>
					</div>
				</a>
				<div id="loan-lend" class="collapse sidebar-submenu {{ $isInLoanLendWorkflow ? 'show' : '' }}">
					<a href="{{ route('go_to_stage', ['stage'=>'Lend']) }}" class="list-group-item list-group-item-action {{ $inventoryStageValue === 'Lend' ? 'active' : '' }}">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ getInventoryWorkflowStageLabel('Lend') }}
							@if(($menuTotals['Lend'] ?? 0) > 0)
								<small class="float-right badge badge-pill">{{ $menuTotals['Lend'] }}</small>
							@endif
						</span>
					</a>
					<a href="{{ route('go_to_stage', ['stage'=>'Loan']) }}" class="list-group-item list-group-item-action {{ $inventoryStageValue === 'Loan' ? 'active' : '' }}">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ getInventoryWorkflowStageLabel('Loan') }}
							@if(($menuTotals['Loan'] ?? 0) > 0)
								<small class="float-right badge badge-pill">{{ $menuTotals['Loan'] }}</small>
							@endif
						</span>
					</a>
				</div>
			@endif

			<a href="{{ route('inventory-categories') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-categories', 'show-inventory-category', 'show-inventory-items') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted-type fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('categories', 'Categories') }}</span>
				</div>
			</a>
			<a href="{{ route('inventory-activity') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-activity', 'get-stock-movement') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-areaspline fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('inventory_movement', 'Inventory Movement') }}</span>
				</div>
			</a>
			<a href="{{ route('show-inventory-departments') }}" class="list-group-item list-group-item-action {{ request()->routeIs('show-inventory-departments', 'show-inventory-department') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('departments', 'Departments') }}</span>
				</div>
			</a>
			<a href="{{ route('inventory-locations') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-locations', 'show-inventory-locations') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('organizational_structure', 'Organizational Structure') }}</span>
				</div>
			</a>
			<a href="{{ route('inventory-suppliers') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-suppliers', 'show-supplier') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-group fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('suppliers', 'Suppliers') }}</span>
				</div>
			</a>

			<a href="{{ route('inventory-stores') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-stores', 'inventory-store-slots', 'inventory-slot-contents') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-package-variant-closed fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('store', 'Store') }}</span>
				</div>
			</a>
			<a href="{{ route('stock-taking-list') }}" class="list-group-item list-group-item-action {{ request()->routeIs('stock-taking-list', 'stock-taking-sheet') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-replace fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('stock_taking', 'Stock Taking') }}</span>
				</div>
			</a>
			<a href="{{ route('stock-transfer-list') }}" class="list-group-item list-group-item-action {{ request()->routeIs('stock-transfer-list', 'stock-transfer-sheet') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-bank-transfer-out fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('stock_transfer', 'Stock Transfer') }}</span>
				</div>
			</a>
			<a href="{{ route('inventory-reports') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-reports', 'consumption-reports', 'fields', 'store_report', 'fetch_report', 'delete_report', 'report_print', 'report_csv', 'update_report') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('reports', 'Reports') }}</span>
				</div>
			</a>

			<a href="{{ route('inventory-reporting-units', ['module'=>'inventory']) }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory-reporting-units') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('unit_of_measure', 'Unit of Measure') }}</span>
				</div>
			</a>
			<a href="#inventory-config-menu" data-toggle="collapse" aria-expanded="{{ $isInventoryConfigActive ? 'true' : 'false' }}" class="list-group-item list-group-item-action flex-column align-items-start {{ $isInventoryConfigActive ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">{{ inventoryLabel('configurations', 'Configurations') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="inventory-config-menu" class="collapse sidebar-submenu {{ $isInventoryConfigActive ? 'show' : '' }}">
				<?php
					$configMenuItems = array("Material Type", "Currency");
				?>
				@foreach ($configMenuItems as $item)
					<a href="{{ route('module-pre-configs', ['config'=>$item, 'module'=>'Inventory-Management']) }}" class="list-group-item list-group-item-action">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item === 'Material Type' ? inventoryLabel('material_type', 'Material Type') : inventoryLabel('currency', 'Currency') }}</span>
					</a>
				@endforeach
				<a href="{{ route('view-currency-conversions') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ inventoryLabel('currency_conversion', 'Currency Conversion') }}</span>
				</a>
				<a href="{{ route('view-uom-conversions') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ inventoryLabel('uom_conversion', 'UoM Conversion') }}</span>
				</a>
			</div>


			<div class="list-group-item copyright-lims p-4 text-center">
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
		<div class="container-fluid inventory-page lab-surface-theme ls-admin-page lab-panel-theme workflow-theme ls-ui-kit" data-ls-type="plex">
			@yield('content2')
		</div>
	</div>
	<!-- Main Col END -->
</div>
<livewire:a-i.ai-drawer :context="'inventory'" />
@endsection

@section('script')
	<script>
		(function () {
			var unlockTimer = null;

			function unlockStuckScroll() {
				if (unlockTimer !== null) {
					clearTimeout(unlockTimer);
				}

				unlockTimer = setTimeout(function () {
					unlockTimer = null;
					var hasOpenModal = document.querySelector('.modal.show');

					if (!hasOpenModal) {
						document.body.classList.remove('modal-open');
						document.body.style.removeProperty('overflow');
						document.body.style.removeProperty('padding-right');
					}
				}, 100);
			}

			document.addEventListener('DOMContentLoaded', unlockStuckScroll);
			document.addEventListener('hidden.bs.modal', unlockStuckScroll);
		})();

		$(function(){
			@if (!getCurrentUserLocation())
				var loc = $('#location-selector').find('.dropdown-item').first().attr('href');
				if (loc) {
					window.location.href = loc;
				}
			@endif
		});
	</script>
	@yield('script2')
@endsection
