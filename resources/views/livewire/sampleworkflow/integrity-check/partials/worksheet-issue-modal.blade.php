@php
    $worksheetSectionOptions = $worksheetSectionOptions ?? [];
    $selectedWorksheetSectionIds = $selectedWorksheetSectionIds ?? [];
    $worksheetModalMode = in_array(($worksheetModalMode ?? 'pdf'), ['pdf', 'excel'], true)
        ? $worksheetModalMode
        : 'pdf';
    $isPdfMode = $worksheetModalMode === 'pdf';
@endphp

@if($showWorksheetModal ?? false)
    <div class="modal fade show d-block integrity-worksheet-modal" tabindex="-1" role="dialog" aria-modal="true" style="background: rgba(15, 23, 42, 0.45);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($isPdfMode)
                            <i class="mdi mdi-file-pdf-box mr-1"></i>
                            Print worksheet PDF
                        @else
                            <i class="mdi mdi-file-excel-outline mr-1"></i>
                            Print worksheet Excel
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="closeWorksheetModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($worksheetSectionOptions === [])
                        <p class="text-muted mb-0">
                            Assign tests to lab sections first. Subcontracted tests are excluded from section worksheets.
                        </p>
                    @else
                        <p class="text-muted small mb-3">
                            Choose lab section(s). Each section gets its own numbered worksheet
                            (<code>YYMMDD-SECTION.###</code>) with only that section’s tests
                            (sample, collection, LOD/LOQ/MU/method/equipment). Client details are omitted.
                            @if($isPdfMode)
                                PDF opens in a new tab for viewing. Use <strong>Issue &amp; notify analysts</strong> only when you want to notify assignees.
                            @else
                                Excel downloads to your computer. Use <strong>Issue &amp; notify analysts</strong> only when you want to notify assignees.
                            @endif
                        </p>
                        <div class="integrity-worksheet-section-list">
                            @foreach($worksheetSectionOptions as $option)
                                @php
                                    $sectionId = (string) ($option['id'] ?? '');
                                    $isChecked = in_array($sectionId, $selectedWorksheetSectionIds, true);
                                @endphp
                                <label class="integrity-worksheet-section-row {{ $isChecked ? 'is-selected' : '' }}">
                                    <input type="checkbox"
                                        class="integrity-worksheet-section-row__check"
                                        value="{{ $sectionId }}"
                                        wire:model.live="selectedWorksheetSectionIds">
                                    <span class="integrity-worksheet-section-row__body">
                                        <span class="integrity-worksheet-section-row__title">
                                            {{ $option['name'] ?? 'Lab section' }}
                                            @if(! empty($option['code']))
                                                <span class="text-muted">({{ $option['code'] }})</span>
                                            @endif
                                        </span>
                                        <span class="integrity-worksheet-section-row__meta">
                                            {{ (int) ($option['test_count'] ?? 0) }} test(s)
                                            · Next no. {{ $option['preview_number'] ?? '—' }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="modal-footer flex-wrap justify-content-between">
                    <button type="button" class="btn btn-light" wire:click="closeWorksheetModal">
                        Cancel
                    </button>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        @if($isPdfMode)
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                wire:click="issueWorksheetPdf"
                                wire:loading.attr="disabled"
                                wire:target="issueWorksheetPdf"
                                @disabled($worksheetSectionOptions === [] || $selectedWorksheetSectionIds === [])>
                                <span wire:loading.remove wire:target="issueWorksheetPdf">
                                    <i class="mdi mdi-file-pdf-box"></i> View PDF
                                </span>
                                <span wire:loading wire:target="issueWorksheetPdf">Opening…</span>
                            </button>
                        @else
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                wire:click="issueWorksheetExcel"
                                wire:loading.attr="disabled"
                                wire:target="issueWorksheetExcel"
                                @disabled($worksheetSectionOptions === [] || $selectedWorksheetSectionIds === [])>
                                <span wire:loading.remove wire:target="issueWorksheetExcel">
                                    <i class="mdi mdi-file-excel-outline"></i> Download Excel
                                </span>
                                <span wire:loading wire:target="issueWorksheetExcel">Preparing…</span>
                            </button>
                        @endif
                        <button type="button"
                            class="btn btn-primary btn-sm"
                            wire:click="issueAndNotifyAnalysts"
                            wire:loading.attr="disabled"
                            wire:target="issueAndNotifyAnalysts"
                            @disabled($worksheetSectionOptions === [] || $selectedWorksheetSectionIds === [])>
                            <span wire:loading.remove wire:target="issueAndNotifyAnalysts">
                                <i class="mdi mdi-send-outline"></i> Issue &amp; notify analysts
                            </span>
                            <span wire:loading wire:target="issueAndNotifyAnalysts">Issuing…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
