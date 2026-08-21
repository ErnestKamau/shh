{{-- Tests tab — samples configuration style --}}
<div class="rv-tests-tab"
     x-data="{
        paramsOpen: false,
        modalTitle: '',
        parameterGroups: []
     }">
    <div class="workflow-board-panel-header rv-tests-tab-header">
        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            <h5 class="mb-0"><i class="mdi mdi-flask-outline"></i> Tests &amp; samples</h5>
            <span class="rv-samples-count-badge">{{ $testSamplesCard['count'] }} {{ \Illuminate\Support\Str::plural('sample', $testSamplesCard['count']) }}</span>
        </div>
        @if($acceptanceForm)
            <a href="{{ route('sample-workflow', ['status' => $boardStatus]) }}" class="btn btn-sm btn-outline-primary rv-btn-compact">
                <i class="mdi mdi-open-in-new"></i> Board
            </a>
        @endif
    </div>

    <div class="workflow-board-panel-body flush-top">
        @if($acceptanceForm)
            <div class="px-3 pt-2 pb-1">
                <span class="rv-meta-label">Acceptance</span>
                <span class="rv-status-badge">{{ str_replace('_', ' ', $acceptanceForm->status) }}</span>
            </div>
        @endif

        @if($testSamplesCard['count'] > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm workflow-table rv-tests-table mb-0">
                    <thead>
                        <tr>
                            @foreach($testSamplesCard['columns'] as $column)
                                @php
                                    $isActions = ($column['key'] ?? '') === 'actions';
                                @endphp
                                <th @class([
                                    'text-center' => $isActions,
                                ]) @if($isActions) style="width: 88px;" @endif>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testSamplesCard['samples'] as $sample)
                            <tr wire:key="sample-row-{{ $sample['row_index'] ?? $sample['number'] }}">
                                @foreach($testSamplesCard['columns'] as $column)
                                    @php
                                        $columnKey = $column['key'] ?? '';
                                    @endphp
                                    @if($columnKey === 'actions')
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                                <button type="button"
                                                    class="btn btn-sm btn-icon btn-light text-info"
                                                    title="View parameters"
                                                    aria-label="View parameters for sample {{ $sample['number'] }}"
                                                    @click='modalTitle = @json("Parameters — sample ".$sample["number"]); parameterGroups = @json($sample["parameter_groups"]); paramsOpen = true;'>
                                                    <i class="mdi mdi-eye" aria-hidden="true"></i>
                                                </button>
                                                @if($canEditSampleRows ?? false)
                                                    <button type="button"
                                                        class="btn btn-sm btn-icon btn-light text-primary"
                                                        title="Edit sample row"
                                                        aria-label="Edit sample row {{ $sample['number'] }}"
                                                        wire:click="openSampleRowEditor({{ (int) ($sample['row_index'] ?? 0) }})">
                                                        <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @elseif($columnKey === 'sample_description')
                                        <td class="rv-sample-description-cell">
                                            @if($sample['has_description'])
                                                <div class="rv-sample-description-inline">{!! $sample['sample_description_html'] !!}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @elseif($columnKey === 'test_requirements')
                                        <td>{{ $sample['test_category'] }}</td>
                                    @elseif(str_starts_with($columnKey, 'extra:'))
                                        @php
                                            $extraLabel = $column['label'] ?? '';
                                            $extraValue = collect($sample['extra_columns'] ?? [])->firstWhere('label', $extraLabel)['value'] ?? '—';
                                        @endphp
                                        <td>{{ $extraValue }}</td>
                                    @else
                                        <td>{{ $sample[$columnKey] ?? '—' }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0 py-3 px-3">No sample rows captured on this request.</p>
        @endif

        @if($attachmentInstances->isEmpty())
            <div class="px-3 py-2">
                @include('submission-forms.partials.sample-creation-actions', [
                    'instance' => $instance,
                    'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm,
                ])
            </div>
        @endif
    </div>

    {{-- Parameters by analysis type --}}
    <div class="rv-modal-backdrop" x-show="paramsOpen" x-cloak @keydown.escape.window="paramsOpen = false">
        <div class="rv-modal rv-modal--wide" role="dialog" aria-modal="true" @click.away="paramsOpen = false">
            <div class="rv-modal-header">
                <h4 class="rv-modal-title" x-text="modalTitle"></h4>
                <button type="button" class="rv-modal-close" @click="paramsOpen = false" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <template x-if="parameterGroups.length === 0">
                    <p class="rv-empty-copy mb-0">No parameters were captured for this sample.</p>
                </template>
                <template x-for="group in parameterGroups" :key="group.analysis_type">
                    <div class="rv-param-group mb-3">
                        <h6 class="rv-param-group-title" x-text="group.analysis_type"></h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered rv-param-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Code</th>
                                        <th>Parameter</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="param in group.parameters" :key="param.code + param.name">
                                        <tr>
                                            <td x-text="param.code"></td>
                                            <td x-text="param.name"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
