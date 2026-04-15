<?php

namespace App\Http\Controllers\Modules\Documents\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InventoryRiskDashboardController extends Controller
{
    /**
     * Show Inventory Risk Dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        
        $cacheKey = 'dashboard_inventory_risk_' . implode('_', array_values($filters));
        $data = Cache::remember($cacheKey, 600, function () use ($filters) {
            return [
                'riskMetrics' => $this->getRiskMetrics($filters),
                'expiryRisk' => $this->getExpiryRisk($filters),
                'deadStock' => $this->getDeadStock($filters),
                'slowMovingStock' => $this->getSlowMovingStock($filters),
                'stockoutRisk' => $this->getStockoutRisk($filters),
                'reorderExceptions' => $this->getReorderExceptions($filters),
                'stockTakeVariance' => $this->getStockTakeVariance($filters),
                'inventoryValue' => $this->getInventoryValue($filters),
            ];
        });

        return view('documents.dashboards.inventory-risk', $data);
    }

    /**
     * Get Risk Metrics (Expiry, Dead Stock, Stockout, Value at Risk)
     */
    private function getRiskMetrics(array $filters): array
    {
        $totalItems = DB::table('inventory_items')->count();
        $expiryRisk = $this->countExpiryRisk();
        $deadStockCount = $this->countDeadStock();
        $stockoutRiskCount = $this->countStockoutRisk();
        $valueAtRisk = $this->calculateValueAtRisk();

        return [
            'totalItems' => $totalItems,
            'expiryRiskCount' => $expiryRisk,
            'deadStockCount' => $deadStockCount,
            'stockoutRiskCount' => $stockoutRiskCount,
            'valueAtRisk' => $valueAtRisk,
            'riskScore' => $this->calculateOverallRiskScore($filters),
        ];
    }

