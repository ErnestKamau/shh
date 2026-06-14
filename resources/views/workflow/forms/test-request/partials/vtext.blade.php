@php
    $parts = $parts ?? [];
@endphp
<span class="trf-vtext-br">
    @foreach($parts as $part)
        {{ $part }}@if(! $loop->last)<br>@endif
    @endforeach
</span>
