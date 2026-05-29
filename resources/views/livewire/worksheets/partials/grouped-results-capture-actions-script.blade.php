@once
    <script>
        window.groupedResultsCaptureActions = function (livewire) {
            return {
                activeSampleId() {
                    const samples = typeof livewire.get === 'function'
                        ? livewire.get('samples')
                        : (livewire.samples || []);
                    const index = typeof livewire.get === 'function'
                        ? livewire.get('activeSampleIndex')
                        : (livewire.activeSampleIndex ?? 0);
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

                    await livewire.setSampleCommentsForSample(sampleId, comments);
                },
            };
        };
    </script>
@endonce
