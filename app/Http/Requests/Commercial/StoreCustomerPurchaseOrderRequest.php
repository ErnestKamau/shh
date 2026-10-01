<?php

namespace App\Http\Requests\Commercial;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderInvoicingPeriod;
use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_CREATE);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return array_merge([
            'po_number' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'uuid', 'exists:crm_customers,id'],
            'quotation_header_id' => ['nullable', 'uuid', 'exists:quotation_headers,id'],
            'currency_id' => ['nullable', 'uuid', 'exists:currencies,id'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
            'invoicing_mode' => ['required', Rule::enum(PurchaseOrderInvoicingMode::class)],
            'invoicing_period' => ['nullable', 'required_if:invoicing_mode,'.PurchaseOrderInvoicingMode::Periodic->value, Rule::enum(PurchaseOrderInvoicingPeriod::class)],
            'expiry_notice_days' => ['required', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'lines' => ['required', 'array', 'min:1', 'max:200'],
        ], AddPurchaseOrderLineRequest::lineRules('lines.*'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge([
            'po_number.required' => 'Enter the customer\'s PO number.',
            'customer_id.required' => 'Choose the customer this PO belongs to.',
            'valid_from.required' => 'Enter the date the PO starts.',
            'valid_to.required' => 'Enter the date the PO expires.',
            'valid_to.after_or_equal' => 'The expiry date must be on or after the start date.',
            'invoicing_period.required_if' => 'Choose how often periodic invoices are raised.',
            'file.mimes' => 'The PO file must be a PDF, JPEG or PNG.',
            'file.max' => 'The PO file must not be larger than 10 MB.',
            'lines.required' => 'Add at least one line to the purchase order.',
            'lines.min' => 'Add at least one line to the purchase order.',
        ], AddPurchaseOrderLineRequest::lineMessages('lines.*'));
    }
}
