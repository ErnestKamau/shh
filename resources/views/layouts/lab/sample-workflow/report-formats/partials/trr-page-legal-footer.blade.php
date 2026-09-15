{{-- T&C disclaimer + QR; PDF page numbers are drawn via DomPDF canvas above this band --}}
@php
    $legalTableClass = !empty($isPdfMode) ? 'pdf-footer-legal' : 'page-disclaimer';
@endphp
<table class="{{ $legalTableClass }}">
    <tr>
        <td class="{{ !empty($isPdfMode) ? 'pdf-footer-disclaimer' : 'disclaimer-text' }}">
            This document is issued by the Company subject to the Terms and Conditions at
            https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that
            information contained herein reflects the Company&#8217;s findings at the time and place of its
            intervention only and within the scope of the Client&#8217;s instructions. The Company&#8217;s sole
            responsibility is to its Client and the Company disclaims any liability to third parties.
            Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
        </td>
        <td class="{{ !empty($isPdfMode) ? 'pdf-footer-qr' : 'disclaimer-qr' }}">
            @if(!empty($footerQrCode))
                <img src="{{ $footerQrCode }}" alt="Report QR Code">
            @endif
        </td>
    </tr>
</table>
