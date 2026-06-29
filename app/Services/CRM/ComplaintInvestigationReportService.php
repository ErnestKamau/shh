<?php

namespace App\Services\CRM;

use App\Mail\ComplaintClosureMail;
use App\Models\CRM\CapaRecord;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\CustomerContact;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ComplaintInvestigationReportService
{
    public const REPORT_INVESTIGATION = 'investigation';
    public const REPORT_CLOSURE = 'closure';
    public const REPORT_CAPA = 'capa';
    public const REPORT_NCR = 'ncr';

    /**
     * @return array<int, string>
     */
    public function sendToSelectedContacts(Complaint $complaint, array $selectedContactIds): array
    {
        $emails = $this->resolveSelectedContactEmails($complaint, $selectedContactIds);

        return $this->sendToEmails($complaint, $emails);
    }

    /**
     * @return array<int, string>
     */
    public function sendToComplaintClient(Complaint $complaint): array
    {
        $complaint->loadMissing('client');

        $email = trim((string) ($complaint->client->email ?? ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'clientEmail' => 'Customer has no valid email address.',
            ]);
        }

        return $this->sendToEmails($complaint, [$email]);
    }

    /**
     * @param  array<int, string>  $emails
     * @return array<int, string>
     */
    public function sendToEmails(Complaint $complaint, array $emails): array
    {
        $emails = collect($emails)
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($emails === []) {
            throw ValidationException::withMessages([
                'selectedContactIds' => 'No valid recipient email addresses were found.',
            ]);
        }

        try {
            $report = $this->storeGeneratedReport($complaint, self::REPORT_INVESTIGATION);
            Mail::to($emails)->send(new ComplaintClosureMail($complaint, $report['pdfContent']));
        } catch (\Throwable $e) {
            Log::error('Complaint investigation report send failed', [
                'complaint_id' => $complaint->id,
                'emails' => $emails,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Unable to send the investigation report right now. Please try again.');
        }

        return $emails;
    }

    public function generateAndAttachInvestigationReport(Complaint $complaint): Complaintattachment
    {
        return $this->storeGeneratedReport($complaint, self::REPORT_INVESTIGATION)['attachment'];
    }

    /**
     * @return array<int, Complaintattachment>
     */
    public function generateAndAttachCloseReports(Complaint $complaint): array
    {
        $attachments = [];
        $resolution = $this->getLatestResolution($complaint);

        $attachments[] = $this->storeGeneratedReport($complaint, self::REPORT_CLOSURE)['attachment'];

        if ($resolution?->car_required) {
            $attachments[] = $this->storeGeneratedReport($complaint, self::REPORT_CAPA)['attachment'];
        }

        if ($resolution?->ncr_required) {
            $attachments[] = $this->storeGeneratedReport($complaint, self::REPORT_NCR)['attachment'];
        }

        return $attachments;
    }

    /**
     * @param  array<int, int|string>  $selectedContactIds
     * @return array<int, string>
     */
    public function resolveSelectedContactEmails(Complaint $complaint, array $selectedContactIds): array
    {
        $selectedContactIds = collect($selectedContactIds)
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '')
            ->unique()
            ->values()
            ->all();

        if ($selectedContactIds === []) {
            throw ValidationException::withMessages([
                'selectedContactIds' => 'Select at least one contact to receive the report.',
            ]);
        }

        if (! $complaint->client_id) {
            throw ValidationException::withMessages([
                'selectedContactIds' => 'This complaint is not linked to a customer with report contacts.',
            ]);
        }

        $contacts = CustomerContact::query()
            ->where('crm_customer_id', $complaint->client_id)
            ->where('receive_report', 1)
            ->where('active', 1)
            ->whereIn('id', $selectedContactIds)
            ->get(['email']);

        $emails = $contacts->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($emails === []) {
            throw ValidationException::withMessages([
                'selectedContactIds' => 'The selected contacts do not have valid email addresses.',
            ]);
        }

        return $emails;
    }

    public function buildPdf(Complaint $complaint): string
    {
        return $this->buildInvestigationPdf($complaint);
    }

    /**
     * @return array{attachment: Complaintattachment, pdfContent: string}
     */
    private function storeGeneratedReport(Complaint $complaint, string $reportType): array
    {
        $report = $this->buildReportPayload($complaint, $reportType);
        $stored = Storage::disk('public')->put($report['storagePath'], $report['pdfContent']);

        if (! $stored) {
            throw new \RuntimeException('Unable to save the generated report attachment.');
        }

        $attachment = $this->replaceLatestAttachment($complaint, $report);

        return [
            'attachment' => $attachment,
            'pdfContent' => $report['pdfContent'],
        ];
    }

    /**
     * @return array{
     *   title: string,
     *   type: string,
     *   description: string,
     *   storagePath: string,
     *   pdfContent: string,
     *   reportNumber: string,
     *   version: int
     * }
     */
    private function buildReportPayload(Complaint $complaint, string $reportType): array
    {
        $complaint = $this->loadComplaint($complaint);
        $config = $this->reportConfig($complaint, $reportType);

        // Resolve version and report number before generating PDF
        $existing = Complaintattachment::query()
            ->where('complaint_id', $complaint->id)
            ->where('type', $config['type'])
            ->where('is_delete', '!=', 1)
            ->orderByDesc('id')
            ->first();

        $version = ($existing->version ?? 0) + 1;
        $reportNumber = ($existing?->report_number) ?: $this->generateUniqueReportNumber($complaint, $reportType);

        $pdfData = [
            'complaint' => $complaint,
            'reportNumber' => $reportNumber,
            'version' => $version,
        ];

        return [
            'title' => $config['title'],
            'type' => $config['type'],
            'description' => $config['description'],
            'storagePath' => $config['directory'] . '/' . $config['fileName'],
            'reportNumber' => $reportNumber,
            'version' => $version,
            'pdfContent' => match ($reportType) {
                self::REPORT_INVESTIGATION => $this->buildInvestigationPdf($pdfData),
                self::REPORT_CLOSURE => $this->buildClosurePdf($complaint),
                self::REPORT_CAPA => $this->buildCapaPdf($pdfData),
                self::REPORT_NCR => $this->buildNcrPdf($pdfData),
                default => throw new \InvalidArgumentException("Unsupported complaint report type [{$reportType}]."),
            },
        ];
    }

    private function generateUniqueReportNumber(Complaint $complaint, string $reportType): string
    {
        return match ($reportType) {
            self::REPORT_INVESTIGATION => 'LR-03',
            self::REPORT_CAPA => 'LR-04',
            self::REPORT_NCR => 'LR-04A',
            default => 'LR-03',
        };
    }

    private function buildInvestigationPdf(array $data): string
    {
        $complaint = $data['complaint'];
        $reportNumber = $data['reportNumber'];
        $version = $data['version'];

        $chainOfCustody = Chain_of_Custody_Complaint::where('complaint_id', $complaint->id)
            ->whereNotIn('action', ['Closure Report Regenerated'])
            ->orderBy('created_at', 'asc')
            ->get();

        $resolution = $this->getLatestResolution($complaint);
        $pdf = Pdf::loadView('pdfs.investigation_report', compact('complaint', 'resolution', 'chainOfCustody', 'reportNumber', 'version'));

        if (function_exists('addClosureReportPageNumbers')) {
            addClosureReportPageNumbers($pdf);
        }

        return $pdf->output();
    }

    private function buildClosurePdf(Complaint $complaint): string
    {
        $complaint = Complaint::with([
            'client',
            'resolutions.resolvedBy',
            'notes' => function ($query) {
                $query->where('is_public', 1)
                    ->where('is_delete', '!=', 1)
                    ->orderBy('created_at', 'asc');
            },
            'attachments' => function ($query) {
                $query->where('is_public', 1)
                    ->where('is_delete', '!=', 1)
                    ->where(function ($q) {
                        $q->where('type', '!=', 'Closure Report')
                            ->where('title', '!=', 'Complaint Closure Report');
                    })
                    ->orderBy('created_at', 'asc');
            },
        ])->findOrFail($complaint->id);

        $chainOfCustody = Chain_of_Custody_Complaint::where('complaint_id', $complaint->id)
            ->with('actionTaker')
            ->orderBy('created_at', 'asc')
            ->get();

        $publicNotes = $complaint->notes;
        $publicAttachments = $complaint->attachments;

        $pdf = Pdf::loadView('pdfs.closure_report', compact(
            'complaint',
            'chainOfCustody',
            'publicNotes',
            'publicAttachments'
        ));

        if (function_exists('addClosureReportPageNumbers')) {
            addClosureReportPageNumbers($pdf);
        }

        return $pdf->output();
    }

    private function buildCapaPdf(array $data): string
    {
        $complaint = $data['complaint'];
        $reportNumber = $data['reportNumber'];
        $version = $data['version'];

        $resolution = $this->getLatestResolution($complaint);
        $capaRecord = $this->getCapaRecord($complaint);

        if (! $resolution || ! $capaRecord) {
            throw new \RuntimeException('CAPA report data is incomplete and cannot be generated.');
        }

        $pdf = Pdf::loadView('pdfs.capa_report', [
            'complaint' => $complaint,
            'resolution' => $resolution,
            'capaRecord' => $capaRecord,
            'reportNumber' => $reportNumber,
            'version' => $version,
        ]);

        return $pdf->output();
    }

    private function buildNcrPdf(array $data): string
    {
        $complaint = $data['complaint'];
        $reportNumber = $data['reportNumber'];
        $version = $data['version'];

        $resolution = $this->getLatestResolution($complaint);
        $capaRecord = $this->getCapaRecord($complaint);

        if (! $resolution || ! $capaRecord) {
            throw new \RuntimeException('NCR report data is incomplete and cannot be generated.');
        }

        $pdf = Pdf::loadView('pdfs.ncr_report', [
            'complaint' => $complaint,
            'resolution' => $resolution,
            'capaRecord' => $capaRecord,
            'reportNumber' => $reportNumber,
            'version' => $version,
        ]);

        return $pdf->output();
    }

    /**
     * @param  array{title: string, type: string, description: string, storagePath: string, pdfContent: string, reportNumber: string, version: int}  $report
     */
    private function replaceLatestAttachment(Complaint $complaint, array $report): Complaintattachment
    {
        $existing = Complaintattachment::query()
            ->where('complaint_id', $complaint->id)
            ->where('type', $report['type'])
            ->where('is_delete', '!=', 1)
            ->orderByDesc('id')
            ->get();

        $attachment = $existing->shift() ?? new Complaintattachment();
        $oldRelativePath = $attachment->exists ? $this->relativeStoragePath($attachment->file_path) : null;

        foreach ($existing as $duplicate) {
            $duplicateRelativePath = $this->relativeStoragePath($duplicate->file_path);
            if ($duplicateRelativePath && Storage::disk('public')->exists($duplicateRelativePath)) {
                Storage::disk('public')->delete($duplicateRelativePath);
            }

            $duplicate->is_delete = 1;
            $duplicate->save();
        }

        $attachment->complaint_id = $complaint->id;
        $attachment->title = $report['title'];
        $attachment->type = $report['type'];
        $attachment->description = $report['description'];
        $attachment->file_path = '/storage/' . $report['storagePath'];
        $attachment->report_number = $report['reportNumber'];
        $attachment->version = $report['version'];
        $attachment->posted_by = Auth::user()?->name ?? 'System';
        $attachment->is_public = true;
        $attachment->is_delete = 0;
        $attachment->save();

        if ($oldRelativePath && $oldRelativePath !== $report['storagePath'] && Storage::disk('public')->exists($oldRelativePath)) {
            Storage::disk('public')->delete($oldRelativePath);
        }

        return $attachment;
    }

    /**
     * @return array{title: string, type: string, description: string, directory: string, fileName: string}
     */
    private function reportConfig(Complaint $complaint, string $reportType): array
    {
        $safeComplaintId = $this->sanitizeForFileName($complaint->complaint_id ?: 'complaint');
        $resolution = $this->getLatestResolution($complaint);
        $capaRecord = $this->getCapaRecord($complaint);
        $timestamp = now()->format('YmdHis');

        return match ($reportType) {
            self::REPORT_INVESTIGATION => [
                'title' => 'Laboratory Investigation Report',
                'type' => 'Investigation Report',
                'description' => 'Automatically generated investigation report.',
                'directory' => 'complaints/investigations',
                'fileName' => "Investigation_Report_{$safeComplaintId}_{$timestamp}.pdf",
            ],
            self::REPORT_CLOSURE => [
                'title' => 'Complaint Closure Report',
                'type' => 'Closure Report',
                'description' => 'Automatically generated closure report after complaint closure.',
                'directory' => 'complaints/closures',
                'fileName' => "Closure_Report_{$safeComplaintId}_{$timestamp}.pdf",
            ],
            self::REPORT_CAPA => [
                'title' => 'Corrective and Preventive Action Report',
                'type' => 'CAPA Report',
                'description' => 'Automatically generated CAPA report after complaint closure.',
                'directory' => 'complaints/capa',
                'fileName' => 'CAPA_Report_' . $this->sanitizeForFileName($resolution?->car_no ?: $safeComplaintId) . "_{$timestamp}.pdf",
            ],
            self::REPORT_NCR => [
                'title' => 'Non-conformance Report',
                'type' => 'NCR Report',
                'description' => 'Automatically generated non-conformance report after complaint closure.',
                'directory' => 'complaints/ncr',
                'fileName' => 'NCR_Report_' . $this->sanitizeForFileName($capaRecord?->lab_no ?: $safeComplaintId) . "_{$timestamp}.pdf",
            ],
            default => throw new \InvalidArgumentException("Unsupported complaint report type [{$reportType}]."),
        };
    }

    private function relativeStoragePath(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        return ltrim((string) preg_replace('#^/storage/#', '', $filePath), '/');
    }

    private function sanitizeForFileName(string $value): string
    {
        return str_replace(['/', '\\'], '-', $value);
    }

    private function loadComplaint(Complaint $complaint): Complaint
    {
        return Complaint::with(['client', 'resolutions', 'capaRecord'])->findOrFail($complaint->id);
    }

    private function getLatestResolution(Complaint $complaint): ?Complaintsresolutions
    {
        if ($complaint->relationLoaded('resolutions')) {
            return $complaint->resolutions->sortBy('id')->last();
        }

        return Complaintsresolutions::where('complaint_id', $complaint->id)->latest('id')->first();
    }

    private function getCapaRecord(Complaint $complaint): ?CapaRecord
    {
        if ($complaint->relationLoaded('capaRecord')) {
            return $complaint->capaRecord;
        }

        return CapaRecord::where('complaint_id', $complaint->id)->first();
    }
}
