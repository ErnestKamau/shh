<?php

namespace App\Services\Dashboards;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LabLogisticsDashboardService
{
    /**
     * Get Lab Logistics (Buffer stock and consumables).
     */
    public function getLabLogisticsSummary(): array
    {
        // 1. Dynamic Buffer Mapping (Primary source: Inventory Sub-Categories marked as Lab)
        $buffers = DB::table('inventory_sub_categories as isc')
            ->where('isc.is_lab', 1)
            ->where('isc.active', 1)
            ->select([
                'isc.id', 
                'isc.name', 
                'isc.code', 
                'isc.minimum_level',
                DB::raw('(SELECT SUM(stock_in - stock_out) FROM inventory_items WHERE inventory_sub_category_id = isc.id) as current_qty')
            ])
            ->get()
            ->map(function($b) {
                $qty = (float) ($b->current_qty ?? 0);
                $min = (float) ($b->minimum_level ?? 0);
                return [
                    'name' => $b->name,
                    'code' => $b->code,
                    'min_level' => $min,
                    'current_qty' => $qty,
                    'is_low' => $qty < $min
                ];
            });

        // 2. Recent Movements (Consumption logs from inventory_items)
        $movements = DB::table('inventory_items as ii')
            ->join('inventory_sub_categories as isc', 'isc.id', '=', 'ii.inventory_sub_category_id')
            ->leftJoin('users as u', 'u.id', '=', 'ii.created_by')
            ->where('ii.created_at', '>=', now()->subDays(30))
            ->where('ii.stock_out', '>', 0)
            ->select([
                'isc.name as buffer_name', 
                'ii.batch_code', 
                'ii.stock_out as quantity', 
                'ii.created_at', 
                'u.name as staff_name'
            ])
            ->orderByDesc('ii.created_at')
            ->limit(10)
            ->get();

        // 3. Calculate Real-time KPIs
        $lowStockCount = $buffers->where('is_low', true)->count();
        $totalItems = $buffers->count();

        // Restock Compliance: % of items above threshold
        $restockCompliance = $totalItems > 0 
            ? round((($totalItems - $lowStockCount) / $totalItems) * 100) 
            : 100;

        // Prep Frequency: Consumption volume trend
        $recentConsumptionVolume = (int) DB::table('inventory_items')->where('created_at', '>=', now()->subDays(7))->where('stock_out', '>', 0)->count();
        $prepFrequencyLabel = $recentConsumptionVolume > 20 ? 'High' : ($recentConsumptionVolume > 5 ? 'Moderate' : 'Low');
        $prepFrequencyPct = min(100, $recentConsumptionVolume * 4); // Scaled for UI progress bar

        // Waste Factor: Percentage of expired vs consumed batches
        $expiredCount = DB::table('inventory_items')->where('expiry', '<', now())->count();
        $consumedCount = DB::table('inventory_items')->where('stock_out', '>', 0)->count();
        $wasteFactor = $consumedCount > 0 ? round(($expiredCount / $consumedCount) * 100, 1) : 0;

        return [
            'buffers' => $buffers,
            'low_stock_count' => $lowStockCount,
            'recent_movements' => $movements,
            'restock_compliance' => $restockCompliance,
            'prep_frequency' => $prepFrequencyLabel,
            'prep_percentage' => $prepFrequencyPct,
            'waste_factor' => $wasteFactor,
            'ai_suggestion' => $this->generateAiSuggestion($buffers->where('is_low', true))
        ];
    }

    /**
     * Internal rule-based generator for stock suggestions.
     */
    private function generateAiSuggestion($lowStockItems): string
    {
        if ($lowStockItems->isEmpty()) {
            return "Inventory levels are stable. No immediate procurement actions required.";
        }

        $mostCritical = $lowStockItems->sortBy('current_qty')->first();
        $itemCount = $lowStockItems->count();

        if ($itemCount === 1) {
            return "Stock for <strong>" . $mostCritical['name'] . "</strong> is critically low (" . $mostCritical['current_qty'] . "). Recommend generating a purchase requisition immediately.";
        }

        return "<strong>$itemCount items</strong> are below threshold. Critical attention needed for <strong>" . $mostCritical['name'] . "</strong>. Suggest consolidated procurement for all low-stock lab supplies.";
    }
}
