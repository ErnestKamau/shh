@extends('layouts.app', ['dataTable' => $dataTable ?? false, 'select2' => $select2 ?? false])

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('full-calendar') }}"><i class="mdi mdi-calendar"></i> {{ __('planner.module_name') }}</a>
</li>
@endsection

@section('title')
  <style type="text/css">
    /* Premium Modal Overhaul */
    #event-modal .modal-content {
      border: none !important;
      border-radius: 14px !important;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
      overflow: hidden;
    }

    #event-modal .modal-header {
      padding: 1.25rem 1.5rem !important;
    }

    #event-modal .modal-header .modal-title {
      font-size: 1.1rem !important;
      font-weight: 600 !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
    }

    #event-modal .modal-header .close {
      font-size: 1.5rem !important;
      font-weight: 400 !important;
      transition: all 0.2s ease !important;
      outline: none !important;
      margin-top: -5px !important;
    }

    #event-modal .tab-card {
      border: none !important;
      box-shadow: none !important;
      background: transparent !important;
    }

    #event-modal .tab-card-header {
      background-color: #ffffff !important;
      border-bottom: 1px solid #e2e8f0 !important;
      padding: 0 1.5rem !important;
    }

    #event-modal .tab-card-header > .nav-tabs {
      border: none !important;
      margin: 0 !important;
      display: flex !important;
      gap: 1.5rem !important;
    }

    #event-modal .tab-card-header > .nav-tabs > li {
      margin: 0 !important;
    }

    #event-modal .tab-card-header > .nav-tabs > li > a {
      border: none !important;
      border-bottom: 2px solid transparent !important;
      margin: 0 !important;
      color: #64748b !important;
      font-weight: 500 !important;
      font-size: 13.5px !important;
      padding: 1rem 0 !important;
      transition: all 0.2s ease !important;
      background: transparent !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.35rem !important;
    }

    #event-modal .tab-card-header > .nav-tabs > li > a:hover {
      color: #0f172a !important;
    }

    #event-modal .tab-card-header > .nav-tabs > li > a.active {
      color: var(--color-primary, #6D0A0E) !important;
      border-bottom: 2px solid var(--color-primary, #6D0A0E) !important;
      font-weight: 600 !important;
      background: transparent !important;
    }

    /* Form Design Details */
    #event-modal .form-section-title {
      font-size: 11px !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      color: #94a3b8 !important;
      letter-spacing: 0.05em !important;
      margin-bottom: 1rem !important;
      margin-top: 1.25rem !important;
      border-bottom: 1px dashed #e2e8f0 !important;
      padding-bottom: 0.5rem !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      width: 100% !important;
    }

    #event-modal .form-section-title i {
      color: var(--color-primary, #6D0A0E) !important;
      font-size: 14px !important;
    }

    #event-modal .form-group label {
      font-size: 12px !important;
      font-weight: 600 !important;
      color: #475569 !important;
      margin-bottom: 0.35rem !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.25rem !important;
    }

    #event-modal .form-group label i {
      color: #94a3b8 !important;
      font-size: 14px !important;
    }

    #event-modal .form-control, 
    #event-modal .select2-container--default .select2-selection--single, 
    #event-modal .select2-container--default .select2-selection--multiple {
      background-color: #f8fafc !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 6px !important;
      color: #0f172a !important;
      font-size: 13px !important;
      padding: 0.45rem 0.75rem !important;
      height: auto !important;
      transition: all 0.15s ease-in-out !important;
      box-shadow: none !important;
    }

    #event-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: inherit !important;
      padding-left: 0 !important;
      color: #0f172a !important;
    }

    #event-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 100% !important;
      top: 0 !important;
    }

    #event-modal .form-control:focus, 
    #event-modal .select2-container--default.select2-container--focus .select2-selection--single, 
    #event-modal .select2-container--default.select2-container--focus .select2-selection--multiple {
      border-color: var(--color-primary, #6D0A0E) !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 3px var(--color-primary-focus, rgba(109, 10, 14, 0.18)) !important;
      outline: none !important;
    }

    #event-modal .form-control:disabled, 
    #event-modal .form-control[readonly], 
    #event-modal .select2-container--default.select2-container--disabled .select2-selection--single,
    #event-modal .select2-container--default.select2-container--disabled .select2-selection--multiple {
      background-color: #f1f5f9 !important;
      color: #475569 !important;
      border-color: #cbd5e1 !important;
      opacity: 0.85 !important;
      cursor: not-allowed !important;
    }

    /* Badges & Tables */
    #event-modal .badge-status {
      display: inline-flex !important;
      align-items: center !important;
      gap: 0.25rem !important;
      padding: 0.35rem 0.65rem !important;
      font-size: 11px !important;
      font-weight: 600 !important;
      border-radius: 50px !important;
      line-height: 1 !important;
    }
    #event-modal .badge-status-upcoming { background-color: #e0f2fe !important; color: #0369a1 !important; }
    #event-modal .badge-status-inprogress { background-color: #fef3c7 !important; color: #d97706 !important; }
    #event-modal .badge-status-complete { background-color: #dcfce7 !important; color: #15803d !important; }
    #event-modal .badge-status-delayed { background-color: #fee2e2 !important; color: #b91c1c !important; }
    #event-modal .badge-status-cancelled { background-color: #f1f5f9 !important; color: #475569 !important; }

    /* Timeline Event History Cards */
    #event-modal .history-timeline {
      position: relative !important;
      padding-left: 1.5rem !important;
      margin-left: 0.5rem !important;
      border-left: 2px solid #e2e8f0 !important;
    }

    #event-modal .history-card {
      position: relative !important;
      background-color: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      padding: 1rem !important;
      margin-bottom: 1.25rem !important;
      box-shadow: none !important;
      transition: all 0.2s ease !important;
    }

    #event-modal .history-card::before {
      content: '' !important;
      position: absolute !important;
      left: -1.95rem !important;
      top: 1.1rem !important;
      width: 10px !important;
      height: 10px !important;
      border-radius: 50% !important;
      background-color: var(--color-primary, #6D0A0E) !important;
      border: 2px solid #ffffff !important;
      box-shadow: 0 0 0 2px var(--color-primary, #6D0A0E) !important;
    }

    #event-modal .history-card:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
      border-color: #cbd5e1 !important;
    }

    #event-modal .history-card-header {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      margin-bottom: 0.5rem !important;
      font-size: 11.5px !important;
      color: #64748b !important;
    }

    #event-modal .history-card-body {
      font-size: 13px !important;
      color: #1e293b !important;
    }

    #event-modal .occurrence-action-header {
      width: 80px !important;
    }

    /* Modal Header — brand mono (global lab/surface theme) */
    #event-modal .modal-header {
      background-color: var(--color-primary, #6D0A0E) !important;
      background-image: var(--ls-modal-header-background-image, var(--ls-modal-header-gradient, linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%))) !important;
      background-size: var(--ls-modal-header-background-size, auto) !important;
      background-repeat: no-repeat !important;
      border-bottom: none !important;
      padding: 1rem 1.5rem !important;
      color: #ffffff !important;
      border-top-left-radius: 4px !important;
      border-top-right-radius: 4px !important;
      box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
    }
    #event-modal .modal-header .modal-title,
    #event-modal .modal-header .modal-title i {
      color: #ffffff !important;
      font-weight: 600 !important;
      font-size: 16px !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
    }
    #event-modal .modal-header .close {
      color: #ffffff !important;
      opacity: 0.8 !important;
      text-shadow: none !important;
      font-size: 24px !important;
      outline: none !important;
      margin-top: -8px !important;
    }
    #event-modal .modal-header .close:hover {
      opacity: 1 !important;
    }

    /* Modal Tabs Header Styling */
    #event-modal .tab-card-header {
      background-color: #f8fafc !important;
      border-bottom: 1px solid #e2e8f0 !important;
      padding: 0 1.5rem !important;
    }

    /* Warning Banner Styling */
    #event-modal .occurrence-banner {
      background-color: #fffbeb !important;
      border: 1px solid #fde68a !important;
      border-left: 4px solid #d97706 !important;
      color: #b45309 !important;
      border-radius: 8px !important;
      padding: 0.75rem 1rem !important;
      font-size: 13px !important;
      font-weight: 500 !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      gap: 1rem !important;
      margin-bottom: 1.25rem !important;
    }

    /* Occurrences Table styling */
    #event-modal #EventOccurrences-tab table {
      border-collapse: separate !important;
      border-spacing: 0 8px !important;
      width: 100% !important;
    }
    #event-modal #EventOccurrences-tab thead th {
      border: none !important;
      font-size: 11px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.05em !important;
      color: #64748b !important;
      font-weight: 700 !important;
      padding: 8px 16px !important;
    }
    #event-modal #EventOccurrences-tab tbody tr {
      background-color: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02) !important;
      border-radius: 8px !important;
      transition: all 0.2s ease !important;
    }
    #event-modal #EventOccurrences-tab tbody tr:hover {
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
      border-color: #cbd5e1 !important;
    }
    #event-modal #EventOccurrences-tab tbody td {
      border: none !important;
      padding: 12px 16px !important;
      vertical-align: middle !important;
      font-size: 13.5px !important;
      color: #334155 !important;
    }
    #event-modal #EventOccurrences-tab tbody td:first-child {
      border-top-left-radius: 8px !important;
      border-bottom-left-radius: 8px !important;
      font-weight: 600 !important;
      color: #64748b !important;
    }
    #event-modal #EventOccurrences-tab tbody td:last-child {
      border-top-right-radius: 8px !important;
      border-bottom-right-radius: 8px !important;
      text-align: right !important;
    }

    /* Occurrences Styled Status Select Dropdown */
    #event-modal .occurrence-status-select {
      appearance: none !important;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23475569'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e") !important;
      background-repeat: no-repeat !important;
      background-position: right 8px center !important;
      background-size: 16px !important;
      padding-right: 28px !important;
      border: 1px solid transparent !important;
      border-radius: 30px !important;
      font-size: 12px !important;
      font-weight: 600 !important;
      padding-top: 4px !important;
      padding-bottom: 4px !important;
      padding-left: 14px !important;
      height: 28px !important;
      width: auto !important;
      display: inline-block !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      text-align: left !important;
    }
    #event-modal .select-status-upcoming {
      background-color: #e0f2fe !important;
      color: #0369a1 !important;
      border-color: #bae6fd !important;
    }
    #event-modal .select-status-inprogress {
      background-color: #fef3c7 !important;
      color: #d97706 !important;
      border-color: #fde68a !important;
    }
    #event-modal .select-status-complete {
      background-color: #dcfce7 !important;
      color: #15803d !important;
      border-color: #bbf7d0 !important;
    }
    #event-modal .select-status-delayed {
      background-color: #fee2e2 !important;
      color: #b91c1c !important;
      border-color: #fecaca !important;
    }
    #event-modal .select-status-cancelled {
      background-color: #f1f5f9 !important;
      color: #475569 !important;
      border-color: #e2e8f0 !important;
    }

    /* Occurrence Action Buttons */
    #event-modal .occurrence-actions {
      display: flex !important;
      gap: 0.35rem !important;
      justify-content: center !important;
      align-items: center !important;
    }
    #event-modal .occurrence-actions .btn {
      padding: 0px !important;
      background-color: #f8fafc !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 6px !important;
      transition: all 0.15s ease !important;
      cursor: pointer !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 28px !important;
      height: 28px !important;
      margin: 0 !important;
      box-shadow: none !important;
    }
    #event-modal .occurrence-actions .btn i {
      font-size: 14px !important;
      margin: 0 !important;
    }
    #event-modal .occurrence-actions .btn:hover {
      background-color: #f1f5f9 !important;
      border-color: #cbd5e1 !important;
      transform: translateY(-1px) !important;
    }

    /* Additional general styles */
    .datepicker {
      position: relative !important;
    }
    .card-widget {
        border-radius: 12px !important;
        margin: 0.5% !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }
    .display-4 {
        font-size: 20px !important;
    }
    .text-uppercase {
        font-size: 12px !important;
    }
    .btn-white{
        background-color: white !important;
    }
  </style>
  @yield('title2')
