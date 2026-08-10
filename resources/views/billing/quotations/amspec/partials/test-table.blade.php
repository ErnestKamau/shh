@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
    $categoryBg = '#E0E0E0';

    $showLoqColumn = (bool) ($showLoqColumn ?? true);
    $showMuColumn = (bool) ($showMuColumn ?? true);
    $showTatColumn = (bool) ($showTatColumn ?? true);
    $showQuantityColumn = (bool) ($showQuantityColumn ?? true);
    $showUnitPriceColumn = (bool) ($showUnitPriceColumn ?? true);

    // S.No. | Tests | Test Method | [LOQ] | [MU%] | [TAT] | [Qty] | [Unit Price]
    $columnCount = 3
        + ($showLoqColumn ? 1 : 0)
        + ($showMuColumn ? 1 : 0)
        + ($showTatColumn ? 1 : 0)
        + ($showQuantityColumn ? 1 : 0)
        + ($showUnitPriceColumn ? 1 : 0);
    $labelColspan = max(1, $columnCount - 1);

    $wSno = 7.0;
    $wTests = 26.0;
    $wMethod = 22.0;
    $optionalShare = 100.0 - $wSno - $wTests - $wMethod;
    $optionalCount = ($showLoqColumn ? 1 : 0) + ($showMuColumn ? 1 : 0) + ($showTatColumn ? 1 : 0) + ($showQuantityColumn ? 1 : 0) + ($showUnitPriceColumn ? 1 : 0);
    if ($optionalCount === 0) {
        $wTests += $optionalShare * 0.55;
        $wMethod += $optionalShare * 0.45;
        $wLoq = 0.0;
        $wMu = 0.0;
        $wTat = 0.0;
        $wQty = 0.0;
        $wPrice = 0.0;
    } else {
        $each = $optionalShare / $optionalCount;
        $wLoq = $showLoqColumn ? $each : 0.0;
        $wMu = $showMuColumn ? $each : 0.0;
        $wTat = $showTatColumn ? $each : 0.0;
        $wQty = $showQuantityColumn ? $each : 0.0;
        $wPrice = $showUnitPriceColumn ? $each : 0.0;
    }

    $thStyle = static function (string $bg, float $width): string {
        return sprintf(
            'background-color: %s; color: #ffffff; width: %s%%;',
            $bg,
            rtrim(rtrim(number_format($width, 1, '.', ''), '0'), '.')
        );
    };

    $serialNo = 0;
@endphp
<table class="amspec-test-table" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; table-layout: fixed; width: 100%; border-collapse: collapse;">
    <colgroup>
        <col class="amspec-col-sno" style="width: {{ $wSno }}%;">
        <col class="amspec-col-tests" style="width: {{ $wTests }}%;">
        <col class="amspec-col-method" style="width: {{ $wMethod }}%;">
        @if($showLoqColumn)
            <col class="amspec-col-num" style="width: {{ $wLoq }}%;">
        @endif
        @if($showMuColumn)
            <col class="amspec-col-num" style="width: {{ $wMu }}%;">
        @endif
        @if($showTatColumn)
            <col class="amspec-col-num" style="width: {{ $wTat }}%;">
        @endif
        @if($showQuantityColumn)
            <col class="amspec-col-num" style="width: {{ $wQty }}%;">
        @endif
        @if($showUnitPriceColumn)
            <col class="amspec-col-price" style="width: {{ $wPrice }}%;">
        @endif
    </colgroup>
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wSno) }}">S.No.</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wTests) }}">Tests</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wMethod) }}">Test Method</th>
            @if($showLoqColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wLoq) }}">
                    LOQ
                    <span class="amspec-th-sub">(Option to add)</span>
                </th>
            @endif
            @if($showMuColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wMu) }}">
                    MU%
                    <span class="amspec-th-sub">(Option to add)</span>
                </th>
            @endif
            @if($showTatColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wTat) }}">
                    TAT
                    <span class="amspec-th-sub">(days)</span>
                </th>
            @endif
            @if($showQuantityColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wQty) }}">
                    Qty
                    <span class="amspec-th-sub">(samples)</span>
                </th>
            @endif
            @if($showUnitPriceColumn)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wPrice) }}">
                    Unit Price
                    <br>({{ $currencyCode }})
                </th>
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
                @php
                    $categoryName = trim((string) ($group['sample_type_name'] ?? ''));
                @endphp
                @if($categoryName !== '')
                    <tr class="amspec-category-row">
                        <td colspan="{{ $columnCount }}" class="amspec-category-banner" bgcolor="{{ $categoryBg }}" style="background-color: {{ $categoryBg }}; font-weight: 700; text-align: left; padding: 5px 8px;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                @endif
                @foreach($group['rows'] as $row)
                    @php
                        $serialNo++;
                        $isPackageHeader = ! empty($row['is_package']);
                        $packageParameters = array_values(array_filter(
                            array_map('strval', (array) ($row['package_parameters'] ?? []))
                        ));
                        $rowTat = $row['tat'] ?? null;
                    @endphp
                    <tr class="amspec-test-row" @if($isPackageHeader) style="background-color: #f8fafc;" @endif>
                        <td class="text-center amspec-num-cell">{{ $serialNo }}</td>
                        <td class="amspec-params-cell{{ $isPackageHeader ? ' amspec-package-cell' : '' }}">
                            <strong class="amspec-test-name">{{ $row['test_name'] }}</strong>
                            @if($packageParameters !== [])
                                <div class="amspec-package-parameters">{{ implode(', ', $packageParameters) }}</div>
                            @endif
                        </td>
                        <td>{{ $row['test_method'] ?? '' }}</td>
                        @if($showLoqColumn)
                            <td class="text-center amspec-num-cell">{{ $row['loq'] ?? '' }}</td>
                        @endif
                        @if($showMuColumn)
                            <td class="text-center amspec-num-cell">{{ $row['mu_percent'] ?? '' }}</td>
                        @endif
                        @if($showTatColumn)
                            <td class="text-center amspec-num-cell">
                                @if($rowTat !== null && (int) $rowTat > 0)
                                    {{ (int) $rowTat }}
                                @endif
                            </td>
                        @endif
                        @if($showQuantityColumn)
                            <td class="text-center amspec-num-cell">
                                {{ max(1, (int) ($row['quantity'] ?? 1)) }}
                            </td>
                        @endif
                        @if($showUnitPriceColumn)
                            <td class="text-right amspec-price-cell">
                                @if((float) ($row['unit_price'] ?? 0) > 0)
                                    {{ number_format((float) $row['unit_price'], 2) }}
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
    @if(!empty($groups))
    <tfoot>
        @include('billing.quotations.amspec.partials.totals', ['labelColspan' => $labelColspan])
    </tfoot>
    @endif
</table>
