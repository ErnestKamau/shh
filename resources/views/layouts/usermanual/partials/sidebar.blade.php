@php
	/** @var array<string, array> $manuals */
	/** @var string|null $activeManual */
	/** @var string|null $activeChapter */
@endphp
<aside class="um-sidebar" aria-label="User manual navigation">
	<div class="um-sidebar__brand">
		<span class="um-sidebar__brand-icon" aria-hidden="true"><i class="mdi mdi-book-open-variant"></i></span>
		<div>
			<h2>User Manual</h2>
			<p>Browse guides like a handbook</p>
		</div>
	</div>

	<a href="{{ route('usermanual.index') }}" class="um-nav-link {{ $activeManual === null ? 'is-active' : '' }}">
		<span class="um-nav-link__icon"><i class="mdi mdi-home-outline"></i></span>
		<span>
			<p class="um-nav-link__title">All manuals</p>
			<p class="um-nav-link__meta">Start here</p>
		</span>
	</a>

	<div class="um-nav-label">Manuals</div>
	@foreach($manuals as $slug => $manual)
		<a
			href="{{ route('usermanual.show', ['manual' => $slug]) }}"
			class="um-nav-link {{ $activeManual === $slug ? 'is-active' : '' }}"
		>
			<span class="um-nav-link__icon"><i class="mdi {{ $manual['icon'] }}"></i></span>
			<span>
				<p class="um-nav-link__title">{{ $manual['title'] }}</p>
				<p class="um-nav-link__meta">{{ $manual['subtitle'] }}</p>
			</span>
		</a>
		@if($activeManual === $slug)
			<ul class="um-chapter-list">
				@foreach($manual['chapters'] as $chapter)
					<li>
						<a
							href="{{ route('usermanual.show', ['manual' => $slug, 'chapter' => $chapter['slug']]) }}"
							class="{{ $activeChapter === $chapter['slug'] ? 'is-active' : '' }}"
						>
							{{ $chapter['title'] }}
						</a>
					</li>
				@endforeach
			</ul>
		@endif
	@endforeach
</aside>
