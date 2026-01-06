<div class="field-item {{ $field->type === 'container' ? 'element-holder-container' : '' }}" data-id="{{ $field->id }}">
    <!-- Actions Toolbar -->
    <div class="field-item-actions">
        {{-- Drag Handle --}}
        <button class="btn btn-xs text-muted drag-handle" style="cursor: grab;">
            <i class="fas fa-arrows-alt"></i>
        </button>
        
        @if(in_array($field->type, ['container', 'ul', 'ol']))
             <button class="btn btn-xs text-success" title="Add Child" wire:click="addField({{ $field->section_id }}, {{ $field->id }})">
                <i class="fas fa-plus"></i>
             </button>
        @endif
        <button class="btn btn-xs text-primary" title="Edit" wire:click="editField({{ $field->id }})">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-xs text-danger" title="Delete" wire:click="deleteField({{ $field->id }})" onclick="confirm('Are you sure you want to delete this field?') || event.stopImmediatePropagation()">
            <i class="fas fa-trash"></i>
        </button>
    </div>

    @if($field->type === 'container')
        @php
            $columns = $field->meta['columns'] ?? 1;
            $css = $field->meta['css'] ?? [];
            $styleService = app(\Modules\TemplateEngine\Services\ContainerStyleService::class);
            $inlineStyles = $styleService->buildInlineStyles($css);
            $columnClass = $styleService->getColumnClass($columns);
            $colWidth = 12 / $columns;
        @endphp
        <div style="min-height: 50px;{{ $inlineStyles ? $inlineStyles : '' }}" x-data="{ expanded: true }">
            <div class="holder-header" @click="expanded = !expanded" style="cursor: pointer;">
                <span class="holder-title">
                    <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                    <i class="fas fa-box-open mx-1 text-muted"></i> {{ $field->label }}
                </span>
                <div>
                    <span class="badge badge-info border mr-1">{{ $columns }} column(s)</span>
                <span class="badge badge-light border">{{ $field->children->count() }} items</span>
                    @if(!empty($inlineStyles))
                        <span class="badge badge-success border ml-1" title="Custom styles applied">
                            <i class="fas fa-paint-brush"></i> Styled
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="holder-content p-2" x-show="expanded" x-collapse>
                @if($field->children->count() > 0)
                   <div class="card-body p-3">
                       <div class="row sortable-list" data-parent-id="{{ $field->id }}" style="min-height: 50px;">
                           @foreach($field->children as $child)
                               <div class="col-md-{{ $colWidth }} col-12 mb-3 field-wrapper" data-id="{{ $child->id }}">
                                   @include('template-engine::livewire.partials.field-item', ['field' => $child])
                               </div>
                           @endforeach
                       </div>
                   </div>
                @else
                    <div class="text-center text-muted py-3 small" wire:click="addField({{ $field->section_id }}, {{ $field->id }})" style="cursor: pointer; border: 1px dashed #ddd; border-radius: 4px;">
                        <i class="fas fa-plus mr-1"></i> Add content here
                    </div>
                @endif
            </div>
            
            <div x-show="!expanded" class="text-center text-muted py-1 small italic" style="display: none; background: #f9fafb; border-radius: 0 0 4px 4px;">
                <span class="opacity-50">Content hidden ({{ $field->children->count() }} items)</span>
            </div>
        </div>
    @elseif(in_array($field->type, ['ul', 'ol']))
        @php
            $listType = $field->type === 'ul' ? 'ul' : 'ol';
            $listStyle = $field->meta['css']['list_style_type'] ?? '';
            $customClass = $field->meta['css']['custom_css'] ?? '';
            $dataSource = $field->meta['data_source'] ?? 'static';
        @endphp
        <div class="element-holder-container {{ $customClass }}" x-data="{ expanded: true }">
             <div class="holder-header" @click="expanded = !expanded" style="cursor: pointer;">
                <span class="holder-title">
                    <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                    <i class="fas fa-list-{{ $listType }} mx-1 text-muted"></i> {{ $field->label }}
                </span>
                <div>
                     @if($dataSource === 'dynamic')
                        <span class="badge badge-warning border mr-1">
                            <i class="fas fa-database"></i> {{ $field->datasetBinding->table_name ?? 'Unbound' }}
                        </span>
                    @endif
                    <span class="badge badge-light border">{{ $field->children->count() }} items</span>
                </div>
            </div>
            
            <div class="holder-content p-2" x-show="expanded" x-collapse>
                <{{ $listType }} style="{{ $listStyle ? 'list-style-type: ' . $listStyle : '' }}" class="pl-4 mb-0 sortable-list" data-parent-id="{{ $field->id }}" style="min-height: 20px;">
                    @if($field->children->count() > 0)
                        @foreach($field->children as $child)
                            <li class="mb-2 field-wrapper" data-id="{{ $child->id }}">
                                @include('template-engine::livewire.partials.field-item', ['field' => $child])
                            </li>
                        @endforeach
                    @else
                        <li class="text-muted small">
                            <span class="d-inline-block px-2 py-1" wire:click="addField({{ $field->section_id }}, {{ $field->id }})" style="cursor: pointer; border: 1px dashed #ddd; border-radius: 4px;">
                                <i class="fas fa-plus mr-1"></i> Add list item
                            </span>
                        </li>
                    @endif
                </{{ $listType }}>
            </div>
            
            <div x-show="!expanded" class="text-center text-muted py-1 small italic" style="display: none; background: #f9fafb; border-radius: 0 0 4px 4px;">
                <span class="opacity-50">List hidden ({{ $field->children->count() }} items)</span>
            </div>
        </div>
    @elseif(in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link']))
        <!-- Static Content Preview -->
        <div>
            @if($field->type === 'heading')
                <div class="p-2 border rounded bg-white">
                    <span class="badge badge-light mb-1">H{{ $field->meta['level'] ?? '1' }}</span>
                    <h5 class="mb-0 text-dark">{{ $field->label }}</h5>
                </div>
            @elseif($field->type === 'paragraph')
                <div class="p-2 border rounded bg-white text-muted">
                    {{ Str::limit($field->label, 100) }}
                </div>
            @elseif($field->type === 'link')
                <div class="p-2 border rounded bg-white">
                    <a href="#" class="text-primary"><i class="fas fa-link mr-1"></i> {{ $field->label }}</a>
                </div>
            @else
                 <div class="p-2 border rounded bg-white">
                    <span class="badge badge-secondary">{{ $field->type }}</span> {{ Str::limit($field->label, 50) }}
                </div>
            @endif
        </div>
    @elseif($field->type === 'image')
         <div class="p-2 border rounded bg-white text-center">
            <i class="fas fa-image text-muted fa-2x mb-2"></i>
            <div class="small">{{ $field->label }}</div>
         </div>
    @elseif($field->type === 'static_image_upload')
        <div class="p-2 border rounded bg-white text-center">
            @if(isset($field->meta['image_path']) && $field->meta['image_path'])
                <img src="{{ asset('storage/' . $field->meta['image_path']) }}" 
                     alt="{{ $field->meta['alt_text'] ?? $field->label }}" 
                     class="img-fluid mb-2"
                     style="max-width: 150px; max-height: 150px; 
                            width: {{ $field->meta['width'] ?? 'auto' }}; 
                            height: {{ $field->meta['height'] ?? 'auto' }};">
            @else
                <i class="fas fa-cloud-upload-alt text-info fa-2x mb-2"></i>
            @endif
            <div class="small">{{ $field->label }}</div>
            @if(isset($field->meta['width']) || isset($field->meta['height']))
                <div class="small text-muted">
                    {{ $field->meta['width'] ?? 'auto' }} x {{ $field->meta['height'] ?? 'auto' }}
                </div>
            @endif
        </div>
    @elseif($field->type === 'image_upload')
        <div>
            <label class="field-label small text-uppercase text-muted">
                {{ $field->label }}
                @if($field->required) <span class="text-danger">*</span> @endif
            </label>
            <div class="field-preview text-center py-3">
                <i class="fas fa-cloud-upload-alt text-info fa-2x mb-2"></i>
                <div class="small font-weight-bold">Image Upload Field</div>
                <div class="small text-muted mt-1">
                    <i class="fas fa-info-circle"></i>
                    Max: {{ $field->meta['max_size'] ?? 2 }}MB | 
                    Types: {{ implode(', ', array_map('strtoupper', $field->meta['allowed_types'] ?? ['jpg', 'png'])) }}
                </div>
                @if(!empty($field->meta['display_width']) || !empty($field->meta['display_height']))
                    <div class="small text-muted">
                        Display: {{ $field->meta['display_width'] ?? 'auto' }} x {{ $field->meta['display_height'] ?? 'auto' }}
                    </div>
                @endif
            </div>
        </div>
    
    @elseif($field->type === 'dynamic_table')
        <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="field-label small text-uppercase text-muted mb-0">
                    <i class="fas fa-table mr-1"></i> Dynamic Table
                </label>
                <span class="badge badge-light border">{{ count($field->meta['rows'] ?? []) }} Rows</span>
            </div>
            
            <div class="table-responsive bg-white border rounded p-2">
                <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                    <thead>
                        <tr class="bg-light">
                            @foreach($field->meta['headers'] ?? [] as $h)
                                <th>{{ $h['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($field->meta['rows'] ?? [] as $row)
                            <tr class="{{ ($row['type'] ?? '') === 'variable' ? 'bg-light' : '' }}">
                                @foreach($row['cells'] ?? [] as $i => $cell)
                                    @if($i < count($field->meta['headers'] ?? []))
                                        <td colspan="{{ $cell['colspan'] ?? 1 }}">
                                            @if(($row['type'] ?? '') === 'variable')
                                                <i class="fas fa-sync text-muted mr-1"></i>
                                            @endif
                                            {{ Str::limit($cell['content'] ?? '', 20) }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- Form Fields Preview -->
        <div>
            <label class="field-label small text-uppercase text-muted">
                {{ $field->label }}
                @if($field->required) <span class="text-danger">*</span> @endif
            </label>
            <div class="field-preview">
                @if($field->type === 'dynamic')
                    <i class="fas fa-database text-warning mr-2"></i> Bound to Dataset
                @elseif($field->type === 'select')
                     Select Option <i class="fas fa-chevron-down float-right small mt-1"></i>
                @elseif($field->type === 'date')
                     YYYY-MM-DD <i class="far fa-calendar float-right small mt-1"></i>
                @else
                     Placeholder input...
                @endif
            </div>
        </div>
    @endif
</div>