    /**
     * Get expiry risk items (next 30 days)
     */
    private function getExpiryRisk(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.batch_code',
                'inventory_items.expiry_date',
                'inventory_items.quantity_in_stock',
                'inventory_sub_categories.sub_category_name',
                DB::raw('EXTRACT(EPOCH FROM (inventory_items.expiry_date - NOW()))/86400 as days_until_expiry')
            )
            ->where('inventory_items.expiry_date', '>', now())
            ->where('inventory_items.expiry_date', '<=', now()->addDays(30))
            ->where('inventory_items.status', '!=', 'Disposed')
            ->orderBy('inventory_items.expiry_date')
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'batchCode' => $row->batch_code,
                'expiryDate' => $row->expiry_date,
                'daysUntilExpiry' => round($row->days_until_expiry, 0),
                'quantity' => $row->quantity_in_stock,
                'category' => $row->sub_category_name,
                'urgency' => $row->days_until_expiry <= 7 ? 'Critical' : ($row->days_until_expiry <= 14 ? 'High' : 'Medium'),
            ])
            ->toArray();
    }

    /**
     * Get dead stock (no movement in 90+ days)
     */
    private function getDeadStock(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.quantity_in_stock',
                'inventory_items.unit_cost',
                'inventory_items.last_movement_date',
                'inventory_sub_categories.sub_category_name',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_movement_date))/86400 as days_since_movement'),
                DB::raw('inventory_items.quantity_in_stock * inventory_items.unit_cost as value_at_risk')
            )
            ->where('inventory_items.last_movement_date', '<=', now()->subDays(90))
            ->where('inventory_items.status', '!=', 'Disposed')
            ->orderByDesc(DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_movement_date))/86400'))
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'quantity' => $row->quantity_in_stock,
                'unitCost' => $row->unit_cost,
                'valueAtRisk' => round($row->value_at_risk ?? 0, 2),
                'daysSinceMovement' => round($row->days_since_movement, 0),
                'category' => $row->sub_category_name,
            ])
            ->toArray();
    }

    /**
     * Get slow-moving stock (30-90 days no movement)
     */
    private function getSlowMovingStock(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.quantity_in_stock',
                'inventory_items.last_movement_date',
                'inventory_sub_categories.sub_category_name',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_movement_date))/86400 as days_since_movement')
            )
            ->whereBetween(DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_movement_date))/86400'), [30, 90])
            ->where('inventory_items.status', '!=', 'Disposed')
            ->orderByDesc(DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_movement_date))/86400'))
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'quantity' => $row->quantity_in_stock,
                'daysSinceMovement' => round($row->days_since_movement, 0),
                'category' => $row->sub_category_name,
            ])
            ->toArray();
    }

    /**
     * Get stockout risk (below reorder level)
     */
    private function getStockoutRisk(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.quantity_in_stock',
                'inventory_items.reorder_level',
                'inventory_items.reorder_quantity',
                'inventory_sub_categories.sub_category_name',
                DB::raw('inventory_items.reorder_level - inventory_items.quantity_in_stock as shortfall')
            )
            ->whereRaw('inventory_items.quantity_in_stock < inventory_items.reorder_level')
            ->where('inventory_items.status', '!=', 'Disposed')
            ->orderByDesc(DB::raw('inventory_items.reorder_level - inventory_items.quantity_in_stock'))
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'currentStock' => $row->quantity_in_stock,
                'reorderLevel' => $row->reorder_level,
                'reorderQuantity' => $row->reorder_quantity,
                'shortfall' => $row->shortfall,
                'category' => $row->sub_category_name,
            ])
            ->toArray();
    }

    /**
     * Get reorder exceptions
     */
    private function getReorderExceptions(array $filters): array
    {
        return DB::table('inventory_items')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.quantity_in_stock',
                'inventory_items.reorder_level',
                'inventory_items.reorder_quantity',
                'inventory_items.last_reorder_date',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_reorder_date))/86400 as days_since_reorder')
            )
            ->whereRaw('
                inventory_items.quantity_in_stock < inventory_items.reorder_level 
                OR (inventory_items.quantity_in_stock > (inventory_items.reorder_level * 2) 
                    AND inventory_items.quantity_in_stock < inventory_items.reorder_quantity)
            ')
            ->where('inventory_items.status', '!=', 'Disposed')
            ->orderByDesc(DB::raw('EXTRACT(EPOCH FROM (NOW() - inventory_items.last_reorder_date))/86400'))
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'currentStock' => $row->quantity_in_stock,
                'reorderLevel' => $row->reorder_level,
                'daysSinceLastReorder' => round($row->days_since_reorder ?? 0, 0),
                'exceptionType' => $row->quantity_in_stock < $row->reorder_level ? 'Below Reorder Level' : 'Anomaly',
            ])
            ->toArray();
    }

    /**
     * Get stock-take variance
     */
    private function getStockTakeVariance(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_items.id',
                'inventory_items.item_name',
                'inventory_items.physical_count',
                'inventory_items.system_count',
                'inventory_items.last_stock_take_date',
                'inventory_sub_categories.sub_category_name',
                DB::raw('inventory_items.system_count - inventory_items.physical_count as variance_quantity'),
                DB::raw('CASE WHEN inventory_items.system_count > 0 THEN ((inventory_items.system_count - inventory_items.physical_count) / inventory_items.system_count * 100) ELSE 0 END as variance_percentage')
            )
            ->whereNotNull('inventory_items.last_stock_take_date')
            ->whereRaw('inventory_items.system_count != inventory_items.physical_count')
            ->orderByDesc(DB::raw('ABS(CASE WHEN inventory_items.system_count > 0 THEN ((inventory_items.system_count - inventory_items.physical_count) / inventory_items.system_count * 100) ELSE 0 END)'))
            ->limit(30)
            ->get()
            ->map(fn($row) => [
                'itemId' => $row->id,
                'itemName' => $row->item_name,
                'physicalCount' => $row->physical_count,
                'systemCount' => $row->system_count,
                'varianceQuantity' => $row->variance_quantity,
                'variancePercentage' => round($row->variance_percentage ?? 0, 2),
                'lastStockTake' => $row->last_stock_take_date,
                'category' => $row->sub_category_name,
                'severity' => abs($row->variance_percentage ?? 0) > 10 ? 'Critical' : (abs($row->variance_percentage ?? 0) > 5 ? 'High' : 'Low'),
            ])
            ->toArray();
    }

    /**
     * Get inventory value by category
     */
    private function getInventoryValue(array $filters): array
    {
        return DB::table('inventory_items')
            ->join('inventory_sub_categories', 'inventory_items.inventory_sub_category_id', '=', 'inventory_sub_categories.id')
            ->select(
                'inventory_sub_categories.sub_category_name',
                DB::raw('SUM(inventory_items.quantity_in_stock * inventory_items.unit_cost) as total_value'),
                DB::raw('COUNT(inventory_items.id) as item_count')
            )
            ->where('inventory_items.status', '!=', 'Disposed')
            ->groupBy('inventory_sub_categories.sub_category_name')
            ->orderByDesc(DB::raw('SUM(inventory_items.quantity_in_stock * inventory_items.unit_cost)'))
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'category' => $row->sub_category_name,
                'totalValue' => round($row->total_value ?? 0, 2),
                'itemCount' => $row->item_count,
            ])
            ->toArray();
    }

    /**
     * Count expiry risk items
     */
    private function countExpiryRisk(): int
    {
        return DB::table('inventory_items')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'Disposed')
            ->count();
    }

    /**
     * Count dead stock
     */
    private function countDeadStock(): int
    {
        return DB::table('inventory_items')
            ->where('last_movement_date', '<=', now()->subDays(90))
            ->where('status', '!=', 'Disposed')
            ->count();
    }

    /**
     * Count stockout risk
     */
    private function countStockoutRisk(): int
    {
        return DB::table('inventory_items')
            ->whereRaw('quantity_in_stock < reorder_level')
            ->where('status', '!=', 'Disposed')
            ->count();
    }

    /**
     * Calculate value at risk (dead stock + expiry risk)
     */
    private function calculateValueAtRisk(): float
    {
        $result = DB::table('inventory_items')
            ->select(
                DB::raw('SUM(inventory_items.quantity_in_stock * inventory_items.unit_cost) as total_value')
            )
            ->where(function ($query) {
                $query->where('last_movement_date', '<=', now()->subDays(90))
                    ->orWhere(function($q) {
                        $q->where('expiry_date', '>', now())
                          ->where('expiry_date', '<=', now()->addDays(30));
                    });
            })
            ->where('status', '!=', 'Disposed')
            ->first();

        return $result->total_value ?? 0;
    }

    /**
     * Calculate overall risk score
     */
    private function calculateOverallRiskScore(array $filters): float
    {
        $expiryRisk = $this->countExpiryRisk();
        $deadStock = $this->countDeadStock();
        $stockoutRisk = $this->countStockoutRisk();
        
        $totalItems = DB::table('inventory_items')->count() ?: 1;
        
        $score = 100 - (
            (($expiryRisk / $totalItems) * 20) +
            (($deadStock / $totalItems) * 40) +
            (($stockoutRisk / $totalItems) * 40)
        );

        return max(0, min(100, $score));
    }

    /**
     * Parse filters from request
     */
    private function parseFilters(Request $request): array
    {
        return [
            'category' => $request->input('category', null),
            'status' => $request->input('status', null),
            'risk_level' => $request->input('risk_level', null),
        ];
    }

    /**
     * Export Inventory Risk Dashboard
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        $filters = $this->parseFilters($request);
        
        $data = [
            'riskMetrics' => $this->getRiskMetrics($filters),
            'expiryRisk' => $this->getExpiryRisk($filters),
            'deadStock' => $this->getDeadStock($filters),
            'stockoutRisk' => $this->getStockoutRisk($filters),
            'stockTakeVariance' => $this->getStockTakeVariance($filters),
        ];

        return response()->json($data);
    }
}
