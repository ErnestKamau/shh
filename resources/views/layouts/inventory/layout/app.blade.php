@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('inventory-home') }}"><i class="mdi mdi-package-variant"></i> Inventory Management</a>
</li>
<li class="nav-item pt-1">
	<div class="btn-group mt-2">
		<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="mdi mdi-map-marker"></i>
			@if (getCurrentUserLocation())
				Location <small class="text-muted"> > </small> {{ getCurrentUserLocation()->name }}
			@else
				Select Location
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
		Alerts {!! $alerts > 0 ? '<small class="badge badge-danger badge">'.number_format($alerts).' '.($alerts == 10 ? '+' : '').'</small>' : '' !!}
	</a>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 350px; overflow-x: hidden; text=overflow: ellipsis ">
		@foreach ($alertsArray as $alert=>$data)
			@if($data['count'] > 0)
				<span class="dropdown-header" style="text-overflow: ellipsis; whitespace: nowrap">{{ $alert }} Alerts</span>
				@foreach ($data['items'] as $item)
					<a class="dropdown-item" href="{{ $item['alert_url'] }}" style="overflow: hidden; text-overflow:ellipsis">
						<small>{{ $item['url_name'] }}</small>
					</a>
				@endforeach
				@if($loop->iteration != $alerters)
					<div class="dropdown-divider"></div>
				@endif
			@endif

			@if (gettype($alertsArray) == "array" && count($alertsArray) == 0)
				<span class="text-muted"><i class="mdi mdi-information-circle"></i> No Alerts</span>
			@endif
		@endforeach
		<div class="m-2 mt-4">
			<a href="{{ route('send-restock-notifications') }}" class="btn btn-success btn-sm btn-block">Send Re-order Notifications</a>
		</div>
	</div>
</li>
@endsection

@section('title')
  @yield('title2')
@endsection


@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-package-variant fa-3x"></i><br>
				<span class="text-lg text-bold">INVENTORY MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->


			<a href="/inventory-home" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="{{ route('my-approvals') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-draw fa-fw mr-3"></span>
					<span class="menu-collapsed">Approval Requests
						<small class="float-right badge badge-danger mt-1 ml-3">{{ count(pendingApprovals()) }}</small>
					</span>
				</div>
			</a>
			<a href="#request-to-order" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">Request to Order</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-order" class="collapse sidebar-submenu">
				<?php
					$menuTotals = getRequisitionWorkflowTotals();
				?>
				@foreach (getRequisitionWorkflow() as $item)
					<a href="{{ route('go_to_stage', ['stage'=>$item]) }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}
						<small class="float-right badge badge-pill">{{ $menuTotals[$item] ?? 0 }}</small>
					</span>
					</a>
				@endforeach
			</div>
			<a href="#request-to-store" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">Request to Store</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-store" class="collapse sidebar-submenu">
				@foreach (getRequestToStoreWorkflow() as $item)
					<a href="{{ route('go_to_stage', ['stage'=>$item]) }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}
						<small class="float-right badge badge-pill">{{ $menuTotals[$item] ?? 0 }}</small>
					</span>
					</a>
				@endforeach
			</div>
			@if(isETCU())
				<a href="#loan-lend" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-file-tree mr-3"></span>
						<span class="menu-collapsed">Loan/Lend</span>
						<span class="submenu-icon ml-auto"></span>
					</div>
				</a>
				<div id="loan-lend" class="collapse sidebar-submenu">
					<a href="{{ route('go_to_stage', ['stage'=>'Lend']) }}" class="list-group-item list-group-item-action">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Lend
							<small class="float-right badge badge-pill">{{ $menuTotals['Lend'] ?? 0 }}</small>
						</span>
					</a>
					<a href="{{ route('go_to_stage', ['stage'=>'Loan']) }}" class="list-group-item list-group-item-action">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Loan
							<small class="float-right badge badge-pill">{{ $menuTotals['Loan'] ?? 0 }}</small>
						</span>
					</a>
				</div>
			@endif
			{{-- <a href="{{route('user-detail-supplier')}}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chat-processing fa-fw mr-3"></span>
					<span class="menu-collapsed">Chat</span>
				</div>
			</a> --}}
			<a href="/inventory-categories" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted-type fa-fw mr-3"></span>
					<span class="menu-collapsed">Categories</span>
				</div>
			</a>
			<a href="/inventory-activity" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-areaspline fa-fw mr-3"></span>
					<span class="menu-collapsed">Inventory Movement</span>
				</div>
			</a>
			<a href="/inventory-departments" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Departments</span>
				</div>
			</a>
			<a href="/inventory-suppliers" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Suppliers</span>
				</div>
			</a>
			<a href="/inventory-stores" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-package-variant-closed fa-fw mr-3"></span>
					<span class="menu-collapsed">Store</span>
				</div>
			</a>
			<a href="{{ route('stock-taking-list') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-replace fa-fw mr-3"></span>
					<span class="menu-collapsed">Stock Taking</span>
				</div>
			</a>
			<a href="{{ route('stock-transfer-list') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-bank-transfer-out fa-fw mr-3"></span>
					<span class="menu-collapsed">Stock Transfer</span>
				</div>
			</a>
			<a href="{{ route('inventory-reports') }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">Reports</span>
				</div>
			</a>
			<a href="{{ route('inventory-reporting-units', ['module'=>'inventory']) }}" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit fa-fw mr-3"></span>
					<span class="menu-collapsed">Unit of Measure</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Configurations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
					$menuTotals = array("Material Type", "Currency");
				?>
				@foreach ($menuTotals as $item)
					<a href="{{ route('module-pre-configs', ['config'=>$item, 'module'=>'Inventory-Management']) }}" class="list-group-item list-group-item-action">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}</span>
					</a>
				@endforeach
				<a href="{{ route('view-currency-conversions') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Currency Conversion</span>
				</a>
				<a href="{{ route('view-uom-conversions') }}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>UoM Conversion</span>
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
<livewire:a-i.ai-drawer :context="'inventory'" />
@endsection

@section('script')
	<script>
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