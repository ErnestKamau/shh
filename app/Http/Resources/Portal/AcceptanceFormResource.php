<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcceptanceFormResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'request_date' => optional($this->request_date)->format('Y-m-d'),
            'number_of_samples' => (int) $this->number_of_samples,
            'mode_of_work' => $this->mode_of_work,
            'date_of_sampling' => optional($this->date_of_sampling)->format('Y-m-d'),
            'total_amount' => (float) $this->total_amount,
            'currency_id' => $this->currency_id,
            'customer_certification_text' => $this->customer_certification_text,
            'customer_signer_name' => $this->customer_signer_name,
            'customer_signed_at' => optional($this->customer_signed_at)?->toIso8601String(),
            'sample_header_id' => $this->sample_header_id,
            'invoice_id' => $this->invoice_id,
            'processing_error' => $this->when($this->processing_error, $this->processing_error),
            'lines' => AcceptanceFormLineResource::collection($this->whenLoaded('lines')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
