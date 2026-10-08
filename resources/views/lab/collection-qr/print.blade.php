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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            outline: 1px dashed #cbd2db;
        }
        .sticker-header {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 1.5mm;
        }
        .sticker-header { justify-content: center; }
        .sticker-header-logo { flex: 0 0 auto; min-width: 0; display: flex; align-items: center; justify-content: center; height: 100%; }
        .sticker-header-logo img { display: block; max-width: 100%; max-height: 100%; object-fit: contain; }
        .sticker-qr-wrap { flex: 0 0 auto; display: block; }
        .sticker-qr { display: block; width: 100%; height: 100%; }

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
        .layout-a4 .sticker { padding: 2mm 2.5mm; gap: 0.8mm; }
        .layout-a4 .sticker-header { height: 5.5mm; }
        .layout-a4 .sticker-qr-wrap { width: 25mm; height: 25mm; }
        .layout-a4 .sticker:nth-child(24n) { break-after: page; }

        /* Thermal: one 50 x 30 mm label per page */
        .layout-thermal .sheet { width: 50mm; background: transparent; box-shadow: none; }
        .layout-thermal .sticker {
            width: 50mm;
            height: 30mm;
            padding: 1.5mm;
            gap: 0.6mm;
            margin-bottom: 4mm;
            background: #fff;
        }
        .layout-thermal .sticker-header { height: 4.5mm; }
        .layout-thermal .sticker-qr-wrap { width: 21mm; height: 21mm; }

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
                <div class="sticker-header">
                    @if($logoDataUri !== '')
                        <span class="sticker-header-logo">
                            <img src="{{ $logoDataUri }}" alt="{{ $companyName }}">
                        </span>
                    @endif
                </div>
                @if($sticker['qr'] !== '')
                    <div class="sticker-qr-wrap">
                        <img class="sticker-qr" src="{{ $sticker['qr'] }}" alt="QR code {{ $sticker['token'] }}">
                    </div>
                @endif
            </div>
        @endforeach
    </main>
</body>
</html>
