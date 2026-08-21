<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $laboratory_ref }}</title>
    <style>
        @page { margin: 16mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; margin: 0; position: relative; }
        table { border-collapse: collapse; width: 100%; }
        .logo-row td { vertical-align: middle; border: none; padding: 0 0 6px; }
        .logo-placeholder {
            border: 1px dashed #bbb;
            color: #999;
            font-size: 8px;
            text-align: center;
            padding: 18px 8px;
            min-height: 40px;
        }
        .company-block { font-size: 8.5px; line-height: 1.4; vertical-align: top; }
        .company-name { font-size: 10px; font-weight: bold; color: #222; margin-bottom: 2px; }
        .contact-block { font-size: 8.5px; line-height: 1.5; text-align: right; vertical-align: top; }
        .meta-table { margin: 10px 0 8px; }
        .meta-table td { padding: 2px 6px 2px 0; font-size: 8.5px; vertical-align: top; }
        .meta-label { font-weight: bold; white-space: nowrap; width: 1%; padding-right: 8px; }
        .meta-value { padding-right: 20px; }
        .intro { margin: 8px 0 10px; font-size: 9px; line-height: 1.45; }
        .lines-table th, .lines-table td {
            border: 1px solid #333;
            padding: 4px 5px;
            font-size: 7.5px;
            vertical-align: top;
        }
        .th-maroon { background: #8B1538; color: #fff; font-weight: bold; }
        .th-green { background: #6BBF3F; color: #fff; font-weight: bold; }
        .category-row td {
            background: #E8E8E8;
            font-weight: bold;
            font-size: 8.5px;
            padding: 5px 6px;
            border: 1px solid #333;
        }
        .totals-row td {
            font-weight: bold;
            font-size: 8.5px;
            border: 1px solid #333;
            padding: 4px 5px;
        }
        .totals-label { text-align: right; }
        .totals-value { text-align: right; width: 12%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .subcontract { font-style: italic; font-size: 7px; }
        .legal-footer { font-size: 7.5px; line-height: 1.35; color: #444; }
        .page-break { page-break-before: always; }
        .terms-title { font-size: 11px; font-weight: bold; margin: 0 0 10px; }
        .terms-list { margin: 0; padding-left: 16px; font-size: 8.5px; line-height: 1.45; }
        .terms-list li { margin-bottom: 5px; }
        .closing { margin-top: 16px; font-size: 9px; line-height: 1.5; }
        .signature-block { margin-top: 28px; margin-bottom: 48px; font-size: 9px; line-height: 1.5; }
        .qr-cell { width: 55px; }
    </style>
</head>
<body>

{{-- Watermark is painted on every page by ReportWatermarkService::applyToPdf. --}}

{{--
  Amspec package layout:
    Per test: Test, Method, (LOQ/MU if toggled)
    Merged per package block: Quantity Required, TAT, No. of Samples, Unit Price, Total
  Total = No. of samples × Unit price (ONCE) — never × number of tests.
--}}
@include('layouts.lab.invoice.partials.amspec-quotation-header')

<table class="meta-table">
    <tr>
        <td class="meta-label">Laboratory Ref:</td>
        <td class="meta-value">{{ $laboratory_ref }}</td>
        <td class="meta-label">Date:</td>
        <td>{{ $quote_date ? \Carbon\Carbon::parse($quote_date)->format('m-d-Y') : '' }}</td>
    </tr>
    <tr>
        <td class="meta-label">Attention:</td>
        <td class="meta-value" colspan="3">{{ $attention }}</td>
    </tr>
    @if($customer_name !== '')
    <tr>
        <td class="meta-label">Customer:</td>
        <td class="meta-value" colspan="3">{{ $customer_name }}</td>
    </tr>
    @endif
    @if($customer_address !== '')
    <tr>
        <td class="meta-label">Address:</td>
        <td colspan="3">{{ $customer_address }}</td>
    </tr>
    @endif
    <tr>
        <td class="meta-label">Subject:</td>
        <td colspan="3"><strong>{{ $subject }}</strong></td>
    </tr>
    <tr>
        <td class="meta-label">Company Unit:</td>
        <td colspan="3">{{ $company_unit !== '' ? $company_unit : '-' }}</td>
    </tr>
    @if($sampling_location !== '')
    <tr>
        <td class="meta-label">Sampling Location:</td>
        <td colspan="3">{{ $sampling_location }}</td>
    </tr>
    @endif
</table>

<div class="intro">
    <p>{{ $intro_salutation }}</p>
    <p>{{ $intro_body }}</p>
</div>

@php
    $showMu = $show_mu_column ?? true;
    $showLoq = $show_loq_column ?? true;
    $showUnit = $show_unit_price_column ?? true;
    $colCount = 3 // sample + test + method
        + ($showMu ? 1 : 0)
        + ($showLoq ? 1 : 0)
        + 2 // quantity required + TAT
        + 1 // no. of samples
        + ($showUnit ? 1 : 0)
        + 1; // total
    $labelColspan = $colCount - 1;
@endphp

<table class="lines-table">
    <thead>
        <tr>
            <th class="th-maroon" style="width: 12%;">Sample Description</th>
            <th class="th-maroon" style="width: 18%;">Test Parameters</th>
            <th class="th-maroon" style="width: 14%;">Test Method</th>
            @if($showMu)
                <th class="th-green text-center" style="width: 7%;">Uncertainty</th>
            @endif
            @if($showLoq)
                <th class="th-maroon text-center" style="width: 7%;">LOQ</th>
            @endif
            <th class="th-maroon text-center" style="width: 9%;">Quantity Required</th>
            <th class="th-maroon text-center" style="width: 7%;">TAT (Working Days)</th>
            <th class="th-maroon text-center" style="width: 7%;">No. Of Samples</th>
            @if($showUnit)
                <th class="th-green text-right" style="width: 9%;">Unit Price ({{ $currency_code }})</th>
            @endif
            <th class="th-maroon text-right" style="width: 9%;">Total Price ({{ $currency_code }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grouped_lines as $group)
            @foreach($group['rows'] as $rowIndex => $row)
                @php
                    $emitCommercial = (bool) ($row['is_package_first'] ?? true);
                    $rowspan = max(1, (int) ($row['package_rowspan'] ?? 1));
                @endphp
                <tr>
                    @if($rowIndex === 0)
                        <td rowspan="{{ count($group['rows']) }}" style="font-weight: bold; vertical-align: top;">{{ $group['category'] }}</td>
                    @endif
                    <td>
                        {{ $row['test'] }}
                        @if(!empty($row['subcontracted']))
                            <span class="subcontract">(Subcontracted)</span>
                        @endif
                    </td>
                    <td>{{ $row['method'] ?: '-' }}</td>
                    @if($showMu)
                        <td class="text-center">{{ $row['mu'] ?: '-' }}</td>
                    @endif
                    @if($showLoq)
                        <td class="text-center">{{ $row['loq'] ?: '-' }}</td>
                    @endif
                    @if($emitCommercial)
                        <td class="text-center" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>{{ $row['quantity_required'] !== '' ? $row['quantity_required'] : '-' }}</td>
                        <td class="text-center" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>{{ $row['tat'] !== '' ? $row['tat'] : '-' }}</td>
                        <td class="text-center" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>{{ $row['quantity'] }}</td>
                        @if($showUnit)
                            <td class="text-right" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>{{ number_format($row['unit_price'], 2) }}</td>
                        @endif
                        <td class="text-right" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>{{ number_format($row['total_price'], 2) }}</td>
                    @endif
                </tr>
            @endforeach
        @endforeach
        <tr class="totals-row">
            <td colspan="{{ $labelColspan }}" class="totals-label">Net Amount</td>
            <td class="totals-value">{{ number_format($net_amount, 2) }}</td>
        </tr>
        <tr class="totals-row">
            <td colspan="{{ $labelColspan }}" class="totals-label">Vat Amount ({{ number_format($vat_rate, 2) }}%)</td>
            <td class="totals-value">{{ number_format($vat_amount, 2) }}</td>
        </tr>
        <tr class="totals-row">
            <td colspan="{{ $labelColspan }}" class="totals-label">Total Amount</td>
            <td class="totals-value">{{ number_format($total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<table style="margin-top: 10px;">
    <tr>
        <td class="legal-footer" style="width: 85%; vertical-align: top;">
            {{ $footer_text }}
        </td>
        <td class="qr-cell text-center" style="vertical-align: top;">
            @if(!empty($qrcode))
                <img src="data:image/svg+xml;base64,{{ $qrcode }}" width="50" height="50" alt="QR">
            @endif
        </td>
    </tr>
</table>

{{-- Page 2 --}}
<div class="page-break"></div>

@include('layouts.lab.invoice.partials.amspec-quotation-header')

<div class="terms-title">Terms and Conditions:</div>

<ol class="terms-list">
    @foreach($terms as $term)
        <li>{{ $term }}</li>
    @endforeach
</ol>

<div class="closing">
    <p>{{ $closing_text }}</p>
    <p>Thanking You,</p>
    <p>Yours faithfully,</p>
</div>

<div class="signature-block">
    <p><strong>For {{ $signature_company_name }}</strong></p>
    <p style="margin-top: 24px;">Authorized Signature</p>
    <p>Phone: {{ $company->telephone ?? '' }}</p>
    <p>Email: {{ $company->email ?? '' }}</p>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("DejaVu Sans");
        $pdf->page_text(500, 820, $text, $font, 8);
    }
</script>

</body>
</html>
