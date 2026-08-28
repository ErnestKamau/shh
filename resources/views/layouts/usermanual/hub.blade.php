@extends('layouts.usermanual.layout.app')

@section('title2')
	<title>User Manual</title>
@endsection

@section('content2')
<main class="um-page container-fluid">
	@include('layouts.usermanual.partials.styles')
	<?php
		$items = [
			['link' => route('home'), 'name' => 'Home', 'icon' => null],
			['link' => route('usermanual.index'), 'name' => 'User Manual', 'icon' => null],
		];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="um-main">
		<div class="um-hero">
			<div>
				<p class="um-hero__kicker">IMARA LIMS handbook</p>
				<h1>User Manual</h1>
				<p>
					Plain-language guides for quotations, inventory, receiving samples,
					direct registration, and the request view.
					Pick a manual from the sidebar or open a card below.
				</p>
			</div>
			<div class="um-hero__lottie" data-um-lottie="https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json" aria-hidden="true"></div>
		</div>

		<div class="um-content">
			<h2>How to use this guide</h2>
			<p>
				Each manual is split into short chapters. Open a chapter from the sidebar,
				read the steps, and look at the screenshots so you can match what you see
				on screen. Tips call out common “gotchas” in everyday lab language.
			</p>
			<div class="um-tip">
				<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
				<p>You do not need technical knowledge — follow the chapter that matches the task you are doing today.</p>
			</div>

			<div class="um-hub-grid">
				@foreach($manuals as $slug => $manual)
					<a href="{{ route('usermanual.show', ['manual' => $slug]) }}" class="um-hub-card">
						<span class="um-hub-card__icon"><i class="mdi {{ $manual['icon'] }}"></i></span>
						<h3>{{ $manual['title'] }}</h3>
						<p>{{ $manual['subtitle'] }}</p>
					</a>
				@endforeach
			</div>
		</div>
	</div>
</main>
@endsection
