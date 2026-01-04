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
        <div class="panel-header p-0">
            <div class="btn-group w-100" role="group">
                <button type="button" class="btn {{ !$showVariablesPanel ? 'btn-primary' : 'btn-light' }} rounded-0 py-3 font-weight-bold" wire:click="$set('showVariablesPanel', false)" style="border:none;">
                    <i class="fas fa-layer-group mr-2"></i>Sections
                </button>
                <button type="button" class="btn {{ $showVariablesPanel ? 'btn-primary' : 'btn-light' }} rounded-0 py-3 font-weight-bold" wire:click="$set('showVariablesPanel', true)" style="border:none;">
                    <i class="fas fa-brackets-curly mr-2"></i>Variables
                </button>
            </div>
        </div>
        <div class="panel-body">
            @if(!$showVariablesPanel)
                <!-- SECTIONS VIEW -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted text-uppercase small font-weight-bold mb-0">Sections List</h6>
                     <button class="btn btn-xs btn-outline-primary" onclick="alert('In progress: Add Section')">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
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
            @else
                <!-- VARIABLES VIEW -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted text-uppercase small font-weight-bold mb-0">Defined Variables</h6>
                     <button class="btn btn-xs btn-outline-primary" wire:click="openVariableModal">
                        <i class="fas fa-plus"></i> New
                    </button>
                </div>
                
                @if($variables->isEmpty())
                    <div class="text-center py-4 bg-light rounded border border-dashed">
                        <i class="fas fa-code text-muted mb-2"></i>
                        <p class="small text-muted mb-0">No variables yet</p>
                    </div>
                @else
                    @foreach($variables as $variable)
                        <div class="card mb-2 shadow-sm border-0">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <div class="overflow-hidden mr-2">
                                    <div class="font-weight-bold text-truncate" title="{{ $variable->name }}">
                                        <code class="text-primary"><?php echo "{{" . $variable->name . "}}"; ?></code>
                                    </div>
                                    <div class="small">
                                        <span class="badge badge-light border">{{ $variable->type }}</span>
                                        <span class="text-muted text-xs ml-1">{{ $variable->data_type }}</span>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-white border" wire:click="openVariableModal({{ $variable->id }})">
                                        <i class="fas fa-cog text-muted"></i>
                                    </button>
                                     <button class="btn btn-xs btn-white border text-danger" wire:click="deleteVariable({{ $variable->id }})" wire:confirm="Are you sure?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                <div class="mt-3 p-3 bg-light rounded small text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Use variables in fields matching <code>@{{ name }}</code>
                </div>
            @endif
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
                            @foreach($section->fields->where('parent_field_id', null) as $field)
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
            <div class="modal-dialog modal-lg modal-dialog-centered" style="max-height: 90vh; margin: 1.75rem auto;">
                <div class="modal-content border-0 shadow-lg d-flex flex-column" style="border-radius: 12px; max-height: 90vh;">
                    <div class="modal-header bg-light flex-shrink-0">
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
                    <div class="modal-body p-4" style="overflow-y: auto; flex: 1 1 auto; min-height: 0;">
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
                                        <option value="image_upload">Image Upload</option>
                                        <optgroup label="Layout & Structure">
                                            <option value="container">Container (Holder)</option>
                                            <option value="dynamic_table">Dynamic Table</option>
                                            <option value="ul">Unordered List (UL)</option>
                                            <option value="ol">Ordered List (OL)</option>
                                        </optgroup>
                                        <optgroup label="Static Content">
                                            <option value="heading">Heading</option>
                                            <option value="paragraph">Paragraph</option>
                                            <option value="blockquote">Blockquote</option>
                                            <option value="code_block">Code Block</option>
                                            <option value="link">Link</option>
                                            <option value="image">Image (URL)</option>
                                            <option value="static_image_upload">Image Upload (Static)</option>
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

                        {{-- Default Value Configuration for Input Fields --}}
                        @if(in_array($fieldData['type'], ['text', 'textarea', 'number', 'date']))
                            <div class="row bg-light p-2 rounded mb-2 mx-0 border">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold small text-muted mb-1">Default Value Source</label>
                                        <div class="d-flex align-items-center">
                                            <select wire:model.live="fieldData.meta.default_source" class="form-control form-control-sm w-auto mr-2">
                                                <option value="manual">Manual / None</option>
                                                <option value="variable">From Variable</option>
                                            </select>
                                            
                                            @if(isset($fieldData['meta']['default_source']) && $fieldData['meta']['default_source'] === 'variable')
                                                <select wire:model.live="fieldData.meta.default_variable_id" class="form-control form-control-sm">
                                                    <option value="">-- Select Variable --</option>
                                                    @foreach($variables as $var)
                                                        <option value="{{ $var->id }}">{{ $var->name }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(!isset($fieldData['meta']['default_source']) || $fieldData['meta']['default_source'] === 'manual')
                                                 <input type="text" wire:model="fieldData.default_value" class="form-control form-control-sm" placeholder="Default value (optional)">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
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
                                    <h6 class="font-weight-bold mb-0">Options Configuration</h6>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Data Source</label>
                                    <select wire:model.live="fieldData.meta.data_source" class="form-control">
                                        <option value="manual">Manual Options</option>
                                        <option value="variable">From Variable</option>
                                    </select>
                                </div>

                                @if(isset($fieldData['meta']['data_source']) && $fieldData['meta']['data_source'] === 'variable')
                                    <div class="variable-config p-2 border rounded bg-white">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Select Variable</label>
                                            <select wire:model.live="fieldData.meta.variable_id" class="form-control">
                                                <option value="">-- Choose Variable --</option>
                                                @foreach($variables as $var)
                                                    <option value="{{ $var->id }}">{{ $var->name }} ({{ $var->type }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @php
                                            $selectedVar = $variables->find($fieldData['meta']['variable_id'] ?? null);
                                        @endphp

                                        @if($selectedVar && $selectedVar->type === 'database')
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold small">Label Column</label>
                                                        <input type="text" wire:model="fieldData.meta.label_column" class="form-control" placeholder="e.g. name">
                                                        <small class="text-muted">Column to show in the dropdown</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold small">Value Column</label>
                                                        <input type="text" wire:model="fieldData.meta.value_column" class="form-control" placeholder="e.g. id">
                                                        <small class="text-muted">Column to save as value</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($selectedVar)
                                            <div class="alert alert-info small p-2 mb-0">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Using variable <strong>{{ $selectedVar->name }}</strong> as source.
                                                Ensure it contains a list or array of values.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="manual-options">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="font-weight-bold small mb-0 text-muted">Manual Options List</label>
                                            <button class="btn btn-xs btn-outline-primary" wire:click="addOption">
                                                <i class="fas fa-plus"></i> Add Option
                                            </button>
                                        </div>
                                        
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

                        @if($fieldData['type'] === 'static_image_upload')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fas fa-image mr-2"></i>Static Image Upload Configuration
                                </h6>
                                
                                <!-- Image Upload -->
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Upload Image</label>
                                    <input type="file" 
                                           wire:model="staticImageUpload" 
                                           class="form-control" 
                                           accept="image/*">
                                    <small class="text-muted">Max size: 5MB. Supported: JPG, PNG, GIF, SVG, WebP</small>
                                    
                                    @if($staticImageUpload)
                                        <div class="mt-2">
                                            <img src="{{ $staticImageUpload->temporaryUrl() }}" 
                                                 alt="Preview" 
                                                 class="img-fluid border rounded p-2 bg-white"
                                                 style="max-width: 200px; max-height: 200px;">
                                            <div class="small text-success mt-1">
                                                <i class="fas fa-check-circle"></i> Image ready to upload
                                            </div>
                                        </div>
                                    @elseif(isset($fieldData['meta']['image_path']) && $fieldData['meta']['image_path'])
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $fieldData['meta']['image_path']) }}" 
                                                 alt="Current Image" 
                                                 class="img-fluid border rounded p-2 bg-white"
                                                 style="max-width: 200px; max-height: 200px;">
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-image"></i> Current image
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @error('staticImageUpload') 
                                        <div class="alert alert-danger alert-sm mt-2">
                                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    
                                    <div wire:loading wire:target="staticImageUpload" class="mt-2">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <span class="small text-muted ml-2">Uploading...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Image Properties -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Width</label>
                                            <input type="text" 
                                                   wire:model="fieldData.meta.width" 
                                                   class="form-control" 
                                                   placeholder="auto, 100%, 200px">
                                            <small class="text-muted">CSS value (px, %, auto, etc.)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Height</label>
                                            <input type="text" 
                                                   wire:model="fieldData.meta.height" 
                                                   class="form-control" 
                                                   placeholder="auto, 100px">
                                            <small class="text-muted">CSS value (px, auto, etc.)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Alignment</label>
                                            <select wire:model="fieldData.meta.alignment" class="form-control">
                                                <option value="left">Left</option>
                                                <option value="center">Center</option>
                                                <option value="right">Right</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Alt Text</label>
                                            <input type="text" 
                                                   wire:model="fieldData.meta.alt_text" 
                                                   class="form-control" 
                                                   placeholder="Alternative text for image">
                                            <small class="text-muted">For accessibility</small>
                                        </div>
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

                        <!-- Dynamic Table Config -->
                        @if($fieldData['type'] === 'dynamic_table')
                            <div class="bg-white p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-table mr-2"></i>Table Configuration</h6>
                                
                                {{-- Table Settings --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Width</label>
                                        <input type="text" wire:model="fieldData.meta.css.width" class="form-control form-control-sm" placeholder="100%">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Class</label>
                                        <input type="text" wire:model="fieldData.meta.css.class" class="form-control form-control-sm" placeholder="table table-bordered">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold">Border Collapse</label>
                                        <select wire:model="fieldData.meta.css.border_collapse" class="form-control form-control-sm">
                                            <option value="collapse">Collapse</option>
                                            <option value="separate">Separate</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                {{-- Headers --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold small mb-0">Headers (Columns)</label>
                                    <button class="btn btn-xs btn-outline-primary" wire:click="addTableHeader">
                                        <i class="fas fa-plus"></i> Add Column
                                    </button>
                                </div>
                                <div class="table-responsive mb-3 border rounded">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 50%">Label</th>
                                                <th>Width</th>
                                                <th>Style</th>
                                                <th style="width: 40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fieldData['meta']['headers'] ?? [] as $i => $header)
                                                <tr>
                                                    <td><input type="text" wire:model="fieldData.meta.headers.{{ $i }}.label" class="form-control form-control-sm" placeholder="Header Name"></td>
                                                    <td><input type="text" wire:model="fieldData.meta.headers.{{ $i }}.width" class="form-control form-control-sm" placeholder="auto"></td>
                                                    <td><input type="text" wire:model="fieldData.meta.headers.{{ $i }}.style" class="form-control form-control-sm" placeholder="CSS"></td>
                                                    <td>
                                                        <button class="btn btn-xs btn-outline-danger" wire:click="removeTableHeader({{ $i }})"><i class="fas fa-times"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                {{-- Rows --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold small mb-0">Table Body Rows</label>
                                    <button class="btn btn-xs btn-outline-primary" wire:click="addTableRow">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>

                                @foreach($fieldData['meta']['rows'] ?? [] as $rowIndex => $row)
                                    <div class="card mb-2 border">
                                        <div class="card-header py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge badge-secondary mr-2">Row {{ $rowIndex + 1 }}</span>
                                                <select wire:model.live="fieldData.meta.rows.{{ $rowIndex }}.type" class="custom-select custom-select-sm w-auto d-inline-block">
                                                    <option value="static">Static Row</option>
                                                    <option value="variable">Variable Loop</option>
                                                </select>
                                                @if($row['type'] === 'variable')
                                                     <select wire:model="fieldData.meta.rows.{{ $rowIndex }}.source" class="custom-select custom-select-sm w-auto d-inline-block ml-2">
                                                        <option value="">-- Select Loop Variable --</option>
                                                        @foreach($variables as $var)
                                                            @if($var->data_type === 'collection' || $var->type === 'database')
                                                                <option value="{{ $var->id }}">{{ $var->name }}</option>
                                                            @endif
                                                        @endforeach
                                                     </select>
                                                @endif
                                            </div>
                                            <button class="btn btn-xs btn-outline-danger" wire:click="removeTableRow({{ $rowIndex }})"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr class="text-muted small">
                                                            @foreach($fieldData['meta']['headers'] ?? [] as $h)
                                                                <th>{{ $h['label'] }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            @foreach($row['cells'] ?? [] as $cellIndex => $cell)
                                                                @if($cellIndex < count($fieldData['meta']['headers'] ?? []))
                                                                    <td>
                                                                        <textarea wire:model="fieldData.meta.rows.{{ $rowIndex }}.cells.{{ $cellIndex }}.content" class="form-control form-control-sm mb-1" rows="1" placeholder="Content..."></textarea>
                                                                        <div class="d-flex">
                                                                            <input type="number" wire:model="fieldData.meta.rows.{{ $rowIndex }}.cells.{{ $cellIndex }}.colspan" class="form-control form-control-sm mr-1" style="width: 50px" title="Colspan" placeholder="1">
                                                                            <input type="text" wire:model="fieldData.meta.rows.{{ $rowIndex }}.cells.{{ $cellIndex }}.style" class="form-control form-control-sm" placeholder="Style...">
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Dynamic Field Config -->
                        @if($fieldData['type'] === 'dynamic')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-database mr-2"></i>Database Binding</h6>
                                @livewire('template-engine::dataset-selector', ['binding' => $fieldData['dataset_binding'] ?? []], 'ds-'.time())
                            </div>
                        @endif

                        <!-- Image Upload Configuration -->
                        @if($fieldData['type'] === 'image_upload')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Image Upload Configuration
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Max File Size (MB)</label>
                                            <input type="number" 
                                                   wire:model="fieldData.meta.max_size" 
                                                   class="form-control" 
                                                   placeholder="2" 
                                                   min="0.1" 
                                                   max="20" 
                                                   step="0.1">
                                            <small class="text-muted">Maximum: 20MB</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Allowed File Types</label>
                                            <select wire:model="fieldData.meta.allowed_types" 
                                                    class="form-control" 
                                                    multiple 
                                                    size="4">
                                                <option value="jpg">JPG</option>
                                                <option value="jpeg">JPEG</option>
                                                <option value="png">PNG</option>
                                                <option value="gif">GIF</option>
                                                <option value="svg">SVG</option>
                                                <option value="webp">WebP</option>
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Display Width</label>
                                            <input type="text" 
                                                   wire:model="fieldData.meta.display_width" 
                                                   class="form-control" 
                                                   placeholder="200px or 100%">
                                            <small class="text-muted">CSS value (px, %, em, etc.)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Display Height</label>
                                            <input type="text" 
                                                   wire:model="fieldData.meta.display_height" 
                                                   class="form-control" 
                                                   placeholder="auto">
                                            <small class="text-muted">CSS value (px, auto, etc.)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Max Image Width (px)</label>
                                            <input type="number" 
                                                   wire:model="fieldData.meta.max_width" 
                                                   class="form-control" 
                                                   placeholder="1920">
                                            <small class="text-muted">Optional - resize if larger</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Max Image Height (px)</label>
                                            <input type="number" 
                                                   wire:model="fieldData.meta.max_height" 
                                                   class="form-control" 
                                                   placeholder="1080">
                                            <small class="text-muted">Optional - resize if larger</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- List Configuration -->
                        @if(in_array($fieldData['type'], ['ul', 'ol', 'select', 'radio', 'checkbox']))
                             <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fas fa-database mr-2"></i>Data Sources
                                    <span class="badge badge-pill badge-light border ml-2 text-muted" style="font-size: 0.6em; vertical-align: middle;">MULTIPLE SUPPORTED</span>
                                </h6>
                                
                                {{-- Source List Manager --}}
                                <div class="source-manager mb-3">
                                    @if(isset($fieldData['data_sources']) && count($fieldData['data_sources']) > 0)
                                        <div class="list-group mb-3 shadow-sm">
                                            @foreach($fieldData['data_sources'] as $index => $source)
                                                <div class="list-group-item p-3 border-left-primary {{ $index % 2 == 0 ? 'bg-white' : 'bg-light' }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge badge-{{ ($source['type'] ?? 'static') === 'static' ? 'secondary' : (($source['type'] ?? '') === 'variable' ? 'info' : 'warning') }} text-uppercase">
                                                            {{ $source['type'] ?? 'static' }}
                                                        </span>
                                                        <div>
                                                            <button class="btn btn-xs btn-link text-muted" wire:click="moveDataSource({{ $index }}, 'up')" @if($index === 0) disabled @endif><i class="fas fa-arrow-up"></i></button>
                                                            <button class="btn btn-xs btn-link text-muted" wire:click="moveDataSource({{ $index }}, 'down')" @if($index === count($fieldData['data_sources']) - 1) disabled @endif><i class="fas fa-arrow-down"></i></button>
                                                            <button class="btn btn-xs btn-outline-danger ml-2" wire:click="removeDataSource({{ $index }})"><i class="fas fa-trash"></i></button>
                                                        </div>
                                                    </div>

                                                    {{-- Static Config --}}
                                                    @if(($source['type'] ?? 'static') === 'static')
                                                        @if(in_array($fieldData['type'], ['ul', 'ol']))
                                                            <div class="form-group mb-0">
                                                                <label class="small text-muted mb-1">Item Content</label>
                                                                <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.label" class="form-control form-control-sm" placeholder="List item text">
                                                            </div>
                                                        @else
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label class="small text-muted mb-1">Label</label>
                                                                    <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.label" class="form-control form-control-sm" placeholder="Displayed Label">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="small text-muted mb-1">Value</label>
                                                                    <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.value" class="form-control form-control-sm" placeholder="Submission Value">
                                                                </div>
                                                            </div>
                                                        @endif

                                                    {{-- Variable Config --}}
                                                    @elseif(($source['type'] ?? '') === 'variable')
                                                        <div class="form-group mb-2">
                                                            <label class="small text-muted mb-1">Select Variable</label>
                                                            <select wire:model.live="fieldData.data_sources.{{ $index }}.config.variable_id" class="form-control form-control-sm">
                                                                <option value="">-- Choose Variable --</option>
                                                                @foreach($variables as $var)
                                                                    <option value="{{ $var->id }}">{{ $var->name }} ({{ $var->type }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        
                                                        @if(!empty($source['config']['variable_id']))
                                                            @php
                                                                $selVar = collect($variables)->firstWhere('id', $source['config']['variable_id']);
                                                                $isCol = ($selVar && $selVar->data_type === 'collection');
                                                            @endphp
                                                            
                                                            @if($isCol)
                                                                <div class="alert alert-info py-1 px-2 mb-2" style="font-size: 0.75rem;">
                                                                    <i class="fas fa-sync mr-1"></i> Repeater: Adds an item for each result.
                                                                </div>
                                                                @if(in_array($fieldData['type'], ['ul', 'ol']))
                                                                     <div class="form-group mb-0">
                                                                        <label class="small text-muted mb-1">Item Template</label>
                                                                        <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.template" class="form-control form-control-sm" placeholder="@{{ name }} - @{{ email }}">
                                                                        <small class="text-muted d-block mt-1">Available columns depend on variable query.</small>
                                                                    </div>
                                                                @else
                                                                    <div class="form-group mb-0">
                                                                        <div class="row">
                                                                            <div class="col-6">
                                                                                <label class="small text-muted mb-1">Label Column</label>
                                                                                <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.template" class="form-control form-control-sm" placeholder="Column (e.g. name)">
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <label class="small text-muted mb-1">Value Column</label>
                                                                                <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.value_col" class="form-control form-control-sm" placeholder="Column (e.g. id)">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <div class="alert alert-secondary py-1 px-2 mb-2" style="font-size: 0.75rem;">
                                                                    <i class="fas fa-tag mr-1"></i> Single: Adds one item.
                                                                </div>
                                                                <label class="small text-muted mb-1">Property / Column</label>
                                                                @php
                                                                    $expandedColumns = [];
                                                                    if ($selVar && $selVar->type === 'database') {
                                                                        // Use the helper method we added to get expanded columns
                                                                        $expandedColumns = $this->getExpandedVariableColumns($selVar->id);
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="row">
                                                                    <div class="col-12 mb-2">
                                                                        <label class="small text-muted mb-1">Label (Prefix)</label>
                                                                        <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.label_prefix" class="form-control form-control-sm" placeholder="e.g. Report No :">
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="small text-muted mb-1">Property / Column</label>
                                                                        @if(count($expandedColumns) > 0)
                                                                            <select wire:model="fieldData.data_sources.{{ $index }}.config.column" class="form-control form-control-sm">
                                                                                <option value="">-- Select Column --</option>
                                                                                @foreach($expandedColumns as $col)
                                                                                    <option value="{{ \Illuminate\Support\Str::afterLast($col, '.') }}">{{ $col }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            <input type="text" wire:model="fieldData.data_sources.{{ $index }}.config.column" class="form-control form-control-sm" placeholder="Column name">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif

                                                    {{-- Dynamic Config --}}
                                                    @elseif(($source['type'] ?? '') === 'dynamic')
                                                        <div class="mb-2">
                                                            <button class="btn btn-sm btn-outline-primary btn-block" 
                                                                    wire:click="$set('activeBindingTarget', 'data_source.{{ $index }}')"
                                                                    data-toggle="collapse" data-target="#ds-{{ $index }}">
                                                                <i class="fas fa-cog mr-1"></i> Configure Dataset
                                                            </button>
                                                            <div id="ds-{{ $index }}" class="collapse mt-2 border p-2 rounded" wire:ignore.self>
                                                                @livewire('template-engine::dataset-selector', ['binding' => $source['config']['dataset_binding'] ?? []], key: 'ds-'.$index.'-'.time())
                                                            </div>
                                                        </div>
                                                        @if(isset($source['config']['dataset_binding']['table']))
                                                            <small class="text-success"><i class="fas fa-check-circle mr-1"></i> Bound to: <strong>{{ $source['config']['dataset_binding']['table'] }}</strong></small>
                                                        @else
                                                            <small class="text-muted"><i class="fas fa-exclamation-circle mr-1"></i> No dataset configured.</small>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4 text-muted border border-dashed rounded mb-3 bg-white">
                                            <i class="fas fa-layer-group fa-2x mb-2 text-gray-300"></i>
                                            <p class="mb-0 small">No data sources defined.</p>
                                        </div>
                                    @endif

                                    <div class="btn-group w-100">
                                        <button class="btn btn-sm btn-outline-secondary" wire:click="addDataSource('static')">
                                            <i class="fas fa-plus mr-1"></i> Add Static
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" wire:click="addDataSource('variable')">
                                            <i class="fas fa-cube mr-1"></i> Add Variable
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" wire:click="addDataSource('dynamic')">
                                            <i class="fas fa-database mr-1"></i> Add Dynamic
                                        </button>
                                    </div>
                                </div>

                                {{-- List Specific Extras --}}
                                @if(in_array($fieldData['type'], ['ul', 'ol']))
                                    <hr>
                                    <div class="form-group">
                                        <label class="font-weight-bold small">List Style Type</label>
                                        <select wire:model="fieldData.meta.css.list_style_type" class="form-control">
                                            <option value="">Default</option>
                                            <option value="none">None</option>
                                            @if($fieldData['type'] === 'ul')
                                                <option value="disc">Disc</option>
                                                <option value="circle">Circle</option>
                                                <option value="square">Square</option>
                                            @else
                                                <option value="decimal">Decimal (1, 2, 3)</option>
                                                <option value="decimal-leading-zero">Decimal Leading Zero (01, 02)</option>
                                                <option value="lower-alpha">Lower Alpha (a, b, c)</option>
                                                <option value="upper-alpha">Upper Alpha (A, B, C)</option>
                                                <option value="lower-roman">Lower Roman (i, ii, iii)</option>
                                                <option value="upper-roman">Upper Roman (I, II, III)</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold small">CSS Class</label>
                                        <input type="text" wire:model="fieldData.meta.css.custom_css" class="form-control" placeholder="e.g. section">
                                    </div>
                                @endif
                             </div>
                        @endif
                        
                        <!-- Container Configuration -->
                        @if($fieldData['type'] === 'container')
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fas fa-layer-group mr-2"></i>Container Configuration
                                </h6>
                                
                                <!-- Column Configuration -->
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small">Columns per Row</label>
                                    <select wire:model="fieldData.meta.columns" class="form-control">
                                        <option value="1">1 Column (Full Width)</option>
                                        <option value="2">2 Columns</option>
                                        <option value="3">3 Columns</option>
                                        <option value="4">4 Columns</option>
                                        <option value="6">6 Columns</option>
                                        <option value="12">12 Columns (Minimum Width)</option>
                                    </select>
                                    <small class="text-muted">This will create {{ $fieldData['meta']['columns'] ?? 1 }} column(s) horizontally</small>
                                </div>

                                <!-- CSS Configuration Tabs -->
                                <div class="mt-4">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#spacing-tab" role="tab">
                                                <i class="fas fa-arrows-alt mr-1"></i> Spacing
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#colors-tab" role="tab">
                                                <i class="fas fa-palette mr-1"></i> Colors
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#border-tab" role="tab">
                                                <i class="fas fa-square mr-1"></i> Border
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#typography-tab" role="tab">
                                                <i class="fas fa-font mr-1"></i> Typography
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#layout-tab" role="tab">
                                                <i class="fas fa-expand-arrows-alt mr-1"></i> Layout
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#effects-tab" role="tab">
                                                <i class="fas fa-magic mr-1"></i> Effects
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#advanced-tab" role="tab">
                                                <i class="fas fa-code mr-1"></i> Advanced
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content mt-3">
                                        <!-- Spacing Tab -->
                                        <div class="tab-pane fade show active" id="spacing-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="small font-weight-bold mb-2">Margin</h6>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Top</label>
                                                        <input type="text" wire:model="fieldData.meta.css.margin_top" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Right</label>
                                                        <input type="text" wire:model="fieldData.meta.css.margin_right" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Bottom</label>
                                                        <input type="text" wire:model="fieldData.meta.css.margin_bottom" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small">Left</label>
                                                        <input type="text" wire:model="fieldData.meta.css.margin_left" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="small font-weight-bold mb-2">Padding</h6>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Top</label>
                                                        <input type="text" wire:model="fieldData.meta.css.padding_top" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Right</label>
                                                        <input type="text" wire:model="fieldData.meta.css.padding_right" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small">Bottom</label>
                                                        <input type="text" wire:model="fieldData.meta.css.padding_bottom" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small">Left</label>
                                                        <input type="text" wire:model="fieldData.meta.css.padding_left" class="form-control form-control-sm" placeholder="e.g., 10px">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Colors Tab -->
                                        <div class="tab-pane fade" id="colors-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Background Color</label>
                                                        <div class="input-group">
                                                            <input type="color" wire:model="fieldData.meta.css.background_color" class="form-control form-control-sm" style="max-width: 60px;">
                                                            <input type="text" wire:model="fieldData.meta.css.background_color" class="form-control form-control-sm" placeholder="#ffffff or rgb(255,255,255)">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Text Color</label>
                                                        <div class="input-group">
                                                            <input type="color" wire:model="fieldData.meta.css.text_color" class="form-control form-control-sm" style="max-width: 60px;">
                                                            <input type="text" wire:model="fieldData.meta.css.text_color" class="form-control form-control-sm" placeholder="#000000 or rgb(0,0,0)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Border Tab -->
                                        <div class="tab-pane fade" id="border-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Width</label>
                                                        <input type="text" wire:model="fieldData.meta.css.border_width" class="form-control form-control-sm" placeholder="e.g., 1px">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Style</label>
                                                        <select wire:model="fieldData.meta.css.border_style" class="form-control form-control-sm">
                                                            <option value="">None</option>
                                                            <option value="solid">Solid</option>
                                                            <option value="dashed">Dashed</option>
                                                            <option value="dotted">Dotted</option>
                                                            <option value="double">Double</option>
                                                            <option value="groove">Groove</option>
                                                            <option value="ridge">Ridge</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Color</label>
                                                        <div class="input-group">
                                                            <input type="color" wire:model="fieldData.meta.css.border_color" class="form-control form-control-sm" style="max-width: 60px;">
                                                            <input type="text" wire:model="fieldData.meta.css.border_color" class="form-control form-control-sm" placeholder="#000000">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Radius</label>
                                                        <input type="text" wire:model="fieldData.meta.css.border_radius" class="form-control form-control-sm" placeholder="e.g., 5px">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Typography Tab -->
                                        <div class="tab-pane fade" id="typography-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Font Family</label>
                                                        <input type="text" wire:model="fieldData.meta.css.font_family" class="form-control form-control-sm" placeholder="e.g., Arial, sans-serif">
                                                        <small class="text-muted">Common: Arial, Helvetica, Times New Roman, Georgia, Courier New</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Font Size</label>
                                                        <input type="text" wire:model="fieldData.meta.css.font_size" class="form-control form-control-sm" placeholder="e.g., 14px, 1em">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Font Weight</label>
                                                        <select wire:model="fieldData.meta.css.font_weight" class="form-control form-control-sm">
                                                            <option value="">Default</option>
                                                            <option value="normal">Normal</option>
                                                            <option value="bold">Bold</option>
                                                            <option value="100">100</option>
                                                            <option value="200">200</option>
                                                            <option value="300">300</option>
                                                            <option value="400">400</option>
                                                            <option value="500">500</option>
                                                            <option value="600">600</option>
                                                            <option value="700">700</option>
                                                            <option value="800">800</option>
                                                            <option value="900">900</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Layout Tab -->
                                        <div class="tab-pane fade" id="layout-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Width</label>
                                                        <input type="text" wire:model="fieldData.meta.css.width" class="form-control form-control-sm" placeholder="e.g., 100%, 500px">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Height</label>
                                                        <input type="text" wire:model="fieldData.meta.css.height" class="form-control form-control-sm" placeholder="e.g., auto, 200px">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Display</label>
                                                        <select wire:model="fieldData.meta.css.display" class="form-control form-control-sm">
                                                            <option value="">Default</option>
                                                            <option value="block">Block</option>
                                                            <option value="inline">Inline</option>
                                                            <option value="inline-block">Inline Block</option>
                                                            <option value="flex">Flex</option>
                                                            <option value="grid">Grid</option>
                                                            <option value="table">Table</option>
                                                            <option value="none">None</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Effects Tab -->
                                        <div class="tab-pane fade" id="effects-tab" role="tabpanel">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Box Shadow</label>
                                                <input type="text" wire:model="fieldData.meta.css.box_shadow" class="form-control form-control-sm" placeholder="e.g., 0 2px 4px rgba(0,0,0,0.1)">
                                                <small class="text-muted">Format: offset-x offset-y blur-radius color</small>
                                            </div>
                                        </div>

                                        <!-- Advanced Tab -->
                                        <div class="tab-pane fade" id="advanced-tab" role="tabpanel">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Custom CSS</label>
                                                <textarea wire:model="fieldData.meta.css.custom_css" class="form-control" rows="6" placeholder="Enter custom CSS properties (e.g., opacity: 0.8; transform: scale(1.1);)"></textarea>
                                                <small class="text-muted">Only whitelisted CSS properties are allowed for security. Dangerous patterns will be removed.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($fieldData['parent_field_id'])
                            <div class="alert alert-warning small">
                                <i class="fas fa-level-up-alt mr-1"></i> Adding as child of existing container.
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer bg-light flex-shrink-0" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showFieldModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" wire:click="saveField">
                            <i class="fas fa-save mr-1"></i> Save Field
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Variable Modal -->
    @if($showVariableModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5); z-index: 1060; overflow-y: auto;" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered" style="margin: 1.75rem auto;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold">
                            @if($variableData['id']) <i class="fas fa-edit text-primary mr-2"></i> Edit Variable @else <i class="fas fa-plus text-success mr-2"></i> New Variable @endif
                        </h5>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-xs btn-info mr-3 rounded-pill px-3" type="button" data-toggle="collapse" data-target="#varHelpCollapse">
                                <i class="fas fa-question-circle mr-1"></i> Help
                            </button>
                            <button type="button" class="close" wire:click="$set('showVariableModal', false)">
                                <span>&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body p-4">
                        
                        <!-- Stepper Header -->
                        <div class="stepper-wrapper d-flex justify-content-between mb-4 position-relative px-2">
                            <div class="position-absolute w-100 bg-light border-bottom" style="top: 16px; left: 0; z-index: 0;"></div>
                            
                            <!-- Step 1: Basics -->
                            <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                               <div class="step-circle {{ $activeVariableStep >= 1 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                    <i class="fas fa-info small"></i>
                               </div>
                               <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 1 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Basics</small>
                            </div>

                            @if($variableData['type'] === 'database')
                                <!-- DB Step 2: Source -->
                                <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                                   <div class="step-circle {{ $activeVariableStep >= 2 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                        <i class="fas fa-database small"></i>
                                   </div>
                                   <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 2 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Source</small>
                                </div>
                                <!-- DB Step 3: Filter -->
                                <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                                   <div class="step-circle {{ $activeVariableStep >= 3 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                        <i class="fas fa-filter small"></i>
                                   </div>
                                   <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 3 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Filter</small>
                                </div>
                                <!-- DB Step 4: Refine -->
                                <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                                   <div class="step-circle {{ $activeVariableStep >= 4 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                        <i class="fas fa-cogs small"></i>
                                   </div>
                                   <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 4 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Refine</small>
                                </div>
                                <!-- DB Step 5: Review -->
                                <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                                   <div class="step-circle {{ $activeVariableStep >= 5 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                        <i class="fas fa-check small"></i>
                                   </div>
                                   <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 5 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Review</small>
                                </div>
                            @else
                                <!-- Static/System Step 2: Config -->
                                <div class="step-item text-center position-relative bg-white px-2" style="z-index: 1;">
                                   <div class="step-circle {{ $activeVariableStep >= 2 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" style="width: 34px; height: 34px; line-height: 34px; border-radius: 50%; margin: 0 auto; transition: all 0.3s;">
                                        <i class="fas fa-cog small"></i>
                                   </div>
                                   <small class="font-weight-bold d-block mt-1 {{ $activeVariableStep >= 2 ? 'text-primary' : 'text-muted' }}" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Config</small>
                                </div>
                            @endif
                        </div>

                        <!-- Step 1 Content: Basics -->
                        @if($activeVariableStep === 1)
                            <div class="step-content">
                                <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Variable Definition</h6>
                                <div class="form-group">
                                    <label class="font-weight-bold small">Variable Name</label>
                                    <input type="text" wire:model="variableData.name" class="form-control" placeholder="e.g. client_name">
                                    <small class="text-muted">Use alphanumeric characters and underscores. Reference as <code>@{{ name }}</code></small>
                                    @error('variableData.name') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Type</label>
                                            <select wire:model.live="variableData.type" class="form-control" {{ $variableData['id'] ? 'disabled' : '' }}>
                                                <option value="static">Static Value</option>
                                                <option value="database">Database Query</option>
                                                <option value="system">System / Context</option>
                                            </select>
                                            @error('variableData.type') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Data Type</label>
                                            <select wire:model="variableData.data_type" class="form-control">
                                                <option value="string">String (Text)</option>
                                                <option value="number">Number</option>
                                                <option value="boolean">Boolean</option>
                                                <option value="date">Date</option>
                                                <option value="collection">Collection (List)</option>
                                                <option value="record">Single Record</option>
                                            </select>
                                            @error('variableData.data_type') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($activeVariableStep === 2)
                            <!-- Step 2 Content: Config or DB Source -->
                            <div class="step-content">
                                @if($variableData['type'] === 'database')
                                    <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Database Source</h6>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Database Table</label>
                                        <select wire:model.live="variableData.config.table" class="form-control">
                                            <option value="">Select Table...</option>
                                            @foreach($dbTables as $table)
                                                <option value="{{ $table }}">{{ $table }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Joins Section -->
                                    <div class="card mb-4 border shadow-sm">
                                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-project-diagram mr-2"></i>Joins</h6>
                                            <button class="btn btn-xs btn-outline-primary" wire:click="addJoin">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div class="card-body bg-light p-3">
                                            @if(empty($variableData['config']['joins']))
                                                <div class="text-center text-muted small py-2">No joins defined.</div>
                                            @else
                                                @foreach($variableData['config']['joins'] as $index => $join)
                                                    <div class="card mb-2 p-2 border-white shadow-sm">
                                                        <div class="form-row align-items-end">
                                                            <div class="col-md-2">
                                                                <label class="small text-muted mb-1 font-weight-bold">Type</label>
                                                                <select wire:model="variableData.config.joins.{{ $index }}.type" class="form-control form-control-sm bg-light">
                                                                    <option value="inner">Inner</option>
                                                                    <option value="left">Left</option>
                                                                    <option value="right">Right</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="small text-muted mb-1 font-weight-bold">Table</label>
                                                                <select wire:model.live="variableData.config.joins.{{ $index }}.table" class="form-control form-control-sm bg-light">
                                                                    <option value="">Select...</option>
                                                                    @foreach($dbTables as $table)
                                                                        <option value="{{ $table }}">{{ $table }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="small text-muted mb-1 font-weight-bold">On (First)</label>
                                                                <select wire:model="variableData.config.joins.{{ $index }}.on_first" class="form-control form-control-sm bg-light">
                                                                    <option value="">Select Column...</option>
                                                                    @foreach($this->getColumnsForJoinFirst($index) as $col)
                                                                        <option value="{{ $col }}">{{ $col }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="small text-muted mb-1 font-weight-bold">Op</label>
                                                                <select wire:model="variableData.config.joins.{{ $index }}.operator" class="form-control form-control-sm bg-light">
                                                                    <option value="=">=</option>
                                                                    <option value=">">></option>
                                                                    <option value="<"><</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="small text-muted mb-1 font-weight-bold">On (Second)</label>
                                                                 <div class="input-group input-group-sm">
                                                                    <select wire:model="variableData.config.joins.{{ $index }}.on_second" class="form-control bg-light">
                                                                        <option value="">Select Column...</option>
                                                                        @foreach($this->getColumnsForJoinSecond($index) as $col)
                                                                            <option value="{{ $col }}">{{ $col }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-outline-danger" wire:click="removeJoin({{ $index }})">&times;</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Injections Section -->
                                    <div class="card mb-3 outline-dashed">
                                        <div class="card-header bg-warning-subtle py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-dark small font-weight-bold"><i class="fas fa-syringe mr-1"></i> Injections</h6>
                                            <button class="btn btn-xs btn-outline-dark" wire:click="addInjection">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div class="card-body p-2">
                                            @if(isset($variableData['config']['injections']) && count($variableData['config']['injections']) > 0)
                                                @foreach($variableData['config']['injections'] as $index => $injection)
                                                    <div class="row align-items-center mb-2 bg-white p-2 border rounded mx-0">
                                                        <div class="col-2 pl-1 pr-1">
                                                            <small class="text-muted d-block">Label</small>
                                                            <input type="text" wire:model.live="variableData.config.injections.{{ $index }}.label" class="form-control form-control-sm" placeholder="param_name">
                                                        </div>
                                                        <div class="col-3 pl-1 pr-1">
                                                            <small class="text-muted d-block">Table</small>
                                                            <select wire:model.live="variableData.config.injections.{{ $index }}.table" class="form-control form-control-sm">
                                                                <option value="">Select...</option>
                                                                @foreach($dbTables as $t)
                                                                    <option value="{{ $t }}">{{ $t }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3 pl-1 pr-1">
                                                            <small class="text-muted d-block">Label Column</small>
                                                            <select wire:model="variableData.config.injections.{{ $index }}.label_column" class="form-control form-control-sm">
                                                                <option value="">Select...</option>
                                                                @if(isset($injectionColumns[$index]))
                                                                    @foreach($injectionColumns[$index] as $col)
                                                                        <option value="{{ $col }}">{{ $col }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-3 pl-1 pr-1">
                                                            <small class="text-muted d-block">Value Column</small>
                                                            <select wire:model="variableData.config.injections.{{ $index }}.column" class="form-control form-control-sm">
                                                                <option value="">Select...</option>
                                                                @if(isset($injectionColumns[$index]))
                                                                    @foreach($injectionColumns[$index] as $col)
                                                                        <option value="{{ $col }}">{{ $col }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-1 text-right">
                                                             <button class="btn btn-sm btn-link text-danger" wire:click="removeInjection({{ $index }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-2 text-muted small">No injections defined.</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($variableData['type'] === 'static')
                                    <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Static Configuration</h6>
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Value</label>
                                        <input type="text" wire:model="variableData.config.value" class="form-control" placeholder="Enter static value">
                                    </div>
                                @elseif($variableData['type'] === 'system')
                                    <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">System Configuration</h6>
                                    <div class="form-group">
                                        <label class="font-weight-bold small">System Source</label>
                                        <select wire:model="variableData.config.source" class="form-control">
                                            <option value="">Select source...</option>
                                            <option value="auth_user">Logged In User (Object)</option>
                                            <option value="auth_user_name">Logged In User Name (String)</option>
                                            <option value="current_date">Current Date (YYYY-MM-DD)</option>
                                            <option value="current_datetime">Current Date & Time</option>
                                        </select>
                                    </div>
                                @endif
                            </div>
                        @elseif($activeVariableStep === 3)
                            <!-- Step 3: DB Filters -->
                            <div class="step-content">
                                <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Filters (Where Clause)</h6>
                                <div class="card mb-3 outline-dashed">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-primary small font-weight-bold"><i class="fas fa-filter mr-1"></i> Conditions</h6>
                                        <button class="btn btn-xs btn-outline-primary" wire:click="addFilter">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        @if(isset($variableData['config']['filters']) && is_array($variableData['config']['filters']) && count($variableData['config']['filters']) > 0)
                                            @foreach($variableData['config']['filters'] as $index => $filter)
                                                <div class="row align-items-center mb-2 bg-white p-2 border rounded mx-0">
                                                    <div class="col-4 pl-1 pr-1">
                                                        <select wire:model="variableData.config.filters.{{ $index }}.field" class="form-control form-control-sm">
                                                            <option value="">Field...</option>
                                                            @foreach($availableColumns as $col)
                                                                <option value="{{ $col }}">{{ $col }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-2 pl-1 pr-1">
                                                        <select wire:model="variableData.config.filters.{{ $index }}.operator" class="form-control form-control-sm">
                                                            <option value="=">=</option>
                                                            <option value=">">></option>
                                                            <option value="<"><</option>
                                                            <option value=">=">>=</option>
                                                            <option value="<="><=</option>
                                                            <option value="<>"><></option>
                                                            <option value="LIKE">LIKE</option>
                                                            <option value="IN">IN</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-2 pl-1 pr-1">
                                                         <select wire:model.live="variableData.config.filters.{{ $index }}.value_source" class="form-control form-control-sm">
                                                            <option value="static">Static</option>
                                                            <option value="injection">Injection</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-3 pl-1 pr-1">
                                                        @if(isset($filter['value_source']) && $filter['value_source'] === 'injection')
                                                            <select wire:model="variableData.config.filters.{{ $index }}.value" class="form-control form-control-sm">
                                                                <option value="">Select...</option>
                                                                @if(isset($variableData['config']['injections']))
                                                                    @foreach($variableData['config']['injections'] as $inj)
                                                                        <option value="{{ $inj['label'] ?? '' }}">{{ $inj['label'] ?? 'Unnamed' }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        @else
                                                            <input type="text" wire:model="variableData.config.filters.{{ $index }}.value" class="form-control form-control-sm" placeholder="Value">
                                                        @endif
                                                    </div>
                                                    <div class="col-1 text-right">
                                                        <button class="btn btn-sm btn-link text-danger" wire:click="removeFilter({{ $index }})">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-3 text-muted small">No filters defined.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($activeVariableStep === 4)
                            <!-- Step 4: DB Refine -->
                            <div class="step-content">
                                <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Refine Query</h6>
                                
                                <!-- Select Columns -->
                                <div class="mb-4 card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 text-primary small font-weight-bold"><i class="fas fa-columns mr-1"></i> Select Columns</h6>
                                        <button class="btn btn-xs btn-outline-primary" wire:click="addSelectField">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="card-body p-3">
                                        @if(!empty($variableData['config']['select']))
                                            <div class="row">
                                                @foreach($variableData['config']['select'] as $index => $col)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="input-group input-group-sm">
                                                             <select wire:model="variableData.config.select.{{ $index }}" class="form-control form-control-sm bg-light border-0">
                                                                <option value="">Select Column...</option>
                                                                @foreach($this->getSelectOptions() as $c)
                                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-link text-danger pr-0" wire:click="removeSelectField({{ $index }})">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-4 bg-light rounded text-muted small">
                                                <i class="fas fa-asterisk mb-2 d-block"></i>
                                                All columns selected by default (*)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Group By -->
                                <div class="mb-4 card border-0 shadow-sm">
                                     <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 text-primary small font-weight-bold"><i class="fas fa-layer-group mr-1"></i> Group By</h6>
                                        <button class="btn btn-xs btn-outline-primary" wire:click="addGroupBy">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="card-body p-3">
                                        @if(!empty($variableData['config']['group_bys']))
                                           <div class="row">
                                                @foreach($variableData['config']['group_bys'] as $index => $grp)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="input-group input-group-sm">
                                                             <select wire:model="variableData.config.group_bys.{{ $index }}" class="form-control form-control-sm bg-light border-0">
                                                                <option value="">Select Field...</option>
                                                                @foreach($availableColumns as $c)
                                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-link text-danger pr-0" wire:click="removeGroupBy({{ $index }})">
                                                                     <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                             <div class="text-center py-3 bg-light rounded text-muted small">
                                                No grouping applied.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Order By -->
                                <div class="mb-4 card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 text-primary small font-weight-bold"><i class="fas fa-sort mr-1"></i> Order By</h6>
                                        <button class="btn btn-xs btn-outline-primary" wire:click="addOrderBy">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>

                                    <div class="card-body p-3">
                                         @if(!empty($variableData['config']['order_bys']))
                                            @foreach($variableData['config']['order_bys'] as $index => $sort)
                                                <div class="d-flex mb-2 align-items-center bg-light p-2 rounded">
                                                     <div class="flex-grow-1 mr-2">
                                                         <select wire:model="variableData.config.order_bys.{{ $index }}.field" class="form-control form-control-sm border-0">
                                                            <option value="">Field...</option>
                                                            @foreach($availableColumns as $c)
                                                                <option value="{{ $c }}">{{ $c }}</option>
                                                            @endforeach
                                                        </select>
                                                     </div>
                                                     <div class="mr-2" style="width: 100px;">
                                                        <select wire:model="variableData.config.order_bys.{{ $index }}.direction" class="form-control form-control-sm border-0">
                                                            <option value="asc">ASC</option>
                                                            <option value="desc">DESC</option>
                                                        </select>
                                                     </div>
                                                    <button class="btn btn-sm btn-link text-danger p-0" wire:click="removeOrderBy({{ $index }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-3 bg-light rounded text-muted small">
                                                Default order.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($activeVariableStep === 5)
                             <!-- Step 5: Review & Test -->
                             <div class="step-content">
                                <h6 class="font-weight-bold text-dark mb-4 pl-2 border-left border-primary" style="border-width: 4px !important;">Limit & Preview</h6>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="font-weight-bold small">Limit</label>
                                        <input type="number" wire:model="variableData.config.limit" class="form-control">
                                    </div>
                                    <div class="col-6">
                                         <label class="font-weight-bold small">Return Type</label>
                                         <select wire:model="variableData.config.return_type" class="form-control">
                                             <option value="collection">Collection (Multiple)</option>
                                             <option value="single">Single Record</option>
                                         </select>
                                    </div>
                                </div>
                                <hr>
                                
                                <button class="btn btn-sm btn-info btn-block shadow-sm mb-3" wire:click="prepareTestQuery" wire:loading.attr="disabled">
                                    <i class="fas fa-play mr-1"></i> Run Test Query
                                </button>
                                
                                <div wire:loading wire:target="prepareTestQuery,testQuery" class="text-center mt-2 small text-muted"> <div class="spinner-border spinner-border-sm mr-1" role="status"></div> Running... </div>
                                
                                @if($queryError)
                                    <div class="alert alert-danger small mt-2 mb-0 border-left-danger shadow-sm">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $queryError }}
                                    </div>
                                @endif
                                
                                @if($queryPreview !== null && is_array($queryPreview))
                                    <div class="mt-3">
                                        <label class="small font-weight-bold text-success">Query Results (Preview)</label>
                                        <div class="table-responsive bg-white border rounded shadow-sm" style="max-height: 250px; overflow: auto;">
                                            <table class="table table-sm table-striped table-bordered mb-0 small" style="font-size: 11px;">
                                                @if(count($queryPreview) > 0)
                                                    <thead>
                                                        <tr>
                                                            @foreach(array_keys($queryPreview[0]) as $header)
                                                                <th>{{ $header }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($queryPreview as $row)
                                                            <tr>
                                                                @foreach($row as $cell)
                                                                    <td>
                                                                        @if(is_array($cell) || is_object($cell))
                                                                            {{ json_encode($cell) }}
                                                                        @else
                                                                            {{ \Illuminate\Support\Str::limit($cell, 20) }}
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                @else
                                                    <tbody><tr><td class="text-center text-muted">Empty result set</td></tr></tbody>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                @endif
                             </div>
                        @endif

                    </div>
                    <div class="modal-footer bg-light flex-shrink-0" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVariableModal', false)">Cancel</button>
                        
                        @if($activeVariableStep > 1)
                            <button type="button" class="btn btn-outline-secondary" wire:click="prevVariableStep">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </button>
                        @endif

                        @php
                            $maxStep = ($variableData['type'] === 'database') ? 5 : 2;
                        @endphp

                        @if($activeVariableStep < $maxStep)
                            <button type="button" class="btn btn-primary" wire:click="nextVariableStep">
                                Next <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-success px-4" wire:click="saveVariable">
                                <i class="fas fa-save mr-1"></i> Save Variable
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Test Injections Modal -->
    @if($showTestInjectionModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-primary text-white py-2">
                        <h6 class="modal-title font-weight-bold small"><i class="fas fa-sliders-h mr-1"></i> Test Parameters</h6>
                    </div>
                    <div class="modal-body bg-light p-3">
                        <p class="small text-muted mb-3">Provide values for parameterized filters to run the test query.</p>
                        
                        @if(isset($variableData['config']['injections']))
                             @foreach($variableData['config']['injections'] as $inj)
                                @if(!empty($inj['label']))
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">{{ $inj['label'] }}</label>
                                        @if(isset($testInjectionOptions[$inj['label']]) && count($testInjectionOptions[$inj['label']]) > 0)
                                            <select wire:model="testInjectionValues.{{ $inj['label'] }}" class="form-control form-control-sm">
                                                <option value="">Select {{ $inj['label'] }}...</option>
                                                @foreach($testInjectionOptions[$inj['label']] as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                             <input type="text" wire:model="testInjectionValues.{{ $inj['label'] }}" class="form-control form-control-sm" placeholder="Enter value...">
                                        @endif
                                    </div>
                                @endif
                             @endforeach
                        @endif
                    </div>
                    <div class="modal-footer py-1 bg-white">
                        <button type="button" class="btn btn-xs btn-secondary" wire:click="cancelTestInjection">Cancel</button>
                        <button type="button" class="btn btn-xs btn-primary" wire:click="runTestQueryWithInjections">Run Query</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            
            function initSortable() {
                try {
                    $(".sortable-list").sortable("destroy");
                } catch(e) {}

                $(".sortable-list").sortable({
                    handle: ".drag-handle",
                    connectWith: ".sortable-list",
                    placeholder: "ui-state-highlight",
                    tolerance: "pointer",
                    cursor: "move",
                    opacity: 0.8,
                    start: function(e, ui) {
                        ui.placeholder.height(ui.item.height());
                        ui.placeholder.addClass('mb-3 bg-light border border-dashed rounded');
                    },
                    stop: function(event, ui) {
                        let item = ui.item;
                        let newParent = item.closest('.sortable-list');
                        let parentId = newParent.data('parent-id');
                        
                        let orderedIds = [];
                        newParent.find('.field-wrapper').each(function() {
                            orderedIds.push($(this).data('id'));
                        });
                        
                        @this.updateFieldOrder(parentId, orderedIds);
                    }
                });
            }

            initSortable();

            Livewire.hook('message.processed', (message, component) => {
                initSortable();
            });
        });
    </script>
@endpush
