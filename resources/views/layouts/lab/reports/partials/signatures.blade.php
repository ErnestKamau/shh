@if(isset($batch_approvers) && $batch_approvers->count() > 0)
<div class="signatures-section">
    @php
        // Attempt to find an analyst approver (title contains 'Analyst') and a final approver
        $analyst = $batch_approvers->first(function($a) {
            return stripos((string)($a->title ?? ''), 'analyst') !== false;
        }) ?? $batch_approvers->first();

        $finalApprover = $batch_approvers->first(function($a) {
            return stripos((string)($a->title ?? ''), 'author') !== false
                || stripos((string)($a->title ?? ''), 'approv') !== false
                || stripos((string)($a->title ?? ''), 'signatory') !== false;
        }) ?? $batch_approvers->last();
    @endphp

    {{-- Analyst (left) --}}
    @if($analyst)
    <div class="signature-block" style="text-align: left;">
        <div class="signature-name" style="font-weight: bold;">
            {{ $analyst->approvername }}
        </div>
        <div class="signature-title">Analyst</div>
        @if($analyst->getApproverDetails() && $analyst->getApproverDetails()->electronic_sig)
            <div class="signature-line" style="border-bottom: none; height: auto; margin: 8px 0 4px 0; justify-content: flex-start;">
                <img src="{{ signatureToDataUri($analyst->getApproverDetails()->electronic_sig) }}"
                     style="height:48px;z-index:-10;position:relative;" alt="Analyst signature">
            </div>
        @endif
        <div class="signature-date" style="font-size: 9px;">
            {{ date('d/m/Y', strtotime($analyst->approved_on ?? date('Y-m-d'))) }}
        </div>
    </div>
    @endif

    {{-- Final Approver (right) --}}
    @if($finalApprover)
    <div class="signature-block" style="text-align: right;">
        <div class="signature-name" style="font-weight: bold;">
            {{ $finalApprover->approvername }}
        </div>
        <div class="signature-title">
            {{ $finalApprover->title ?? 'Approver' }}
        </div>
        @if($finalApprover->getApproverDetails() && $finalApprover->getApproverDetails()->electronic_sig)
            <div class="signature-line" style="border-bottom: none; height: auto; margin: 8px 0 4px 0; justify-content: flex-end;">
                <img src="{{ signatureToDataUri($finalApprover->getApproverDetails()->electronic_sig) }}"
                     style="height:48px;z-index:-10;position:relative;" alt="Approver signature">
            </div>
        @endif
        <div class="signature-date" style="font-size: 9px;">
            {{ date('d/m/Y', strtotime($finalApprover->approved_on ?? date('Y-m-d'))) }}
        </div>
    </div>
    @endif
</div>
@endif

@if(isset($is_stamp) && $is_stamp)
<div class="stamp-area" style="position: fixed; bottom: 120px; right: 30px; z-index: 1100;">
    <img src="{{ isset($stamp) ? $stamp : '' }}" alt="Official Stamp" style="height: 80px;">
    <div class="stamp-date" style="color: red; font-weight: bold; text-align: center; margin-top: 5px; font-size: 10px;">{{ date('d M Y') }}</div>
</div>
@endif