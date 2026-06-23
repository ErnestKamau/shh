@php
    $rowIdx = $rowIdx ?? 0;
    $editorId = 'trf-sample-desc-'.$rowIdx;
@endphp
<div
    class="trf-sample-desc-editor"
    wire:ignore
    x-data="{
        editorId: @js($editorId),
        wireKey: @js('formData.sample_rows.'.$rowIdx.'.sample_description'),
        initEditor() {
            if (typeof tinymce === 'undefined') {
                const s = document.createElement('script');
                s.src = '/tinymce/tinymce.min.js';
                s.onload = () => this.mountEditor();
                document.head.appendChild(s);
            } else {
                this.mountEditor();
            }
        },
        mountEditor() {
            const self = this;
            if (tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
            tinymce.init({
                selector: '#' + this.editorId,
                height: 80,
                menubar: false,
                statusbar: false,
                plugins: 'lists',
                toolbar: 'bold italic underline | bullist numlist',
                setup(editor) {
                    editor.on('change blur', function () {
                        $wire.set(self.wireKey, editor.getContent());
                    });
                }
            });
        },
        destroyEditor() {
            if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
        }
    }"
    x-init="initEditor()"
    @trf-destroy-editors.window="destroyEditor()"
>
    <textarea id="{{ $editorId }}" class="form-control form-control-xs">{{ $row['sample_description'] ?? '' }}</textarea>
</div>
