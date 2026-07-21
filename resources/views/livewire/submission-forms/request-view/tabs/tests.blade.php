{{-- Tests tab — samples configuration style, read-only --}}
<div class="rv-tests-tab"
     x-data="{
        paramsOpen: false,
        descriptionOpen: false,
        modalTitle: '',
        parameterGroups: [],
        descriptionHtml: ''
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
                            <th class="text-center" style="width: 56px;">Actions</th>
                            <th>Sample type</th>
                            <th>Analysis types</th>
                            <th class="text-center" style="width: 72px;">Sample description</th>
                            <th>Sample quantity</th>
                            <th>Sampling point / location</th>
                            <th>Test category</th>
                            <th>Production date</th>
                            <th>Expiry date</th>
                            <th>Batch number</th>
                            @foreach($testSamplesCard['extra_column_labels'] as $extraLabel)
                                <th>{{ $extraLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testSamplesCard['samples'] as $sample)
                            <tr>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-sm btn-icon btn-light text-info"
                                        title="View parameters"
                                        aria-label="View parameters for sample {{ $sample['number'] }}"
                                        @click='modalTitle = @json("Parameters — sample ".$sample["number"]); parameterGroups = @json($sample["parameter_groups"]); descriptionHtml = ""; paramsOpen = true; descriptionOpen = false;'>
                                        <i class="mdi mdi-eye" aria-hidden="true"></i>
                                    </button>
                                </td>
                                <td>{{ $sample['sample_type'] }}</td>
                                <td>{{ $sample['analysis_type'] }}</td>
                                <td class="text-center">
                                    @if($sample['has_description'])
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-light text-primary"
                                            title="View sample description"
                                            aria-label="View sample description"
                                            @click='modalTitle = @json("Sample description — sample ".$sample["number"]); descriptionHtml = @json($sample["sample_description_html"]); parameterGroups = []; descriptionOpen = true; paramsOpen = false;'>
                                            <i class="mdi mdi-text-box-outline" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $sample['sample_quantity'] }}</td>
                                <td>{{ $sample['sampling_point'] }}</td>
                                <td>{{ $sample['test_category'] }}</td>
                                <td>{{ $sample['production_date'] }}</td>
                                <td>{{ $sample['expiry_date'] }}</td>
                                <td>{{ $sample['batch_number'] }}</td>
                                @foreach($sample['extra_columns'] as $extra)
                                    <td>{{ $extra['value'] }}</td>
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

    {{-- Sample description (rich text, read-only) --}}
    <div class="rv-modal-backdrop" x-show="descriptionOpen" x-cloak @keydown.escape.window="descriptionOpen = false">
        <div class="rv-modal rv-modal--wide" role="dialog" aria-modal="true" @click.away="descriptionOpen = false">
            <div class="rv-modal-header">
                <h4 class="rv-modal-title" x-text="modalTitle"></h4>
                <button type="button" class="rv-modal-close" @click="descriptionOpen = false" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <div class="rv-richtext-readonly" x-html="descriptionHtml"></div>
            </div>
        </div>
    </div>
</div>
