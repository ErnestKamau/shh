<?php

namespace App\Http\Requests\Api\Portal;

use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use Illuminate\Foundation\Http\FormRequest;

class SignAcceptanceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'customer_signer_name' => ['required', 'string', 'max:255'],
            'customer_signature' => ['required', 'string'],
            'customer_signed_at' => ['nullable', 'date'],
        ], app(SampleReceiptNotificationService::class)->portalPartialValidationRules());
    }
}
