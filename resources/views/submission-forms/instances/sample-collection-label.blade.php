<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(($labelType ?? 'collection') === 'registration')
            Lab sample labels
        @else
            Sample Collection Label
        @endif
    </title>
    <style>
        :root {
            --ink: #111111;
            --accent: #8f2222;
            --paper: #ffffff;
            --line: #1d1d1d;
            --muted: #f4f4f4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 18px;
            font-family: "Segoe UI", Arial, sans-serif;
            background: var(--paper);
            color: var(--ink);
        }

        .toolbar {
            margin-bottom: 14px;
        }

        .print-btn {
            appearance: none;
            border: 0;
            border-radius: 6px;
            background: #0f5bd2;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 16px;
            cursor: pointer;
        }

        .sheet {
            max-width: 980px;
            margin: 0 auto;
            display: grid;
            gap: 18px;
        }

        .label-card {
            border: 2px solid var(--line);
            background: var(--paper);
            width: 100%;
        }

        .label-head {
            border-bottom: 2px solid var(--line);
            display: grid;
            grid-template-columns: 120px 1fr;
            align-items: stretch;
            min-height: 40px;
        }

        .label-head-logo {
            background: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 2px solid var(--line);
            padding: 4px;
        }

        .label-head-logo img {
            max-width: 100%;
            max-height: 28px;
            object-fit: contain;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .label-head-title {
            background: var(--accent);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 17px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            text-align: center;
            padding: 0 10px;
        }

        .label-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .label-table td,
        .label-table th {
            border: 1px solid var(--line);
            padding: 4px 6px;
            font-size: 14px;
            line-height: 1.15;
            vertical-align: middle;
        }

        .label-table td:first-child,
        .label-table th:first-child {
            width: 42%;
            background: #efefef;
        }

        .label-table .wide-cell {
            background: #fff;
        }

        .hint-title {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin: 0;
        }

        .registration-title {
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            margin: 0;
            padding: 0 4px;
        }

        .registration-wrapper {
            padding: 8px;
        }

        .barcode-wrap {
            border: 1px solid var(--line);
            border-top: 0;
            text-align: center;
            padding: 8px 8px 6px;
            min-height: 62px;
        }

        .barcode-wrap svg {
            max-width: 100%;
            height: 42px;
        }

        .barcode-text {
            margin-top: 3px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        @media print {
            @page {
                margin: 6mm;
            }

            body {
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                gap: 10mm;
            }

            .label-card {
                break-inside: avoid;
            }

            .label-head-logo img {
                display: block !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    @php
        $selectedLabelType = ($labelType ?? 'collection') === 'registration' ? 'registration' : 'collection';

        $logoSrc = trim((string) ($logoSrc ?? ''));
        if ($logoSrc === '') {
            $rawLogoPath = trim((string) ($logoPath ?? ''));
            if ($rawLogoPath !== '') {
                if (\Illuminate\Support\Str::startsWith($rawLogoPath, ['http://', 'https://', '//', 'data:'])) {
                    $logoSrc = $rawLogoPath;
                } elseif (\Illuminate\Support\Str::startsWith($rawLogoPath, ['storage/', '/storage/'])) {
                    $logoSrc = url('/'.ltrim($rawLogoPath, '/'));
                } elseif (\Illuminate\Support\Str::startsWith($rawLogoPath, ['public/', '/public/'])) {
                    $logoSrc = url('/storage/'.ltrim(preg_replace('#^/?public/#', '', $rawLogoPath), '/'));
                } else {
                    $logoSrc = asset(ltrim($rawLogoPath, '/'));
                }
            }
        }

        if ($logoSrc === '') {
            $logoSrc = asset('images/company_logo.png');
        }

        $barcodeValue = trim((string) ($barcodeValue ?? ''));
        if ($barcodeValue === '') {
            $barcodeValue = trim((string) ($instance->form_number ?? ''));
        }
        if ($barcodeValue === '' || \Illuminate\Support\Str::isUuid($barcodeValue)) {
            $barcodeValue = '';
        }

        $normalizedDateTime = (string) $dateTimeOfCollection;

        try {
            $normalizedDateTime = \Illuminate\Support\Carbon::parse($dateTimeOfCollection)->format('jS F, Y  h:iA');
        } catch (\Throwable $e) {
            // Keep provided value when date parsing fails.
        }

        $preservationText = trim((string) $preservationApplied);
        $containerText = trim((string) $containerType);
        $collectionForText = trim((string) $sampleCollectionFor);
    @endphp

    <div class="toolbar">
        <button class="print-btn" onclick="window.print()">Print Label</button>
    </div>

    <main class="sheet">
        @if($selectedLabelType === 'collection')
            <section class="label-card" aria-label="Sample Collection Label">
                <header class="label-head">
                    <div class="label-head-logo">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Company logo">
                        @endif
                    </div>
                    <div class="label-head-title">Sample Collection Label</div>
                </header>

                <table class="label-table" role="presentation">
                    <tr>
                        <td>Sample Name / Description:</td>
                        <td class="wide-cell">{{ $sampleName }}</td>
                    </tr>
                    <tr>
                        <td>Batch Number:</td>
                        <td class="wide-cell">{{ $batchNumber }}</td>
                    </tr>
                    <tr>
                        <td>Client Name:</td>
                        <td class="wide-cell">{{ $clientName }}</td>
                    </tr>
                    <tr>
                        <td>Site - Location:</td>
                        <td class="wide-cell">{{ $siteLocation }}</td>
                    </tr>
                    <tr>
                        <td>Date &amp; Time of Collection:</td>
                        <td class="wide-cell">{{ $normalizedDateTime }}</td>
                    </tr>
                    <tr>
                        <td>Sample Temperature (&deg;C):</td>
                        <td class="wide-cell">{{ $sampleTemperature }}</td>
                    </tr>
                    <tr>
                        <td>Collected By (Name / Sign):</td>
                        <td class="wide-cell">{{ $collectedBy }}</td>
                    </tr>
                    <tr>
                        <td>Preservation Applied:</td>
                        <td class="wide-cell">[{{ strcasecmp($preservationText, 'Yes') === 0 ? 'x' : ' ' }}] Yes [{{ strcasecmp($preservationText, 'No') === 0 ? 'x' : ' ' }}] No (Specify: {{ $preservationText }})</td>
                    </tr>
                    <tr>
                        <td>Container Type:</td>
                        <td class="wide-cell">[{{ stripos($containerText, 'HDPE') !== false ? 'x' : ' ' }}] HDPE [{{ stripos($containerText, 'Glass') !== false ? 'x' : ' ' }}] Glass Bottle [{{ stripos($containerText, 'Zipper') !== false ? 'x' : ' ' }}] Zipper Bag</td>
                    </tr>
                    <tr>
                        <td>Sample Collection for:</td>
                        <td class="wide-cell">[{{ stripos($collectionForText, 'Micro') !== false ? 'x' : ' ' }}] Micro Lab [{{ stripos($collectionForText, 'Chemistry') !== false ? 'x' : ' ' }}] Chemistry Lab</td>
                    </tr>
                </table>

                @if ($barcodeValue !== '')
                    <div class="barcode-wrap">
                        {!! DNS1D::getBarcodeSVG($barcodeValue, 'C128') !!}
                        <div class="barcode-text">{{ $barcodeValue }}</div>
                    </div>
                @endif
            </section>
        @else
            @php
                $labelsToPrint = is_array($registrationLabels ?? null) && $registrationLabels !== []
                    ? $registrationLabels
                    : [['sampleId' => $sampleId ?? 'N/A']];
            @endphp
            @foreach ($labelsToPrint as $registrationLabel)
                <section class="label-card" aria-label="Lab sample label">
                    <header class="label-head">
                        <div class="label-head-logo">
                            @if ($logoSrc)
                                <img src="{{ $logoSrc }}" alt="Company logo">
                            @endif
                        </div>
                        <div class="label-head-title">Lab sample label with barcode</div>
                    </header>

                    <table class="label-table" role="presentation">
                        <tr>
                            <td>Lab No.</td>
                            <td class="wide-cell">{{ ($jobNumber ?? '') !== '' ? $jobNumber : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Sample No.</td>
                            <td class="wide-cell">{{ ($registrationLabel['sampleId'] ?? '') !== '' ? $registrationLabel['sampleId'] : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Sample Description</td>
                            <td class="wide-cell">{{ $sampleDescription }}</td>
                        </tr>
                        <tr>
                            <td>Date &amp; Time of Collection:</td>
                            <td class="wide-cell">{{ $normalizedDateTime }}</td>
                        </tr>
                    </table>

                    @if ($barcodeValue !== '')
                        <div class="barcode-wrap">
                            {!! DNS1D::getBarcodeSVG($barcodeValue, 'C128') !!}
                            <div class="barcode-text">{{ $barcodeValue }}</div>
                        </div>
                    @endif
                </section>
            @endforeach
        @endif
    </main>
</body>
</html>
