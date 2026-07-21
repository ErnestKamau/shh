@extends('layouts.lab.layout.app')

@section('title2')
<title>Preview Test Report — {{ $reportNumber }}</title>
<style>
    /*
     * Compact in-app preview: maximize report viewport, keep chrome quiet.
     */
    .trr-system-preview-page {
        --trr-preview-ink: #1c2430;
        --trr-preview-muted: #6b7280;
        --trr-preview-line: #e5e7eb;
        --trr-preview-surface: #f3f4f6;
        display: flex;
        flex-direction: column;
        /* Top app bar (~56px) + page pad; leave most of the screen for the report */
        height: calc(100vh - 72px);
        min-height: 0;
        padding: 0 8px 10px;
        overflow: hidden;
    }

    .trr-system-preview-page .content-header,
    .trr-system-preview-page .breadcrumbs-top {
        margin-bottom: 6px !important;
        padding-bottom: 0 !important;
        flex-shrink: 0;
    }

    .trr-system-preview-chrome {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0 10px;
        margin: 0 0 8px;
        border-bottom: 1px solid var(--trr-preview-line);
    }

    .trr-system-preview-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex-wrap: wrap;
    }

    .trr-preview-badge {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 650;
        color: #9a3412;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 4px;
        padding: 2px 7px;
        line-height: 1.4;
        white-space: nowrap;
    }

    .trr-system-preview-chrome h1 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: var(--trr-preview-ink);
        font-family: inherit;
        line-height: 1.2;
        white-space: nowrap;
    }

    .trr-preview-sep {
        width: 1px;
        height: 14px;
        background: var(--trr-preview-line);
        flex-shrink: 0;
    }

    .trr-preview-number {
        font-size: 12px;
        color: var(--trr-preview-muted);
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .trr-preview-number strong {
        color: var(--trr-preview-ink);
        font-weight: 600;
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
        gap: 5px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 550;
        padding: 6px 11px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        font-family: inherit;
        line-height: 1.2;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .trr-system-preview-actions .btn-print {
        background: var(--trr-preview-ink);
        color: #fff;
        border-color: var(--trr-preview-ink);
    }

    .trr-system-preview-actions .btn-print:hover {
        background: #111827;
    }

    .trr-system-preview-actions .btn-back {
        background: #fff;
        color: #374151;
        border-color: #d1d5db;
    }

    .trr-system-preview-actions .btn-back:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .trr-system-preview-frame-wrap {
        flex: 1 1 auto;
        min-height: 0;
        background: var(--trr-preview-surface);
        border: 1px solid var(--trr-preview-line);
        border-radius: 8px;
        overflow: hidden;
    }

    .trr-system-preview-frame-wrap iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
        background: var(--trr-preview-surface);
    }

    @media (max-width: 768px) {
        .trr-system-preview-page {
            height: auto;
            overflow: visible;
            min-height: calc(100vh - 72px);
        }

        .trr-system-preview-chrome {
            flex-direction: column;
            align-items: flex-start;
        }

        .trr-system-preview-frame-wrap {
            min-height: 70vh;
            height: 70vh;
        }
    }

    @media print {
        .trr-system-preview-chrome,
        .breadcrumbs-top,
        .navbar,
        .main-menu,
        .header-navbar {
            display: none !important;
        }

        .trr-system-preview-page {
            height: auto;
            overflow: visible;
            padding: 0;
        }

        .trr-system-preview-frame-wrap {
            border: none;
            background: #fff;
            height: auto;
            overflow: visible;
        }

        .trr-system-preview-frame-wrap iframe {
            height: 100vh;
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
            ['link' => '#', 'name' => 'Preview', 'icon' => null],
        ];
        $frameUrl = route('generateTestRequestReport', [
            'batch_id' => $batch->id,
            'mode' => 'preview-doc',
            'lang' => $language ?? 'en',
            'include_reference_method' => !empty($includeReferenceMethod) ? 1 : 0,
        ]);
    @endphp

    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="trr-system-preview-chrome">
        <div class="trr-system-preview-meta">
            <span class="trr-preview-badge">Draft</span>
            <h1>Test Report</h1>
            <span class="trr-preview-sep" aria-hidden="true"></span>
            <span class="trr-preview-number">{{ $reportNumber }}</span>
        </div>
        <div class="trr-system-preview-actions">
            <button type="button" class="btn-print" onclick="document.getElementById('trr-preview-frame')?.contentWindow?.print()">Print</button>
            <a href="{{ $batchBackUrl }}" class="btn-back">Back to Batch</a>
        </div>
    </div>

    <div class="trr-system-preview-frame-wrap">
        <iframe
            id="trr-preview-frame"
            title="Test Report Preview"
            src="{{ $frameUrl }}"
            loading="eager"
        ></iframe>
    </div>
</main>
@endsection
