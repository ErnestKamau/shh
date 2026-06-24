@php
    $checks = $checks ?? [];
    $onlyKeys = $onlyKeys ?? [];
    $orderedKeys = $orderedKeys ?? [];
    $columns = (int) ($columns ?? 1);
    $labelsOnly = $labelsOnly ?? false;
    $bordered = (bool) ($bordered ?? false);
    $split = (bool) ($split ?? false);
    $compact = (bool) ($compact ?? false);
    $size = (string) ($size ?? ($compact ? 'compact' : 'normal'));
    $solidGrid = (bool) ($solidGrid ?? false);
    $sectionFit = (bool) ($sectionFit ?? false);
    $asRows = (bool) ($asRows ?? false);
    $headerRow = isset($headerRow) ? (string) $headerRow : null;
    $fullWidthRows = $fullWidthRows ?? [];
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
        if (! $sectionFit) {
            $gridClass .= ' trf-check-grid-bordered';
        }
        if (in_array($size, ['compact', 'normal', 'relaxed'], true)) {
            $gridClass .= ' trf-check-grid-'.$size;
        } elseif ($compact) {
            $gridClass .= ' trf-check-grid-compact';
        }
        if ($sectionFit) {
            $gridClass .= ' trf-ww-section-rows trf-check-grid-'.$size;
        } elseif ($solidGrid) {
            $gridClass .= ' trf-check-grid-solid trf-ww-inner-grid';
        }
    }

    $chunkCount = count($chunks);
    $fullWidthRowCount = count($fullWidthRows);
    $hasTrailing = ! empty($trailingHtml);
    $gridLine = '#000';
    $sizeStyles = [
        'compact' => 'padding:0 2px;font-size:5.5pt;line-height:1.05;vertical-align:top;text-align:left;background:#fff;min-height:10px;height:10px;',
        'normal' => 'padding:2px 3px;font-size:7pt;line-height:1.2;vertical-align:top;text-align:left;background:#fff;min-height:14px;height:14px;',
        'relaxed' => 'padding:3px 4px;font-size:7.5pt;line-height:1.25;vertical-align:top;text-align:left;background:#fff;min-height:18px;height:18px;',
    ];
    $baseCellStyle = $sizeStyles[$size] ?? $sizeStyles['normal'];

    $cellStyle = function (int $colIndex, bool $isLastRow) use ($columns, $baseCellStyle, $gridLine, $solidGrid, $sectionFit): string {
        if ($solidGrid || $sectionFit) {
            return '';
        }

        $style = $baseCellStyle;

        if ($columns > 1) {
            $style .= 'border-bottom:1px solid '.$gridLine.';';
            if ($colIndex === 0) {
                $style .= 'border-right:1px solid '.$gridLine.';';
            }
            if ($colIndex === 1) {
                $style .= 'border-left:1px solid '.$gridLine.';';
            }
        } else {
            $style .= 'border-bottom:1px solid '.$gridLine.';';
        }

        if ($isLastRow) {
            $style .= 'border-bottom:none;';
        }

        return $style;
    };

    $innerCellClass = '';
    if ($sectionFit) {
        $innerCellClass = 'trf-ww-section-cell';
    } elseif ($bordered && $solidGrid) {
        $innerCellClass = 'trf-ww-inner-cell trf-ww-inner-grid-cell';
    }
@endphp
@if($items === [] && empty($trailingHtml) && $headerRow === null && $fullWidthRows === [])
    @if(! $asRows)
        &nbsp;
    @endif
