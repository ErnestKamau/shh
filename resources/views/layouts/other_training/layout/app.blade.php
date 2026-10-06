@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('customers-list') }}"><i class="mdi mdi-account-group"></i> Skills Matrix</a>
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
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block col-sm-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">Skills Matrix</span>
			</div>
			<!-- Separator with title -->
			{{-- <li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
				<small>MAIN MENU</small>
			</li> --}}
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="#" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-multiple fa-fw mr-3"></span>
					<span class="menu-collapsed">Capability Matrix</span>
				</div>
			</a>
						
			
			<a href="#" class="list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-account fa-fw mr-3"></span>
					<span class="menu-collapsed">Customer Feedback</span>
				</div>
			</a>
			<a href="#matrix-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Matrix</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="matrix-menu" class="collapse sidebar-submenu">
				<a href="{{route('matrix')}}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Skills Matrix
						<small class="float-right badge badge-pill"></small></span>
				</a>				
			</div>

			<a href="#training-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Training</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="training-menu" class="collapse sidebar-submenu">
				<a href="{{route('other-training')}}" class="list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Training Plan
						<small class="float-right badge badge-pill"></small></span>
				</a>				
			</div>

            <a href="#skills-confflow-menu" data-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Configurations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>			
			<div id="skills-confflow-menu" class="collapse sidebar-submenu">
				<?php
					$menuTotals = array("Proficiency","Training", "Roles", "Education","Competence","Competence Type",
                    "Competence Description");
				?>
				@foreach ($menuTotals as $item)
					<a href="{{ route('module-skills-pre-configs', ['config'=>$item, 'module'=>'Skills-Matrix']) }}" class="list-group-item list-group-item-action">
					@if ($item=='Proficiency')
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Skills Proficiency</span>
					@elseif ($item=='Training')
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Training Proficiency</span>		
					@elseif ($item=='Competence')
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Area of Competence</span>					
					@else
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>{{ $item }}</span>
					@endif
					</a>
				@endforeach					
			</div>

			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
			<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-8 col-lg-10 py-3" id="main-container-body">
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