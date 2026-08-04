{{-- Notes → amendment → analysis meta → signature → end of text (repeated on every PDF page) --}}
@foreach($footerNotesBodies ?? [] as $notesBody)
<div class="sample-notes">
    <strong>Notes:</strong> {!! $notesBody !!}
</div>
@endforeach

@if(!empty($ammendment?->id))
<div class="sample-amendment">
    <div><strong>{{ $supersedesText }}</strong></div>
    @if(!empty($amendmentRevisionLabel))
    <div style="margin-top:4px;">
        <strong>{{ $revisionLabel }}:</strong>
        {{ $amendmentRevisionLabel }}
        @if(!empty($reportNumber))
            <span style="color:#555;">({{ $reportNumber }})</span>
        @endif
    </div>
    @endif
    <div style="margin-top:4px;">
        <strong>{{ $reasonLabel }}:</strong>
        {{ $ammendment->reason }}
    </div>
</div>
@endif

<table class="meta-box">
    <tr>
        <td>
            {{ $labels['analysis_conducted'] }}: {{ $batch->getLabSectionsNames() ?: 'Laboratory' }}
        </td>
        <td>
            {{ $labels['test_method_dev'] }}
        </td>
    </tr>
</table>

<div class="sig-section">
    <div class="sig-intro">
        {{ $labels['signed_behalf'] }} {{ $company->name ?? 'AmSpec Inspection &amp; Testing Services' }}
    </div>

    <table class="sig-block">
        <tr>
            <td class="sig-left">
                <div class="sig-name">{{ $approverUser->name ?? '&nbsp;' }}</div>
                <div class="sig-title-line">
                    {{ $approverRole ?? '&nbsp;' }}
                    @if($company->location ?? null) | {{ $company->location }} @endif
                </div>
                <div class="sig-company-line">{{ $company->name ?? '&nbsp;' }}</div>
                <div class="sig-image">
                    @if(!empty($signatureSrc))
                        <img src="{{ $signatureSrc }}" alt="Signature">
                    @else
                        <span style="color:#aaa;font-size:9px;font-style:italic;">{{ $labels['no_signature'] }}</span>
                    @endif
                </div>
                <div class="sig-underline"></div>
            </td>
            <td class="sig-right">
                @if($companyLogo)
                    <img src="{{ $companyLogo }}" alt="{{ $company->name ?? 'AmSpec' }}">
                @endif
            </td>
        </tr>
    </table>
</div>

@if(!empty($reportLogos['bottom_left']) || !empty($reportLogos['bottom_right']))
<table class="bottom-logos">
    <tr>
        <td>
            @if(!empty($reportLogos['bottom_left']))
                <img src="{{ $reportLogos['bottom_left']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}">
            @endif
        </td>
        <td>
            @if(!empty($reportLogos['bottom_right']))
                <img src="{{ $reportLogos['bottom_right']['src'] }}" alt="{{ $company->name ?? 'AmSpec' }}">
            @endif
        </td>
    </tr>
</table>
@endif

<div class="end-text">{{ $labels['end_of_text'] }}</div>
