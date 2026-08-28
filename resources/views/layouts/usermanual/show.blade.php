@extends('layouts.usermanual.layout.app')

@section('title2')
	<title>{{ $manual['title'] }} — User Manual</title>
@endsection

@section('content2')
@php
	$chapters = $manual['chapters'];
	$chapterIndex = collect($chapters)->search(fn ($c) => $c['slug'] === $activeChapter);
	$chapterMeta = $chapters[$chapterIndex] ?? $chapters[0];
	$prev = $chapterIndex > 0 ? $chapters[$chapterIndex - 1] : null;
	$next = ($chapterIndex !== false && $chapterIndex < count($chapters) - 1) ? $chapters[$chapterIndex + 1] : null;
@endphp
<main class="um-page container-fluid">
	@include('layouts.usermanual.partials.styles')
	<?php
		$items = [
			['link' => route('home'), 'name' => 'Home', 'icon' => null],
			['link' => route('usermanual.index'), 'name' => 'User Manual', 'icon' => null],
			['link' => route('usermanual.show', ['manual' => $manualSlug]), 'name' => $manual['title'], 'icon' => null],
		];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="um-main">
		<div class="um-hero">
			<div>
				<p class="um-hero__kicker">{{ $manual['title'] }}</p>
				<h1>{{ $chapterMeta['title'] }}</h1>
				<p>{{ $chapterMeta['summary'] }}</p>
			</div>
			<div class="um-hero__lottie" data-um-lottie="{{ $manual['lottie'] }}" aria-hidden="true"></div>
		</div>

		<div class="um-content">
			@include($chapterPartial)
		</div>

		<nav class="um-pager" aria-label="Chapter pager">
			@if($prev)
				<a href="{{ route('usermanual.show', ['manual' => $manualSlug, 'chapter' => $prev['slug']]) }}">
					<i class="mdi mdi-arrow-left"></i> {{ $prev['title'] }}
				</a>
			@else
				<span></span>
			@endif
			@if($next)
				<a href="{{ route('usermanual.show', ['manual' => $manualSlug, 'chapter' => $next['slug']]) }}">
					{{ $next['title'] }} <i class="mdi mdi-arrow-right"></i>
				</a>
			@endif
		</nav>
	</div>
</main>
@endsection
