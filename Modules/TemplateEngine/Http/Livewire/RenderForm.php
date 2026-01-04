<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Services\DynamicFieldResolverService;
use Modules\TemplateEngine\Services\VariableResolutionEngine;
use Modules\TemplateEngine\Services\FileUploadService;

class RenderForm extends Component
{
    use WithFileUploads;
    
    public $template;
    public $formData = [];
    public $uploads = [];
    public $showInjectionModal = false;
    public $requiredInjections = [];
    public $injectionValues = [];
    public $injectionOptions = [];
    public $dynamicOptions = [];
    public $resolvedVariables = [];

    public function mount(FormTemplate $template)
    {
        $this->template = $template;
        $this->formData = [];
        
        // Ensure recursive loading of children for correct rendering
        // Load up to 3 levels deep just to be safe for containers
        $template->load([
            'sections.fields.children.options', 
            'sections.fields.children.children.options',
            'sections.fields.children.datasetBinding',
            'sections.fields.children.children.datasetBinding'
        ]);
        
        // Check for Injections
        $this->detectInjections();

        // If no injections needed, resolve normally. Else wait for user input.
        if (!$this->showInjectionModal) {
            $this->resolveAndInit();
        }
    }

    public function detectInjections()
    {
        $this->requiredInjections = [];
        $this->injectionOptions = [];
        $this->showInjectionModal = false;

        foreach ($this->template->variables as $var) {
            if ($var->type === 'database' && !empty($var->config['injections']) && is_array($var->config['injections'])) {
                foreach ($var->config['injections'] as $injection) {
                    // Use the injection's label as the key (this matches what the filter value references)
                    // e.g., filter value = "sampleHeaderID", injection label = "sampleHeaderID"
                    $name = $injection['label'] ?? $injection['name'] ?? ('injection_' . uniqid());
                    // Skip if already added
                    if (isset($this->requiredInjections[$name])) continue;

                    $this->requiredInjections[$name] = [
                        'label' => $injection['label'] ?? $name,
                        'name' => $name,
                        'type' => 'select', // Assume select for now as requested
                    ];
                    
                    // Fetch Options if table is defined (Builder users 'table', legacy might use 'source_table')
                    $table = $injection['table'] ?? $injection['source_table'] ?? null;
                    
                    if (!empty($table)) {
                       try {
                           $query = \Illuminate\Support\Facades\DB::table($table);
                           
                           $labelCol = 'id';
                           // Priority: Configured Label Column
                           if (!empty($injection['label_column'])) {
                               $labelCol = $injection['label_column'];
                           } else {
                               // Fallback: Guess
                               $cols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
                               foreach(['name', 'title', 'description', 'code', 'label'] as $guess) {
                                   if (in_array($guess, $cols)) {
                                       $labelCol = $guess;
                                       break;
                                   }
                               }
                           }
                           
                           // Ensure we select both ID (or value col) and label col
                           // For now assuming ID is always the value key, but ideally should be configurable too if we wanted 'code' as value.
                           // User prompt implies "value field" is selected as 'column', let's use that if available.
                           $valueCol = $injection['column'] ?? 'id';
                           
                           $results = $query->select($valueCol . ' as value', $labelCol . ' as label')->limit(100)->get();
                           
                           $this->injectionOptions[$name] = $results->map(function($r) {
                               return ['value' => $r->value, 'label' => $r->label];
                           })->toArray();
                           
                       } catch (\Throwable $e) {
                           $this->injectionOptions[$name] = [];
                       }
                    }
                }
            }
        }
        
        if (!empty($this->requiredInjections)) {
            $this->showInjectionModal = true;
        }
    }
    
    public function submitInjections()
    {
        try {
            // Ensure relationships are loaded (Livewire rehydration might have lost them)
            $this->template->load([
                'variables', 
                'sections.fields.children.options', 
                'sections.fields.children.children.options',
                'sections.fields.children.datasetBinding'
            ]);

            $this->showInjectionModal = false;
            $this->resolveAndInit();
        } catch (\Throwable $e) {
            // If something fails, log it (if we could) and maybe show error
            // For now, we set a visual error or just allow the crash but nicely
            session()->flash('error', 'Error preparing preview: ' . $e->getMessage());
        }
    }

