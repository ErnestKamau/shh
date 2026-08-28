@php
	/** @var array<string, array> $manuals */
	/** @var string|null $activeManual */
	/** @var string|null $activeChapter */
	$isHub = request()->routeIs('usermanual.index');
@endphp
<ul class="list-group sticky-top sticky-offset">
	<div class="list-group-item p-4 text-center text-ultra-bold sidebar-module-div">
		<i class="mdi mdi-book-open-page-variant fa-3x"></i><br>
		<span class="text-lg text-bold">User Manual</span>
	</div>

	<a href="{{ route('usermanual.index') }}" class="list-group-item list-group-item-action {{ $isHub ? 'active' : '' }}">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-home-outline fa-fw mr-3"></span>
			<span class="menu-collapsed">All manuals</span>
		</div>
	</a>

	<li class="list-group-item bg-black sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
		<small>MANUALS</small>
	</li>

	@foreach($manuals as $slug => $manual)
		@php
			$isManualActive = $activeManual === $slug;
			$menuId = 'um-manual-'.$slug;
		@endphp
		<a
			href="#{{ $menuId }}"
			data-toggle="collapse"
			aria-expanded="{{ $isManualActive ? 'true' : 'false' }}"
			class="list-group-item list-group-item-action flex-column align-items-start {{ $isManualActive ? 'active' : '' }}"
		>
			<div class="d-flex w-100 justify-content-start align-items-center">
				<span class="mdi {{ $manual['icon'] }} mr-3"></span>
				<span class="menu-collapsed">{{ $manual['title'] }}</span>
				<span class="submenu-icon ml-auto"></span>
			</div>
		</a>
		<div id="{{ $menuId }}" class="collapse sidebar-submenu {{ $isManualActive ? 'show' : '' }}">
			@foreach($manual['chapters'] as $chapter)
				<a
					href="{{ route('usermanual.show', ['manual' => $slug, 'chapter' => $chapter['slug']]) }}"
					class="list-group-item list-group-item-action {{ $isManualActive && $activeChapter === $chapter['slug'] ? 'active' : '' }}"
				>
					<span class="menu-collapsed">
						<i class="mdi mdi-circle-medium"></i> {{ $chapter['title'] }}
					</span>
				</a>
			@endforeach
		</div>
	@endforeach

	<div class="list-group-item copyright-lims p-4 text-center">
		Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
	</div>
</ul>
