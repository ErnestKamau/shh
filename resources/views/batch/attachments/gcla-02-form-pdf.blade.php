<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>GCLA 02 Form - {{ $batch->batch_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
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

        /* Top Header */
        .top-header {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
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
        
        .logo-img {
            height: 90px;
            width: auto;
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
            vertical-align: middle;
            text-align: center;
        }

        /* Signature block: Grid */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
            font-weight: normal;
            margin-top: 5px;
            font-size: 11px;
        }
        .sig-title {
            font-weight: bold;
            font-size: 10px;
        }

        /* Footer */
        .footer-note {
            font-size: 10px;
            text-align: center;
            margin-top: 18px;
            margin-bottom: 12px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
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
        
        $translateTitle = function($title) use ($isSwahili) {
            if (!$title) return '';
            if (!$isSwahili) return $title;
            
            $titleLower = strtolower(trim($title));
            
            // Exact or partial translations to match Tanzanian official terms
            $mapping = [
                'acting director of forensic science services' => 'Kaimu Mkurugenzi – Kurugenzi ya Huduma za Sayansi Jinai',
                'acting director - forensic science services' => 'Kaimu Mkurugenzi – Kurugenzi ya Huduma za Sayansi Jinai',
                'director of forensic science services' => 'Mkurugenzi wa Kurugenzi ya Huduma za Sayansi Jinai',
                'director - forensic science services' => 'Mkurugenzi – Kurugenzi ya Huduma za Sayansi Jinai',
                'acting director' => 'Kaimu Mkurugenzi',
                'senior chemist ii' => 'Mkemia Mwandamizi Daraja II',
                'senior chemist i' => 'Mkemia Mwandamizi Daraja I',
                'senior chemist' => 'Mkemia Mwandamizi',
                'principal chemist' => 'Mkemia Mkuu',
                'chemist ii' => 'Mkemia Daraja II',
                'chemist i' => 'Mkemia Daraja I',
                'chemist' => 'Mkemia',
                'laboratory manager' => 'Meneja Maabara',
                'lab manager' => 'Meneja Maabara',
                'manager - forensic chemistry laboratory' => 'Meneja – Maabara ya Sayansi Jinai Kemia',
                'manager - forensic laboratory' => 'Meneja – Maabara ya Sayansi Jinai',
                'manager' => 'Meneja',
                'director' => 'Mkurugenzi'
            ];

            foreach ($mapping as $key => $val) {
                if ($titleLower === $key || stripos($titleLower, $key) !== false) {
                    return $val;
                }
            }
            return $title;
        };
    @endphp

    <div class="top-header">
        {{ $isSwahili ? 'MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI' : 'GOVERNMENT CHEMIST LABORATORY AUTHORITY' }}
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 30%; text-align: left;">
                @if(isset($coat_of_arms) && $coat_of_arms)
                    <img src="{{ $coat_of_arms }}" class="logo-img" alt="Coat of Arms">
                @endif
            </td>
            <td style="width: 40%; text-align: center;">
                <div style="font-size: 14px; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin-top: 25px;">
                    {{ $isSwahili ? 'HATI YA UCHUNGUZI' : 'CERTIFICATE OF ANALYSIS' }}
                </div>
            </td>
            <td style="width: 30%; text-align: right;">
                @if(isset($gcla_logo) && $gcla_logo)
                    <img src="{{ $gcla_logo }}" class="logo-img" alt="GCLA Logo"><br>
                @endif
                <div style="font-size: 11px; font-weight: bold; margin-top: 5px; margin-right: 5px;">
                    GCLA 02
                </div>
            </td>
        </tr>
    </table>

    <div style="font-size: 12px; font-weight: bold; margin: 15px 0 8px 0;">
        {{ $isSwahili ? 'A. Utambuzi' : 'A. Identification' }}
    </div>
    
    <table class="id-table">
        <tr>
            <td style="width: 30%;">1. <b>Lab. No.</b> {{ $batch->batch_code }}</td>
            <td style="width: 30%;">2. <b>{{ $isSwahili ? 'Tarehe ya hati' : 'Report Date' }}:</b> {{ $processing_date }}</td>
            <td style="width: 40%;">3. <b>{{ $isSwahili ? 'Kumb. Na. yetu' : 'Our Ref. No' }}:</b> {{ $batch->reference_number }}</td>
        </tr>
        <tr>
            <td colspan="3">4. <b>{{ $isSwahili ? 'Aina ya kielelezo/sampuli' : 'Sample Type' }}:</b> {{ $batch->sample_type->name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="width: 60%;">5. <b>{{ $isSwahili ? 'Tarehe ya kupokelewa' : 'Date Received' }}:</b> {{ $receipt_date }}</td>
            <td style="width: 40%;">
                6. <b>{{ $isSwahili ? 'Kumb. Na. yako' : 'Your Ref. No' }}:</b> {{ $batch->kra_office_ref ?? '' }}<br>
                <b>{{ $isSwahili ? 'Jalada Na.' : 'File No.' }}</b> {{ $batch->file_no ?? '' }}
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

    <div style="font-size: 12px; font-weight: bold; margin: 15px 0 8px 0;">
        {{ $isSwahili ? 'B. Matokeo ya Uchunguzi' : 'B. Results of Analysis' }}
    </div>
    
    <div class="section-text">
        {{ $isSwahili 
            ? 'Uchunguzi wa kielelezo umefanyika kwa mujibu wa mbinu za uchunguza husika na matokeo yake yanahusisha kielelezo kilichowasilishwa na kupokelewa tu na Mamlaka kwa ajili ya uchunguza kama yalivyoainishwa kwenye jedwali Na.1' 
            : 'The analysis of the sample has been conducted according to the relevant test methods and the results apply only to the sample as received by the Authority for analysis as detailed in Table 1.' 
        }}
    </div>
    
    <div class="section-text" style="font-weight: bold; margin-top: 8px;">
        {{ $isSwahili ? 'Jedwali Na. 1' : 'Table 1' }}: {{ $isSwahili ? 'Matokeo ya Uchunguzi wa Kimaabara wa Sampuli' : 'Laboratory Test Results for the Sample' }}: {{ $sample_description }}
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%; text-transform: none !important;">{{ $isSwahili ? 'Na.' : 'NA.' }}</th>
                <th style="width: 20%;">{{ $isSwahili ? 'KIELELEZO' : 'SAMPLE REF' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MUONEKANO WA SAMPULI' : 'SAMPLE APPEARANCE' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MATOKEO YA UCHUNGUZI' : 'TEST RESULTS' }}</th>
                <th style="width: 25%;">{{ $isSwahili ? 'MBINU ZA UCHUNGUZI' : 'TEST METHODS' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($samples_data as $index => $sample_data)
                @php 
                    $rowspan = count($sample_data['results']) > 0 ? count($sample_data['results']) : 1;
                @endphp
                
                @if($rowspan == 1 && count($sample_data['results']) == 0)
                    <tr>
                        <td>{{ $index + 1 }}.</td>
                        <td>{{ $sample_data['sample_code'] }}</td>
                        <td>{{ $sample_data['appearance'] }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                @else
                    @foreach($sample_data['results'] as $r_idx => $result)
                        <tr>
                            @if($r_idx == 0)
                                <td rowspan="{{ $rowspan }}">{{ $index + 1 }}.</td>
                                <td rowspan="{{ $rowspan }}">{{ $sample_data['sample_code'] }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $sample_data['appearance'] }}</td>
                            @endif
                            
                            <td>
                                @if(empty($result['unit']) && (!is_numeric($result['value']) || in_array(strtolower($result['value']), ['absent', 'present', 'positive', 'negative'])))
                                    <b>{{ $result['value'] }}</b>
                                @else
                                    {{ $result['analyte'] }}:<br>
                                    <b>{{ $result['value'] }} {{ $result['unit'] }}</b>
                                @endif
                            </td>
                            <td>{{ $result['method'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($nb_notes))
        <div class="section-text" style="margin-top: 12px;">
            <b>{{ $isSwahili ? 'NB' : 'NB' }}:</b> {!! nl2br(e($nb_notes)) !!}
        </div>
    @endif

    <div style="font-size: 12px; font-weight: bold; margin: 15px 0 8px 0;">
        {{ $isSwahili ? 'C. Maoni ya Kitaalamu' : 'C. Professional Opinion' }}
    </div>
    
    <div class="section-text" style="text-align: justify;">
        {!! nl2br(e($comments ?? '')) !!}
    </div>
    
    <div style="text-align: center; font-weight: bold; margin-top: 20px; font-size: 12px; letter-spacing: 1px;">
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
                    <div class="sig-title">{{ $translateTitle($analyst['title'] ?? '') ?: ($isSwahili ? 'Mkemia' : 'Chemist') }}</div>
                </td>
                <td>
                    <div class="sig-space">
                        @if($verifier && $verifier['signature'])
                            <img src="{{ $verifier['signature'] }}" style="max-height: 40px;" alt="Signature">
                        @endif
                    </div>
                    <div class="sig-name">{{ $verifier['name'] ?? '' }}</div>
                    <div class="sig-title">{{ $translateTitle($verifier['title'] ?? '') ?: ($isSwahili ? 'Meneja Maabara' : 'Lab Manager') }}</div>
                </td>
                <td>
                    <div class="sig-space">
                        @if($approver && $approver['signature'])
                            <img src="{{ $approver['signature'] }}" style="max-height: 40px;" alt="Signature">
                        @endif
                    </div>
                    <div class="sig-name">{{ $approver['name'] ?? '' }}</div>
                    <div class="sig-title">{{ $translateTitle($approver['title'] ?? '') ?: ($isSwahili ? 'Mkurugenzi' : 'Director') }}</div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        @if($isSwahili)
            <b>Angalizo:</b> Ripoti hii imetolewa bila marekebisho/mabadiliko na isitolewe kwa aina yoyote ile bila kibali cha <b>Mkemia Mkuu wa Serikali.</b>
        @else
            <b>Note:</b> This report is issued without alteration and shall not be reproduced in any form without written permission from the <b>Chief Government Chemist.</b>
        @endif
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 25%;">5 Barack Obama</td>
            <td style="width: 25%;">S.L.P. 164,<br>Dar es Salaam Tanzania</td>
            <td style="width: 25%;">Simu: +255 22 2113383/4<br>Simu/Nukushi: +255 22 2113320</td>
            <td style="width: 25%;">Baruapepe: gcla@gcla.go.tz<br>Tovuti: www.gcla.go.tz</td>
        </tr>
    </table>
    
    <div style="text-align: right; font-size: 9px; margin-top: 5px;">
        {{ $isSwahili ? 'Ukurasa 1 kati 1' : 'Page 1 of 1' }}
    </div>

</body>
</html>
