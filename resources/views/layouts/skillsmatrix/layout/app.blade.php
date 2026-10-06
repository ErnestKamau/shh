@extends('layouts.app', ['dataTable' => $dataTable ?? false, 'select2' => $select2 ?? false])

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('matrix.dashboard') }}"><i class="mdi mdi-account-star-outline"></i> Skills Matrix</a>
</li>
@endsection

@section('title')
@yield('title2')
@endsection

@section('content')
<div class="row" id="body-row">
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block col-sm-3 col-lg-2">
		@php
			$user = auth()->user();
			$canDashboard = $user->can('matrix.module.access');
			$canSkillsMatrix = $user->can('skills-matrix.components.skills-matrix.view');
			$canCapability = $user->can('skills-matrix.components.capability.view');
			$canTrainingNeeds = $user->can('skills-matrix.components.training-needs.view');
			$canTrainingPlan = $user->can('skills-matrix.components.training-plan.view');
			$canModulePreconfigs = $user->can('skills-matrix.components.module-preconfigs.view');
			$canEvalApprove = $user->can('skills-matrix.components.training-plan.evaluation.approve');
		@endphp
		<ul class="list-group">
			<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-account-star-outline fa-3x"></i><br>
				<span class="text-lg text-bold">Skills Matrix</span>
			</div>

			@if($canDashboard)
			<a href="{{ route('matrix.dashboard') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('matrix.dashboard') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			@endif

			@if($canSkillsMatrix)
			<a href="{{ route('matrix') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('matrix') && !request()->routeIs('matrix.show') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-star-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Skills Matrix</span>
				</div>
			</a>
			@endif

			@if($canCapability)
			<a href="{{ route('capability-index') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('capability-index', 'capability.show') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-check-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Capability Matrix</span>
				</div>
			</a>
			<a href="{{ route('matrix.staff') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('matrix.staff*') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-circle fa-fw mr-3"></span>
					<span class="menu-collapsed">Staff Profiles</span>
				</div>
			</a>
			@endif

			@if($canSkillsMatrix)
			<a href="{{ route('matrix.education') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('matrix.education') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-certificate fa-fw mr-3"></span>
					<span class="menu-collapsed">Education Requirements</span>
				</div>
			</a>
			@endif

			@if($canTrainingNeeds || $canTrainingPlan)
			<a href="#training-menu" data-toggle="collapse" aria-expanded="{{ request()->routeIs('train.needs.*', 'train.plan.*', 'matrix.evaluations') ? 'true' : 'false' }}"
				class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-school mr-3"></span>
					<span class="menu-collapsed">Training</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="training-menu" class="collapse {{ request()->routeIs('train.needs.*', 'train.plan.*', 'matrix.evaluations') ? 'show' : '' }} sidebar-submenu">
				@if($canTrainingNeeds)
				<a href="{{ route('train.needs.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Training Needs</span>
				</a>
				@endif
				@if($canTrainingPlan)
				<a href="{{ route('train.plan.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Training Plan</span>
				</a>
				@endif
				@if($canEvalApprove)
				<a href="{{ route('matrix.evaluations') }}" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Evaluation Approvals</span>
				</a>
				@endif
			</div>
			@endif

			@if($canModulePreconfigs)
			<a href="#skills-confflow-menu" data-toggle="collapse" aria-expanded="{{ request()->routeIs('module-skills-pre-configs') ? 'true' : 'false' }}"
				class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cog mr-3"></span>
					<span class="menu-collapsed">Configurations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="skills-confflow-menu" class="collapse {{ request()->routeIs('module-skills-pre-configs') ? 'show' : '' }} sidebar-submenu">
				@foreach (['Proficiency', 'Training', 'Roles', 'Competence', 'Competence Type', 'Competence Description'] as $item)
					<a href="{{ route('module-skills-pre-configs', ['config' => $item, 'module' => 'Skills-Matrix']) }}"
						class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>
							@if ($item === 'Proficiency') Skills Proficiency
							@elseif ($item === 'Training') Training Proficiency
							@elseif ($item === 'Competence') Area of Competence
							@elseif ($item === 'Roles') Job Descriptions
							@else {{ $item }}
							@endif
						</span>
					</a>
				@endforeach
			</div>
			@endif

			@if($canSkillsMatrix)
			<a href="{{ route('matrix.reports') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('matrix.reports') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-multiple-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Reports</span>
				</div>
			</a>
			@endif

			<div class="list-group-item copyright-lims p-4 text-center" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
	</div>

	<div class="col-sm-8 col-lg-10 py-3" id="main-container-body">
		@include('layouts.registry.partials.rm-act-btn-styles')
		<div id="message-section" style="padding: 10px 10px 0px 10px !important">
			@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			@if (session('success'))
				<div class="alert alert-success">{{ session('success') }}</div>
			@endif
			@if (session('error'))
				<div class="alert alert-danger">{{ session('error') }}</div>
			@endif
			@if (session('warning'))
				<div class="alert alert-warning">{{ session('warning') }}</div>
			@endif
		</div>
		@yield('content2')
	</div>
</div>
@endsection

@section('script')
@yield('script2')
@endsection
