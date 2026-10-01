<?php

namespace App\Http\Requests\Sampleworkflow;

use App\Services\Sampleworkflow\CollectionQrCodeService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionQrExtrasRequest extends FormRequest
{
    /**
     * Route middleware already restricts access to the TRF / planner module.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CollectionQrCodeService::MAX_EXTRAS_PER_REQUEST],
            'layout' => ['nullable', 'in:a4,thermal'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Enter how many extra QR codes to add.',
            'quantity.integer' => 'The number of extra QR codes must be a whole number.',
            'quantity.min' => 'Add at least one extra QR code.',
            'quantity.max' => 'You can add at most '.CollectionQrCodeService::MAX_EXTRAS_PER_REQUEST.' extra QR codes at a time.',
            'layout.in' => 'Choose either the A4 sheet or the thermal label layout.',
            'copies.min' => 'Print at least one copy of each QR code.',
            'copies.max' => 'You can print at most 10 copies of each QR code.',
        ];
    }
}
