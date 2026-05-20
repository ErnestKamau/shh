<?php

namespace App\Http\Requests\Api\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PortalCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $routeCustomerId = (string) $this->route('customer_id', '');
        $headerCustomerId = (string) ($this->header('X-CRM-Customer-Id') ?? $this->input('crm_customer_id', ''));

        return $routeCustomerId !== ''
            && $headerCustomerId !== ''
            && hash_equals($headerCustomerId, $routeCustomerId);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->route('customer_id'),
        ]);
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this customer resource.',
            'errors' => ['customer_id' => ['Customer scope mismatch.']],
        ], 403));
    }
}
