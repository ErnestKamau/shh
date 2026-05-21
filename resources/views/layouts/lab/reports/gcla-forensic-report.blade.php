<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>RIPOTI YA VINASABA - LAB.NO. {{ $batch->batch_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 25mm; /* Top: 20mm, Right: 20mm, Bottom: 20mm, Left: 25mm */
        }
        * {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
        
        /* Standard GCLA Double-Header Layout */
        .header-container {
            width: 100%;
            border-bottom: 2px double #000;
            padding-bottom: 6px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
        .ref-block {
            width: 32%;
            font-size: 9px;
            line-height: 1.3;
            text-align: left;
        }
        .emblem-block {
            width: 36%;
            text-align: center;
        }
        .emblem-image {
            height: 60px;
            width: auto;
            margin-bottom: 2px;
        }
        .emblem-block h2 {
            font-size: 10px;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .address-block {
            width: 32%;
            font-size: 9px;
            line-height: 1.3;
            text-align: right;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0 5px 0;
            text-transform: uppercase;
        }

        /* Section Headings */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 15px 0 6px 0;
            text-transform: uppercase;
        }
        .section-content {
            margin-bottom: 12px;
            text-align: justify;
            font-size: 11px;
            text-justify: inter-word;
        }

        /* Tri-Party Signatures */
        .signature-container {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .signature-table td {
            border: none;
            width: 33.33%;
            vertical-align: top;
            padding: 0 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    @php
        $isCriminal = false;
        if (str_contains(strtoupper($batch->batch_code ?? ''), 'CZ') || 
            str_contains(strtoupper($batch->crm_unit_name ?? ''), 'DODOMA') || 
            (isset($batch->sample_type) && $batch->sample_type->code === 'ST-TOX')) {
            $isCriminal = true;
        }
        
        $exhibits = [];
        foreach ($ungrouped_samples as $sampleData) {
            $exhibits[] = $sampleData;
        }
        foreach ($grouped_samples as $areaName => $areaSamples) {
            foreach ($areaSamples as $sampleData) {
                $exhibits[] = $sampleData;
            }
        }
        
        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
    @endphp

    <!-- GCLA Double-Header -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <!-- Left Ref Block -->
                <td class="ref-block">
                    E-Mail: gcla@gcla.go.tz<br>
                    Simu Nambari: 255 – 22 – 2113383/4<br>
                    Fax: 255 – 22 – 2113320.<br>
                    Jibu kwa Mkemia Mkuu wa Serikali na tutaje:<br>
                    Kumb. Na. yetu: -<br>
                    Kumb. Na. yako: {{ $customerReference ?? '-' }}
                </td>
                
                <!-- Center Emblem Block -->
                <td class="emblem-block">
                    @if(!empty($logos['tanzania']))
                        <img class="emblem-image" src="{{ $logos['tanzania'] }}" alt="Coat of Arms">
                    @endif
                    <h2>JAMHURI YA MUUNGANO WA TANZANIA</h2>
                    <h2 style="font-weight: normal; font-size: 9px;">WIZARA YA AFYA</h2>
                </td>
                
                <!-- Right Address Block -->
                <td class="address-block">
                    MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI,<br>
                    @if($isCriminal)
                        MAABARA YA MKEMIA MKUU KANDA YA KATI,<br>
                        S.L.P. 38,<br>
                        DODOMA.<br>
                    @else
                        05 BARABARA YA BARACK OBAMA,<br>
                        S.L.P. 164.<br>
                        DAR ES SALAAM.<br>
                    @endif
                    <br>
                    {{ $date ?? date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Lab Ref Number at Top Left -->
    <div style="font-weight: bold; font-size: 11px; margin-bottom: 10px;">
        LAB.NO. {{ $batch->batch_code }}
    </div>

    <!-- Requesting Agency Block -->
    <div style="font-size: 11px; margin-bottom: 20px; line-height: 1.3;">
        {{ strtoupper($batch->crm_customer?->name ?? 'OFISI YA MKUU WA UPELELEZI') }},<br>
        WILAYA YA {{ $batch->crm_customer?->district ?? 'XXXX' }},<br>
        S.L.P {{ $batch->crm_customer?->po_box ?? '82' }},<br>
        {{ strtoupper($batch->crm_customer?->city ?? 'XXXX') }}.
    </div>

    <!-- Document Title Block -->
    <div class="doc-title">
        RIPOTI YA UHUSIANO WA CHEMBECHEMBE ASILI ZA URITHI (VINASABA) ZA MAKOSA YA JINAI [FORENSIC DNA PROFILING TEST REPORT]
    </div>
    <div style="text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase;">
        YAH: JALADA: {{ $batch->reference_number ?? 'XXXX' }}
    </div>

    <!-- 1.0 UTANGULIZI -->
    <div class="section-title">1.0 UTANGULIZI</div>
    <div class="section-content">
        @if($isCriminal)
            Mnamo tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} tulipokea kifurushi kilichofungwa kwa lakiri kutoka MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI, OFISI YA KANDA YA KATI – DODOMA kikiambatana na barua kutoka {{ strtoupper($batch->crm_customer?->name ?? 'OFISI YA MKUU WA UPELELEZI') }} yenye Kumb. Na. {{ $batch->reference_number ?? 'XXXX' }} ya tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} pamoja na P.F. 180 yenye Kumb. Na. {{ $batch->reference_number ?? 'XXXX' }} ya tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} kama ilivyotajwa kwenye barua ya MAMLAKA YA MAABARA YA MKEMIA MKUU WA SERIKALI, OFISI YA KANDA YA KATI – DODOMA yenye Kumb. Na. {{ $batch->reference_number ?? 'XXXX' }} ya tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} ili tufanye uchunguzi wa mpangilio wa chembechembe asili za urithi (DNA Profile) na kukupa maoni ya kitaalamu. Aidha, sampuli hizo zilipewa namba ya usajili wa maabara LAB.NO. {{ $batch->batch_code }}.
        @else
            Mnamo tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} tulipokea kifurushi kilichofungwa kwa lakiri kutoka {{ strtoupper($batch->crm_customer?->name ?? 'OFISI YA MKUU WA UPELELEZI') }} kama ilivyotajwa kwenye barua yako yenye Kumb. Na. {{ $batch->reference_number ?? 'XXXX' }} ya tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} pamoja na P.F 180 yenye Kumb. Na. {{ $batch->reference_number ?? 'XXXX' }} ya tarehe {{ $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : 'DATE' }} ili tufanye uchunguzi wa mpangilio wa chembechembe asili za urithi (DNA Profile) na kukupa maoni ya kitaalamu. Aidha, vielelezo hivyo vilipewa namba ya usajili wa maabara LAB. NO {{ $batch->batch_code }}.
        @endif
    </div>

    <!-- 1.1 Vielelezo vilivyopokelewa -->
    <div class="section-title" style="margin-left: 10px;">1.1 Vielelezo vilivyopokelewa:</div>
    <div class="section-content" style="margin-left: 20px;">
        @foreach($exhibits as $i => $sampleData)
            @php
                $lbl = $letters[$i] ?? 'a';
                $sampleName = $sampleData['sample']->main_body ?? $sampleData['sample_point_name'] ?? 'Kielelezo';
            @endphp
            <div style="margin-bottom: 4px;">{{ $lbl }}) Kielelezo ‘{{ strtoupper($lbl) }}’ – {{ $sampleName }}.</div>
        @endforeach
    </div>

    <!-- 2.0 MATOKEO YA UCHUNGUZI -->
    <div class="section-title">2.0 MATOKEO YA UCHUNGUZI</div>
    @if($isCriminal)
        <div class="section-title" style="margin-left: 10px;">2.1 Uchunguzi wa awali:</div>
        <div class="section-content" style="margin-left: 20px;">
            @foreach($exhibits as $i => $sampleData)
                @php
                    $lbl = $letters[$i] ?? 'a';
                    $sampleName = $sampleData['sample']->main_body ?? $sampleData['sample_point_name'] ?? 'Kielelezo';
                    $prelim = $sampleData['sample']->header_body ?? 'kimedhihirisha kuwa na damu ya binadamu.';
                @endphp
                <div style="margin-bottom: 4px;">{{ $lbl }}) Kielelezo ‘{{ strtoupper($lbl) }}’ ({{ $sampleName }}) {{ $prelim }}</div>
            @endforeach
        </div>
    @else
        <div class="section-content">
            Kutokana na matokeo ya uchunguzi, tukilinganisha mpangilio wa chembechembe asili za urithi (DNA Profile) zitokazo kwa Mzazi kwenda kwa Mtoto:
            <div style="margin-left: 20px; margin-top: 6px;">
                @foreach($exhibits as $i => $sampleData)
                    @php
                        $lbl = $letters[$i] ?? 'a';
                        $sampleName = $sampleData['sample']->main_body ?? $sampleData['sample_point_name'] ?? 'Kielelezo';
                        $notes = $sampleData['sample']->notes_body ?? 'yaliyooana na maeneo ya Mtoto.';
                    @endphp
                    <div style="margin-bottom: 4px;">{{ $lbl }}) Kati ya maeneo yote kumi na tano (15) ya {{ $sampleName }} (Kielelezo ‘{{ strtoupper($lbl) }}’) yaliyofanyiwa uchunguzi ni maeneo yote kumi na tano (15) {{ $notes }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3.0 TAFSIRI YA MATOKEO (Criminal Only) -->
    @if($isCriminal)
        <div class="section-title">3.0 TAFSIRI YA MATOKEO</div>
        <div class="section-title" style="margin-left: 10px;">3.1 Mchanganuo wa mpangilio wa vinasaba:</div>
        <div class="section-content" style="margin-left: 20px;">
            Kutokana na matokeo ya uchunguzi wa mpangilio wa vinasaba;<br>
            @foreach($exhibits as $i => $sampleData)
                @php
                    $lbl = $letters[$i] ?? 'a';
                    $sampleName = $sampleData['sample']->main_body ?? $sampleData['sample_point_name'] ?? 'Kielelezo';
                    $analysis = $sampleData['sample']->notes_body ?? 'kimedhihirisha kuwa ni cha mmiliki mmoja mwenye jinsi ya kike.';
                @endphp
                <div style="margin-bottom: 4px; margin-top: 4px;">{{ $lbl }}) Kielelezo ‘{{ strtoupper($lbl) }}’ ({{ $sampleName }}) {{ $analysis }}</div>
            @endforeach
        </div>

        <div class="section-title" style="margin-left: 10px;">3.2 Ulinganisho wa mpangilio wa vinasaba:</div>
        <div class="section-content" style="margin-left: 20px;">
            Kutokana na matokeo ya uchunguzi wa mpangilio wa vinasaba:<br><br>
            Kielelezo ‘A’ ({{ $exhibits[0]['sample']->main_body ?? 'Mpini wa shoka' }}) kimedhihirisha kuwa na mahusiano ya mpangilio wa vinasaba na Kielelezo ‘X’ ({{ $exhibits[1]['sample']->main_body ?? 'Blood swab' }}).
        </div>
    @endif

    <!-- HITIMISHO -->
    <div class="section-title">{{ $isCriminal ? '4.0' : '3.0' }} HITIMISHO</div>
    <div class="section-content">
        @if($isCriminal)
            {{ $batch->notes_body ?? 'Tegemeo la nafasi (chances) ya vinasaba toka Kielelezo ‘A’ kutokuwa na mahusiano ya mpangilio wa vinasaba na Kielelezo ‘X’ ni moja kati ya bilioni.' }}
        @else
            {{ $batch->notes_body ?? 'Tegemeo la nafasi (chances) ya Mtuhumiwa kuwa Baba Mzazi wa Mtoto ni asilimia 99.99 (99.99%) ukizingatia kuwa Mama ni Mama Mzazi wa Mtoto.' }}
        @endif
    </div>

    <!-- Tri-Party Signatures Block -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Mchunguzi:</strong>
                    <div style="height: 45px; border-bottom: 1px dotted #000; margin-bottom: 5px;"></div>
                    Jina: {{ $analystName ?? 'Dkt. John Doe' }}<br>
                    Cheo: MKEMIA DARAJA I
                </td>
                <td>
                    <strong>Imehakikiwa:</strong>
                    <div style="height: 45px; border-bottom: 1px dotted #000; margin-bottom: 5px;"></div>
                    Jina: {{ $verifierName ?? 'Prof. Jane Smith' }}<br>
                    Cheo: MENEJA WA MAABARA YA SAYANSI JINAI BAIOLOJIA NA VINASABA
                </td>
                <td>
                    <strong>Imethibitishwa:</strong>
                    <div style="height: 45px; border-bottom: 1px dotted #000; margin-bottom: 5px;"></div>
                    Jina: {{ $approverName ?? 'Dkt. John Doe' }}<br>
                    Cheo: MKURUGENZI WA HUDUMA ZA SAYANSI JINAI<br>
                    Tarehe: {{ $date ?? date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
