<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportingDocumentInstanceResource extends JsonResource
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
            'supporting_document_template_id' => $this->supporting_document_template_id,
            'template_version' => $this->template_version,
            'sample_header_id' => $this->sample_header_id,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'values' => $this->whenLoaded('values', function () {
                return $this->values->map(function ($v) {
                    return [
                        'element_id' => $v->supporting_document_element_id,
                        'value' => $v->value,
                    ];
                })->values();
            }),
        ];
    }
}
