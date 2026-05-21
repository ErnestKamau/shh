<?php

namespace App\Http\Requests\Api\Dashboard;

use App\Http\Requests\Api\Portal\PortalPaginatedRequest;

class DashboardPaginatedRequest extends PortalPaginatedRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }

    public function page(): int
    {
        return (int) $this->input('page', 1);
    }
}
