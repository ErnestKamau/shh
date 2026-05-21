<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcceptanceFormLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'line_no' => $this->line_no,
            'sample_type_id' => $this->sample_type_id,
            'sample_type_name' => $this->sampleType?->name,
            'analysis_type_id' => $this->analysis_type_id,
            'analysis_type_name' => $this->analysisType?->name,
            'analysis_element_id' => $this->analysis_element_id,
            'parameter_label' => $this->parameter_label,
            'unit_amount' => (float) $this->unit_amount,
            'number_of_samples' => (int) $this->number_of_samples,
            'is_approved' => (bool) $this->is_approved,
            'line_total' => (float) $this->unit_amount * (int) $this->number_of_samples,
        ];
    }
}
