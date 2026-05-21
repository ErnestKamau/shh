<?php

namespace App\Transformers;

use App\DTOs\Dashboard\AnalyticsDTO;
use App\DTOs\Dashboard\DashboardDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class DashboardTransformer
{
    /**
     * @return array<string, mixed>
     */
    public function transformDashboard(DashboardDTO $dashboard): array
    {
        return $dashboard->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function transformAnalytics(AnalyticsDTO $analytics): array
    {
        return $analytics->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function transformPaginated(LengthAwarePaginator $paginator, string $resourceKey): array
    {
        $items = collect($paginator->items())->map(function ($item) {
            return is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item;
        })->all();

        return [
            $resourceKey => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
