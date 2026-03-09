<div class="section-title">{{ $custom_title ?? 'Interpretation and Comments' }}</div>
<div class="interpretation-section">
    @if(isset($report_type))
    <p><strong>Report Type:</strong> {{ $report_type }}</p>
    @endif

    @if(isset($ammendment))
    <p><strong>Amendment Reason:</strong> {{ $ammendment->reason ?? 'N/A' }}</p>
    @endif

    @if(isset($reportFormat) && $reportFormat->getDetail('methodology_statement'))
    <p>{!! nl2br(e($reportFormat->getDetail('methodology_statement'))) !!}</p>
    @else
    <p>This certificate relates only to the samples tested. Results are valid at the time of testing. Samples were analyzed under controlled laboratory conditions.</p>
    @endif
</div>

@if(isset($reportFormat) && $reportFormat->getDetail('footer_disclaimer'))
<div class="section-title">Disclaimers</div>
<div class="disclaimer">
    {!! nl2br(e($reportFormat->getDetail('footer_disclaimer'))) !!}
</div>
@endif

<div class="disclaimer-" style="margin-top: 10px; font-size: 8px;">
    Information marked * has been provided by the customer.
</div>