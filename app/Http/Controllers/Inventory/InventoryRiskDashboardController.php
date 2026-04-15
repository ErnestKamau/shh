<?php

namespace App\Http\Controllers\Inventory;

use App\InventoryItem;
use App\InventorySubCategories;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryRiskDashboardController extends Controller
{
    protected $mysqlConnection = null; // Default MySQL ground truth

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Inventory Risk board
     */
    public function index()
    {
        $data = [
            'nearExpiryStock' => $this->getNearExpiryStock(),
            'deadStock' => $this->getDeadStock(),
            'slowMovingStock' => $this->getSlowMovingStock(),
            'stockoutRisk' => $this->getStockoutRisk(),
            'reorderExceptions' => $this->getReorderExceptions(),
            'stockTakeVariance' => $this->getStockTakeVariance(),
            'inventoryMetrics' => $this->getInventoryMetrics(),
        ];

        return view('inventory.dashboards.risk', $data);
    }

    /**
     * Near-expiry stock (next 30 days)
     */
    private function getNearExpiryStock()
    {
        try {
            $nearExpiry = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->where('days_until_expiry', '>', 0)
                ->where('days_until_expiry', '<=', 30)
                ->selectRaw('
                    item_id,
                    item_name,
                    batch_code,
                    expiry_date,
                    days_until_expiry,
                    quantity_in_stock,
                    location_name
                ')
                ->orderBy('days_until_expiry')
                ->limit(50)
                ->get();

            return $nearExpiry->map(function ($row) {
                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'batchCode' => $row->batch_code,
                    'expiryDate' => $row->expiry_date ? Carbon::parse($row->expiry_date)->format('M d, Y') : 'N/A',
                    'daysUntilExpiry' => (int)$row->days_until_expiry,
                    'quantity' => (float)$row->quantity_in_stock,
                    'location' => $row->location_name,
                    'urgency' => $row->days_until_expiry <= 7 ? 'critical' : ($row->days_until_expiry <= 14 ? 'high' : 'medium'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load near-expiry stock: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dead stock (no movement in 90+ days)
     */
    private function getDeadStock()
    {
        try {
            $deadStock = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->where('days_since_last_movement', '>=', 90)
                ->selectRaw('
                    item_id,
                    item_name,
                    quantity_in_stock,
                    value_at_risk,
                    days_since_last_movement,
                    location_name
                ')
                ->orderByDesc('days_since_last_movement')
                ->limit(30)
                ->get();

            return $deadStock->map(function ($row) {
                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'quantity' => (float)$row->quantity_in_stock,
                    'valueAtRisk' => (float)$row->value_at_risk,
                    'daysSinceMovement' => (int)$row->days_since_last_movement,
                    'location' => $row->location_name,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load dead stock: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Slow-moving stock (30-90 days no movement)
     */
    private function getSlowMovingStock()
    {
        try {
            $slowMoving = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->whereBetween('days_since_last_movement', [30, 90])
                ->selectRaw('
                    item_id,
                    item_name,
                    quantity_in_stock,
                    days_since_last_movement,
                    location_name
                ')
                ->orderByDesc('days_since_last_movement')
                ->limit(30)
                ->get();

            return $slowMoving->map(function ($row) {
                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'quantity' => (float)$row->quantity_in_stock,
                    'daysSinceMovement' => (int)$row->days_since_last_movement,
                    'location' => $row->location_name,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load slow-moving stock: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Stockout risk (below reorder level)
     */
    private function getStockoutRisk()
    {
        try {
            $stockoutRisk = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->whereRaw('quantity_in_stock < reorder_level')
                ->selectRaw('
                    item_id,
                    item_name,
                    quantity_in_stock,
                    reorder_level,
                    reorder_quantity,
                    location_name
                ')
                ->orderByDesc('reorder_level')
                ->limit(30)
                ->get();

            return $stockoutRisk->map(function ($row) {
                $shortfall = (float)$row->reorder_level - (float)$row->quantity_in_stock;

                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'currentStock' => (float)$row->quantity_in_stock,
                    'reorderLevel' => (float)$row->reorder_level,
                    'reorderQuantity' => (float)$row->reorder_quantity,
                    'shortfall' => $shortfall,
                    'location' => $row->location_name,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load stockout risk: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reorder exceptions
     */
    private function getReorderExceptions()
    {
        try {
            $exceptions = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->selectRaw('
                    item_id,
                    item_name,
                    quantity_in_stock,
                    reorder_level,
                    reorder_quantity,
                    last_reorder_date,
                    days_since_last_reorder
                ')
                ->whereRaw('
                    quantity_in_stock < reorder_level 
                    OR (quantity_in_stock > (reorder_level * 2) AND quantity_in_stock < (reorder_quantity))
                ')
                ->orderByDesc('days_since_last_reorder')
                ->limit(30)
                ->get();

            return $exceptions->map(function ($row) {
                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'currentStock' => (float)$row->quantity_in_stock,
                    'reorderLevel' => (float)$row->reorder_level,
                    'daysSinceLastReorder' => (int)$row->days_since_last_reorder,
                    'exceptionType' => (float)$row->quantity_in_stock < (float)$row->reorder_level ? 'Below Reorder' : 'Anomaly',
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load reorder exceptions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Stock-take variance
     */
    private function getStockTakeVariance()
    {
        try {
            $variance = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->selectRaw('
                    item_id,
                    item_name,
                    physical_count,
                    system_count,
                    variance_quantity,
                    variance_percentage,
                    last_stock_take_date,
                    location_name
                ')
                ->whereNotNull('variance_percentage')
                ->orderByDesc('variance_percentage')
                ->limit(30)
                ->get();

            return $variance->map(function ($row) {
                $absVariance = abs((float)$row->variance_percentage);
                $severity = $absVariance > 10 ? 'critical' : ($absVariance > 5 ? 'high' : 'low');

                return [
                    'itemId' => $row->item_id,
                    'itemName' => $row->item_name,
                    'physicalCount' => (float)$row->physical_count,
                    'systemCount' => (float)$row->system_count,
                    'varianceQuantity' => (float)$row->variance_quantity,
                    'variancePercentage' => (float)$row->variance_percentage,
                    'lastStockTake' => $row->last_stock_take_date ? Carbon::parse($row->last_stock_take_date)->format('M d, Y') : 'N/A',
                    'location' => $row->location_name,
                    'severity' => $severity,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load stock-take variance: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Overall inventory metrics
     */
    private function getInventoryMetrics()
    {
        try {
            $metrics = DB::connection($this->mysqlConnection)
                ->table('v_inventory_risk_detail')
                ->selectRaw('
                    COUNT(DISTINCT item_id) as total_items,
                    COUNT(DISTINCT CASE WHEN days_until_expiry > 0 AND days_until_expiry <= 30 THEN item_id END) as near_expiry_count,
                    COUNT(DISTINCT CASE WHEN days_since_last_movement >= 90 THEN item_id END) as dead_stock_count,
                    COUNT(DISTINCT CASE WHEN quantity_in_stock < reorder_level THEN item_id END) as stockout_risk_count,
                    ROUND(SUM(value_at_risk), 2) as total_value_at_risk
                ')
                ->first();

            return [
                'totalItems' => (int)$metrics->total_items,
                'nearExpiryCount' => (int)$metrics->near_expiry_count,
                'deadStockCount' => (int)$metrics->dead_stock_count,
                'stockoutRiskCount' => (int)$metrics->stockout_risk_count,
                'totalValueAtRisk' => (float)$metrics->total_value_at_risk,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load inventory metrics: ' . $e->getMessage());
            return [];
        }
    }
}
