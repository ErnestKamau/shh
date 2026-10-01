@php
    $isThermal = $layout === 'thermal';
    $printStickers = [];
    foreach ($stickers as $sticker) {
        for ($copy = 0; $copy < $copies; $copy++) {
            $printStickers[] = $sticker;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — {{ $subtitle }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eceff3;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #111;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 16px;
            padding: 14px 20px;
            background: #fff;
            border-bottom: 1px solid #d9dee5;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            font-size: 13px;
        }
        .toolbar h1 { margin: 0; font-size: 16px; }
        .toolbar .meta { color: #5b6573; margin-top: 2px; }
        .toolbar form { display: flex; align-items: flex-end; gap: 8px; margin: 0; }
        .toolbar label { display: flex; flex-direction: column; gap: 3px; font-weight: 600; color: #374151; }
        .toolbar select, .toolbar input {
            height: 32px;
            padding: 0 8px;
            border: 1px solid #c7ced8;
            border-radius: 4px;
            font: inherit;
        }
        .toolbar input[type="number"] { width: 72px; }
        .toolbar .spacer { flex: 1 1 auto; }
        .btn {
            height: 32px;
            padding: 0 14px;
            border: 1px solid #c7ced8;
            border-radius: 4px;
            background: #fff;
            color: #1f2937;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-primary { background: #8B1A1A; border-color: #8B1A1A; color: #fff; }
        .notice { flex-basis: 100%; padding: 8px 10px; border-radius: 4px; }
        .notice-success { background: #ecfdf3; color: #05603a; }
        .notice-error { background: #fef3f2; color: #b42318; }
        .hint { flex-basis: 100%; color: #5b6573; }

        .sheet { margin: 20px auto; background: #fff; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12); }

        .sticker {
            display: flex;
            align-items: center;
            overflow: hidden;
            outline: 1px dashed #cbd2db;
        }
        .sticker-qr { flex: 0 0 auto; display: block; }
        .sticker-text { flex: 1 1 auto; min-width: 0; line-height: 1.2; }
        .sticker-client { font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sticker-line { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; }
        .sticker-label { color: #555; font-weight: 600; }
        .sticker-blank { display: inline-block; min-width: 60%; border-bottom: 0.2mm solid #333; }
        .sticker-token { font-family: "DejaVu Sans Mono", Menlo, Consolas, monospace; color: #666; letter-spacing: 0.04em; }

        /* A4 sheet: 3 x 8 labels, 70 x 36 mm */
        .layout-a4 .sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 4.5mm 0;
            display: grid;
            grid-template-columns: repeat(3, 70mm);
            grid-auto-rows: 36mm;
            align-content: start;
        }
        .layout-a4 .sticker { padding: 2mm 2.5mm; gap: 2mm; }
        .layout-a4 .sticker-qr { width: 28mm; height: 28mm; }
        .layout-a4 .sticker-text { font-size: 7.5pt; }
        .layout-a4 .sticker-client { font-size: 8.5pt; margin-bottom: 1mm; }
        .layout-a4 .sticker-line { -webkit-line-clamp: 2; margin-bottom: 0.6mm; }
        .layout-a4 .sticker-token { font-size: 6pt; margin-top: 0.6mm; }
        .layout-a4 .sticker:nth-child(24n) { break-after: page; }

        /* Thermal: one 50 x 30 mm label per page */
        .layout-thermal .sheet { width: 50mm; background: transparent; box-shadow: none; }
        .layout-thermal .sticker {
            width: 50mm;
            height: 30mm;
            padding: 1.5mm;
            gap: 1.5mm;
            margin-bottom: 4mm;
            background: #fff;
        }
        .layout-thermal .sticker-qr { width: 24mm; height: 24mm; }
        .layout-thermal .sticker-text { font-size: 6pt; }
        .layout-thermal .sticker-client { font-size: 6.5pt; margin-bottom: 0.6mm; }
        .layout-thermal .sticker-line { -webkit-line-clamp: 2; margin-bottom: 0.4mm; }
        .layout-thermal .sticker-token { font-size: 5pt; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { margin: 0; box-shadow: none; }
            .sticker { outline: none; }
            .layout-thermal .sticker { margin: 0; break-after: page; }
            .layout-thermal .sticker:last-child { break-after: auto; }
        }
    </style>
    <style media="print">
        @page { size: {{ $isThermal ? '50mm 30mm' : 'A4' }}; margin: 0; }
    </style>
</head>
<body class="layout-{{ $layout }}">
    <div class="toolbar no-print">
        <div>
            <h1>{{ $title }}</h1>
            <div class="meta">{{ $companyName }} · {{ $subtitle }}</div>
        </div>

        <form method="GET" action="{{ $printUrl }}">
            <label>
                Layout
                <select name="layout" onchange="this.form.submit()">
                    <option value="a4" @selected(!$isThermal)>A4 sheet (3 × 8)</option>
                    <option value="thermal" @selected($isThermal)>Thermal label (50 × 30 mm)</option>
                </select>
            </label>
            <label>
                Copies of each
                <input type="number" name="copies" min="1" max="10" value="{{ $copies }}">
            </label>
            <button type="submit" class="btn">Apply</button>
        </form>

        <form method="POST" action="{{ $extrasUrl }}">
            @csrf
            <input type="hidden" name="layout" value="{{ $layout }}">
            <input type="hidden" name="copies" value="{{ $copies }}">
            <label>
                Extra QR codes
                <input type="number" name="quantity" min="1" max="{{ \App\Services\Sampleworkflow\CollectionQrCodeService::MAX_EXTRAS_PER_REQUEST }}" value="{{ old('quantity', 1) }}">
            </label>
            <button type="submit" class="btn">Add</button>
        </form>

        <div class="spacer"></div>

        @if($backUrl)
            <a href="{{ $backUrl }}" class="btn">Back</a>
        @endif
        <button type="button" class="btn btn-primary" onclick="window.print()">Print {{ count($printStickers) }} {{ \Illuminate\Support\Str::plural('sticker', count($printStickers)) }}</button>

        @if(session('status'))
            <div class="notice notice-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="notice notice-error">{{ $errors->first() }}</div>
        @endif
        <div class="hint">
            {{ count($stickers) }} {{ \Illuminate\Support\Str::plural('QR code', count($stickers)) }} for {{ $rowCount }} {{ \Illuminate\Support\Str::plural('sample', $rowCount) }}.
            Each QR code belongs to one sample; use "Copies of each" when a sample needs more than one container.
            Reprinting always uses the same codes.
        </div>
    </div>

    <main class="sheet">
        @foreach($printStickers as $sticker)
            <div class="sticker">
                @if($sticker['qr'] !== '')
                    <img class="sticker-qr" src="{{ $sticker['qr'] }}" alt="QR code {{ $sticker['token'] }}">
                @endif
                <div class="sticker-text">
                    <div class="sticker-client">{{ $sticker['client'] !== '' ? $sticker['client'] : 'Client: ____________' }}</div>
                    <div class="sticker-line">
                        <span class="sticker-label">Date:</span>
                        @if($sticker['collection_date'] !== '')
                            {{ $sticker['collection_date'] }}
                        @else
                            <span class="sticker-blank">&nbsp;</span>
                        @endif
                    </div>
                    <div class="sticker-line">
                        <span class="sticker-label">Sample:</span>
                        @if($sticker['sample_type'] !== '')
                            {{ $sticker['sample_type'] }}
                        @else
                            <span class="sticker-blank">&nbsp;</span>
                        @endif
                    </div>
                    <div class="sticker-line">
                        <span class="sticker-label">Tests:</span>
                        @if($sticker['tests'] !== '')
                            {{ $sticker['tests'] }}
                        @else
                            <span class="sticker-blank">&nbsp;</span>
                        @endif
                    </div>
                    <div class="sticker-token">{{ $sticker['token'] }}</div>
                </div>
            </div>
        @endforeach
    </main>
</body>
</html>
