@extends('layouts.audit.layout.app')

@section('title2')
<title>Create New Audit - JASIRI LIMS</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-plus-circle"></i> Schedule New Audit</h4>
                <a href="{{ route('audit.audits.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit-module.audit-form')
        </div>
    </div>
</div>
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    function initTinyMCE() {
        if (typeof tinymce === 'undefined') return;
        
        // Find all editor textareas that aren't initialized yet
        $('textarea.editor').each(function() {
            const textareaId = $(this).attr('id') || 'editor-' + Math.random().toString(36).substr(2, 9);
            if (!$(this).attr('id')) {
                $(this).attr('id', textareaId);
            }
            
            // Skip if already initialized
            if (tinymce.get(textareaId)) {
                return;
            }
            
            // Initialize TinyMCE for this textarea
            tinymce.init({
                selector: '#' + textareaId,
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    // Sync with Livewire on change
                    editor.on('change keyup', function() {
                        editor.save();
                        // Get the wire:model attribute and update Livewire
                        var wireModel = editor.targetElm.getAttribute('wire:model');
                        if (wireModel) {
                            // Use Livewire's wire:model binding
                            var event = new Event('input', { bubbles: true });
                            editor.targetElm.value = editor.getContent();
                            editor.targetElm.dispatchEvent(event);
                        }
                    });
                }
            });
        });
    }

    // Initialize TinyMCE as soon as possible - before Livewire renders
    $(document).ready(function() {
        // Wait for TinyMCE script to load
        if (typeof tinymce !== 'undefined') {
            initTinyMCE();
        } else {
            // If TinyMCE isn't loaded yet, wait for it
            var checkTinyMCE = setInterval(function() {
                if (typeof tinymce !== 'undefined') {
                    clearInterval(checkTinyMCE);
                    initTinyMCE();
                }
            }, 100);
        }
    });

    // Initialize TinyMCE when Livewire component is mounted (before it's visible)
    document.addEventListener('livewire:init', function() {
        // Initialize immediately when Livewire is ready
        setTimeout(function() {
            initTinyMCE();
        }, 50);
        
        // Also initialize before any Livewire updates
        Livewire.hook('morph.updating', ({ component, el }) => {
            // Initialize TinyMCE before the update
            setTimeout(function() {
                initTinyMCE();
            }, 10);
        });
        
        // Reinitialize after Livewire updates
        Livewire.hook('message.processed', (message, component) => {
            setTimeout(function() {
                initTinyMCE();
            }, 100);
        });
    });
</script>
@endsection

