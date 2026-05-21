<?php

namespace App\Http\Resources\Dashboard;

use App\DTOs\Dashboard\DashboardDTO;
use App\Transformers\DashboardTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function __construct(DashboardDTO $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return app(DashboardTransformer::class)->transformDashboard($this->resource);
    }
}
