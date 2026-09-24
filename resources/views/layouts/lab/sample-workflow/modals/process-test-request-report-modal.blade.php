{{-- ═══════════════════════════════════════════════════════════════
     Process Test Report Modal — LS UI Kit (gallery: ls-modal-cta-form)
     Variables expected: $batch
     Single root required (included from Livewire batch.header).
     ═══════════════════════════════════════════════════════════════ --}}
<div class="ptrr-modal-host" wire:ignore>
@once
    @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
@endonce

@php
    $pendingAmendment = \App\BatchAmmendment::resolveForBatch($batch);
    $inAmendment = (int) ($batch->in_ammendment_proccess ?? 0) === 1;
    $nextFromSequence = ((int) ($batch->test_request_report_sequence ?? 0)) + 1;
    $amendmentVersion = max(1, (int) ($batch->is_amendment ?? 1));
    $nextRev = max($nextFromSequence, $amendmentVersion);
    $nextRevPadded = str_pad((string) $nextRev, 2, '0', STR_PAD_LEFT);

    $ptrrLabSections = $batch->labSectionsForDisplay();
    $ptrrSamples = \App\SamplesCategory::query()
        ->where('sample_header_id', $batch->id)
        ->orderBy('sample_code')
        ->get(['id', 'sample_code', 'comments']);
    if ($ptrrSamples->isEmpty() && \App\SampleDetails::where('sample_header_id', $batch->id)->exists()) {
        app(\App\Services\Sampleworkflow\SamplesByCategoryViewService::class)->recreate();
        $ptrrSamples = \App\SamplesCategory::query()
            ->where('sample_header_id', $batch->id)
            ->orderBy('sample_code')
            ->get(['id', 'sample_code', 'comments']);
    }
    $ptrrResultsBySample = \App\CapturedResult::query()
        ->where('sample_header_id', $batch->id)
        ->whereNotNull('lab_section_id')
        ->get(['sample_detail_id', 'lab_section_id'])
        ->groupBy(static fn (\App\CapturedResult $row): string => (string) $row->sample_detail_id)
        ->map(static function ($rows): string {
            return $rows->pluck('lab_section_id')
                ->map(static fn ($id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->implode(',');
        });

    $isBrazilExportationReport = app(\App\Services\Sampleworkflow\TestRequestReportDataService::class)
        ->isBrazilExportationBatch($batch);

    $batchContacts = collect();
    $companyUnits = collect();
    if (!empty($batch->crm_customer_id)) {
        $companyUnits = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $batch->crm_customer_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $batchContacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $batch->crm_customer_id)
            ->where('active', 1)->get();
    }

    $companyUnitsById = $companyUnits->keyBy('id');

    $resolveContactUnitNames = function ($contact) use ($companyUnitsById) {
        $contactUnits = collect(explode(',', (string) ($contact->unit_name ?? '')))
            ->map(fn ($unit) => trim((string) $unit))
            ->filter()
            ->map(function ($unit) use ($companyUnitsById) {
                if (\Illuminate\Support\Str::isUuid($unit)) {
                    return trim((string) ($companyUnitsById->get($unit)?->name ?? ''));
                }

                return $unit;
            })
            ->filter();

        if (!empty($contact->crm_company_unit_id)) {
            $mappedUnitName = trim((string) ($companyUnitsById->get($contact->crm_company_unit_id)?->name ?? ''));
            if ($mappedUnitName !== '') {
                $contactUnits->push($mappedUnitName);
            }
        }

        return $contactUnits
            ->unique(fn ($name) => strtolower((string) $name))
            ->values();
    };

    $companyUnitOptions = $companyUnits
        ->pluck('name')
        ->map(fn ($name) => trim((string) $name))
        ->filter()
        ->values();

    foreach ($batchContacts as $contact) {
        $companyUnitOptions = $companyUnitOptions->merge($resolveContactUnitNames($contact));
    }

    $companyUnitOptions = $companyUnitOptions
        ->unique(fn ($name) => strtolower((string) $name))
        ->sort(fn ($a, $b) => strcasecmp((string) $a, (string) $b))
        ->values();

    $defaultCompanyUnit = trim((string) ($batch->crm_unit_name ?? ''));
    if (\Illuminate\Support\Str::isUuid($defaultCompanyUnit)) {
        $defaultCompanyUnit = trim((string) ($companyUnitsById->get($defaultCompanyUnit)?->name ?? ''));
    }
    if (
        $defaultCompanyUnit === ''
        || !$companyUnitOptions->contains(fn ($name) => strtolower((string) $name) === strtolower($defaultCompanyUnit))
    ) {
        $defaultCompanyUnit = '';
    }

    $existingRevisions = \App\Models\TestRequestReportRevision::where('batch_id', $batch->id)
        ->orderByDesc('revision_no')->get();

    $canDeliver = $batchContacts->isNotEmpty() && $companyUnitOptions->isNotEmpty();
@endphp

<div class="modal fade" id="process-test-request-report-modal" tabindex="-1" role="dialog"
     aria-labelledby="ptrr-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ptrr-dialog" role="document">
        <div class="modal-content ptrr-modal-shell">
            <div class="ls-ui-kit ptrr-ls">
                <form method="POST" action="{{ route('processTestRequestReport') }}" id="process-trr-form" target="_blank"
                      class="ls-modal-card ls-modal-card--wide ls-modal-card--report"
                      data-ls-modal-type="cta-form">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                    <div class="ls-modal-card__header">
                        <div>
                            <h5 class="ls-modal-card__title" id="ptrr-modal-label">Generate Test Report</h5>
                            <p class="ls-modal-card__subtitle">
                                Choose scope, language, and columns — then generate or send.
                            </p>
                        </div>
                        <button type="button" class="ls-modal-card__close" data-dismiss="modal" aria-label="Close">
                            <i class="mdi mdi-close" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="ls-modal-card__body ptrr-modal-body">
                        <style>
                            .ptrr-dialog {
                                max-width: 44rem;
                                width: calc(100% - 1.5rem);
                                margin: 1rem auto;
                            }
                            .ptrr-modal-shell {
                                border: none;
                                background: transparent;
                                box-shadow: none;
                            }
                            .ls-modal-card--report {
                                max-width: none;
                                width: 100%;
                                max-height: calc(100vh - 3rem);
                                box-shadow: 0 16px 48px rgba(15, 23, 42, 0.18);
                            }
                            .ptrr-ls .ptrr-modal-body {
                                overflow-y: auto;
                                flex: 1 1 auto;
                                min-height: 0;
                                max-height: min(62vh, 36rem);
                            }
                            .ptrr-ls .ls-modal-card--report {
                                display: flex;
                                flex-direction: column;
                            }
                            .ptrr-chip-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(9.5rem, 1fr));
                                gap: 0.45rem;
                            }
                            .ptrr-chip {
                                display: flex;
                                align-items: flex-start;
                                gap: 0.45rem;
                                margin: 0;
                                padding: 0.55rem 0.65rem;
                                border: 1.5px solid var(--ls-border, #e2e8f0);
                                border-radius: 10px;
                                background: #fafbfc;
                                cursor: pointer;
                                transition: border-color .15s, background .15s, box-shadow .15s;
                            }
                            .ptrr-chip:hover { border-color: #c4a0a0; background: #fff; }
                            .ptrr-chip:has(input:checked) {
                                border-color: var(--ls-accent, #8b1e2d);
                                background: #fdf4f4;
                                box-shadow: inset 0 0 0 1px rgba(139, 30, 45, 0.08);
                            }
                            .ptrr-chip input { margin-top: 0.15rem; flex-shrink: 0; }
                            .ptrr-chip strong { display: block; font-size: 0.78rem; color: var(--ls-ink, #1e293b); line-height: 1.3; }
                            .ptrr-chip small { display: block; font-size: 0.65rem; color: var(--ls-muted, #64748b); margin-top: 0.1rem; }
                            .ptrr-sample-list {
                                border: 1px solid var(--ls-border, #e2e8f0);
                                border-radius: 10px;
                                max-height: 11rem;
                                overflow-y: auto;
                                background: #f8fafc;
                            }
                            .ptrr-sample-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 0.55rem;
                                margin: 0;
                                padding: 0.55rem 0.7rem;
                                border-bottom: 1px solid #eef2f7;
                                cursor: pointer;
                            }
                            .ptrr-sample-row:last-child { border-bottom: none; }
                            .ptrr-sample-row:hover { background: #fff; }
                            .ptrr-sample-row input { margin-top: 0.15rem; }
                            .ptrr-sample-row strong { display: block; font-size: 0.78rem; color: var(--ls-ink, #1e293b); }
                            .ptrr-sample-row .ptrr-sample-desc { font-size: 0.68rem; color: var(--ls-muted, #64748b); }
                            #ptrr-samples-panel[data-disabled="1"] { opacity: .55; pointer-events: none; }
                            .ptrr-panel-hint {
                                font-size: 0.68rem;
                                color: var(--ls-muted, #64748b);
                                margin: -0.25rem 0 0.65rem;
                                line-height: 1.4;
                            }
                            .ptrr-panel-actions {
                                display: flex;
                                gap: 0.75rem;
                                font-size: 0.72rem;
                            }
                            .ptrr-panel-actions .btn-link {
                                font-size: 0.72rem;
                                font-weight: 600;
                                color: #2563eb;
                            }
                            .ptrr-lang-card:has(.ptrr-lang-radio:checked),
                            .ptrr-channel-card:has(.ptrr-ch-check:checked) {
                                border-color: var(--ls-accent, #8b1e2d) !important;
                                background: #fdf4f4;
                            }
                            .ptrr-portal-lang-card:has(.ptrr-portal-lang-check:checked) {
                                border-color: #4A90D9 !important;
                                background: #f4f8fd;
                            }
                            .ptrr-check-list {
                                border: 1px solid var(--ls-border, #e2e8f0);
                                border-radius: 10px;
                                max-height: 7.5rem;
                                overflow-y: auto;
                                padding: 0.5rem 0.75rem;
                                background: #f8fafc;
                            }
                            .ptrr-check-list--grid-3 {
                                display: grid;
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                                gap: 0.35rem 0.75rem;
                                align-items: start;
                            }
                            .ptrr-check-list--grid-3 .custom-control {
                                margin-bottom: 0;
                                min-width: 0;
                            }
                            .ptrr-check-list--grid-3 .custom-control-label {
                                overflow-wrap: anywhere;
                            }
                            .ptrr-check-list--grid-3 #ptrr-no-contacts-for-unit {
                                grid-column: 1 / -1;
                            }
                            @media (max-width: 576px) {
                                .ptrr-check-list--grid-3 {
                                    grid-template-columns: 1fr;
                                }
                            }
                            .ptrr-option-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 0.5rem;
                                padding: 0.35rem 0;
                                margin: 0;
                                font-size: 0.78rem;
                                cursor: pointer;
                            }
                            .ptrr-option-row + .ptrr-option-row { border-top: 1px solid #eef2f7; }
                            .ptrr-option-row input { margin-top: 0.2rem; }
                            .ptrr-rev-table-wrap {
                                max-height: 9rem;
                                overflow-y: auto;
                                border: 1px solid var(--ls-border, #e2e8f0);
                                border-radius: 10px;
                            }
                            .ptrr-rev-table-wrap .table { font-size: 0.72rem; margin: 0; }
                            .ptrr-rev-table-wrap thead { background: #f8fafc; position: sticky; top: 0; }
                            .ptrr-callout--warn {
                                background: #fffbeb;
                                border-color: #fde68a;
                            }
                            .ptrr-callout--warn .ls-modal-callout__icon { color: #b45309; }
                            .ptrr-callout--section {
                                background: #fdf4f4;
                                border-color: #f0d4d4;
                            }
                            .ptrr-callout--section .ls-modal-callout__icon { color: var(--ls-accent, #8b1e2d); }
                            .ptrr-ls .ls-btn--send {
                                background: #166534;
                                border-color: #166534;
                                color: #fff;
                            }
                            .ptrr-ls .ls-btn--send:hover,
                            .ptrr-ls .ls-btn--send:focus {
                                background: #14532d;
                                border-color: #14532d;
                                color: #fff;
                            }
                            .ptrr-ls .ls-soft-card { margin-bottom: 0.65rem; }
                            .ptrr-ls .ls-soft-card__body { padding-top: 0.65rem; }
                            .ptrr-lang-badge {
                                background: var(--ls-accent, #8b1e2d);
                                color: #fff;
                                font-weight: 700;
                                font-size: 0.62rem;
                                padding: 0.15rem 0.4rem;
                                border-radius: 4px;
                                letter-spacing: 0.04em;
                                flex-shrink: 0;
                            }
                            .ptrr-lang-badge--portal { background: #4A90D9; }
                            .ptrr-channel-card,
                            .ptrr-lang-card,
                            .ptrr-portal-lang-card {
                                display: flex;
                                align-items: center;
                                gap: 0.55rem;
                                padding: 0.55rem 0.75rem;
                                border: 1.5px solid var(--ls-border, #e2e8f0);
                                border-radius: 10px;
                                cursor: pointer;
                                background: #fafbfc;
                                flex: 1;
                                min-width: 7.5rem;
                                transition: border-color .15s, background .15s;
                                margin: 0;
                            }
                            .ptrr-channel-card { flex: 0 1 auto; min-width: 6.5rem; }
                            .ptrr-channel-row,
                            .ptrr-lang-row {
                                display: flex;
                                gap: 0.5rem;
                                flex-wrap: wrap;
                            }
                        </style>

                        @if($inAmendment && $pendingAmendment)
                            <div class="ls-modal-callout ptrr-callout--warn" role="status">
                                <span class="ls-modal-callout__icon" aria-hidden="true">
                                    <i class="mdi mdi-file-replace-outline"></i>
                                </span>
                                <div>
                                    <p class="ls-modal-callout__title">Amendment in progress (V{{ $pendingAmendment->version_number }})</p>
                                    <p class="ls-modal-callout__text">{{ $pendingAmendment->reason }}</p>
                                    <p class="ls-modal-callout__text mt-1 mb-0">
                                        Re-issuing clears the amendment flag and prints the reason on the PDF.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="ls-modal-callout" id="ptrr-mode-banner" role="status">
                            <span class="ls-modal-callout__icon" aria-hidden="true">
                                <i class="mdi mdi-information-outline"></i>
                            </span>
                            <div id="ptrr-mode-banner-text">
                                <p class="ls-modal-callout__title mb-0">
                                    Revision {{ $nextRevPadded }} · {{ $batch->batch_code }}-R{{ $nextRevPadded }}
                                </p>
                                <p class="ls-modal-callout__text mb-0">Full official Test Report</p>
                            </div>
                        </div>

                        <div class="ls-modal-summary" aria-live="polite">
                            <div class="ls-modal-summary__item">
                                <span class="ls-modal-summary__label">Job</span>
                                <p class="ls-modal-summary__value">{{ $batch->batch_code }}</p>
                            </div>
                            <div class="ls-modal-summary__item">
                                <span class="ls-modal-summary__label">Revision</span>
                                <p class="ls-modal-summary__value">R{{ $nextRevPadded }}</p>
                            </div>
                            <div class="ls-modal-summary__item">
                                <span class="ls-modal-summary__label">Samples</span>
                                <p class="ls-modal-summary__value">{{ $ptrrSamples->count() }}</p>
                            </div>
                        </div>

                        @if($ptrrLabSections !== [])
                            <div class="ls-modal-panel">
                                <h4 class="ls-modal-panel__title">
                                    <i class="mdi mdi-flask-outline mr-1" aria-hidden="true"></i>
                                    Lab sections
                                </h4>
                                <p class="ptrr-panel-hint">
                                    Leave all unchecked for the full official report. Check one or more to print only those sections’ tests (one page per sample).
                                </p>
                                <div class="ptrr-chip-grid" id="ptrr-lab-sections">
                                    @foreach($ptrrLabSections as $section)
                                        <label class="ptrr-chip" for="ptrr-section-{{ $section['id'] }}">
                                            <input type="checkbox"
                                                   class="ptrr-section-check"
                                                   id="ptrr-section-{{ $section['id'] }}"
                                                   name="lab_section_ids[]"
                                                   value="{{ $section['id'] }}">
                                            <span>
                                                <strong>{{ $section['name'] !== '' ? $section['name'] : $section['code'] }}</strong>
                                                @if($section['code'] !== '' && $section['name'] !== '' && $section['code'] !== $section['name'])
                                                    <small>{{ $section['code'] }}</small>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="ls-modal-panel" id="ptrr-samples-panel" data-disabled="1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h4 class="ls-modal-panel__title mb-0">
                                        <i class="mdi mdi-test-tube mr-1" aria-hidden="true"></i>
                                        Samples
                                    </h4>
                                    <div class="ptrr-panel-actions">
                                        <button type="button" class="btn btn-link btn-sm p-0" id="ptrr-select-all-samples">Select all</button>
                                        <button type="button" class="btn btn-link btn-sm p-0" id="ptrr-clear-samples">Clear</button>
                                    </div>
                                </div>
                                <p class="ptrr-panel-hint">Choose which samples to include for the selected lab section(s).</p>
                                <div class="ptrr-sample-list" id="ptrr-sample-list">
                                    @forelse($ptrrSamples as $sample)
                                        @php
                                            $sampleId = (string) $sample->id;
                                            $sectionIdsCsv = (string) ($ptrrResultsBySample[$sampleId] ?? '');
                                            $sampleLabel = format_sample_code($sample->sample_code ?? '') ?: $sampleId;
                                            $sampleDesc = trim(strip_tags((string) ($sample->comments ?? '')));
                                        @endphp
                                        <label class="ptrr-sample-row"
                                               data-section-ids="{{ $sectionIdsCsv }}"
                                               style="display:none;">
                                            <input type="checkbox"
                                                   class="ptrr-sample-check"
                                                   name="sample_ids[]"
                                                   value="{{ $sampleId }}"
                                                   disabled>
                                            <span>
                                                <strong>{{ $sampleLabel }}</strong>
                                                @if($sampleDesc !== '')
                                                    <span class="ptrr-sample-desc">{{ \Illuminate\Support\Str::limit($sampleDesc, 90) }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @empty
                                        <div class="p-3 text-muted small mb-0">No samples on this job.</div>
                                    @endforelse
                                </div>
                                <small class="text-muted d-block mt-2" id="ptrr-sample-hint" style="font-size:0.68rem;">
                                    Select a lab section to choose samples.
                                </small>
                            </div>
                        @endif

                        <div class="ls-field ls-compact mb-3">
                            <span class="ls-field__label">
                                <i class="mdi mdi-translate" aria-hidden="true"></i>
                                Report language
                                <span class="ls-req">*</span>
                            </span>
                            <div class="ptrr-lang-row" role="radiogroup" aria-label="Report language">
                                @foreach([
                                    'en' => ['label' => 'English', 'sub' => 'Default', 'code' => 'EN'],
                                    'ar' => ['label' => 'Arabic',  'sub' => 'عربي',   'code' => 'AR'],
                                    'pt' => ['label' => 'Portuguese', 'sub' => 'Português', 'code' => 'PT'],
                                ] as $val => $lang)
                                    <label for="ptrr-lang-{{ $val }}" class="ptrr-lang-card">
                                        <input type="radio" name="language" id="ptrr-lang-{{ $val }}"
                                               value="{{ $val }}" {{ $val === 'en' ? 'checked' : '' }}
                                               class="ptrr-lang-radio" style="display:none;">
                                        <span class="ptrr-lang-badge">{{ $lang['code'] }}</span>
                                        <span>
                                            <strong style="font-size:0.78rem;display:block;line-height:1.25;">{{ $lang['label'] }}</strong>
                                            <span style="font-size:0.65rem;color:var(--ls-muted,#64748b);">{{ $lang['sub'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ls-soft-card is-expanded" x-data="{ open: true }" :class="{ 'is-expanded': open }">
                            <button type="button" class="ls-soft-card__header" @click="open = !open">
                                <span>
                                    <i class="mdi mdi-table-column mr-1" aria-hidden="true"></i>
                                    Result table options
                                </span>
                                <i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                            </button>
                            <div class="ls-soft-card__body" x-show="open">
                                <label class="ptrr-option-row" for="ptrr-include-reference-method">
                                    <input type="checkbox" id="ptrr-include-reference-method"
                                           name="include_reference_method" value="1">
                                    <span>Include <strong>Reference Method</strong> column</span>
                                </label>
                                <label class="ptrr-option-row" for="ptrr-show-specification">
                                    <input type="checkbox" id="ptrr-show-specification"
                                           name="show_specification" value="1"
                                           {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                    <span>Include <strong>Specification limit</strong> column</span>
                                </label>
                                <label class="ptrr-option-row" for="ptrr-show-specification-standard">
                                    <input type="checkbox" id="ptrr-show-specification-standard"
                                           name="show_specification_standard" value="1"
                                           {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                    <span>Include <strong>Specification Standard</strong> column</span>
                                </label>
                                <label class="ptrr-option-row" for="ptrr-show-mu-percent">
                                    <input type="checkbox" id="ptrr-show-mu-percent"
                                           name="show_mu_percent" value="1"
                                           {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                    <span>Include <strong>M.U%</strong> column</span>
                                </label>
                            </div>
                        </div>

                        <div class="ls-field ls-compact mb-2">
                            <label class="ls-field__label" for="ptrr-notes">
                                <i class="mdi mdi-note-text-outline" aria-hidden="true"></i>
                                Revision notes
                                <span class="font-weight-normal text-muted" style="text-transform:none;letter-spacing:0;">(optional)</span>
                            </label>
                            <textarea name="notes" id="ptrr-notes" rows="2" class="form-control form-control-sm"
                                      placeholder="Describe what changed in this revision…">{{ $inAmendment && !empty($pendingAmendment?->reason) ? $pendingAmendment->reason : '' }}</textarea>
                        </div>

                        @if($existingRevisions->isNotEmpty())
                            <div class="ls-soft-card" x-data="{ open: false }" :class="{ 'is-expanded': open }">
                                <button type="button" class="ls-soft-card__header" @click="open = !open">
                                    <span>
                                        <i class="mdi mdi-history mr-1" aria-hidden="true"></i>
                                        Revision history ({{ $existingRevisions->count() }})
                                    </span>
                                    <i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                                </button>
                                <div class="ls-soft-card__body" x-show="open" x-cloak>
                                    <div class="ptrr-rev-table-wrap">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-3">Rev.</th>
                                                    <th>Language</th>
                                                    <th>By</th>
                                                    <th>Date</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($existingRevisions as $rev)
                                                    <tr>
                                                        <td class="pl-3 font-weight-bold">
                                                            R{{ str_pad($rev->revision_no, 2, '0', STR_PAD_LEFT) }}
                                                        </td>
                                                        <td>
                                                            @php $langLabels = ['en'=>'English','ar'=>'Arabic','pt'=>'Portuguese']; @endphp
                                                            <span class="badge badge-light border">
                                                                {{ $langLabels[$rev->language] ?? strtoupper($rev->language) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional(\App\User::find($rev->generated_by))->name ?? '—' }}</td>
                                                        <td>{{ $rev->created_at ? $rev->created_at->format('d/m/Y') : '—' }}</td>
                                                        <td class="text-muted">{{ $rev->notes ? \Illuminate\Support\Str::limit($rev->notes, 40) : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div id="ptrr-delivery-section">
                            <div class="ls-soft-card is-expanded" x-data="{ open: true }" :class="{ 'is-expanded': open }">
                                <button type="button" class="ls-soft-card__header" @click="open = !open">
                                    <span>
                                        <i class="mdi mdi-send-outline mr-1" aria-hidden="true"></i>
                                        Send report to
                                        <span class="text-muted font-weight-normal">(optional)</span>
                                    </span>
                                    <i class="mdi ls-soft-card__chevron" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                                </button>
                                <div class="ls-soft-card__body" x-show="open">
                                    @if($batchContacts->isEmpty())
                                        <div class="ls-modal-warn" role="status">
                                            <i class="mdi mdi-alert-outline" aria-hidden="true"></i>
                                            <span>No contacts found for this customer.</span>
                                        </div>
                                    @elseif($companyUnitOptions->isEmpty())
                                        <div class="ls-modal-warn" role="status">
                                            <i class="mdi mdi-alert-outline" aria-hidden="true"></i>
                                            <span>No site locations configured for this customer.</span>
                                        </div>
                                    @else
                                        <div class="ls-field ls-compact">
                                            <span class="ls-field__label">Site location</span>
                                            <div id="ptrr-company-unit-list" class="ptrr-check-list ptrr-check-list--grid-3">
                                                @foreach($companyUnitOptions as $unitName)
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input ptrr-company-unit-check"
                                                               id="ptrr-unit-{{ md5($unitName) }}"
                                                               value="{{ $unitName }}"
                                                               {{ strtolower((string) $defaultCompanyUnit) === strtolower((string) $unitName) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="ptrr-unit-{{ md5($unitName) }}" style="font-size:0.78rem;cursor:pointer;">
                                                            {{ $unitName }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="ls-field ls-compact">
                                            <span class="ls-field__label">Select contact(s)</span>
                                            <div id="ptrr-contact-list" class="ptrr-check-list ptrr-check-list--grid-3" style="max-height:8.5rem;">
                                                @foreach($batchContacts as $c)
                                                    @php
                                                        $contactUnits = $resolveContactUnitNames($c)->all();
                                                        $contactName = trim((string) ($c->name ?? implode(' ', array_filter([
                                                            $c->first_name ?? null,
                                                            $c->middle_name ?? null,
                                                            $c->last_name ?? null,
                                                        ]))));
                                                        if ($contactName === '') {
                                                            $contactName = 'Contact';
                                                        }
                                                    @endphp
                                                    <div class="custom-control custom-checkbox ptrr-contact-item"
                                                         data-unit-names='@json($contactUnits)'>
                                                        <input type="checkbox" class="custom-control-input ptrr-contact-check"
                                                               id="ptrr-contact-{{ $c->id }}" value="{{ $c->id }}"
                                                               data-name="{{ $contactName }}"
                                                               data-email="{{ $c->email ?? '' }}"
                                                               data-phone="{{ $c->mobile ?? $c->telephone ?? '' }}">
                                                        <label class="custom-control-label" for="ptrr-contact-{{ $c->id }}" style="font-size:0.78rem;cursor:pointer;">
                                                            {{ $contactName }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                                <div id="ptrr-no-contacts-for-unit" class="text-muted" style="display:none;font-size:0.72rem;padding:0.25rem 0;">
                                                    No contacts found for the selected site location(s).
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ls-field ls-compact">
                                            <span class="ls-field__label">Send via</span>
                                            <div class="ptrr-channel-row">
                                                <label class="ptrr-channel-card" for="ptrr-ch-email">
                                                    <input type="checkbox" id="ptrr-ch-email" value="email" class="ptrr-ch-check" style="display:none;">
                                                    <i class="mdi mdi-email-outline" style="font-size:1.1rem;color:#64748b;" aria-hidden="true"></i>
                                                    <span style="font-size:0.78rem;font-weight:600;">Email</span>
                                                </label>
                                                <label class="ptrr-channel-card" for="ptrr-ch-whatsapp">
                                                    <input type="checkbox" id="ptrr-ch-whatsapp" value="whatsapp" class="ptrr-ch-check" style="display:none;">
                                                    <i class="mdi mdi-whatsapp" style="font-size:1.1rem;color:#25D366;" aria-hidden="true"></i>
                                                    <span style="font-size:0.78rem;font-weight:600;">WhatsApp</span>
                                                </label>
                                                <label class="ptrr-channel-card" for="ptrr-ch-portal">
                                                    <input type="checkbox" id="ptrr-ch-portal" value="portal" class="ptrr-ch-check" style="display:none;">
                                                    <i class="mdi mdi-web" style="font-size:1.1rem;color:#4A90D9;" aria-hidden="true"></i>
                                                    <span style="font-size:0.78rem;font-weight:600;">Customer Portal</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="ls-field ls-compact mb-0" id="ptrr-portal-languages-section" style="display:none;">
                                            <span class="ls-field__label">
                                                <i class="mdi mdi-web" aria-hidden="true"></i>
                                                Portal languages
                                            </span>
                                            <p class="ptrr-panel-hint">Choose which language versions customers can view and download on the portal.</p>
                                            <div class="ptrr-lang-row">
                                                @foreach([
                                                    'en' => ['label' => 'English', 'sub' => 'Default', 'code' => 'EN'],
                                                    'ar' => ['label' => 'Arabic',  'sub' => 'عربي',   'code' => 'AR'],
                                                    'pt' => ['label' => 'Portuguese', 'sub' => 'Português', 'code' => 'PT'],
                                                ] as $val => $lang)
                                                    <label for="ptrr-portal-lang-{{ $val }}" class="ptrr-portal-lang-card">
                                                        <input type="checkbox" id="ptrr-portal-lang-{{ $val }}"
                                                               value="{{ $val }}" class="ptrr-portal-lang-check"
                                                               {{ $val === 'en' ? 'checked' : '' }}
                                                               style="display:none;">
                                                        <span class="ptrr-lang-badge ptrr-lang-badge--portal">{{ $lang['code'] }}</span>
                                                        <span>
                                                            <strong style="font-size:0.78rem;display:block;">{{ $lang['label'] }}</strong>
                                                            <span style="font-size:0.65rem;color:var(--ls-muted,#64748b);">{{ $lang['sub'] }}</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div id="ptrr-delivery-result" style="display:none;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>{{-- /body --}}

                    <div class="ls-modal-card__footer">
                        <button type="button" class="ls-btn" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="ls-btn ls-btn--accent" id="ptrr-submit-btn">
                            <i class="mdi mdi-file-check-outline" aria-hidden="true"></i>
                            Generate Report
                        </button>
                        @if($canDeliver)
                            <button type="button" class="ls-btn ls-btn--send" id="ptrr-send-btn">
                                <i class="mdi mdi-send" aria-hidden="true"></i>
                                Send Now
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function normalizeUnit(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function parseUnits(raw) {
        try {
            var parsed = JSON.parse(raw || '[]');
            if (!Array.isArray(parsed)) {
                return [];
            }
            return parsed.map(function(unit) { return normalizeUnit(unit); }).filter(Boolean);
        } catch (e) {
            return [];
        }
    }

    var modal = document.getElementById('process-test-request-report-modal');

    function getSelectedCompanyUnits() {
        if (!modal) {
            return [];
        }

        return Array.from(modal.querySelectorAll('.ptrr-company-unit-check:checked'))
            .map(function(input) { return (input.value || '').toString().trim(); })
            .filter(Boolean);
    }

    function getSelectedCompanyUnitsNormalized() {
        return getSelectedCompanyUnits().map(normalizeUnit);
    }

    function filterContactsByUnit() {
        var contactItems = modal
            ? Array.from(modal.querySelectorAll('.ptrr-contact-item'))
            : [];
        var emptyUnitContacts = modal
            ? modal.querySelector('#ptrr-no-contacts-for-unit')
            : null;
        var selectedUnits = getSelectedCompanyUnitsNormalized();

        if (!contactItems.length) {
            return;
        }

        var visibleCount = 0;

        contactItems.forEach(function(item) {
            var contactUnits = parseUnits(item.getAttribute('data-unit-names'));
            var belongs = selectedUnits.length > 0 && selectedUnits.some(function(unit) {
                return contactUnits.includes(unit);
            });
            item.style.display = belongs ? '' : 'none';

            if (!belongs) {
                var checkbox = item.querySelector('.ptrr-contact-check');
                if (checkbox) {
                    checkbox.checked = false;
                }
            } else {
                visibleCount += 1;
            }
        });

        if (emptyUnitContacts) {
            emptyUnitContacts.style.display = selectedUnits.length > 0 && visibleCount === 0 ? '' : 'none';
        }
    }

    function getSelectedReportLanguage() {
        var checked = modal ? modal.querySelector('.ptrr-lang-radio:checked') : null;
        return checked ? checked.value : 'en';
    }

    function syncPortalLanguageWithReportLanguage() {
        if (!modal) {
            return;
        }

        var reportLang = getSelectedReportLanguage();
        var portalCheckbox = modal.querySelector('.ptrr-portal-lang-check[value="' + reportLang + '"]');
        if (portalCheckbox && !portalCheckbox.checked) {
            portalCheckbox.checked = true;
            portalCheckbox.dispatchEvent(new Event('change'));
        }
    }

    function togglePortalLanguagesSection() {
        if (!modal) {
            return;
        }

        var portalChannel = modal.querySelector('#ptrr-ch-portal');
        var section = modal.querySelector('#ptrr-portal-languages-section');
        if (!portalChannel || !section) {
            return;
        }

        section.style.display = portalChannel.checked ? '' : 'none';
        if (portalChannel.checked) {
            syncPortalLanguageWithReportLanguage();
        }
    }

    function getSelectedPortalLanguages() {
        if (!modal) {
            return [];
        }

        return Array.from(modal.querySelectorAll('.ptrr-portal-lang-check:checked'))
            .map(function(input) { return (input.value || '').toString().trim(); })
            .filter(Boolean);
    }

    if (modal) {
        modal.addEventListener('change', function(event) {
            if (event.target && event.target.classList.contains('ptrr-company-unit-check')) {
                filterContactsByUnit();
            }
        });

        modal.querySelectorAll('.ptrr-lang-radio').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var portalChannel = modal.querySelector('#ptrr-ch-portal');
                if (portalChannel && portalChannel.checked) {
                    syncPortalLanguageWithReportLanguage();
                }
            });
        });

        if (typeof $ !== 'undefined') {
            $(modal).on('shown.bs.modal', function() {
                filterContactsByUnit();
                togglePortalLanguagesSection();
            });
        }

        filterContactsByUnit();
        togglePortalLanguagesSection();
    }

    document.querySelectorAll('#process-test-request-report-modal .ptrr-ch-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            if (this.id === 'ptrr-ch-portal') {
                togglePortalLanguagesSection();
            }
        });
    });

    setTimeout(function() {
        var sendBtn = document.getElementById('ptrr-send-btn');
        if (!sendBtn) {
            return;
        }

        sendBtn.addEventListener('click', function() {
            var contactIds = [];
            document.querySelectorAll('.ptrr-contact-check:checked').forEach(function(c) {
                contactIds.push(c.value);
            });
            var channels = [];
            document.querySelectorAll('.ptrr-ch-check:checked').forEach(function(c) {
                channels.push(c.value);
            });
            var selectedUnits = getSelectedCompanyUnits();

            if (!selectedUnits.length) {
                alert('Please select at least one site location.'); return;
            }

            if (!contactIds.length) {
                alert('Please select at least one contact.'); return;
            }
            if (!channels.length) {
                alert('Please select at least one delivery channel.'); return;
            }

            var portalLanguages = getSelectedPortalLanguages();
            if (channels.indexOf('portal') !== -1 && !portalLanguages.length) {
                alert('Please select at least one portal language.'); return;
            }

            var batchId = document.querySelector('#process-trr-form input[name="batch_id"]').value;
            var notes   = document.getElementById('ptrr-notes') ? document.getElementById('ptrr-notes').value : '';
            var csrf    = document.querySelector('#process-trr-form input[name="_token"]').value;

            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i> Sending…';

            var body = new FormData();
            body.append('_token', csrf);
            body.append('batch_id', batchId);
            body.append('notes', notes);
            selectedUnits.forEach(function(unit) {
                body.append('company_units[]', unit);
            });
            contactIds.forEach(function(id) { body.append('contact_ids[]', id); });
            channels.forEach(function(ch)  { body.append('channels[]', ch); });
            portalLanguages.forEach(function(lang) { body.append('portal_languages[]', lang); });

            fetch('{{ route("deliverTestRequestReport") }}', { method: 'POST', body: body })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var failedResults = (data.results || []).filter(function(item) {
                        return item && item.status === 'failed';
                    });
                    var isSuccess = Boolean(data.success) && failedResults.length === 0;
                    var resultEl = document.getElementById('ptrr-delivery-result');
                    var html = '<div class="alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' py-2 px-3 mt-2" style="font-size:12px;border-radius:6px;">';
                    html += '<strong>' + (data.message || '') + '</strong>';
                    if (data.results && data.results.length) {
                        html += '<ul class="mb-0 mt-1" style="padding-left:16px;">';
                        data.results.forEach(function(r) {
                            var icon = r.status === 'sent' ? '✓' : '✗';
                            var clr  = r.status === 'sent' ? 'green' : 'red';
                            html += '<li style="color:' + clr + ';">' + icon + ' ' + r.contact + ' via ' + r.channel + (r.error ? ' — ' + r.error : '') + '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</div>';
                    resultEl.innerHTML = html;
                    resultEl.style.display = 'block';
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="mdi mdi-send" aria-hidden="true"></i> Send Now';
                })
                .catch(function() {
                    alert('Delivery failed. Please try again.');
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="mdi mdi-send" aria-hidden="true"></i> Send Now';
                });
        });
    }, 0);
})();
</script>

<script>
(function () {
    var form = document.getElementById('process-trr-form');
    var submitBtn = document.getElementById('ptrr-submit-btn');
    var sendBtn = document.getElementById('ptrr-send-btn');
    var samplesPanel = document.getElementById('ptrr-samples-panel');
    var sampleHint = document.getElementById('ptrr-sample-hint');
    var bannerText = document.getElementById('ptrr-mode-banner-text');
    var modeBanner = document.getElementById('ptrr-mode-banner');
    var deliverySection = document.getElementById('ptrr-delivery-section');
    var batchCode = @json((string) $batch->batch_code);
    var nextRev = @json($nextRevPadded);

    if (!form || !document.getElementById('ptrr-lab-sections')) {
        return;
    }

    var selectedSectionIds = function () {
        return Array.prototype.slice.call(document.querySelectorAll('.ptrr-section-check:checked'))
            .map(function (el) { return el.value; });
    };

    var sampleRows = function () {
        return Array.prototype.slice.call(document.querySelectorAll('.ptrr-sample-row'));
    };

    var syncScope = function () {
        var sectionIds = selectedSectionIds();
        var sectionMode = sectionIds.length > 0;
        var visibleCount = 0;

        if (samplesPanel) {
            samplesPanel.setAttribute('data-disabled', sectionMode ? '0' : '1');
        }

        sampleRows().forEach(function (row) {
            var check = row.querySelector('.ptrr-sample-check');
            var rowSections = (row.getAttribute('data-section-ids') || '').split(',').filter(Boolean);
            var matches = sectionMode && rowSections.some(function (id) {
                return sectionIds.indexOf(id) !== -1;
            });

            row.style.display = matches ? '' : 'none';
            if (check) {
                check.disabled = !matches;
                if (!matches) {
                    check.checked = false;
                } else if (!check.dataset.userTouched) {
                    check.checked = true;
                }
                if (matches) {
                    visibleCount += 1;
                }
            }
        });

        if (sampleHint) {
            if (!sectionMode) {
                sampleHint.textContent = 'Select a lab section to choose samples.';
            } else if (visibleCount === 0) {
                sampleHint.textContent = 'No samples have results for the selected lab section(s).';
            } else {
                sampleHint.textContent = visibleCount + ' sample' + (visibleCount === 1 ? '' : 's') + ' available for the selected section(s).';
            }
        }

        if (bannerText) {
            if (sectionMode) {
                bannerText.innerHTML =
                    '<p class="ls-modal-callout__title mb-0">Section-only report</p>' +
                    '<p class="ls-modal-callout__text mb-0">Prints selected lab section(s) and samples only. This does not replace the official Test Report.</p>';
            } else {
                bannerText.innerHTML =
                    '<p class="ls-modal-callout__title mb-0">Revision ' + nextRev + ' · ' + batchCode + '-R' + nextRev + '</p>' +
                    '<p class="ls-modal-callout__text mb-0">Full official Test Report</p>';
            }
        }

        if (modeBanner) {
            modeBanner.classList.toggle('ptrr-callout--section', sectionMode);
        }

        if (submitBtn) {
            submitBtn.innerHTML = '<i class="mdi mdi-file-check-outline" aria-hidden="true"></i> Generate Report';
        }
        if (sendBtn) {
            sendBtn.style.display = '';
        }
        if (deliverySection) {
            deliverySection.style.display = '';
        }
    };

    document.querySelectorAll('.ptrr-section-check').forEach(function (el) {
        el.addEventListener('change', function () {
            sampleRows().forEach(function (row) {
                var check = row.querySelector('.ptrr-sample-check');
                if (check) {
                    delete check.dataset.userTouched;
                }
            });
            syncScope();
        });
    });

    sampleRows().forEach(function (row) {
        var check = row.querySelector('.ptrr-sample-check');
        if (!check) {
            return;
        }
        check.addEventListener('change', function () {
            check.dataset.userTouched = '1';
        });
    });

    var selectAll = document.getElementById('ptrr-select-all-samples');
    var clearAll = document.getElementById('ptrr-clear-samples');
    if (selectAll) {
        selectAll.addEventListener('click', function () {
            sampleRows().forEach(function (row) {
                if (row.style.display === 'none') {
                    return;
                }
                var check = row.querySelector('.ptrr-sample-check');
                if (check && !check.disabled) {
                    check.checked = true;
                    check.dataset.userTouched = '1';
                }
            });
        });
    }
    if (clearAll) {
        clearAll.addEventListener('click', function () {
            sampleRows().forEach(function (row) {
                var check = row.querySelector('.ptrr-sample-check');
                if (check) {
                    check.checked = false;
                    check.dataset.userTouched = '1';
                }
            });
        });
    }

    form.addEventListener('submit', function (event) {
        var sectionIds = selectedSectionIds();
        if (sectionIds.length === 0) {
            return;
        }

        var hasSample = sampleRows().some(function (row) {
            if (row.style.display === 'none') {
                return false;
            }
            var check = row.querySelector('.ptrr-sample-check');
            return check && check.checked && !check.disabled;
        });

        if (!hasSample) {
            event.preventDefault();
            alert('Select at least one sample for the selected lab section(s).');
        }
    });

    syncScope();
})();
</script>
</div>{{-- /.ptrr-modal-host --}}