@endsection

@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block">
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-calendar-text fa-3x"></i><br>
				<span class="text-lg text-bold">{{ __('planner.module_name') }}</span>
			</div>

			<a href="{{ route('system-planner.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.dashboard') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.dashboard') }}</span>
				</div>
			</a>

			<a href="{{ route('system-planner.schedule-sampling') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.schedule-sampling') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clock-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.sampling_schedule') }}</span>
				</div>
			</a>

			<a href="{{ route('system-planner.fill-sampling-forms') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.fill-sampling-forms*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-edit-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.fill_sampling_forms') }}</span>
				</div>
			</a>

			<a href="{{ route('system-planner.actual-collections') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.actual-collections') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-check-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.actual_collections') }}</span>
				</div>
			</a>

			<a href="{{ route('system-planner.kpi-reports') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.kpi-reports') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-timeline-variant fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.kpi_reports') }}</span>
				</div>
			</a>

			<a href="{{ route('system-planner.tasks') }}" class="list-group-item list-group-item-action {{ request()->routeIs('system-planner.tasks') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calendar-text-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.tasks') }}</span>
				</div>
			</a>

			<a href="{{ route('full-calendar') }}" class="list-group-item list-group-item-action {{ request()->routeIs('full-calendar') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-calendar-month-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">{{ __('planner.calendar') }}</span>
				</div>
			</a>

			<div class="list-group-item copyright-lims p-4 text-center">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
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
  @yield('script2')
@endsection
