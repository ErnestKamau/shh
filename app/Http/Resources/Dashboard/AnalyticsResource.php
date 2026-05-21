<?php

namespace App\Http\Resources\Dashboard;

use App\DTOs\Dashboard\AnalyticsDTO;
use App\Transformers\DashboardTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsResource extends JsonResource
{
    public function __construct(AnalyticsDTO $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return app(DashboardTransformer::class)->transformAnalytics($this->resource);
    }
}
