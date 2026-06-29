@php
    $rowIdx = $rowIdx ?? 0;
    $sectionId = $sectionId ?? 'section';
    $wirePrefix = $wirePrefix ?? ('formData.sample_description.'.$rowIdx);
    $modalId = 'trf-desc-modal-'.$sectionId.'-'.$rowIdx;
    $editorId = 'trf-desc-editor-'.$sectionId.'-'.$rowIdx;
    $html = (string) ($formData['sample_description'][$rowIdx] ?? '');
    $preview = trim(strip_tags($html));
@endphp
<button
    type="button"
    class="btn btn-xs btn-outline-secondary walk-in-trf-desc-btn"
    data-toggle="modal"
    data-target="#{{ $modalId }}"
    title="{{ $preview !== '' ? $preview : 'Edit sample description' }}"
    aria-label="Edit sample description"
>
    <i class="mdi mdi-text-box-outline"></i>
</button>

<div class="modal fade walk-in-trf-desc-modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self
    x-data="{
        editorId: @js($editorId),
        modalId: @js($modalId),
        wireKey: @js($wirePrefix),
        mountEditor() {
            if (typeof tinymce === 'undefined') {
                const s = document.createElement('script');
                s.src = '/tinymce/tinymce.min.js';
                s.onload = () => this.initTiny();
                document.head.appendChild(s);
            } else {
                this.initTiny();
            }
        },
        initTiny() {
            const self = this;
            if (tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
            tinymce.init({
                selector: '#' + this.editorId,
                height: 220,
                menubar: false,
                statusbar: false,
                plugins: 'lists',
                toolbar: 'bold italic underline | bullist numlist',
            });
        },
        destroyEditor() {
            if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
        }
    }"
    x-init="
        $nextTick(() => {
            if ($el.parentElement && ($el.parentElement.closest('.table-responsive') || $el.parentElement.closest('.receive-sample-modal-body'))) {
                document.body.appendChild($el);
            }
            $('#' + modalId).on('shown.bs.modal', () => mountEditor());
            $('#' + modalId).on('hidden.bs.modal', () => destroyEditor());
        });
    "
    @trf-destroy-editors.window="destroyEditor()"
>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Sample description — row {{ $rowIdx + 1 }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <textarea id="{{ $editorId }}" class="form-control">{!! $html !!}</textarea>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    onclick="window.saveWalkInTrfDescription(@js($modalId), @js($editorId), @js($wirePrefix))"
                >Save</button>
            </div>
        </div>
    </div>
</div>
