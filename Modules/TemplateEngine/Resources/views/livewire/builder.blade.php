<div class="builder-container">
    <style>
    /* === Main Builder Container === */
    .builder-container {
        display: flex;
        flex-direction: row;
    }
    
    /* === Modern Color Palette === */
    :root {
        --primary-color: #4f46e5;
        --primary-light: #818cf8;
        --primary-dark: #3730a3;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
     /* === Sections Panel (Left Sidebar) === */
     .sections-panel {
         width: 300px;
         min-width: 300px;
         height: calc(100vh - 100px);
         background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
         border-right: 1px solid var(--gray-200);
         z-index: 100;
         display: flex;
         flex-direction: column;
         box-shadow: var(--shadow-md);
         flex-shrink: 0;
     }

    .sections-panel .panel-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    .sections-panel .panel-header h6 {
        color: white;
        font-weight: 600;
        font-size: 15px;
        margin: 0;
    }

    .sections-panel .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .section-panel-item {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: var(--shadow-sm);
    }

    .section-panel-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .section-panel-item.active-section {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }

    .section-panel-header {
        padding: 14px 16px;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
        border-bottom: 1px solid transparent;
    }
    
    .section-title {
        flex: 1;
        font-weight: 600;
        font-size: 14px;
        color: var(--gray-800);
    }

     /* === Designer Canvas (Center) === */
    .designer-canvas-wrapper {
        flex: 1;
        padding: 24px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        height: calc(100vh - 100px);
        overflow: auto;
        box-sizing: border-box;
    }

    .canvas-toolbar {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 12px 12px 0 0;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: -1px;
        box-shadow: var(--shadow-sm);
    }

    .canvas-container {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        overflow: auto;
        border: 1px solid var(--gray-200);
        border-radius: 0 0 12px 12px;
        padding: 48px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        min-height: 600px;
    }

    .designer-canvas {
        background: #ffffff;
        box-shadow: var(--shadow-xl);
        position: relative;
        margin: 0 auto;
        background-image: 
            linear-gradient(var(--gray-100) 1px, transparent 1px),
            linear-gradient(90deg, var(--gray-100) 1px, transparent 1px);
        background-size: 20px 20px;
        background-position: -1px -1px;
        border-radius: 4px;
        padding: 40px;
        min-height: 800px; /* A4 height approx scale */
        width: 100%;
        max-width: 900px;
    }
    
    .canvas-section {
        border: 1px solid var(--gray-200);
        border-radius: 4px; /* More document like */
        margin-bottom: 24px;
        padding: 16px;
        position: relative;
    }
    
    .canvas-section:hover {
        border-color: var(--primary-light);
    }
    
    .canvas-section.active {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    }
    
    .section-label-corner {
        position: absolute;
        top: -10px;
        left: 10px;
        background: var(--gray-100);
        border: 1px solid var(--gray-300);
        color: var(--gray-600);
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
    }
    
    .add-field-placeholder {
        border: 2px dashed var(--gray-300);
        border-radius: 6px;
        padding: 12px;
        text-align: center;
        color: var(--gray-400);
        margin-top: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .add-field-placeholder:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }

    /* === Element Styles === */
    .field-item {
        margin-bottom: 12px;
        position: relative;
    }
    
    .field-item-actions {
        position: absolute;
        right: 0;
        top: 0;
        display: none;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 4px;
        padding: 2px;
        z-index: 10;
        box-shadow: var(--shadow-sm);
    }
    
    .field-item:hover .field-item-actions {
        display: flex;
    }
    
    .field-label {
        font-weight: 500;
        margin-bottom: 4px;
        color: var(--gray-700);
    }
    
    .field-preview {
        padding: 8px 12px;
        border: 1px solid var(--gray-300);
        border-radius: 4px;
        background: var(--gray-50);
        color: var(--gray-500);
        font-size: 14px;
    }
    
    /* Container/Holder Styles */
    .element-holder-container {
        border: 1px solid var(--gray-300);
        background: #fdfdfd;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .holder-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        border-bottom: 1px solid var(--gray-100);
        padding-bottom: 4px;
    }
    
    .holder-title {
        font-weight: 600;
        font-size: 12px;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    </style>

    <!-- Sidebar / Navigation -->
    <div class="sections-panel">
        <div class="panel-header">
            <h6><i class="fas fa-layer-group mr-2"></i>Sections</h6>
            <!-- Add Section Button -->
            <button class="btn btn-sm btn-outline-light" onclick="alert('In progress: Add Section')">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="panel-body">
            @foreach($sections as $section)
                <div class="section-panel-item {{ $currentSectionId == $section->id ? 'active-section' : '' }}">
                    <div class="section-panel-header" wire:click="$set('currentSectionId', {{ $section->id }})">
                        <i class="fas fa-grip-vertical text-muted"></i>
                        <span class="section-title">{{ $section->title }}</span>
                        <div class="section-panel-actions">
                             <a href="#section-{{ $section->id }}" class="btn btn-xs btn-light">
                                <i class="fas fa-arrow-right"></i>
                             </a>
                        </div>
                    </div>
                </div>
            @endforeach
            
            <div class="mt-4">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-3">Toolbox</h6>
                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <button class="btn btn-sm btn-white border shadow-sm" wire:click="addField({{ $currentSectionId ?? $sections->first()->id ?? 0 }})" {{ !$currentSectionId && $sections->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-plus text-primary mr-1"></i> Add Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Canvas -->
    <div class="designer-canvas-wrapper">
        <div class="canvas-toolbar">
            <div class="canvas-info">
                 <span class="font-weight-bold text-dark">{{ $template->name }}</span>
                 <span class="badge badge-light ml-2">{{ $template->status }}</span>
            </div>
            <div class="canvas-controls">
                <button class="btn btn-white" wire:click="$refresh"><i class="fas fa-sync-alt"></i></button>
                <a href="{{ route('templates.preview', $template->id) }}" target="_blank" class="btn btn-white"><i class="fas fa-eye"></i> Preview</a>
            </div>
        </div>
        
        <div class="canvas-container">
            <div class="designer-canvas">
                @if($sections->isEmpty())
                    <div class="text-center py-5">
                         <i class="fas fa-layer-group text-muted mb-3" style="font-size: 3rem;"></i>
                         <h5 class="text-muted">No sections yet</h5>
                         <p>Add a section to start building your template.</p>
                    </div>
                @endif
                
                @foreach($sections as $section)
                    <div class="canvas-section {{ $currentSectionId == $section->id ? 'active' : '' }}" id="section-{{ $section->id }}" wire:click="$set('currentSectionId', {{ $section->id }})">
                        <span class="section-label-corner">{{ $section->title }}</span>
                        
                        @if($section->fields->count() > 0)
                            @foreach($section->fields as $field)
                                @include('template-engine::livewire.partials.field-item', ['field' => $field])
                            @endforeach
                        @endif
                        
                        <div class="add-field-placeholder" wire:click="addField({{ $section->id }})">
                            <i class="fas fa-plus-circle mr-1"></i> Add Element to {{ $section->title }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Field Modal -->
    @if($showFieldModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold">
                            @if($editingFieldId)
                                <i class="fas fa-edit text-primary mr-2"></i> Edit Field
                            @else
                                <i class="fas fa-plus text-success mr-2"></i> New Field
                            @endif
                        </h5>
                        <button type="button" class="close" wire:click="$set('showFieldModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-uppercase text-muted">Field Type</label>
                                    <select wire:model.live="fieldData.type" class="form-control custom-select">
                                        <option value="text">Text Input</option>
                                        <option value="textarea">Text Area</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date picker</option>
                                        <option value="select">Dropdown (Static)</option>
                                        <option value="radio">Radio Buttons</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="dynamic">Dataset Binding (Dynamic)</option>
                                        <optgroup label="Layout & Structure">
                                            <option value="container">Container (Holder)</option>
                                        </optgroup>
                                        <optgroup label="Static Content">
                                            <option value="heading">Heading</option>
                                            <option value="paragraph">Paragraph</option>
                                            <option value="blockquote">Blockquote</option>
                                            <option value="code_block">Code Block</option>
                                            <option value="link">Link</option>
                                            <option value="image">Image</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-uppercase text-muted">Label / Content</label>
                                     @if(in_array($fieldData['type'], ['paragraph', 'blockquote', 'code_block']))
                                        <textarea wire:model="fieldData.label" class="form-control" rows="4" placeholder="Enter content..."></textarea>
                                     @else
                                        <input type="text" wire:model="fieldData.label" class="form-control" placeholder="Field Label">
                                     @endif
                                    @error('fieldData.label') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                             <div class="col-12">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="reqSwitch" wire:model="fieldData.required">
                                        <label class="custom-control-label" for="reqSwitch">Required Field</label>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Type Specific Configs -->
                        @if($fieldData['type'] === 'heading')
                            <div class="row bg-light p-3 rounded mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label>Heading Level</label>
                                        <select wire:model.live="fieldData.meta.level" class="form-control">
                                            <option value="h1">H1 (Main Title)</option>
                                            <option value="h2">H2 (Section)</option>
                                            <option value="h3">H3 (Subsection)</option>
                                            <option value="h4">H4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($fieldData['type'] === 'select' || $fieldData['type'] === 'radio')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold mb-0">Options</h6>
                                    <button class="btn btn-xs btn-outline-primary" wire:click="addOption">
                                        <i class="fas fa-plus"></i> Add Option
                                    </button>
                                </div>
                                <p class="small text-muted mb-3">Define the choices available for this field.</p>
                                
                                <div class="options-list">
                                    @foreach($fieldData['options'] as $index => $option)
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <input type="text" wire:model="fieldData.options.{{ $index }}.label" class="form-control form-control-sm" placeholder="Label">
                                            </div>
                                            <div class="col-5">
                                                <input type="text" wire:model="fieldData.options.{{ $index }}.value" class="form-control form-control-sm" placeholder="Value">
                                            </div>
                                            <div class="col-2">
                                                <button class="btn btn-sm btn-outline-danger" wire:click="removeOption({{ $index }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if(empty($fieldData['options']))
                                        <div class="alert alert-warning small py-2">No options defined.</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($fieldData['type'] === 'image')
                            <div class="row bg-light p-3 rounded mb-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Image URL</label>
                                        <input type="url" wire:model="fieldData.meta.url" class="form-control" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($fieldData['type'] === 'link')
                            <div class="row bg-light p-3 rounded mb-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Link URL</label>
                                        <input type="url" wire:model="fieldData.meta.url" class="form-control" placeholder="https://...">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="custom-control custom-checkbox mt-3">
                                        <input type="checkbox" class="custom-control-input" id="newTabCheck" wire:model="fieldData.meta.new_tab">
                                        <label class="custom-control-label" for="newTabCheck">Open in new tab</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($fieldData['type'] === 'code_block')
                             <div class="row bg-light p-3 rounded mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Language</label>
                                        <input type="text" wire:model="fieldData.meta.language" class="form-control" placeholder="javascript">
                                    </div>
                                </div>
                             </div>
                        @endif

                        <!-- Dynamic Field Config -->
                        @if($fieldData['type'] === 'dynamic')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-database mr-2"></i>Database Binding</h6>
                                @livewire('template-engine::dataset-selector', ['binding' => $fieldData['dataset_binding'] ?? []], 'ds-'.time())
                            </div>
                        @endif
                        
                        @if($fieldData['parent_field_id'])
                            <div class="alert alert-warning small">
                                <i class="fas fa-level-up-alt mr-1"></i> Adding as child of existing container.
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showFieldModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" wire:click="saveField">
                            <i class="fas fa-save mr-1"></i> Save Field
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
