<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSampleSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'crm_customer_id' => ['required', 'integer'],
            'crm_contact_id' => ['nullable', 'integer'],
            'submitting_agency' => ['nullable', 'string', 'max:255'],
            'submitting_officer_full_name' => ['nullable', 'string', 'max:255'],
            'submitting_officer_title' => ['nullable', 'string', 'max:255'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'working_station' => ['nullable', 'string', 'max:255'],
            'office_telephone_no' => ['nullable', 'string', 'max:255'],
            'mobile_telephone_no' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'case_no' => ['nullable', 'string', 'max:255'],
            'offence' => ['nullable', 'string', 'max:255'],
            'date_of_seizure' => ['nullable', 'date'],
            'seizure_region' => ['nullable', 'string', 'max:255'],
            'seizure_district' => ['nullable', 'string', 'max:255'],
            'seizure_ward' => ['nullable', 'string', 'max:255'],
            'seizure_village_street' => ['nullable', 'string', 'max:255'],

            'submitted_by_full_name' => ['nullable', 'string', 'max:255'],
            'submitted_by_title' => ['nullable', 'string', 'max:255'],
            'submitted_by_signature' => ['nullable', 'string'],
            'submitted_by_date' => ['nullable', 'date'],
            'submitted_by_time' => ['nullable', 'date_format:H:i'],

            'received_by_full_name' => ['nullable', 'string', 'max:255'],
            'received_by_title' => ['nullable', 'string', 'max:255'],
            'received_by_signature' => ['nullable', 'string', 'max:255'],
            'received_by_date' => ['nullable', 'date'],
            'received_by_time' => ['nullable', 'date_format:H:i'],

            'exhibits' => ['nullable', 'array'],
            'exhibits.*.serial_number' => ['nullable', 'integer', 'min:1'],
            'exhibits.*.number_of_items' => ['nullable', 'integer', 'min:0'],
            'exhibits.*.item_description' => ['nullable', 'string', 'max:1000'],
            'exhibits.*.suspected_item' => ['nullable', 'string', 'max:255'],

            'suspects' => ['nullable', 'array'],
            'suspects.*.serial_number' => ['nullable', 'integer', 'min:1'],
            'suspects.*.first_name' => ['nullable', 'string', 'max:255'],
            'suspects.*.middle_name' => ['nullable', 'string', 'max:255'],
            'suspects.*.last_name' => ['nullable', 'string', 'max:255'],
            'suspects.*.sex' => ['nullable', 'string', 'max:50'],
            'suspects.*.date_of_birth' => ['nullable', 'date'],
            'suspects.*.nationality' => ['nullable', 'string', 'max:255'],
            'suspects.*.id_passport_number' => ['nullable', 'string', 'max:255'],

            'supporting_document_template_ids' => ['nullable', 'array'],
            'supporting_document_template_ids.*' => ['integer', 'exists:supporting_document_templates,id'],
        ];
    }
}
