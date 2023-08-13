<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

  <?php echo $__env->yieldContent('title'); ?>

  <!-- Scripts -->
  <link rel="stylesheet" href="/css/all.min.css" />
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="/css/bootstrap.min.css">
  <script src="/js/fullcalendar.min.js"></script>
  <style type="text/css">
    /* html,body{
      background-color: #2a2a2a;
    } */
		.select2-container .select2-choice, .select2-container .select2-choices{
			margin-left: 4px !important;
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

		@keyframes  ring {
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

	span.select2.select2-container{
		width: 95% !important;
		min-width: 125px;
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
		select{
			width: 100% !important;
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
		.btn-default-h{
			box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px !important;
		}
  </style>

  <?php if(isset($dataTable)): ?>
    <link type="text/css" rel="stylesheet" href="/assets/css/theme-default/libs/DataTables/jquery.dataTables.css?1423553989" />
    <link href="/css/buttons.dataTables.min.css" rel="stylesheet">
		<style type="text/css" rel="stylesheet">
			.table.table-hover tr{
				transition: all 300ms ease-in-out;
				color: inherit;
				font-weight: inherit;
				background-color: inherit;
			}
			.table.table-hover tr:hover{
				background-color: rgb(209, 233, 206)!important;
				cursor: pointer;
				font-weight: 600;
				color: #353535;
				transition: all 300ms ease-in-out;
			}
			.table.table-hover tr:hover td{
				border: none;
			}
		</style>
  <?php endif; ?>
  <?php if(isset($select2)): ?>
	<link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
  <?php endif; ?>
  <?php if(isset($datePicker)): ?>
    <link type="text/css" rel="stylesheet" href="/css/bootstrap-datepicker.css" />
	<?php endif; ?>
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
			<a class="navbar-brand" href="<?php echo e(url('/home')); ?>">
			<?php $active_company = getActiveCompany()?>
				<img src="<?php echo e($active_company->logo); ?>" style="height: 40px" />
			</a>
			
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation')); ?>">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<!-- Left Side Of Navbar -->
				<ul class="navbar-nav mr-auto">

				</ul>
				<ul class="nav navbar-nav navbar-center">
					<?php echo $__env->yieldContent('module-name'); ?>
				</ul>
				<!-- Right Side Of Navbar -->
				<ul class="navbar-nav ml-auto">
					<!-- Authentication Links -->
					<?php if(auth()->guard()->guest()): ?>
						<li class="nav-item">
							<a class="nav-link" href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
						</li>
						<?php if(Route::has('register')): ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo e(route('register')); ?>"><?php echo e(__('Register')); ?></a>
							</li>
						<?php endif; ?>
					<?php else: ?>
						<?php echo $__env->yieldContent('alerts'); ?>
						<li class="nav-item dropdown">
							<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
								<?php echo e(Auth::user()->name); ?> <span class="caret"></span>
							</a>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 230px">
								<?php if(Auth::user()->is_client == 0 && Auth::user()->supplier_id == 0): ?>
								<a class="dropdown-item" href="<?php echo e(route('user_profile')); ?>"><i class="mdi mdi-account-details text-primary"></i> &nbsp;&nbsp;My Profile</a>
								<a class="dropdown-item" data-target="#imara-system-support-form" data-toggle="modal" href="#imara-system-support-form">
									<i class="mdi mdi-alert text-warning"></i> &nbsp;&nbsp;Report An Issue
								</a>
								<?php endif; ?>
								<?php if(isset(Auth::user()->company_id) && Auth::user()->company_id == 0): ?>
								<a class="dropdown-item" href="#" data-target="#select-default-company" data-toggle="modal"><i class="mdi mdi-domain text-info"></i> &nbsp;&nbsp;Select Default Company</a>
								<?php endif; ?>
								<a class="dropdown-item" href="#" data-toggle="modal" data-target="#change-password-modal">
									<i class="text-success mdi mdi-key-change"></i> &nbsp;&nbsp;Change Password
								</a>
								<a class="dropdown-item" href="http://127.0.0.1:8000/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
									<i class="text-danger mdi mdi-power"></i> &nbsp;&nbsp;Sign-Out
								</a>
								<form id="logout-form" action="<?php echo e(route('mylogout')); ?>" method="POST" style="display: none;">
									<?php echo csrf_field(); ?>
								</form>
							</div>
						</li>
						<li class="nav-item">
							<span data-target="#attachments-on-this-page-modal" data-toggle="modal" class="nav-link" href="#page-attachments" style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
								<b class="has-floating-badge">
									<i class="mdi mdi-paperclip fa-1x"></i>
									<?php if($PageAttachments->count() > 0): ?>
										<small class="floating-badge"><?php echo e($PageAttachments->count()); ?></small>
									<?php endif; ?>
								</b>
							</span>
						</li>
						

					<?php endif; ?>
				</ul>
			</div>
		</div>
	</nav>
	<?php echo $__env->yieldContent('content'); ?>
	
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
								<small class="badge bg-white"><?php echo e($PageAttachments->count()); ?></small>
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
								<?php $__currentLoopData = $PageAttachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr data-href="<?php echo e($doc->file); ?>" data-toggle="tooltip" title="<?php echo e($doc->description); ?>" class="download-the-document" style="cursor: pointer">
										<td class="p-2 text-primary"><i class="mdi mdi-download"></i>
											<small class="text-muted">
												<?php if(intval($doc->size) > 1000): ?>
													<?php echo e(number_format(intval($doc->size)/1000, 2)); ?> KB
												<?php elseif(intval($doc->size) > 1000000): ?>
													<?php echo e(number_format(intval($doc->size)/1000000, 2)); ?> MB
												<?php else: ?>
													<?php echo e($doc->size); ?> bytes
												<?php endif; ?>
											</small>
										</td>
										<td class="p-2"><?php echo e($doc->title); ?></td>
										<td class="p-2"><?php echo e($doc->mime); ?></td>
										<td class="p-2" style="width: 25px">
											<form action="<?php echo e(route('remove-page-attachment', ['docID' => $doc->id])); ?>" method="POST">
												<?php echo csrf_field(); ?>
												<button class="btn-sm btn btn-transparent text-danger">
													<i class="mdi mdi-delete"></i>
												</button>
											</form>
										</td>
									</tr>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</table>
						</div>
						<div class="tab-pane fade p-3" id="modal-add-attachment-form" role="tabpanel" aria-labelledby="one-tab">
							<form id="add-attachment-modal-form" class="mt-2" action="<?php echo e(route('add-page-attachment')); ?>" method="POST" enctype="multipart/form-data">
								<?php echo csrf_field(); ?>
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
										<input type="hidden" name="url" value="<?php echo e(request()->path()); ?>" />
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
<script src="/js/jquery.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<link href="/css/roboto.css" rel="stylesheet" type="text/css">

<style>
  html, body{
    font-family:'Roboto', sans-serif !important;
    background-color: #f3f3f3 !important;
  }
</style>
<?php if(isset($dataTable)): ?>
  
  <script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="/js/buttons.flash.min.js"></script>
  <script type="text/javascript" src="/js/jszip.min.js"></script>
  <script type="text/javascript" src="/js/pdfmake.min.js"></script>
  <script type="text/javascript" src="/js/vfs_fonts.js"></script>
  <script type="text/javascript" src="/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="/js/vfs_fonts_2.js"></script>
  <script type="text/javascript" src="/js/buttons.print.min.js"></script>
	<script type="text/javascript" src="/js/datatables-fixed-columns.min.js"></script>
<?php endif; ?>
<?php if(isset($select2)): ?>
  <script src="/select2/select2.min.js"></script>
<?php endif; ?>
<?php if(isset($datePicker)): ?>
  <script src="/js/bootstrap-datepicker.min.js"></script>
<?php endif; ?>
<script>
	function userChats(item){
		console.log('test2');
	}

	function timeConverter(dt){
		var a = new Date(dt);
		var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		var year = a.getFullYear();
		var month = months[a.getMonth()];
		var date = a.getDate();
		var hour = a.getHours();
		var min = a.getMinutes();
		var sec = a.getSeconds();
		var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
		return time;
	}

	Date.prototype.today = function () {
    return ((this.getDate() < 10)?"0":"") + this.getDate() +"/"+(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) +"/"+ this.getFullYear();
	}

	// For the time now
	Date.prototype.timeNow = function () {
			return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
	}
  $(function(){
		$('form').find('[type="submit"]').on('click', function(event){
			event.preventDefault();
			$(this).removeClass('btn-info btn-success btn-primary');
			$(this).addClass('btn-muted');
			$(this).attr('disabled',true);
			$(this).prop('disabled',true);

			$(this).parents('form').submit();
		});
	  $('#message-save').click(function(event){
			event.preventDefault();
			var to_user_id = $('#to-user-id').val();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				url: "<?php echo e(url('/user/chat/add')); ?>",
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


	  })
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
				url: "<?php echo e(url('/user/chat/view')); ?>",
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

	  });

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

		// $('[data-toggle="tooltip"]').tooltip();

		// //Page Configuration Code
		// $('#body-row .collapse').collapse('hide');

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

    <?php if(isset($select2)): ?>
      $('select').not('.hidden').each(function(i, e){
				if(!$(e).hasClass('no-select2')){
					$(e).select2({
						placeHolder: $(e).attr('placeholder') || $(e).data('placeholder')
					});
					$(e).attr('style', 'width: 100%');
				}
      })
		<?php endif; ?>

		<?php if(isset($datePicker)): ?>
			$('.datepicker').each(function(){
				var dF = $(this);
				var hasMax = $.trim($(this).attr('max')) == "" ? "0" : "";
				dF.datepicker({
					clearBtn: true,
					maxDate: $.now(),
					format: "yyyy-mm-dd"
				});
			});
		<?php endif; ?>

		<?php if(\Session::has('success') || \Session::has('error')): ?>
			setTimeout(()=>{
				$('#message-section').slideUp(600);
			}, 10000);
		<?php endif; ?>

    $('#main-body-content').on('click', '#main-sidebar-toggler', function(){
      $("#main-sidebar").toggleClass('close');
    });

    if($('#main-wrapper').length > 0){
      $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
      });
    }
		<?php if(isset($dataTable)): ?>
			$('.table-responsive .table.table-condensed.table-sm').not('.server-side').each(function(i,e){
				var lengthMenu = $(e).data('menutext') ?? [ 10, 25, 50, 75, 100 ];
				var fixedCols = $(e).data('fixedcls');
				var $fCOps = {
					dom: 'Blfrtip',
					buttons: [
						'copy', 'csv', {extend: 'excelHtml5',footer: true}, 'pdf', 'print'
					],
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
					// $fCOps['scrollY'] = 200;
					// $fCOps['scrollX'] = true;
					// $fCOps['scrollCollapse'] = true;
					// $fCOps['scroller'] = true;
					// $fCOps['fixedColumns'] = {
					// 	left: 2
					// }
					// console.log($fCOps);
				}
				$(e).DataTable($fCOps);
    	});
		<?php endif; ?>
    // if($("#main-sidebar").length > 0){
    //   $('#main-body-content').append(`<button id="main-sidebar-toggler" class="btn btn-circle btn-circle-sm btn-danger"><i class="mdi mdi-menu"></i></button>`);
    // }

  });
