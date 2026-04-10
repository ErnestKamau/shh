@extends('layouts.audit.layout.app')

@section('title2')
<title>Edit NC {{ $nc->nc_number }} - JASIRI LIMS</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-pencil"></i> Edit: {{ $nc->nc_number }}</h4>
                <a href="{{ route('audit.nc.show', $nc->id) }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit-module.non-conformance-form', ['ncId' => $nc->id])
        </div>
    </div>
</div>
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    // Store TinyMCE content before Livewire updates
    var preservedTinyMCEContent = {};
    
    function preserveTinyMCEContent() {
        if (typeof tinymce === 'undefined') return;
        
        $('textarea.editor').each(function() {
            var textareaId = $(this).attr('id');
            if (textareaId) {
                var editor = tinymce.get(textareaId);
                if (editor) {
                    // Save content to both the editor and preserve it
                    editor.save();
                    preservedTinyMCEContent[textareaId] = editor.getContent();
                } else {
                    // If editor not initialized yet, save from textarea
                    preservedTinyMCEContent[textareaId] = $(this).val();
                }
            }
        });
    }
    
    function initTinyMCE() {
        if (typeof tinymce === 'undefined') return;
        
        // Find all textareas with editor class that don't have TinyMCE yet
        $('textarea.editor').each(function() {
            var $textarea = $(this);
            var textareaId = $textarea.attr('id') || 'editor-' + Math.random().toString(36).substr(2, 9);
            if (!$textarea.attr('id')) {
                $textarea.attr('id', textareaId);
            }
            
            // Check if TinyMCE is already initialized for this textarea
            if (tinymce.get(textareaId)) {
                return; // Skip if already initialized
            }
            
            // Get preserved content if available
            var savedContent = preservedTinyMCEContent[textareaId] || $textarea.val() || '';
            
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
                    // Set initial content from preserved content
                    editor.on('init', function() {
                        if (savedContent) {
                            editor.setContent(savedContent);
                        }
                    });
                    
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
            // Initialize immediately
            setTimeout(function() {
                initTinyMCE();
            }, 50);
        } else {
            // If TinyMCE isn't loaded yet, wait for it
            var checkTinyMCE = setInterval(function() {
                if (typeof tinymce !== 'undefined') {
                    clearInterval(checkTinyMCE);
                    setTimeout(function() {
                        initTinyMCE();
                    }, 50);
                }
            }, 100);
        }
    });

    // Initialize TinyMCE when Livewire component is mounted (before it's visible)
    document.addEventListener('livewire:init', function() {
        // Initialize immediately when Livewire is ready
        setTimeout(function() {
            initTinyMCE();
        }, 10);
        
        // Preserve content before Livewire sends update
        Livewire.hook('message.sent', (message, component) => {
            preserveTinyMCEContent();
        });
        
        // Initialize before any Livewire updates
        Livewire.hook('morph.updating', ({ component, el }) => {
            // Initialize TinyMCE before the update
            setTimeout(function() {
                initTinyMCE();
            }, 10);
        });
        
        // Reinitialize after any Livewire update
        Livewire.hook('message.processed', (message, component) => {
            setTimeout(function() {
                initTinyMCE();
            }, 100);
        });
        
        // Also reinitialize after DOM morphing
        Livewire.hook('morph.updated', ({ el, component }) => {
            setTimeout(function() {
                initTinyMCE();
            }, 100);
        });
    });
    
    // Fallback for Livewire 2 compatibility
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.sent', (message, component) => {
                preserveTinyMCEContent();
            });
            
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(function() {
                    initTinyMCE();
                }, 250);
            });
        });
    }
</script>
@endsection

