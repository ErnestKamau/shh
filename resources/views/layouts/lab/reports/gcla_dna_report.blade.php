<!DOCTYPE html>
<html lang="sw">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>GCLA FORENSIC DNA PROFILING TEST REPORT</title>
    <style>
        @page {
            margin: 2.0cm 2.0cm 2.0cm 2.5cm; /* Margins: Left 2.5cm, Right 2.0cm, Top 2.0cm, Bottom 2.0cm */
            size: A4;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .page {
            position: relative;
            height: 980px; /* Precise height to fit A4 page printing boundaries without leaking */
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        
        /* Header styles */
        .lab-no {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .header-left {
            width: 40%;
            font-size: 9pt;
            line-height: 1.2;
        }
        .header-center {
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
        .header-right {
            width: 40%;
            font-size: 9pt;
            line-height: 1.2;
            text-align: right;
        }
        .header-title-gov {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin-top: 1px;
            margin-bottom: 1px;
        }
        
        .divider-line {
            border-bottom: 1.5px solid #000;
            margin-top: 3px;
            margin-bottom: 10px;
        }
        
        /* Addressee styling */
        .addressee-section {
            margin-left: 20px;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        /* Report Title styling */
        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-top: 10px;
            margin-bottom: 10px;
            text-decoration: underline;
            line-height: 1.3;
        }
        
        /* Subject styling */
        .subject-line {
            font-weight: bold;
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        /* Section styling */
        .section-header {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
        }
        .section-content {
            text-align: justify;
            text-indent: 40px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .list-item {
            margin-left: 40px;
            margin-bottom: 5px;
            text-align: justify;
        }
        
        /* STR table styles */
        .str-table {
            width: 75%;
            margin: 10px auto;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .str-table th, .str-table td {
            border: 1px solid #000;
            padding: 3px 8px;
            text-align: center;
        }
        .str-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        /* Signature styles */
        .signature-grid {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-grid td {
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
            font-size: 8.5pt;
            line-height: 1.3;
        }
        
        /* Footer styles */
        .page-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 9pt;
            border-top: 1px solid #ccc;
            padding-top: 3px;
            width: 100%;
            line-height: 1.2;
        }
        .footer-left {
            float: left;
            width: 70%;
        }
        .footer-right {
            float: right;
            width: 30%;
            text-align: right;
        }
    </style>
</head>
<body>

    @php
        $isHq = stripos($batch->batch_code, 'HQ') !== false;
        
        // Dynamic dates
        $analysisStart = $analysis_date?->start_analysis_date;
        $analysisEnd = $analysis_date?->end_analysis_date;
        if ($analysisStart && $analysisEnd) {
            $analysisDateFormatted = date('d/m/Y', strtotime($analysisStart)) . ' - ' . date('d/m/Y', strtotime($analysisEnd));
        } elseif ($analysisStart) {
            $analysisDateFormatted = date('d/m/Y', strtotime($analysisStart));
        } elseif ($analysisEnd) {
            $analysisDateFormatted = date('d/m/Y', strtotime($analysisEnd));
        } else {
            $analysisDateFormatted = date('d/m/Y');
        }
        $letterDateFormatted = date('d/m/Y', strtotime($batch->created_at));
        $todayFormatted = date('d/m/Y', strtotime($date ?? getTodayDate()));
        
        // Paternity / Criminal variables
        $district = $customer->physical_address ?? 'XXXX';
        $box = $customer->postal_address ?? '82';
        $region = $customer->location ?? 'XXXX';
        $caseFile = $batch->case_id ?? $customerReference ?? 'XXXX';
        
        // Extract samples
        $samplesList = [];
        if (isset($ungrouped_samples) && count($ungrouped_samples) > 0) {
            foreach ($ungrouped_samples as $idx => $s) {
                $samplesList[] = $s;
            }
        }
    @endphp

    <!-- PAGE 1 -->
    <div class="page">
        <div class="lab-no">LAB.NO. {{ $batch->batch_code }}</div>
        
        <!-- Gov Header -->
        <div class="header-title-gov">JAMHURI YA MUUNGANO WA TANZANIA</div>
        <div class="header-title-gov" style="font-size: 10pt; font-weight: normal; margin-top: 0;">WIZARA YA AFYA</div>
        
        <table class="header-table">
            <tr>
                <td class="header-left">
                    E-Mail: gcla@gcla.go.tz<br>
                    Simu Nambari: 255 – 22 – 2113383/4<br>
                    Fax: 255 – 22 – 2113320.<br>
                    Jibu kwa Mkemia Mkuu wa Serikali na tutaje:<br>
                    Kumb. Na. yetu: -<br>
                    Kumb. Na. yako: {{ $batch->customer_reference ?? '-' }}
                </td>
                <td class="header-center">
                    @if(!empty($report_logo))
                        <img src="{{ $report_logo }}" alt="Emblem" height="65">
                    @endif
                </td>
                <td class="header-right">
                    MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI,<br>
                    05 BARABARA YA BARACK OBAMA,<br>
                    S.L.P. 164,<br>
                    DAR ES SALAAM.<br>
                    <span style="font-weight: bold; font-size: 10pt; display: block; margin-top: 5px;">{{ $todayFormatted }}</span>
                </td>
            </tr>
        </table>
        
        <div class="divider-line"></div>
        
        <!-- Addressee -->
        <div class="addressee-section">
            OFISI YA MKUU WA UPELELEZI,<br>
            WILAYA YA {{ strtoupper($district) }},<br>
            S.L.P {{ $box }},<br>
            {{ strtoupper($region) }}.
        </div>
        
        <!-- Report Title -->
        <div class="report-title">
            RIPOTI YA UHUSIANO WA CHEMBECHEMBE ASILI ZA URITHI (VINASABA) ZA MAKOSA<br>
            YA JINAI [FORENSIC DNA PROFILING TEST REPORT]
        </div>
        
        <!-- Subject -->
        <div class="subject-line">
            YAH: JALADA: {{ $caseFile }}
        </div>
        
        <!-- Section 1.0 -->
        <div class="section-header">1.0 UTANGULIZI</div>
        
        <div class="section-content">
            @if($isHq)
                Mnamo tarehe {{ $analysisDateFormatted }} tulipokea kifurushi kilichofungwa kwa lakiri kutoka OFISI YA MKUU WA UPELELEZI, WILAYA YA {{ strtoupper($district) }} kama ilivyotajwa kwenye barua yako yenye Kumb. Na. {{ $batch->customer_reference ?? 'XXXX' }} ya tarehe {{ $letterDateFormatted }} pamoja na P.F 180 yenye Kumb. Na. {{ $batch->customer_reference ?? 'XXXX' }} ya tarehe {{ $letterDateFormatted }} ili tufanye uchunguzi wa mpangilio wa chembechembe asili za urithi (DNA Profile) na kukupa maoni ya kitaalamu. Aidha, vielelezo hivyo vilipewa namba ya usajili wa maabara LAB. NO {{ $batch->batch_code }}.
            @else
                Mnamo tarehe {{ $analysisDateFormatted }} tulipokea kifurushi kilichofungwa kwa lakiri kutoka MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI, OFISI YA KANDA YA KATI – DODOMA kikiambatana na barua kutoka OFISI YA MKUU WA UPELELEZI, WILAYA YA {{ strtoupper($district) }} yenye Kumb. Na. {{ $batch->customer_reference ?? 'XXXX' }} ya tarehe {{ $letterDateFormatted }} pamoja na PF. 180 yenye Kumb. Na. {{ $batch->customer_reference ?? 'XXXX' }} ya tarehe {{ $letterDateFormatted }} kama ilivyotajwa kwenye barua ya MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI, OFISI YA KANDA YA KATI – DODOMA yenye Kumb. Na. {{ $batch->customer_reference ?? 'XXXX' }} ya tarehe {{ $letterDateFormatted }} ili tufanye uchunguzi wa mpangilio wa chembechembe asili za urithi (DNA Profile) na kukupa maoni ya kitaalamu. Aidha, sampuli hizo zilipewa namba ya usajili wa maabara LAB.NO. {{ $batch->batch_code }}.
            @endif
        </div>
        
        <!-- Section 1.1 -->
        <div class="section-header">1.1 Vielelezo vilivyopokelewa:</div>
        
        @if($isHq)
            <div class="list-item">a) Kielelezo ‘A’ – Mpanguso wa kinywa cha mama {{ count($samplesList) > 0 ? ($samplesList[0]['sample_code'] ?? 'XXXX') : 'XXXX' }}.</div>
            <div class="list-item">b) Kielelezo ‘B’ – Mpanguso wa kinywa cha mtoto {{ count($samplesList) > 1 ? ($samplesList[1]['sample_code'] ?? 'XXXX') : 'XXXX' }}.</div>
            <div class="list-item">c) Kielelezo ‘C’ – Mpanguso wa kinywa cha mtuhumiwa {{ count($samplesList) > 2 ? ($samplesList[2]['sample_code'] ?? 'XXXX') : 'XXXX' }}.</div>
        @else
            <div class="list-item">a) Kielelezo ‘A’– Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki.</div>
            <div class="list-item">b) Kielelezo ‘X’– Blood swab ya marehemu imefungwa kwenye bahasha ya khaki.</div>
        @endif
        
        <!-- Page 1 Footer -->
        <div class="page-footer">
            <div class="footer-left">Sahihi 1……….……………Sahihi 2………….…….… Sahihi 3…………..…..………Tarehe………………….</div>
            <div class="footer-right">Ukurasa 1 kati ya 2</div>
        </div>
    </div>
    
    <!-- PAGE 2 -->
    <div class="page">
        <div class="lab-no">LAB.NO. {{ $batch->batch_code }}</div>
        <div style="height: 15px;"></div>
        
        @if($isHq)
            <!-- HQ (Paternity) Page 2 content -->
            <div class="section-header">2.0 MATOKEO YA UCHUNGUZI</div>
            <div class="section-content" style="text-indent: 0; margin-bottom: 5px;">
                Kutokana na matokeo ya uchunguzi, tukilinganisha mpangilio wa chembechembe asili za urithi (DNA Profile) zitokazo kwa Mzazi kwenda kwa Mtoto:
            </div>
            
            <div class="list-item" style="margin-left: 20px; text-indent: -20px; padding-left: 20px; margin-bottom: 2px;">
                a) Kati ya maeneo yote kumi na tano (15) ya Mtuhumiwa (Kielelezo ‘C’) yaliyofanyiwa uchunguzi ni maeneo yote kumi na tano (15) yaliyooana na maeneo ya Mtoto (Kielelezo ‘B’).
            </div>
            <div class="list-item" style="margin-left: 20px; text-indent: -20px; padding-left: 20px; margin-bottom: 10px;">
                b) Kati ya maeneo yote kumi na tano (15) ya Mama (Kielelezo ‘A’) yaliyofanyiwa uchunguzi ni maeneo yote kumi na tano (15) yaliyooana na maeneo ya Mtoto (Kielelezo ‘B’).
            </div>
            
            <!-- DNA Loci Table -->
            <table class="str-table">
                <thead>
                    <tr>
                        <th>STR Locus</th>
                        <th>Kielelezo 'A' (Mama)</th>
                        <th>Kielelezo 'B' (Mtoto)</th>
                        <th>Kielelezo 'C' (Mtuhumiwa)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>D8S1179</td><td>13, 15</td><td>13, 14</td><td>14, 16</td></tr>
                    <tr><td>D21S11</td><td>28, 30</td><td>28, 29</td><td>29, 31.2</td></tr>
                    <tr><td>D7S820</td><td>10, 11</td><td>10, 8</td><td>8, 12</td></tr>
                    <tr><td>CSF1PO</td><td>12, 12</td><td>10, 12</td><td>10, 11</td></tr>
                    <tr><td>TH01</td><td>6, 9.3</td><td>9.3, 9.3</td><td>8, 9.3</td></tr>
                    <tr><td>vWA</td><td>16, 17</td><td>16, 18</td><td>15, 18</td></tr>
                    <tr><td>D16S539</td><td>11, 12</td><td>11, 13</td><td>9, 13</td></tr>
                    <tr><td>D18S51</td><td>15, 19</td><td>14, 15</td><td>14, 20</td></tr>
                    <tr><td>D2S1338</td><td>17, 20</td><td>17, 23</td><td>19, 23</td></tr>
                </tbody>
            </table>
            
            <div class="section-header" style="margin-top: 15px;">3.0 HITIMISHO</div>
            <div class="section-content">
                Tegemeo la nafasi (chances) ya Mtuhumiwa (Kielelezo ‘C’) kuwa Baba Mzazi wa Mtoto (Kielelezo ‘B’) ni asilimia 99.99 (99.99%) ukizingatia kuwa Mama (Kielelezo ‘A’) ni Mama Mzazi wa Mtoto (Kielelezo ‘B’).
            </div>
        @else
            <!-- Zone (Criminal) Page 2 content -->
            <div class="section-header">2.0 MATOKEO YA UCHUNGUZI</div>
            <div class="section-header" style="margin-left: 20px; font-size: 10.5pt;">2.1 Uchunguzi wa awali:</div>
            <div class="list-item">
                a) Kielelezo ‘A’ (Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki) kimedhihirisha kuwa na damu ya binadamu.
            </div>
            <div class="list-item">
                b) Kielelezo ‘X’ (Blood swab ya marehemu imefungwa kwenye bahasha ya khaki) kimedhihirisha kuwa na damu ya binadamu.
            </div>
            
            <div class="section-header" style="margin-top: 15px;">3.0 TAFSIRI YA MATOKEO</div>
            <div class="section-header" style="margin-left: 20px; font-size: 10.5pt;">3.1 Mchanganuo wa mpangilio wa vinasaba:</div>
            <div class="section-content" style="text-indent: 0; margin-left: 20px; margin-bottom: 5px;">
                Kutokana na matokeo ya uchunguzi wa mpangilio wa vinasaba;
            </div>
            <div class="list-item">
                a) Kielelezo ‘A’ (Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki - sehemu yenye damu) kimedhihirisha kuwa ni cha mmiliki mmoja mwenye jinsi ya kike.
            </div>
            <div class="list-item">
                b) Kielelezo ‘A’ (Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki - sehemu isiyo na damu) kimedhihirisha kuwa ni cha wamiliki zaidi ya mmoja wenye jinsi ya kike na kiume.
            </div>
            <div class="list-item" style="margin-bottom: 15px;">
                c) Kielelezo ‘X’ (Blood swab ya marehemu imefungwa kwenye bahasha ya khaki) kimedhihirisha kuwa ni cha mmiliki mmoja mwenye jinsi ya kike.
            </div>
            
            <div class="section-header" style="margin-left: 20px; margin-top: 10px; font-size: 10.5pt;">3.2 Ulinganisho wa mpangilio wa vinasaba:</div>
            <div class="section-content" style="text-indent: 0; margin-left: 20px; margin-bottom: 15px;">
                Kutokana na matokeo ya uchunguzi wa mpangilio wa vinasaba, Kielelezo ‘A’ (Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki - sehemu yenye damu) kimedhihirisha kuwa na mahusiano ya mpangilio wa vinasaba na Kielelezo ‘X’ (Blood swab ya marehemu imefungwa kwenye bahasha ya khaki).
            </div>
            
            <div class="section-header" style="margin-top: 15px;">4.0 HITIMISHO</div>
            <div class="section-content">
                Tegemeo la nafasi (chances) ya vinasaba toka Kielelezo ‘A’ (Mpini wa shoka umechafuka damu kikiwa kimefungwa kwenye karatasi ya khaki - sehemu yenye damu) kutokuwa na mahusiano ya mpangilio wa vinasaba na Kielelezo ‘X’ (Blood swab ya marehemu imefungwa kwenye bahasha ya khaki) ni moja kati ya bilioni.
            </div>
        @endif
        
        <!-- Signatures Grid -->
        <table class="signature-grid">
            <tr>
                <td>
                    <strong>Mchunguzi:</strong><br><br><br>
                    Jina: {{ count($batch_approvers) > 0 ? ($batch_approvers[0]->approvername ?? 'Dr. Jane Doe') : 'Dr. Jane Doe' }}<br>
                    Cheo: MKEMIA DARAJA I
                </td>
                <td>
                    <strong>Imehakikiwa:</strong><br><br><br>
                    Jina: {{ count($batch_approvers) > 1 ? ($batch_approvers[1]->approvername ?? 'Prof. John Smith') : 'Prof. John Smith' }}<br>
                    Cheo: MENEJA WA MAABARA YA SAYANSI JINAI BAIOLOJIA NA VINASABA
                </td>
                <td>
                    <strong>Imethibitishwa:</strong><br><br><br>
                    Jina: {{ count($batch_approvers) > 2 ? ($batch_approvers[2]->approvername ?? 'Commissioner Mary Lwiza') : 'Commissioner Mary Lwiza' }}<br>
                    Cheo: MKURUGENZI WA HUDUMA ZA SAYANSI JINAI<br>
                    Tarehe: {{ $todayFormatted }}
                </td>
            </tr>
        </table>
        
        <!-- Page 2 Footer -->
        <div class="page-footer">
            <div class="footer-left"></div>
            <div class="footer-right">Ukurasa 2 kati ya 2</div>
        </div>
    </div>

</body>
</html>
