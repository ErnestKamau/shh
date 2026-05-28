<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>GCLA 02 Form - {{ $batch->batch_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Standard GCLA Double-Header Layout */
        .header-table {
            width: 100%;
            margin-bottom: 5px;
            border: none;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .header-text {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        
        .logo-img {
            height: 90px;
            width: auto;
        }

        /* Document Title */
        .doc-title-table {
            width: 100%;
            border: none;
            margin: 10px 0 15px 0;
        }
        .doc-title-table td {
            border: none;
            padding: 0;
        }
        
        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        
        .doc-ref {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }

        /* Section Headings */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 8px 0;
        }
        
        .section-text {
            font-size: 11px;
            margin-bottom: 8px;
            text-align: justify;
        }

        /* Identification Table Grid */
        .id-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0 15px 0;
        }
        
        .id-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 11px;
        }

        /* Results Table Grid */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
        }
        .results-table th {
            border: 1px solid #000;
            padding: 6px;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
        }
        .results-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
            vertical-align: top;
            text-align: center;
        }

        /* Signature block: Grid */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .signature-table th, .signature-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            width: 33.33%;
        }
        .signature-table th {
            font-weight: bold;
            font-size: 11px;
        }
        .sig-space {
            height: 40px;
        }
        .sig-name {
            font-weight: bold;
            margin-top: 5px;
            font-size: 11px;
        }
        .sig-title {
            font-size: 10px;
        }

        /* Footer */
        .footer-note {
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            text-align: center;
            border: 1px solid #000;
        }
        .footer-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 25%;
            left: 20%;
            width: 60%;
            opacity: 0.1;
            z-index: -1000;
        }
    </style>
