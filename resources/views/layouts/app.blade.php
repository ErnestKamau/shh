<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">  -->

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @yield('title')

  <!-- Scripts -->
  <link rel="stylesheet" href="/assets/css/font-awesome/all.min.css">
  {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> --}}
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
	<link rel="stylesheet" href="/assets/css/bootstrap/bootstrap4.4.1.min.css">

  {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> --}}
  
  {{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.9.0/dist/fullcalendar.min.js"></script> --}}
  <style type="text/css">
    /* html,body{
      background-color: #2a2a2a;
		} */

		select, .select2.select2-container.select2-container--default{
			width: 100% !important;
		}

		button.dt-button, div.dt-button, a.dt-button{
    	padding: 2px 6px !important;
		}

		.bell{
			display:block;
			-webkit-animation: ring 4s .01s ease-in-out infinite;
			-webkit-transform-origin: 50% 4px;
			-moz-animation: ring 4s .01s ease-in-out infinite;
			-moz-transform-origin: 50% 4px;
			animation: ring 4s .01s ease-in-out infinite;
			transform-origin: 50% 4px;
		}

		@-webkit-keyframes ring {
			0% { -webkit-transform: rotateZ(0); }
			1% { -webkit-transform: rotateZ(30deg); }
			3% { -webkit-transform: rotateZ(-28deg); }
			5% { -webkit-transform: rotateZ(34deg); }
			7% { -webkit-transform: rotateZ(-32deg); }
			9% { -webkit-transform: rotateZ(30deg); }
			11% { -webkit-transform: rotateZ(-28deg); }
			13% { -webkit-transform: rotateZ(26deg); }
			15% { -webkit-transform: rotateZ(-24deg); }
			17% { -webkit-transform: rotateZ(22deg); }
			19% { -webkit-transform: rotateZ(-20deg); }
			21% { -webkit-transform: rotateZ(18deg); }
			23% { -webkit-transform: rotateZ(-16deg); }
			25% { -webkit-transform: rotateZ(14deg); }
			27% { -webkit-transform: rotateZ(-12deg); }
			29% { -webkit-transform: rotateZ(10deg); }
			31% { -webkit-transform: rotateZ(-8deg); }
			33% { -webkit-transform: rotateZ(6deg); }
			35% { -webkit-transform: rotateZ(-4deg); }
			37% { -webkit-transform: rotateZ(2deg); }
			39% { -webkit-transform: rotateZ(-1deg); }
			41% { -webkit-transform: rotateZ(1deg); }

			43% { -webkit-transform: rotateZ(0); }
			100% { -webkit-transform: rotateZ(0); }
		}

		@-moz-keyframes ring {
			0% { -moz-transform: rotate(0); }
			1% { -moz-transform: rotate(30deg); }
			3% { -moz-transform: rotate(-28deg); }
			5% { -moz-transform: rotate(34deg); }
			7% { -moz-transform: rotate(-32deg); }
			9% { -moz-transform: rotate(30deg); }
			11% { -moz-transform: rotate(-28deg); }
			13% { -moz-transform: rotate(26deg); }
			15% { -moz-transform: rotate(-24deg); }
			17% { -moz-transform: rotate(22deg); }
			19% { -moz-transform: rotate(-20deg); }
			21% { -moz-transform: rotate(18deg); }
			23% { -moz-transform: rotate(-16deg); }
			25% { -moz-transform: rotate(14deg); }
			27% { -moz-transform: rotate(-12deg); }
			29% { -moz-transform: rotate(10deg); }
			31% { -moz-transform: rotate(-8deg); }
			33% { -moz-transform: rotate(6deg); }
			35% { -moz-transform: rotate(-4deg); }
			37% { -moz-transform: rotate(2deg); }
			39% { -moz-transform: rotate(-1deg); }
			41% { -moz-transform: rotate(1deg); }

			43% { -moz-transform: rotate(0); }
			100% { -moz-transform: rotate(0); }
		}

		@keyframes ring {
			0% { transform: rotate(0); }
			1% { transform: rotate(30deg); }
			3% { transform: rotate(-28deg); }
			5% { transform: rotate(34deg); }
			7% { transform: rotate(-32deg); }
			9% { transform: rotate(30deg); }
			11% { transform: rotate(-28deg); }
			13% { transform: rotate(26deg); }
			15% { transform: rotate(-24deg); }
			17% { transform: rotate(22deg); }
			19% { transform: rotate(-20deg); }
			21% { transform: rotate(18deg); }
			23% { transform: rotate(-16deg); }
			25% { transform: rotate(14deg); }
			27% { transform: rotate(-12deg); }
			29% { transform: rotate(10deg); }
			31% { transform: rotate(-8deg); }
			33% { transform: rotate(6deg); }
			35% { transform: rotate(-4deg); }
			37% { transform: rotate(2deg); }
			39% { transform: rotate(-1deg); }
			41% { transform: rotate(1deg); }

			43% { transform: rotate(0); }
			100% { transform: rotate(0); }
		}

    .btn-circle {
      width: 45px;
      height: 45px;
      line-height: 45px;
      text-align: center;
      padding: 0;
      border-radius: 50%;
    }

		.card-body .rotate {
			z-index: 8;
			float: right;
			height: 100%;
		}

		.no-overflow{
			overflow: hidden;
		}

		.dataTables_length{
    	margin-left: 15px;
		}
		.dataTables_length label{
    	white-space: nowrap!important;
		}
		table.dataTable thead th, table.dataTable thead td {
			padding: 10px 18px;
			border-bottom: 1px solid #868686 !important;
		}

		.btn-xs{
			font-size: 12px !important;
		}
		.card-body .rotate i {
			color: rgba(20, 20, 20, 0.15);
			position: absolute;
			left: 0;
			left: auto;
			right: -10px;
			bottom: 0;
			display: block;
			-webkit-transform: rotate(-44deg);
			-moz-transform: rotate(-44deg);
			-o-transform: rotate(-44deg);
			-ms-transform: rotate(-44deg);
			transform: rotate(-44deg);
		}

    .btn-circle i {
      position: relative;
      top: -1px;
    }

    .btn-circle-sm {
      width: 35px;
      height: 35px;
      line-height: 35px;
      font-size: 0.9rem;
    }

    .btn-circle-lg {
      width: 55px;
      height: 55px;
      line-height: 55px;
      font-size: 1.1rem;
    }

    .btn-circle-xl {
      width: 70px;
      height: 70px;
      line-height: 70px;
      font-size: 1.3rem;
    }

    body {
				padding-top: 56px;
				font-size: 0.8rem!important;
		}

		.sticky-offset {
				top: 56px;
		}

		#body-row {
				margin-left:0;
				margin-right:0;
		}
		#sidebar-container {
				min-height: 100vh;
				background-color: #333;
				padding: 0;
		}

		/* Sidebar sizes when expanded and expanded */
		.sidebar-expanded {
				width: 230px;
		}
		.sidebar-collapsed {
				width: 60px !important;
		}

		/* Menu item*/
		#sidebar-container .list-group a {
				height: 50px;
				color: white;
		}

		/* Submenu item*/
		#sidebar-container .list-group .sidebar-submenu a {
				height: 45px;
				padding-left: 30px;
		}
		.sidebar-submenu {
				font-size: 0.78rem;
		}
		.form-control {
    	font-size: 0.8rem!important;
		}

		/* Separators */
		.sidebar-separator-title {
				background-color: #333;
				height: 35px;
		}
		.sidebar-separator {
				background-color: #333;
				height: 25px;
		}
		.logo-separator {
				background-color: #333;
				height: 60px;
		}

		/* Closed submenu icon */
		#sidebar-container .list-group .list-group-item[aria-expanded="false"] .submenu-icon::after {
			content: "\2193";
			/* font-family: 'Font Awesome 5 Free'; */
			display: inline;
			text-align: right;
			padding-left: 10px;
		}
		/* Opened submenu icon */
		#sidebar-container .list-group .list-group-item[aria-expanded="true"] .submenu-icon::after {
			content: "\2191";
			/* font-family: 'Font Awesome 5 Free'; */
			display: inline;
			text-align: right;
			padding-left: 10px;
		}
    /*\
    * Restore Bootstrap 3 "hidden" utility classes.
    \*/

    /* Breakpoint XS */
    @media (max-width: 575px)
    {
      .hidden-xs-down, .hidden-sm-down, .hidden-md-down, .hidden-lg-down, .hidden-xl-down,
      .hidden-xs-up,
      .hidden-unless-sm, .hidden-unless-md, .hidden-unless-lg, .hidden-unless-xl
      {
        display: none !important;
      }
    }

    /* Breakpoint SM */
    @media (min-width: 576px) and (max-width: 767px)
    {
      .hidden-sm-down, .hidden-md-down, .hidden-lg-down, .hidden-xl-down,
      .hidden-xs-up, .hidden-sm-up,
      .hidden-unless-xs, .hidden-unless-md, .hidden-unless-lg, .hidden-unless-xl
      {
        display: none !important;
      }
    }

    /* Breakpoint MD */
    @media (min-width: 768px) and (max-width: 991px)
    {
      .hidden-md-down, .hidden-lg-down, .hidden-xl-down,
      .hidden-xs-up, .hidden-sm-up, .hidden-md-up,
      .hidden-unless-xs, .hidden-unless-sm, .hidden-unless-lg, .hidden-unless-xl
      {
        display: none !important;
      }
    }

    /* Breakpoint LG */
    @media (min-width: 992px) and (max-width: 1199px)
    {
      .hidden-lg-down, .hidden-xl-down,
      .hidden-xs-up, .hidden-sm-up, .hidden-md-up, .hidden-lg-up,
      .hidden-unless-xs, .hidden-unless-sm, .hidden-unless-md, .hidden-unless-xl
      {
        display: none !important;
      }
    }

    /* Breakpoint XL */
    @media (min-width: 1200px)
    {
      .hidden-xl-down,
      .hidden-xs-up, .hidden-sm-up, .hidden-md-up, .hidden-lg-up, .hidden-xl-up,
      .hidden-unless-xs, .hidden-unless-sm, .hidden-unless-md, .hidden-unless-lg
      {
        display: none !important;
      }
    }

    .module-name{
      text-decoration: none !important;
      color: #545454 !important;
      font-size: 24px !important;
      font-weight: 400 !important;
    }

    .table-image{
      height: 50px;
      padding: 4px;
    }

    .my-small-text{
      font-size: 13px !important;
    }

		.no-border-tab{
			border: none !important;
			background-color: none !important;
		}
		.no-border-tab.active{
			border: 1px solid rgba(0,0,0,0.08) !important;
			border-bottom-color: #fff !important;
			background-image: linear-gradient(rgba(0,0,0,0.06), rgba(0,0,0,0.0)) !important;
		}

		.small-badge{
			padding:2px 6px;
			font-size: 85% !important;
			border-radius: 5% 50%;
			font-weight: 600;
		}

		.my-tab{
			float: left;
			cursor: pointer;
			font-size: 14px;
			margin: 2px 3px;
			padding: 5px 8px;
			color: #363636;
			/* background: radial-gradient(closest-side, #eeeeee, #f0f0f0, #fff); */
		}

		.my-tab-headers{
			padding: 5px 2px;
			border-bottom: 1px solid #e7e7e7;
		}

		.my-tab.selected{
			font-size: 13px;
			font-weight: 600;
			color: #4b4b4b;
			border:1px solid rgb(197, 205, 207);
			padding: 4px 15px 0px;
			border-radius: 15px;
			box-shadow: 0px 0px 35px rgb(211, 219, 221) inset;
			/* background: radial-gradient(closest-side, #c5dee7, #cbdbe0, #fff); */
		}

    table td{
      vertical-align: middle !important;
    }

		.bg-orange{
			color: #fff;
			background-color:rgb(255, 60, 0);
		}

		.bg-red{
			color: #fff;
			background-color: rgb(214, 3, 3);
		}

		.bg-green{
			color: #fff;
			background-color: rgb(36, 155, 0);
		}

		.has-floating-badge{
			position: relative;
		}

		.floating-badge{
			font-size: 11.5px;
			position: absolute;
			top: 0px; left: 100%;
			z-index: 10;
			border-radius: 10px;
			padding: 0px 4px;
			background-color: rgb(196, 95, 0);
			color: #fff;
			font-weight: 600;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.05);
		}

		.table-seperated{
			border-collapse:separate;
		}
		.table-seperated td, th{
			white-space:nowrap !important;
			margin: 0px !important;
		}

		.fixed-column{
			position:absolute;
			width:5em;
			left:0;
			top:auto;/*only relevant for first row*/
			margin-top:-3px; /*compensate for top border*/
		}

		.floating-sidebar{
			position: fixed;
			width: 300px;
			left: 0px;
			bottom: 0px;
			top: 66px;
			z-index: 5;
		}

		.hidden{
			display: none !important;
		}

		.sidebar-module-div, .copyright-lims, #sidebar-container{
			background-color: #2a2a2a !important;
		}
  </style>

  @if(isset($dataTable))
	{{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.css"/> --}}
	<link rel="stylesheet" href="/assets/css/datatable/datatable.min.css">

	{{-- <link href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css" rel="stylesheet"> --}}
	<link rel="stylesheet" href="/assets/css/datatable/button.datatable1.2.4.min.css">

  @endif
  @if(isset($select2))
	<link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
  @endif
  @if(isset($datePicker))
    {{-- <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.css" /> --}}
	<link rel="stylesheet" href="/assets/css/bootstrap-datepicker/bootstrap-datepicker.min.css">

	@endif
	<?php

use Illuminate\Support\Facades\Auth;

$thePath = request()->path();
		$PageAttachments = getPageAttachments($thePath);
		$current = Auth::user()->id;
	?>
</head>
<body>
	<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top" id="main-app-header">
		<div class="container-fluid">
			<button class="btn btn-transparent text-primary btn-lg" id="toggle-main-sidebar" style="margin-left: -10px; margin-right: 5px">
				<i class="mdi mdi-menu"></i>
			</button>
			<a class="navbar-brand" href="{{ url('/home') }}">
			<?php $active_company = getActiveCompany()?>
				<img src="{{$active_company->logo ?? ''}}" style="height: 40px" />
			</a>
			<form method="post" action="{{ route('search-sample-code') }}" class="float-right text-info ml-5m">
				@csrf
				<div class="input-group">
					<input type="text" name="sample" style="border:0px solid;border-bottom:1px solid" class="form-control" placeholder="Search by Sample Code">
					<div class="input-group-append">
					<button class="btn btn-default btn-sm" type="submit ">
						<i class="fa fa-search"></i>
					</button>
					</div>
				</div>
			</form>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<!-- Left Side Of Navbar -->
				<ul class="navbar-nav mr-auto">

				</ul>
				<ul class="nav navbar-nav navbar-center">
					@yield('module-name')
				</ul>
				<!-- Right Side Of Navbar -->
				<ul class="navbar-nav ml-auto">
					<!-- Authentication Links -->
					@guest
						<li class="nav-item">
							<a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
						</li>
						@if (Route::has('register'))
							<li class="nav-item">
								<a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
							</li>
						@endif
					@else
						@yield('alerts')
						<li class="nav-item dropdown">
							<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
								{{ Auth::user()->name }} <span class="caret"></span>
							</a>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 230px">
								@if(Auth::user()->is_client == 0 && Auth::user()->supplier_id == 0)
								<a class="dropdown-item" href="{{route('user_profile')}}"><i class="mdi mdi-account-details text-primary"></i> &nbsp;&nbsp;My Profile</a>
								@endif
								@if(isset(Auth::user()->company_id) && Auth::user()->company_id == 0)
								<a class="dropdown-item" href="#" data-target="#select-default-company" data-toggle="modal"><i class="mdi mdi-domain text-info"></i> &nbsp;&nbsp;Select Default Company</a>
								@endif
								<a class="dropdown-item" href="http://127.0.0.1:8000/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
									<i class="text-danger mdi mdi-power"></i> &nbsp;&nbsp;Sign-Out
								</a>
								<form id="logout-form" action="{{ route('mylogout') }}" method="POST" style="display: none;">
									@csrf
								</form>
							</div>
						</li>
						<li class="nav-item">
							<span data-target="#attachments-on-this-page-modal" data-toggle="modal" class="nav-link" href="#page-attachments" style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
								<b class="has-floating-badge">
									<i class="mdi mdi-paperclip fa-1x"></i>
									@if ($PageAttachments->count() > 0)
										<small class="floating-badge">{{ $PageAttachments->count() }}</small>
									@endif
								</b>
							</span>
						</li>
						@if(Auth::user()->is_client == 0)
						<li class="nav-item">
							<a class="nav-link" href="{{route('full-calendar')}}" style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
								<i class="mdi mdi-calendar text-primary"></i>
							</a>
						</li>
						<li class="nav-item">
							<span data-target="#chat-system" data-toggle="modal" class="nav-link" href="#chat-system" style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
								<b class="has-floating-badge">
									<i class="mdi mdi-chat fa-1x text-success"></i>
										<?php
										$chat_count = getUserChats();
										?>
										<small class="floating-badge">{{$chat_count->count()}}</small>

								</b>
							</span>
						</li>
						@endif

					@endguest
				</ul>
			</div>
		</div>
	</nav>
	@yield('content')
	<div class="modal fade" id="chat-system" role="dialog">
		<div class="modal-dialog modal-xl" style="height: 100vh;">
			<div class="modal-content">
				<div class="row no-gutter">
					<div class="col-sm-4 col-xl-4 col-md-4 pr-0">
						<div class="card p-0" style="border: 0px;">
							<div class="card-header bg-dark">
								<h4 class="card-title">
									<i class="mdi mdi-message-bulleted" style="font-size: 15px;font-weight:600;color:turquoise"> Imara System</i>
									@if(Auth::user()->photo == '')
									<img src="/images/l.jpeg" class="float-right" style="border-radius: 50%; height:50px;width:50px" alt="">
									@else
									<img src="{{Auth::user()->photo}}" class="float-right" style="border-radius: 50%; height:50px;width:50px" alt="">
									@endif
								</h4>
							</div>
							<?php
							$users = getCompanyUsers();
							?>
							<div class="card-body bg-default ">
								<div class="table-responsive p-0">
									<table class="table table-condensed my-small-text table-hover table-sm">
										<tbody>
											@foreach($users as $user)
											<tr>
												<td class="user-chat" id="{{$user}}">
													@if($user->photo == '')
													<img src="/images/l.jpeg" style="border-radius: 50%;height:40px;width:40px" alt="">
													@else
													<img src="{{$user->photo}}" style="border-radius: 50%;height:40px;width:40px" alt="">
													@endif
													{{$user->name}}
													<span class="text-small float-right mr-3 has-floating-badge">{!! $user->is_online == 1 ? '<i class="mdi mdi-circle-medium text-success"></i>' : '' !!}
													@if($user->chats > 0)
													<small id="user-chat-count-{{$user->id}}" class="floating-badge" style="background-color: turquoise;">{{$user->chats}}</small>
													@endif
												</span>
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-8 col-xl-8 col-md-8 pl-0">
						<div class="card" style="height: 100vh;">
							<div class="card-header" style="background-color: white; height:74px">
							<h4 class="card-title" id="card-title"></h4>
							</div>
							<div class="card-body bg-light" id="messages">

							</div>
							<div class="card-footer p-0" style="background-color: white;">
								<form id="message-form">
									<div class="row no-gutter">
										<div class="col-sm-11 col-md-11 col-xl-11">
											<input type="hidden" name="to_user_id" id="to-user-id" value="">
											<div class="form-group">
												<textarea name="chat" id="chat" rows="5" class="form-control" placeholder="Type here..." required /></textarea>
											</div>
										</div>
										<div class="col-sm-1 col-md-1 col-xl-1">
											<center>

												<span class="btn btn-default text-primary mt-5 pr-3" style="font-size: 25px;" id="message-save">
												<i class="mdi mdi-send"></i>
												</span>
											</center>

										</div>
									</div>


								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="attachments-on-this-page-modal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-paperclip"></i> Attachments
					</h4>
				</div>
				<div class="modal-body">
					<ul class="nav nav-tabs card-header-tabs border-bottom" role="tablist">
						<li class="nav-item">
							<a data-toggle="tab" href="#modal-available-attachments" class="nav-link active no-border-tab">
								<i class="mdi mdi-paperclip"></i> Available Attachments
								<small class="badge bg-white">{{ $PageAttachments->count() }}</small>
							</a>
						</li>
						<li class="nav-item">
							<a data-toggle="tab" href="#modal-add-attachment-form" class="nav-link no-border-tab">
								<i class="mdi mdi-plus"></i> Add Attachment
							</a>
						</li>
					</ul>
					<div class="tab-content" id="analyte-tabs-content">
						<div class="tab-pane fade pt-3 show active" id="modal-available-attachments" role="tabpanel" aria-labelledby="one-tab">
							<table class="table table-sm mt-3 table-condensed table-banded table-hover table-borderless">
								@foreach ($PageAttachments as $doc)
							<tr data-href="{{ $doc->file }}" data-toggle="tooltip" title="{{ $doc->description }}" class="download-the-document" style="cursor: pointer">
										<td class="p-2 text-primary"><i class="mdi mdi-download"></i>
											<small class="text-muted">
												@if (intval($doc->size) > 1000)
													{{ number_format(intval($doc->size)/1000, 2) }} KB
												@elseif(intval($doc->size) > 1000000)
													{{ number_format(intval($doc->size)/1000000, 2) }} MB
												@else
													{{ $doc->size }} bytes
												@endif
											</small>
										</td>
										<td class="p-2">{{ $doc->title }}</td>
										<td class="p-2">{{ $doc->mime }}</td>
										<td class="p-2" style="width: 25px">
											<form action="{{ route('remove-page-attachment', ['docID' => $doc->id]) }}" method="POST">
												@csrf
												<button class="btn-sm btn btn-transparent text-danger">
													<i class="mdi mdi-delete"></i>
												</button>
											</form>
										</td>
									</tr>
								@endforeach
							</table>
						</div>
						<div class="tab-pane fade p-3" id="modal-add-attachment-form" role="tabpanel" aria-labelledby="one-tab">
							<form id="add-attachment-modal-form" class="mt-2" action="{{route('add-page-attachment') }}" method="POST" enctype="multipart/form-data">
								@csrf
								<fieldset>
									<legend>Attachment Details</legend>
									<div class="form-group">
										<label class="control-label">Title</label>
										<input type="text" name="title" class="form-control" placeholder="Attachment Title..." required />
									</div>
									<div class="form-group">
										<label class="control-label">Description</label>
										<textarea name="description" class="form-control" placeholder="Attachment Description..." required></textarea>
									</div>
									<div class="form-group">
										<input type="hidden" name="url" value="{{ request()->path() }}" />
										<label class="control-label">Attachment</label>
										<input type="file" name="attachment" class="form-control" required />
									</div>
									<div class="form-group">
										<button type="submit" class="btn btn-primary btn-block">
											<i class="mdi mdi-content-save"></i> Save Attachment
										</button>
									</div>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</body>
<script src="/assets/js/libs/jquery/jquery-3.5.1.min.js"></script>
<script src="/assets/js/libs/jquery/popper.min.js"></script>
<script src="/assets/js/libs/bootstrap/bootstrap-4.4.1.min.js"></script>



{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script> --}}
{{-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script> --}}
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,600,700&display=swap" rel="stylesheet" type="text/css">
<style>
  html, body{
    font-family:'Roboto', sans-serif !important;
    background-color: #f3f3f3 !important;
  }
</style>
@if(isset($dataTable))
  <script src="/assets/js/libs/DataTables/jquery.dataTables.min.js"></script>
  <script src="/assets/js/libs/DataTables/data.datatables.min.js"></script>
  <script src="/assets/js/libs/DataTables/datatable.buttons.min.js"></script>
  <script src="/assets/js/libs/DataTables/button.flash.min.js"></script>
  <script src="/assets/js/libs/DataTables/jszip.min.js"></script>
  <script src="/assets/js/libs/DataTables/pdfmake.min.js"></script>
  <script src="/assets/js/libs/DataTables/vsf_fonts.min.js"></script>
  <script src="/assets/js/libs/DataTables/buttons.html5.min.js"></script>
  <script src="/assets/js/libs/DataTables/buttons.print.min.js"></script>


  {{-- <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script> --}}
@endif
@if(isset($select2))
  <script src="/select2/select2.min.js"></script>
@endif
@if(isset($datePicker))
  {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script> --}}
<script src="/assets/js/libs/bootstrap-datepicker/datepicker1.9.0.min.js"></script>
{{-- <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script> --}}
@endif
<script>
	function userChats(item){
		console.log('test2');
	}
	Date.prototype.today = function () {
    return ((this.getDate() < 10)?"0":"") + this.getDate() +"/"+(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) +"/"+ this.getFullYear();
	}

	// For the time now
	Date.prototype.timeNow = function () {
			return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
	}
  $(function(){
	  
	  $('#message-save').click(function(event){
		event.preventDefault();
		var to_user_id = $('#to-user-id').val();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{url('/user/chat/add')}}",
			method: 'post',
			data: {
				to_user: to_user_id,
				chat: $('#chat').val(),
			},
			success: function(data){
				console.log(data);
				$('#chat').val('');
				var $chat = $(`
						<div class="card p-2 mb-3 ${data.from_user_id == data.current_user_id ? 'float-right':'float-left'}"  ${data.from_user_id == data.current_user_id ? 'style="background-color:turquoise;width:60%"':''} >
							<h5 style="font-size:12px;font-weight:600">${data.from_user_id == data.current_user_id ? 'You:~': data.from_user_name}
							<small class="float-right"><i>${data.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${data.message}</p>
						</div>
					`);

					$('#messages').append($chat);

			},
			error: function(data){
				console.log(data);
			}
		});


	  });
	  $('.user-chat').click(function(event){
		var row = $(this).parent('td');
		var user = JSON.parse(this.id);


		$('#to-user-id').val(user.id);
		var $header = $(`
						${user.photo == null ? '<img src="/images/l.jpeg" style="border-radius: 50%;height:50px;width:50px" alt="">':'<img src='+user.photo+'alt="" style="border-radius:50%;height:50px;width:50px;">'}

						<span style="color:turqoise">${user.name}</span>
					`);
		$('#card-title').empty();
		$('#card-title').append($header);
		var count_id = '#user-chat-count-'+user.id;
		$(count_id).empty();
		$.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
		$.ajax({
			url: "{{url('/user/chat/view')}}",
			method: 'post',
			data:{
				to_user_id : user.id,

			},
			success: function(data){
				console.log(data);
				$('#messages').empty();
				if(data.length > 1){

				$.each(data,function(){
					var $chat = $(`
						<div class="card p-2 mb-3 ${this.from_user_id == this.current_user_id ? 'float-right':'float-left'}"  ${this.from_user_id == this.current_user_id ? 'style="background-color:turquoise;width:60%"':'style="width:60%"'} >
							<h5 style="font-size:12px;font-weight:600">${this.from_user_id == this.current_user_id ? 'You:~': this.from_user_name}
							<small class="float-right"><i>${this.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${this.message}</p>
						</div>
					`);

					$('#messages').append($chat);
				})
				}if(data.length == 1){
					var $chat = $(`
						<div class="card p-2 mb-3 ${data.from_user_id == data.current_user_id ? 'float-right':'float-left'}"  ${data.from_user_id == data.current_user_id ? 'style="background-color:turquoise;width:60%"':'style="width:60%"'} >
							<h5 style="font-size:12px;font-weight:600">${data.from_user_id == data.current_user_id ? 'You:~': data.from_user_name}
							<small class="float-right"><i>${data.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${data.message}</p>
						</div>
					`);

					$('#messages').append($chat);
				}
			},
			error: function(data){
				console.log(data);
			}

		});

	  })
		$('.add-attachment-modal-form-btn').on('click', function(){
			$('#add-attachment-modal-form').submit();
		});

		if($(window).width() < 760){
			$('#sidebar-container').addClass('hidden');
		}

		$('#toggle-main-sidebar').on('click', function(){
			$('#sidebar-container').toggleClass('hidden');
			if($('#sidebar-container').hasClass('hidden')){
				$('#sidebar-container').removeClass('col-8 col-sm-4 col-md-3 col-lg-2').removeClass('floating-sidebar');
				$('#main-container-body').removeClass('col-4 col-sm-8 col-md-9 col-lg-10').addClass('col-12')
			}
			else{
				if($(window).width() < 760){
					// $('#main-container-body').addClass('col-4');
					// $('#sidebar-container').addClass('col-8').removeClass('d-none');
					$('#sidebar-container').addClass('floating-sidebar').removeClass('d-none');
				}
				else{
					$('#sidebar-container').addClass('col-sm-4 col-md-3 col-lg-2');
					$('#main-container-body').addClass('col-sm-8 col-md-9 col-lg-10').removeClass('col-12');
				}
			}
		})

		$('.download-the-document').on('click', function(){
			var href = $(this).data('href');
			console.log(href);
			var a = $(`<a href="${href}" target="_blank">Download</a>`);

			$(this).parents('.modal').append(a);
			a[0].click();
			a.remove();
		});

		$('[data-toggle="tooltip"]').tooltip();

		//Page Configuration Code
		$('#body-row .collapse').collapse('hide');

		// Collapse/Expand icon
		$('#collapse-icon').addClass('fa-angle-double-left');

		// Collapse click
		$('[data-toggle=sidebar-colapse]').click(function() {
			SidebarCollapse();
		});

		function SidebarCollapse () {
			$('.menu-collapsed').toggleClass('d-none');
			$('.sidebar-submenu').toggleClass('d-none');
			$('.submenu-icon').toggleClass('d-none');
			$('#sidebar-container').toggleClass('sidebar-expanded sidebar-collapsed');

			// Treating d-flex/d-none on separators with title
			var SeparatorTitle = $('.sidebar-separator-title');
			if ( SeparatorTitle.hasClass('d-flex') ) {
				SeparatorTitle.removeClass('d-flex');
			} else {
				SeparatorTitle.addClass('d-flex');
			}

			// Collapse/Expand icon
			$('#collapse-icon').toggleClass('fa-angle-double-left fa-angle-double-right');
		}
		//Page Configuration Code End


    $('.modal').appendTo("body");

    if($(window).width() < 1201){
      $("#main-sidebar").addClass('close');
    }

    @if(isset($select2))
      $('select').not('.hidden').each(function(i, e){
				if(!$(e).hasClass('no-select2')){
					$(e).select2({
						placeHolder: $(e).attr('placeholder') || $(e).data('placeholder')
					});

					$(e).attr('style', 'width: 100%');
				}
      })
		@endif

		@if(isset($datePicker))
			$('.datepicker').each(function(){
				var dF = $(this);
				var hasMax = $.trim($(this).attr('max')) == "" ? "0" : "";
				dF.datepicker({
					clearBtn: true,
					maxDate: $.now(),
					format: "yyyy-mm-dd"
				});
			});
		@endif

		@if (\Session::has('success') || \Session::has('error'))
			setTimeout(()=>{
				$('#message-section').slideUp(600);
			}, 10000);
		@endif

    $('#main-body-content').on('click', '#main-sidebar-toggler', function(){
      $("#main-sidebar").toggleClass('close');
    });

    if($('#main-wrapper').length > 0){
      $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
      });
    }

    @if(isset($dataTable))
      $('.table-responsive .table.table-condensed.table-sm').not('.server-side').each(function(i,e){
        var lengthMenu = $(e).data('menutext') ?? [ 10, 25, 50, 75, 100 ];
		var pageTitle = $(document).find('title').text();
        var fileName = $(e).data('filename') ?? pageTitle;

		fileName += '-D{{getRandomHex()}}';

        buttonConfigs = ['copy', {extend:'csv', filename: fileName}, {extend: 'excelHtml5',footer: true, filename: fileName}, {extend: 'pdf', filename: fileName}, 'print'];

        var fixedCols = $(e).data('fixedcls');
        var $fCOps = {
          dom: 'Blfrtip',
          buttons: buttonConfigs,
          "order": [],
          "language": {
            // "lengthMenu": lengthMenu,
            "search": '<i class="fa fa-search"></i>',
            "paginate": {
              "previous": '<i class="fa fa-angle-left"></i>',
              "next": '<i class="fa fa-angle-right"></i>'
            }
          }
        };
        if(fixedCols == "true" || fixedCols == true){
        //   $fCOps['scrollY'] = 200;
        //   $fCOps['scrollX'] = true;
        //   $fCOps['scrollCollapse'] = true;
        //   $fCOps['scroller'] = true;
        //   $fCOps['fixedColumns'] = {
        //    left: 2
        //   }
          // console.log($fCOps);
        }
        $(e).DataTable($fCOps);
      });
    @endif

    // if($("#main-sidebar").length > 0){
    //   $('#main-body-content').append(`<button id="main-sidebar-toggler" class="btn btn-circle btn-circle-sm btn-danger"><i class="mdi mdi-menu"></i></button>`);
    // }

  });
</script>
@yield('script')
@if(isset(Auth::user()->company_id) && Auth::user()->company_id == 0)
  <div id="select-default-company" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('set-default-company') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-domain"></i> View System As:</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Select Company</label>
            <select class="form-control" name="company_id" required>
              <option value="0">As Administrator</option>
              @foreach (getCompanies() as $company)
                <option value="{{ $company->id }}">{{ $company->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endif
</html>
