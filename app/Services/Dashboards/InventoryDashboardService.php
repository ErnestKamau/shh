<?php

namespace App\Services\Dashboards;

use App\InventoryItem;
use App\InventoryStore;
use App\RequestEntity;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use App\StockTaking;
use Carbon\Carbon;
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
            
            $connection = DB::connection(config('imara_ai.source_connection', config('database.default')));

            $positionRows = $connection->table('inventory_items as i')
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
                    DB::raw('SUM(CASE WHEN i.expiry < CURRENT_DATE THEN (i.stock_in - i.stock_out) ELSE 0 END) as expired_qty'),
                    DB::raw("SUM(CASE WHEN i.expiry BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '{$expiryThresholdDays} days') THEN (i.stock_in - i.stock_out) ELSE 0 END) as near_expiry_qty"),
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

    /**
     * Location-scoped operational widgets for the inventory home dashboard.
     *
     * @return array{
     *     on_hand_lots: int,
     *     expired_lots: int,
     *     expiring_lots: int,
     *     low_stock_count: int,
     *     pipeline: array<int, array{stage: string, label: string, count: int}>,
     *     expiring: array<int, array<string, mixed>>,
     *     low_stock: array<int, array<string, mixed>>,
     *     recent_lots: array<int, array<string, mixed>>,
     *     stores: array<int, array<string, mixed>>,
     *     open_stock_takes: array<int, array<string, mixed>>,
     *     recent_requests: array<int, array<string, mixed>>
     * }
     */
    public function getLocationOperationsBoard(string $locationId): array
    {
        $board = $this->emptyOperationsBoard();

        try {
            $qtySql = 'COALESCE(inventory_items.stock_in, 0) - COALESCE(inventory_items.stock_out, 0)';
            $today = Carbon::today();
            $horizon = $today->copy()->addDays(30);

            $itemQuery = fn () => InventoryItem::query()
                ->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
                ->where('inventory_items.inventory_location_id', $locationId)
                ->where('ic.category_type', '!=', 'is_lab_samples')
                ->where(function ($query) {
                    $query->whereNull('inventory_items.status')
                        ->orWhere('inventory_items.status', '!=', 'pending');
                });

            $board['on_hand_lots'] = (int) $itemQuery()
                ->whereRaw("{$qtySql} > 0")
                ->count('inventory_items.id');

            $board['expired_lots'] = (int) $itemQuery()
                ->whereRaw("{$qtySql} > 0")
                ->whereDate('inventory_items.expiry', '<', $today->toDateString())
                ->whereDate('inventory_items.expiry', '<', '2090-01-01')
                ->count('inventory_items.id');

            $board['expiring_lots'] = (int) $itemQuery()
                ->whereRaw("{$qtySql} > 0")
                ->whereDate('inventory_items.expiry', '>=', $today->toDateString())
                ->whereDate('inventory_items.expiry', '<=', $horizon->toDateString())
                ->count('inventory_items.id');

            $lowStockRows = InventoryItem::query()
                ->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
                ->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
                ->where('inventory_items.inventory_location_id', $locationId)
                ->where('ic.category_type', '!=', 'is_lab_samples')
                ->where(function ($query) {
                    $query->whereNull('inventory_items.status')
                        ->orWhere('inventory_items.status', '!=', 'pending');
                })
                ->selectRaw('isc.id, isc.name, isc.code, isc.unit_type, isc.minimum_level, isc.inventory_category_id, SUM('.$qtySql.') as available_qty')
                ->groupBy('isc.id', 'isc.name', 'isc.code', 'isc.unit_type', 'isc.minimum_level', 'isc.inventory_category_id')
                ->havingRaw('SUM('.$qtySql.') < COALESCE(isc.minimum_level, 0)')
                ->havingRaw('COALESCE(isc.minimum_level, 0) > 0')
                ->orderBy('available_qty')
                ->get();

            $board['low_stock_count'] = $lowStockRows->count();
            $board['low_stock'] = $lowStockRows->take(8)->map(function ($row) {
                return [
                    'name' => (string) $row->name,
                    'code' => (string) ($row->code ?: ''),
                    'unit' => (string) ($row->unit_type ?: ''),
                    'available' => round((float) $row->available_qty, 2),
                    'minimum' => round((float) $row->minimum_level, 2),
                    'url' => route('show-inventory-items', [
                        'category' => $row->inventory_category_id,
                        'id' => $row->id,
                    ]),
                ];
            })->all();

            $board['expiring'] = $itemQuery()
                ->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
                ->whereRaw("{$qtySql} > 0")
                ->whereDate('inventory_items.expiry', '>=', $today->toDateString())
                ->whereDate('inventory_items.expiry', '<=', $horizon->toDateString())
                ->selectRaw('isc.name, isc.code, isc.unit_type, isc.inventory_category_id, inventory_items.inventory_sub_category_id, inventory_items.batch_code, inventory_items.lot_no, inventory_items.expiry, '.$qtySql.' as available_qty')
                ->orderBy('inventory_items.expiry')
                ->limit(8)
                ->get()
                ->map(function ($row) use ($today) {
                    $expiry = $row->expiry ? Carbon::parse($row->expiry) : null;
                    $daysLeft = $expiry ? (int) $today->diffInDays($expiry, false) : 0;

                    return [
                        'name' => (string) $row->name,
                        'code' => (string) ($row->code ?: ''),
                        'batch' => (string) ($row->lot_no ?: $row->batch_code ?: ''),
                        'unit' => (string) ($row->unit_type ?: ''),
                        'qty' => round((float) $row->available_qty, 2),
                        'expiry' => $expiry?->format('d M Y'),
                        'days_left' => $daysLeft,
                        'url' => route('show-inventory-items', [
                            'category' => $row->inventory_category_id,
                            'id' => $row->inventory_sub_category_id,
                        ]),
                    ];
                })
                ->all();

            $board['recent_lots'] = InventoryItem::query()
                ->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
                ->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
                ->where('inventory_items.inventory_location_id', $locationId)
                ->where('ic.category_type', '!=', 'is_lab_samples')
                ->selectRaw('isc.name, isc.code, isc.unit_type, isc.inventory_category_id, inventory_items.inventory_sub_category_id, inventory_items.stock_in, inventory_items.stock_out, inventory_items.created_at')
                ->orderByDesc('inventory_items.created_at')
                ->limit(8)
                ->get()
                ->map(function ($row) {
                    $stockIn = (float) $row->stock_in;
                    $stockOut = (float) $row->stock_out;

                    return [
                        'name' => (string) $row->name,
                        'code' => (string) ($row->code ?: ''),
                        'unit' => (string) ($row->unit_type ?: ''),
                        'stock_in' => round($stockIn, 2),
                        'stock_out' => round($stockOut, 2),
                        'kind' => $stockOut > $stockIn ? 'out' : 'in',
                        'when' => $row->created_at ? Carbon::parse($row->created_at)->diffForHumans() : '',
                        'url' => route('show-inventory-items', [
                            'category' => $row->inventory_category_id,
                            'id' => $row->inventory_sub_category_id,
                        ]),
                    ];
                })
                ->all();

            $qtyByStore = InventoryItem::query()
                ->where('inventory_location_id', $locationId)
                ->whereRaw("{$qtySql} > 0")
                ->selectRaw('inventory_store_id, COUNT(*) as lots, SUM('.$qtySql.') as qty')
                ->groupBy('inventory_store_id')
                ->get()
                ->keyBy('inventory_store_id');

            $board['stores'] = InventoryStore::query()
                ->where('inventory_location_id', $locationId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function ($store) use ($qtyByStore) {
                    $stats = $qtyByStore->get($store->id);

                    return [
                        'id' => $store->id,
                        'name' => (string) $store->name,
                        'lots' => (int) ($stats->lots ?? 0),
                        'qty' => round((float) ($stats->qty ?? 0), 2),
                        'url' => route('inventory-store-slots', ['store' => $store->id]),
                    ];
                })
                ->all();

            try {
                $totals = function_exists('getRequisitionWorkflowTotals') ? getRequisitionWorkflowTotals() : [];
                $pipelineStages = array_merge(
                    function_exists('getRequisitionWorkflow') ? getRequisitionWorkflow() : [],
                    function_exists('getRequestToStoreWorkflow') ? getRequestToStoreWorkflow() : []
                );
                $board['pipeline'] = collect($pipelineStages)->map(function (string $stage) use ($totals) {
                    return [
                        'stage' => $stage,
                        'label' => getInventoryWorkflowStageLabel($stage),
                        'count' => (int) ($totals[$stage] ?? 0),
                    ];
                })->all();

                $board['open_stock_takes'] = StockTaking::query()
                    ->where('inventory_location_id', $locationId)
                    ->whereNull('completed_at')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get(['id', 'code', 'status', 'store_names', 'created_at'])
                    ->map(function ($taking) {
                        return [
                            'code' => (string) ($taking->code ?: 'Stock take'),
                            'status' => (string) ($taking->status ?: ''),
                            'stores' => (string) ($taking->store_names ?: ''),
                            'when' => $taking->created_at ? Carbon::parse($taking->created_at)->diffForHumans() : '',
                            'url' => route('stock-taking-sheet', ['id' => $taking->id]),
                        ];
                    })
                    ->all();

                $board['recent_requests'] = RequestEntity::query()
                    ->where('inventory_location_id', $locationId)
                    ->where(function ($query) {
                        $query->whereNull('delete')->orWhere('delete', 0);
                    })
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(['id', 'request_code', 'request_type', 'priority', 'status', 'created_at'])
                    ->map(function ($request) {
                        return [
                            'code' => (string) $request->request_code,
                            'type' => (string) $request->request_type,
                            'priority' => (string) ($request->priority ?: ''),
                            'status' => (string) ($request->status ?: ''),
                            'when' => $request->created_at ? Carbon::parse($request->created_at)->diffForHumans() : '',
                            'url' => route('view-request-details', [
                                'stage' => $request->request_type,
                                'id' => $request->id,
                            ]),
                        ];
                    })
                    ->all();
            } catch (Throwable $exception) {
                Log::warning('Failed to load inventory workflow widgets: '.$exception->getMessage());
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to load inventory operations board: '.$exception->getMessage());
        }

        return $board;
    }

    /**
     * @return array{
     *     on_hand_lots: int,
     *     expired_lots: int,
     *     expiring_lots: int,
     *     low_stock_count: int,
     *     pipeline: array<int, array{stage: string, label: string, count: int}>,
     *     expiring: array<int, array<string, mixed>>,
     *     low_stock: array<int, array<string, mixed>>,
     *     recent_lots: array<int, array<string, mixed>>,
     *     stores: array<int, array<string, mixed>>,
     *     open_stock_takes: array<int, array<string, mixed>>,
     *     recent_requests: array<int, array<string, mixed>>
     * }
     */
    public function emptyOperationsBoard(): array
    {
        return [
            'on_hand_lots' => 0,
            'expired_lots' => 0,
            'expiring_lots' => 0,
            'low_stock_count' => 0,
            'pipeline' => [],
            'expiring' => [],
            'low_stock' => [],
            'recent_lots' => [],
            'stores' => [],
            'open_stock_takes' => [],
            'recent_requests' => [],
        ];
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
