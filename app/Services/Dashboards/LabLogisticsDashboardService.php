<?php

namespace App\Services\Dashboards;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LabLogisticsDashboardService
{
    /**
     * Get Lab Logistics (Buffer stock and consumables).
     */
    public function getLabLogisticsSummary(): array
    {
        $buffers = collect();
        $movements = collect();

        if (Schema::hasTable('lab_buffers')) {
            $buffers = DB::table('lab_buffers')->where('is_active', 1)->get()->map(function($b) {
                return [
                    'name' => $b->name,
                    'code' => $b->code,
                    'min_level' => (float) $b->min_level,
                    'current_qty' => (float) $b->current_qty,
                    'is_low' => (float) $b->current_qty < (float) $b->min_level
                ];
            });
        }

        if (Schema::hasTable('lab_buffer_movements')) {
            $movements = DB::table('lab_buffer_movements')
                ->where('created_at', '>=', now()->subDays(30))
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        return [
            'buffers' => $buffers,
            'low_stock_count' => $buffers->where('is_low', true)->count(),
            'recent_movements' => $movements
        ];
    }
}
