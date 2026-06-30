<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\ChainOfCustody;
use App\Models\CRM\CustomerNotification;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\Models\SubmissionFormInstance;
use App\Models\SampleSubmissionRequest;
use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Models\Billing\Pricelist;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\Services\Sampleworkflow\AcceptanceFormPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AcceptanceFormService
{
    public const CUSTOMER_CERTIFICATION_TEXT = 'I certify that the above request is correct.';

    public const MANAGER_CERTIFICATION_TEXT = 'I certify that the laboratory has capability and resources to meet customer requirements.';

    public function __construct(
        private readonly AcceptanceFormPricingService $pricingService,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    /**
     * @param  array<string, mixed>|null  $disclaimerPayload
     */
    public function createFromStep1(
        ?string $submissionFormInstanceId,
        ?string $submissionRequestId,
        array $header,
        array $lines,
        string $createdBy,
        bool $raisesSampleDisclaimer = false,
        ?array $disclaimerPayload = null,
    ): AnalysisAcceptanceForm {
        return DB::transaction(function () use ($submissionFormInstanceId, $submissionRequestId, $header, $lines, $createdBy, $raisesSampleDisclaimer, $disclaimerPayload) {
            $prefill = $this->pricingService->buildPrefillFromSelection($submissionRequestId, $submissionFormInstanceId);
            $customerId = (string) ($header['crm_customer_id'] ?? $prefill['customer_id'] ?? '');
            $pricelist = $prefill['pricelist'] ?? $this->pricingService->resolvePricelist($customerId);

            $form = AnalysisAcceptanceForm::query()->create([
                'status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
                'submission_form_instance_id' => $submissionFormInstanceId,
                'sample_submission_request_id' => $submissionRequestId,
                'crm_customer_id' => $customerId,
                'pricelist_id' => $pricelist?->id,
                'currency_id' => $pricelist?->currency_id,
                'customer_name' => (string) ($header['customer_name'] ?? $prefill['customer_name'] ?? ''),
                'request_date' => $header['request_date'] ?? $prefill['request_date'],
                'number_of_samples' => (int) ($header['number_of_samples'] ?? $prefill['number_of_samples'] ?? 1),
                'mode_of_work' => (string) ($header['mode_of_work'] ?? $prefill['mode_of_work'] ?? 'Normal'),
                'date_of_sampling' => $header['date_of_sampling'] ?? $prefill['date_of_sampling'],
                'customer_certification_text' => self::CUSTOMER_CERTIFICATION_TEXT,
                'raises_sample_disclaimer' => $raisesSampleDisclaimer,
                'sample_disclaimer_payload' => $raisesSampleDisclaimer && is_array($disclaimerPayload)
                    ? $disclaimerPayload
                    : null,
                'sample_configuration_payload' => is_array($header['sample_configuration_payload'] ?? null)
                    ? $header['sample_configuration_payload']
                    : null,
                'created_by' => $createdBy,
            ]);

            $this->syncLines($form, $lines, $pricelist);
            $form->recalculateTotal();

            $this->notifyCustomer($form);

            return $form->load('lines');
        });
    }

    public function recordCustomerSignature(
        AnalysisAcceptanceForm $form,
        string $signerName,
        string $signature,
        ?string $signedAt = null
    ): AnalysisAcceptanceForm {
        if ($form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN) {
            throw new \InvalidArgumentException('Acceptance form is not awaiting customer signature.');
        }

        $form->update([
            'customer_signer_name' => $signerName,
            'customer_signature' => $signature,
            'customer_signed_at' => $signedAt ?? now(),
            'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
            'processing_error' => null,
        ]);

        return $form->fresh(['lines']);
    }

    /**
     * Accept samples with a single staff signature.
     *
     * This creates the acceptance form, creates the batch/job + samples, and assigns the job number
     * (batch code) in one step, without requiring customer/manager signing flows.
     *
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function acceptWithStaffSignature(
        ?string $submissionFormInstanceId,
        ?string $submissionRequestId,
        array $header,
        array $lines,
        string $signerName,
        string $signature,
        ?string $signedAt = null,
        ?string $createdBy = null,
    ): AnalysisAcceptanceForm {
        return DB::transaction(function () use (
            $submissionFormInstanceId,
            $submissionRequestId,
            $header,
            $lines,
            $signerName,
            $signature,
            $signedAt,
            $createdBy,
        ) {
            $prefill = $this->pricingService->buildPrefillFromSelection($submissionRequestId, $submissionFormInstanceId);
            $customerId = (string) ($header['crm_customer_id'] ?? $prefill['customer_id'] ?? '');
            $pricelist = $prefill['pricelist'] ?? $this->pricingService->resolvePricelist($customerId);

            $signedAtValue = $signedAt ?? now();

            $form = AnalysisAcceptanceForm::query()->create([
                'status' => AnalysisAcceptanceForm::STATUS_COMPLETED,
                'submission_form_instance_id' => $submissionFormInstanceId,
                'sample_submission_request_id' => $submissionRequestId,
                'crm_customer_id' => $customerId,
                'pricelist_id' => $pricelist?->id,
                'currency_id' => $pricelist?->currency_id,
                'customer_name' => (string) ($header['customer_name'] ?? $prefill['customer_name'] ?? ''),
                'request_date' => $header['request_date'] ?? $prefill['request_date'],
                'number_of_samples' => (int) ($header['number_of_samples'] ?? $prefill['number_of_samples'] ?? 1),
                'mode_of_work' => (string) ($header['mode_of_work'] ?? $prefill['mode_of_work'] ?? 'Normal'),
                'date_of_sampling' => $header['date_of_sampling'] ?? $prefill['date_of_sampling'],
                'customer_certification_text' => self::CUSTOMER_CERTIFICATION_TEXT,
                'customer_signer_name' => $signerName,
                'customer_signature' => $signature,
                'customer_signed_at' => $signedAtValue,
                'manager_signer_name' => $signerName,
                'manager_signature' => $signature,
                'manager_signed_at' => $signedAtValue,
                'manager_assignment_payload' => [
                    'assigned_analyst_ids' => [],
                    'lead_analyst_id' => null,
                    'technical_signatory_id' => null,
                ],
                'created_by' => $createdBy,
            ]);

            $this->syncLines($form, $lines, $pricelist);
            $form->recalculateTotal();

            $this->dispatchSampleCreationJob((string) $form->id);

            $completed = $this->ensureSampleBatchForManagerApproval(
                $form->fresh(['lines', 'sampleHeader'])
            )->fresh(['lines', 'sampleHeader']);

            if ($completed->sample_header_id) {
                $this->transitionBatchToSamplesInLab($completed);
                $completed = $completed->fresh(['lines', 'sampleHeader']);
            }

            return $completed;
        });
    }

    /**
     * Accept samples with receiving personnel and customer contact signatures in one step.
     *
     * Creates the acceptance form, batch/job, samples, and moves the batch to Samples In Lab.
     *
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function acceptWithDualSignatures(
        ?string $submissionFormInstanceId,
        ?string $submissionRequestId,
        array $header,
        array $lines,
        string $receivingPersonName,
        string $receivingPersonSignature,
        ?string $receivingPersonSignedAt,
        string $customerContactId,
        string $customerSignerName,
        string $customerSignature,
        ?string $customerSignedAt = null,
        ?string $createdBy = null,
    ): AnalysisAcceptanceForm {
        return DB::transaction(function () use (
            $submissionFormInstanceId,
            $submissionRequestId,
            $header,
            $lines,
            $receivingPersonName,
            $receivingPersonSignature,
            $receivingPersonSignedAt,
            $customerContactId,
            $customerSignerName,
            $customerSignature,
            $customerSignedAt,
            $createdBy,
        ) {
            $prefill = $this->pricingService->buildPrefillFromSelection($submissionRequestId, $submissionFormInstanceId);
            $customerId = (string) ($header['crm_customer_id'] ?? $prefill['customer_id'] ?? '');
            $pricelist = $prefill['pricelist'] ?? $this->pricingService->resolvePricelist($customerId);

            $receivingSignedAt = $receivingPersonSignedAt ?? now();
            $customerSignedAtValue = $customerSignedAt ?? now();

            $form = AnalysisAcceptanceForm::query()->create([
                'status' => AnalysisAcceptanceForm::STATUS_COMPLETED,
                'submission_form_instance_id' => $submissionFormInstanceId,
                'sample_submission_request_id' => $submissionRequestId,
                'crm_customer_id' => $customerId,
                'pricelist_id' => $pricelist?->id,
                'currency_id' => $pricelist?->currency_id,
                'customer_name' => (string) ($header['customer_name'] ?? $prefill['customer_name'] ?? ''),
                'request_date' => $header['request_date'] ?? $prefill['request_date'],
                'number_of_samples' => (int) ($header['number_of_samples'] ?? $prefill['number_of_samples'] ?? 1),
                'mode_of_work' => (string) ($header['mode_of_work'] ?? $prefill['mode_of_work'] ?? 'Normal'),
                'date_of_sampling' => $header['date_of_sampling'] ?? $prefill['date_of_sampling'],
                'customer_certification_text' => self::CUSTOMER_CERTIFICATION_TEXT,
                'customer_signer_name' => $customerSignerName,
                'customer_signature' => $customerSignature,
                'customer_signed_at' => $customerSignedAtValue,
                'manager_signer_name' => $receivingPersonName,
                'manager_signature' => $receivingPersonSignature,
                'manager_signed_at' => $receivingSignedAt,
                'manager_assignment_payload' => [
                    'assigned_analyst_ids' => [],
                    'lead_analyst_id' => null,
                    'technical_signatory_id' => null,
                    'customer_contact_id' => $customerContactId,
                ],
                'sample_configuration_payload' => is_array($header['sample_configuration_payload'] ?? null)
                    ? $header['sample_configuration_payload']
                    : null,
                'created_by' => $createdBy,
            ]);

            $this->syncLines($form, $lines, $pricelist);
            $form->recalculateTotal();

            $this->dispatchSampleCreationJob((string) $form->id);

            $completed = $form->fresh(['lines', 'sampleHeader']);

            if ($completed->sample_header_id) {
                $this->transitionBatchToSamplesInLab($completed);
                $completed = $completed->fresh(['lines', 'sampleHeader']);
            }

            \App\Jobs\Sampleworkflow\GenerateAcceptanceFormPdfJob::dispatch((string) $completed->id);

            return $completed;
        });
    }

    private function dispatchSampleCreationJob(string $acceptanceFormId): void
    {
        $runSync = (bool) config('sampleworkflow.acceptance_form.dispatch_sample_creation_sync', true);

        if ($runSync) {
            CreateSamplesFromAcceptanceFormJob::dispatchSync($acceptanceFormId);
        } else {
            CreateSamplesFromAcceptanceFormJob::dispatch($acceptanceFormId);
        }
    }

    /**
     * @param  list<string>  $assignedAnalystIds
     */
    public function recordManagerSignature(
        AnalysisAcceptanceForm $form,
        string $signerName,
        string $signature,
        ?string $signedAt = null,
        ?string $leadAnalystId = null,
        ?string $technicalSignatoryId = null,
        array $assignedAnalystIds = [],
    ): AnalysisAcceptanceForm {
        if ($form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            throw new \InvalidArgumentException('Acceptance form is not awaiting laboratory manager signature.');
        }

        $assignedAnalystIds = array_values(array_unique(array_filter($assignedAnalystIds)));

        if ($leadAnalystId !== null && $leadAnalystId !== '' && ! in_array($leadAnalystId, $assignedAnalystIds, true)) {
            throw new \InvalidArgumentException('Lead analyst must be selected from the assigned analysts.');
        }

        if ($assignedAnalystIds === []) {
            throw new \InvalidArgumentException('Assign at least one analyst before manager approval.');
        }

        return DB::transaction(function () use ($form, $signerName, $signature, $signedAt, $leadAnalystId, $technicalSignatoryId, $assignedAnalystIds) {
            $form->update([
                'manager_signer_name' => $signerName,
                'manager_signature' => $signature,
                'manager_signed_at' => $signedAt ?? now(),
                'manager_assignment_payload' => [
                    'assigned_analyst_ids' => $assignedAnalystIds,
                    'lead_analyst_id' => $leadAnalystId,
                    'technical_signatory_id' => $technicalSignatoryId,
                ],
                'status' => AnalysisAcceptanceForm::STATUS_COMPLETED,
            ]);

            $this->dispatchSampleCreationJob((string) $form->id);

            $completed = $form->fresh(['lines', 'sampleHeader']);

            if ($completed->sample_header_id) {
                $this->transitionBatchToSamplesInLab($completed, $leadAnalystId, $technicalSignatoryId, $assignedAnalystIds);
                $completed = $completed->fresh(['lines', 'sampleHeader']);
            }

            \App\Jobs\Sampleworkflow\GenerateAcceptanceFormPdfJob::dispatch((string) $completed->id);

            if ($completed->raises_sample_disclaimer && $completed->sampleHeader) {
                $disclaimerService = app(SampleReceivingDisclaimerService::class);
                $payload = $disclaimerService->hydrateDefaultsFromAcceptanceForm($completed);
                $disclaimerService->tryFinalizePdfAttachment(
                    $completed,
                    $completed->sampleHeader,
                    $payload,
                    Auth::id() ? (string) Auth::id() : null
                );
            }

            return $completed;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncLines(AnalysisAcceptanceForm $form, array $lines, ?Pricelist $pricelist): void
    {
        $form->lines()->delete();

        foreach (array_values($lines) as $index => $line) {
            $sampleTypeId = $line['sample_type_id'] ?? null;
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $analysisElementId = $line['analysis_element_id'] ?? null;

            $unitAmount = isset($line['unit_amount'])
                ? (float) $line['unit_amount']
                : $this->pricingService->resolveLinePrice($pricelist, $sampleTypeId, $analysisTypeId, $analysisElementId);

            AnalysisAcceptanceFormLine::query()->create([
                'analysis_acceptance_form_id' => $form->id,
                'line_no' => (int) ($line['line_no'] ?? $index + 1),
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId !== '' ? $analysisTypeId : null,
                'analysis_element_id' => $analysisElementId,
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'unit_amount' => $unitAmount,
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
                'is_approved' => (bool) ($line['is_approved'] ?? true),
                'sort_order' => (int) ($line['sort_order'] ?? $index),
            ]);
        }
    }

    private function notifyCustomer(AnalysisAcceptanceForm $form): void
    {
        CustomerNotification::query()->create([
            'customer_id' => $form->crm_customer_id,
            'entity_type' => AnalysisAcceptanceForm::class,
            'entity_id' => $form->id,
            'notification_type' => CustomerNotification::TYPE_ACCEPTANCE_FORM_SIGNING,
            'notification_description' => 'Please review the analysis prices for your request and approve the analysis acceptance form. '
                . 'Your approval will create a laboratory control number and invoice for the agreed work.',
        ]);
    }

    private function ensureSampleBatchForManagerApproval(AnalysisAcceptanceForm $form): AnalysisAcceptanceForm
    {
        if ($form->sample_header_id) {
            return $form->fresh(['sampleHeader']);
        }

        try {
            CreateSamplesFromAcceptanceFormJob::dispatchSync((string) $form->id);
        } catch (\Throwable) {
            // Job logs processing_error on the form.
        }

        $refreshed = $form->fresh(['sampleHeader']);

        if (! $refreshed?->sample_header_id) {
            throw new \RuntimeException(
                'Sample batch has not been created yet. '
                . ($refreshed?->processing_error ?: 'Ask the customer to sign the acceptance form first, or retry after batch creation completes.')
            );
        }

        return $refreshed;
    }

    /**
     * @param  list<string>  $assignedAnalystIds
     */
    private function transitionBatchToSamplesInLab(
        AnalysisAcceptanceForm $form,
        ?string $leadAnalystId = null,
        ?string $technicalSignatoryId = null,
        array $assignedAnalystIds = [],
    ): void {
        $batch = SampleHeader::query()->find((string) $form->sample_header_id);
        if (! $batch) {
            throw new \RuntimeException('Sample batch not found for this acceptance form.');
        }

        $targetStatus = 'Samples In Lab';
        $targetTrackingStage = null;
        $stages = $batch->stages($targetStatus);

        if (isset($stages[0]->id)) {
            $targetTrackingStage = $stages[0]->id;
        } else {
            $fallbackStages = $batch->stages();
            if (isset($fallbackStages[0]->id)) {
                $targetTrackingStage = $fallbackStages[0]->id;
            } else {
                $targetTrackingStage = SampleAnalysisStage::query()
                    ->where('sample_workflow', $targetStatus)
                    ->orderBy('level')
                    ->value('id');
            }
        }

        if ($targetTrackingStage !== null && ! Str::isUuid((string) $targetTrackingStage)) {
            $targetTrackingStage = null;
        }

        $previousStatus = $batch->status;
        $batch->status = $targetStatus;
        $batch->prelim_batch_status = null;
        $batch->in_lab_date = now()->format('Y-m-d');
        $batch->sample_tracking_stage = $targetTrackingStage;
        $batch->priority = $this->normalizeBatchPriority((string) $form->mode_of_work);

        if ($leadAnalystId !== null && $leadAnalystId !== '' && Str::isUuid($leadAnalystId)) {
            $batch->specialist_analyst_id = $leadAnalystId;
        }

        if ($technicalSignatoryId !== null && $technicalSignatoryId !== '' && Str::isUuid($technicalSignatoryId)) {
            $batch->approve_user_id = $technicalSignatoryId;
        }

        $batch->save();

        $this->closeOpenChainOfCustody($batch, sprintf(
            'Moved to %s after laboratory manager approval (from %s).',
            $targetStatus,
            $previousStatus ?: 'unknown'
        ));

        $custody = new ChainOfCustody();
        $custody->sample_header_id = $batch->id;
        $custody->workflow_stage = $targetStatus;
        $custody->tracking_stage_id = $batch->sample_tracking_stage;
        $custody->moved_in_by = Auth::id();
        $custody->comments = sprintf(
            'Analysis acceptance form completed by manager (from %s).',
            $previousStatus ?: 'unknown'
        );
        $custody->save();

        $this->syncAssignedAnalystsOnBatch($batch, $assignedAnalystIds, $leadAnalystId, $targetStatus);

        $instanceIds = collect([
            $form->submission_form_instance_id,
            $batch->submission_form_instance_id,
        ])
            ->filter(fn ($id) => $id !== null && (string) $id !== '')
            ->unique()
            ->values();

        if ($instanceIds->isNotEmpty()) {
            SubmissionFormInstance::query()
                ->whereIn('id', $instanceIds->all())
                ->whereIn('status', ['in_review', 'In Review', 'submitted', 'Submitted'])
                ->update(['status' => 'approved']);
        }
    }

    private function closeOpenChainOfCustody(SampleHeader $batch, string $comments): void
    {
        $movedOutBy = Auth::id();

        ChainOfCustody::query()
            ->where('sample_header_id', $batch->id)
            ->whereNull('moved_out_date')
            ->update([
                'moved_out_date' => now(),
                'moved_out_by' => $movedOutBy,
                'comments' => $comments,
            ]);
    }

    /**
     * @param  list<string>  $assignedAnalystIds
     */
    private function syncAssignedAnalystsOnBatch(
        SampleHeader $batch,
        array $assignedAnalystIds,
        ?string $leadAnalystId,
        string $batchStatus,
    ): void {
        $assignedAnalystIds = array_values(array_unique(array_filter(
            $assignedAnalystIds,
            fn (string $id) => Str::isUuid($id)
        )));

        if ($assignedAnalystIds === []) {
            return;
        }

        $defaultSectionId = $batch->sample_tracking_stage;
        if ($defaultSectionId === null || ! Str::isUuid((string) $defaultSectionId)) {
            $stagesForStatus = $batch->stages($batchStatus);
            $defaultSectionId = $stagesForStatus->isNotEmpty()
                ? $stagesForStatus->first()->id
                : ($batch->stages()->first()->id ?? null);
        }

        BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', $batchStatus)
            ->where('title', 'Analyst')
            ->delete();

        foreach ($assignedAnalystIds as $analystId) {
            $approver = new BatchLabSectionApprover();
            $approver->status = 0;
            $approver->user_id = $analystId;
            $approver->title = 'Analyst';
            $approver->lab_section_ids = $defaultSectionId !== null ? (string) $defaultSectionId : '';
            $approver->batch_id = $batch->id;
            $approver->batch_status = $batchStatus;
            $approver->show_report = 0;
            $approver->save();
        }

        if ($leadAnalystId !== null && $leadAnalystId !== '' && Str::isUuid($leadAnalystId)) {
            CapturedResult::query()
                ->where('sample_header_id', $batch->id)
                ->update(['user_id' => $leadAnalystId]);
        }
    }

    private function normalizeBatchPriority(string $modeOfWork): string
    {
        return strtolower(trim($modeOfWork)) === 'express' ? 'Express' : 'Normal';
    }
}
