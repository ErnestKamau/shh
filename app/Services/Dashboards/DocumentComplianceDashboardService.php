<?php

namespace App\Services\Dashboards;

use App\InventoryDepartment;
use App\Models\DocumentType;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentComplianceDashboardService
{
    use DashboardHelpers;

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
            Log::warning('Failed to load document compliance board: ' . $exception->getMessage());

            return $this->emptyDocumentBoard('Document compliance data is unavailable.');
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
            Log::warning('Failed to load ticket SLA board: ' . $exception->getMessage());

            return $this->emptyTicketBoard('Ticket SLA data is unavailable.');
        }
    }

    protected function emptyDocumentBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Document compliance data is unavailable.',
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

    protected function emptyTicketBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Ticket SLA data is unavailable.',
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
}
