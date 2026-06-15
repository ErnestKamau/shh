@php
    $forPdf = true;
    $reportViewData = app(\App\Services\Billing\QuotationReportService::class)
        ->buildViewData(\App\QuotationHeader::findOrFail($header->id ?? request()->route('id')), true);
@endphp

@include('billing.quotations.amspec.pdf', $reportViewData)
