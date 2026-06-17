@php
    $renderChecks = function (array $checks, array $onlyKeys = []) {
        $html = '';
        foreach ($checks as $label => $checked) {
            if ($onlyKeys !== [] && ! in_array($label, $onlyKeys, true)) {
                continue;
            }
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
    $grid = $collectionGrid ?? [];
@endphp
<div class="trf-section-title">Sample Collection Data</div>
<table class="trf-table">
    <tr>
        <th class="trf-grid-meta"></th>
        <th class="trf-grid-col">Sampling Apparatus</th>
        <th class="trf-grid-col">Method of Sampling</th>
        <th class="trf-grid-col">Reason of Collection</th>
        <th class="trf-grid-col">Transport Condition</th>
    </tr>
    @foreach($grid['rows'] ?? [] as $row)
        <tr>
            <td class="trf-meta-label">{!! $row['meta'] ?? '&nbsp;' !!}</td>
            <td>{!! $renderChecks($row['apparatus'] ?? [], $row['apparatus_keys'] ?? []) !!}</td>
            <td>{!! $renderChecks($row['method'] ?? [], $row['method_keys'] ?? []) !!}</td>
            <td>{!! $renderChecks($row['reason'] ?? [], $row['reason_keys'] ?? []) !!}</td>
            <td>{!! $renderChecks($row['transport'] ?? [], $row['transport_keys'] ?? []) !!}</td>
        </tr>
    @endforeach
</table>
