@php
    $editorId = $editorId ?? ('lw-rich-'.uniqid());
    $wireKey = $wireKey ?? 'value';
    $html = (string) ($value ?? '');
    $height = (int) ($height ?? 160);
    $rowIndex = isset($rowIndex) ? (int) $rowIndex : null;
    $cardEventPrefix = $cardEventPrefix ?? 'ceq-sample-card';
@endphp
{{-- wire:ignore is required for TinyMCE (rich editor plugin lifecycle). --}}
<div
    class="lw-tinymce-field"
    wire:ignore
    x-data="{
        editorId: @js($editorId),
        wireKey: @js($wireKey),
        rowIndex: @js($rowIndex),
        syncBeforeDestroy() {
            if (typeof tinymce === 'undefined') {
                return;
            }
            const editor = tinymce.get(this.editorId);
            if (editor && this.$wire) {
                this.$wire.set(this.wireKey, editor.getContent(), false);
            }
        },
        initEditor() {
            if (typeof tinymce === 'undefined') {
                const existing = document.querySelector('script[data-lw-tinymce]');
                if (existing) {
                    existing.addEventListener('load', () => this.mountEditor());
                    return;
                }
                const script = document.createElement('script');
                script.src = '/tinymce/tinymce.min.js';
                script.dataset.lwTinymce = '1';
                script.onload = () => this.mountEditor();
                document.head.appendChild(script);
                return;
            }
            this.mountEditor();
        },
        mountEditor() {
            if (typeof tinymce === 'undefined') {
                return;
            }
            if (tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
            const textarea = document.getElementById(this.editorId);
            if (!textarea || textarea.offsetParent === null) {
                return;
            }
            if (this.$wire) {
                const content = this.$wire.get(this.wireKey);
                if (typeof content === 'string') {
                    textarea.value = content;
                }
            }
            const self = this;
            tinymce.init({
                selector: '#' + this.editorId,
                height: {{ $height }},
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: 'lists',
                toolbar: 'bold italic underline | bullist numlist',
                setup(editor) {
                    editor.on('change keyup blur', function () {
                        if (self.$wire) {
                            self.$wire.set(self.wireKey, editor.getContent(), false);
                            window.dispatchEvent(new CustomEvent('ceq-sample-progress-refresh'));
                        }
                    });
                },
            });
        },
        destroyEditor() {
            this.syncBeforeDestroy();
            if (typeof tinymce !== 'undefined' && tinymce.get(this.editorId)) {
                tinymce.remove('#' + this.editorId);
            }
        },
        remountEditor() {
            this.destroyEditor();
            this.$nextTick(() => this.initEditor());
        }
    }"
    x-init="if (rowIndex === null) { $nextTick(() => initEditor()); }"
    x-on:lw-destroy-tinymce.window="destroyEditor()"
    x-on:ceq-sync-tinymce.window="syncBeforeDestroy()"
    @if($rowIndex !== null)
        x-on:{{ $cardEventPrefix }}-hidden.window="if ($event.detail.row === rowIndex) destroyEditor()"
        x-on:{{ $cardEventPrefix }}-shown.window="if ($event.detail.row === rowIndex) remountEditor()"
    @endif
>
    <textarea id="{{ $editorId }}" class="form-control">{!! $html !!}</textarea>
</div>
