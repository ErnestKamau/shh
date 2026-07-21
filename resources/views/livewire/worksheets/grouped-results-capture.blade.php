<div class="grouped-results-capture p-3">
    @if(!empty($parameters) && !empty($samples))
        <div class="gw-results-capture__status mb-3">
            @if($isPosted)
                <span class="badge gw-results-capture__badge gw-results-capture__badge--posted">
                    <i class="mdi mdi-check-circle"></i> Worksheet Posted
                </span>
                @if($postedAtLabel || $postedByName)
                    <span class="small text-muted ml-2">
                        @if($postedAtLabel)
                            {{ $postedAtLabel }}
                        @endif
                        @if($postedByName)
                            @if($postedAtLabel) &middot; @endif
                            by {{ $postedByName }}
                        @endif
                    </span>
                @endif
            @else
                <span class="badge gw-results-capture__badge gw-results-capture__badge--draft">
                    <i class="mdi mdi-clock-outline"></i> Not Posted
                </span>
            @endif
        </div>
    @endif

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} mb-3 py-2 small">
            {{ $message }}
        </div>
    @endif

    @if(empty($parameters) || empty($samples))
        <div class="alert alert-light border text-center py-5 mb-0">
            <i class="mdi mdi-flask-empty-outline text-muted" style="font-size: 2.5rem;"></i>
            <p class="mb-0 mt-2 text-muted">No parameters or samples found for results capture on this pipeline.</p>
        </div>
    @else
        @include('worksheets.partials.worksheet-meta-bar', ['metaSummary' => $worksheetMetaSummary])
        @if(!empty($worksheetMetaRows) && count($worksheetMetaRows) > 1)
            @include('worksheets.partials.worksheet-meta-table', ['metaRows' => $worksheetMetaRows, 'compact' => true])
        @endif
        <div x-data="groupedResultsCaptureActions">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div class="mb-2 mb-md-0">
                <h6 class="mb-1">Results capture</h6>
                <p class="small text-muted mb-0">
                    @if($captureStep === 1)
                        Enter a reporting symbol and result for each parameter and sample.
                    @else
                        Add comments, recommendations, and notes for each sample.
                    @endif
                </p>
            </div>
            @if(!$reviewOnly)
            <div class="d-flex flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-1"
                    @click="syncActiveEditors().then(() => $wire.saveDraft())"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft,saveAndPost,postResults">
                    <span wire:loading.remove wire:target="saveDraft">
                        <i class="mdi mdi-content-save-outline"></i> Save
                    </span>
                    <span wire:loading wire:target="saveDraft">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Saving…
                    </span>
                </button>
                <button type="button" class="btn btn-primary btn-sm mr-2 mb-1"
                    @click="syncActiveEditors().then(() => $wire.saveAndPost())"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft,saveAndPost,postResults">
                    <span wire:loading.remove wire:target="saveAndPost">
                        <i class="mdi mdi-content-save-check"></i> Save and Post
                    </span>
                    <span wire:loading wire:target="saveAndPost">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Posting…
                    </span>
                </button>
                <button type="button" class="btn btn-success btn-sm mb-1"
                    @click="syncActiveEditors().then(() => $wire.postResults())"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft,saveAndPost,postResults">
                    <span wire:loading.remove wire:target="postResults">
                        <i class="mdi mdi-upload"></i> Post Results
                    </span>
                    <span wire:loading wire:target="postResults">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Posting…
                    </span>
                </button>
            </div>
            @endif
        </div>

        <ul class="nav mb-3 gw-results-capture__steps">
            <li class="nav-item mr-2">
                <button type="button"
                    class="nav-link gw-results-capture__step {{ $captureStep === 1 ? 'gw-results-capture__step--active' : '' }}"
                    wire:click="goToStep(1)">
                    <i class="mdi mdi-table-large"></i> Results table
                </button>
            </li>
            <li class="nav-item">
                <button type="button"
                    class="nav-link gw-results-capture__step {{ $captureStep === 2 ? 'gw-results-capture__step--active' : '' }}"
                    wire:click="goToStep(2)">
                    <i class="mdi mdi-comment-text-outline"></i> Comments
                </button>
            </li>
        </ul>

        <div class="gw-results-capture__panel {{ $captureStep === 1 ? '' : 'd-none' }}">
            <div class="gw-results-matrix-wrap">
                <table class="table table-sm table-bordered gw-results-matrix mb-0">
                    <thead>
                        <tr>
                            <th class="gw-results-matrix__sticky-col">Loci/Parameters</th>
                            @foreach($samples as $sample)
                                <th class="text-center gw-results-matrix__sample-col">
                                    <span class="gw-results-matrix__sample-code d-block">{{ $sample['sample_detail_code'] }}</span>
                                    <span class="gw-results-matrix__sample-sub d-block">Symbol / Result</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parameters as $parameter)
                            @php $rowKey = $parameter['row_key']; @endphp
                            <tr wire:key="row-{{ $rowKey }}">
                                <td class="gw-results-matrix__sticky-col">
                                    <span class="font-weight-semibold d-block">{{ $parameter['label'] }}</span>
                                </td>
                                @foreach($samples as $sample)
                                    @php
                                        $sampleCode = $sample['sample_detail_code'];
                                        $cell = $cells[$rowKey][$sampleCode] ?? null;
                                    @endphp
                                    @if($cell)
                                        <td wire:key="cell-{{ $cell['captured_result_id'] }}" class="gw-results-matrix__cell">
                                            <div class="gw-results-matrix__cell-inputs">
                                                <select class="form-control form-control-sm gw-results-matrix__symbol"
                                                    wire:model.lazy="cells.{{ $rowKey }}.{{ $sampleCode }}.reporting_symbol"
                                                    title="Reporting symbol"
                                                    @if($reviewOnly) disabled @endif>
                                                    <option value="">—</option>
                                                    <option value="=">=</option>
                                                    <option value="<">&lt;</option>
                                                    <option value=">">&gt;</option>
                                                    <option value="≤">≤</option>
                                                    <option value="≥">≥</option>
                                                    <option value="<=">&lt;=</option>
                                                    <option value=">=">&gt;=</option>
                                                </select>
                                                <input type="text"
                                                    class="form-control form-control-sm gw-results-matrix__result"
                                                    wire:model.lazy="cells.{{ $rowKey }}.{{ $sampleCode }}.result"
                                                    placeholder="Result"
                                                    @if($reviewOnly) readonly @endif>
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-muted text-center small">—</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="{{ $captureStep === 2 ? '' : 'd-none' }}">
            @php
                $activeSample = $samples[$activeSampleIndex] ?? null;
                $activeSampleDetailId = $activeSample['sample_detail_id'] ?? null;
                $activeComments = $activeSampleDetailId
                    ? ($sampleComments[$activeSampleDetailId] ?? ['header_body' => '', 'main_body' => '', 'notes_body' => ''])
                    : ['header_body' => '', 'main_body' => '', 'notes_body' => ''];
            @endphp

            @if(count($samples) > 1)
                <ul class="nav nav-tabs mb-3">
                    @foreach($samples as $index => $sample)
                        <li class="nav-item">
                            <button type="button"
                                class="nav-link {{ $index === $activeSampleIndex ? 'active' : '' }}"
                                @click="syncActiveEditors().then(() => $wire.setActiveSampleIndex({{ $index }}))">
                                {{ $sample['sample_detail_code'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($activeSample && $activeSampleDetailId)
                <script type="text/javascript" src="/tinymce/tinymce.min.js"></script>

                <div class="gw-results-capture__comments" wire:key="comments-{{ $activeSampleDetailId }}-{{ $commentsEditorKey }}">
                    <div wire:ignore
                        x-data="{
                            sampleId: @js($activeSampleDetailId),
                            initialComments: @js($activeComments),
                            initEditor(selector, field, initialHtml) {
                                if (typeof tinymce === 'undefined') {
                                    return;
                                }
                                const sampleId = this.sampleId;
                                tinymce.remove(selector);
                                tinymce.init({
                                    selector: selector,
                                    menubar: false,
                                    statusbar: false,
                                    height: 180,
                                    readonly: @js($reviewOnly),
                                    toolbar: @js($reviewOnly) ? false : 'bold italic underline | bullist numlist | forecolor',
                                    plugins: 'lists textcolor',
                                    setup: (editor) => {
                                        editor.on('init', () => {
                                            editor.setContent(initialHtml || '');
                                        });
                                        editor.on('change blur', () => {
                                            editor.save();
                                            const comments = {
                                                header_body: tinymce.get('grc-header-' + sampleId)?.getContent() ?? '',
                                                main_body: tinymce.get('grc-main-' + sampleId)?.getContent() ?? '',
                                                notes_body: tinymce.get('grc-notes-' + sampleId)?.getContent() ?? '',
                                            };
                                            this.initialComments = comments;
                                            $wire.setSampleCommentsForSample(sampleId, comments);
                                        });
                                    }
                                });
                            },
                            initAll() {
                                this.initEditor('#grc-header-' + this.sampleId, 'header_body', this.initialComments.header_body || '');
                                this.initEditor('#grc-main-' + this.sampleId, 'main_body', this.initialComments.main_body || '');
                                this.initEditor('#grc-notes-' + this.sampleId, 'notes_body', this.initialComments.notes_body || '');
                            }
                        }"
                        x-init="setTimeout(() => initAll(), 150)">
                        <div class="form-group">
                            <label class="font-weight-semibold">Comments</label>
                            <textarea id="grc-header-{{ $activeSampleDetailId }}" class="form-control">{!! $activeComments['header_body'] !!}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">Recommendations / Interpretation</label>
                            <textarea id="grc-main-{{ $activeSampleDetailId }}" class="form-control">{!! $activeComments['main_body'] !!}</textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-semibold">Notes</label>
                            <textarea id="grc-notes-{{ $activeSampleDetailId }}" class="form-control">{!! $activeComments['notes_body'] !!}</textarea>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No sample selected for comments.
                </div>
            @endif
        </div>
        </div>
    @endif

    @script
    <script>
        Alpine.data('groupedResultsCaptureActions', () => ({
            activeSampleId() {
                const samples = $wire.get('samples') || [];
                const index = $wire.get('activeSampleIndex') ?? 0;
                const sample = samples[index];

                return sample && sample.sample_detail_id ? sample.sample_detail_id : null;
            },
            async syncActiveEditors() {
                if (typeof tinymce === 'undefined') {
                    return;
                }

                const sampleId = this.activeSampleId();
                if (!sampleId) {
                    return;
                }

                const comments = {
                    header_body: '',
                    main_body: '',
                    notes_body: '',
                };

                [
                    { editorId: 'grc-header-' + sampleId, field: 'header_body' },
                    { editorId: 'grc-main-' + sampleId, field: 'main_body' },
                    { editorId: 'grc-notes-' + sampleId, field: 'notes_body' },
                ].forEach(({ editorId, field }) => {
                    const editor = tinymce.get(editorId);
                    if (!editor) {
                        return;
                    }

                    editor.save();
                    comments[field] = editor.getContent();
                });

                await $wire.setSampleCommentsForSample(sampleId, comments);
            },
        }));
    </script>
    @endscript
</div>
