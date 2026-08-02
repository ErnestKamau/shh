@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
    $categoryBg = '#E0E0E0';

    // AmSpec Format PDF: fixed 6-column layout
    $columnCount = 6;
    $labelColspan = 5;

    $wSno = 7.0;
    $wTests = 28.0;
    $wMethod = 24.0;
    $wLoq = 13.0;
    $wMu = 13.0;
    $wPrice = 15.0;

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
        <col class="amspec-col-num" style="width: {{ $wLoq }}%;">
        <col class="amspec-col-num" style="width: {{ $wMu }}%;">
        <col class="amspec-col-price" style="width: {{ $wPrice }}%;">
    </colgroup>
    <thead>
        <tr>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wSno) }}">S.No.</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wTests) }}">Tests</th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wMethod) }}">Test Method</th>
            <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wLoq) }}">
                LOQ
                <span class="amspec-th-sub">(Option to add)</span>
            </th>
            <th class="amspec-th-accent" bgcolor="{{ $accentColor }}" style="{{ $thStyle($accentColor, $wMu) }}">
                MU%
                <span class="amspec-th-sub">(Option to add)</span>
            </th>
            <th class="amspec-th-primary" bgcolor="{{ $primaryColor }}" style="{{ $thStyle($primaryColor, $wPrice) }}">
                Unit Price
                <br>({{ $currencyCode }})
            </th>
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
                        <td class="text-center amspec-num-cell">{{ $row['loq'] ?? '' }}</td>
                        <td class="text-center amspec-num-cell">{{ $row['mu_percent'] ?? '' }}</td>
                        <td class="text-right amspec-price-cell">
                            @if((float) ($row['unit_price'] ?? 0) > 0)
                                {{ number_format((float) $row['unit_price'], 2) }}
                            @else
                                {{ number_format(0, 2) }}
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
