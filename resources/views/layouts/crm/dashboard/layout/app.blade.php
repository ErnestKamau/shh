
@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('customers-list') }}"><i class="mdi mdi-account-group"></i>{{ __('crm.module_name') }}</a>
</li>
@endsection

@section('title')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
  <style type="text/css">
    .tab-card {
      border:1px solid #eee;
    }

    .tab-card-header {
      background:none;
    }
    /* Default mode */
    .tab-card-header > .nav-tabs {
      border: none;
      margin: 0px;
    }
    .tab-card-header > .nav-tabs > li {
      margin-right: 2px;
    }
    .tab-card-header > .nav-tabs > li > a {
      border: 0;
      border-bottom:2px solid transparent;
      margin-right: 0;
      color: #737373;
      padding: 2px 15px;
    }

    .tab-card-header > .nav-tabs > li > a.show {
      border-bottom:2px solid #007bff;
      color: #007bff;
    }
    .tab-card-header > .nav-tabs > li > a:hover {
      color: #007bff;
    }

    .tab-card .nav-link.active{
      background-color: #dadccd !important;
      border: 1px solid #cccebf !important;
    }

    .tab-card-header > .tab-content {
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
      <a href="/dasboard/crm/client-home" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.dashboard') }}</span>
				</div>
			</a>

            <a href="/dashboard/crm/client-details" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-details fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.client_details') }}</span>
				</div>
			</a>
			<a href="" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-notebook-edit-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.lab_booking') }}</span>
				</div>
			</a>
			<a href="" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-google-analytics fa-fw mr-3"></span>
					<span class="menu-collapsed">{{ __('crm.reports') }}</span>
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
	<div class="col-sm-8 col-md-9 col-lg-10 py-1" id="main-container-body">
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