@elseif($asRows)
    @foreach($chunks as $chunkIndex => $chunk)
        @php
            $isLastRow = $chunkIndex === ($chunkCount - 1) && $fullWidthRowCount === 0 && ! $hasTrailing;
            $isIncompleteRow = $bordered && $columns > 1 && count($chunk) < $columns;
        @endphp
        <tr @if($isIncompleteRow) class="trf-check-grid-incomplete-row" @endif>
            @for($i = 0; $i < $columns; $i++)
                <td class="{{ $innerCellClass }}{{ ($bordered && ! isset($chunk[$i])) ? ' trf-check-grid-empty-cell' : '' }}" @if($bordered && ! $solidGrid && ! $sectionFit) style="{{ $cellStyle($i, $isLastRow) }}" @endif width="{{ $columns > 1 ? '50%' : '100%' }}">
                    @if(isset($chunk[$i]))
                        @php
                            $class = $chunk[$i]['checked'] ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
                        @endphp
                        <span class="{{ $class }}"></span>@if(! $labelsOnly) {{ $chunk[$i]['label'] }}@endif
                    @else
                        <span class="trf-check trf-check-off trf-check-grid-empty-placeholder"></span>
                    @endif
                </td>
            @endfor
        </tr>
    @endforeach
    @foreach($fullWidthRows as $rowIndex => $rowHtml)
        <tr>
            <td colspan="{{ $columns }}" class="{{ $innerCellClass }}" @if($bordered && ! $solidGrid && ! $sectionFit && ($rowIndex === $fullWidthRowCount - 1) && ! $hasTrailing) style="{{ rtrim($baseCellStyle, ';').';border-bottom:none;' }}" @elseif($bordered && ! $solidGrid && ! $sectionFit) style="{{ $cellStyle(0, false) }}" @endif>{!! $rowHtml !!}</td>
        </tr>
    @endforeach
    @if($hasTrailing)
        <tr>
            <td colspan="{{ $columns }}" class="{{ $innerCellClass }}" @if($bordered && ! $solidGrid && ! $sectionFit) style="{{ $baseCellStyle }}border-bottom:none;" @endif>{!! $trailingHtml !!}</td>
        </tr>
    @endif
@else
<table class="{{ $gridClass }}" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;table-layout:fixed;">
    @if($headerRow !== null && $headerRow !== '')
        <tr>
            <td colspan="{{ $columns }}" class="trf-check-grid-subheader-cell">{{ $headerRow }}</td>
        </tr>
    @endif
    @foreach($chunks as $chunkIndex => $chunk)
        @php
            $isLastRow = $chunkIndex === ($chunkCount - 1) && $fullWidthRowCount === 0 && ! $hasTrailing;
            $isIncompleteRow = $bordered && $columns > 1 && count($chunk) < $columns;
        @endphp
        <tr @if($isIncompleteRow) class="trf-check-grid-incomplete-row" @endif>
            @for($i = 0; $i < $columns; $i++)
                <td class="{{ $innerCellClass }}{{ ($bordered && ! isset($chunk[$i])) ? ' trf-check-grid-empty-cell' : '' }}" @if($bordered && ! $solidGrid && ! $sectionFit) style="{{ $cellStyle($i, $isLastRow) }}" @endif width="{{ $columns > 1 ? '50%' : '100%' }}">
                    @if(isset($chunk[$i]))
                        @php
                            $class = $chunk[$i]['checked'] ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
                        @endphp
                        <span class="{{ $class }}"></span>@if(! $labelsOnly) {{ $chunk[$i]['label'] }}@endif
                    @else
                        <span class="trf-check trf-check-off trf-check-grid-empty-placeholder"></span>
                    @endif
                </td>
            @endfor
        </tr>
    @endforeach
    @foreach($fullWidthRows as $rowIndex => $rowHtml)
        <tr>
            <td colspan="{{ $columns }}" class="{{ $innerCellClass }}" @if($bordered && ! $solidGrid && ! $sectionFit && ($rowIndex === $fullWidthRowCount - 1) && ! $hasTrailing) style="{{ rtrim($baseCellStyle, ';').';border-bottom:none;' }}" @elseif($bordered && ! $solidGrid && ! $sectionFit) style="{{ $cellStyle(0, false) }}" @endif>{!! $rowHtml !!}</td>
        </tr>
    @endforeach
    @if($hasTrailing)
        <tr>
            <td colspan="{{ $columns }}" class="{{ $innerCellClass }}" @if($bordered && ! $solidGrid && ! $sectionFit) style="{{ $baseCellStyle }}border-bottom:none;" @endif>{!! $trailingHtml !!}</td>
        </tr>
    @endif
</table>
@endif
