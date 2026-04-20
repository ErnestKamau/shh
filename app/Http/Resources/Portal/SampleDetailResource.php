<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class SampleDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'sample_code'      => $this->sample_code,
            'barcode'          => $this->barcode,
            'sample_condition' => $this->sample_condition_id,
            'sample_point'     => $this->sample_point_id,
            'analysis_types'   => $this->analysis_type_id
                ? array_values(array_filter(explode(',', $this->analysis_type_id)))
                : [],
            'notes'            => $this->notes_body,
            'disposal_date'    => $this->disposal_date,
        ];
    }
}
