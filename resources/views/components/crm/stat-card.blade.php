@props([
    'href' => null,
    'accent' => 'neutral',
    'label' => '',
    'value' => '',
    'icon' => null,
    'sublabel' => null,
])
@if($href)
    <a href="{{ $href }}" class="text-decoration-none d-block h-100">
        <div class="crm-stat-card crm-stat-card-{{ $accent }}" {{ $attributes }}>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center flex-grow-1 min-w-0">
                    @if(isset($icon))
                        <div class="crm-stat-icon mr-3">
                            <i class="mdi {{ $icon }}"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1 min-w-0">
                        <div class="crm-stat-label">{{ $label }}</div>
                        <div class="crm-stat-value">{{ $value }}</div>
                        @if(isset($sublabel))
                            <small class="text-muted crm-stat-sublabel">{{ $sublabel }}</small>
                        @endif
                    </div>
                </div>
                @if($badge ?? null)
                    <div class="crm-stat-badge">{{ $badge }}</div>
                @endif
            </div>
        </div>
    </a>
@else
    <div class="crm-stat-card crm-stat-card-{{ $accent }} h-100" {{ $attributes }}>
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-grow-1 min-w-0">
                @if(isset($icon))
                    <div class="crm-stat-icon mr-3">
                        <i class="mdi {{ $icon }}"></i>
                    </div>
                @endif
                <div class="flex-grow-1 min-w-0">
                    <div class="crm-stat-label">{{ $label }}</div>
                    <div class="crm-stat-value">{{ $value }}</div>
                    @if(isset($sublabel))
                        <small class="text-muted crm-stat-sublabel">{{ $sublabel }}</small>
                    @endif
                </div>
            </div>
            @if($badge ?? null)
                <div class="crm-stat-badge">{{ $badge }}</div>
            @endif
        </div>
    </div>
@endif
