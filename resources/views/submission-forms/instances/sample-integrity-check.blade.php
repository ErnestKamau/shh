@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Sample Integrity Check | {{ $instance->getDocumentControlNumber() ?? $instance->form_number }}</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme request-view-page workflow-theme lab-surface-theme" data-ls-type="plex">
    @include('layouts.lab.partials.lab-panel-theme-styles')
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.partials.page-header-styles')
    @include('layouts.lab.partials.request-view-page-styles')

    <style>
        .request-view-page .sample-integrity-check-page .rv-request-info-panel {
            margin-bottom: 1rem;
        }

        .request-view-page .sample-integrity-check-page .rv-sample-table {
            overflow-anchor: none;
        }

        .request-view-page .sample-integrity-check-page .integrity-master-detail {
            display: grid;
            grid-template-columns: minmax(200px, 240px) minmax(0, 1fr);
            min-height: 420px;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-rail {
            border-right: 1px solid rgba(30, 41, 59, 0.12);
            background: #f8fafc;
        }

        .request-view-page .sample-integrity-check-page .integrity-rail-title {
            padding: 0.75rem 1rem 0.35rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-item {
            border: 0;
            border-bottom: 1px solid rgba(30, 41, 59, 0.08);
            padding: 0.75rem 1rem;
            background: transparent;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-item-row {
            gap: 0.35rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-select {
            border: 0;
            background: transparent;
            padding: 0;
            color: inherit;
            cursor: pointer;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-select:focus {
            outline: none;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-info-btn {
            flex-shrink: 0;
            margin-top: 0.05rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-item.is-active {
            background: #fff;
            box-shadow: inset 3px 0 0 var(--ls-color-primary, #9f1239);
        }

        .request-view-page .sample-integrity-check-page .integrity-sample-meta {
            margin-top: 0.2rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .request-view-page .sample-integrity-check-page .integrity-progress {
            margin-top: 0.45rem;
            height: 4px;
            border-radius: 999px;
            background: rgba(30, 41, 59, 0.08);
            overflow: hidden;
        }

        .request-view-page .sample-integrity-check-page .integrity-progress-bar {
            height: 100%;
            background: var(--ls-color-primary, #9f1239);
        }

        .request-view-page .sample-integrity-check-page .integrity-test-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: flex-start;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(30, 41, 59, 0.1);
        }

        .request-view-page .sample-integrity-check-page .integrity-toolbar-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-toolbar-controls .form-control {
            min-width: 180px;
            max-width: 240px;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-bar {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            padding: 0.65rem 1rem;
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
            gap: 0.5rem 0;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-count {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-clear-btn {
            margin-left: 0.15rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-lab-sections-group {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.65rem;
            align-items: center;
            flex: 1 1 auto;
            margin-left: 2.5rem;
            min-width: 0;
            max-width: 560px;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-toolbar-spacer {
            flex: 1 1 auto;
            min-width: 1rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-group-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            white-space: nowrap;
            line-height: 1;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-more-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex: 0 0 auto;
            margin-left: 0.5rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-more-actions .rv-icon-btn.is-active {
            background: var(--ls-color-primary-soft, #fce7f3);
            color: var(--ls-color-primary, #9f1239);
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-overflow .dropdown-toggle::after {
            display: none;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap {
            flex: 1 1 280px;
            width: 100%;
            min-width: 220px;
            max-width: 480px;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-sections {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            width: 100%;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem 1.25rem;
            width: 100%;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-row {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
            width: 100%;
            padding-top: 0.35rem;
            border-top: 1px dashed #e2e8f0;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-head {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            align-items: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            line-height: 1.8;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-sections {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 160px;
            flex: 1 1 180px;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-group-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #64748b;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-analyst-picks {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-chip-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.1rem 0.45rem;
            border-radius: 999px;
            background: var(--ls-color-primary-soft, #fce7f3);
            color: var(--ls-color-primary, #9f1239);
            font-size: 0.72rem;
            font-weight: 600;
        }

        .request-view-page .sample-integrity-check-page .integrity-chip--section {
            padding-right: 0.2rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-chip-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: inherit;
            line-height: 1;
            cursor: pointer;
        }

        .request-view-page .sample-integrity-check-page .integrity-chip-remove:hover {
            background: rgba(159, 18, 57, 0.16);
        }

        .request-view-page .sample-integrity-check-page .integrity-chip-remove .mdi {
            font-size: 0.7rem;
            line-height: 1;
        }

        .request-view-page .sample-integrity-check-page .integrity-test-table tbody tr.is-incomplete td {
            background: transparent;
        }

        .request-view-page .sample-integrity-check-page .integrity-editor-row td {
            background: #f8fafc;
            border-top: 0;
        }

        .request-view-page .sample-integrity-check-page .integrity-editor {
            padding: 0.75rem 0.35rem 0.35rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-editor-grid {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(260px, 1.4fr);
            gap: 1rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container {
            min-width: 200px;
            width: 100% !important;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-selection--multiple {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 28px !important;
            height: auto !important;
            padding: 2px 22px 2px 4px !important;
            line-height: 1.2;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            gap: 2px;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-selection__clear {
            position: absolute;
            top: 50%;
            right: 4px;
            transform: translateY(-50%);
            float: none !important;
            display: inline-flex !important;
            align-items: center;
            margin: 0 !important;
            padding: 0;
            height: auto;
            line-height: 1;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-selection__choice {
            display: inline-flex !important;
            align-items: center;
            margin: 0 !important;
            padding: 1px 4px 1px 6px !important;
            line-height: 1.2;
            font-size: 0.72rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-search--inline {
            float: none !important;
            display: inline-flex !important;
            align-items: center;
            margin: 0 !important;
            padding: 0 !important;
        }

        .request-view-page .sample-integrity-check-page .integrity-lab-section-select + .select2-container .select2-search--inline .select2-search__field {
            margin: 0 !important;
            padding: 0 !important;
            height: 22px !important;
            min-height: 22px !important;
            line-height: 22px !important;
            font-size: 0.75rem;
        }

        /* Bulk lab-section Select2 — must come after row-editor rules above */
        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container {
            width: 100% !important;
            max-width: 100%;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection--multiple {
            display: flex !important;
            align-items: center !important;
            min-height: 34px !important;
            height: auto !important;
            padding: 4px 8px !important;
            overflow: hidden;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection__clear {
            display: none !important;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center !important;
            gap: 4px;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 24px;
            line-height: 1.2;
            list-style: none;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.is-empty .select2-container .select2-selection__rendered {
            justify-content: center;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.has-values .select2-container .select2-selection__rendered {
            justify-content: flex-start;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection__choice {
            display: inline-flex !important;
            align-items: center;
            float: none !important;
            margin: 0 !important;
            padding: 2px 8px 2px 4px !important;
            max-width: 100%;
            line-height: 1.2;
            font-size: 0.72rem;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.is-empty .select2-container .select2-search--inline {
            float: none !important;
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.has-values .select2-container .select2-search--inline {
            float: none !important;
            display: inline-flex !important;
            width: 0.75em !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: 0 0 auto;
        }

        .request-view-page.lab-surface-theme .sample-integrity-check-page .integrity-bulk-select-wrap.is-empty .select2-container .select2-search--inline .select2-search__field,
        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.is-empty .select2-container .select2-search--inline .select2-search__field {
            display: block !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 24px !important;
            min-height: 24px !important;
            line-height: 24px !important;
            font-size: 0.8125rem !important;
            text-align: center !important;
        }

        .request-view-page.lab-surface-theme .sample-integrity-check-page .integrity-bulk-select-wrap.has-values .select2-container .select2-search--inline .select2-search__field,
        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap.has-values .select2-container .select2-search--inline .select2-search__field {
            width: 0.75em !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 22px !important;
            min-height: 22px !important;
            line-height: 22px !important;
            text-align: left !important;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-search--inline .select2-search__field::placeholder {
            text-align: center;
            color: #94a3b8;
            opacity: 1;
        }

        .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container .select2-selection__placeholder {
            display: none !important;
        }

        @media (max-width: 991.98px) {
            .request-view-page .sample-integrity-check-page .integrity-master-detail {
                grid-template-columns: 1fr;
            }

            .request-view-page .sample-integrity-check-page .integrity-sample-rail {
                border-right: 0;
                border-bottom: 1px solid rgba(30, 41, 59, 0.12);
                max-height: 220px;
                overflow: auto;
            }

            .request-view-page .sample-integrity-check-page .integrity-editor-grid {
                grid-template-columns: 1fr;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-count,
            .request-view-page .sample-integrity-check-page .integrity-bulk-lab-sections-group,
            .request-view-page .sample-integrity-check-page .integrity-bulk-more-actions {
                width: 100%;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-toolbar-spacer {
                display: none;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-lab-sections-group {
                margin-left: 0;
                max-width: 100%;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-more-actions {
                margin-left: 0;
                justify-content: flex-start;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap {
                width: 100%;
                min-width: 0;
            }

            .request-view-page .sample-integrity-check-page .integrity-bulk-select-wrap .select2-container {
                width: 100% !important;
            }
        }
    </style>

    @php
        $formNumber = $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Pending';
        $boardStatus = 'Samples Receiving';
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('sample-workflow', ['status' => $boardStatus, 'tab' => 'sample_integrity_check']), 'name' => 'Integrity Check', 'icon' => null],
            ['link' => '#', 'name' => 'Request '.$formNumber, 'icon' => null],
        ];
    @endphp

    <div class="request-view-shell">
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('sampleworkflow.sample-integrity-check-page', [
            'submissionFormId' => $submissionForm->id,
            'instanceId' => $instance->id,
        ], key('sample-integrity-'.$instance->id))
    </div>
</main>
@endsection
