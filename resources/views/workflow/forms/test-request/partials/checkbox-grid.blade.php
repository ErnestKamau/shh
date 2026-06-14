@php
    $checks = $checks ?? [];
    $onlyKeys = $onlyKeys ?? [];
    $columns = (int) ($columns ?? 2);
    $labelsOnly = $labelsOnly ?? false;
    $items = [];
    foreach ($checks as $label => $checked) {
        if ($onlyKeys !== [] && ! in_array($label, $onlyKeys, true)) {
            continue;
        }
        $items[] = ['label' => $label, 'checked' => $checked];
    }
    $chunks = array_chunk($items, $columns);
@endphp
@if($items === [])
    &nbsp;
@else
<table class="trf-check-grid">
    @foreach($chunks as $chunk)
        <tr>
            @for($i = 0; $i < $columns; $i++)
                <td>
                    @if(isset($chunk[$i]))
                        @php
                            $class = $chunk[$i]['checked'] ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
                        @endphp
                        <span class="{{ $class }}"></span>
                        @if(! $labelsOnly)
                            {{ $chunk[$i]['label'] }}
                        @endif
                    @endif
                </td>
            @endfor
        </tr>
    @endforeach
</table>
@endif
