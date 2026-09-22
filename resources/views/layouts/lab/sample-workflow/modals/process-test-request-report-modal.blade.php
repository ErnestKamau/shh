{{-- ═══════════════════════════════════════════════════════════════
     Process Test Report Modal
     Variables expected: $batch
     ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="process-test-request-report-modal" tabindex="-1" role="dialog"
     aria-labelledby="ptrr-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:720px;width:calc(100% - 32px);margin:16px auto;">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;box-shadow:0 12px 40px rgba(15,23,42,.18);display:flex;flex-direction:column;max-height:calc(100vh - 56px);">

            {{-- Header --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#8B1A1A 0%,#6d1414 100%);color:#fff;border-bottom:none;padding:18px 24px;">
                <h5 class="modal-title font-weight-bold" id="ptrr-modal-label" style="letter-spacing:.2px;">
                    <i class="mdi mdi-file-document-edit-outline mr-2"></i>
                    Process Test Report
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color:#fff;opacity:1;font-size:1.4rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('processTestRequestReport') }}" id="process-trr-form" target="_blank"
                  style="display:flex;flex-direction:column;flex:1 1 auto;min-height:0;">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                <div class="modal-body" style="padding:22px 26px;overflow-y:auto;flex:1 1 auto;">
                    <style>
                        .ptrr-panel {
                            border: 1px solid #e8ecef;
                            border-radius: 12px;
                            background: #fff;
                            padding: 14px 16px;
                            margin-bottom: 16px;
                        }
                        .ptrr-panel__title {
                            font-size: 13px;
                            font-weight: 700;
                            color: #1f2937;
                            margin-bottom: 4px;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                        }
                        .ptrr-panel__hint {
                            font-size: 11px;
                            color: #6b7280;
                            margin-bottom: 12px;
                        }
                        .ptrr-chip-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                            gap: 8px;
                        }
                        .ptrr-chip {
                            display: flex;
                            align-items: flex-start;
                            gap: 8px;
                            margin: 0;
                            padding: 10px 12px;
                            border: 1.5px solid #e5e7eb;
                            border-radius: 10px;
                            background: #fafafa;
                            cursor: pointer;
                            transition: border-color .15s, background .15s, box-shadow .15s;
                        }
                        .ptrr-chip:hover { border-color: #c4a0a0; background: #fff; }
                        .ptrr-chip:has(input:checked) {
                            border-color: #8B1A1A;
                            background: #fdf4f4;
                            box-shadow: inset 0 0 0 1px rgba(139,26,26,.08);
                        }
                        .ptrr-chip input { margin-top: 2px; flex-shrink: 0; }
                        .ptrr-chip strong { display: block; font-size: 12.5px; color: #111827; line-height: 1.3; }
                        .ptrr-chip small { display: block; font-size: 10.5px; color: #6b7280; margin-top: 2px; }
                        .ptrr-sample-list {
                            border: 1px solid #e5e7eb;
                            border-radius: 10px;
                            max-height: 180px;
                            overflow-y: auto;
                            background: #fafafa;
                        }
                        .ptrr-sample-row {
                            display: flex;
                            align-items: flex-start;
                            gap: 10px;
                            margin: 0;
                            padding: 10px 12px;
                            border-bottom: 1px solid #eee;
                            cursor: pointer;
                        }
                        .ptrr-sample-row:last-child { border-bottom: none; }
                        .ptrr-sample-row:hover { background: #fff; }
                        .ptrr-sample-row input { margin-top: 2px; }
                        .ptrr-lang-card:has(.ptrr-lang-radio:checked) {
                            border-color: #8B1A1A !important;
                            background: #fdf4f4;
                        }
                        #ptrr-samples-panel[data-disabled="1"] { opacity: .55; pointer-events: none; }
                    </style>

                    {{-- Revision info banner --}}
                    @php
                        $pendingAmendment = \App\BatchAmmendment::resolveForBatch($batch);
                        $inAmendment = (int) ($batch->in_ammendment_proccess ?? 0) === 1;
                        $nextFromSequence = ((int) ($batch->test_request_report_sequence ?? 0)) + 1;
                        $amendmentVersion = max(1, (int) ($batch->is_amendment ?? 1));
                        $nextRev = max($nextFromSequence, $amendmentVersion);

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
                    @endphp
                    @if($inAmendment && $pendingAmendment)
                    <div class="alert alert-warning d-flex align-items-start mb-3" style="border-radius:10px;font-size:13px;padding:12px 16px;">
                        <i class="mdi mdi-file-replace-outline mr-2" style="font-size:18px;margin-top:1px;"></i>
                        <div>
                            <strong>Amendment in progress</strong> (V{{ $pendingAmendment->version_number }})
                            <div class="mt-1">{{ $pendingAmendment->reason }}</div>
                            <small class="text-muted d-block mt-1">Re-issuing this Test Report will clear the amendment flag and print the amendment reason on the PDF.</small>
                        </div>
                    </div>
                    @endif
                    <div class="alert alert-light border d-flex align-items-center mb-3" id="ptrr-mode-banner"
                         style="border-radius:10px;font-size:13px;padding:12px 16px;background:#f8fafc;">
                        <i class="mdi mdi-information-outline mr-2" style="font-size:18px;color:#8B1A1A;"></i>
                        <div id="ptrr-mode-banner-text">
                            Generating <strong>Revision {{ str_pad($nextRev, 2, '0', STR_PAD_LEFT) }}</strong>
                            of report
                            <strong>{{ $batch->batch_code }}-R{{ str_pad($nextRev, 2, '0', STR_PAD_LEFT) }}</strong>
                            <span class="text-muted"> — full official Test Report</span>
                        </div>
                    </div>

                    {{-- Lab sections --}}
                    @if($ptrrLabSections !== [])
                    <div class="ptrr-panel mb-3">
                        <div class="ptrr-panel__title">
                            <i class="mdi mdi-flask-outline" style="color:#8B1A1A;"></i>
                            Lab sections
                        </div>
                        <div class="ptrr-panel__hint mb-0">
                            Leave all unchecked for the full official report. Check one or more to print only those sections’ tests (one page per sample).
                        </div>
                        <div class="ptrr-chip-grid mt-3" id="ptrr-lab-sections">
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

                    <div class="ptrr-panel mb-3" id="ptrr-samples-panel" data-disabled="1">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="ptrr-panel__title mb-0">
                                <i class="mdi mdi-test-tube" style="color:#8B1A1A;"></i>
                                Samples
                            </div>
                            <div class="small" style="display:flex;gap:12px;">
                                <button type="button" class="btn btn-link btn-sm p-0" id="ptrr-select-all-samples">Select all</button>
                                <button type="button" class="btn btn-link btn-sm p-0" id="ptrr-clear-samples">Clear</button>
                            </div>
                        </div>
                        <div class="ptrr-panel__hint">Choose which samples to include for the selected lab section(s).</div>
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
                                        <strong style="font-size:12.5px;display:block;color:#111827;">{{ $sampleLabel }}</strong>
                                        @if($sampleDesc !== '')
                                            <span style="font-size:11px;color:#6b7280;">{{ \Illuminate\Support\Str::limit($sampleDesc, 90) }}</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <div class="p-3 text-muted small">No samples on this job.</div>
                            @endforelse
                        </div>
                        <small class="text-muted d-block mt-2" id="ptrr-sample-hint">
                            Select a lab section to choose samples.
                        </small>
                    </div>
                    @endif

                    {{-- Language --}}
                    <div class="form-group mb-4">
                        <label class="font-weight-bold d-block mb-2" style="font-size:14px;">
                            <i class="mdi mdi-translate mr-1"></i> Report Language
                            <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
                            @foreach([
                                'en' => ['label' => 'English', 'sub' => 'Default', 'code' => 'EN'],
                                'ar' => ['label' => 'Arabic',  'sub' => 'عربي',   'code' => 'AR'],
                                'pt' => ['label' => 'Portuguese', 'sub' => 'Português', 'code' => 'PT'],
                            ] as $val => $lang)
                            <label for="ptrr-lang-{{ $val }}"
                                   class="ptrr-lang-card"
                                   style="display:flex;align-items:center;gap:10px;padding:10px 16px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;flex:1;min-width:130px;transition:all .15s;">
                                <input type="radio" name="language" id="ptrr-lang-{{ $val }}"
                                       value="{{ $val }}" {{ $val === 'en' ? 'checked' : '' }}
                                       style="display:none;" class="ptrr-lang-radio">
                                <span style="background:#8B1A1A;color:#fff;font-weight:700;font-size:11px;padding:3px 7px;border-radius:4px;letter-spacing:.5px;flex-shrink:0;">{{ $lang['code'] }}</span>
                                <span>
                                    <strong style="font-size:13px;display:block;">{{ $lang['label'] }}</strong>
                                    <span style="font-size:11px;color:#888;">{{ $lang['sub'] }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <script>
                        document.querySelectorAll('.ptrr-lang-radio').forEach(function(radio) {
                            radio.addEventListener('change', function() {
                                document.querySelectorAll('.ptrr-lang-card').forEach(function(card) {
                                    card.style.borderColor = '#dee2e6';
                                    card.style.background = '';
                                });
                                if (this.checked) {
                                    this.closest('.ptrr-lang-card').style.borderColor = '#8B1A1A';
                                    this.closest('.ptrr-lang-card').style.background = '#fdf4f4';
                                }
                            });
                        });
                        document.addEventListener('DOMContentLoaded', function() {
                            var checked = document.querySelector('.ptrr-lang-radio:checked');
                            if (checked) {
                                checked.closest('.ptrr-lang-card').style.borderColor = '#8B1A1A';
                                checked.closest('.ptrr-lang-card').style.background = '#fdf4f4';
                            }
                        });
                    </script>

                    {{-- Report column options --}}
                    @php
                        $isBrazilExportationReport = app(\App\Services\Sampleworkflow\TestRequestReportDataService::class)
                            ->isBrazilExportationBatch($batch);
                    @endphp
                    <div class="form-group mb-4">
                        <label class="font-weight-bold d-block mb-2" style="font-size:14px;">
                            <i class="mdi mdi-table-column mr-1"></i> Result Table Options
                        </label>
                        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px;background:#fafafa;">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="ptrr-include-reference-method"
                                       name="include_reference_method" value="1">
                                <label class="custom-control-label" for="ptrr-include-reference-method" style="font-size:13px;">
                                    Include <strong>Reference Method</strong> column
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="ptrr-show-specification"
                                       name="show_specification" value="1"
                                       {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                <label class="custom-control-label" for="ptrr-show-specification" style="font-size:13px;">
                                    Include <strong>Specification limit</strong> column
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="ptrr-show-specification-standard"
                                       name="show_specification_standard" value="1"
                                       {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                <label class="custom-control-label" for="ptrr-show-specification-standard" style="font-size:13px;">
                                    Include <strong>Specification Standard</strong> column
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="ptrr-show-mu-percent"
                                       name="show_mu_percent" value="1"
                                       {{ $isBrazilExportationReport ? '' : 'checked' }}>
                                <label class="custom-control-label" for="ptrr-show-mu-percent" style="font-size:13px;">
                                    Include <strong>M.U%</strong> column
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size:11px;">
                                Empty optional columns are omitted automatically. Brazil Exportation defaults Spec / Spec Standard / M.U% off.
                            </small>
                        </div>
                    </div>

                    {{-- Revision notes --}}
                    <div class="form-group mb-2">
                        <label class="font-weight-bold" for="ptrr-notes" style="font-size:14px;">
                            <i class="mdi mdi-note-text-outline mr-1"></i> Revision Notes
                            <span class="text-muted font-weight-normal">(optional)</span>
                        </label>
                        <textarea name="notes" id="ptrr-notes" rows="3" class="form-control"
                                  style="border-radius:8px;font-size:13px;resize:none;"
                                  placeholder="Describe what changed in this revision…">{{ ($inAmendment ?? false) && !empty($pendingAmendment?->reason) ? $pendingAmendment->reason : '' }}</textarea>
                    </div>

                    {{-- Previous revisions --}}
                    @php
                        $existingRevisions = \App\Models\TestRequestReportRevision::where('batch_id', $batch->id)
                            ->orderByDesc('revision_no')->get();
                    @endphp
                    @if($existingRevisions->isNotEmpty())
                    <div class="mt-4">
                        <p class="font-weight-bold mb-2" style="font-size:13px;color:#555;">
                            <i class="mdi mdi-history mr-1"></i> Revision History
                        </p>
                        <div style="max-height:140px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:8px;">
                            <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                                <thead style="background:#f5f5f5;position:sticky;top:0;">
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
                    @endif

                    {{-- ════════ DELIVERY OPTIONS ════════ --}}
                @php
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

                    // Contact.unit_name may store legacy display names or CRM unit UUIDs
                    // (ContactForm). Always resolve to a human-readable unit name.
                    $resolveContactUnitNames = function ($contact) use ($companyUnitsById) {
                        $contactUnits = collect(explode(',', (string) ($contact->unit_name ?? '')))
                            ->map(fn($unit) => trim((string) $unit))
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
                            ->unique(fn($name) => strtolower((string) $name))
                            ->values();
                    };

                    $companyUnitOptions = $companyUnits
                        ->pluck('name')
                        ->map(fn($name) => trim((string) $name))
                        ->filter()
                        ->values();

                    foreach ($batchContacts as $contact) {
                        $companyUnitOptions = $companyUnitOptions->merge($resolveContactUnitNames($contact));
                    }

                    $companyUnitOptions = $companyUnitOptions
                        ->unique(fn($name) => strtolower((string) $name))
                        ->sort(fn($a, $b) => strcasecmp((string) $a, (string) $b))
                        ->values();

                    $defaultCompanyUnit = trim((string) ($batch->crm_unit_name ?? ''));
                    if (\Illuminate\Support\Str::isUuid($defaultCompanyUnit)) {
                        $defaultCompanyUnit = trim((string) ($companyUnitsById->get($defaultCompanyUnit)?->name ?? ''));
                    }
                    if (
                        $defaultCompanyUnit === ''
                        || !$companyUnitOptions->contains(fn($name) => strtolower((string) $name) === strtolower($defaultCompanyUnit))
                    ) {
                        $defaultCompanyUnit = '';
                    }
                @endphp

                <div class="mt-4" id="ptrr-delivery-section">
                    <div style="border-top:2px solid #f0f0f0;padding-top:16px;">
                        <p class="font-weight-bold mb-3" style="font-size:13px;color:#333;">
                            <i class="mdi mdi-send-outline mr-1" style="color:#8B1A1A;"></i> Send Report To
                            <span class="text-muted font-weight-normal" style="font-size:11px;">(optional)</span>
                        </p>

                        @if($batchContacts->isEmpty())
                            <div class="alert alert-warning py-2 px-3" style="font-size:12px;border-radius:6px;">
                                <i class="mdi mdi-alert-outline mr-1"></i> No contacts found for this customer.
                            </div>
                        @elseif($companyUnitOptions->isEmpty())
                            <div class="alert alert-warning py-2 px-3" style="font-size:12px;border-radius:6px;">
                                <i class="mdi mdi-alert-outline mr-1"></i> No department/company units configured for this customer.
                            </div>
                        @else
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" style="color:#555;">Department / Company Unit</label>
                                <small class="text-muted d-block mb-2" style="font-size:11px;">Select one or more departments to filter contacts.</small>
                                <div id="ptrr-company-unit-list" style="border:1px solid #dee2e6;border-radius:8px;max-height:110px;overflow-y:auto;padding:8px 12px;background:#fafafa;">
                                    @foreach($companyUnitOptions as $unitName)
                                    <div class="custom-control custom-checkbox mb-1">
                                        <input type="checkbox" class="custom-control-input ptrr-company-unit-check"
                                               id="ptrr-unit-{{ md5($unitName) }}"
                                               value="{{ $unitName }}"
                                               {{ strtolower((string) $defaultCompanyUnit) === strtolower((string) $unitName) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="ptrr-unit-{{ md5($unitName) }}" style="font-size:13px;cursor:pointer;">
                                            {{ $unitName }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Contact picker --}}
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" style="color:#555;">Select Contact(s)</label>
                                <div id="ptrr-contact-list" style="border:1px solid #dee2e6;border-radius:8px;max-height:130px;overflow-y:auto;padding:8px 12px;background:#fafafa;">
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
                                    <div class="custom-control custom-checkbox mb-1 ptrr-contact-item"
                                         data-unit-names='@json($contactUnits)'>
                                        <input type="checkbox" class="custom-control-input ptrr-contact-check"
                                               id="ptrr-contact-{{ $c->id }}" value="{{ $c->id }}"
                                               data-name="{{ $contactName }}"
                                               data-email="{{ $c->email ?? '' }}"
                                               data-phone="{{ $c->mobile ?? $c->telephone ?? '' }}">
                                        <label class="custom-control-label" for="ptrr-contact-{{ $c->id }}" style="font-size:13px;cursor:pointer;">
                                            <strong>{{ $contactName }}</strong>
                                            @if($c->email)
                                                <span class="text-muted" style="font-size:11px;"> &bull; {{ $c->email }}</span>
                                            @endif
                                            @if($c->mobile ?? $c->telephone)
                                                <span class="text-muted" style="font-size:11px;"> &bull; {{ $c->mobile ?? $c->telephone }}</span>
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                    <div id="ptrr-no-contacts-for-unit" class="text-muted" style="display:none;font-size:12px;padding:4px 0;">
                                        No contacts found for the selected department(s)/company unit(s).
                                    </div>
                                </div>
                            </div>

                            {{-- Channel selection --}}
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" style="color:#555;">Send Via</label>
                                <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
                                    <label class="ptrr-channel-card" for="ptrr-ch-email"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-email" value="email" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-email-outline" style="font-size:18px;color:#555;"></i>
                                        <span>Email</span>
                                    </label>
                                    <label class="ptrr-channel-card" for="ptrr-ch-whatsapp"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-whatsapp" value="whatsapp" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-whatsapp" style="font-size:18px;color:#25D366;"></i>
                                        <span>WhatsApp</span>
                                    </label>
                                    <label class="ptrr-channel-card" for="ptrr-ch-portal"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-portal" value="portal" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-web" style="font-size:18px;color:#4A90D9;"></i>
                                        <span>Customer Portal</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Portal language selection --}}
                            <div class="form-group mb-3" id="ptrr-portal-languages-section" style="display:none;">
                                <label class="small font-weight-bold" style="color:#555;">
                                    <i class="mdi mdi-web mr-1" style="color:#4A90D9;"></i> Portal Languages
                                </label>
                                <small class="text-muted d-block mb-2" style="font-size:11px;">
                                    Choose which language versions customers can view and download on the portal.
                                </small>
                                <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
                                    @foreach([
                                        'en' => ['label' => 'English', 'sub' => 'Default', 'code' => 'EN'],
                                        'ar' => ['label' => 'Arabic',  'sub' => 'عربي',   'code' => 'AR'],
                                        'pt' => ['label' => 'Portuguese', 'sub' => 'Português', 'code' => 'PT'],
                                    ] as $val => $lang)
                                    <label for="ptrr-portal-lang-{{ $val }}"
                                           class="ptrr-portal-lang-card"
                                           style="display:flex;align-items:center;gap:10px;padding:10px 16px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;flex:1;min-width:130px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-portal-lang-{{ $val }}"
                                               value="{{ $val }}" class="ptrr-portal-lang-check"
                                               {{ $val === 'en' ? 'checked' : '' }}
                                               style="display:none;">
                                        <span style="background:#4A90D9;color:#fff;font-weight:700;font-size:11px;padding:3px 7px;border-radius:4px;letter-spacing:.5px;flex-shrink:0;">{{ $lang['code'] }}</span>
                                        <span>
                                            <strong style="font-size:13px;display:block;">{{ $lang['label'] }}</strong>
                                            <span style="font-size:11px;color:#888;">{{ $lang['sub'] }}</span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Delivery result area --}}
                            <div id="ptrr-delivery-result" style="display:none;"></div>
                        @endif
                    </div>
                </div>

                <style>
                    .ptrr-channel-card:has(.ptrr-ch-check:checked) { border-color:#8B1A1A !important; background:#fdf4f4; }
                    .ptrr-portal-lang-card:has(.ptrr-portal-lang-check:checked) { border-color:#4A90D9 !important; background:#f4f8fd; }
                </style>
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

                        modal.querySelectorAll('.ptrr-portal-lang-check:checked').forEach(function(cb) {
                            var card = cb.closest('.ptrr-portal-lang-card');
                            if (card) {
                                card.style.borderColor = '#4A90D9';
                                card.style.background = '#f4f8fd';
                            }
                        });
                    }

                    document.querySelectorAll('.ptrr-ch-check').forEach(function(cb) {
                        cb.addEventListener('change', function() {
                            var card = this.closest('.ptrr-channel-card');
                            card.style.borderColor = this.checked ? '#8B1A1A' : '#dee2e6';
                            card.style.background  = this.checked ? '#fdf4f4' : '';
                            if (this.id === 'ptrr-ch-portal') {
                                togglePortalLanguagesSection();
                            }
                        });
                    });

                    document.querySelectorAll('.ptrr-portal-lang-check').forEach(function(cb) {
                        cb.addEventListener('change', function() {
                            var card = this.closest('.ptrr-portal-lang-card');
                            card.style.borderColor = this.checked ? '#4A90D9' : '#dee2e6';
                            card.style.background  = this.checked ? '#f4f8fd' : '';
                        });
                    });

                    // Footer button is rendered after this script; bind once it exists.
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
                                alert('Please select at least one department/company unit.'); return;
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
                            sendBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Sending…';

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
                                    sendBtn.innerHTML = '<i class="mdi mdi-send mr-1"></i> Send Now';
                                })
                                .catch(function(err) {
                                    alert('Delivery failed. Please try again.');
                                    sendBtn.disabled = false;
                                    sendBtn.innerHTML = '<i class="mdi mdi-send mr-1"></i> Send Now';
                                });
                        });
                    }, 0);
                })();
                </script>

                </div>{{-- /modal-body --}}

                {{-- Footer --}}
                <div class="modal-footer" style="border-top:1px solid #f0f0f0;padding:16px 24px;background:#fafafa;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:6px;">
                        <i class="mdi mdi-close mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm font-weight-bold" id="ptrr-submit-btn"
                            style="background:#8B1A1A;color:#fff;border-radius:6px;padding:6px 20px;">
                        <i class="mdi mdi-file-check-outline mr-1"></i> Generate Report
                    </button>
                    @if($batchContacts->isNotEmpty() && $companyUnitOptions->isNotEmpty())
                        <button type="button" id="ptrr-send-btn"
                                style="background:#1a6b1a;color:#fff;border:none;border-radius:6px;padding:7px 20px;font-size:13px;font-weight:700;cursor:pointer;">
                            <i class="mdi mdi-send mr-1"></i> Send Now
                        </button>
                    @endif
                </div>
            </form>

            <script>
            (function () {
                var form = document.getElementById('process-trr-form');
                var submitBtn = document.getElementById('ptrr-submit-btn');
                var sendBtn = document.getElementById('ptrr-send-btn');
                var samplesPanel = document.getElementById('ptrr-samples-panel');
                var sampleHint = document.getElementById('ptrr-sample-hint');
                var bannerText = document.getElementById('ptrr-mode-banner-text');
                var deliverySection = document.getElementById('ptrr-delivery-section');
                var batchCode = @json((string) $batch->batch_code);
                @php
                    $ptrrNextRevPadded = str_pad((string) ($nextRev ?? 1), 2, '0', STR_PAD_LEFT);
                @endphp
                var nextRev = @json($ptrrNextRevPadded);

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
                            bannerText.innerHTML = 'Printing a <strong>section-only</strong> report for selected lab section(s) and samples. This does not replace the official Test Report.';
                        } else {
                            bannerText.innerHTML = 'Generating <strong>Revision ' + nextRev + '</strong> of report <strong>' + batchCode + '-R' + nextRev + '</strong> <span class="text-muted">— full official Test Report</span>';
                        }
                    }

                    if (submitBtn) {
                        submitBtn.innerHTML = sectionMode
                            ? '<i class="mdi mdi-printer mr-1"></i> Print Section Report'
                            : '<i class="mdi mdi-file-check-outline mr-1"></i> Generate Report';
                    }
                    if (sendBtn) {
                        sendBtn.style.display = sectionMode ? 'none' : '';
                    }
                    if (deliverySection) {
                        deliverySection.style.display = sectionMode ? 'none' : '';
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

        </div>
    </div>
</div>
