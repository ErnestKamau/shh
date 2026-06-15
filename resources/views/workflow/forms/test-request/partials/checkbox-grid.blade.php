@php
    $checks = $checks ?? [];
    $onlyKeys = $onlyKeys ?? [];
    $orderedKeys = $orderedKeys ?? [];
    $columns = (int) ($columns ?? 1);
    $labelsOnly = $labelsOnly ?? false;
    $bordered = (bool) ($bordered ?? false);
    $split = (bool) ($split ?? false);
    $trailingHtml = $trailingHtml ?? null;
    $items = [];

    if ($orderedKeys !== []) {
        foreach ($orderedKeys as $label) {
            if (! array_key_exists($label, $checks)) {
                continue;
            }
            $items[] = ['label' => $label, 'checked' => (bool) $checks[$label]];
        }
    } else {
        foreach ($checks as $label => $checked) {
            if ($onlyKeys !== [] && ! in_array($label, $onlyKeys, true)) {
                continue;
            }
            $items[] = ['label' => $label, 'checked' => $checked];
        }
    }

    $chunks = array_chunk($items, max(1, $columns));
    $gridClass = 'trf-check-grid';
    $gridClass .= $columns > 1 ? ' trf-check-grid-2' : ' trf-check-grid-1';
    if ($split) {
        $gridClass .= ' trf-check-grid-split';
    } elseif ($bordered) {
        $gridClass .= ' trf-check-grid-bordered';
    }

    $chunkCount = count($chunks);
    $hasTrailing = ! empty($trailingHtml);
    $gridLine = '#b0b0b0';
    $baseCellStyle = 'padding:3px 4px;font-size:6pt;vertical-align:middle;background:#fff;';

    $cellStyle = function (int $colIndex, bool $isLastRow) use ($columns, $baseCellStyle, $gridLine): string {
        $style = $baseCellStyle;

        if ($columns > 1) {
            $style .= 'border-bottom:1px solid '.$gridLine.';';
            if ($colIndex === 0) {
                $style .= 'border-right:1px solid '.$gridLine.';';
            }
        } else {
            $style .= 'border-bottom:1px solid '.$gridLine.';';
        }

        if ($isLastRow) {
            $style .= 'border-bottom:none;';
        }

        return $style;
    };
@endphp
@if($items === [] && empty($trailingHtml))
    &nbsp;
@else
<table class="{{ $gridClass }}" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;table-layout:fixed;">
    @foreach($chunks as $chunkIndex => $chunk)
        @php
            $isLastRow = $chunkIndex === ($chunkCount - 1) && ! $hasTrailing;
        @endphp
        <tr>
            @for($i = 0; $i < $columns; $i++)
                <td @if($bordered) style="{{ $cellStyle($i, $isLastRow) }}" @endif width="{{ $columns > 1 ? '50%' : '100%' }}">
                    @if(isset($chunk[$i]))
                        @php
                            $class = $chunk[$i]['checked'] ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
                        @endphp
                        <span class="{{ $class }}"></span>@if(! $labelsOnly){{ $chunk[$i]['label'] }}@endif
                    @endif
                </td>
            @endfor
        </tr>
    @endforeach
    @if($hasTrailing)
        <tr>
            <td colspan="{{ $columns }}" style="{{ $baseCellStyle }}">{!! $trailingHtml !!}</td>
        </tr>
    @endif
</table>
@endif
