
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
            
            // Default styling for container should be minimal/transparent if no styles provided
            // We remove the dashed border/bg default from previously
        @endphp
        <div class="container-wrapper" style="{{ $inlineStyles }}">
           @if($field->label && $field->label !== 'Container')
               <div class="container-header mb-2">
                   <h6 class="m-0 font-weight-bold">{{ $field->label }}</h6>
               </div>
           @endif
           <div class="container-body">
               <div class="row">
                   @foreach($field->children as $child)
                       <div class="col-md-{{ $colWidth }} col-12 mb-2">
                           @include('template-engine::livewire.partials.render-field', [
                               'field' => $child, 
                               'isChildOfContainer' => true,
                               'resolvedVariables' => $resolvedVariables ?? [],
                               'dynamicOptions' => $dynamicOptions ?? []
                           ])
                       </div>
                   @endforeach
               </div>
           </div>
        </div>
    @elseif(in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link', 'image', 'static_image_upload', 'ul', 'ol']))
        {{-- Static Content --}}
        @if($field->type === 'heading')
            @php
                $headingContent = $field->label;
                
                // Resolve variable placeholders like {{ table.column }} or {{ variable }}
                if (isset($resolvedVariables) || (isset($this) && isset($this->resolvedVariables))) {
                    $vars = isset($resolvedVariables) ? $resolvedVariables : ($this->resolvedVariables ?? []);
                    
                    $headingContent = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($vars) {
                        $placeholder = $matches[1];
                        $parts = explode('.', $placeholder);
                        
                        if (count($parts) == 2) {
                            $varName = $parts[0];
                            $colName = $parts[1];
                            
                            if (isset($vars[$varName])) {
                                $data = $vars[$varName];
                                if (is_object($data)) return $data->$colName ?? '';
                                elseif (is_array($data)) return $data[$colName] ?? '';
                            }
                            
                            foreach ($vars as $key => $val) {
                                if (strtolower($key) === strtolower($varName)) {
                                    if (is_object($val)) return $val->$colName ?? '';
                                    elseif (is_array($val)) return $val[$colName] ?? '';
                                }
                            }
                        } elseif (count($parts) == 1 && isset($vars[$placeholder])) {
                            $val = $vars[$placeholder];
                            if (is_scalar($val)) return $val;
                        }
                        
                        return $matches[0];
                    }, $headingContent);
                }
            @endphp
            <{{ $field->meta['level'] ?? 'h2' }}>{!! e($headingContent) !!}</{{ $field->meta['level'] ?? 'h2' }}>
        @elseif($field->type === 'paragraph')
            @php
                $content = $field->label;
                
                // Resolve variable placeholders like {{ table.column }} or {{ variable }}
                if (isset($resolvedVariables) || (isset($this) && isset($this->resolvedVariables))) {
                    $vars = isset($resolvedVariables) ? $resolvedVariables : ($this->resolvedVariables ?? []);
                    
                    $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($vars) {
                        $placeholder = $matches[1];
                        $parts = explode('.', $placeholder);
                        
                        // Case 1: {{ table.column }} format
                        if (count($parts) == 2) {
                            $varName = $parts[0];
                            $colName = $parts[1];
                            
                            // Direct match
                            if (isset($vars[$varName])) {
                                $data = $vars[$varName];
                                if (is_object($data)) {
                                    return $data->$colName ?? '';
                                } elseif (is_array($data)) {
                                    return $data[$colName] ?? '';
                                }
                            }
                            
                            // Try case-insensitive and singular/plural matching
                            foreach ($vars as $key => $val) {
                                $normKey = strtolower($key);
                                $normVar = strtolower($varName);
                                
                                if ($normKey === $normVar || 
                                    $normKey === strtolower(\Illuminate\Support\Str::singular($varName)) ||
                                    strtolower(\Illuminate\Support\Str::plural($key)) === $normVar) {
                                    
                                    if (is_object($val)) {
                                        return $val->$colName ?? '';
                                    } elseif (is_array($val)) {
                                        return $val[$colName] ?? '';
                                    }
                                }
                            }
                        }
                        // Case 2: {{ variable }} format (single value)
                        elseif (count($parts) == 1) {
                            if (isset($vars[$placeholder])) {
                                $val = $vars[$placeholder];
                                if (is_scalar($val)) {
                                    return $val;
                                }
                            }
                        }
                        
                        // Return original placeholder if not resolved
                        return $matches[0];
                    }, $content);
                }
            @endphp
            <p>{!! nl2br(e($content)) !!}</p>
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
        @elseif(trim($field->type) == 'ul' || trim($field->type) == 'ol')
             @php
                $listType = $field->type === 'ul' ? 'ul' : 'ol';
                $styleType = $field->meta['css']['list_style_type'] ?? '';
                $customClass = $field->meta['css']['custom_css'] ?? '';
                // Add unstyled class if list-style is none or not set for reports if desired, 
                // but let's stick to user config.
                // We should ensure padding/margin is handled if user wants to remove it.
                $inlineStyle = '';
                if ($styleType) $inlineStyle .= 'list-style-type: ' . $styleType . ';';
            @endphp
            
            <{{ $listType }} class="{{ $customClass }}" style="{{ $inlineStyle }}">

                @php
                    // Check if the LIST ITSELF is dynamic
                    $parentDynamicItems = isset($dynamicOptions[$field->id]) ? $dynamicOptions[$field->id] : null;
                @endphp

                @if($parentDynamicItems)
                    {{-- The UL/OL itself is bound to a variable (collection) --}}
                    @foreach($parentDynamicItems as $item)
                        {{-- For each item in the collection, render all children as sub-elements --}}
                        @foreach($field->children as $child)
                            <li class="mb-1">
                                @php
                                    $content = '';
                                    
                                    // The child's label may contain variable placeholders like {{ companies.street }}
                                    // We need to resolve these against the current $item
                                    $template = $child->label ?? '';
                                    
                                    // Replace {{ variable.column }} or {{ column }} with actual values from $item
                                    $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($item) {
                                        $placeholder = $matches[1];
                                        
                                        // Handle both "table.column" and "column" formats
                                        // Split by dot and take the last part as the actual column name
                                        // Split by dot and take the last part as the actual column name
                                        $parts = explode('.', $placeholder);
                                        $column = end($parts);
                                        
                                        // 1. Try to get value from the current List Item (Context)
                                        $val = '';
                                        $foundInItem = false;
                                        if (is_object($item)) {
                                            if (isset($item->$column)) {
                                                $val = $item->$column;
                                                $foundInItem = true;
                                            }
                                        } elseif (is_array($item)) {
                                            if (isset($item[$column])) {
                                                $val = $item[$column];
                                                $foundInItem = true;
                                            }
                                        }

                                        // 2. If not found in item, try Global Variables (e.g. {{ table.column }} or {{ var }})
                                        if (!$foundInItem) {
                                            // Access global resolvedVariables passed securely to view or via Livewire
                                            // In RenderForm component context, they are properties. But inside partial loop?
                                            // We need to ensure $resolvedVariables is available.
                                            // Using $this->resolvedVariables if available in blade scope? No, 'this' is component.
                                            // We usually pass $field. 
                                            // We can access 'resolvedVariables' if it was passed to the view.
                                            
                                            // The render-form view receives $resolvedVariables.
                                            // Does render-field receive it?
                                            // Recursive include: ['field' => $child, 'isChildOfContainer' => true]
                                            // We MIGHT be missing 'resolvedVariables' in the recursive include array!
                                            // BUT, global variables in Blade usually persist?
                                            // Let's assume we can access $resolvedVariables or $this->resolvedVariables.
                                            
                                            $vars = isset($resolvedVariables) ? $resolvedVariables : (isset($this->resolvedVariables) ? $this->resolvedVariables : []);

                                            // Logic from Static Resolver (simplified)
                                            if (count($parts) == 2) {
                                                $varName = $parts[0]; // e.g. sample_headers
                                                $colName = $parts[1]; // e.g. batch_code
                                                
                                                if (isset($vars[$varName])) {
                                                    $data = $vars[$varName];
                                                    return is_object($data) ? ($data->$colName ?? '') : ($data[$colName] ?? '');
                                                }
                                            } elseif (count($parts) == 1) {
                                                 if (isset($vars[$placeholder])) return $vars[$placeholder];
                                            }
                                            
                                            // If still not found, return placeholder or empty?
                                            // Return placeholder so user knows it failed.
                                            return $item->$column ?? $placeholder;
                                        }
                                        
                                        return $val;
                                    }, $template);
                                @endphp
                                {!! nl2br(e($content)) !!}
                            </li>
                        @endforeach
                    @endforeach
                @else
                    {{-- Static List with potentially dynamic children --}}
                    @foreach($field->children as $child)
                        @php
                            // Check if this child is a Repeater (Dynamic/Variable Loop)
                            $repeaterItems = null; 
                            if (isset($dynamicOptions[$child->id])) {
                                $repeaterItems = $dynamicOptions[$child->id];
                            }
                        @endphp

                        @if($repeaterItems)
                            {{-- REPEATER: Render multiple LIs --}}
                            @foreach($repeaterItems as $item)
                                 <li class="mb-1">
                                    @php
                                        $content = '';
                                        // 1. Variable Template
                                        if (!empty($child->meta['item_template'])) {
                                            $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($item) {
                                                $key = $matches[1];
                                                $val = is_object($item) ? ($item->$key ?? '') : ($item[$key] ?? '');
                                                return $val;
                                            }, $child->meta['item_template']);
                                        } 
                                        // 2. Dynamic Binding (uses 'label' alias from resolver if available)
                                        elseif (is_object($item) && isset($item->label)) {
                                             $content = $item->label;
                                        } elseif (is_array($item) && isset($item['label'])) {
                                             $content = $item['label'];
                                        }
                                        // 3. Fallback: Child Label as Template (legacy concept) or properties
                                        elseif ($child->label) {
                                             $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($item) {
                                                $key = $matches[1];
                                                $val = is_object($item) ? ($item->$key ?? '') : ($item[$key] ?? '');
                                                return $val;
                                            }, $child->label);
                                        } 
                                        // 4. Fallback Values
                                        else {
                                            $content = is_object($item) 
                                                ? ($item->name ?? $item->title ?? $item->description ?? '') 
                                                : ($item['name'] ?? $item['title'] ?? $item['description'] ?? '');
                                        }
                                    @endphp
                                    {!! nl2br(e($content)) !!}
                                 </li>
                            @endforeach
                        @else
                            {{-- STATIC: Render single LI --}}
                            {{-- RESOLVE PLACEHOLDERS for static items like {{ companies.street }} --}}
                            <li class="mb-1">
                                 @php
                                     // Get label (potential template)
                                     $content = $child->label;
                                     
                                     // Resolve global variables if they exist in resolvedVariables
                                     if (isset($this->resolvedVariables) || isset($resolvedVariables)) {
                                         $vars = isset($this->resolvedVariables) ? $this->resolvedVariables : ($resolvedVariables ?? []);
                                         
                                         $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($vars) {
                                             $placeholder = $matches[1];
                                             $parts = explode('.', $placeholder);
                                             
                                             // Case 1: {{ table.column }}
                                             if (count($parts) == 2) {
                                                 $varName = $parts[0];
                                                 $colName = $parts[1];
                                                 
                                                 if (isset($vars[$varName])) {
                                                     $data = $vars[$varName];
                                                     return is_object($data) ? ($data->$colName ?? '') : ($data[$colName] ?? '');
                                                 }
                                                 foreach($vars as $k => $v) {
                                                     $normKey = strtolower($k);
                                                     $normVar = strtolower($varName);
                                                     // Robust matching: exact, singular, or plural
                                                     if ($normKey == $normVar || 
                                                         $normKey == strtolower(\Illuminate\Support\Str::singular($varName)) ||
                                                         strtolower(\Illuminate\Support\Str::plural($k)) == $normVar) {
                                                         
                                                         return is_object($v) ? ($v->$colName ?? '') : ($v[$colName] ?? '');
                                                     }
                                                 }
                                             }
                                             // Case 2: {{ global_var }}
                                             elseif (count($parts) == 1) {
                                                 if (isset($vars[$placeholder])) return $vars[$placeholder];
                                             }
                                             return $matches[0];
                                         }, $content);
                                     }
                                 @endphp
                                 {!! nl2br(e($content)) !!}
                            </li>
                        @endif
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
            @endif
        @elseif($field->type === 'dynamic_table')
            @php
                $headers = $field->meta['headers'] ?? [];
                $rows = $field->meta['rows'] ?? [];
                $css = $field->meta['css'] ?? [];
                
                // Build Table Style
                $tableStyle = '';
                if(isset($css['width'])) $tableStyle .= "width: {$css['width']};";
                if(isset($css['border_collapse'])) $tableStyle .= "border-collapse: {$css['border_collapse']};";
            @endphp

            <div class="table-responsive">
                <table class="{{ $css['class'] ?? 'table table-bordered' }}" style="{{ $tableStyle }}">
                    <thead>
                        <tr>
                            @foreach($headers as $h)
                                <th style="{{ $h['style'] ?? '' }}; width: {{ $h['width'] ?? 'auto' }}">{{ $h['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @if(($row['type'] ?? 'static') === 'static')
                                {{-- Static Row --}}
                                <tr>
                                    @foreach($row['cells'] ?? [] as $cell)
                                        @php
                                            $cellContent = $cell['content'] ?? '';
                                            // Resolve global variables in static content
                                            if (isset($resolvedVariables) || (isset($this) && isset($this->resolvedVariables))) {
                                                $vars = isset($resolvedVariables) ? $resolvedVariables : ($this->resolvedVariables ?? []);
                                                
                                                $cellContent = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($vars) {
                                                    $placeholder = $matches[1];
                                                    $parts = explode('.', $placeholder);
                                                    
                                                    if (count($parts) == 2) {
                                                        $varName = $parts[0];
                                                        $colName = $parts[1];
                                                        if (isset($vars[$varName])) {
                                                            $data = $vars[$varName];
                                                            return is_object($data) ? ($data->$colName ?? '') : ($data[$colName] ?? '');
                                                        }
                                                    } elseif (count($parts) == 1 && isset($vars[$placeholder])) {
                                                        return $vars[$placeholder];
                                                    }
                                                    return $matches[0];
                                                }, $cellContent);
                                            }
                                        @endphp
                                        <td colspan="{{ $cell['colspan'] ?? 1 }}" style="{{ $cell['style'] ?? '' }}">
                                            {!! nl2br(e($cellContent)) !!}
                                        </td>
                                    @endforeach
                                </tr>
                            @elseif(($row['type'] ?? '') === 'variable')
                                {{-- Variable Loop Row --}}
                                @php
                                    $varId = $row['source'] ?? null;
                                    $loopItems = [];
                                    
                                    // Resolve the loop variable
                                    if ($varId) {
                                        // We need to find the variable name from ID provided in metadata?
                                        // Or we pass the resolved variable data directly?
                                        // View receives $resolvedVariables which is [ 'name' => data ]
                                        // Metadata stores variable ID.
                                        // We need a way to map ID to Name or search in resolvedVariables.
                                        // Ideally, $resolvedVariables should be keyed by ID or we look up name first.
                                        // Current rendering context passes $resolvedVariables keyed by Name.
                                        // BUT we don't have access to the Variable Definition here to know the name from the ID.
                                        
                                        // Workaround: We loop through $resolvedVariables and check if we can match? No.
                                        // Better: In Builder, we save the Variable Name in 'source_name' or similar?
                                        // Or we rely on 'resolvedVariables' containing the data.
                                        // Let's assume we can pass the variable definitions to the view?
                                        // Or $dynamicOptions might contain it?
                                        
                                        // Actually, if we look at existing List implementation: it uses 'parentDynamicItems'.
                                        // For this table row, the source is a variable ID.
                                        // We probably need to Inject the variable definition or name into the view.
                                        
                                        // Let's try to find the variable by ID if available in $variables?
                                        // $variables is usually not passed to render-field (it is in Builder).
                                        
                                        // Fallback: If we can't map ID, we might need to rely on the user typing the correct name? 
                                        // No, configuration uses ID.
                                        
                                        // Solution: We need to pass $templateVariables to the view or resolve it before.
                                        // Assuming RenderForm passes 'variables' (definitions).
                                    }
                                    
                                    // SIMPLIFICATION:
                                    // For now, let's look for a resolved variable that matches the ID? No, resolved uses Names.
                                    // Let's iterate $resolvedVariables.
                                    // If we can't find by ID, we might be stuck.
                                    // Update Builder to save Variable Name as well?
                                    // YES. Let's assume we saved it or can get it.
                                    // Wait, in Builder.php updatedFieldDataType for 'variable', we didn't save name.
                                    
                                    // Let's try this: Look for a variable in $resolvedVariables where the key matches a known name? 
                                    // But we don't know the name.
                                    
                                    // FIX: We will scan $resolvedVariables. If the value describes itself?
                                    // No.
                                    
                                    // Let's assume we update the Builder to store 'source_name' when saving?
                                    // Or we try to resolve it from the context if possible.
                                    
                                    // TEMPORARY FIX:
                                    // We will try to find a matching variable in $resolvedVariables by checking if any key matches the expected one?
                                    // Actually, let's check if we can access the variables list.
                                    // If not, we iterate all resolved variables and check if one is a collection.
                                    // This is risky.
                                    
                                    // BETTER: In Builder.php save Field, we should store source_name.
                                    // But I can't update Builder.php saveField logic easily right now for this specific "row source".
                                    
                                    // Let's assume the user will define the loop variable.
                                    // Let's assume the 'source' value IS the variable ID.
                                    // We can try to look it up if we had $variables.
                                    
                                    // ALTERNATIVE:
                                    // In the view, we can check if $field->template->variables contains it?
                                    // $field->template is available!
                                    
                                    if($varId && $field->template) {
                                         $targetVar = $field->template->variables()->find($varId);
                                         if($targetVar && isset($resolvedVariables[$targetVar->name])) {
                                             $loopItems = $resolvedVariables[$targetVar->name];
                                         }
                                    }
                                @endphp
                                
                                @if(!empty($loopItems) && (is_array($loopItems) || is_object($loopItems)))
                                    @foreach($loopItems as $item)
                                        <tr>
                                            @foreach($row['cells'] ?? [] as $cell)
                                                @php
                                                    $cellContent = $cell['content'] ?? '';
                                                    // Resolve loop variable placeholders {{ item.col }} or {{ col }}
                                                    $cellContent = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($item) {
                                                        $placeholder = $matches[1];
                                                        $parts = explode('.', $placeholder);
                                                        $colName = end($parts);
                                                        
                                                        // Check in item
                                                        if (is_object($item)) {
                                                            if(isset($item->$colName)) return $item->$colName;
                                                        } elseif (is_array($item)) {
                                                            if(isset($item[$colName])) return $item[$colName];
                                                        }
                                                        
                                                        return $matches[0];
                                                    }, $cellContent);
                                                @endphp
                                                <td colspan="{{ $cell['colspan'] ?? 1 }}" style="{{ $cell['style'] ?? '' }}">
                                                    {!! nl2br(e($cellContent)) !!}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                @php
                    $allOptions = $field->options;
                    if(isset($dynamicOptions[$field->id])) {
                        // Merge or replace? 
                        // If dynamicOptions is present, it likely contains aggregated sources.
                        // But $field->options contains static legacy options.
                        // My RenderForm logic aggregates static+var+dyn into dynamicOptions.
                        // So I should use dynamicOptions exclusively if it exists.
                        // But wait, RenderForm loops 'data_sources' (which includes static).
                        // So dynamicOptions should be complete.
                        // Legacy static options might still exist in $field->options if data_sources was not present.
                    }
                    $opts = isset($dynamicOptions[$field->id]) ? $dynamicOptions[$field->id] : $field->options;
                @endphp
                @foreach($opts as $opt)
                     @php 
                        $val = is_array($opt) ? $opt['value'] : $opt->value;
                        $lab = is_array($opt) ? $opt['label'] : $opt->label;
                     @endphp
                    <option value="{{ $val }}">{{ $lab }}</option>
                @endforeach
            </select>
        @elseif($field->type === 'radio')
            <div>
                @php
                    $opts = isset($dynamicOptions[$field->id]) ? $dynamicOptions[$field->id] : $field->options;
                @endphp
                @foreach($opts as $opt)
                     @php 
                        $val = is_array($opt) ? $opt['value'] : $opt->value;
                        $lab = is_array($opt) ? $opt['label'] : $opt->label;
                     @endphp
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="formData.{{ $field->name }}" value="{{ $val }}">
                        <label class="form-check-label">{{ $lab }}</label>
                    </div>
                @endforeach
            </div>
        @elseif($field->type === 'checkbox')
            @if($field->options->count() > 0)
                <div>
                    @php
                        $opts = isset($dynamicOptions[$field->id]) ? $dynamicOptions[$field->id] : $field->options;
                    @endphp
                    @foreach($opts as $opt)
                        @php 
                            $val = is_array($opt) ? $opt['value'] : $opt->value;
                            $lab = is_array($opt) ? $opt['label'] : $opt->label;
                         @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="formData.{{ $field->name }}" value="{{ $val }}">
                            <label class="form-check-label">{{ $lab }}</label>
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
