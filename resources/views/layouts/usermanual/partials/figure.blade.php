@php
	$src = $src ?? null;
	$caption = $caption ?? '';
	$alt = $alt ?? ($caption ?: 'Manual screenshot');
	$exists = is_string($src) && $src !== '' && file_exists(public_path(ltrim(parse_url($src, PHP_URL_PATH) ?: $src, '/')));
	if (! $exists && is_string($src) && str_starts_with($src, '/')) {
		$exists = file_exists(public_path(ltrim($src, '/')));
	}
@endphp
<figure class="um-figure">
	@if($exists)
		<img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
	@else
		<div class="um-figure--placeholder">
			<i class="mdi mdi-monitor-screenshot" aria-hidden="true"></i>
			<span>{{ $placeholder ?? 'Screenshot coming soon — open this screen in the app to follow along.' }}</span>
		</div>
	@endif
	@if($caption !== '')
		<figcaption>{{ $caption }}</figcaption>
	@endif
</figure>
