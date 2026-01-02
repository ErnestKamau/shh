<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Services\DynamicFieldResolverService;
use Modules\TemplateEngine\Services\FileUploadService;

class RenderForm extends Component
{
    use WithFileUploads;
    
    public $template;
    public $formData = [];
    public $uploads = [];
    public $dynamicOptions = [];

    public function mount(FormTemplate $template)
    {
        $this->template = $template;
        $this->formData = [];
        
        // Pre-fetch dynamic data
        $resolver = app(DynamicFieldResolverService::class);
        foreach ($template->fields as $field) {
            if ($field->type === 'dynamic') {
                $this->dynamicOptions[$field->id] = $resolver->resolveOptions($field);
            }
            // Resolve data for dynamic lists (UL/OL)
            if (in_array($field->type, ['ul', 'ol']) && 
                isset($field->meta['data_source']) && 
                $field->meta['data_source'] === 'dynamic' &&
                $field->datasetBinding) {
                    
                $this->dynamicOptions[$field->id] = $resolver->resolveOptions($field);
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
