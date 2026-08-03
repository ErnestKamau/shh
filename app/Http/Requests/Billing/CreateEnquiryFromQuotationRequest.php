<?php

namespace App\Http\Requests\Billing;

use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEnquiryFromQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'quotation_id' => ['required', 'uuid', 'exists:quotation_headers,id'],
            'creation_token' => ['required', 'uuid'],
            'creation_intent' => ['required', Rule::in(EnquiryFromQuotationService::creationIntents())],
            'source_channel' => ['nullable', Rule::in(EnquiryFromQuotationService::allowedSourceChannels())],
            'number_of_samples' => ['required', 'integer', 'min:1', 'max:10000'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'client_po_number' => ['nullable', 'string', 'max:255'],
            'po_skipped' => ['nullable', 'boolean'],
            'date_expected' => ['nullable', 'date'],
            'sample_description' => ['nullable', 'string', 'max:5000'],
            'enquiry_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quotation_id.exists' => 'The selected quotation no longer exists.',
            'creation_intent.required' => 'Choose how this quotation should enter the workflow.',
            'creation_intent.in' => 'Choose a valid quotation creation intent.',
            'number_of_samples.required' => 'Confirm the number of physical samples.',
            'number_of_samples.min' => 'At least one physical sample is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('creation_intent')) {
            $this->merge([
                'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
            ]);
        }

        if ($this->has('po_skipped')) {
            $this->merge([
                'po_skipped' => filter_var($this->input('po_skipped'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
