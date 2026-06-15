@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
    $greyColor = '#8a8a8a';
@endphp
<svg class="amspec-hex-cluster" viewBox="0 0 118 88" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    {{-- Small solid maroon hex (top-right) --}}
    <polygon points="86,2 96,8 96,20 86,26 76,20 76,8" fill="{{ $primaryColor }}" />
    {{-- Large grey outline hex (center) --}}
    <polygon points="58,18 78,29 78,51 58,62 38,51 38,29" fill="none" stroke="{{ $greyColor }}" stroke-width="1.3" />
    {{-- Small grey outline hex (left) --}}
    <polygon points="22,34 34,41 34,55 22,62 10,55 10,41" fill="none" stroke="{{ $greyColor }}" stroke-width="1.3" />
    {{-- Large dashed grey hex (bottom-right) --}}
    <polygon points="68,44 92,58 92,82 68,96 44,82 44,58" fill="none" stroke="{{ $greyColor }}" stroke-width="1.3" stroke-dasharray="4,3" transform="translate(0,-12)" />
</svg>
