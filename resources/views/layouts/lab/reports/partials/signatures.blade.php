@if(isset($batch_approvers) && $batch_approvers->count() > 0)
<div class="section-title">{{ $custom_title ?? 'Signatures & Approvals' }}</div>
<div class="signatures-section">
    @foreach($batch_approvers as $approver)
    <div class="signature-block">
        <div class="signature-line">
            @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
            <center>
                <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}" style="height:48px;z-index:-10;position:relative;" alt="signature">
            </center>
            @endif
        </div>
        <div class="signature-title">{{ $approver->title ?? 'Analyst' }}</div>
        @if($approver->getApproverDetails() && $approver->getApproverDetails()->electronic_sig)
        <div class="signature-name">{{ $approver->approvershortname ?? $approver->approvername }}</div>
        @else
        <div class="signature-name">{{ $approver->approvershortname ?? $approver->approvername }} – Signature</div>
        @endif
        <div class="signature-date">{{ date('d/m/Y', strtotime($approver->approved_on ?? date('Y-m-d'))) }}</div>
    </div>
    @endforeach
</div>
@endif

@if(isset($is_stamp) && $is_stamp)
<div class="stamp-area" style="position: fixed; bottom: 120px; right: 30px; z-index: 1100;">
    <img src="{{ isset($stamp) ? $stamp : '' }}" alt="Official Stamp" style="height: 80px;">
    <div class="stamp-date" style="color: red; font-weight: bold; text-align: center; margin-top: 5px; font-size: 10px;">{{ date('d M Y') }}</div>
</div>
@endif