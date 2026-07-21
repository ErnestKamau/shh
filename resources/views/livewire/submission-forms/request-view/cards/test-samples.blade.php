<article class="rv-card rv-card--white rv-card--samples"
     x-data="{
        testsOpen: false,
        detailOpen: false,
        modalTitle: '',
        modalTests: [],
        modalDetails: []
     }">
    <header class="rv-card-header rv-card-header--plain">
        <div>
            <h3 class="rv-card-title rv-card-title--sm mb-0">Test &amp; samples</h3>
            <p class="rv-card-subtitle mb-0">{{ $testSamplesCard['count'] }} {{ \Illuminate\Support\Str::plural('sample', $testSamplesCard['count']) }}</p>
        </div>
        @if($acceptanceForm)
            <a href="{{ route('sample-workflow', ['status' => $boardStatus]) }}" class="btn btn-sm btn-outline-primary rv-btn-compact">
                <i class="mdi mdi-open-in-new"></i> Board
            </a>
        @endif
    </header>
    <div class="rv-card-body">
        @if($acceptanceForm)
            <div class="rv-samples-meta">
                <span class="rv-meta-label">Acceptance</span>
                <span class="rv-status-badge">{{ str_replace('_', ' ', $acceptanceForm->status) }}</span>
            </div>
        @endif

        @if($testSamplesCard['count'] > 0)
            <div class="rv-sample-table-wrap">
                <table class="rv-sample-table">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Sample type</th>
                            <th scope="col">Analysis type</th>
                            <th scope="col">Sample quantity</th>
                            <th scope="col">Tests</th>
                            <th scope="col" class="rv-col-actions">Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testSamplesCard['samples'] as $sample)
                            @php
                                $visibleTests = array_slice($sample['test_codes'], 0, 5);
                                $hiddenCount = max(0, count($sample['test_codes']) - 5);
                            @endphp
                            <tr>
                                <td class="rv-sample-index">{{ $sample['number'] }}</td>
                                <td>{{ $sample['sample_type'] }}</td>
                                <td>{{ $sample['analysis_type'] }}</td>
                                <td>{{ $sample['sample_quantity'] }}</td>
                                <td>
                                    @if(count($sample['test_codes']) > 0)
                                        <div class="rv-test-codes">
                                            @foreach($visibleTests as $code)
                                                <span class="rv-test-code">{{ $code }}</span>
                                            @endforeach
                                            @if($hiddenCount > 0)
                                                <button type="button"
                                                    class="rv-icon-btn"
                                                    title="View all tests"
                                                    aria-label="View all tests"
                                                    @click='modalTitle = @json("Tests — sample ".$sample["number"]); modalTests = @json($sample["test_codes"]); modalDetails = []; testsOpen = true; detailOpen = false;'>
                                                    <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                                                    <span>+{{ $hiddenCount }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="rv-col-actions">
                                    <button type="button"
                                        class="rv-icon-btn rv-icon-btn--solo"
                                        title="Sample details"
                                        aria-label="View sample details"
                                        @click='modalTitle = @json("Sample ".$sample["number"]." details"); modalDetails = @json($sample["details"]); modalTests = []; detailOpen = true; testsOpen = false;'>
                                        <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="rv-empty-copy mb-0">No sample rows captured on this request.</p>
        @endif

        @if($attachmentInstances->isEmpty())
            <div class="rv-samples-footer">
                @include('submission-forms.partials.sample-creation-actions', [
                    'instance' => $instance,
                    'linkedBatchesOutOfSyncWithForm' => $linkedBatchesOutOfSyncWithForm,
                ])
            </div>
        @endif
    </div>

    {{-- All tests modal --}}
    <div class="rv-modal-backdrop" x-show="testsOpen" x-cloak @keydown.escape.window="testsOpen = false">
        <div class="rv-modal" role="dialog" aria-modal="true" @click.away="testsOpen = false">
            <div class="rv-modal-header">
                <h4 class="rv-modal-title" x-text="modalTitle"></h4>
                <button type="button" class="rv-modal-close" @click="testsOpen = false" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <div class="rv-test-codes rv-test-codes--modal">
                    <template x-for="code in modalTests" :key="code">
                        <span class="rv-test-code" x-text="code"></span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Sample details modal --}}
    <div class="rv-modal-backdrop" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false">
        <div class="rv-modal" role="dialog" aria-modal="true" @click.away="detailOpen = false">
            <div class="rv-modal-header">
                <h4 class="rv-modal-title" x-text="modalTitle"></h4>
                <button type="button" class="rv-modal-close" @click="detailOpen = false" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <template x-if="modalDetails.length === 0">
                    <p class="rv-empty-copy mb-0">No additional sample details were captured.</p>
                </template>
                <dl class="rv-detail-grid" x-show="modalDetails.length > 0">
                    <template x-for="field in modalDetails" :key="field.label">
                        <div class="rv-detail-row">
                            <dt x-text="field.label"></dt>
                            <dd x-text="field.value"></dd>
                        </div>
                    </template>
                </dl>
            </div>
        </div>
    </div>
</article>
