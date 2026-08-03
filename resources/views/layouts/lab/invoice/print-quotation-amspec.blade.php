<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $laboratory_ref }}</title>
    <style>
        @page { margin: 16mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; margin: 0; position: relative; }
        table { border-collapse: collapse; width: 100%; }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 55%;
            text-align: center;
            opacity: 0.08;
            z-index: -1;
            transform: translate(-50%, -50%) rotate(-35deg);
        }
        .watermark img { max-width: 100%; max-height: 220px; }
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
            font-size: 8px;
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
        .totals-value { text-align: right; width: 14%; }
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

@if(!empty($watermark_path) && file_exists($watermark_path))
    <div class="watermark">
        <img src="{{ $watermark_path }}" alt="">
    </div>
@endif

{{-- Page 1 --}}
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

<table class="lines-table">
    <thead>
        <tr>
            <th class="th-maroon" style="width: 14%;">Sample Description</th>
            <th class="th-maroon" style="width: 22%;">Test Parameters</th>
            <th class="th-maroon" style="width: 18%;">Test Method</th>
            @if($show_mu_column ?? true)
                <th class="th-green text-center" style="width: 9%;">Uncertainty</th>
            @endif
            @if($show_loq_column ?? true)
                <th class="th-maroon text-center" style="width: 9%;">LOQ</th>
            @endif
            @if($show_unit_price_column ?? true)
                <th class="th-green text-right" style="width: 11%;">Unit Price ({{ $currency_code }})</th>
            @endif
            <th class="th-maroon text-right" style="width: 11%;">Total Price ({{ $currency_code }})</th>
        </tr>
    </thead>
    <tbody>
        @php
            $legacyColCount = 4
                + (($show_mu_column ?? true) ? 1 : 0)
                + (($show_loq_column ?? true) ? 1 : 0)
                + (($show_unit_price_column ?? true) ? 1 : 0);
            $legacyLabelColspan = $legacyColCount - 1;
        @endphp
        @foreach($grouped_lines as $group)
            @foreach($group['rows'] as $rowIndex => $row)
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
                    @if($show_mu_column ?? true)
                        <td class="text-center">{{ $row['mu'] ?: '-' }}</td>
                    @endif
                    @if($show_loq_column ?? true)
                        <td class="text-center">{{ $row['loq'] ?: '-' }}</td>
                    @endif
                    @if($show_unit_price_column ?? true)
                        <td class="text-right">{{ number_format($row['unit_price'], 2) }}</td>
                    @endif
                    <td class="text-right">{{ number_format($row['total_price'], 2) }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr class="totals-row">
            <td colspan="{{ $legacyLabelColspan }}" class="totals-label">Net Amount</td>
            <td class="totals-value">{{ number_format($net_amount, 2) }}</td>
        </tr>
        <tr class="totals-row">
            <td colspan="{{ $legacyLabelColspan }}" class="totals-label">Vat Amount ({{ number_format($vat_rate, 2) }}%)</td>
            <td class="totals-value">{{ number_format($vat_amount, 2) }}</td>
        </tr>
        <tr class="totals-row">
            <td colspan="{{ $legacyLabelColspan }}" class="totals-label">Total Amount</td>
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
