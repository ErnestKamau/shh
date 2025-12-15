<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Services\DynamicFieldResolverService;

class RenderForm extends Component
{
    public $template;
    public $formData = [];
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

            if (!empty($rule)) {
                $rules['formData.' . $field->name] = implode('|', $rule);
                $attributes['formData.' . $field->name] = $field->label;
            }
        }

        $this->validate($rules, [], $attributes);
        
        // Save submission
        $this->template->submissions()->create([
            'user_id' => auth()->id(),
            'data' => $this->formData,
            'meta' => ['ip' => request()->ip(), 'user_agent' => request()->userAgent()]
        ]);
        
        session()->flash('success', 'Form submitted successfully!');
        return redirect()->route('templates.index');
    }

    public function render()
    {
        return view('template-engine::livewire.render-form');
    }
}
