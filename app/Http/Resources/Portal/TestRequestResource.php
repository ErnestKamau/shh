<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class TestRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                          => $this->id,
            'batch_code'                  => $this->batch_code,
            'status'                      => $this->status,
            'sample_type_id'              => $this->sample_type_id,
            'crm_customer_id'             => $this->crm_customer_id,
            'crm_contact_id'              => $this->crm_contact_id,
            'date_collected'              => $this->date_collected,
            'date_expected'               => $this->date_expected,
            'receipt_date'                => $this->receipt_date,
            'batch_scope'                 => $this->batch_scope,
            'batch_instructions'          => $this->batch_instructions,
            'reference_number'            => $this->reference_number,
            'created_at'                  => $this->created_at?->toISOString(),
            'updated_at'                  => $this->updated_at?->toISOString(),
            'samples'                     => SampleDetailResource::collection(
                $this->whenLoaded('samples')
            ),
        ];
    }
}
