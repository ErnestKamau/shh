@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
    $colCategory = $isPdf ? '14%' : '14%';
    $colTests = $isPdf ? '22%' : '22%';
    $colMethod = $isPdf ? '20%' : '20%';
    $colOptional = $isPdf ? '10%' : '10%';
    $colPrice = $isPdf ? '12%' : '14%';
    $loqSubLabel = $isPdf ? '' : '(Option to add)';
    $muSubLabel = $isPdf ? '' : '(Option to add)';
@endphp
<table class="amspec-test-table" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; table-layout: fixed; width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colCategory }}; background-color: {{ $primaryColor }}; color: #ffffff;">S.No.</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colTests }}; background-color: {{ $primaryColor }}; color: #ffffff;">Tests</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colMethod }}; background-color: {{ $primaryColor }}; color: #ffffff;">Test Method</th>
            @if($showLoqColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="width: {{ $colOptional }}; background-color: {{ $accentColor }}; color: #ffffff;">
                    LOQ
                    @if($loqSubLabel !== '')
                        <span class="amspec-th-sub">{{ $loqSubLabel }}</span>
                    @endif
                </th>
            @endif
            @if($showMuColumn)
                <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="width: {{ $colOptional }}; background-color: {{ $accentColor }}; color: #ffffff;">
                    MU%
                    @if($muSubLabel !== '')
                        <span class="amspec-th-sub">{{ $muSubLabel }}</span>
                    @endif
                </th>
            @endif
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="width: {{ $colPrice }}; background-color: {{ $primaryColor }}; color: #ffffff;">Unit Price<br>({{ $currencyCode }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groups as $group)
            @foreach($group['rows'] as $rowIndex => $row)
                <tr class="amspec-test-row">
                    @if($rowIndex === 0)
                        <td rowspan="{{ count($group['rows']) }}" class="amspec-category-cell" style="vertical-align: top; font-weight: 700;">
                            {{ $group['sample_type_name'] }}
                        </td>
                    @endif
                    <td>{{ $row['test_name'] }}</td>
                    <td>{{ $row['test_method'] }}</td>
                    @if($showLoqColumn)
                        <td class="text-center">{{ $row['loq'] }}</td>
                    @endif
                    @if($showMuColumn)
                        <td class="text-center">{{ $row['mu_percent'] }}</td>
                    @endif
                    <td class="text-right">{{ number_format((float) $row['unit_price'], 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        @include('billing.quotations.amspec.partials.totals')
    </tfoot>
</table>
