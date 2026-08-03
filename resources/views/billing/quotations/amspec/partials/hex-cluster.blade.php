@php
    $primaryColor = $branding['primary'] ?? '#8B1538';
    $greyColor = '#6e6e6e';
@endphp
{{-- AmSpec hex cluster — matches Quotation Format reference (no frame / no top rule). --}}
<svg class="amspec-hex-cluster" viewBox="0 0 140 105" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" overflow="visible">
    {{-- Large grey outline (center-back) --}}
    <polygon points="70,14 102,32 102,68 70,86 38,68 38,32" fill="none" stroke="{{ $greyColor }}" stroke-width="2"/>
    {{-- Medium grey outline (lower-left) --}}
    <polygon points="36,42 58,54 58,78 36,90 14,78 14,54" fill="none" stroke="{{ $greyColor }}" stroke-width="2"/>
    {{-- Large dashed grey outline (lower-right) --}}
    <polygon points="92,48 118,63 118,91 92,106 66,91 66,63" fill="none" stroke="{{ $greyColor }}" stroke-width="1.8" stroke-dasharray="5 3.5" transform="translate(0,-12)"/>
    {{-- Small solid primary hex (top-right tip) --}}
    <polygon points="108,4 122,12 122,28 108,36 94,28 94,12" fill="{{ $primaryColor }}"/>
</svg>
