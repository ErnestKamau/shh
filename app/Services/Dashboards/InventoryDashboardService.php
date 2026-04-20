<?php

namespace App\Services\Dashboards;

use App\InventoryStore;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventoryDashboardService
{
    use DashboardHelpers;

    public function getInventoryRiskBoard($expiryThresholdDays = 30): array
    {
        try {
            $expiryThresholdDays = (int) $expiryThresholdDays;
            
            $positionRows = DB::connection('mysql')->table('inventory_items as i')
                ->join('inventory_sub_categories as sc', 'sc.id', '=', 'i.inventory_sub_category_id')
                ->join('inventory_stores as s', 's.id', '=', 'i.inventory_store_id')
                ->select([
                    'sc.id as inventory_sub_category_id',
                    's.id as inventory_store_id',
                    'sc.name as item_name',
                    'sc.code as item_code',
                    DB::raw('SUM(i.stock_in - i.stock_out) as available_qty'),
                    DB::raw('0 as pending_qty'),
                    'sc.minimum_level',
                    DB::raw('(SUM(i.stock_in - i.stock_out) < sc.minimum_level) as below_minimum'),
                    DB::raw('SUM(CASE WHEN i.expiry < CURDATE() THEN (i.stock_in - i.stock_out) ELSE 0 END) as expired_qty'),
                    DB::raw("SUM(CASE WHEN i.expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$expiryThresholdDays} DAY) THEN (i.stock_in - i.stock_out) ELSE 0 END) as near_expiry_qty"),
                    DB::raw('MAX(i.updated_at) as refreshed_at')
                ])
                ->groupBy('sc.id', 's.id', 'sc.name', 'sc.code', 'sc.minimum_level')
                ->get();

            $stores = InventoryStore::pluck('name', 'id');

            $normalizedPositions = collect($positionRows)->map(function ($row) use ($stores) {
                return [
                    'inventory_sub_category_id' => $row->inventory_sub_category_id,
                    'inventory_store_id' => $row->inventory_store_id,
                    'store_name' => $stores[$row->inventory_store_id] ?? ('Store #' . ($row->inventory_store_id ?? 'N/A')),
                    'item_name' => $row->item_name ?: ('Item #' . ($row->inventory_sub_category_id ?? 'N/A')),
                    'item_code' => $row->item_code ?: 'n/a',
                    'available_qty' => $this->toFloat($row->available_qty) ?? 0,
                    'pending_qty' => $this->toFloat($row->pending_qty) ?? 0,
                    'minimum_level' => $this->toFloat($row->minimum_level),
                    'below_minimum' => (bool) $row->below_minimum,
                    'expired_qty' => $this->toFloat($row->expired_qty) ?? 0,
                    'near_expiry_qty' => $this->toFloat($row->near_expiry_qty) ?? 0,
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $normalizedStoreRows = $normalizedPositions->groupBy('inventory_store_id')->map(function ($rows, $storeId) use ($stores) {
                return [
                    'inventory_store_id' => $storeId,
                    'store_name' => $stores[$storeId] ?? ('Store #' . ($storeId ?? 'N/A')),
                    'items_below_minimum' => $rows->where('below_minimum', true)->count(),
                    'items_near_expiry' => $rows->filter(fn ($r) => $r['near_expiry_qty'] > 0)->count(),
                    'items_expired' => $rows->filter(fn ($r) => $r['expired_qty'] > 0)->count(),
                    'total_available_qty' => round($rows->sum('available_qty'), 2),
                    'refreshed_at' => $rows->max('refreshed_at'),
                ];
            })->sortByDesc('items_below_minimum')->sortByDesc('items_expired')->values();

            $summary = [
                'tracked_items' => (int) $normalizedPositions->count(),
                'items_below_minimum' => (int) $normalizedPositions->where('below_minimum', true)->count(),
                'items_near_expiry' => (int) $normalizedPositions->filter(fn ($row) => $row['near_expiry_qty'] > 0)->count(),
                'items_expired' => (int) $normalizedPositions->filter(fn ($row) => $row['expired_qty'] > 0)->count(),
                'total_available_qty' => round((float) $normalizedPositions->sum('available_qty'), 2),
            ];

            $priorityItems = $normalizedPositions
                ->sort(function ($left, $right) {
                    if ($left['below_minimum'] !== $right['below_minimum']) {
                        return $left['below_minimum'] ? -1 : 1;
                    }
                    if ($left['expired_qty'] !== $right['expired_qty']) {
                        return $right['expired_qty'] <=> $left['expired_qty'];
                    }
                    return $right['near_expiry_qty'] <=> $left['near_expiry_qty'];
                })
                ->take(10)
                ->values();

            return [
                'available' => true,
                'message' => null,
                'summary' => $summary,
                'store_rows' => $normalizedStoreRows->all(),
                'priority_items' => $priorityItems->all(),
                'refreshed_at' => $normalizedStoreRows->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load inventory risk board: ' . $exception->getMessage());

            return $this->emptyInventoryBoard('Inventory risk data is unavailable.');
        }
    }

    protected function emptyInventoryBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Inventory risk data is unavailable.',
            'summary' => [
                'tracked_items' => 0,
                'items_below_minimum' => 0,
                'items_near_expiry' => 0,
                'items_expired' => 0,
                'total_available_qty' => 0,
            ],
            'store_rows' => [],
            'priority_items' => [],
            'refreshed_at' => null,
        ];
    }
}