</head>
<body>
    @if(isset($gcla_logo) && $gcla_logo)
        <img src="{{ $gcla_logo }}" class="watermark" alt="Watermark">
    @endif

    @php
        $isSwahili = $language === 'sw';
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: left;">
                @if(isset($coat_of_arms) && $coat_of_arms)
                    <img src="{{ $coat_of_arms }}" class="logo-img" alt="Coat of Arms">
                @endif
            </td>
            <td style="width: 50%;" class="header-text">
                {{ $isSwahili ? 'MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI' : 'GOVERNMENT CHEMIST LABORATORY AUTHORITY' }}
            </td>
            <td style="width: 25%; text-align: right;">
                @if(isset($gcla_logo) && $gcla_logo)
                    <img src="{{ $gcla_logo }}" class="logo-img" alt="GCLA Logo">
                @endif
            </td>
        </tr>
    </table>
    
    <table class="doc-title-table">
        <tr>
            <td style="width: 25%;"></td>
            <td style="width: 50%;" class="doc-title">
                {{ $isSwahili ? 'HATI YA UCHUNGUZI' : 'CERTIFICATE OF ANALYSIS' }}
            </td>
            <td style="width: 25%;" class="doc-ref">
                GCLA 02
            </td>
        </tr>
    </table>

    <div class="section-title">
        {{ $isSwahili ? 'A. Utambuzi' : 'A. Identification' }}
    </div>
    
    <table class="id-table">
        <tr>
            <td style="width: 35%;">1. <b>Lab. No.</b> {{ $batch->batch_code }}</td>
            <td style="width: 35%;">2. <b>{{ $isSwahili ? 'Tarehe ya hati' : 'Report Date' }}:</b> {{ $processing_date }}</td>
            <td style="width: 30%;">3. <b>{{ $isSwahili ? 'Kumb. Na. yetu' : 'Our Ref. No' }}:</b> {{ $batch->reference_number }}</td>
        </tr>
        <tr>
            <td colspan="3">4. <b>{{ $isSwahili ? 'Aina ya kielelezo/sampuli' : 'Sample Type' }}:</b> {{ $batch->sample_type->name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2">5. <b>{{ $isSwahili ? 'Tarehe ya kupokelewa' : 'Date Received' }}:</b> {{ $receipt_date }}</td>
            <td>
                6. <b>{{ $isSwahili ? 'Kumb. Na. yako' : 'Your Ref. No' }}:</b> {{ $batch->kra_office_ref ?? '' }}<br>
                @if($batch->file_no)
                    <b>{{ $isSwahili ? 'Jalada Na.' : 'File No.' }}</b> {{ $batch->file_no }}
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3">7. <b>{{ $isSwahili ? 'Jina la aliyeleta kielelezo/sampuli' : 'Submitted By' }}:</b> {{ $customer->name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="3">8. <b>{{ $isSwahili ? 'Anwani' : 'Address' }}:</b> {{ $customer->physical_address ?? ($customer->postal_address ?? '') }}</td>
        </tr>
        <tr>
            <td colspan="3">9. <b>{{ $isSwahili ? 'Uchunguzi ulioombwa' : 'Requested Analysis' }}:</b> {{ $tests_requested }}</td>
        </tr>
    </table>

    <div class="section-title">
        {{ $isSwahili ? 'B. Matokeo ya Uchunguzi' : 'B. Results of Analysis' }}
    </div>
    
    <div class="section-text">
        {{ $isSwahili 
            ? 'Uchunguzi wa kielelezo umefanyika kwa mujibu wa mbinu za uchunguzi husika na matokeo yake yanahusisha kielelezo kilichowasilishwa na kupokelewa tu na Mamlaka kwa ajili ya uchunguzi kama yalivyoainishwa kwenye jedwali Na.1.' 
            : 'The analysis of the sample has been conducted according to the relevant test methods and the results apply only to the sample as received by the Authority for analysis as detailed in Table 1.' 
        }}
    </div>
    
    <div class="section-text" style="font-weight: bold;">
        {{ $isSwahili ? 'Jedwali Na. 1' : 'Table 1' }}: {{ $isSwahili ? 'Matokeo ya Uchunguzi wa Kimaabara wa Sampuli' : 'Laboratory Test Results for the Sample' }}: {{ $sample_description }}
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%;">Na.</th>
                <th style="width: 20%;">{{ $isSwahili ? 'KIELELEZO' : 'SAMPLE REF' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MUONEKANO WA SAMPULI' : 'SAMPLE APPEARANCE' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MATOKEO YA UCHUNGUZI' : 'TEST RESULTS' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MBINU ZA UCHUNGUZI' : 'TEST METHODS' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($samples_data as $index => $sample_data)
                @php 
                    $rowspan = count($sample_data['results']) > 0 ? count($sample_data['results']) : 1;
                @endphp
                
                @if($rowspan == 1 && count($sample_data['results']) == 0)
                    <tr>
                        <td style="vertical-align: middle;">{{ $index + 1 }}</td>
                        <td style="vertical-align: middle;">{{ $sample_data['sample_code'] }}</td>
                        <td style="vertical-align: middle;">{{ $sample_data['appearance'] }}</td>
                        <td style="vertical-align: middle;">-</td>
                        <td style="vertical-align: middle;">-</td>
                    </tr>
                @else
                    @foreach($sample_data['results'] as $r_idx => $result)
                        <tr>
                            @if($r_idx == 0)
                                <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $index + 1 }}</td>
                                <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $sample_data['sample_code'] }}</td>
                                <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $sample_data['appearance'] }}</td>
                            @endif
                            
                            <td style="vertical-align: middle;">
                                {{ $result['analyte'] }}:<br>
                                <b>{{ $result['value'] }} {{ $result['unit'] }}</b>
                            </td>
                            <td style="vertical-align: middle;">{{ $result['method'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="section-text" style="margin-top: 15px;">
        <b>{{ $isSwahili ? 'NB' : 'NB' }}:</b> {!! nl2br(e($nb_notes ?? '')) !!}
    </div>

    <div class="section-title" style="margin-top: 20px;">
        {{ $isSwahili ? 'C. Maoni ya Kitaalamu' : 'C. Professional Opinion' }}
    </div>
    
    <div class="section-text">
        {!! nl2br(e($comments ?? '')) !!}
    </div>
    
    <div style="text-align: center; font-weight: bold; margin-top: 20px; font-size: 12px;">
        {{ $isSwahili ? 'MWISHO WA RIPOTI' : 'END OF REPORT' }}
    </div>

    <table class="signature-table">
        <thead>
            <tr>
                <th>{{ $isSwahili ? 'Imefanywa na:' : 'Analyzed by:' }}</th>
                <th>{{ $isSwahili ? 'Imethibitishwa na:' : 'Verified by:' }}</th>
                <th>{{ $isSwahili ? 'Imeidhinishwa na:' : 'Approved by:' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="sig-space">
                        @if($analyst && $analyst['signature'])
                            <img src="{{ $analyst['signature'] }}" style="max-height: 40px;" alt="Signature">
                        @endif
                    </div>
                    <div class="sig-name">{{ $analyst['name'] ?? '' }}</div>
                    <div class="sig-title">{{ $analyst['title'] ?? ($isSwahili ? 'Mkemia' : 'Chemist') }}</div>
                </td>
                <td>
                    <div class="sig-space">
                        @if($verifier && $verifier['signature'])
                            <img src="{{ $verifier['signature'] }}" style="max-height: 40px;" alt="Signature">
                        @endif
                    </div>
                    <div class="sig-name">{{ $verifier['name'] ?? '' }}</div>
                    <div class="sig-title">{{ $verifier['title'] ?? ($isSwahili ? 'Meneja Maabara' : 'Lab Manager') }}</div>
                </td>
                <td>
                    <div class="sig-space">
                        @if($approver && $approver['signature'])
                            <img src="{{ $approver['signature'] }}" style="max-height: 40px;" alt="Signature">
                        @endif
                    </div>
                    <div class="sig-name">{{ $approver['name'] ?? '' }}</div>
                    <div class="sig-title">{{ $approver['title'] ?? ($isSwahili ? 'Mkurugenzi' : 'Director') }}</div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        {{ $isSwahili 
            ? 'Angalizo: Ripoti hii imetolewa bila marekebisho/mabadiliko na isitolewe kwa aina yoyote ile bila kibali cha Mkemia Mkuu wa Serikali.' 
            : 'Note: This report is issued without alteration and shall not be reproduced in any form without written permission from the Chief Government Chemist.' 
        }}
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 25%;">5 Barack Obama</td>
            <td style="width: 25%;">S.L.P. 164,<br>Dar es Salaam Tanzania</td>
            <td style="width: 25%;">Simu: +255 22 2113383/4<br>Simu/Nukushi: +255 22 2113320</td>
            <td style="width: 25%;">Baruapepe: gcla@gcla.go.tz<br>Tovuti: www.gcla.go.tz</td>
        </tr>
    </table>

</body>
</html>
