<?php

namespace App\Http\Requests\Api\Portal;

class ShowPortalInvoiceRequest extends PortalCustomerRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'invoice_id' => ['required', 'uuid'],
        ]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'invoice_id' => $this->route('invoice_id'),
        ]);
    }
}
