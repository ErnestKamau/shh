@extends('layouts.lab.layout.app')

@section('title2')
<title>Preview Test Report — {{ $reportNumber }}</title>
<style>
    .trr-system-preview-page {
        padding: 0 4px 24px;
    }
    .trr-system-preview-chrome {
        background: #0f172a;
        color: #f8fafc;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }
    .trr-system-preview-chrome .trr-preview-badge {
        display: inline-block;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        background: #f59e0b;
        color: #111827;
        border-radius: 999px;
        padding: 3px 10px;
        margin-bottom: 6px;
    }
    .trr-system-preview-chrome h1 {
        font-size: 17px;
        font-weight: 650;
        margin: 0 0 4px;
        color: #fff;
        font-family: Georgia, 'Times New Roman', serif;
    }
    .trr-system-preview-chrome p {
        margin: 0;
        font-size: 12px;
        color: #cbd5e1;
        line-height: 1.45;
        max-width: 52rem;
    }
    .trr-system-preview-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .trr-system-preview-actions a,
    .trr-system-preview-actions button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        font-family: inherit;
    }
    .trr-system-preview-actions .btn-print {
        background: #fff;
        color: #0f172a;
        border: none;
    }
    .trr-system-preview-actions .btn-back {
        background: transparent;
        color: #e2e8f0;
        border-color: #475569;
    }
    .trr-system-preview-frame-wrap {
        background: #e8eef5;
        border: 1px solid #d5dee8;
        border-radius: 12px;
        overflow: hidden;
        min-height: calc(100vh - 220px);
    }
    .trr-system-preview-frame-wrap iframe {
        display: block;
        width: 100%;
        min-height: calc(100vh - 220px);
        height: 85vh;
        border: 0;
        background: #e9ecef;
    }
    @media print {
        .trr-system-preview-chrome,
        .breadcrumbs-top,
        .navbar,
        .main-menu,
        .header-navbar {
            display: none !important;
        }
        .trr-system-preview-frame-wrap {
            border: none;
            background: #fff;
        }
        .trr-system-preview-frame-wrap iframe {
            height: auto;
            min-height: 100vh;
        }
    }
</style>
@endsection

@section('content2')
<main class="trr-system-preview-page">
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('sample-workflow', ['status' => $batch->status ?? 'Samples In Lab']), 'name' => 'Sample Workflow', 'icon' => null],
            ['link' => $batchBackUrl ?? route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status]), 'name' => $batch->batch_code ?? 'Batch', 'icon' => null],
            ['link' => '#', 'name' => 'Preview Test Report', 'icon' => null],
        ];
        $frameUrl = route('generateTestRequestReport', [
            'batch_id' => $batch->id,
            'mode' => 'preview-doc',
            'lang' => $language ?? 'en',
        ]);
    @endphp

    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="trr-system-preview-chrome">
        <div>
            <span class="trr-preview-badge">Draft preview</span>
            <h1>Test Request Report</h1>
            <p>
                This is how the final report will look with the current results and comments.
                It does not issue a revision and is not saved as an official PDF.
                Provisional number: <strong style="color:#fff;">{{ $reportNumber }}</strong>
            </p>
        </div>
        <div class="trr-system-preview-actions">
            <button type="button" class="btn-print" onclick="document.getElementById('trr-preview-frame')?.contentWindow?.print()">Print</button>
            <a href="{{ $batchBackUrl }}" class="btn-back">← Back to Batch</a>
        </div>
    </div>

    <div class="trr-system-preview-frame-wrap">
        <iframe
            id="trr-preview-frame"
            title="Test Request Report Preview"
            src="{{ $frameUrl }}"
            loading="eager"
        ></iframe>
    </div>
</main>
@endsection
