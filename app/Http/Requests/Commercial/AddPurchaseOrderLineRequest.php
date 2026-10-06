<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;

class AddPurchaseOrderLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_AMEND);
    }

    /**
     * Rules for one PO line, keyed under the given prefix (e.g. "line" or "lines.*").
     *
     * @return array<string, list<string>>
     */
    public static function lineRules(string $prefix): array
    {
        return [
            "{$prefix}.description" => ['required', 'string', 'max:500'],
            "{$prefix}.ordered_qty" => ['required', 'integer', 'min:1', 'max:10000000'],
            "{$prefix}.unit_price_gross" => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            "{$prefix}.notify_remaining_qty" => ['nullable', 'integer', 'min:0'],
            "{$prefix}.sample_type_id" => ['nullable', 'uuid'],
            "{$prefix}.analysis_type_ids" => ['nullable', 'array'],
            "{$prefix}.analysis_type_ids.*" => ['uuid'],
            "{$prefix}.analysis_element_ids" => ['nullable', 'array'],
            "{$prefix}.analysis_element_ids.*" => ['uuid'],
            "{$prefix}.is_package" => ['nullable', 'boolean'],
            "{$prefix}.quotation_detail_id" => ['nullable', 'uuid', 'exists:quotation_details,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function lineMessages(string $prefix): array
    {
        return [
            "{$prefix}.description.required" => 'Each line needs a description.',
            "{$prefix}.ordered_qty.required" => 'Enter the ordered quantity.',
            "{$prefix}.ordered_qty.min" => 'The ordered quantity must be at least 1.',
            "{$prefix}.unit_price_gross.required" => 'Enter the unit price (VAT inclusive).',
            "{$prefix}.unit_price_gross.min" => 'The unit price cannot be negative.',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return array_merge(self::lineRules('line'), [
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(self::lineMessages('line'), [
            'reason.required' => 'Give a reason for adding this line.',
        ]);
    }
}
