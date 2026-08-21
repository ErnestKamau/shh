{{--
	Pricelist show metric shell — same card chrome as quotation-kpi-card
	(left accent + top-right icon). Content is passed in; layout stays 4-up.
--}}
@php
	$value = $value ?? '—';
	$label = $label ?? '';
	$sublabel = $sublabel ?? '';
	$color = $color ?? '#3498db';
	$icon = $icon ?? 'mdi-information-outline';
@endphp
<div class="quotation-kpi-card pricelist-kpi-card" style="--metric-color: {{ $color }};">
	<div class="d-flex justify-content-between align-items-start">
		<div>
			<p class="quotation-kpi-value">{{ $value }}</p>
			<p class="quotation-kpi-label">{{ $label }}</p>
			<p class="quotation-kpi-sublabel">{{ $sublabel }}</p>
		</div>
		<div class="quotation-kpi-icon" style="color: {{ $color }};">
			<i class="mdi {{ $icon }}"></i>
		</div>
	</div>
</div>