    public function resolveAndInit()
    {
        // 1. Resolve Variables with Context (Injections)
        $varEngine = app(VariableResolutionEngine::class);
        $this->resolvedVariables = $varEngine->resolveVariables($this->template, $this->injectionValues);
        
        // DEBUG LOGGING


        // 1b. Alias variables by table name for legacy placeholders (e.g. {{ table.column }})
        if ($this->template->variables) {
             foreach ($this->template->variables as $var) {
                 if (isset($this->resolvedVariables[$var->name])) {
                     $config = $var->config ?? [];
                     
                     // Check common config keys for table/source
                     $alias = $config['table'] ?? $config['source_table'] ?? null;
                     if ($alias && !isset($this->resolvedVariables[$alias])) {
                         $this->resolvedVariables[$alias] = $this->resolvedVariables[$var->name];
 
                     }
                     
                     // Also alias by joined table names (for fields like {{ crm_customers.name }})
                     if (!empty($config['joins']) && is_array($config['joins'])) {
                         foreach ($config['joins'] as $join) {
                             $joinTable = $join['table'] ?? null;
                             if ($joinTable && !isset($this->resolvedVariables[$joinTable])) {
                                 // For joined tables, the data is in the same resolved record
                                 $this->resolvedVariables[$joinTable] = $this->resolvedVariables[$var->name];
 
                             }
                         }
                     }
                 }
             }
        }

        // 2. Resolve Dynamic Options for Fields
        $dynamicResolver = app(DynamicFieldResolverService::class);
        

        
        // Ensure we have fields and children loaded
        $this->template->loadMissing(['sections.fields.children']);

        foreach ($this->template->sections as $section) {

            foreach ($section->fields as $field) {
                try {
                    // Debug log for relevant fields


                    // Processing logic (extracted to helper or kept here)
                    $this->processFieldOptions($field, $dynamicResolver);

                    // Recurse for children
                    if ($field->children && $field->children->count() > 0) {
                        $this->processIterativeChildren($field->children, $dynamicResolver);
                    }
                } catch (\Throwable $e) {

                }
                
                // Also process children
                if ($field->children) {
                    foreach ($field->children as $child) {
                        $this->processFieldOptions($child, $dynamicResolver);
                         // And grandchildren... (consider recursion if deeply nested)
                         if ($child->children) {
                            foreach ($child->children as $grandChild) {
                                $this->processFieldOptions($grandChild, $dynamicResolver);
                            }
                         }
                    }
                }
            }
        }
    }
    
    protected function processFieldOptions($field, $dynamicResolver) 
    {
         if ($field->id == 22) {

         }

            // Handle Option-based fields (Select, Radio, Checkbox)
            if (in_array($field->type, ['select', 'radio', 'checkbox'])) {
                // If it uses the new Multi-Source system
                if (isset($field->meta['data_sources']) && is_array($field->meta['data_sources'])) {
                    $options = [];
                    
                    foreach ($field->meta['data_sources'] as $source) {
                        $type = $source['type'] ?? 'static';
                        $config = $source['config'] ?? [];

                        // Static
                        if ($type === 'static') {
                            if (!empty($config['label'])) {
                                $options[] = [
                                    'label' => $config['label'],
                                    'value' => $config['value'] ?? $config['label']
                                ];
                            }
                        }
                        // Dynamic
                        elseif ($type === 'dynamic' && !empty($config['dataset_binding'])) {
                            $rows = $dynamicResolver->resolveBinding($config['dataset_binding']);
                            foreach ($rows as $row) {
                                $options[] = [
                                    'label' => $row->label ?? $row->value ?? '',
                                    'value' => $row->value ?? $row->label ?? ''
                                ];
                            }
                        }
                        // Variable
                        elseif ($type === 'variable' && !empty($config['variable_id'])) {
                            $variableVar = $this->template->variables->firstWhere('id', $config['variable_id']);
                            
                            if ($variableVar && isset($this->resolvedVariables[$variableVar->name])) {
                                $data = $this->resolvedVariables[$variableVar->name];
                                
                                // Collection
                                if (is_iterable($data)) {
                                    $labelCol = $config['template'] ?? 'name'; // 'template' used as label col for inputs
                                    $valueCol = $config['value_col'] ?? 'id';
                                    $column = $config['column'] ?? null; // If column is specified (often for single val but logic here is vague)
                                    
                                    // If 'column' is set and we have collection, maybe user wants specific column from collection? 
                                    // Usually Select options come from row properties.
                                    
                                    foreach ($data as $item) {
                                        // Handle object or array items
                                        $itemObj = (object) $item;
                                        
                                        $optLabel = $itemObj->$labelCol ?? '';
                                        
                                        // If 'column' was selected in builder instead of template string usage
                                        if (empty($config['template']) && !empty($config['column'])) {
                                             $colName = $config['column'];
                                             $optLabel = $itemObj->$colName ?? '';
                                        }
                                        
                                        $options[] = [
                                            'label' => $optLabel,
                                            'value' => $itemObj->$valueCol ?? ''
                                        ];
                                    }
                                } 
                                // Single Object/Scalar
                                else {
                                    $col = $config['column'] ?? null;
                                    $val = $data;
                                    if ($col && is_object($data)) {
                                        $val = $data->$col ?? $val;
                                    } elseif ($col && is_array($data)) {
                                        $val = $data[$col] ?? $val;
                                    }
                                    
                                    $options[] = [
                                        'label' => (string) $val,
                                        'value' => (string) $val
                                    ];
                                }
                            }
                        }
                    }
                    $this->dynamicOptions[$field->id] = $options;
                }
                // legacy Fallback for 'dynamic' type fields
                elseif ($field->type === 'dynamic') { 
                     $this->dynamicOptions[$field->id] = $dynamicResolver->resolveOptions($field);
                }
            }

            // LIST ITEMS / REPEATERS 
            if (isset($field->datasetBinding) || !empty($field->meta['dataset_binding'])) {
                 $binding = $field->datasetBinding ? $field->datasetBinding->toArray() : ($field->meta['dataset_binding'] ?? []);
                 if (!empty($binding)) {
                     $this->dynamicOptions[$field->id] = $dynamicResolver->resolveBinding($binding, true); 
                 }
            }
            elseif (!empty($field->meta['variable_id']) && !empty($field->meta['is_loop'])) {
                 $varId = $field->meta['variable_id'];
                 $variableVar = $this->template->variables->firstWhere('id', $varId);
                 if ($variableVar && isset($this->resolvedVariables[$variableVar->name])) {
                     $data = $this->resolvedVariables[$variableVar->name];
                     $this->dynamicOptions[$field->id] = is_iterable($data) ? $data : [$data];
                 }
            }
            // Handle UL/OL with variable data source (legacy format)
            elseif (in_array($field->type, ['ul', 'ol']) && !empty($field->meta['data_source']) && $field->meta['data_source'] === 'variable' && !empty($field->meta['variable_id'])) {
                 $varId = $field->meta['variable_id'];
                 $variableVar = $this->template->variables->firstWhere('id', $varId);
                 if ($variableVar && isset($this->resolvedVariables[$variableVar->name])) {
                     $data = $this->resolvedVariables[$variableVar->name];
                     // For UL/OL, we expect data to be iterable (a collection of items)
                     $this->dynamicOptions[$field->id] = is_iterable($data) ? $data : [$data];
                     
                     // Debug logging

                 }
            }
    }

