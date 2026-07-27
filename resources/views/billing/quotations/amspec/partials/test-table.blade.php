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
@endphp
<table class="amspec-test-table" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; table-layout: fixed; width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">Sample Description</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">Test Parameters</th>
            @if($showMethod)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">Test Method</th>
            @endif
            @if($showLoq)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="background-color: {{ $accentColor }}; color: #ffffff;">
                    LOQ
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                </th>
            @endif
            @if($showMu)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="background-color: {{ $accentColor }}; color: #ffffff;">
                    MU%
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                </th>
            @endif
            @if($showTat)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">TAT</th>
            @endif
            @if($showQty)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">Qty</th>
            @endif
            @if($showPrice)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="background-color: {{ $accentColor }}; color: #ffffff;">
                    Unit Price
                    <br>({{ $currencyCode }})
                </th>
            @endif
            @if($showTotal)
                <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="background-color: {{ $primaryColor }}; color: #ffffff;">Total Price<br>({{ $currencyCode }})</th>
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
                        $isPackageSub = ! empty($row['is_package_sub_item']);
                        $isPackageHeader = ! empty($row['is_package']);
                    @endphp
                    <tr class="amspec-test-row" @if($isPackageHeader) style="background-color: #f0fdf4;" @endif>
                        @if($rowIndex === 0)
                            <td rowspan="{{ count($group['rows']) }}" class="amspec-category-cell" style="vertical-align: top; font-weight: 700;">
                                {{ $group['sample_type_name'] }}
                            </td>
                        @endif
                        <td>
                            @if($isPackageSub)
                                <span style="color: #64748b; font-size: 0.95em;">{{ $row['test_name'] }}</span>
                            @else
                                <strong>{{ $row['test_name'] }}</strong>
                            @endif
                        </td>
                        @if($showMethod)
                            <td>{{ $row['test_method'] }}</td>
                        @endif
                        @if($showLoq)
                            <td class="text-center">{{ $row['loq'] }}</td>
                        @endif
                        @if($showMu)
                            <td class="text-center">{{ $row['mu_percent'] }}</td>
                        @endif
                        @if($showTat)
                            <td class="text-center">{{ ! empty($row['tat']) ? $row['tat'] : '' }}</td>
                        @endif
                        @if($showQty)
                            <td class="text-center">{{ $row['quantity'] ?? '' }}</td>
                        @endif
                        @if($showPrice)
                            <td class="text-right">
                                @if((float) ($row['unit_price'] ?? 0) > 0)
                                    {{ number_format((float) $row['unit_price'], 2) }}
                                @elseif($isPackageSub)
                                    —
                                @else
                                    {{ number_format(0, 2) }}
                                @endif
                            </td>
                        @endif
                        @if($showTotal)
                            <td class="text-right">
                                @if((float) ($row['total_price'] ?? 0) > 0)
                                    {{ number_format((float) $row['total_price'], 2) }}
                                @elseif($isPackageSub)
                                    —
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
