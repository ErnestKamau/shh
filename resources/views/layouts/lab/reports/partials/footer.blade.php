<div class="footer">
    <table style="width: 100%; height: 100%; border-collapse: collapse;">
        <tr>
            <!-- QR Code Section -->
            <td style="width: 25%; text-align: left; vertical-align: bottom; padding: 0;">
                <div class="qr-section" style="text-align: left;">
                    <img src="data:image/svg+xml;base64,{{ $qrcode ?? '' }}" alt="QR Code" style="vertical-align: bottom;">
                    <br>
                    <small style="display: block; margin-top: 2px;">Scan to Verify Report</small>
                </div>
            </td>

            <!-- Center Accreditation Logo -->
            <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 0;">
                @php
                    // Show accreditation logo only when at least one parameter/test is accredited.
                    $hasAccreditedParameter = false;

                    if (isset($parameters) && $parameters) {
                        $hasAccreditedParameter = collect($parameters)
                            ->contains(function ($p) {
                                return (int) ($p->analyte_accredited ?? 0) === 1;
                            });
                    }

                    // If nothing is accredited, ensure we never fall back to a default logo file.
                    if (! $hasAccreditedParameter) {
                        $accreditation_logo = null;
                    }
                @endphp
                @php
                // Try variable from controller (already base64), then local file fallback
                $finalAccreditationLogo = (!empty($accreditation_logo) && str_starts_with($accreditation_logo, 'data:'))
                ? $accreditation_logo
                : null;

                if (!$finalAccreditationLogo) {
                    // Only fallback when we already decided accreditation applies.
                    if (! $hasAccreditedParameter) {
                        $finalAccreditationLogo = null;
                    } else {
                        $imagePath = public_path('images/sadc-ilac.jpeg');
                        if (file_exists($imagePath)) {
                            $finalAccreditationLogo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imagePath));
                        }
                    }
                }
                @endphp
                @if($finalAccreditationLogo)
                <img src="{{ $finalAccreditationLogo }}"
                    alt="SADCAS / ILAC Accreditation"
                    style="height: 80px; margin: 0 auto; display: inline-block; vertical-align: bottom;">
                @endif
            </td>

            <!-- Page Number Section (visual placeholder; numbers added via DomPDF script) -->
            <td style="width: 25%; text-align: center; vertical-align: bottom; padding: 0;">
            </td>
        </tr>
    </table>
</div>