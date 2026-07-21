@php
    $tint = 'white';
@endphp
<article class="rv-card rv-card--{{ $tint }}">
    <header class="rv-card-header">
        <span class="rv-card-tag">{{ $section['title'] }}</span>
        <i class="mdi mdi-clipboard-text-outline rv-card-header-icon" aria-hidden="true"></i>
    </header>
    <div class="rv-card-body">
        <h3 class="rv-card-title rv-card-title--sm">{{ $section['title'] }}</h3>
        <ul class="rv-field-list">
            @foreach($section['fields'] as $field)
                <li class="rv-field-item">
                    <i class="mdi {{ $field['icon'] }}" aria-hidden="true"></i>
                    <div>
                        <span class="rv-field-label">{{ $field['label'] }}</span>
                        @if(in_array($field['element_type'], ['checkbox', 'radio', 'select'], true) && str_contains($field['value'], ','))
                            <div class="rv-pill-row">
                                @foreach(preg_split('/\s*,\s*/', $field['value']) as $pill)
                                    @if(trim($pill) !== '')
                                        <span class="rv-pill">{{ trim($pill) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="rv-field-value">{{ $field['value'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</article>
