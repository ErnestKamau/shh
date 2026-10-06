@php
    $companyName = trim((string) (getActiveCompany()?->name ?? '')) ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $reportNumber }} — {{ $companyName }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #e9e6e5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f1f1f;
        }
        .bar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: #fff;
            border-top: 4px solid #8B1A1A;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .bar-text { flex: 1 1 auto; min-width: 0; }
        .bar-company {
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #8B1A1A;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bar-title {
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 6px;
            background: #8B1A1A;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-outline { background: #fff; color: #8B1A1A; border: 1px solid #d9c4c4; }
        .btn svg { width: 16px; height: 16px; }
        .pages {
            max-width: 900px;
            margin: 0 auto;
            padding: 12px 8px 32px;
        }
        .pages canvas {
            display: block;
            width: 100%;
            height: auto;
            margin: 0 auto 12px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }
        .status {
            padding: 48px 20px;
            text-align: center;
            color: #555;
            font-size: 15px;
        }
        .spinner {
            width: 36px;
            height: 36px;
            margin: 0 auto 14px;
            border: 3px solid #d9c4c4;
            border-top-color: #8B1A1A;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        .status .btn { margin: 14px 6px 0; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <header class="bar">
        <div class="bar-text">
            <div class="bar-company">{{ $companyName }}</div>
            <div class="bar-title">Test Report {{ $reportNumber }}</div>
        </div>
        <a class="btn" href="{{ $downloadUrl }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0l-5-5m5 5l5-5M4 21h16"/></svg>
            Download
        </a>
    </header>

    <main class="pages" id="pages" aria-live="polite">
        <div class="status" id="status">
            <div class="spinner" aria-hidden="true"></div>
            Loading report…
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            var pdfUrl = @json($pdfUrl);
            var downloadUrl = @json($downloadUrl);
            var pagesEl = document.getElementById('pages');
            var pdfDocument = null;
            var renderedWidth = 0;
            var renderToken = 0;

            function showFallback() {
                pagesEl.innerHTML =
                    '<div class="status">The report could not be displayed here.<br>' +
                    '<a class="btn btn-outline" href="' + pdfUrl + '">Open PDF</a>' +
                    '<a class="btn" href="' + downloadUrl + '">Download</a></div>';
            }

            function availableWidth() {
                return Math.max(280, Math.min(pagesEl.clientWidth - 16, 884));
            }

            async function renderPages() {
                var token = ++renderToken;
                var cssWidth = availableWidth();
                var outputScale = Math.min((window.devicePixelRatio || 1) * 1.5, 4);

                for (var pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
                    var page = await pdfDocument.getPage(pageNumber);
                    if (token !== renderToken) {
                        return;
                    }

                    var viewport = page.getViewport({ scale: cssWidth / page.getViewport({ scale: 1 }).width });
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.floor(viewport.width * outputScale);
                    canvas.height = Math.floor(viewport.height * outputScale);
                    canvas.setAttribute('aria-label', 'Page ' + pageNumber + ' of ' + pdfDocument.numPages);

                    await page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport,
                        transform: [outputScale, 0, 0, outputScale, 0, 0],
                    }).promise;

                    if (token !== renderToken) {
                        return;
                    }

                    if (pageNumber === 1) {
                        pagesEl.innerHTML = '';
                    }
                    pagesEl.appendChild(canvas);
                }

                renderedWidth = cssWidth;
            }

            if (!window.pdfjsLib) {
                showFallback();
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            pdfjsLib.getDocument({ url: pdfUrl }).promise
                .then(function (loadedDocument) {
                    pdfDocument = loadedDocument;
                    return renderPages();
                })
                .catch(showFallback);

            var resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    if (pdfDocument && Math.abs(availableWidth() - renderedWidth) > 40) {
                        renderPages().catch(showFallback);
                    }
                }, 250);
            });
        })();
    </script>
</body>
</html>
