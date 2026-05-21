<?php

namespace App\Http\Controllers\Mas;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\SampleHeader;
use App\InventorySubCategories;
use App\Models\RiskManagement\Risk;
use Illuminate\Support\Facades\DB;
use App\Services\Dashboards\LabTatDashboardService;
use App\Services\Dashboards\LabGeneralDashboardService;
use App\Services\Dashboards\QcDashboardService;
use App\Services\Dashboards\InventoryDashboardService;
use App\Services\Dashboards\LabLogisticsDashboardService;
use App\Services\Dashboards\EquipmentDashboardService as EquipmentBoardService;
use App\Services\AI\PerformanceDashboardService;
use App\Services\Documents\Dashboards\EquipmentReliabilityDashboardService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;


class MasController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
                abort(403, 'Unauthorized access. AI Analytics is restricted to Administrators.');
            }
            return $next($request);
        });
    }

    /**
     * AI Analytics Index - Global KPI Overview
     */
    public function index(
        InventoryDashboardService $inventoryService,
        PerformanceDashboardService $perfService,
        EquipmentReliabilityDashboardService $equipService
    ) {
        // High-Level Stats from Services & Models
        $labTotal = SampleHeader::where('status', '!=', 'Finished Sample')->count();
        $inventoryStats = $inventoryService->getInventoryRiskBoard();
        $inventoryAlerts = $inventoryStats['summary']['items_below_minimum'] ?? 0;
        
        $billingTotal = DB::table('customer_invoice')->sum('total') ?? 0;
        $riskHigh = Risk::where('risk_level', 'Critical')->where('workflow_step', '<', 8)->count();
        $aiHealth = $perfService->getDashboardOverview()['kpis']['health_score'] ?? 0;

        // New Module Stats
        $equipMetrics = $equipService->getMetrics();
        $staffCount = DB::table('users')->where('active', 1)->count();
        
        // Quality Control Summary (Fixed column name to status_code)
        $qcPending = DB::table('qc_results')->whereNotIn('status_code', ['PASSED', 'FAILED'])->count();

        // Audit Summary (Recent Critical Events)
        $auditRecent = DB::table('audits')->where('created_at', '>=', now()->subDays(7))->count();

        // Lab General Stat: Diversity of Workload
        $sampleTypesCount = DB::table('sample_types')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sample_headers')
                    ->whereColumn('sample_headers.sample_type_id', 'sample_types.id')
                    ->where('sample_headers.isactive', 1);
            })->count();

        $stats = [
            'total_samples' => $labTotal,
            'sample_types_count' => $sampleTypesCount,
            'inventory_alerts' => $inventoryAlerts,
            'unpaid_billing' => number_format($billingTotal / 1000, 1) . 'k',
            'high_risks' => $riskHigh,
            'ai_health' => $aiHealth,
            'equip_overdue' => $equipMetrics['calibrationOverdue'] ?? 0,
            'staff_count' => $staffCount,
            'qc_pending' => $qcPending,
            'audit_alerts' => $auditRecent,
        ];

        return view('layouts.mas.index', compact('stats'));
    }

    /**
     * Lab TAT Analytics & Monitoring (Turnaround Time)
     */
    public function lab(Request $request, LabTatDashboardService $service)
    {
        $period = $request->get('period', 'active');
        $stats = $service->getLabTatBoard($period);
        return view('layouts.mas.lab', compact('stats'));
    }

    /**
     * General Laboratory Analytics & Workload Distribution
     */
    public function labGeneral(LabTatDashboardService $tatService, LabGeneralDashboardService $generalService)
    {
        $labBoard = $tatService->getLabTatBoard();
        
        // Additional General Stats: Volume by Client
        $topClients = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->where('sample_headers.isactive', 1)
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $stats = [
            'sample_type_distribution' => $labBoard['sample_type_distribution'] ?? [],
            'top_clients' => $topClients,
            'charts' => [
                'type_labels' => $labBoard['charts']['type_labels'] ?? [],
                'type_counts' => $labBoard['charts']['type_counts'] ?? [],
                'client_labels' => $topClients->pluck('name')->all(),
                'client_counts' => $topClients->pluck('total')->all(),
            ],
            'summary' => $labBoard['summary'] ?? []
        ];

        return view('layouts.mas.lab_general', compact('stats'));
    }

    /**
     * Lab Quality Control & Stability Monitoring
     */
    public function labQc(QcDashboardService $service)
    {
        $stats = $service->getQcStabilityBoard();
        return view('layouts.mas.lab_qc', compact('stats'));
    }

    /**
     * Lab Logistics & Supplies Monitoring
     */
    public function labLogistics(LabLogisticsDashboardService $service)
    {
        $stats = $service->getLabLogisticsSummary();
        return view('layouts.mas.lab_logistics', compact('stats'));
    }

    /**
     * Inventory & Supply Chain Intel
     */
    public function inventory(InventoryDashboardService $service)
    {
        $stats = $service->getInventoryRiskBoard();
        return view('layouts.mas.inventory', compact('stats'));
    }

    /**
     * CRM & Financial Billing
     */
    public function crm(Request $request)
    {
        $period = $request->get('period', '1_month');
        $fromDate = $request->get('from');
        $toDate = $request->get('to');

        if ($period === 'custom' && $fromDate && $toDate) {
            $days = (int)now()->parse($fromDate)->diffInDays(now()->parse($toDate));
            $periodLabel = $fromDate . ' - ' . $toDate;
            $orderTrendQuery = SampleHeader::whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        } else {
            $days = match ($period) {
                '1_week' => 7,
                '2_weeks' => 14,
                '1_month' => 30,
                '2_months' => 60,
                'quarterly' => 90,
                'semi_annually' => 180,
                'annually' => 365,
                default => 30,
            };
            $periodLabel = __('mas/crm.label_' . $period);
            $orderTrendQuery = SampleHeader::where('created_at', '>=', now()->subDays($days));
        }

        $topClients = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $orderTrend = $orderTrendQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $stats = [
            'total_clients' => DB::table('crm_customers')->count(),
            'recent_orders' => SampleHeader::where('created_at', '>=', now()->subDays(30))->count(),
            'unpaid_invoices' => DB::table('customer_invoice')->count(),
            'top_clients' => $topClients,
            'order_trend' => $orderTrend,
            'period' => $period,
            'period_label' => $periodLabel,
            'from' => $fromDate,
            'to' => $toDate,
        ];

        return view('layouts.mas.crm', compact('stats'));
    }

    /**
     * Risk & Compliance Dashboard
     */
    public function risk()
    {
        $byLevel = Risk::select('risk_level', DB::raw('count(*) as count'))
            ->where('workflow_step', '<', 8)
            ->groupBy('risk_level')
            ->orderByDesc('count')
            ->get();

        $stats = [
            'active_count' => Risk::where('workflow_step', '<', 8)->count(),
            'critical_count' => Risk::where('risk_level', 'Critical')->where('workflow_step', '<', 8)->count(),
            'requiring_review' => Risk::where('next_review_date', '<=', now())->where('workflow_step', '<', 8)->count(),
            'by_level' => $byLevel,
        ];
        return view('layouts.mas.risk', compact('stats'));
    }


    /**
     * Equipment Maintenance & Reliability
     */
    public function equipment(EquipmentReliabilityDashboardService $service)
    {
        $stats = $service->getMetrics();
        return view('layouts.mas.equipment', compact('stats'));
    }

    /**
     * Personnel & HR Analytics
     */
    public function personnel()
    {
        $stats = [
            'total_staff' => DB::table('users')->count(),
            'active_users' => DB::table('users')->where('active', 1)->count(),
            'departments' => DB::table('inventory_departments')->count(),
            'certifications' => DB::table('personel_certifications')->count(),
        ];
        return view('layouts.mas.personnel', compact('stats'));
    }

    /**
     * Quality Control Monitoring
     */
    public function qc(QcDashboardService $service)
    {
        $stats = $service->getQcStabilityBoard();
        return view('layouts.mas.qc', compact('stats'));
    }

    /**
     * Audit & Compliance Log
     */
    public function audit()
    {
        $stats = [
            'recent_audits' => DB::table('audits')->orderByDesc('created_at')->limit(50)->get(),
            'total_events' => DB::table('audits')->count(),
        ];
        return view('layouts.mas.audit', compact('stats'));
    }

    /**
     * Unified Export Handler for MaS Modules
     */
    public function export(
        $module, 
        LabTatDashboardService $tatService,
        InventoryDashboardService $inventoryService,
        QcDashboardService $qcService,
        PerformanceDashboardService $perfService, 
        EquipmentReliabilityDashboardService $equipService
    ) {
        $data = [];
        $columns = [];
        $fileName = 'AI_Analytics_Report_' . ucfirst($module) . '_' . date('Ymd_His') . '.xlsx';

        switch ($module) {
            case 'lab':
                $stats = $tatService->getLabTatBoard();
                $columns = ['Stage Name', 'Batch Count', 'Overdue Count', 'Avg Days in Stage'];
                foreach ($stats['stage_counts'] as $stage => $count) {
                    $data[] = [$stage, $count, 0, 0]; // Simplified for base export
                }
                break;

            case 'inventory':
                $stats = $inventoryService->getInventoryRiskBoard();
                $columns = ['Item Name', 'Code', 'Store', 'Available', 'Min Level', 'Near Expiry'];
                foreach ($stats['priority_items'] as $item) {
                    $data[] = [
                        $item['item_name'],
                        $item['item_code'],
                        $item['store_name'],
                        $item['available_qty'],
                        $item['minimum_level'],
                        $item['near_expiry_qty']
                    ];
                }
                break;

            case 'crm':
                $topClients = DB::table('crm_customers')
                    ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
                    ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
                    ->groupBy('crm_customers.id', 'crm_customers.name')
                    ->orderByDesc('total')->limit(50)->get();
                $columns = ['Client Name', 'Total Orders/Batches'];
                foreach ($topClients as $client) {
                    $data[] = [$client->name, $client->total];
                }
                break;

            case 'risk':
                $risks = Risk::where('workflow_step', '<', 8)->get();
                $columns = ['Risk Description', 'Level', 'Workflow Step', 'Review Date'];
                foreach ($risks as $risk) {
                    $data[] = [$risk->description, $risk->risk_level, $risk->workflow_step, $risk->next_review_date];
                }
                break;

            case 'equipment':
                $metrics = $equipService->getMetrics();
                $columns = ['Metric Name', 'Value'];
                $data = [
                    ['Reliability Score', $metrics['reliabilityScore'] . '%'],
                    ['MTBF (Hours)', $metrics['mtbf']],
                    ['Calibration Overdue', $metrics['calibrationOverdue']],
                    ['Maintenance Due', $metrics['maintenanceDue']],
                    ['Calibration Compliance', $metrics['calibrationCompliance'] . '%'],
                ];
                break;

            case 'personnel':
                $users = DB::table('users')->select('id', 'name', 'email', 'active', 'created_at')
                    ->where('active', 1)->get();
                $columns = ['ID', 'Name', 'Email', 'Active Status', 'Created At'];
                foreach ($users as $user) {
                    $data[] = [$user->id, $user->name, $user->email, $user->active ? 'Yes' : 'No', $user->created_at];
                }
                break;

            case 'qc':
                $qc = $qcService->getQcStabilityBoard();
                $columns = ['Stage', 'Batch Count', 'Critical Alerts', 'Warning Alerts', 'Avg Deviation'];
                foreach ($qc['stages'] as $q) {
                    $data[] = [$q['name'], $q['batches'], $q['alerts_critical'], $q['alerts_warning'], $q['avg_deviation']];
                }
                break;

            case 'audit':
                $audits = DB::table('audits')->orderByDesc('created_at')->limit(100)->get();
                $columns = ['ID', 'User ID', 'Event', 'Target Type', 'IP Address', 'Timestamp'];
                foreach ($audits as $audit) {
                    $data[] = [
                        $audit->id,
                        $audit->user_id,
                        strtoupper($audit->event),
                        class_basename($audit->auditable_type),
                        $audit->ip_address,
                        $audit->created_at
                    ];
                }
                break;

            case 'ai':
                $ai = $perfService->getMLModelMetrics();
                $columns = ['Model Name', 'Success Rate (%)', 'Latency (ms)'];
                foreach ($ai['performance'] as $model) {
                    $data[] = [$model['name'], $model['success_rate'], $model['avg_latency_ms']];
                }
                break;

            default:
                return abort(404, 'Export module not supported: ' . $module);
        }
        return Excel::download(new ReportExporter($data, $columns), $fileName);
    }

    /**
     * Specialized Export with Visual Assets (PDF)
     */
    public function exportWithVisuals(
        Request $request, 
        $module, 
        LabTatDashboardService $tatService,
        LabGeneralDashboardService $generalService,
        QcDashboardService $qcService,
        LabLogisticsDashboardService $logisticsService,
        InventoryDashboardService $inventoryService,
        PerformanceDashboardService $perfService
    ) {
        $chartImage = $request->input('chart_image');
        $preview = $request->has('preview') && $request->input('preview') == 'true';
        $timestamp = date('Ymd_His');

        switch ($module) {
            case 'lab': // TAT Analysis
                $period = $request->get('period', 'active');
                $stats = $tatService->getLabTatBoard($period);
                $stats['sections'] = $tatService->getLabSectionTatStats();
                $stats['analyst_performance'] = $tatService->getAnalystPerformanceStats($period);
                $stats['smart_grid'] = $tatService->getSmartActionGridData('urgent');
                $stats['detailed_logs'] = $tatService->getDetailedAnalyteTatLogsPage($period, [], 1, 100)['rows'] ?? [];
                $pdf = Pdf::loadView('layouts.mas.pdf.lab_pdf', compact('stats', 'chartImage'))


                    ->setPaper('a4', 'landscape');
                $fileName = "Lab_Performance_Report_{$timestamp}.pdf";
                break;

            case 'lab-general':
                $stats = $tatService->getLabTatBoard();
                $stats['top_clients'] = $generalService->getTopClientsData() ?? [];
                $stats['monthly_trends'] = $generalService->getLabMonthlyTrends();
                $stats['geographic_data'] = $generalService->getLabGeographicData();
                $pdf = Pdf::loadView('layouts.mas.pdf.lab_general_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "Lab_General_Analytics_{$timestamp}.pdf";
                break;

            case 'lab-qc':
                $stats = $qcService->getQcStabilityBoard();
                $stats['parameter_performance'] = $qcService->getParameterPerformanceData();
                $stats['testing_matrix'] = $generalService->getTestingMatrixData();
                $pdf = Pdf::loadView('layouts.mas.pdf.lab_qc_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "Lab_QC_Stability_Report_{$timestamp}.pdf";
                break;

            case 'lab-logistics':
                $stats = $logisticsService->getLabLogisticsSummary();
                $pdf = Pdf::loadView('layouts.mas.pdf.lab_logistics_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "Lab_Logistics_Report_{$timestamp}.pdf";
                break;

            case 'crm':
                $period = $request->get('period', '1_month');
                $fromDate = $request->get('from');
                $toDate = $request->get('to');
                
                // Get fresh stats for current filters
                $topClients = DB::table('crm_customers')
                    ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
                    ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
                    ->groupBy('crm_customers.id', 'crm_customers.name')
                    ->orderByDesc('total')->limit(10)->get();

                $stats = [
                    'total_clients' => DB::table('crm_customers')->count(),
                    'recent_orders' => SampleHeader::where('created_at', '>=', now()->subDays(30))->count(),
                    'unpaid_invoices' => DB::table('customer_invoice')->count(),
                    'top_clients' => $topClients,
                ];

                $pdf = Pdf::loadView('layouts.mas.pdf.crm_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "CRM_Performance_Report_{$timestamp}.pdf";
                break;

            case 'inventory':
                $stats = $inventoryService->getInventoryRiskBoard();
                $pdf = Pdf::loadView('layouts.mas.pdf.inventory_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "Inventory_Risk_Report_{$timestamp}.pdf";
                break;

            case 'ai':
                $govData = $perfService->getMLModelMetrics();
                $intents = $perfService->getIntentBreakdown();
                $stats = [
                    'performance' => $govData['overview'] ?? [],
                    'models' => [
                        'models' => $govData['performance'] ?? [],
                        'registry' => $govData['models'] ?? []
                    ],
                    'intents' => $intents,
                    'alerts' => $govData['alerts'] ?? []
                ];
                $pdf = Pdf::loadView('layouts.mas.pdf.ai_pdf', compact('stats', 'chartImage'))
                    ->setPaper('a4', 'landscape');
                $fileName = "AI_Governance_Report_{$timestamp}.pdf";
                break;

            default:
                return abort(404, 'Visual export not supported for this module.');
        }

        if ($preview) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}
