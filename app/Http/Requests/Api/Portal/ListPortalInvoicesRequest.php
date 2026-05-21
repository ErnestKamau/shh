<?php

namespace App\Http\Requests\Api\Portal;

class ListPortalInvoicesRequest extends PortalPaginatedRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string', 'in:paid,unpaid,partial'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    /**
     * @return array{status?: string, date_from?: string, date_to?: string}
     */
    public function filters(): array
    {
        return array_filter([
            'status' => $this->input('status'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
        ], fn ($value) => $value !== null && $value !== '');
    }
}
