<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laboratory Analysis Acceptance Form (GCLA/F/63)</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
        body { font-size: 11px; color: #000; line-height: 1.4; padding: 20px; margin: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-td { width: 80px; }
        .logo { width: 70px; height: auto; }
        .title-td { text-align: center; }
        .title { font-size: 13px; font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .subtitle { font-size: 12px; font-weight: bold; }
        .official-use-td { width: 150px; text-align: right; }
        .official-use-box { border: 1px solid #000; padding: 5px; text-align: left; display: inline-block; width: 100%; }
        .official-use-title { font-weight: bold; text-decoration: underline; font-size: 10px; margin-bottom: 10px; }
        .official-use-line { margin-top: 15px; border-bottom: 1px dashed #000; }
        
        .part-title { font-weight: bold; font-size: 12px; margin-top: 15px; margin-bottom: 5px; text-transform: uppercase; }
        
        .form-row { margin-bottom: 8px; clear: both; }
        .form-label { display: inline-block; }
        .form-value { display: inline-block; border-bottom: 1px dashed #000; min-width: 50px; }
        
        table.grid { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 5px; }
        table.grid th { font-weight: bold; text-align: left; }
        
        .signature-box { display: inline-block; height: 30px; }
        .signature-img { max-height: 30px; max-width: 150px; }
        
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; text-align: center; line-height: 12px; font-size: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-td">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </td>
            <td class="title-td">
                <div class="title">Laboratory Analysis Acceptance Form</div>
                <div class="subtitle">GCLA/F/63</div>
            </td>
            <td class="official-use-td">
                <div class="official-use-box">
                    <div class="official-use-title">Official Use only</div>
                    <div>Lab No.<div class="official-use-line">{{ $batch->batch_code }}</div></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="part-title">PART A: SAMPLE DETAILS</div>
    
    <div class="form-row">
        <span class="form-label">Name of customer</span>
        <span class="form-value" style="width: 80%;">{{ $form['customer_name'] ?? '' }}</span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Address</span>
        <span class="form-value" style="width: 50%;">{{ $form['customer_address'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Email</span>
        <span class="form-value" style="width: 30%;">{{ $form['customer_email'] ?? '' }}</span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Number of samples</span>
        <span class="form-value" style="width: 15%;">{{ $form['number_of_samples'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Mode of work:</span>
        <span class="form-label" style="margin-left: 5px;">Normal</span>
        <span class="checkbox">{{ ($form['mode_of_work'] ?? '') === 'Normal' ? 'X' : '' }}</span>
        <span class="form-label" style="margin-left: 5px;">Express</span>
        <span class="checkbox">{{ ($form['mode_of_work'] ?? '') === 'Express' ? 'X' : '' }}</span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Type of samples</span>
        <span class="form-value" style="width: 40%;">{{ $form['type_of_samples'] ?? '' }}</span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Date of sampling (if applicable)</span>
        <span class="form-value" style="width: 20%;">{{ $form['date_of_sampling'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Date</span>
        <span class="form-value" style="width: 15%;">{{ $form['date'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Tel</span>
        <span class="form-value" style="width: 20%;">{{ $form['tel'] ?? '' }}</span>
    </div>
    
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">S/No</th>
                <th style="width: 45%;">Parameter(s) Requested</th>
                <th style="width: 15%; text-align: center;">Accept (&radic;)</th>
                <th style="width: 15%; text-align: center;">Reject (x)</th>
                <th style="width: 20%;">Amount TZS</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; @endphp
            @foreach($requestedParameters as $row)
                <tr>
                    <td style="text-align: center;">{{ $count++ }}</td>
                    <td>{{ $row['label'] ?? '' }}</td>
                    <td style="text-align: center;">
                        @if(!empty($row['selected'])) &radic; @endif
                    </td>
                    <td style="text-align: center;">
                        @if(empty($row['selected'])) x @endif
                    </td>
                    <td>
                        @if(!empty($row['selected']))
                            {{ number_format((float) ($row['price'] ?? 0), 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
            @for($i = $count; $i <= max(5, $count); $i++)
                <tr>
                    <td style="text-align: center; color: white;">.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="font-weight: bold;">{{ number_format((float) $acceptedTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="form-row" style="margin-bottom: 20px;">
        <span class="form-label">Any deviation from specified conditions?</span>
        <span class="form-label" style="margin-left: 20px;">Yes</span>
        <span class="checkbox">{{ ($form['deviation_answer'] ?? '') === 'Yes' ? 'X' : '' }}</span>
        <span class="form-label" style="margin-left: 10px;">No</span>
        <span class="checkbox">{{ ($form['deviation_answer'] ?? '') === 'No' ? 'X' : '' }}</span>
    </div>
    
    <div class="part-title">PART B: CUSTOMER CERTIFIED</div>
    <div class="form-row">
        <span class="form-label">{{ $form['customer_certification_text'] ?? 'I certify that the above request is correct.' }}</span>
    </div>
    
    <div class="form-row" style="margin-top: 15px;">
        <span class="form-label">Name</span>
        <span class="form-value" style="width: 40%;">{{ $form['customer_name_certified'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Signature</span>
        <span class="form-value" style="width: 20%;">
            @if(!empty($form['customer_signature']))
                <img src="{{ $form['customer_signature'] }}" class="signature-img" alt="signature">
            @endif
        </span>
        
        <span class="form-label" style="margin-left: 10px;">Date</span>
        <span class="form-value" style="width: 15%;">{{ $form['customer_date'] ?? '' }}</span>
    </div>
    
    <div class="part-title">PART C: CONFORMITY ASSESSMENT</div>
    <div class="form-row">
        <span class="form-label">Customer requests statement of conformity?</span>
        <span class="form-label" style="margin-left: 20px;">Yes</span>
        <span class="checkbox">{{ ($form['conformity_request'] ?? '') === 'requested' ? 'X' : '' }}</span>
        <span class="form-label" style="margin-left: 10px;">No</span>
        <span class="checkbox">{{ ($form['conformity_request'] ?? '') === 'not_requested' ? 'X' : '' }}</span>
    </div>
    
    <div class="part-title">PART D: LABORATORY MANAGER</div>
    <div class="form-row">
        <span class="form-label">I certify that the laboratory</span>
        <span class="form-label" style="margin-left: 10px;">Has</span>
        <span class="checkbox">{{ ($form['manager_capability'] ?? '') === 'has' ? 'X' : '' }}</span>
        <span class="form-label" style="margin-left: 10px;">Has not</span>
        <span class="checkbox">{{ ($form['manager_capability'] ?? '') === 'has_not' ? 'X' : '' }}</span>
        <span class="form-label" style="margin-left: 10px;">capability and resources.</span>
    </div>
    
    <div class="form-row" style="margin-top: 15px;">
        <span class="form-label">Laboratory</span>
        <span class="form-value" style="width: 30%;">{{ $form['laboratory_name'] ?? '' }}</span>
        
        <span class="form-label" style="margin-left: 10px;">Name</span>
        <span class="form-value" style="width: 25%;">{{ $form['laboratory_manager_name'] ?? '' }}</span>
    </div>
    
    <div class="form-row" style="margin-top: 15px;">
        <span class="form-label">Signature</span>
        <span class="form-value" style="width: 25%;">
            @if(!empty($form['manager_signature']))
                <img src="{{ $form['manager_signature'] }}" class="signature-img" alt="signature">
            @endif
        </span>
        
        <span class="form-label" style="margin-left: 10px;">Date</span>
        <span class="form-value" style="width: 20%;">{{ $form['manager_date'] ?? '' }}</span>
    </div>
</body>
</html>
