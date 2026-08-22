@php
    $labSectionWorksheets = $this->labSectionWorksheets;
    $sectionOptions = $this->labSectionWorksheetSectionOptions;
    $analystOptions = $this->labSectionWorksheetAnalystOptions;
@endphp

@if($sectionOptions !== [] || $labSectionWorksheets !== [])
    <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <h5 class="mb-0">
                <i class="mdi mdi-file-document-multiple-outline"></i>
                Lab section worksheets
            </h5>
            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                @if($sectionOptions !== [])
                    <select class="form-control form-control-sm" style="min-width: 160px;"
                        wire:model.live="labSectionWorksheetSectionFilter">
                        <option value="">All lab sections</option>
                        @foreach($sectionOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                        @endforeach
                    </select>
                @endif
                @if($analystOptions !== [])
                    <select class="form-control form-control-sm" style="min-width: 160px;"
                        wire:model.live="labSectionWorksheetAnalystFilter">
                        <option value="">All analysts</option>
                        @foreach($analystOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
        <div class="workflow-board-panel-body p-0">
            @if($labSectionWorksheets === [])
                <p class="text-muted small mb-0 p-3">No lab section worksheets match the current filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Worksheet no.</th>
                                <th>Lab section</th>
                                <th>Tests</th>
                                <th>Analysts</th>
                                <th>Issued</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labSectionWorksheets as $worksheet)
                                <tr>
                                    <td class="font-weight-bold">{{ $worksheet['worksheet_number'] }}</td>
                                    <td>{{ $worksheet['section_name'] }}</td>
                                    <td>{{ $worksheet['test_count'] }}</td>
                                    <td>{{ $worksheet['analyst_names'] }}</td>
                                    <td>{{ $worksheet['issued_at'] ?? '—' }}</td>
                                    <td>
                                        @if(($worksheet['status'] ?? '') === 'imported')
                                            <span class="badge badge-success">Imported</span>
                                            @if(! empty($worksheet['imported_at']))
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ $worksheet['imported_at'] }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($worksheet['status'] ?? 'issued') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        @if($worksheet['has_pdf'])
                                            <a href="{{ route('lab-section-worksheets.pdf', ['worksheet' => $worksheet['id']]) }}"
                                                target="_blank"
                                                class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2"
                                                title="Download PDF">
                                                <i class="mdi mdi-file-pdf-box"></i>
                                            </a>
                                        @endif
                                        @if($worksheet['has_excel'])
                                            <a href="{{ route('lab-section-worksheets.excel', ['worksheet' => $worksheet['id']]) }}"
                                                class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2"
                                                title="Download Excel template">
                                                <i class="mdi mdi-file-excel-outline"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