    protected function processIterativeChildren($fields, $resolver)
    {
        foreach ($fields as $field) {
            try {
                // Debug recursion
                if ($field->id == 22) {

                }
                
                $this->processFieldOptions($field, $resolver);
                
                if ($field->children && $field->children->count() > 0) {
                     $this->processIterativeChildren($field->children, $resolver);
                }
            } catch (\Throwable $e) {
                // log error but continue

            }
        }
    }

    public function submit()
    {
        // Generate validation rules
        $rules = [];
        $attributes = [];

        foreach ($this->template->fields as $field) {
            // Skip static fields
            if (in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link', 'image', 'container'])) {
                continue;
            }

            $rule = [];
            if ($field->required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            // specific type validation
            if ($field->type === 'number') $rule[] = 'numeric';
            if ($field->type === 'date') $rule[] = 'date';
            // if ($field->type === 'email') $rule[] = 'email'; // if we had email type
            
            // Image upload validation
            if ($field->type === 'image_upload') {
                $maxSize = $field->meta['max_size'] ?? 2;
                $allowedTypes = $field->meta['allowed_types'] ?? ['jpg', 'jpeg', 'png'];
                
                $rule[] = 'image';
                $rule[] = 'max:' . ($maxSize * 1024); // Convert MB to KB
                $rule[] = 'mimes:' . implode(',', $allowedTypes);
                
                $rules['uploads.' . $field->name] = implode('|', $rule);
                $attributes['uploads.' . $field->name] = $field->label;
            } else {
                if (!empty($rule)) {
                    $rules['formData.' . $field->name] = implode('|', $rule);
                    $attributes['formData.' . $field->name] = $field->label;
                }
            }
        }

        $this->validate($rules, [], $attributes);
        
        // Handle file uploads first (before creating submission)
        $fileUploadService = app(FileUploadService::class);
        $uploadedFilePaths = [];
        
        foreach ($this->template->fields as $field) {
            if ($field->type === 'image_upload' && isset($this->uploads[$field->name])) {
                $file = $this->uploads[$field->name];
                
                // Store in temporary location first
                $path = $fileUploadService->storeUploadedFile($file, $field->name);
                $uploadedFilePaths[$field->name] = $path;
            }
        }
        
        // Merge uploaded file paths into formData
        $this->formData = array_merge($this->formData, $uploadedFilePaths);
        
        // Save submission
        $submission = $this->template->submissions()->create([
            'user_id' => auth()->id(),
            'data' => $this->formData,
            'meta' => ['ip' => request()->ip(), 'user_agent' => request()->userAgent()]
        ]);
        
        // Move files from temp to submission-specific directory
        if (!empty($uploadedFilePaths)) {
            $updatedPaths = $fileUploadService->moveTemporaryFiles($uploadedFilePaths, $submission->id);
            
            // Update submission with new paths
            $submission->update([
                'data' => array_merge($this->formData, $updatedPaths)
            ]);
        }
        
        session()->flash('success', 'Form submitted successfully!');
        return redirect()->route('templates.index');
    }

    public function render()
    {
        return view('template-engine::livewire.render-form');
    }
}
