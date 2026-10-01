{{-- T&C disclaimer (+ per-sample QR slot); PDF page numbers and QR are drawn via DomPDF canvas --}}
@php
    $legalTableClass = !empty($isPdfMode) ? 'pdf-footer-legal' : 'page-disclaimer';
    $showFooterQrSlot = !empty($isPdfMode) && !empty($hasFooterQr);
@endphp
<table @class([$legalTableClass, 'pdf-footer-legal-with-qr' => $showFooterQrSlot])>
    <tr>
        <td class="{{ !empty($isPdfMode) ? 'pdf-footer-disclaimer' : 'disclaimer-text' }}">
            This document is issued by the Company subject to the Terms and Conditions at
            https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that
            information contained herein reflects the Company&#8217;s findings at the time and place of its
            intervention only and within the scope of the Client&#8217;s instructions. The Company&#8217;s sole
            responsibility is to its Client and the Company disclaims any liability to third parties.
            Any alteration, forgery or falsification of the content or appearance of this document is unlawful.
        </td>
        @if($showFooterQrSlot)
        <td class="pdf-footer-qr-slot"></td>
        @endif
    </tr>
</table>
