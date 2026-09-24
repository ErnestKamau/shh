{{-- Tests tab — samples configuration style --}}
<div class="rv-tests-tab ls-ui-kit"
     x-data="{
        paramsOpen: false,
        modalTitle: 'Parameters',
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
            @include('livewire.submission-forms.request-view.partials.ls-tests-samples-table', [
                'columns' => $testSamplesCard['columns'] ?? [],
                'samples' => $testSamplesCard['samples'] ?? [],
                'showEditActions' => (bool) ($canEditSampleRows ?? false),
            ])
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
                        <div class="ls-table-wrap">
                            <table class="ls-table rv-param-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Report display</th>
                                        <th>Method</th>
                                        <th>Lab section</th>
                                        <th>Reporting unit</th>
                                        <th>TAT</th>
                                        <th>LOQ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="param in group.parameters" :key="(param.name || '') + (param.report_display_name || '') + (param.method || '')">
                                        <tr>
                                            <td x-text="param.name"></td>
                                            <td x-text="param.report_display_name"></td>
                                            <td x-text="param.method"></td>
                                            <td x-text="param.lab_section || '—'"></td>
                                            <td x-text="param.reporting_unit"></td>
                                            <td x-text="param.tat"></td>
                                            <td x-text="param.loq"></td>
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
