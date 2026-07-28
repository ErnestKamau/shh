@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);

    $showMethod = (bool) ($showMethodColumn ?? false);
    $showLoq = (bool) ($showLoqColumn ?? false);
    $showMu = (bool) ($showMuColumn ?? false);
    $showTat = (bool) ($showTatColumn ?? false);
    $showQty = (bool) ($showQuantityColumn ?? false);
    $showPrice = (bool) ($showUnitPriceColumn ?? false);
    $showTotal = (bool) ($showTotalPriceColumn ?? $showPrice);

    $columnCount = 2 // sample + parameters
        + ($showMethod ? 1 : 0)
        + ($showLoq ? 1 : 0)
        + ($showMu ? 1 : 0)
        + ($showTat ? 1 : 0)
        + ($showQty ? 1 : 0)
        + ($showPrice ? 1 : 0)
        + ($showTotal ? 1 : 0);

    $labelColspan = max(1, $columnCount - ($showTotal ? 1 : 0));
    $optionalSubLabel = $isPdf ? '' : '(optional)';

    // Prefer Test Parameters, but leave readable room for methods and numbers.
    $wSample = 11.0;
    $wMethod = $showMethod ? 14.0 : 0.0;
    $wLoq = $showLoq ? 6.5 : 0.0;
    $wMu = $showMu ? 6.0 : 0.0;
    $wTat = $showTat ? 5.5 : 0.0;
    $wQty = $showQty ? 5.0 : 0.0;
    $wPrice = $showPrice ? 10.0 : 0.0;
    $wTotal = $showTotal ? 10.5 : 0.0;
    $wParams = max(
        24.0,
        100.0 - ($wSample + $wMethod + $wLoq + $wMu + $wTat + $wQty + $wPrice + $wTotal)
    );

    $thStyle = static function (string $bg, float $width): string {
        return sprintf(
            'background-color: %s; color: #ffffff; width: %s%%;',
            $bg,
            rtrim(rtrim(number_format($width, 1, '.', ''), '0'), '.')
        );
    };
@endphp
<table class="amspec-test-table" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; table-layout: fixed; width: 100%; border-collapse: collapse;">
    <colgroup>
        <col class="amspec-col-sample" style="width: {{ $wSample }}%;">
        <col class="amspec-col-params" style="width: {{ $wParams }}%;">
        @if($showMethod)
            <col class="amspec-col-method" style="width: {{ $wMethod }}%;">
        @endif
        @if($showLoq)
            <col class="amspec-col-num" style="width: {{ $wLoq }}%;">
        @endif
        @if($showMu)
            <col class="amspec-col-num" style="width: {{ $wMu }}%;">
        @endif
        @if($showTat)
            <col class="amspec-col-num" style="width: {{ $wTat }}%;">
        @endif
        @if($showQty)
            <col class="amspec-col-num" style="width: {{ $wQty }}%;">
        @endif
        @if($showPrice)
            <col class="amspec-col-price" style="width: {{ $wPrice }}%;">
        @endif
        @if($showTotal)
            <col class="amspec-col-price" style="width: {{ $wTotal }}%;">
        @endif
    </colgroup>
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wSample) }}">Sample Description</th>
            <th class="amspec-th-primary amspec-th-params" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wParams) }}">Test Parameters</th>
            @if($showMethod)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wMethod) }}">Test Method</th>
            @endif
            @if($showLoq)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wLoq) }}">
                    LOQ
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                </th>
            @endif
            @if($showMu)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wMu) }}">
                    MU%
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                </th>
            @endif
            @if($showTat)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wTat) }}">TAT</th>
            @endif
            @if($showQty)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wQty) }}">Qty</th>
            @endif
            @if($showPrice)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wPrice) }}">
                    Unit Price
                    <br>({{ $currencyCode }})
                </th>
            @endif
            @if($showTotal)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wTotal) }}">Total Price<br>({{ $currencyCode }})</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @if(empty($groups))
            <tr>
                <td colspan="{{ $columnCount }}" class="text-center" style="padding: 16px; color: #666;">
                    No tests configured. Add line items before generating the quotation report.
                </td>
            </tr>
        @else
            @foreach($groups as $group)
                @foreach($group['rows'] as $rowIndex => $row)
                    @php
                        $isPackageHeader = ! empty($row['is_package']);
                        $packageParameters = array_values(array_filter(
                            array_map('strval', (array) ($row['package_parameters'] ?? []))
                        ));
                    @endphp
                    <tr class="amspec-test-row" @if($isPackageHeader) style="background-color: #f8fafc;" @endif>
                        @if($rowIndex === 0)
                            <td rowspan="{{ count($group['rows']) }}" class="amspec-category-cell" style="vertical-align: middle; text-align: center; font-weight: 700;">
                                <span class="amspec-sample-description">{{ $group['sample_type_name'] }}</span>
                            </td>
                        @endif
                        <td class="amspec-params-cell{{ $isPackageHeader ? ' amspec-package-cell' : '' }}">
                            <strong class="amspec-test-name">{{ $row['test_name'] }}</strong>
                            @if($packageParameters !== [])
                                <div class="amspec-package-parameters">{{ implode(', ', $packageParameters) }}</div>
                            @endif
                        </td>
                        @if($showMethod)
                            <td>{{ $row['test_method'] }}</td>
                        @endif
                        @if($showLoq)
                            <td class="text-center amspec-num-cell">{{ $row['loq'] }}</td>
                        @endif
                        @if($showMu)
                            <td class="text-center amspec-num-cell">{{ $row['mu_percent'] }}</td>
                        @endif
                        @if($showTat)
                            <td class="text-center amspec-num-cell">{{ ! empty($row['tat']) ? $row['tat'] : '' }}</td>
                        @endif
                        @if($showQty)
                            <td class="text-center amspec-num-cell">{{ $row['quantity'] ?? '' }}</td>
                        @endif
                        @if($showPrice)
                            <td class="text-right amspec-price-cell">
                                @if((float) ($row['unit_price'] ?? 0) > 0)
                                    {{ number_format((float) $row['unit_price'], 2) }}
                                @else
                                    {{ number_format(0, 2) }}
                                @endif
                            </td>
                        @endif
                        @if($showTotal)
                            <td class="text-right amspec-price-cell">
                                @if((float) ($row['total_price'] ?? 0) > 0)
                                    {{ number_format((float) $row['total_price'], 2) }}
                                @else
                                    {{ number_format(0, 2) }}
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        @endif
    </tbody>
    @if(!empty($groups) && $showTotal)
    <tfoot>
        @include('billing.quotations.amspec.partials.totals', ['labelColspan' => $labelColspan])
    </tfoot>
    @elseif(!empty($groups) && $showPrice)
    <tfoot>
        @include('billing.quotations.amspec.partials.totals', ['labelColspan' => max(1, $columnCount - 1)])
    </tfoot>
    @endif
</table>
