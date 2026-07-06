<div class="breadcrumb-container">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb-modern">
			@foreach ($items as $item)
				<li class="breadcrumb-item-modern {{ $lastIndex == $loop->iteration ? 'active' : '' }}">
					@if ($lastIndex == $loop->iteration)
						<span class="breadcrumb-current">
							@if ($item['icon'])
								<i class="{{ $item['icon'] }} breadcrumb-icon"></i>
							@endif
							@if ($item['name'])
								<span class="breadcrumb-text">{{ $item['name'] }}</span>
							@endif
							@if (!empty($item['badge']))
								<span class="badge badge-modern {{ $item['badge_class'] ?? 'badge-secondary' }} ml-2" style="font-size: 0.875rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center;">{{ $item['badge'] }}</span>
							@endif
						</span>
					@else
						<a href="{{ $item['link'] }}" class="breadcrumb-link">
							@if ($item['icon'])
								<i class="{{ $item['icon'] }} breadcrumb-icon"></i>
							@endif
							@if ($item['name'])
								<span class="breadcrumb-text">{{ $item['name'] }}</span>
							@endif
							@if (!empty($item['badge']))
								<span class="badge badge-modern {{ $item['badge_class'] ?? 'badge-secondary' }} ml-2" style="font-size: 0.875rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center;">{{ $item['badge'] }}</span>
							@endif
						</a>
					@endif
				</li>
			@endforeach
		</ol>
	</nav>
</div>
{{-- Slot anchor: emitted on every page that uses this component --}}
<div data-sf-slot="after_breadcrumb" style="display:none;"></div>