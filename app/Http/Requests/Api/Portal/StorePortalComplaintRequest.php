<?php

namespace App\Http\Requests\Api\Portal;

class StorePortalComplaintRequest extends PortalCustomerRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'received_from_type' => ['nullable', 'string', 'max:50'],
            'is_lab_related' => ['nullable', 'boolean'],
            'nature_of_complaint' => ['nullable', 'string', 'max:255'],
            'test_item' => ['nullable', 'string', 'max:255'],
            'report_serial_no' => ['nullable', 'string', 'max:255'],
            'title_position' => ['nullable', 'string', 'max:255'],
            'mode_of_delivery' => ['nullable', 'string', 'max:255'],
            'submitter_name' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
