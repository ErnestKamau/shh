<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportingDocumentTemplateResource extends JsonResource
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
        ];
    }
}
