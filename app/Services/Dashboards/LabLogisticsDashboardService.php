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

        $totalBuffers   = $buffers->count();
        $healthyBuffers = $buffers->where('is_low', false)->count();

        // Restock compliance: % of buffers that are at or above minimum level
        $restockCompliance = $totalBuffers > 0
            ? round(($healthyBuffers / $totalBuffers) * 100)
            : 100;

        // Prep frequency: movements in last 30 days
        $movementCount   = $movements->count();
        $prepFrequency   = $movementCount > 0 ? "{$movementCount} preps / 30 days" : 'No activity recorded';
        $prepPercentage  = min(100, $movementCount * 10); // 10 movements = 100%

        // Waste factor: estimate from over-stocked buffers (qty > 2× min_level)
        $overStocked = $buffers->filter(fn($b) => $b['min_level'] > 0 && $b['current_qty'] > $b['min_level'] * 2)->count();
        $wasteFactor = $totalBuffers > 0 ? round(($overStocked / $totalBuffers) * 10, 1) : 0;

        // AI suggestion: simple rule-based insight
        if ($totalBuffers === 0) {
            $aiSuggestion = 'No buffer stock data is currently tracked. Add items to the lab buffer registry to begin monitoring.';
        } elseif ($restockCompliance < 50) {
            $aiSuggestion = '<strong>Action required:</strong> More than half of your buffers are below minimum levels. Initiate an urgent requisition.';
        } elseif ($restockCompliance < 80) {
            $aiSuggestion = 'Several buffers are running low. Review low-stock items and schedule restocking within the next 48 hours.';
        } else {
            $aiSuggestion = 'Stock levels are within acceptable ranges. Continue monitoring weekly to maintain operational continuity.';
        }

        return [
            'buffers'            => $buffers,
            'low_stock_count'    => $buffers->where('is_low', true)->count(),
            'recent_movements'   => $movements,
            'restock_compliance' => $restockCompliance,
            'prep_frequency'     => $prepFrequency,
            'prep_percentage'    => $prepPercentage,
            'waste_factor'       => $wasteFactor,
            'ai_suggestion'      => $aiSuggestion,
        ];
    }
}
