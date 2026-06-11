@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
    $termsUrl = $copy['terms_url'] ?? '';
    $isPdf = (bool) ($forPdf ?? false);
@endphp
<div class="amspec-footer-wrap">
    <table class="amspec-footer" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <tr>
            <td class="amspec-footer-text" @if($isPdf) width="78%" @endif>
                <p class="amspec-footer-disclaimer">
                    This document is issued by the Company subject to the Terms and Conditions at
                    @if($termsUrl !== '')
                        <a href="{{ $termsUrl }}" style="color: {{ $primaryColor }};" target="_blank" rel="noopener">{{ $termsUrl }}</a>.
                    @else
                        the applicable terms and conditions.
                    @endif
                    Any holder of this document is advised that information contained herein reflects the Company's findings at the time and place of its intervention only and within the scope of the Client's instructions. The Company's sole responsibility is to its Client and the Company disclaims any liability to third parties. Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
                </p>
            </td>
            <td class="amspec-footer-qr-cell" @if($isPdf) width="22%" @endif>
                @if(!empty($qrCode))
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="Scan to view quotation" class="amspec-footer-qr" @if($isPdf) width="52" height="52" @endif>
                @endif
            </td>
        </tr>
    </table>
</div>
