<?php

namespace App\Http\Requests\Api\Portal;

class CompletePortalFeedbackRequest extends PortalCustomerRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'token' => ['required', 'string', 'max:255'],
            'contact_id' => ['required', 'uuid'],
            'service_type' => ['required', 'string', 'max:100'],
            'service_type_other' => ['nullable', 'string', 'max:255'],
            'service_reference_no' => ['required', 'string', 'max:255'],
            'equipment_sample_id' => ['nullable', 'string', 'max:255'],
            'results_issued_date' => ['nullable', 'date'],
            'specific_feedback' => ['nullable', 'string', 'max:5000'],
            'suggestions' => ['nullable', 'string', 'max:5000'],
            'will_recommend' => ['nullable', 'string', 'max:50'],
            'consent_contact' => ['nullable', 'boolean'],
            'preferred_contact_method' => ['nullable', 'string', 'max:100'],
            'contact_position' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'usage_duration' => ['nullable', 'string', 'max:100'],
            'ratings' => ['required', 'array', 'min:1'],
            'ratings.*.evaluation_metric_id' => ['required', 'uuid'],
            'ratings.*.rating' => ['required', 'integer', 'min:1'],
        ]);
    }
}
