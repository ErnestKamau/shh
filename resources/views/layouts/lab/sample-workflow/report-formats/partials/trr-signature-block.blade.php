<div class="sig-intro">
    {{ $labels['signed_behalf'] }} {{ $labels['signed_org'] ?? 'AMSPEC' }}
</div>

<div class="sig-name">{{ $approverUser->name ?? '&nbsp;' }}</div>
<div class="sig-title-line">{{ $approverRole ?? '&nbsp;' }}</div>
<div class="sig-company-line">{{ $company->name ?? '&nbsp;' }}</div>
<div class="sig-image-box">
    @if(!empty($signatureSrc))
        <img src="{{ $signatureSrc }}" alt="Signature">
    @else
        <span class="sig-missing">{{ $labels['no_signature'] }}</span>
    @endif
</div>