</script>
<?php echo $__env->yieldContent('script'); ?>
<?php if(isset(Auth::user()->company_id) && Auth::user()->company_id == 0): ?>
  <div id="select-default-company" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('set-default-company')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-domain"></i> View System As:</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Select Company</label>
            <select class="form-control" name="company_id" required>
              <option value="0">As Administrator</option>
              <?php $__currentLoopData = getCompanies(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($company->id); ?>"><?php echo e($company->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endif; ?>
<div id="change-password-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<form action="<?php echo e(route('reset-personnel')); ?>" method="POST" class="modal-content">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h3 class="modal-title"> <i class="mdi mdi-key-change"></i> Reset your Password! </h3>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Password</label>

					<input type="password" id="pass" autocomplete="new-password" placeholder="Type Password..." class="form-control" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" name="password" onchange="form.pwd2.pattern = RegExp.escape(this.value);">
				</div>
				<div class="form-group">
					<label class="control-label">Confirm Password</label>

					<input type="password" id="passcon" autocomplete="new-password" class="form-control" placeholder="Confirm Password..." required onkeyup="activecheck()" name="con_password">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save </button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="imara-system-support-form" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('send-issue-email')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-face-agent"></i> Imara System Support</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-default">
					<i class="mdi mdi-information"></i>
					Use this form to raise Imara System Support Tickets, by providing the information requested below.
				</div>
				<div class="form-group mt-2">
					<label class="control-label">Issue Title</label>
					<input type="text" placeholder="Issue Title..." class="form-control" name="title" required />
				</div>
				<div class="form-group">
					<label class="control-label">Issue Description</label>
					<textarea rows="8" placeholder="Issue Description..." class="form-control" name="description" required></textarea>
				</div>
				<h6><i class="mdi mdi-paperclip"></i>Attach Screenshots</h6>
				<div class="form-group">
					<label class="control-label">Screenshot1</label>
					<input type="file" name="attachment1" class="form-control" />
				</div>
				<div class="form-group">
					<label class="control-label">Screenshot2</label>
					<input type="file" name="attachment2" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-send"></i> SEND</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

</html>
<?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/layouts/master.blade.php ENDPATH**/ ?>