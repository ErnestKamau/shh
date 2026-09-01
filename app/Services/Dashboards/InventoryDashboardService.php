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
                ->where(function ($q) use ($locationId) {
                    $q->where('inventory_items.inventory_location_id', $locationId)
                      ->orWhere('ic.inventory_location_id', $locationId);
                })
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
                ->where(function ($q) use ($locationId) {
                    $q->where('inventory_items.inventory_location_id', $locationId)
                      ->orWhere('ic.inventory_location_id', $locationId);
                })
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
                ->where(function ($q) use ($locationId) {
                    $q->where('inventory_items.inventory_location_id', $locationId)
                      ->orWhere('ic.inventory_location_id', $locationId);
                })
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
                ->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
                ->where(function ($q) use ($locationId) {
                    $q->where('inventory_items.inventory_location_id', $locationId)
                      ->orWhere('ic.inventory_location_id', $locationId);
                })
                ->where('ic.category_type', '!=', 'is_lab_samples')
                ->whereRaw("{$qtySql} > 0")
                ->selectRaw('inventory_items.inventory_store_id, COUNT(*) as lots, SUM('.$qtySql.') as qty')
                ->groupBy('inventory_items.inventory_store_id')
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
     * Complete payload for the inventory dashboard including trend charts,
     * period-filtered metrics, breakdowns, operational health, and available filter options.
     *
     * @param string $locationId
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getInventoryDashboardPayload(string $locationId, array $filters = []): array
    {
        $trendData = $this->getInventoryTrendData($locationId, $filters);
        $opsBoard = $this->getLocationOperationsBoard($locationId);

        $categories = \App\InventoryCategories::query()
            ->where('inventory_location_id', $locationId)
            ->where('category_type', '!=', 'is_lab_samples')
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $stores = \App\InventoryStore::query()
            ->where('inventory_location_id', $locationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = \App\InventoryDepartment::query()
            ->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                  ->orWhereNull('location_id');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $pendingCount = 0;
        $pendingPreview = collect();
        try {
            $pending = function_exists('pendingApprovals') ? pendingApprovals() : collect();
            $pendingCount = $pending->count();
            $pendingPreview = $pending->take(5)->values();
        } catch (Throwable $e) {
            Log::warning('Failed to load pending approvals in inventory dashboard: ' . $e->getMessage());
        }

        $restockCount = 0;
        $restockItems = [];
        try {
            $restock = function_exists('getRestockNotifications') ? getRestockNotifications(true) : [];
            $restockCount = (int) ($restock['count'] ?? 0);
            $restockItems = $restock['items'] ?? [];
        } catch (Throwable $e) {
            Log::warning('Failed to load restock notifications in inventory dashboard: ' . $e->getMessage());
        }

        return array_merge($trendData, [
            'location_id' => $locationId,
            'ops' => $opsBoard,
            'categories' => $categories,
            'stores' => $stores,
            'departments' => $departments,
            'pendingApprovalsCount' => $pendingCount,
            'pendingApprovalsPreview' => $pendingPreview,
            'restockCount' => $restockCount,
            'restockItems' => $restockItems,
        ]);
    }

    /**
     * Generate dynamic time-series trend data (hourly, weekly, monthly) and period-scoped metrics.
     *
     * Granularity adaptation:
     * - 24 hours / 1 day: hour-by-hour (24 continuous hourly slots)
     * - 1 month / 30 days: week-by-week (chronological weekly slots)
     * - 1 year / 12 months: month-by-month (12 continuous monthly slots)
     *
     * @param string $locationId
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getInventoryTrendData(string $locationId, array $filters = []): array
    {
        $rangeInfo = $this->resolvePeriodDateRange($filters);
        $start = $rangeInfo['start'];
        $end = $rangeInfo['end'];
        $granularity = $rangeInfo['granularity'];
        $rangePreset = $rangeInfo['preset'];

        $slots = $this->buildTimelineSlots($granularity, $start, $end, $rangePreset);

        $baseQuery = InventoryItem::query()
            ->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
            ->where(function ($q) use ($locationId) {
                $q->where('inventory_items.inventory_location_id', $locationId)
                  ->orWhere('ic.inventory_location_id', $locationId);
            })
            ->where('ic.category_type', '!=', 'is_lab_samples')
            ->where(function ($q) {
                $q->whereNull('inventory_items.status')
                  ->orWhere('inventory_items.status', '!=', 'pending');
            })
            ->whereBetween('inventory_items.created_at', [
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ]);

        $selectedCategoryId = !empty($filters['category_id']) ? (string) $filters['category_id'] : null;
        if ($selectedCategoryId) {
            $baseQuery->where('inventory_items.inventory_category_id', $selectedCategoryId);
        }

        $selectedStoreId = !empty($filters['store_id']) ? (string) $filters['store_id'] : null;
        if ($selectedStoreId) {
            $baseQuery->where('inventory_items.inventory_store_id', $selectedStoreId);
        }

        $selectedDepartmentId = !empty($filters['department_id']) ? (string) $filters['department_id'] : null;
        if ($selectedDepartmentId) {
            $baseQuery->where('inventory_items.inventory_department_id', $selectedDepartmentId);
        }

        $driver = DB::connection()->getDriverName();
        if ($granularity === 'hourly') {
            $expr = $driver === 'pgsql'
                ? "TO_CHAR(inventory_items.created_at, 'YYYY-MM-DD HH24:00')"
                : ($driver === 'sqlite' ? "strftime('%Y-%m-%d %H:00', inventory_items.created_at)" : "DATE_FORMAT(inventory_items.created_at, '%Y-%m-%d %H:00')");
        } elseif ($granularity === 'monthly') {
            $expr = $driver === 'pgsql'
                ? "TO_CHAR(inventory_items.created_at, 'YYYY-MM')"
                : ($driver === 'sqlite' ? "strftime('%Y-%m', inventory_items.created_at)" : "DATE_FORMAT(inventory_items.created_at, '%Y-%m')");
        } else {
            // weekly / daily / custom
            $expr = $driver === 'pgsql'
                ? "TO_CHAR(inventory_items.created_at, 'YYYY-MM-DD')"
                : ($driver === 'sqlite' ? "strftime('%Y-%m-%d', inventory_items.created_at)" : "DATE_FORMAT(inventory_items.created_at, '%Y-%m-%d')");
        }

        $periodRows = (clone $baseQuery)
            ->selectRaw("{$expr} as period_key, SUM(COALESCE(inventory_items.stock_in, 0)) as total_in, SUM(COALESCE(inventory_items.stock_out, 0)) as total_out, COUNT(inventory_items.id) as count")
            ->groupBy(DB::raw($expr))
            ->get();

        foreach ($periodRows as $row) {
            $key = (string) $row->period_key;
            $stockIn = (float) $row->total_in;
            $stockOut = (float) $row->total_out;
            $count = (int) $row->count;

            if ($granularity === 'hourly' || $granularity === 'monthly') {
                if (isset($slots[$key])) {
                    $slots[$key]['stock_in'] += $stockIn;
                    $slots[$key]['stock_out'] += $stockOut;
                    $slots[$key]['transactions_count'] += $count;
                }
            } elseif ($granularity === 'weekly') {
                $rowDate = Carbon::parse($key)->startOfDay();
                foreach ($slots as $slotKey => &$slot) {
                    $slotStart = Carbon::parse($slot['start'])->startOfDay();
                    $slotEnd = Carbon::parse($slot['end'])->endOfDay();
                    if ($rowDate->between($slotStart, $slotEnd)) {
                        $slot['stock_in'] += $stockIn;
                        $slot['stock_out'] += $stockOut;
                        $slot['transactions_count'] += $count;
                        break;
                    }
                }
                unset($slot);
            } else {
                // daily
                if (isset($slots[$key])) {
                    $slots[$key]['stock_in'] += $stockIn;
                    $slots[$key]['stock_out'] += $stockOut;
                    $slots[$key]['transactions_count'] += $count;
                }
            }
        }

        $runningBalance = 0.0;
        $chartLabels = [];
        $stockInData = [];
        $stockOutData = [];
        $netMovementData = [];
        $cumulativeData = [];
        $tooltips = [];
        $timelineList = [];

        foreach ($slots as $slot) {
            $sIn = round((float) $slot['stock_in'], 2);
            $sOut = round((float) $slot['stock_out'], 2);
            $net = round($sIn - $sOut, 2);
            $runningBalance += $net;

            $slot['stock_in'] = $sIn;
            $slot['stock_out'] = $sOut;
            $slot['net_movement'] = $net;
            $slot['cumulative_stock'] = round($runningBalance, 2);

            $chartLabels[] = $slot['label'];
            $stockInData[] = $sIn;
            $stockOutData[] = $sOut;
            $netMovementData[] = $net;
            $cumulativeData[] = round($runningBalance, 2);
            $tooltips[] = $slot['tooltip'];
            $timelineList[] = $slot;
        }

        $totalStockIn = round((float) $periodRows->sum('total_in'), 2);
        $totalStockOut = round((float) $periodRows->sum('total_out'), 2);
        $netMovement = round($totalStockIn - $totalStockOut, 2);
        $transactionsCount = (int) $periodRows->sum('count');

        $topMovingItems = (clone $baseQuery)
            ->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
            ->selectRaw("
                isc.id,
                isc.name,
                isc.code,
                isc.unit_type,
                ic.name as category_name,
                ic.id as category_id,
                SUM(COALESCE(inventory_items.stock_in, 0)) as stock_in,
                SUM(COALESCE(inventory_items.stock_out, 0)) as stock_out,
                SUM(COALESCE(inventory_items.stock_in, 0) - COALESCE(inventory_items.stock_out, 0)) as net_change,
                COUNT(inventory_items.id) as move_count
            ")
            ->groupBy('isc.id', 'isc.name', 'isc.code', 'isc.unit_type', 'ic.name', 'ic.id')
            ->orderByDesc(DB::raw('SUM(COALESCE(inventory_items.stock_in, 0) + COALESCE(inventory_items.stock_out, 0))'))
            ->limit(8)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'code' => (string) ($row->code ?: ''),
                    'unit' => (string) ($row->unit_type ?: ''),
                    'category' => (string) $row->category_name,
                    'stock_in' => round((float) $row->stock_in, 2),
                    'stock_out' => round((float) $row->stock_out, 2),
                    'net_change' => round((float) $row->net_change, 2),
                    'move_count' => (int) $row->move_count,
                    'url' => route('show-inventory-items', [
                        'category' => $row->category_id,
                        'id' => $row->id,
                    ]),
                ];
            })
            ->all();

        $categoryBreakdown = (clone $baseQuery)
            ->selectRaw("
                ic.id,
                ic.name,
                SUM(COALESCE(inventory_items.stock_in, 0)) as stock_in,
                SUM(COALESCE(inventory_items.stock_out, 0)) as stock_out,
                COUNT(inventory_items.id) as move_count
            ")
            ->groupBy('ic.id', 'ic.name')
            ->orderByDesc(DB::raw('SUM(COALESCE(inventory_items.stock_in, 0) + COALESCE(inventory_items.stock_out, 0))'))
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $in = round((float) $row->stock_in, 2);
                $out = round((float) $row->stock_out, 2);
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'stock_in' => $in,
                    'stock_out' => $out,
                    'net' => round($in - $out, 2),
                    'move_count' => (int) $row->move_count,
                ];
            })
            ->all();

        $storeBreakdown = (clone $baseQuery)
            ->leftJoin('inventory_stores as s', 's.id', '=', 'inventory_items.inventory_store_id')
            ->selectRaw("
                s.id as store_id,
                COALESCE(s.name, 'Unassigned Store') as store_name,
                SUM(COALESCE(inventory_items.stock_in, 0)) as stock_in,
                SUM(COALESCE(inventory_items.stock_out, 0)) as stock_out,
                COUNT(inventory_items.id) as move_count
            ")
            ->groupBy('s.id', 's.name')
            ->orderByDesc(DB::raw('SUM(COALESCE(inventory_items.stock_in, 0) + COALESCE(inventory_items.stock_out, 0))'))
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $in = round((float) $row->stock_in, 2);
                $out = round((float) $row->stock_out, 2);
                return [
                    'store_id' => $row->store_id,
                    'name' => (string) $row->store_name,
                    'stock_in' => $in,
                    'stock_out' => $out,
                    'net' => round($in - $out, 2),
                    'move_count' => (int) $row->move_count,
                ];
            })
            ->all();

        $hasActivity = $totalStockIn > 0 || $totalStockOut > 0 || $transactionsCount > 0;

        $granularityLabel = match ($granularity) {
            'hourly' => inventoryLabel('granularity_hourly', 'Hour-by-Hour (24 Hours)'),
            'weekly' => inventoryLabel('granularity_weekly', 'Week-by-Week (1 Month)'),
            'monthly' => inventoryLabel('granularity_monthly', 'Month-by-Month (1 Year)'),
            default => inventoryLabel('granularity_daily', 'Daily Trend'),
        };

        $periodDescription = match ($rangePreset) {
            '24h' => inventoryLabel('period_24h', 'Last 24 Hours'),
            '7d' => inventoryLabel('period_7d', 'Last 7 Days'),
            '1m' => inventoryLabel('period_1m', 'Last 30 Days (1 Month)'),
            '1y' => inventoryLabel('period_1y', 'Last 12 Months (1 Year)'),
            default => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
        };

        return [
            'trend_chart' => [
                'labels' => $chartLabels,
                'stock_in' => $stockInData,
                'stock_out' => $stockOutData,
                'net_movement' => $netMovementData,
                'cumulative' => $cumulativeData,
                'tooltips' => $tooltips,
                'timeline' => $timelineList,
                'granularity' => $granularity,
                'granularity_label' => $granularityLabel,
            ],
            'metrics' => [
                'total_stock_in' => $totalStockIn,
                'total_stock_out' => $totalStockOut,
                'net_movement' => $netMovement,
                'transactions_count' => $transactionsCount,
                'active_items_count' => count($topMovingItems),
                'range_preset' => $rangePreset,
                'granularity' => $granularity,
                'granularity_label' => $granularityLabel,
                'period_description' => $periodDescription,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'start_formatted' => $start->format('d M Y'),
                'end_formatted' => $end->format('d M Y'),
            ],
            'top_moving_items' => $topMovingItems,
            'category_breakdown' => $categoryBreakdown,
            'store_breakdown' => $storeBreakdown,
            'has_activity' => $hasActivity,
            'filters' => [
                'range' => $rangePreset,
                'start_date' => $filters['start_date'] ?? $start->format('Y-m-d'),
                'end_date' => $filters['end_date'] ?? $end->format('Y-m-d'),
                'category_id' => $selectedCategoryId,
                'store_id' => $selectedStoreId,
                'department_id' => $selectedDepartmentId,
            ],
        ];
    }

    /**
     * Resolve start date, end date, preset name, and time series granularity.
     *
     * Rules:
     * - 24 hours / 1 day: hourly granularity (24 points)
     * - 1 month / 30 days: weekly granularity (week-by-week)
     * - 1 year / 12 months: monthly granularity (month-by-month)
     *
     * @param array<string, mixed> $filters
     * @return array{start: Carbon, end: Carbon, granularity: 'hourly'|'daily'|'weekly'|'monthly', preset: string}
     */
    public function resolvePeriodDateRange(array $filters): array
    {
        $preset = !empty($filters['range']) ? (string) $filters['range'] : '1m';
        $startDateInput = !empty($filters['start_date']) ? (string) $filters['start_date'] : null;
        $endDateInput = !empty($filters['end_date']) ? (string) $filters['end_date'] : null;

        $now = Carbon::now();

        if ($startDateInput && $endDateInput && $preset === 'custom') {
            $start = Carbon::parse($startDateInput)->startOfDay();
            $end = Carbon::parse($endDateInput)->endOfDay();
            if ($start->gt($end)) {
                $temp = $start;
                $start = $end->copy()->startOfDay();
                $end = $temp->copy()->endOfDay();
            }

            $diffHours = $start->diffInHours($end);
            $diffDays = $start->diffInDays($end);

            if ($diffHours <= 36) {
                $granularity = 'hourly';
            } elseif ($diffDays <= 90) {
                $granularity = 'weekly';
            } else {
                $granularity = 'monthly';
            }

            return [
                'start' => $start,
                'end' => $end,
                'granularity' => $granularity,
                'preset' => 'custom',
            ];
        }

        switch ($preset) {
            case '24h':
            case '1d':
            case 'today':
                return [
                    'start' => $now->copy()->subHours(23)->startOfHour(),
                    'end' => $now->copy()->endOfHour(),
                    'granularity' => 'hourly',
                    'preset' => '24h',
                ];

            case '7d':
                return [
                    'start' => $now->copy()->subDays(6)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'granularity' => 'daily',
                    'preset' => '7d',
                ];

            case '1y':
            case '12m':
            case 'year':
                return [
                    'start' => $now->copy()->subMonths(11)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'granularity' => 'monthly',
                    'preset' => '1y',
                ];

            case '1m':
            case '30d':
            case 'month':
            default:
                return [
                    'start' => $now->copy()->subDays(27)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'granularity' => 'weekly',
                    'preset' => '1m',
                ];
        }
    }

    /**
     * Build continuous, strictly chronological timeline slots.
     * Ensures all intervals (hours, weeks, months) exist with zero values so
     * periods with no activity do not drop or shift the timeline.
     *
     * @param 'hourly'|'daily'|'weekly'|'monthly' $granularity
     * @param Carbon $start
     * @param Carbon $end
     * @param string $preset
     * @return array<string, array<string, mixed>>
     */
    public function buildTimelineSlots(string $granularity, Carbon $start, Carbon $end, string $preset): array
    {
        $slots = [];

        if ($granularity === 'hourly') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d H:00');
                $slots[$key] = [
                    'key' => $key,
                    'label' => $cursor->format('H:i'),
                    'tooltip' => $cursor->format('d M Y, H:00') . ' - ' . $cursor->format('H:59'),
                    'start' => $cursor->format('Y-m-d H:i:s'),
                    'end' => $cursor->copy()->endOfHour()->format('Y-m-d H:i:s'),
                    'stock_in' => 0.0,
                    'stock_out' => 0.0,
                    'net_movement' => 0.0,
                    'transactions_count' => 0,
                    'cumulative_stock' => 0.0,
                ];
                $cursor->addHour();
            }
        } elseif ($granularity === 'monthly') {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m');
                $slots[$key] = [
                    'key' => $key,
                    'label' => $cursor->format('M Y'),
                    'tooltip' => $cursor->format('F Y'),
                    'start' => $cursor->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                    'end' => $cursor->copy()->endOfMonth()->format('Y-m-d H:i:s'),
                    'stock_in' => 0.0,
                    'stock_out' => 0.0,
                    'net_movement' => 0.0,
                    'transactions_count' => 0,
                    'cumulative_stock' => 0.0,
                ];
                $cursor->addMonth();
            }
        } elseif ($granularity === 'weekly') {
            $cursor = $start->copy()->startOfDay();
            $weekNum = 1;
            while ($cursor->lte($end)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addDays(6)->endOfDay();
                if ($slotEnd->gt($end)) {
                    $slotEnd = $end->copy()->endOfDay();
                }

                $key = 'W' . $weekNum . '_' . $slotStart->format('Ymd');
                $label = $slotStart->format('d M') . ' - ' . $slotEnd->format('d M');
                $tooltip = 'Week ' . $weekNum . ' (' . $slotStart->format('d M Y') . ' - ' . $slotEnd->format('d M Y') . ')';

                $slots[$key] = [
                    'key' => $key,
                    'label' => $label,
                    'tooltip' => $tooltip,
                    'start' => $slotStart->format('Y-m-d H:i:s'),
                    'end' => $slotEnd->format('Y-m-d H:i:s'),
                    'stock_in' => 0.0,
                    'stock_out' => 0.0,
                    'net_movement' => 0.0,
                    'transactions_count' => 0,
                    'cumulative_stock' => 0.0,
                ];

                $cursor->addDays(7);
                $weekNum++;
            }
        } else {
            // daily
            $cursor = $start->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $slots[$key] = [
                    'key' => $key,
                    'label' => $cursor->format('D, d M'),
                    'tooltip' => $cursor->format('l, d M Y'),
                    'start' => $cursor->copy()->startOfDay()->format('Y-m-d H:i:s'),
                    'end' => $cursor->copy()->endOfDay()->format('Y-m-d H:i:s'),
                    'stock_in' => 0.0,
                    'stock_out' => 0.0,
                    'net_movement' => 0.0,
                    'transactions_count' => 0,
                    'cumulative_stock' => 0.0,
                ];
                $cursor->addDay();
            }
        }

        return $slots;
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
