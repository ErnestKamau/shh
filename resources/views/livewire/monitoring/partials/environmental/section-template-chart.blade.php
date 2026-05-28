@php
    $template = $workspace['template'];
    $chart = $workspace['chart'];
    $chartId = 'sectionChart-' . $template->id;
    $referenceMode = $chart['reference_mode'] ?? 'optimum';
    $unitSuffix = filled($chart['unit']) ? ' ' . $chart['unit'] : '';
@endphp

<div class="env-template-block env-template-block--chart mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h6 class="mb-1">
                <i class="mdi mdi-chart-line-variant text-primary"></i>
                Final readings — {{ $template->name }}
            </h6>
            <p class="text-muted small mb-0">{{ $chart['period_label'] ?? 'Current period' }}</p>
        </div>
    </div>

    @if($chart['hasData'])
        <div class="env-chart-wrap">
            <canvas id="{{ $chartId }}"
                    class="section-monitoring-chart"
                    data-chart-data='@json($chart)'></canvas>
        </div>
        <div class="env-chart-legend d-flex flex-wrap justify-content-around text-center mt-3 pt-3 border-top">
            @if($referenceMode === 'range')
                <div>
                    <span class="env-chart-legend__label">Low</span>
                    <strong class="text-warning d-block">{{ $chart['reference']['min'] ?? 'N/A' }}{{ $unitSuffix }}</strong>
                </div>
                <div>
                    <span class="env-chart-legend__label">High</span>
                    <strong class="text-danger d-block">{{ $chart['reference']['max'] ?? 'N/A' }}{{ $unitSuffix }}</strong>
                </div>
                @if(filled($chart['optimum_level'] ?? null))
                    <div>
                        <span class="env-chart-legend__label">Optimum</span>
                        <strong class="text-success d-block">{{ $chart['optimum_level'] }}{{ $unitSuffix }}</strong>
                    </div>
                @endif
            @else
                <div>
                    <span class="env-chart-legend__label">{{ $referenceMode === 'constant' ? 'Target' : 'Optimum' }}</span>
                    <strong class="text-success d-block">
                        {{ $chart['reference']['constant'] ?? $chart['reference']['optimum'] ?? 'N/A' }}{{ $unitSuffix }}
                    </strong>
                </div>
            @endif
            @if($chart['include_uncertainty_on_optimum'] ?? false)
                <div>
                    <span class="env-chart-legend__label">Limits ± U.M</span>
                    <strong class="text-warning d-block">Lower ↓ · Upper ↑</strong>
                </div>
                <div>
                    <span class="env-chart-legend__label">Optimum ± U.M</span>
                    <strong class="text-info d-block">From calibration at capture</strong>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-4 text-muted">
            <i class="mdi mdi-chart-bubble" style="font-size: 2rem;"></i>
            <p class="mb-0 mt-2 small">No final readings in the selected period for this template.</p>
            <p class="mb-0 small">Try widening the chart period or capture readings on the Logs tab.</p>
        </div>
    @endif
</div>
