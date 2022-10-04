<div class="m-3">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			@foreach ($items as $item)
				<li class="breadcrumb-item">
					<a {!! $lastIndex == $loop->iteration ? '' : 'href="'.$item['link'].'"' !!} class="{{ $lastIndex == $loop->iteration ? 'text-default' : '' }}">
						@if ($item['icon'])
							<i class="{{ $item['icon'] }}"></i>
						@endif
						@if ($item['name'])
							{{ $item['name'] }}
						@endif
					</a>
				</li>
			@endforeach
		</ol>
	</nav>
</div>