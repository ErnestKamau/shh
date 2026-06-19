<?php

namespace App\Services\TestRequestForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\Services\Commercial\CommercialEnquiryFromTrfService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class TestRequestFormSubmissionService
{
    public function __construct(
        private TestRequestFormDataMapper $mapper,
        private SubmissionFormSubmissionService $submissionFormService,
        private CommercialEnquiryFromTrfService $enquiryFromTrfService,
        private TestRequestFormPdfService $pdfService,
    ) {}

    public function submitFromPortalInstance(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm,
        TestRequestForm $template,
    ): TestRequestFormInstance {
        $instance->loadMissing(['values.element', 'crmCustomer']);
        $submissionForm->loadMissing('sampleTypes');

        $rawFormData = $this->mapper->fromSubmissionFormInstance($instance, $submissionForm, $template);
        $context = new TestRequestFormSubmissionContext(
            sourceChannel: TestRequestFormInstance::CHANNEL_PORTAL,
            crmCustomerId: $instance->crm_customer_id,
            portalAccountId: $instance->portal_account_id,
            submittedBy: $instance->submitted_by ?? Auth::id(),
            existingSubmissionFormInstance: $instance,
            portalSubmissionForm: $submissionForm,
            zoneId: $instance->zone_id,
            receivingLabId: $instance->receiving_lab_id,
        );

        return $this->submit($template, $rawFormData, $context);
    }

    public function submit(
        TestRequestForm $template,
        array $rawFormData,
        TestRequestFormSubmissionContext $context,
    ): TestRequestFormInstance {
        return DB::transaction(function () use ($template, $rawFormData, $context): TestRequestFormInstance {
            $normalized = $this->mapper->normalizeFormData($rawFormData, $template);
            $portalForm = $context->portalSubmissionForm;
            $shadow = $context->existingSubmissionFormInstance;

            if ($shadow === null && $portalForm !== null) {
                $shadow = $this->createShadowInstance($portalForm, $context);
            }

            $formNumber = ($shadow?->form_number)
                ? ['format' => (string) $shadow->form_number, 'sequence_no' => (int) ($shadow->sequence_number ?? 0)]
                : TrfFormNumberGenerator::generate($template, $portalForm);
            $submittedAt = now();
            $status = $context->isDraft
                ? TestRequestFormInstance::STATUS_DRAFT
                : TestRequestFormInstance::STATUS_SUBMITTED;

            $trfi = TestRequestFormInstance::query()->updateOrCreate(
                [
                    'submission_form_instance_id' => $shadow?->id,
                ],
                [
                    'test_request_form_id' => $template->id,
                    'form_data' => $normalized,
                    'form_number' => $shadow?->form_number ?? $formNumber['format'],
                    'sequence_number' => $shadow?->sequence_number ?? $formNumber['sequence_no'],
                    'crm_customer_id' => $context->crmCustomerId ?? $shadow?->crm_customer_id,
                    'portal_account_id' => $context->portalAccountId ?? $shadow?->portal_account_id,
                    'source_channel' => $context->sourceChannel,
                    'submitted_by' => $context->submittedBy ?? Auth::id(),
                    'submitted_at' => $submittedAt,
                    'zone_id' => $context->zoneId ?? $shadow?->zone_id,
                    'receiving_lab_id' => $context->receivingLabId ?? $shadow?->receiving_lab_id,
                    'sampling_schedule_id' => $context->samplingScheduleId,
                    'status' => $status,
                    'created_by' => $context->submittedBy ?? Auth::id(),
                ]
            );

            if ($shadow !== null && $portalForm !== null) {
                $this->syncShadowFromTrfi($shadow, $portalForm, $normalized, $trfi, $context);
            }

            if ($context->syncEnquiry) {
                if ($context->isDraft) {
                    $this->enquiryFromTrfService->syncFromTrfi($trfi->fresh(), asDraft: true);
                } else {
                    $this->enquiryFromTrfService->syncFromTrfi($trfi->fresh());
                }
            }

            if ($context->generatePdf && ! $context->isDraft) {
                $this->generatePdfSafely($trfi);
            }

            return $trfi->fresh([
                'testRequestForm.sampleType',
                'crmCustomer',
                'submissionFormInstance',
                'sampleSubmissionRequest',
            ]);
        });
    }

    private function createShadowInstance(
        SubmissionForm $portalForm,
        TestRequestFormSubmissionContext $context,
    ): SubmissionFormInstance {
        $userId = $context->submittedBy ?? Auth::id();

        $shadow = SubmissionFormInstance::query()->create([
            'submission_form_id' => $portalForm->id,
            'title' => 'Test Request Form - '.now()->format('Y-m-d H:i'),
            'submitted_by' => $userId,
            'status' => TestRequestFormInstance::STATUS_DRAFT,
            'priority' => 'normal',
            'crm_customer_id' => $context->crmCustomerId,
            'portal_account_id' => $context->portalAccountId,
            'zone_id' => $context->zoneId,
            'receiving_lab_id' => $context->receivingLabId,
        ]);

        $user = $userId ? User::query()->find($userId) : null;
        if ($user) {
            $shadow->logAction('created', $user);
        }

        return $shadow;
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    private function syncShadowFromTrfi(
        SubmissionFormInstance $shadow,
        SubmissionForm $portalForm,
        array $normalized,
        TestRequestFormInstance $trfi,
        TestRequestFormSubmissionContext $context,
    ): void {
        $requestData = TestRequestFormInstance::mapToSubmissionFormRequestData(
            $normalized,
            $trfi->testRequestForm,
        );
        $elements = $this->submissionFormService->elementsForForm($portalForm);
        $request = new Request();
        $request->merge($requestData);
        $this->submissionFormService->processFormData($shadow, $request, $elements);

        if (empty($shadow->form_number)) {
            $this->submissionFormService->assignFormNumberWithRetry($shadow, $portalForm);
            $shadow->refresh();
            $trfi->update([
                'form_number' => $shadow->form_number,
                'sequence_number' => $shadow->sequence_number,
            ]);
        }

        if ($context->isDraft) {
            return;
        }

        $user = ($context->submittedBy ?? Auth::id())
            ? User::query()->find($context->submittedBy ?? Auth::id())
            : Auth::user();

        if ($shadow->status === TestRequestFormInstance::STATUS_DRAFT && $user) {
            $shadow->submit($user);
            $shadow->refresh();
        }

        if ($context->markShadowAsReceived && $user) {
            $shadow->markAsReceived($user);
        }

        $trfi->update(['status' => $context->markShadowAsReceived
            ? TestRequestFormInstance::STATUS_RECEIVED
            : TestRequestFormInstance::STATUS_SUBMITTED,
        ]);
    }

    private function generatePdfSafely(TestRequestFormInstance $trfi): void
    {
        try {
            $this->pdfService->generateAndStore($trfi->fresh());
        } catch (\Throwable $exception) {
            Log::warning('Test request form PDF generation failed.', [
                'trfi_id' => $trfi->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
