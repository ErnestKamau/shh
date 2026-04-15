<?php

namespace App\Services\AI;

use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\NonConformance;
use App\Models\CRM\Complaint;
use App\Jobs\AI\RagIndexJob;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseService
{
    /**
     * Index a Corrective Action (CAPA).
     */
    public function indexCorrectiveAction(CorrectiveAction $ca): void
    {
        $content = "CAPA #{$ca->capa_number}: {$ca->title}\n\n" .
                   "Description: {$ca->description}\n\n" .
                   "Implementation Notes: {$ca->implementation_notes}";

        $metadata = [
            'capa_number' => $ca->capa_number,
            'status' => $ca->status_name,
            'priority' => $ca->priority_name,
            'department' => $ca->department,
            'category' => $ca->capa_category_name,
        ];

        RagIndexJob::dispatch('corrective_actions', $content, get_class($ca), $ca->id, $metadata);
    }

    /**
     * Index a Non-Conformance (Audit Finding/Anomaly).
     */
    public function indexNonConformance(NonConformance $nc): void
    {
        $content = "Finding/Anomaly: {$nc->title}\n\n" .
                   "Description: {$nc->description}\n\n" .
                   "Root Cause: {$nc->root_cause}\n\n" .
                   "Correction: {$nc->correction}";

        $metadata = [
            'severity' => $nc->severity_name,
            'source' => $nc->source_type, // Internal, external, etc.
            'department' => $nc->department_name,
        ];

        RagIndexJob::dispatch('anomalies', $content, get_class($nc), $nc->id, $metadata);
    }

    /**
     * Index a Complaint (Incident).
     */
    public function indexComplaint(Complaint $complaint): void
    {
        $content = "Incident/Complaint #{$complaint->ticket_no}: {$complaint->subject}\n\n" .
                   "Description: {$complaint->description}\n\n" .
                   "Resolution: {$complaint->resolution_details}";

        $metadata = [
            'ticket_no' => $complaint->ticket_no,
            'customer' => $complaint->customer_name,
            'priority' => $complaint->priority_name,
            'status' => $complaint->status_name,
        ];

        RagIndexJob::dispatch('incidents', $content, get_class($complaint), $complaint->id, $metadata);
    }

    /**
     * Index a generic Document (SOP).
     */
    public function indexDocument(Model $doc, string $collection = 'sops'): void
    {
        $title = $doc->title ?? $doc->name ?? 'Untitled Document';
        $body = $doc->body ?? $doc->content ?? $doc->description ?? '';
        $documentNo = $doc->document_number ?? null;

        $header = $documentNo
            ? "Document: {$title} ({$documentNo})"
            : "Document: {$title}";

        $content = $header . "\n\n" . $body;

        $metadata = [
            'document_no' => $documentNo,
            'type' => $doc->document_type_name
                ?? ($doc->documentType->name ?? $collection),
            'department' => $doc->department_name
                ?? ($doc->department->name ?? null),
        ];

        RagIndexJob::dispatch($collection, $content, get_class($doc), $doc->id, $metadata);
    }
}
