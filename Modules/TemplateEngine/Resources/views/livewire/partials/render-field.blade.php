@php
    // Determine if this field is inside a container
    $isInsideContainer = isset($isChildOfContainer) && $isChildOfContainer;
@endphp
@if(!$isInsideContainer)
<div class="col-md-12 mb-3">
@endif
    @if($field->type === 'container')
        @php
            $columns = $field->meta['columns'] ?? 1;
            $css = $field->meta['css'] ?? [];
            $styleService = app(\Modules\TemplateEngine\Services\ContainerStyleService::class);
            $inlineStyles = $styleService->buildInlineStyles($css);
            $colWidth = 12 / $columns;
        @endphp
        <div class="card card-custom gutter-b" style="{{ $inlineStyles ? $inlineStyles : 'border: 1px dashed #e2e5ec; background-color: #f8f9fa;' }}">
           @if($field->label && $field->label !== 'Container')
               <div class="card-header p-3 min-h-auto">
                   <h6 class="card-title m-0">{{ $field->label }}</h6>
               </div>
           @endif
           <div class="card-body p-3">
               <div class="row">
                   @foreach($field->children as $child)
                       <div class="col-md-{{ $colWidth }} col-12 mb-3">
                           @include('template-engine::livewire.partials.render-field', ['field' => $child, 'isChildOfContainer' => true])
                       </div>
                   @endforeach
               </div>
           </div>
        </div>
    @elseif(in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link', 'image', 'static_image_upload']))
        {{-- Static Content --}}
        @if($field->type === 'heading')
            <{{ $field->meta['level'] ?? 'h2' }}>{{ $field->label }}</{{ $field->meta['level'] ?? 'h2' }}>
        @elseif($field->type === 'paragraph')
            <p>{{ nl2br(e($field->label)) }}</p>
        @elseif($field->type === 'blockquote')
            <blockquote class="blockquote border-left pl-3">
                <p class="mb-0">{{ $field->label }}</p>
            </blockquote>
        @elseif($field->type === 'code_block')
            <pre><code class="language-{{ $field->meta['language'] ?? 'text' }}">{{ $field->label }}</code></pre>
        @elseif($field->type === 'link')
            <a href="{{ $field->meta['url'] ?? '#' }}" target="{{ ($field->meta['new_tab'] ?? false) ? '_blank' : '_self' }}">{{ $field->label }}</a>
        @elseif($field->type === 'image')
            <img src="{{ $field->meta['url'] ?? '' }}" alt="{{ $field->label }}" class="img-fluid">
            @if($field->label && $field->label !== 'Image') <div class="text-muted small">{{ $field->label }}</div> @endif
        @elseif(in_array($field->type, ['ul', 'ol']))
             @php
                $listType = $field->type === 'ul' ? 'ul' : 'ol';
                $listStyle = $field->meta['css']['list_style_type'] ?? '';
                $customClass = $field->meta['css']['custom_css'] ?? '';
                $dataSource = $field->meta['data_source'] ?? 'static';
                
                // Prepare items to iterate
                $items = [];
                if ($dataSource === 'dynamic' && isset($dynamicOptions[$field->id])) {
                    $items = $dynamicOptions[$field->id];
                } elseif ($dataSource === 'static') {
                     // For static, we just loop once to render children effectively as a single "block" of items
                     // But wait, static UL/OL usually means the children ARE the LIs?
                     // In the builder, we add "children" to the UL. Each child is a field.
                     // The user said "create... li with static element".
                     // So each child field should be wrapped in <li>.
                     // If it's static, we iterate the children directly.
                     $items = [null]; // Dummy item to trigger the loop once? No, different logic.
                }
            @endphp
            
            <{{ $listType }} class="{{ $customClass }}" style="{{ $listStyle ? 'list-style-type: ' . $listStyle : '' }}">
                @if($dataSource === 'dynamic')
                    @foreach($items as $item)
                        {{-- Iterate children for each row item, creates multiple LIs per row --}}
                        @if($field->children->count() > 0)
                            @foreach($field->children as $child)
                                <li class="mb-1">
                                     @php
                                         // Full object substitution
                                         $originalLabel = $child->label;
                                         if ($item) {
                                             // Convert object to array for easy handling if needed, but object property access is fine
                                             // Naive substitution: search for {{ key }}
                                             // We can use regex to find all {{ keys }}
                                             $child->label = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($item) {
                                                 $key = $matches[1];
                                                 return $item->$key ?? $matches[0];
                                             }, $originalLabel);
                                         }
                                     @endphp
                                     @include('template-engine::livewire.partials.render-field', ['field' => $child, 'isChildOfContainer' => true])
                                     @php $child->label = $originalLabel; // Reset @endphp
                                </li>
                            @endforeach
                        @else
                            {{-- Fallback if no children defined --}}
                            <li class="mb-1">{{ $item->label ?? $item->name ?? $item->title ?? json_encode($item) }}</li>
                        @endif
                    @endforeach
                @else
                    {{-- Static List --}}
                    @foreach($field->children as $child)
                        <li class="mb-1">
                             @include('template-engine::livewire.partials.render-field', ['field' => $child, 'isChildOfContainer' => true])
                        </li>
                    @endforeach
                @endif
            </{{ $listType }}>

        @elseif($field->type === 'static_image_upload')
            @if(isset($field->meta['image_path']) && $field->meta['image_path'])
                <div class="text-{{ $field->meta['alignment'] ?? 'left' }}">
                    <img src="{{ asset('storage/' . $field->meta['image_path']) }}" 
                         alt="{{ $field->meta['alt_text'] ?? $field->label }}" 
                         class="img-fluid"
                         style="width: {{ $field->meta['width'] ?? 'auto' }}; 
                                height: {{ $field->meta['height'] ?? 'auto' }};">
                    @if($field->label && $field->label !== 'Image') 
                        <div class="text-muted small mt-1">{{ $field->label }}</div> 
                    @endif
                </div>
            @else
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle"></i> No image uploaded
                </div>
            @endif
        @endif
    @else
        {{-- Input Fields --}}
        @if(!$isInsideContainer)
        <label>{{ $field->label }} @if($field->required) <span class="text-danger">*</span> @endif</label>
        @else
            <label class="small">{{ $field->label }} @if($field->required) <span class="text-danger">*</span> @endif</label>
        @endif
        
        @if($field->type === 'text')
            <input type="text" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'textarea')
            <textarea class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}></textarea>
        @elseif($field->type === 'date')
            <input type="date" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'number')
            <input type="number" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'select')
            <select class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
                <option value="">Select...</option>
                @foreach($field->options as $opt)
                    <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                @endforeach
            </select>
        @elseif($field->type === 'radio')
            <div>
                @foreach($field->options as $opt)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="formData.{{ $field->name }}" value="{{ $opt->value }}">
                        <label class="form-check-label">{{ $opt->label }}</label>
                    </div>
                @endforeach
            </div>
        @elseif($field->type === 'checkbox')
            @if($field->options->count() > 0)
                <div>
                    @foreach($field->options as $opt)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="formData.{{ $field->name }}" value="{{ $opt->value }}">
                            <label class="form-check-label">{{ $opt->label }}</label>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" wire:model="formData.{{ $field->name }}" value="1">
                    <label class="form-check-label">{{ $field->label }}</label>
                </div>
            @endif
        @elseif($field->type === 'dynamic')
            <select class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
                <option value="">Select...</option>
                @if(isset($dynamicOptions[$field->id]))
                    @foreach($dynamicOptions[$field->id] as $opt)
                        <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                    @endforeach
                @endif
            </select>
        @elseif($field->type === 'image_upload')
            <div class="image-upload-field">
                <input type="file" 
                       wire:model="uploads.{{ $field->name }}" 
                       class="form-control" 
                       accept="image/*"
                       id="upload_{{ $field->name }}"
                       {{ $field->required ? 'required' : '' }}>
                
                <small class="text-muted d-block mt-1">
                    Max size: {{ $field->meta['max_size'] ?? 2 }}MB. 
                    Allowed types: {{ implode(', ', array_map('strtoupper', $field->meta['allowed_types'] ?? ['jpg', 'png'])) }}
                </small>
                
                @if(isset($uploads[$field->name]))
                    <div class="mt-3 text-center">
                        <div class="border rounded p-2 d-inline-block bg-light">
                            <img src="{{ $uploads[$field->name]->temporaryUrl() }}" 
                                 alt="Preview" 
                                 class="img-fluid"
                                 style="max-width: {{ $field->meta['display_width'] ?? '200px' }}; height: {{ $field->meta['display_height'] ?? 'auto' }};">
                            <div class="small text-muted mt-2">
                                <i class="fas fa-check-circle text-success"></i> Image ready to upload
                            </div>
                        </div>
                    </div>
                @endif
                
                @error('uploads.' . $field->name) 
                    <div class="alert alert-danger alert-sm mt-2">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
                
                <div wire:loading wire:target="uploads.{{ $field->name }}" class="mt-2">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <span class="small text-muted ml-2">Uploading...</span>
                    </div>
                </div>
            </div>
        @endif
    @endif
@if(!$isInsideContainer)
</div>
@endif
