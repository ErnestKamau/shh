<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportingDocumentTemplateDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_code' => $this->document_code,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'version' => $this->version,
            'sections' => $this->whenLoaded('sections', function () {
                return $this->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'title' => $section->title,
                        'sort_order' => $section->sort_order,
                        'elements' => $section->elements->map(function ($el) {
                            return [
                                'id' => $el->id,
                                'element_type' => $el->element_type,
                                'label' => $el->label,
                                'name' => $el->name,
                                'placeholder' => $el->placeholder,
                                'help_text' => $el->help_text,
                                'is_required' => (bool) $el->is_required,
                                'is_readonly' => (bool) $el->is_readonly,
                                'default_value' => $el->default_value,
                                'validation_rules' => $el->validation_rules,
                                'options' => $el->options,
                                'conditional_logic' => $el->conditional_logic,
                                'sort_order' => $el->sort_order,
                            ];
                        })->values(),
                    ];
                })->values();
            }),
        ];
    }
}
