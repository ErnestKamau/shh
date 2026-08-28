@extends('layouts.app')

@section('module-name')
<li class="nav-item d-flex align-items-center">
	<a class="nav-link module-name" href="{{ route('usermanual.index') }}"><i class="mdi mdi-book-open-page-variant"></i> User Manual</a>
</li>
@endsection

@section('title')
<style>
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

	#sidebar-container .sidebar-submenu .list-group-item {
		padding-left: 2rem;
		font-size: 0.9rem;
	}
</style>
@yield('title2')
@endsection

@section('content')
@php
	$manuals = $manuals ?? \App\Support\UserManualRegistry::manuals();
	$activeManual = $activeManual ?? request()->route('manual');
	$activeChapter = $activeChapter ?? request()->route('chapter');

	if ($activeManual && ! $activeChapter) {
		$manualData = \App\Support\UserManualRegistry::find($activeManual);
		$activeChapter = $manualData['chapters'][0]['slug'] ?? null;
	}
@endphp

<div class="row" id="body-row">
	<div id="sidebar-container" class="sidebar-expanded d-none d-lg-block">
		@include('layouts.usermanual.partials.module-sidebar', [
			'manuals' => $manuals,
			'activeManual' => $activeManual,
			'activeChapter' => $activeChapter,
		])
	</div>

	<div class="py-3" id="main-container-body">
		<div id="message-section" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: auto; max-width: 600px;">
			@if ($errors->any())
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<ul class="mb-0">
					@foreach ($errors->all() as $error)
					<li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
					@endforeach
				</ul>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			@endif
		</div>
		@yield('content2')
	</div>
</div>
@endsection

@section('script')
@yield('script2')
@endsection
