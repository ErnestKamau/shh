<?php

namespace App\Services\Sampleworkflow;

use App\Jobs\Sampleworkflow\SendSampleRejectionNotifications;
use App\Models\RequestWorkflowForm;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\SampleRejectionLog;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SampleRejectionService
{
    public function __construct(
        private readonly SampleRejectionReasonService $reasonService,
        private readonly SampleRejectionPrefillService $prefillService,
    ) {}

    /**
     * @param  list<array{key: string, label: string, explanation: string}>  $selectedReasons
     */
    public function rejectFromRequestReview(
        ?string $submissionFormInstanceId,
        ?string $submissionRequestId,
        array $selectedReasons,
        ?string $userId = null,
    ): SampleRejectionLog {
        $validated = $this->validateReasons($selectedReasons);

        $header = $this->prefillService->buildFromSelection($submissionRequestId, $submissionFormInstanceId);

        if (empty($header['submission_form_instance_id']) && empty($header['sample_submission_request_id'])) {
            throw ValidationException::withMessages([
                'selection' => ['Select exactly one submission request or form row before rejecting.'],
            ]);
        }

        $userId = $userId ?? (string) Auth::id();

        return DB::transaction(function () use ($header, $validated, $userId, $submissionFormInstanceId) {
            $instance = null;
            $submissionRequest = null;

            if (! empty($header['submission_form_instance_id'])) {
                $instance = SubmissionFormInstance::query()
                    ->with(['attachmentInstances', 'crmCustomer'])
                    ->find((string) $header['submission_form_instance_id']);
            }

            if (! empty($header['sample_submission_request_id'])) {
                $submissionRequest = SampleSubmissionRequest::query()
                    ->with(['supportingDocumentInstances'])
                    ->find((string) $header['sample_submission_request_id']);
            }

            if ($instance === null && $submissionFormInstanceId) {
                throw ValidationException::withMessages([
                    'selection' => ['The selected form instance could not be found.'],
                ]);
            }

            $reviewNotes = collect($validated)
                ->map(fn (array $row) => (string) ($row['label'] ?? ''))
                ->filter()
                ->implode('; ');

            if ($instance !== null) {
                $instance->update([
                    'status' => 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => $userId,
                    'review_notes' => $reviewNotes !== '' ? $reviewNotes : 'Rejected from Samples Request Review.',
                ]);

                foreach ($instance->attachmentInstances as $attachmentInstance) {
                    if (in_array((string) $attachmentInstance->status, ['draft', 'submitted', 'in_review'], true)) {
                        $attachmentInstance->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                            'reviewed_by' => $userId,
                            'review_notes' => $reviewNotes !== '' ? $reviewNotes : 'Rejected from Samples Request Review.',
                        ]);
                    }
                }

                if ($submissionRequest === null
                    && (string) $instance->target_record_type === SampleSubmissionRequest::class
                    && ! empty($instance->target_record_id)) {
                    $submissionRequest = SampleSubmissionRequest::query()
                        ->with(['supportingDocumentInstances'])
                        ->find((string) $instance->target_record_id);
                }
            }

            if ($submissionRequest !== null) {
                $submissionRequest->update(['status' => 'rejected']);

                foreach ($submissionRequest->supportingDocumentInstances as $docInstance) {
                    if (in_array((string) $docInstance->status, ['draft', 'submitted', 'in_review'], true)) {
                        $docInstance->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                        ]);
                    }
                }
            }

            $log = SampleRejectionLog::query()->create([
                'submission_form_instance_id' => $instance?->id,
                'sample_submission_request_id' => $submissionRequest?->id,
                'request_no' => $header['request_no'],
                'client_name' => $header['client_name'],
                'date_sample_received' => $header['date_sample_received'],
                'type_of_sample' => $header['type_of_sample'],
                'number_of_samples' => $header['number_of_samples'],
                'reasons' => $validated,
                'integrity_notice' => SampleRejectionLog::INTEGRITY_NOTICE,
                'rejected_by' => $userId,
                'rejected_at' => now(),
            ]);

            $pdfPath = $this->generatePdf($log, $validated, $header, $userId);
            if ($pdfPath !== null) {
                $log->update(['pdf_path' => $pdfPath]);
            }

            $this->syncRequestWorkflowForm($log, $instance, $submissionRequest, $validated, $header, $pdfPath, $userId);

            SendSampleRejectionNotifications::dispatch($log->id);

            return $log->fresh();
        });
    }

    /**
     * @param  list<array{key: string, label: string, explanation: string}>  $selectedReasons
     * @return list<array{key: string, label: string, explanation: string}>
     */
    private function validateReasons(array $selectedReasons): array
    {
        $allowedKeys = collect($this->reasonService->getReasons())->pluck('key')->all();

        if ($allowedKeys === []) {
            throw ValidationException::withMessages([
                'reasons' => ['Rejection reasons are not configured. Contact your administrator.'],
            ]);
        }

        $validator = Validator::make(
            ['reasons' => $selectedReasons],
            [
                'reasons' => ['required', 'array', 'min:1'],
                'reasons.*.key' => ['required', 'string', 'in:'.implode(',', array_map('strval', $allowedKeys))],
                'reasons.*.label' => ['required', 'string', 'max:500'],
                'reasons.*.explanation' => ['required', 'string', 'min:3', 'max:2000'],
            ],
            [
                'reasons.required' => 'Select at least one rejection reason.',
                'reasons.*.explanation.required' => 'Provide an explanation for each selected reason.',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return array_values($validator->validated()['reasons']);
    }

    /**
     * @param  list<array{key: string, label: string, explanation: string}>  $reasons
     * @param  array<string, mixed>  $header
     */
    private function generatePdf(SampleRejectionLog $log, array $reasons, array $header, string $userId): ?string
    {
        $user = User::query()->find($userId);

        $payload = [
            'request_no' => $log->request_no,
            'sample_id' => $log->request_no,
            'name_of_client' => $log->client_name,
            'date_sample_received' => optional($log->date_sample_received)->format('Y-m-d'),
            'type_of_sample' => $log->type_of_sample,
            'number_of_samples_received' => (string) $log->number_of_samples,
            'integrity_notice' => $log->integrity_notice,
            'reasons' => $reasons,
            'laboratory_staff' => (string) ($user?->name ?? ''),
            'signature_name' => '',
            'date' => now()->format('Y-m-d'),
        ];

        try {
            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView('workflow.forms.sample-rejection-pdf', [
                'payload' => $payload,
                'logos' => $this->resolveWorkflowFormLogos(),
            ]);

            $filename = sprintf('sample-rejection-%s-%s.pdf', $log->id, date('YmdHis'));
            $relativePath = 'request-workflow-forms/' . $filename;
            Storage::disk('public')->put($relativePath, $pdf->output());

            return '/storage/' . $relativePath;
        } catch (\Throwable $exception) {
            Log::warning('Failed to render sample rejection PDF', [
                'log_id' => $log->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  list<array{key: string, label: string, explanation: string}>  $reasons
     * @param  array<string, mixed>  $header
     */
    private function syncRequestWorkflowForm(
        SampleRejectionLog $log,
        ?SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $submissionRequest,
        array $reasons,
        array $header,
        ?string $pdfPath,
        string $userId,
    ): void {
        $legacyReasons = collect($reasons)->pluck('label')->all();
        $legacyExplanation = collect($reasons)
            ->map(fn (array $row) => ($row['label'] ?? '') . ': ' . ($row['explanation'] ?? ''))
            ->implode("\n");

        RequestWorkflowForm::query()->create([
            'form_type' => 'sample_rejection',
            'sample_submission_request_id' => $submissionRequest?->id,
            'submission_form_instance_id' => $instance?->id,
            'request_reference' => (string) ($log->request_no ?? ''),
            'payload' => [
                'sample_rejection_log_id' => $log->id,
                'sample_id' => $log->request_no,
                'name_of_client' => $log->client_name,
                'date_sample_received' => $header['date_sample_received'] ?? null,
                'type_of_sample' => $log->type_of_sample,
                'number_of_samples_received' => (string) $log->number_of_samples,
                'integrity_notice' => $log->integrity_notice,
                'reasons' => $legacyReasons,
                'reason_details' => $reasons,
                'explanation' => $legacyExplanation,
                'laboratory_staff' => User::query()->find($userId)?->name ?? '',
                'date' => now()->format('Y-m-d'),
            ],
            'pdf_path' => $pdfPath,
            'created_by' => $userId,
            'submitted_at' => now(),
        ]);
    }

    /**
     * @return array{tanzania: ?string, gcla: ?string}
     */
    private function resolveSingleLogoPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }
        $path = ltrim($path, '/');
        
        if (file_exists($path)) {
            return $path;
        }

        $filename = basename($path);
        if ($filename !== '') {
            $relative = preg_replace('#^storage/#', '', $path);
            if ($relative !== $path) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }

            $fullPath = storage_path('app/companies/' . $filename);
            if (file_exists($fullPath)) {
                return $fullPath;
            }

            if (file_exists(public_path($path))) {
                return public_path($path);
            }

            if (file_exists(base_path('public/' . $path))) {
                return base_path('public/' . $path);
            }
        }

        return null;
    }

    private function getResolvedTanzaniaLogoPath(): ?string
    {
        $company = getActiveCompany();
        if ($company) {
            $paths = [
                $company->getReportLogoPath('coat_of_arms'),
                $company->getReportLogoPath('tz_flag'),
                $company->report_logo,
            ];
            foreach ($paths as $path) {
                $resolved = $this->resolveSingleLogoPath($path);
                if ($resolved) {
                    return $resolved;
                }
            }
        }

        $fallbacks = [
            public_path('images/forms/tanzanialogo.jpeg'),
            public_path('images/forms/tanzanialogo.jpg'),
            base_path('public/images/forms/tanzanialogo.jpeg'),
            base_path('public/images/forms/tanzanialogo.jpg'),
        ];
        foreach ($fallbacks as $fallback) {
            if (is_file($fallback)) {
                return $fallback;
            }
        }

        return null;
    }

    private function getResolvedGclaLogoPath(): ?string
    {
        $company = getActiveCompany();
        if ($company) {
            $paths = [
                $company->getReportLogoPath('gcla_logo'),
                $company->getReportLogoPath('gcla'),
                $company->logo,
            ];
            foreach ($paths as $path) {
                $resolved = $this->resolveSingleLogoPath($path);
                if ($resolved) {
                    return $resolved;
                }
            }
        }

        $fallbacks = [
            public_path('images/forms/gclalogo.png'),
            public_path('images/forms/gclalogo.jpg'),
            base_path('public/images/forms/gclalogo.png'),
            base_path('public/images/forms/gclalogo.jpg'),
        ];
        foreach ($fallbacks as $fallback) {
            if (is_file($fallback)) {
                return $fallback;
            }
        }

        return null;
    }

    private function resolveWorkflowFormLogos(): array
    {
        $tanzania = $this->getResolvedTanzaniaLogoPath();
        $gcla = $this->getResolvedGclaLogoPath();

        return [
            'tanzania' => $tanzania ? 'file://' . $tanzania : null,
            'gcla' => $gcla ? 'file://' . $gcla : null,
        ];
    }
}
