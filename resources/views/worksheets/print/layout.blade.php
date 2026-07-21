<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Worksheet' }} — {{ $batch->batch_code }}</title>
    @include('worksheets.partials.worksheet-print-styles')
</head>
<body class="worksheet-print-shell p-3">
    <div class="worksheet-print-toolbar no-print">
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            Print
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm ml-2" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="worksheet-print-header">
        <h1>{{ $title ?? 'Worksheet' }}</h1>
        <p>Batch: <strong>{{ $batch->batch_code }}</strong></p>
    </div>

    @if(!empty($metaSummary))
        @include('worksheets.partials.worksheet-meta-bar', ['metaSummary' => $metaSummary])
    @endif

    @if(!empty($metaRows) && count($metaRows) > 1)
        @include('worksheets.partials.worksheet-meta-table', ['metaRows' => $metaRows])
    @endif

    @yield('worksheet-print-content')

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
