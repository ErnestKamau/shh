<?php

namespace App\Services\AI\Repository;

use App\Analyte;
use App\AnalysisType;
use App\InventoryDepartment;
use App\InventoryStore;
use App\SampleType;
use App\Models\DocumentType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportingMartDashboardService
{
    public function getLabTatBoard(string $period = 'active'): array
    {
        try {
            $connection = DB::connection('mysql');

            // --- Original Batch-Level Data ---
            $stageSummary = $connection->table("v_lab_tat_stage_summary")
                ->orderByDesc('overdue_batches')
                ->orderByDesc('total_batches')
                ->get();

            $agingBuckets = $connection->table("v_lab_tat_aging_buckets")->get();
            $overdueBatches = $connection->table("v_lab_tat_overdue_batches")
                ->orderByDesc('days_overdue')
                ->limit(12)
                ->get();

            $normalizedStageSummary = $stageSummary->map(function ($row) {
                return [
                    'workflow_stage' => $row->workflow_stage,
                    'total_batches' => (int) $row->total_batches,
                    'overdue_batches' => (int) $row->overdue_batches,
                    'due_today_batches' => (int) $row->due_today_batches,
                    'avg_days_to_target' => $this->toFloat($row->avg_days_to_target),
                    'avg_days_overdue' => $this->toFloat($row->avg_days_overdue),
                    'avg_completion_days' => $this->toFloat($row->avg_completion_days),
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $summary = [
                'active_batches' => (int) $normalizedStageSummary->sum('total_batches'),
                'overdue_batches' => (int) $normalizedStageSummary->sum('overdue_batches'),
                'due_today_batches' => (int) $normalizedStageSummary->sum('due_today_batches'),
                'workflow_stages' => (int) $normalizedStageSummary->count(),
                'avg_completion_days' => $this->weightedAverage($normalizedStageSummary, 'avg_completion_days', 'total_batches'),
            ];

            $orderedBuckets = collect($this->agingBucketLabels())->map(function ($label, $bucketKey) use ($agingBuckets) {
                $row = $agingBuckets->firstWhere('aging_bucket', $bucketKey);

                return [
                    'aging_bucket' => $bucketKey,
                    'label' => $label,
                    'batch_count' => (int) ($row->batch_count ?? 0),
                ];
            })->values();

            $normalizedOverdueBatches = $overdueBatches->map(function ($row) {
                return [
                    'source_id' => (int) $row->source_id,
                    'batch_code' => $row->batch_code,
                    'workflow_stage' => $row->workflow_stage,
                    'target_date' => $row->target_date,
                    'days_overdue' => (int) $row->days_overdue,
                    'is_qc_batch' => (bool) $row->is_qc_batch,
                ];
            })->values();

            $sampleTypeDistribution = $connection->table('sample_headers as sh')
                ->join('sample_types as st', 'st.id', '=', 'sh.sample_type_id')
                ->where('sh.isactive', 1)
                ->whereNotIn('sh.status', ['Completed', 'Finished Sample'])
                ->select('st.name as sample_type', DB::raw('COUNT(sh.id) as total_batches'))
                ->groupBy('st.name')
                ->orderByDesc('total_batches')
                ->get();

            // --- New Test-Level (Parameter) Analytics with Filtering ---
            // 1. Completion Ratio (Always focus on current/active workload for context)
            $activeSampleIds = $connection->table('sample_headers')
                ->where('isactive', 1)
                ->whereNotIn('status', ['Completed', 'Finished Sample'])
                ->pluck('id');

            $totalTestsRequested = $connection->table('sample_details')->whereIn('sample_header_id', $activeSampleIds)->count();
            $totalTestsCompleted = $connection->table('results')->whereIn('sample_header_id', $activeSampleIds)->count();

            // 2. Throughput Volume (Filtered by historical period)
            $throughputQuery = $connection->table('results')
                ->join('analytes', 'analytes.id', '=', 'results.analyte_id');

            if ($period === 'active') {
                $throughputQuery->whereIn('results.sample_header_id', $activeSampleIds);
                $periodLabel = 'Active Workload';
            } else {
                $dateLimit = match($period) {
                    'week' => now()->subDays(7),
                    'month' => now()->subMonth(),
                    'year' => now()->subYear(),
                    default => null, // Lifetime
                };
                
                if ($dateLimit) {
                    $throughputQuery->where('results.created_at', '>=', $dateLimit);
                    $periodLabel = 'Last ' . ucfirst($period);
                } else {
                    $periodLabel = 'Lifetime Total';
                }
            }

            $throughputByAnalyte = $throughputQuery
                ->select('analytes.name as analyte_name', DB::raw('COUNT(results.id) as total_tests'))
                ->groupBy('analytes.name')
                ->orderByDesc('total_tests')
                ->limit(8)
                ->get();

            return [
                'available' => true,
                'message' => null,
                'period' => $period,
                'period_label' => $periodLabel,
                'summary' => array_merge($summary, [
                    'tests_requested' => $totalTestsRequested,
                    'tests_completed' => $totalTestsCompleted,
                    'tests_pending' => max(0, $totalTestsRequested - $totalTestsCompleted),
                ]),
                'stage_summary' => $normalizedStageSummary->all(),
                'stage_counts' => $normalizedStageSummary->pluck('total_batches', 'workflow_stage')->all(),
                'aging_buckets' => $orderedBuckets->all(),
                'overdue_batches' => $normalizedOverdueBatches->all(),
                'sample_type_distribution' => $sampleTypeDistribution->all(),
                'charts' => [
                    'stage_labels' => $normalizedStageSummary->pluck('workflow_stage')->all(),
                    'stage_totals' => $normalizedStageSummary->pluck('total_batches')->all(),
                    'stage_overdue' => $normalizedStageSummary->pluck('overdue_batches')->all(),
                    'aging_labels' => $orderedBuckets->pluck('label')->all(),
                    'aging_counts' => $orderedBuckets->pluck('batch_count')->all(),
                    'type_labels' => $sampleTypeDistribution->pluck('sample_type')->all(),
                    'type_counts' => $sampleTypeDistribution->pluck('total_batches')->all(),
                    'completion_labels' => ['Completed', 'Pending'],
                    'completion_counts' => [$totalTestsCompleted, max(0, $totalTestsRequested - $totalTestsCompleted)],
                    'throughput_labels' => $throughputByAnalyte->pluck('analyte_name')->all(),
                    'throughput_counts' => $throughputByAnalyte->pluck('total_tests')->all(),
                ],
                'refreshed_at' => $normalizedStageSummary->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load lab TAT board from reporting mart: ' . $exception->getMessage());

            return $this->emptyLabTatBoard('Reporting mart unavailable. Lab TAT cockpit is showing fallback data only.');
        }
    }

    public function getQcStabilityBoard(): array
    {
        try {
            $connection = DB::connection($this->repositoryConnection());
            $schema     = $this->reportingSchema();

            // Read pre-computed ISO 13528 Algorithm A statistics from Postgres
            $rows = $connection->table("{$schema}.v_qc_stability_metrics")
                ->orderByDesc('robust_cv_pct')
                ->get();

            if ($rows->isEmpty()) {
                return $this->emptyQcBoard('No QC data in reporting mart. Run ETL sync to populate.');
            }

            // CV% thresholds (aligned with settings in config/imara_ai.php)
            $warnThreshold     = (float) config('imara_ai.reporting.qc_cv_warning_pct',  15.0);
            $criticalThreshold = (float) config('imara_ai.reporting.qc_cv_critical_pct', 25.0);

            $normalizedDetailRows = $rows->map(function ($row) use ($warnThreshold, $criticalThreshold) {
                $cv = (float) ($row->robust_cv_pct ?? 0);

                $status = 'stable';
                if ($cv >= $criticalThreshold) $status = 'critical';
                elseif ($cv >= $warnThreshold)  $status = 'warning';

                return [
                    'source_id'                  => null,
                    'sample_type_name'           => '—',
                    'analysis_type_name'         => '—',
                    'analyte_name'               => $row->analyte_name,
                    'analyte_code'               => $row->analyte_code,
                    'robust_mean'                => round((float) ($row->robust_mean ?? 0), 4),
                    'robust_standard_deviation'  => round((float) ($row->robust_sd   ?? 0), 4),
                    'robust_cv_percentage'       => round($cv, 2),
                    'ucl'                        => round((float) ($row->ucl ?? 0), 4),
                    'lcl'                        => round((float) ($row->lcl ?? 0), 4),
                    'total_tests'                => (int) ($row->total_tests  ?? 0),
                    'passed_tests'               => (int) ($row->passed_tests ?? 0),
                    'failed_tests'               => (int) ($row->failed_tests ?? 0),
                    'pass_rate_pct'              => (float) ($row->pass_rate_pct ?? 0),
                    'stability_status'           => $status,
                    'status_label'               => $this->qcStatusLabels()[$status] ?? ucfirst($status),
                    'refreshed_at'               => now()->toDateTimeString(),
                ];
            });

            $summary = [
                'total_records'   => $normalizedDetailRows->count(),
                'stable_records'  => $normalizedDetailRows->where('stability_status', 'stable')->count(),
                'warning_records' => $normalizedDetailRows->where('stability_status', 'warning')->count(),
                'critical_records'=> $normalizedDetailRows->where('stability_status', 'critical')->count(),
                'unknown_records' => 0,
                'avg_cv_percentage' => round((float) ($normalizedDetailRows->avg('robust_cv_percentage') ?? 0), 2),
            ];

            return [
                'available' => true,
                'message'   => null,
                'summary'   => $summary,
                'status_summary' => collect($this->qcStatusLabels())->map(function ($label, $key) use ($normalizedDetailRows) {
                    $subset = $normalizedDetailRows->where('stability_status', $key);
                    return [
                        'stability_status' => $key,
                        'label'            => $label,
                        'record_count'     => $subset->count(),
                        'avg_cv_percentage'=> round((float) ($subset->avg('robust_cv_percentage') ?? 0), 2),
                    ];
                })->all(),
                'exception_rows' => $normalizedDetailRows->take(12)->values()->all(),
                'charts' => [
                    'status_labels'  => array_values($this->qcStatusLabels()),
                    'status_counts'  => collect($this->qcStatusLabels())
                        ->map(fn ($l, $k) => $normalizedDetailRows->where('stability_status', $k)->count())
                        ->values()->all(),
                    'top_labels'     => $normalizedDetailRows->take(8)
                        ->map(fn ($r) => $r['analyte_name'])
                        ->values()->all(),
                    'top_cv_values'  => $normalizedDetailRows->take(8)
                        ->pluck('robust_cv_percentage')
                        ->values()->all(),
                ],
                'refreshed_at' => now()->toDateTimeString(),
            ];
        } catch (\Throwable $exception) {
            Log::warning('QcStabilityBoard (Postgres) failed: ' . $exception->getMessage());
            return $this->emptyQcBoard(null); // Don't expose SQL errors to users
        }
    }

    public function getEquipmentReliabilityBoard(): array
    {
        try {
            $connection = DB::connection('mysql');

            $detailRows = $connection->table("v_equipment_reliability")
                ->orderByRaw("CASE WHEN maintenance_status = 'overdue' OR calibration_status = 'overdue' THEN 1 ELSE 2 END")
                ->get();
            $departmentRows = $connection->table("v_equipment_summary")
                ->orderByDesc('due_soon')
                ->orderByDesc('total_assets')
                ->get();

            $departmentNames = InventoryDepartment::pluck('name', 'id');

            $normalizedDetailRows = $detailRows->map(function ($row) use ($departmentNames) {
                return [
                    'source_id' => (int) $row->equipment_id,
                    'name' => $row->equipment_name ?: ('Equipment #' . $row->equipment_id),
                    'assigned_department' => $this->resolveDepartmentName($row->assigned_department, $departmentNames),
                    'maintenance_status' => $row->maintenance_status,
                    'calibration_status' => $row->calibration_status,
                    'verification_status' => $row->verification_status ?? 'n/a',
                    'risk_window' => $row->maintenance_status == 'overdue' || $row->calibration_status == 'overdue' ? 'overdue' : 'stable',
                    'nearest_due_days' => min(array_filter([$row->maintenance_overdue_days ?? null, $row->calibration_overdue_days ?? null], fn($v) => !is_null($v))) ?? 0,
                    'maintenance_due_date' => $row->next_maintenance_due,
                    'calibration_due_date' => $row->next_calibration_due,
                    'verification_due_date' => null,
                ];
            })->values();

            $normalizedDepartmentRows = $departmentRows->map(function ($row) use ($departmentNames) {
                return [
                    'assigned_department' => $this->resolveDepartmentName($row->department, $departmentNames),
                    'total_assets' => (int) $row->total_assets,
                    'maintenance_overdue' => (int) $row->maint_overdue,
                    'calibration_overdue' => (int) $row->calib_overdue,
                    'verification_overdue' => 0,
                    'due_within_30_days' => (int) $row->due_soon,
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $summary = [
                'total_assets' => (int) $normalizedDepartmentRows->sum('total_assets'),
                'maintenance_overdue' => (int) $normalizedDepartmentRows->sum('maintenance_overdue'),
                'calibration_overdue' => (int) $normalizedDepartmentRows->sum('calibration_overdue'),
                'verification_overdue' => 0,
                'due_within_30_days' => (int) $normalizedDepartmentRows->sum('due_within_30_days'),
            ];

            $priorityAssets = $normalizedDetailRows
                ->filter(fn ($row) => in_array($row['risk_window'], ['overdue', '7_days', '14_days', '30_days'], true))
                ->sort(function ($left, $right) {
                    $priorityCompare = $this->riskWindowPriority($left['risk_window']) <=> $this->riskWindowPriority($right['risk_window']);
                    if ($priorityCompare !== 0) {
                        return $priorityCompare;
                    }

                    return ($left['nearest_due_days'] ?? PHP_INT_MAX) <=> ($right['nearest_due_days'] ?? PHP_INT_MAX);
                })
                ->take(10)
                ->values();

            return [
                'available' => true,
                'message' => null,
                'summary' => $summary,
                'department_rows' => $normalizedDepartmentRows->all(),
                'priority_assets' => $priorityAssets->all(),
                'refreshed_at' => $normalizedDepartmentRows->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load equipment reliability board from reporting mart: ' . $exception->getMessage());

            return $this->emptyEquipmentBoard('Equipment reliability reporting mart is unavailable.');
        }
    }

    public function getTicketSlaBoard(): array
    {
        try {
            $connection = DB::connection($this->repositoryConnection());
            $schema = $this->reportingSchema();

            $summaryRows = $connection->table("{$schema}.ticket_sla_summary")->get();
            $detailRows = $connection->table("{$schema}.ticket_sla_detail")
                ->orderByDesc('unresponded')
                ->orderByDesc('is_escalated')
                ->orderByDesc('age_days')
                ->limit(10)
                ->get();

            $summaryMap = [];
            foreach ($summaryRows as $row) {
                $summaryMap[$row->summary_key] = $row->metric_average !== null
                    ? $this->toFloat($row->metric_average)
                    : (int) $row->metric_value;
            }

            $summary = [
                'total_open' => (int) ($summaryMap['total_open'] ?? 0),
                'unresponded_open' => (int) ($summaryMap['unresponded_open'] ?? 0),
                'first_response_breached' => (int) ($summaryMap['first_response_breached'] ?? 0),
                'resolution_breached' => (int) ($summaryMap['resolution_breached'] ?? 0),
                'escalated_open' => (int) ($summaryMap['escalated_open'] ?? 0),
                'avg_open_age_days' => $summaryMap['avg_open_age_days'] ?? null,
            ];

            $priorityQueue = collect($detailRows)->map(function ($row) {
                return [
                    'source_id' => (int) $row->source_id,
                    'ticket_no' => $row->ticket_no ?: ('Ticket #' . $row->source_id),
                    'priority' => $row->priority ?: 'n/a',
                    'sla_level' => $row->sla_level ?: 'n/a',
                    'assigned_to' => $row->assigned_to ?: 'Unassigned',
                    'is_closed' => (bool) $row->is_closed,
                    'unresponded' => (bool) $row->unresponded,
                    'is_escalated' => (bool) $row->is_escalated,
                    'age_days' => (int) $row->age_days,
                    'first_response_sla_status' => $row->first_response_sla_status ?: 'unknown',
                    'resolution_sla_status' => $row->resolution_sla_status ?: 'unknown',
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            return [
                'available' => true,
                'message' => null,
                'summary' => $summary,
                'priority_queue' => $priorityQueue->all(),
                'refreshed_at' => $priorityQueue->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load ticket SLA board from reporting mart: ' . $exception->getMessage());

            return $this->emptyTicketBoard('Ticket SLA reporting mart is unavailable.');
        }
    }

    public function getInventoryRiskBoard($expiryThresholdDays = 30): array
    {
        try {
            $expiryThresholdDays = (int) $expiryThresholdDays;
            
            // Build the query dynamically, replacing the 'v_inventory_position_summary' view
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

            // Group by store dynamically, replacing 'v_inventory_risk_summary'
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
            Log::warning('Failed to load inventory risk board from reporting mart: ' . $exception->getMessage());

            return $this->emptyInventoryBoard('Inventory risk reporting mart is unavailable.');
        }
    }

    public function getDocumentComplianceBoard(): array
    {
        try {
            $connection = DB::connection($this->repositoryConnection());
            $schema = $this->reportingSchema();

            $detailRows = $connection->table("{$schema}.document_compliance_detail")
                ->orderByDesc('is_expired')
                ->orderByDesc('is_expiring_soon')
                ->orderBy('days_until_expiry')
                ->limit(12)
                ->get();
            $summaryRows = $connection->table("{$schema}.document_compliance_summary")
                ->orderByDesc('expired_documents')
                ->orderByDesc('expiring_documents')
                ->get();

            $departments = InventoryDepartment::pluck('name', 'id');
            $documentTypes = DocumentType::pluck('name', 'id');

            $normalizedDetailRows = collect($detailRows)->map(function ($row) use ($departments, $documentTypes) {
                return [
                    'source_id' => (int) $row->source_id,
                    'name' => $row->name ?: ('Document #' . $row->source_id),
                    'department_name' => $departments[$row->department_id] ?? ('Department #' . ($row->department_id ?? 'N/A')),
                    'document_type_name' => $documentTypes[$row->document_type_id] ?? ('Type #' . ($row->document_type_id ?? 'N/A')),
                    'validity_period' => $row->validity_period,
                    'days_until_expiry' => $row->days_until_expiry !== null ? (int) $row->days_until_expiry : null,
                    'is_expired' => (bool) $row->is_expired,
                    'is_expiring_soon' => (bool) $row->is_expiring_soon,
                    'is_published' => (bool) $row->is_published,
                    'avg_approval_cycle_days' => $this->toFloat($row->approval_cycle_days),
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $normalizedSummaryRows = collect($summaryRows)->map(function ($row) use ($departments, $documentTypes) {
                return [
                    'department_name' => $departments[$row->department_id] ?? ($row->department_id ? ('Department #' . $row->department_id) : 'Unassigned'),
                    'document_type_name' => $documentTypes[$row->document_type_id] ?? ($row->document_type_id ? ('Type #' . $row->document_type_id) : 'Unassigned'),
                    'total_documents' => (int) $row->total_documents,
                    'expired_documents' => (int) $row->expired_documents,
                    'expiring_documents' => (int) $row->expiring_documents,
                    'unpublished_documents' => (int) $row->unpublished_documents,
                    'avg_approval_cycle_days' => $this->toFloat($row->avg_approval_cycle_days),
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $summary = [
                'total_documents' => (int) $normalizedSummaryRows->sum('total_documents'),
                'expired_documents' => (int) $normalizedSummaryRows->sum('expired_documents'),
                'expiring_documents' => (int) $normalizedSummaryRows->sum('expiring_documents'),
                'unpublished_documents' => (int) $normalizedSummaryRows->sum('unpublished_documents'),
                'avg_approval_cycle_days' => $this->weightedAverage($normalizedSummaryRows, 'avg_approval_cycle_days', 'total_documents'),
            ];

            return [
                'available' => true,
                'message' => null,
                'summary' => $summary,
                'summary_rows' => $normalizedSummaryRows->take(10)->all(),
                'priority_documents' => $normalizedDetailRows->all(),
                'refreshed_at' => $normalizedSummaryRows->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load document compliance board from reporting mart: ' . $exception->getMessage());

            return $this->emptyDocumentBoard('Document compliance reporting mart is unavailable.');
        }
    }

    protected function emptyLabTatBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message,
            'period' => 'active',
            'period_label' => 'Active Workload',
            'summary' => [
                'active_batches' => 0,
                'overdue_batches' => 0,
                'due_today_batches' => 0,
                'workflow_stages' => 0,
                'avg_completion_days' => null,
                'tests_requested' => 0,
                'tests_completed' => 0,
                'tests_pending' => 0,
            ],
            'stage_summary' => [],
            'stage_counts' => [],
            'sample_type_distribution' => [],
            'aging_buckets' => collect($this->agingBucketLabels())->map(function ($label, $bucketKey) {
                return [
                    'aging_bucket' => $bucketKey,
                    'label' => $label,
                    'batch_count' => 0,
                ];
            })->values()->all(),
            'overdue_batches' => [],
            'charts' => [
                'stage_labels' => [],
                'stage_totals' => [],
                'stage_overdue' => [],
                'aging_labels' => array_values($this->agingBucketLabels()),
                'aging_counts' => array_fill(0, count($this->agingBucketLabels()), 0),
                'type_labels' => [],
                'type_counts' => [],
                'completion_labels' => ['Completed', 'Pending'],
                'completion_counts' => [0, 0],
                'throughput_labels' => [],
                'throughput_counts' => [],
            ],
            'refreshed_at' => null,
        ];
    }

    protected function emptyQcBoard(?string $message = null): array
    {
        $statusRows = collect($this->qcStatusLabels())->map(function ($label, $statusKey) {
            return [
                'stability_status' => $statusKey,
                'label' => $label,
                'record_count' => 0,
                'avg_cv_percentage' => null,
                'max_cv_percentage' => null,
            ];
        })->values();

        return [
            'available' => false,
            'message' => $message ?? 'QC stability reporting mart is unavailable.',
            'summary' => [
                'total_records' => 0,
                'stable_records' => 0,
                'warning_records' => 0,
                'critical_records' => 0,
                'unknown_records' => 0,
                'avg_cv_percentage' => 0,
            ],
            'status_summary' => $statusRows->all(),
            'exception_rows' => [],
            'charts' => [
                'status_labels' => $statusRows->pluck('label')->all(),
                'status_counts' => $statusRows->pluck('record_count')->all(),
                'top_labels' => [],
                'top_cv_values' => [],
            ],
            'refreshed_at' => null,
        ];
    }

    protected function emptyEquipmentBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Equipment reliability reporting mart is unavailable.',
            'summary' => [
                'total_assets' => 0,
                'maintenance_overdue' => 0,
                'calibration_overdue' => 0,
                'verification_overdue' => 0,
                'due_within_30_days' => 0,
            ],
            'department_rows' => [],
            'priority_assets' => [],
            'refreshed_at' => null,
        ];
    }

    protected function emptyTicketBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Ticket SLA reporting mart is unavailable.',
            'summary' => [
                'total_open' => 0,
                'unresponded_open' => 0,
                'first_response_breached' => 0,
                'resolution_breached' => 0,
                'escalated_open' => 0,
                'avg_open_age_days' => 0,
            ],
            'priority_queue' => [],
            'refreshed_at' => null,
        ];
    }

    protected function emptyInventoryBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Inventory risk reporting mart is unavailable.',
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

    protected function emptyDocumentBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Document compliance reporting mart is unavailable.',
            'summary' => [
                'total_documents' => 0,
                'expired_documents' => 0,
                'expiring_documents' => 0,
                'unpublished_documents' => 0,
                'avg_approval_cycle_days' => 0,
            ],
            'summary_rows' => [],
            'priority_documents' => [],
            'refreshed_at' => null,
        ];
    }

    protected function weightedAverage(Collection $rows, string $valueKey, string $weightKey): ?float
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($rows as $row) {
            $value = data_get($row, $valueKey);
            $weight = data_get($row, $weightKey);

            if ($value === null || $weight === null || (float) $weight <= 0) {
                continue;
            }

            $weightedSum += ((float) $value * (float) $weight);
            $totalWeight += (float) $weight;
        }

        if ($totalWeight === 0.0) {
            return null;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    protected function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    protected function agingBucketLabels(): array
    {
        return [
            'on_time' => 'On time',
            'due_today' => 'Due today',
            '1_3_overdue' => '1-3 days overdue',
            '4_7_overdue' => '4-7 days overdue',
            '8_plus_overdue' => '8+ days overdue',
            'no_target' => 'No target date',
        ];
    }

    protected function qcStatusLabels(): array
    {
        return [
            'stable' => 'Stable',
            'warning' => 'Warning',
            'critical' => 'Critical',
            'unknown' => 'Unknown',
        ];
    }

    protected function resolveDepartmentName($value, Collection $departmentNames): string
    {
        if ($value === null || $value === '') {
            return 'Unassigned';
        }

        if (is_numeric($value) && isset($departmentNames[(int) $value])) {
            return $departmentNames[(int) $value];
        }

        return (string) $value;
    }

    protected function nearestDueDays(array $values): ?int
    {
        $filtered = array_values(array_filter($values, fn ($value) => $value !== null));
        if (empty($filtered)) {
            return null;
        }

        return (int) min($filtered);
    }

    protected function riskWindowPriority(?string $riskWindow): int
    {
        return match ($riskWindow) {
            'overdue' => 1,
            '7_days' => 2,
            '14_days' => 3,
            '30_days' => 4,
            'stable' => 5,
            default => 6,
        };
    }

    protected function repositoryConnection(): string
    {
        return config('imara_ai.repository_connection', 'pgsql_ai');
    }

    protected function reportingSchema(): string
    {
        return config('imara_ai.schemas.reporting', 'reporting');
    }
}
