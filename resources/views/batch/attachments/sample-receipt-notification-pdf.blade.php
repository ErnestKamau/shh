<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Receipt Notification (GCLA 01)</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
        body { font-size: 14px; color: #000; line-height: 1.6; padding: 30px; margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .main-header { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .sub-header { font-weight: bold; font-size: 15px; margin-bottom: 20px; text-decoration: underline; }
        .form-code { font-weight: bold; font-size: 12px; margin-bottom: 20px; }
        
        .list-container { margin-top: 30px; }
        .list-item { margin-bottom: 15px; clear: both; }
        .list-number { float: left; width: 25px; font-weight: bold; }
        .list-content { margin-left: 25px; }
        
        .dotted-line { display: inline-block; border-bottom: 1px dashed #000; min-height: 20px; vertical-align: bottom; }
        .full-width-line { width: 100%; margin-top: 25px; }
        .inline-label { margin-right: 10px; }
        
        .signature-img { max-height: 30px; max-width: 150px; vertical-align: bottom; }
        .stamp-area { text-align: right; margin-top: 60px; margin-right: 30px; font-style: italic; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { vertical-align: middle; }
        .logo-td { width: 80px; }
        .logo { width: 70px; height: auto; }
        .title-td { text-align: center; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-td">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" class="logo" alt="Logo">
                @else
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
                @endif
            </td>
            <td class="title-td">
                <div class="main-header">THE UNITED REPUBLIC OF TANZANIA</div>
                <div class="main-header" style="font-size: 15px;">GOVERNMENT CHEMIST LABORATORY AUTHORITY</div>
            </td>
            <td style="width: 100px; text-align: right; vertical-align: middle;">
                <div class="form-code" style="margin-bottom: 5px;">GCLA 01</div>
                @if(!empty($reportLogoSrc))
                    <img src="{{ $reportLogoSrc }}" class="logo" alt="Report Logo" style="width: 70px; height: auto;">
                @else
                    <img src="{{ public_path('images/logo-report.png') }}" class="logo" alt="Report Logo" style="width: 70px; height: auto;">
                @endif
            </td>
        </tr>
    </table>
    
    <div class="text-center sub-header" style="margin-top: 10px;">SAMPLE RECEIPT NOTIFICATION</div>
    
    <div class="list-container">
        
        <div class="list-item">
            <div class="list-number">1.</div>
            <div class="list-content">
                Name of the client or submitting authority 
                <div class="dotted-line" style="width: 60%;">{{ $form['client_or_authority_name'] ?? '' }}</div>
            </div>
        </div>
        
        <div class="list-item">
            <div class="list-number">2.</div>
            <div class="list-content">
                Description of sample(s) 
                <div class="dotted-line full-width-line">{{ $form['sample_description'] ?? '' }}</div>
            </div>
        </div>
        
        <div class="list-item">
            <div class="list-number">3.</div>
            <div class="list-content">
                Name of the person submitting the sample or exhibit 
                <div class="dotted-line full-width-line">{{ $form['submitter_name'] ?? '' }}</div>
                
                <div style="margin-top: 15px;">
                    <span class="inline-label">Designation</span>
                    <span class="dotted-line" style="width: 40%;">{{ $form['submitter_designation'] ?? '' }}</span>
                    
                    <span class="inline-label" style="margin-left: 20px;">Signature</span>
                    <span class="dotted-line" style="width: 30%;">
                        @if(!empty($form['submitter_signature']))
                            <img src="{{ $form['submitter_signature'] }}" class="signature-img" alt="signature">
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        <div class="list-item" style="margin-top: 25px;">
            <div class="list-number">4.</div>
            <div class="list-content">
                Laboratory identification number (Lab. No.) 
                <div class="dotted-line" style="width: 50%;">{{ $form['laboratory_identification_number'] ?? '' }}</div>
            </div>
        </div>
        
        <div class="list-item">
            <div class="list-number">5.</div>
            <div class="list-content">
                Number of samples 
                <div class="dotted-line" style="width: 30%;">{{ $form['number_of_samples'] ?? '' }}</div>
            </div>
        </div>
        
        <div class="list-item">
            <div class="list-number">6.</div>
            <div class="list-content">
                Name of the receiving person 
                <div class="dotted-line full-width-line">{{ $form['receiver_name'] ?? '' }}</div>
                
                <div style="margin-top: 15px;">
                    <span class="inline-label">Designation</span>
                    <span class="dotted-line" style="width: 40%;">{{ $form['receiver_designation'] ?? '' }}</span>
                    
                    <span class="inline-label" style="margin-left: 20px;">Signature</span>
                    <span class="dotted-line" style="width: 30%;">
                        @if(!empty($form['receiver_signature']))
                            <img src="{{ $form['receiver_signature'] }}" class="signature-img" alt="signature">
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        <div class="list-item" style="margin-top: 25px;">
            <div class="list-number">7.</div>
            <div class="list-content">
                Sample receiving date 
                <div class="dotted-line" style="width: 40%;">{{ $form['sample_receiving_date'] ?? '' }}</div>
            </div>
        </div>
        
    </div>
    
    <div class="stamp-area">
        (Official Stamp / Seal)
    </div>

</body>
</html>
