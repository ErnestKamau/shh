
@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('system-settings') }}"><i class="fas fa-cogs"></i> {{ __('system.system_settings') }}</a>
</li>
@endsection

@section('title')
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
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="fas fa-cogs fa-3x"></i><br>
				<span class="text-lg text-bold">{{ __('system.system_settings') }}</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->

			<a href="{{ route('system-settings') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('system.system_dashboard') }}</span>
				</div>
			</a>
			
			@can('system.companies.view')
			<a href="/companies" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-domain fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('system.companies') }}</span>
				</div>
			</a>
			@endcan

			@can('system.module-switching.view')
			<a href="{{ route('system-settings.module-visibility') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-swap-horizontal fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('system.module_switching') }}</span>
				</div>
			</a>
			@endcan

			@if(auth()->user()->can('system.translations.view'))
			<a href="{{ route('system-settings.translations') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-translate fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('system.languages_and_translations') }}</span>
				</div>
			</a>
			@endif

			<a href="{{ route('bulk-import') }}" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-upload fa-fw mr-1"></span>
					<span class="menu-collapsed">Bulk Data Import</span>
				</div>
			</a>
			
			<a href="#system-defaults" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cogs fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('system.system_defaults') }}</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="system-defaults" class="collapse sidebar-submenu">
				@can('system.configuration_type.view')
				<a href="{{ route('configuration-type-home') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> {{ __('system.configuration_type') }}</span>
				</a>
				@endcan
				@can('system.configuration.view')
				<a href="{{ route('configuration-system-home') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> {{ __('system.system_configurations') }}</span>
				</a>
				@endcan
			</div>
			<div class="list-group-item copyright-lims p-4 text-center text-white" style="position: fixed;bottom:0">
				{{ __('system.copyright') }} {{ date('Y') }} <span class="text-red">Imara LIMS</span>
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
  <div class="flex-fill" id="main-body-content">
    @yield('content2')
  </div>
	</div>
</div>
@endsection

@section('script')
<link href="https://cdn.jsdelivr.net/npm/handsontable@7.4.2/dist/handsontable.full.min.css" rel="stylesheet" media="screen">
  @yield('script2')
@endsection