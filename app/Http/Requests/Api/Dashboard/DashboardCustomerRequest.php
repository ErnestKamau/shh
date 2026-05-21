<?php

namespace App\Http\Requests\Api\Dashboard;

use App\Http\Requests\Api\Portal\PortalCustomerRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DashboardCustomerRequest extends PortalCustomerRequest
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.uuid' => 'A valid customer identifier is required.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this customer dashboard.',
            'errors' => ['customer_id' => ['Customer scope mismatch.']],
        ], 403));
    }
}
