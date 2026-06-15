@php
    $parts = $parts ?? [];
    $rotate = (bool) ($rotate ?? false);
@endphp
@if($rotate)
<div class="trf-vtext-box">
    <div class="trf-vtext-box-inner">
        <span class="trf-vtext">{{ implode(' ', $parts) }}</span>
    </div>
</div>
@else
<span class="trf-vtext-br">
    @foreach($parts as $part)
        {{ $part }}@if(! $loop->last)<br>@endif
    @endforeach
</span>
@endif
