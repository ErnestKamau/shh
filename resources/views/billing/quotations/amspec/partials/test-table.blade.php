@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
    $showMu = (bool) ($showMuColumn ?? true);
    $showPrice = (bool) ($showUnitPriceColumn ?? true);

    // Columns that are always shown
    $colSample = '16%';
    $colMethod = '20%';
    $colLoq = '10%';
    $colTotal = '14%';

    // Optional columns
    $colOptional = $showMu ? '12%' : '0%';
    $colPrice = $showPrice ? '12%' : '0%';

    // Dynamically calculate the parameters column width so total is exactly 100%
    $remaining = 100 - 16 - 20 - 10 - 14;
    if ($showMu) { $remaining -= 12; }
    if ($showPrice) { $remaining -= 12; }
    $colParams = $remaining . '%';

    $optionalSubLabel = $isPdf ? '' : '(optional)';
    $columnCount = 4 + ($showMuColumn ? 1 : 0) + ($showUnitPriceColumn ? 1 : 0) + 1;
    $labelColspan = $columnCount - 1;
@endphp
<table class="amspec-test-table" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; table-layout: fixed; width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colSample }}; background-color: {{ $primaryColor }}; color: #ffffff;">Sample Description</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colParams }}; background-color: {{ $primaryColor }}; color: #ffffff;">Test Parameters</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colMethod }}; background-color: {{ $primaryColor }}; color: #ffffff;">Test Method</th>
            @if($showMuColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="width: {{ $colOptional }}; background-color: {{ $accentColor }}; color: #ffffff;">
                    Uncertainty
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                </th>
            @endif
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colLoq }}; background-color: {{ $primaryColor }}; color: #ffffff;">LOQ</th>
            @if($showUnitPriceColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="width: {{ $colPrice }}; background-color: {{ $accentColor }}; color: #ffffff;">
                    Unit Price
                    @if($optionalSubLabel !== '')
                        <span class="amspec-th-sub">{{ $optionalSubLabel }}</span>
                    @endif
                    <br>({{ $currencyCode }})
                </th>
            @endif
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colTotal }}; background-color: {{ $primaryColor }}; color: #ffffff;">Total Price<br>({{ $currencyCode }})</th>
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
                    <tr class="amspec-test-row">
                        @if($rowIndex === 0)
                            <td rowspan="{{ count($group['rows']) }}" class="amspec-category-cell" style="vertical-align: top; font-weight: 700;">
                                {{ $group['sample_type_name'] }}
                            </td>
                        @endif
                        <td>
                            @if(!empty($row['is_package_sub_item']))
                                <span style="color: #64748b; font-size: 0.95em;">{{ $row['test_name'] }}</span>
                            @else
                                {{ $row['test_name'] }}
                            @endif
                        </td>
                        <td>{{ $row['test_method'] }}</td>
                        @if($showMuColumn)
                            <td class="text-center">{{ $row['mu_percent'] }}</td>
                        @endif
                        <td class="text-center">{{ $row['loq'] }}</td>
                        @if($showUnitPriceColumn)
                            <td class="text-right">
                                @if(!empty($row['is_package_sub_item']))
                                    —
                                @else
                                    {{ number_format((float) $row['unit_price'], 2) }}
                                @endif
                            </td>
                        @endif
                        <td class="text-right">
                            @if(!empty($row['is_package_sub_item']))
                                —
                            @else
                                {{ number_format((float) $row['total_price'], 2) }}
                            @endif
                        </td>
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
