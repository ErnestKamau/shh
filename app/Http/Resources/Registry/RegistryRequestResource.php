<?php

namespace App\Http\Resources\Registry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistryRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'direction' => $this->direction,
            'current_stage' => $this->current_stage,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'code' => $this->category->code,
            ]),
            'assigned_to' => $this->assigned_to,
            'received_at' => $this->received_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
