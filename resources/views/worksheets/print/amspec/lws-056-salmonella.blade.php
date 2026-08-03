<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $docNo }} – Detection of Salmonella spp.</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm 16mm 12mm; }
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; box-sizing: border-box; }
        body { font-size: 8px; color: #000; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        .header td { border: 1px solid #000; padding: 3px 5px; vertical-align: middle; }
        .header .meta-label { width: 16%; font-weight: bold; background: #f2f2f2; }
        .header .meta-value { width: 22%; }
        .header .title-main { font-weight: bold; font-size: 13px; text-align: center; }
        .header .title-sub { font-weight: bold; font-size: 9.5px; text-align: center; line-height: 1.25; }
        .logo-cell { width: 14%; text-align: center; }
        .logo-cell img { width: 52px; height: auto; }
        .info th, .info td,
        .grid th, .grid td { border: 1px solid #000; padding: 3px 4px; vertical-align: middle; }
        .info th, .grid th { background: #f0f0f0; font-weight: bold; text-align: left; }
        .center { text-align: center; }
        .small { font-size: 7px; line-height: 1.2; }
        .tiny { font-size: 6.5px; line-height: 1.15; }
        .fill { min-height: 11px; }
        .muted { color: #222; }
        .slash { color: #222; }
        .val { font-weight: bold; }
        .section-title { font-weight: bold; font-size: 9px; margin: 8px 0 3px; }
        .page-break { page-break-before: always; }
        .footer-bar {
            position: fixed; bottom: -10px; left: 0; right: 0;
            text-align: center; font-size: 7px; border-top: 1px solid #000; padding-top: 3px;
        }
    </style>
</head>
<body>
@php
    $s = $steps ?? [];
    $v = fn (string $key) => trim((string) ($s[$key] ?? ''));
    $pair = function (string $key, string $a, string $b) use ($v) {
        $val = $v($key);
        if ($val === '') {
            return '<span class="slash">'.$a.'/ '.$b.'</span>';
        }

        return '<span class="val">'.$val.'</span>';
    };
    $triple = function (string $key, string $opts) use ($v) {
        $val = $v($key);
        if ($val === '') {
            return '<span class="slash">'.$opts.'</span>';
        }

        return '<span class="val">'.$val.'</span>';
    };
    $logoPath = public_path('images/amspec/logo.png');
    $logoSrc = is_file($logoPath) ? $logoPath : null;
@endphp

<table class="header" style="margin-bottom: 6px;">
    <tr>
        <td class="logo-cell" rowspan="3">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="AmSpec">
            @else
                <strong>AmSpec</strong>
            @endif
        </td>
        <td class="title-main" rowspan="1">LABORATORY WORK SHEET</td>
        <td class="meta-label">Doc No.</td>
        <td class="meta-value">{{ $docNo }}</td>
    </tr>
    <tr>
        <td class="title-sub" rowspan="2">
            MICROBIOLOGICAL ANALYSIS –<br>
            DETECTION OF <em>SALMONELLA</em> SPP
        </td>
        <td class="meta-label">Revision Date</td>
        <td class="meta-value">{{ $revisionDate }}</td>
    </tr>
    <tr>
        <td class="meta-label">Revision No.</td>
        <td class="meta-value">{{ $revisionNo }}</td>
    </tr>
</table>

<table class="info">
    <tr>
        <th style="width: 22%;">Job Number</th>
        <td colspan="3">{{ $jobNumber }}</td>
    </tr>
    <tr>
        <th>Sample Name</th>
        <td colspan="3">{{ $sampleName }}</td>
    </tr>
    <tr>
        <th>Other Details</th>
        <td style="width: 28%;">{{ $otherDetails }}</td>
        <th style="width: 18%;">Food Product</th>
        <td style="width: 32%;">{{ $foodProduct }}</td>
    </tr>
    <tr>
        <th>Test Method</th>
        <td colspan="3">{{ $testMethod }}</td>
    </tr>
    <tr>
        <th>Analysis Start Date</th>
        <td>{{ $analysisStartDate }}</td>
        <th>Completion Date</th>
        <td>{{ $completionDate }}</td>
    </tr>
    <tr>
        <th>Analyst Name</th>
        <td colspan="3">{{ $analystName }}</td>
    </tr>
</table>

<table class="grid small" style="margin-top: 7px;">
    <thead>
        <tr>
            <th class="center" style="width: 9%;">Date of Test</th>
            <th class="center" style="width: 13%;">Test Procedure</th>
            <th class="center" style="width: 10%;">Sample Volume</th>
            <th class="center" style="width: 11%;">Media</th>
            <th class="center" style="width: 17%;">Incubation</th>
            <th class="center" style="width: 13%;">Observation</th>
            <th class="center" style="width: 13%;">Media Control</th>
            <th class="center" style="width: 14%;">Positive Control</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="fill"></td>
            <td>Pre-enrichment</td>
            <td class="center">25 g</td>
            <td class="center">225 mL</td>
            <td>Room Temperature for 60 min &amp; 35 °C for 24 ± 2 h</td>
            <td>{!! $pair('Pre-enrichment – Observation', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('Pre-enrichment – Media Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('Pre-enrichment – Positive Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
        </tr>
        <tr>
            <td class="fill" rowspan="2"></td>
            <td rowspan="2">Secondary enrichment</td>
            <td class="center">0.1 or 1 mL</td>
            <td class="center">10 mL RV broth</td>
            <td>42 °C for 24 h</td>
            <td>{!! $pair('RV enrichment – Observation', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('RV enrichment – Media Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('RV enrichment – Positive Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
        </tr>
        <tr>
            <td class="center">1 mL</td>
            <td class="center">10 mL TTB</td>
            <td>35 °C/ 43 °C for 24 h</td>
            <td>{!! $pair('TTB enrichment – Observation', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('TTB enrichment – Media Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
            <td>{!! $pair('TTB enrichment – Positive Control', 'Turbidity Observed', 'No Turbidity') !!}</td>
        </tr>
        <tr>
            <th>Incubation Start Time</th>
            <td>{{ $incubationStartTime }}</td>
            <th>Observation Date &amp; Time</th>
            <td colspan="2">{{ $observationDateTime }}</td>
            <th>Incubator ID</th>
            <td colspan="2">{{ $incubatorId }}</td>
        </tr>
    </tbody>
</table>

<table class="grid tiny" style="margin-top: 8px;">
    <thead>
        <tr>
            <th class="center" rowspan="2" style="width: 18%;">Selective Isolation</th>
            <th class="center" rowspan="2" style="width: 10%;">Incubation</th>
            <th class="center" colspan="3">Inoculation from RV broth</th>
            <th class="center" colspan="3">Inoculation from TTB</th>
            <th class="center" rowspan="2" style="width: 9%;">Result (per 25 g)</th>
        </tr>
        <tr>
            <th class="center">XLDA</th>
            <th class="center">HEA</th>
            <th class="center">BSA</th>
            <th class="center">XLDA</th>
            <th class="center">HEA</th>
            <th class="center">BSA</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Loopful of inoculum from secondary enrichment streaked onto selective agar plate (XLDA, HEA &amp; BSA)</td>
            <td class="center">35°C for 24 h ± 2h to 48 h</td>
            <td>{!! $triple('XLDA from RV', 'Pink colony with black center/ Pink colony without black center/ No Growth') !!}</td>
            <td>{!! $triple('HEA from RV', 'Blue-green colony with black center/ Blue-green colony without black center/ No Growth') !!}</td>
            <td>{!! $triple('BSA from RV', 'Black colony with metallic sheen/ Black colony without metallic sheen/ No Growth') !!}</td>
            <td>{!! $triple('XLDA from TTB', 'Pink colony with black center/ Pink colony without black center/ No Growth') !!}</td>
            <td>{!! $triple('HEA from TTB', 'Blue-green colony with black center/ Blue-green colony without black center/ No Growth') !!}</td>
            <td>{!! $triple('BSA from TTB', 'Black colony with metallic sheen/ Black colony without metallic sheen/ No Growth') !!}</td>
            <td class="center">
                @if(trim((string) $finalResult) !== '')
                    <span class="val">{{ $finalResult }}</span>
                @else
                    <span class="slash">Detected/ Not Detected</span>
                @endif
            </td>
        </tr>
        <tr>
            <th colspan="2">Positive Control</th>
            <td class="muted">Pink colony with black center or Pink colony without black center</td>
            <td class="muted">Blue-green colony with black center or Blue-green colony without black center</td>
            <td class="muted">Black colony with metallic sheen or Black colony without metallic sheen</td>
            <td class="muted">Pink colony with black center or Pink colony without black center</td>
            <td class="muted">Blue-green colony with black center or Blue-green colony without black center</td>
            <td class="muted">Black colony with metallic sheen or Black colony without metallic sheen</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>Media Control (XLDA, HEA &amp; BSA):</strong> No Growth observed / No Characteristic colonies observed
            </td>
            <td colspan="5">
                <strong>Negative Control:</strong> Growth observed / No Characteristic colonies observed
            </td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<table class="header" style="margin-bottom: 6px;">
    <tr>
        <td class="logo-cell" rowspan="3">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="AmSpec">
            @else
                <strong>AmSpec</strong>
            @endif
        </td>
        <td class="title-main">LABORATORY WORK SHEET</td>
        <td class="meta-label">Doc No.</td>
        <td class="meta-value">{{ $docNo }}</td>
    </tr>
    <tr>
        <td class="title-sub" rowspan="2">
            MICROBIOLOGICAL ANALYSIS –<br>
            DETECTION OF <em>SALMONELLA</em> SPP
        </td>
        <td class="meta-label">Revision Date</td>
        <td class="meta-value">{{ $revisionDate }}</td>
    </tr>
    <tr>
        <td class="meta-label">Revision No.</td>
        <td class="meta-value">{{ $revisionNo }}</td>
    </tr>
</table>

<div class="section-title">CONFIRMATION TESTS:</div>
<table class="grid small">
    <thead>
        <tr>
            <th class="center" style="width: 16%;">Test</th>
            <th class="center" style="width: 18%;">Medium</th>
            <th class="center" style="width: 14%;">Incubation</th>
            <th class="center" style="width: 18%;">Result</th>
            <th class="center" style="width: 17%;">Positive Control</th>
            <th class="center" style="width: 17%;">Negative Control</th>
        </tr>
    </thead>
    <tbody>
        @php
            $confirmation = [
                ['TSI', 'TSI agar slants', '35°C ± 1°C for 24 h ± 2 h to 48h', 'Alkaline Slant & Acid Butt/ Acid slant and acid Butt/No Growth', 'Alkaline Slant & Acid Butt', ''],
                ['LIA', 'Lysine iron agar', '35°C ± 1°C for 24 h ± 2 h', 'Alkaline Slant & Alkaline Butt/ Alkaline Slant & Acid Butt/No Growth', 'Alkaline Slant & Alkaline Butt', ''],
                ['Lysine decarboxylation Test', 'L- lysine decarboxylation Broth', '35°C ± 1°C for 24 h ± 2 h', 'Purple color/ Yellow color', 'Purple color/ Yellow color', 'Purple color/ Yellow color'],
                ['Indole Test', 'Tryptone Broth', '35°C ± 1°C for 24 h ± 2 h', 'No red ring/ Red ring', 'No red ring/ Red ring', 'No red ring/ Red ring'],
                ['Urease Test', 'Urease Broth/Agar', '35°C ± 1°C for 24 h ± 2 h', 'No color change/ Pink color', 'No color change/ Pink color', 'No color change/ Pink color'],
                ['Voges-Proskauer Test', 'MR-VP Broth', '35°C ± 1°C for 24 h ± 2 h', 'No color change/ Red color', 'No color change/ Red color', 'No color change/ Red color'],
                ['Methyl red', 'MR-VP Broth', '35°C ± 1°C for 24 h ± 2 h', 'Red color/ No color change', 'Red color/ No color change', 'Red color/ No color change'],
                ['Dulcitol', 'Phenol red Dulcitol broth', '35°C ± 1°C for 24 h ± 2 h to 48h', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production'],
                ['Sucrose', 'Phenol red sucrose broth', '35°C ± 1°C for 24 h ± 2 h to 48h', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production'],
                ['Lactose', 'Phenol red Lactose broth', '35°C ± 1°C for 24 h ± 2 h', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production', 'Yellow color change with gas production / No color change and No gas production'],
                ['Malonate', 'Malonate Broth', '35°C ± 1°C for 24 h ± 2 h', 'No color change/ Blue color', 'No color change/ Blue color', 'No color change/ Blue color'],
                ['Citrate utilization Test', 'Simmon citrate agar', '35°C ± 1°C for 24 h ± 2 h to 48h', 'No color change/ Blue color', 'No color change/ Blue color', 'No color change/ Blue color'],
                ['O antigen Agglutination Test', '-', '-', 'Agglutination/ No agglutination', 'Agglutination/ No agglutination', 'Agglutination/ No agglutination'],
                ['H antigen Agglutination Test', '-', '48 - 50°C for 1 h', 'Agglutination/ No agglutination', 'Agglutination/ No agglutination', 'Agglutination/ No agglutination'],
            ];
        @endphp
        @foreach($confirmation as [$test, $medium, $incubation, $opts, $pos, $neg])
            <tr>
                <td><strong>{{ $test }}</strong></td>
                <td>{{ $medium }}</td>
                <td>{{ $incubation }}</td>
                <td>
                    @if($v($test) !== '')
                        <span class="val">{{ $v($test) }}</span>
                    @else
                        <span class="slash">{{ $opts }}</span>
                    @endif
                </td>
                <td class="muted">{{ $pos }}</td>
                <td class="muted">{{ $neg }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="6">
                <strong>Positive Reference Culture:</strong> {{ $positiveReference }}
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <strong>Negative Reference Culture:</strong> {{ $negativeReference ?: '' }}
            </td>
        </tr>
    </tbody>
</table>

<div class="footer-bar">
    {{ $companyFooter }}
</div>
</body>
</html>
