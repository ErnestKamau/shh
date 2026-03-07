<div class="footer">
    <div style="display: table; width: 100%; height: 100%;">
        <!-- QR Code Section -->
        <div style="display: table-cell; width: 25%; text-align: left; vertical-align: bottom; padding-left: 0;">
            <div class="qr-section" style="text-align: left; margin: 0;">
                <img src="data:image/svg+xml;base64,{{ $qrcode ?? '' }}" alt="QR Code">
                <br>
                <small>Scan to Verify Report</small>
            </div>
        </div>

        <!-- SADCAS Logo with Caption -->
        <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
            <div style="text-align: center;">
                <img src="{{ $sadc_logo ?? '' }}" alt="SADCAS Logo" style="height: 55px; margin: 0;">
                <div style="margin-top: 2px; font-size: 10px; font-weight: bold;">
                    TEST-1 0028<br>
                    VET 009
                </div>
            </div>
        </div>

        <!-- ILAC Logo -->
        <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
            <div style="text-align: center;">
                <img src="{{ $ilac_logo ?? '' }}" alt="ILAC Logo" style="height: 80px; margin: 0;">
            </div>
        </div>

        <!-- Page Number Section -->
        <div style="display: table-cell; width: 25%; text-align: center; vertical-align: bottom;">
        </div>
    </div>
</div>