<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>DCEA 009 - {{ $batch->batch_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 3px 0;
        }
        .form-no {
            font-size: 11px;
            font-weight: bold;
            text-align: right;
            margin-bottom: 5px;
        }
        .logo-container {
            text-align: center;
            margin: 5px 0;
        }
        .logo-img {
            height: 90px;
            width: auto;
        }
        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
            margin: 10px 0 2px 0;
        }
        .made-under {
            text-align: center;
            font-size: 11px;
            font-style: italic;
            margin-bottom: 20px;
        }
        .section-paragraph {
            text-align: justify;
            margin-bottom: 15px;
            text-indent: 40px;
        }
        .exhibits-container {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .exhibit-block {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .exhibit-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .exhibit-item {
            margin-left: 20px;
            margin-bottom: 4px;
        }
        .exhibit-item-label {
            display: inline-block;
            width: 25px;
            font-weight: bold;
        }
        .dotted-fill {
            border-bottom: 1px dotted #000;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            padding-bottom: -1px;
        }
        .signature-block {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .signature-field {
            margin-bottom: 6px;
            position: relative;
        }
        .signature-image {
            position: absolute;
            bottom: 2px;
            left: 100px;
            max-height: 40px;
            width: auto;
        }
    </style>
</head>
<body>

    @php
        $isSwahili = $language === 'sw';
    @endphp

    <div class="form-no">
        {{ $isSwahili ? 'FOMU NA. DCEA 009' : 'FORM NO. DCEA 009' }}
    </div>

    <div class="header">
        <div class="header-title">{{ $isSwahili ? 'JAMHURI YA MUUNGANO WA TANZANIA' : 'THE UNITED REPUBLIC OF TANZANIA' }}</div>
        <div class="header-title">{{ $isSwahili ? 'MAMLAKA YA KUDHIBITI NA KUPAMBANA NA DAWA ZA KULEVYA' : 'DRUG CONTROL AND ENFORCEMENT AUTHORITY' }}</div>
    </div>

    <div class="logo-container">
        @if(isset($dcea_logo) && $dcea_logo)
            <img src="{{ $dcea_logo }}" class="logo-img" alt="Logo">
        @else
            <div style="border: 1px dashed #000; width: 90px; height: 90px; line-height: 90px; margin: 0 auto; font-size: 10px;">[Coat of Arms]</div>
        @endif
    </div>

    <div class="report-title">
        {{ $isSwahili ? 'TAARIFA YA MKEMIA WA SERIKALI' : 'THE GOVERNMENT LABORATORY ANALYST REPORT' }}
    </div>
    <div class="made-under">
        {{ $isSwahili ? '(Imetengenezwa chini ya Kifungu cha 51(5))' : '(Made under Section 51(5))' }}
    </div>

    <!-- Paragraph 1: Certification statement (Word-for-Word identical to screenshots) -->
    <div style="text-align: justify; margin-bottom: 15px;">
        @if($isSwahili)
            Mimi, <span class="dotted-fill" style="width: 250px;">{{ $chemist_name }}</span> (Jina la Mkemia) wa 
            <span class="dotted-fill" style="width: 280px;">{{ $institution }}</span> (taasisi), nikiwa afisa niliyeidhinishwa ipasavyo kuchunguza na kuchambua sampuli/vielelezo, nathibitisha kama ifuatavyo:
            <div style="margin-top: 10px; text-indent: 0;">
                1. Mnamo tarehe <span class="dotted-fill" style="width: 40px;">{{ $receipt_day }}</span> 
                ya mwezi wa <span class="dotted-fill" style="width: 100px;">{{ $receipt_month_sw }}</span>, 
                20<span class="dotted-fill" style="width: 30px;">{{ substr($receipt_year, -2) }}</span> 
                Katika <span class="dotted-fill" style="width: 120px;">{{ $receipt_place }}</span> (mahali) nilipokea 
                <span class="dotted-fill" style="width: 40px;">{{ $quantity ?: '...........' }}</span> 
                (kiasi) pakiti/sanduku/viroba/vyombo vilivyofungwa kwa muhuri (vyovyote vinavyohusika) namba 
                <span class="dotted-fill" style="width: 150px;">{{ $marked_numbers }}</span> (namba yoyote iliyowekwa alama) inayodhaniwa kuwa imetumwa na 
                <span class="dotted-fill" style="width: 200px;">{{ $sending_institution }}</span> (taasisi) inayohisiwa kuwa na 
                <span class="dotted-fill" style="width: 150px;">{{ $exhibit_type }}</span> (aina ya kielelezo) katika fomu Na. 
                <span class="dotted-fill" style="width: 120px;">{{ $form_no }}</span> inayodhaniwa kuwa imesainiwa na 
                <span class="dotted-fill" style="width: 180px;">{{ $officer_sending_samples }}</span> (afisa wa taasisi inayotuma sampuli) ambayo ilikabidhiwa kwangu na 
                <span class="dotted-fill" style="width: 180px;">{{ $officer_bringing_samples }}</span> (afisa/maafisa wa taasisi) na kupewa Lab Na. 
                <span class="dotted-fill" style="width: 120px;">{{ $lab_no }}</span>.
            </div>
        @else
            I, <span class="dotted-fill" style="width: 250px;">{{ $chemist_name }}</span> (Name of Chemist) of the 
            <span class="dotted-fill" style="width: 280px;">{{ $institution }}</span> (institution), being an officer dully authorised to examine and analyse samples/exhibits, hereby certify as follows:
            <div style="margin-top: 10px; text-indent: 0;">
                1. On the <span class="dotted-fill" style="width: 40px;">{{ $receipt_day }}</span> 
                day of <span class="dotted-fill" style="width: 100px;">{{ $receipt_month_en }}</span>, 
                20<span class="dotted-fill" style="width: 30px;">{{ substr($receipt_year, -2) }}</span> 
                At <span class="dotted-fill" style="width: 120px;">{{ $receipt_place }}</span> (place) I received 
                <span class="dotted-fill" style="width: 40px;">{{ $quantity ?: '...........' }}</span> 
                (quantity) sealed packets/boxes/sacks/containers (whichever applicable) number 
                <span class="dotted-fill" style="width: 150px;">{{ $marked_numbers }}</span> (any marked number) purporting to be sent by 
                <span class="dotted-fill" style="width: 200px;">{{ $sending_institution }}</span> (institution) suspected to have contained 
                <span class="dotted-fill" style="width: 150px;">{{ $exhibit_type }}</span> (type of exhibit) in the form No. 
                <span class="dotted-fill" style="width: 120px;">{{ $form_no }}</span> purporting to be signed by 
                <span class="dotted-fill" style="width: 180px;">{{ $officer_sending_samples }}</span> (officer of the institution sending the sample(s)) which were handled to me by 
                <span class="dotted-fill" style="width: 180px;">{{ $officer_bringing_samples }}</span> (officer(s) of the institution) and was given Laboratory No. 
                <span class="dotted-fill" style="width: 120px;">{{ $lab_no }}</span>.
            </div>
        @endif
    </div>

    <!-- Paragraph 2: Results statement -->
    <div style="margin-top: 15px; margin-bottom: 10px; text-align: justify;">
        @if($isSwahili)
            2. Nimefanya uchunguzi na uchambuzi wa sampuli/vielelezo vilivyotajwa ambapo matokeo yake yameelezwa hapa chini:
        @else
            2. I have examined and analysed the said samples/exhibits the results of which are stated hereunder:
        @endif
    </div>

    <!-- Exhibits List -->
    <div class="exhibits-container">
        @forelse($exhibits as $idx => $exhibit)
            <div class="exhibit-block" style="page-break-inside: avoid;">
                <div class="exhibit-title">
                    @if($isSwahili)
                        Kielelezo "{{ chr(65 + $idx) }}" <span class="dotted-fill" style="width: 320px;">{{ $exhibit['description'] }}</span> (Maelezo ya Kielelezo)
                    @else
                        Exhibit "{{ chr(65 + $idx) }}" <span class="dotted-fill" style="width: 320px;">{{ $exhibit['description'] }}</span> (Description of Exhibit)
                    @endif
                </div>
                <div class="exhibit-item">
                    <span class="exhibit-item-label">(a)</span>
                    @if($isSwahili)
                        kimegundulika/hakikugundulika kuwa na dawa ya kulevya/dutu au dutu inayotumika katika maandalizi ya dawa: 
                        <b>{{ $exhibit['found'] ? 'KIMEGUNDULIKA' : 'HAKIKUGUNDULIKA' }}</b>
                    @else
                        has been found/not found to have contained drug/substance or substance used in preparation of drug: 
                        <b>{{ $exhibit['found'] ? 'HAS BEEN FOUND' : 'HAS NOT BEEN FOUND' }}</b>
                    @endif
                </div>
                <div class="exhibit-item">
                    <span class="exhibit-item-label">(b)</span>
                    @if($isSwahili)
                        aina ya dawa ya kulevya/dutu au dutu inayotumika katika maandalizi ya dawa (kama ipo): <b>{{ $exhibit['drug_type'] }}</b>
                    @else
                        type of drug/substance or substance used in preparation of drug (if any found): <b>{{ $exhibit['drug_type'] }}</b>
                    @endif
                </div>
                <div class="exhibit-item">
                    <span class="exhibit-item-label">(c)</span>
                    @if($isSwahili)
                        uzito/kiasi chake katika kilo/gramu au lita/mililita: <b>{{ $exhibit['weight'] }}</b>
                    @else
                        its weight/volume in kilograms/grams or litres/millilitres: <b>{{ $exhibit['weight'] }}</b>
                    @endif
                </div>
                <div class="exhibit-item">
                    <span class="exhibit-item-label">(d)</span>
                    @if($isSwahili)
                        athari zake kwa afya ya binadamu ikitumiwa/ikitumiwa kwa nje au kutumika kwa namna yoyote: <br>
                        <span style="font-weight: bold; text-transform: uppercase; margin-left: 25px; display: inline-block;">{{ $exhibit['health_effect'] }}</span>
                    @else
                        its effect to human health if consumed/applied or used anyhow: <br>
                        <span style="font-weight: bold; text-transform: uppercase; margin-left: 25px; display: inline-block;">{{ $exhibit['health_effect'] }}</span>
                    @endif
                </div>
                
                <div class="exhibit-item" style="margin-top: 5px;">
                    @if($isSwahili)
                        Maoni mengine (kama yapo) <span class="dotted-fill" style="width: 320px;">{{ $exhibit['remarks'] ?: '..................................................' }}</span>
                    @else
                        Other remarks (if any) <span class="dotted-fill" style="width: 320px;">{{ $exhibit['remarks'] ?: '..................................................' }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="exhibit-block">
                <div class="exhibit-title">{{ $isSwahili ? 'Kielelezo "A"' : 'Exhibit "A"' }}</div>
                <div style="font-style: italic;">{{ $isSwahili ? 'Hakuna sampuli zilizopatikana kwenye mfumo.' : 'No samples found in the system.' }}</div>
            </div>
        @endforelse
    </div>

    <!-- Paragraph 3: Return declaration -->
    <div style="text-align: justify; margin-top: 20px; margin-bottom: 20px;">
        @if($isSwahili)
            3. Pakiti/sanduku/viroba/vyombo vilivyofungwa kwa muhuri <span class="dotted-fill" style="width: 40px;">{{ $quantity ?: '...........' }}</span> (vyovyote vinavyohusika) kila kimoja kikiwa kimesainiwa na mimi, vimerudishwa baada ya uchunguzi kwa <span class="dotted-fill" style="width: 200px;">{{ $officer_bringing_samples }}</span> (afisa aliyetuletea sampuli).
        @else
            3. The <span class="dotted-fill" style="width: 40px;">{{ $quantity ?: '...........' }}</span> sealed packets/boxes/sacks/containers (whichever applicable) each signed by me, has/have been handed back after examination to <span class="dotted-fill" style="width: 200px;">{{ $officer_bringing_samples }}</span> (officer who brought the sample).
        @endif
    </div>

    <!-- Date block -->
    <div style="margin-top: 20px; margin-bottom: 25px; font-weight: bold;">
        @if($isSwahili)
            Imewekwa saini <span class="dotted-fill" style="width: 150px;">{{ $receipt_place }}</span> tarehe <span class="dotted-fill" style="width: 40px;">{{ $cert_day }}</span> ya mwezi wa <span class="dotted-fill" style="width: 100px;">{{ $cert_month_sw }}</span> 20<span class="dotted-fill" style="width: 30px;">{{ $cert_year_short }}</span>
        @else
            Dated at <span class="dotted-fill" style="width: 150px;">{{ $receipt_place }}</span> this <span class="dotted-fill" style="width: 40px;">{{ $cert_day }}</span> day of <span class="dotted-fill" style="width: 100px;">{{ $cert_month_en }}</span> 20<span class="dotted-fill" style="width: 30px;">{{ $cert_year_short }}</span>
        @endif
    </div>

    <!-- Stacked Signature blocks (Identical to Page 39) -->
    <div class="signature-block">
        <!-- Examining Officer -->
        <div style="margin-bottom: 30px; page-break-inside: avoid;">
            <div class="signature-title">{{ $isSwahili ? 'Afisa aliyefanya uchunguzi' : 'Examining officer' }}</div>
            <div class="signature-field">
                {{ $isSwahili ? 'Jina:' : 'Name:' }}
                <span class="dotted-fill" style="width: 350px;">{{ $examining_officer['name'] }}</span>
            </div>
            <div class="signature-field">
                {{ $isSwahili ? 'Sahihi:' : 'Signature:' }}
                <span class="dotted-fill" style="width: 350px;">
                    @if($examining_officer && $examining_officer['signature'])
                        <img src="{{ $examining_officer['signature'] }}" class="signature-image" alt="Signature">
                    @endif
                    &nbsp;
                </span>
            </div>
            <div class="signature-field">
                {{ $isSwahili ? 'Cheo/Sifa za Kitaaluma:' : 'Title/Qualification:' }}
                <span class="dotted-fill" style="width: 350px;">{{ $examining_officer['title'] }}</span>
            </div>
        </div>

        <!-- Certifying Officer -->
        <div style="page-break-inside: avoid;">
            <div class="signature-title">{{ $isSwahili ? 'Afisa anayethibitisha:' : 'Certifying officer:' }}</div>
            <div class="signature-field">
                {{ $isSwahili ? 'Jina:' : 'Name:' }}
                <span class="dotted-fill" style="width: 350px;">{{ $certifying_officer['name'] }}</span>
            </div>
            <div class="signature-field">
                {{ $isSwahili ? 'Sahihi:' : 'Signature:' }}
                <span class="dotted-fill" style="width: 350px;">
                    @if($certifying_officer && $certifying_officer['signature'])
                        <img src="{{ $certifying_officer['signature'] }}" class="signature-image" alt="Signature">
                    @endif
                    &nbsp;
                </span>
            </div>
            <div class="signature-field">
                {{ $isSwahili ? 'Cheo/Sifa za Kitaaluma:' : 'Title/Qualification:' }}
                <span class="dotted-fill" style="width: 350px;">{{ $certifying_officer['title'] }}</span>
            </div>
            <div class="signature-field">
                {{ $isSwahili ? 'Tarehe:' : 'Date:' }}
                <span class="dotted-fill" style="width: 350px;">{{ date('d/m/Y', $cert_time) }}</span>
            </div>
        </div>
    </div>

</body>
</html>
