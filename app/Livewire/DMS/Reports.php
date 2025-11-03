<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentAmendment;
use App\Models\DMS\DocumentAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Reports extends Component
{
    public $reportType = 'document_activity';
    public $startDate;
    public $endDate;
    public $documentTypeFilter = null;
    public $reportData = [];

    public $message = '';
    public $messageType = 'success';

    public $documentTypes = [];

    public function mount(): void
    {
        $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->documentTypes = DocumentType::active()->orderBy('name')->get();
    }

    public function generateReport(): void
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        try {
            switch ($this->reportType) {
                case 'document_activity':
                    $this->reportData = $this->generateDocumentActivityReport();
                    break;
                case 'amendment_history':
                    $this->reportData = $this->generateAmendmentHistoryReport();
                    break;
                case 'user_access':
                    $this->reportData = $this->generateUserAccessReport();
                    break;
                case 'expiring_documents':
                    $this->reportData = $this->generateExpiringDocumentsReport();
                    break;
                case 'audit_trail':
                    $this->reportData = $this->generateAuditTrailReport();
                    break;
                default:
                    $this->reportData = [];
            }

            $this->message = 'Report generated successfully';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error generating report: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    protected function generateDocumentActivityReport(): array
    {
        $query = Document::with(['documentType', 'owner', 'creator'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->documentTypeFilter) {
            $query->where('document_type_id', $this->documentTypeFilter);
        }

        $documents = $query->get();

        return [
            'total_documents' => $documents->count(),
            'by_status' => $documents->groupBy('status')->map->count(),
            'by_type' => $documents->groupBy('documentType.name')->map->count(),
            'by_owner' => $documents->groupBy('owner.name')->map->count(),
            'documents' => $documents,
        ];
    }

    protected function generateAmendmentHistoryReport(): array
    {
        $query = DocumentAmendment::with(['document', 'requester', 'authorizer', 'approver'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->documentTypeFilter) {
            $query->whereHas('document', function($q) {
                $q->where('document_type_id', $this->documentTypeFilter);
            });
        }

        $amendments = $query->get();

        return [
            'total_amendments' => $amendments->count(),
            'by_status' => $amendments->groupBy('status')->map->count(),
            'amendments' => $amendments,
        ];
    }

    protected function generateUserAccessReport(): array
    {
        $query = DocumentAuditLog::with(['auditable', 'user'])
            ->whereIn('action', ['viewed', 'downloaded'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->documentTypeFilter) {
            $query->where('auditable_type', Document::class)
                ->whereHas('auditable', function($q) {
                    $q->where('document_type_id', $this->documentTypeFilter);
                });
        }

        $logs = $query->get();

        return [
            'total_accesses' => $logs->count(),
            'by_action' => $logs->groupBy('action')->map->count(),
            'by_user' => $logs->groupBy('user.name')->map->count(),
            'logs' => $logs,
        ];
    }

    protected function generateExpiringDocumentsReport(): array
    {
        $query = Document::with(['documentType', 'owner'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', $this->startDate)
            ->where('expiry_date', '<=', $this->endDate)
            ->where('is_archived', false);

        if ($this->documentTypeFilter) {
            $query->where('document_type_id', $this->documentTypeFilter);
        }

        $documents = $query->orderBy('expiry_date')->get();

        return [
            'total_expiring' => $documents->count(),
            'already_expired' => $documents->filter->isExpired()->count(),
            'documents' => $documents,
        ];
    }

    protected function generateAuditTrailReport(): array
    {
        $query = DocumentAuditLog::with(['auditable', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->documentTypeFilter) {
            $query->where('auditable_type', Document::class)
                ->whereHas('auditable', function($q) {
                    $q->where('document_type_id', $this->documentTypeFilter);
                });
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        return [
            'total_activities' => $logs->count(),
            'by_action' => $logs->groupBy('action')->map->count(),
            'by_user' => $logs->groupBy('user.name')->map->count(),
            'logs' => $logs,
        ];
    }

    public function exportPDF(): void
    {
        $this->message = 'PDF export functionality coming soon';
        $this->messageType = 'info';
    }

    public function exportExcel(): void
    {
        $this->message = 'Excel export functionality coming soon';
        $this->messageType = 'info';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.dms.reports-component');
    }
}

