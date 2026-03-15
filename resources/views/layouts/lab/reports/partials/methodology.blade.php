<div class="section-title">Disclaimers</div>
<div class="interpretation-section">
    @if(isset($ammendment))
    <p><strong>Amendment Reason:</strong> {{ $ammendment->reason ?? 'N/A' }}</p>
    @endif

    @if(isset($reportFormat) && $reportFormat->getDetail('methodology_statement'))
    {!! $reportFormat->getDetail('methodology_statement') !!}
    @else
    <p>This certificate relates only to the samples tested. Results are valid at the time of testing. Samples were analyzed under controlled laboratory conditions.</p>
    @endif
</div>

@if(isset($reportFormat) && $reportFormat->getDetail('footer_disclaimer'))
<div class="section-title">Statement of Conformity:</div>
<div class="disclaimer">
    {!! $reportFormat->getDetail('footer_disclaimer') !!}
</div>
@endif

<div class="disclaimer-" style="margin-top: 10px; font-size: 8px;">
    Information marked * has been provided by the customer.
</div>