<?php

namespace App\Http\Requests\Api\Portal;

class StorePortalAmendmentRequest extends PortalCustomerRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'batch_id' => ['required', 'uuid'],
            'sample_ids' => ['required', 'array', 'min:1'],
            'sample_ids.*' => ['required'],
            'reason' => ['required', 'string', 'min:5', 'max:5000'],
        ]);
    }
}
