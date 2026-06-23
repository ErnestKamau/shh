<?php

namespace Database\Seeders\Concerns;

use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use App\Services\TestRequestForm\TestRequestFormSubmissionContext;
use App\Services\TestRequestForm\TestRequestFormSubmissionService;
use App\User;
use App\Zone;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait SeedsTrfWorkflowSamples
{
    /**
     * @return list<array<string, mixed>>
     */
    protected function trfWorkflowScenarios(): array
    {
        return [
            [
                'seed_key' => 'SEED-TRF-001',
                'document_code' => 'TRF-WATER-020',
                'description' => 'Potable water chemistry panel',
                'category_flag' => 'chemistry',
                'pipeline_stage' => 'received',
                'days_ago' => 4,
            ],
            [
                'seed_key' => 'SEED-TRF-002',
                'document_code' => 'TRF-FOOD-019',
                'description' => 'Food microbiology screening',
                'category_flag' => 'microbiology',
                'pipeline_stage' => 'acceptance_review',
                'acceptance_status' => AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
                'days_ago' => 3,
            ],
            [
                'seed_key' => 'SEED-TRF-003',
                'document_code' => 'TRF-WASTE-036',
                'description' => 'Waste water compliance check',
                'category_flag' => 'chemistry',
                'pipeline_stage' => 'accepted',
                'workflow' => ['status' => 'Samples In Lab', 'stage' => 'TEST', 'processed' => true, 'active' => true],
                'days_ago' => 2,
            ],
            [
                'seed_key' => 'SEED-TRF-004',
                'document_code' => 'TRF-WATER-020',
                'description' => 'Legionella monitoring sample',
                'category_flag' => 'legionella',
                'pipeline_stage' => 'accepted',
                'workflow' => ['status' => 'Sample Verification', 'stage' => 'QCR', 'processed' => true, 'active' => true],
                'days_ago' => 1,
            ],
            [
                'seed_key' => 'SEED-TRF-005',
                'document_code' => 'TRF-FOOD-019',
                'description' => 'Food chemistry certification batch',
                'category_flag' => 'chemistry',
                'pipeline_stage' => 'accepted',
                'workflow' => ['status' => 'Sample Approval', 'stage' => 'APP', 'processed' => true, 'active' => true],
                'days_ago' => 0,
            ],
        ];
    }

    protected function purgeLegacyPhase9SampleBatches(): void
    {
        $this->purgeAllSeedTrfChains();
        $this->purgeNonSeedSampleBatches();
        $this->purgeNonSeedTrfWorkflowChains();
    }

    /**
     * @return Collection<int, string>
     */
    protected function protectedSeedTrfHeaderIds(): Collection
    {
        $seedTrfiIds = TestRequestFormInstance::query()
            ->where('form_number', 'like', 'SEED-TRF-%')
            ->pluck('id');

        if ($seedTrfiIds->isEmpty()) {
            return collect();
        }

        return AnalysisAcceptanceForm::query()
            ->whereIn('test_request_form_instance_id', $seedTrfiIds)
            ->whereNotNull('sample_header_id')
            ->pluck('sample_header_id');
    }

    protected function purgeNonSeedSampleBatches(): void
    {
        $keepIds = $this->protectedSeedTrfHeaderIds();

        $toDelete = SampleHeader::query()
            ->when($keepIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $keepIds->all()))
            ->pluck('id');

        if ($toDelete->isEmpty()) {
            return;
        }

        $this->command?->warn('Purging '.$toDelete->count().' non-seed sample batches.');

        foreach ($toDelete as $headerId) {
            $this->deleteSampleBatchTree((string) $headerId);
        }
    }

    protected function purgeNonSeedTrfWorkflowChains(): void
    {
        $keepSfiIds = SubmissionFormInstance::query()
            ->where('form_number', 'like', 'SEED-TRF-%')
            ->pluck('id');

        $keepTrfiIds = TestRequestFormInstance::query()
            ->where('form_number', 'like', 'SEED-TRF-%')
            ->pluck('id');

        $orphanAcceptanceQuery = AnalysisAcceptanceForm::query()
            ->where(function ($query) use ($keepTrfiIds): void {
                if ($keepTrfiIds->isEmpty()) {
                    $query->whereRaw('1 = 1');
                } else {
                    $query->whereNull('test_request_form_instance_id')
                        ->orWhereNotIn('test_request_form_instance_id', $keepTrfiIds->all());
                }
            })
            ->where(function ($query) use ($keepSfiIds): void {
                if ($keepSfiIds->isEmpty()) {
                    $query->whereRaw('1 = 1');
                } else {
                    $query->whereNull('submission_form_instance_id')
                        ->orWhereNotIn('submission_form_instance_id', $keepSfiIds->all());
                }
            });

        $orphanAcceptanceQuery->each(function (AnalysisAcceptanceForm $form): void {
            if ($form->sample_header_id) {
                $this->deleteSampleBatchTree((string) $form->sample_header_id);
            } else {
                $form->lines()->delete();
                $form->delete();
            }
        });

        $enquiryQuery = SampleSubmissionRequest::query();

        if ($keepTrfiIds->isNotEmpty() || $keepSfiIds->isNotEmpty()) {
            $enquiryQuery->where(function ($query) use ($keepTrfiIds, $keepSfiIds): void {
                $query->where(function ($inner) use ($keepTrfiIds): void {
                    if ($keepTrfiIds->isEmpty()) {
                        $inner->whereRaw('1 = 1');
                    } else {
                        $inner->whereNull('test_request_form_instance_id')
                            ->orWhereNotIn('test_request_form_instance_id', $keepTrfiIds->all());
                    }
                })->where(function ($inner) use ($keepSfiIds): void {
                    if ($keepSfiIds->isEmpty()) {
                        $inner->whereRaw('1 = 1');
                    } else {
                        $inner->whereNull('submission_form_instance_id')
                            ->orWhereNotIn('submission_form_instance_id', $keepSfiIds->all());
                    }
                });
            });
        }

        $deletedEnquiries = (clone $enquiryQuery)->count();
        if ($deletedEnquiries > 0) {
            $enquiryQuery->delete();
            $this->command?->warn("Purged {$deletedEnquiries} non-seed commercial enquiries.");
        }

        $deletedTrfis = 0;
        if ($keepTrfiIds->isNotEmpty()) {
            $deletedTrfis = TestRequestFormInstance::query()
                ->whereNotIn('id', $keepTrfiIds->all())
                ->count();
            TestRequestFormInstance::query()
                ->whereNotIn('id', $keepTrfiIds->all())
                ->delete();
        } else {
            $deletedTrfis = TestRequestFormInstance::query()->count();
            TestRequestFormInstance::query()->delete();
        }

        if ($deletedTrfis > 0) {
            $this->command?->warn("Purged {$deletedTrfis} non-seed TRF instances.");
        }

        $deletedSfis = 0;
        if ($keepSfiIds->isNotEmpty()) {
            $deletedSfis = SubmissionFormInstance::query()
                ->whereNotIn('id', $keepSfiIds->all())
                ->count();
            SubmissionFormInstance::query()
                ->whereNotIn('id', $keepSfiIds->all())
                ->delete();
        } else {
            $deletedSfis = SubmissionFormInstance::query()->count();
            SubmissionFormInstance::query()->delete();
        }

        if ($deletedSfis > 0) {
            $this->command?->warn("Purged {$deletedSfis} non-seed submission form instances.");
        }
    }

    protected function purgeAllSeedTrfChains(): void
    {
        $trfis = TestRequestFormInstance::query()
            ->where('form_number', 'like', 'SEED-TRF-%')
            ->get();

        if ($trfis->isEmpty()) {
            return;
        }

        $this->command?->warn('Resetting '.$trfis->count().' SEED-TRF workflow chains.');

        foreach ($trfis as $trfi) {
            $acceptance = AnalysisAcceptanceForm::query()
                ->where('test_request_form_instance_id', $trfi->id)
                ->first();

            if ($acceptance?->sample_header_id) {
                $this->deleteSampleBatchTree((string) $acceptance->sample_header_id);
            } elseif ($acceptance) {
                $acceptance->lines()->delete();
                $acceptance->delete();
            }

            SampleSubmissionRequest::query()
                ->where('test_request_form_instance_id', $trfi->id)
                ->delete();

            if ($trfi->submission_form_instance_id) {
                SubmissionFormInstance::query()
                    ->whereKey($trfi->submission_form_instance_id)
                    ->delete();
            }

            $trfi->delete();
        }
    }

    protected function resolveTrfSubmissionForm(string $documentCode): SubmissionForm
    {
        $form = SubmissionForm::query()
            ->where('document_code', $documentCode)
            ->first();

        if ($form === null) {
            throw new \RuntimeException("Submission form {$documentCode} not found. Run TRF seeders first.");
        }

        return $form;
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @param  array<string, SampleAnalysisStage>  $stages
     * @return array{
     *     submissionFormInstance: SubmissionFormInstance,
     *     testRequestFormInstance: TestRequestFormInstance,
     *     submissionRequest: SampleSubmissionRequest,
     *     acceptanceForm: ?AnalysisAcceptanceForm,
     *     sampleHeader: ?SampleHeader
     * }
     */
    protected function seedTrfInstanceChain(
        array $scenario,
        Collection $customers,
        Collection $users,
        Collection $zones,
        array $stages,
    ): array {
        $seedKey = (string) $scenario['seed_key'];
        $submittedAt = Carbon::now()->subDays((int) ($scenario['days_ago'] ?? 0))->setTime(10, 0);
        $submissionForm = $this->resolveTrfSubmissionForm((string) $scenario['document_code']);
        $submissionForm->loadMissing('sampleTypes');

        $sampleType = $this->resolveSampleTypeForForm($submissionForm, (string) $scenario['document_code']);
        $analysisType = $this->resolveAnalysisTypeForSampleType($sampleType, (string) ($scenario['category_flag'] ?? 'chemistry'));
        $customer = $customers->values()[(int) substr($seedKey, -1) % $customers->count()];
        $zone = $zones->values()[(int) substr($seedKey, -1) % $zones->count()];
        $receivingUser = $users->first();
        $reviewUser = $users->skip(1)->first() ?? $users->first();
        $analystUser = $users->skip(2)->first() ?? $reviewUser;

        $testRequestForm = TestRequestForm::query()
            ->where('sample_type_id', $sampleType->id)
            ->where('is_active', true)
            ->first();

        if ($testRequestForm === null) {
            throw new \RuntimeException("No TestRequestForm template for sample type {$sampleType->code}.");
        }

        $labId = (string) ($analysisType->lab_id ?? '');
        $pipelineStage = (string) ($scenario['pipeline_stage'] ?? 'accepted');
        $sfiStatus = match ($pipelineStage) {
            'received' => 'received',
            'acceptance_review', 'accepted' => 'in_review',
            default => 'received',
        };

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $submissionForm->id,
            'form_number' => $seedKey,
            'title' => 'Seed TRF '.$seedKey,
            'crm_customer_id' => $customer->id,
            'zone_id' => $zone->id,
            'receiving_lab_id' => $labId !== '' ? $labId : null,
            'submitted_by' => $receivingUser?->id,
            'reviewed_by' => $reviewUser?->id,
            'reviewed_at' => $submittedAt->copy()->addHours(2),
            'submitted_at' => $submittedAt,
            'status' => $sfiStatus,
            'priority' => 'normal',
            'sequence_number' => (int) substr($seedKey, -3),
        ]);

        $row = [
            'sample_description' => (string) $scenario['description'],
            'sample_type_id' => $sampleType->id,
            'analysis_type_id' => $analysisType->id,
            'number_of_samples' => 1,
            (string) $scenario['category_flag'] => true,
        ];

        $trfi = app(TestRequestFormSubmissionService::class)->submit(
            $testRequestForm,
            [
                'customer_name' => $customer->name,
                'sampling_date' => $submittedAt->copy()->subDay()->format('Y-m-d'),
                'sample_rows' => [$row],
            ],
            new TestRequestFormSubmissionContext(
                sourceChannel: TestRequestFormInstance::CHANNEL_STAFF,
                crmCustomerId: (string) $customer->id,
                submittedBy: $receivingUser?->id,
                existingSubmissionFormInstance: $instance,
                portalSubmissionForm: $submissionForm,
                zoneId: (string) $zone->id,
                receivingLabId: $labId !== '' ? $labId : null,
                syncEnquiry: true,
                generatePdf: false,
                isDraft: false,
                markShadowAsReceived: $pipelineStage === 'received',
            ),
        );

        $trfi->update([
            'form_number' => $seedKey,
            'status' => TestRequestFormInstance::STATUS_RECEIVED,
        ]);

        $instance->update(['status' => $sfiStatus]);

        $enquiry = SampleSubmissionRequest::query()
            ->where('test_request_form_instance_id', $trfi->id)
            ->firstOrFail();

        $enquiry->update([
            'crm_customer_id' => $customer->id,
            'zone_id' => $zone->id,
            'number_of_samples' => 1,
            'sample_type_id' => $sampleType->id,
            'sample_header_id' => null,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'sample_description' => (string) $scenario['description'],
        ]);

        $pricingService = app(AcceptanceFormPricingService::class);
        $prefill = $pricingService->buildPrefillFromSelection((string) $enquiry->id, (string) $instance->id);
        $lines = $prefill['lines'] !== []
            ? $prefill['lines']
            : $this->buildFallbackAcceptanceLines($sampleType, $analysisType);

        $acceptanceForm = null;
        $header = null;

        if ($pipelineStage === 'received') {
            return $this->buildSeedChainResult($instance, $trfi, $enquiry, null, null);
        }

        $acceptanceService = app(AcceptanceFormService::class);
        $prefix = $this->resolveCategoryPrefix((string) ($scenario['category_flag'] ?? 'chemistry'));
        $signedAt = $submittedAt->copy()->addHours(3)->format('Y-m-d H:i:s');

        if ($pipelineStage === 'acceptance_review') {
            $acceptanceForm = $acceptanceService->createFromStep1(
                submissionFormInstanceId: (string) $instance->id,
                submissionRequestId: (string) $enquiry->id,
                header: [
                    'crm_customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'number_of_samples' => 1,
                    'mode_of_work' => 'Normal',
                    'request_date' => $submittedAt->format('Y-m-d'),
                    'date_of_sampling' => $submittedAt->copy()->subDay()->format('Y-m-d'),
                    'sample_configuration_payload' => [
                        [
                            'sample_type_id' => $sampleType->id,
                            'analysis_type_id' => $analysisType->id,
                            'number_of_samples' => 1,
                            'sample_code_prefix' => $prefix,
                            'instances' => [
                                ['customer_sample_id' => $seedKey, 'sample_marking' => ''],
                            ],
                        ],
                    ],
                ],
                lines: $lines,
                createdBy: (string) ($reviewUser?->id ?? ''),
            );

            $targetStatus = (string) ($scenario['acceptance_status'] ?? AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN);
            if ($targetStatus === AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
                $acceptanceForm = $acceptanceService->recordCustomerSignature(
                    $acceptanceForm,
                    (string) ($customer->name ?? 'Seed Customer'),
                    'seed-customer-signature',
                    $signedAt,
                );
            }

            return $this->buildSeedChainResult($instance, $trfi, $enquiry, $acceptanceForm, null);
        }

        $acceptanceForm = $acceptanceService->createFromStep1(
            submissionFormInstanceId: (string) $instance->id,
            submissionRequestId: (string) $enquiry->id,
            header: [
                'crm_customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'number_of_samples' => 1,
                'mode_of_work' => 'Normal',
                'request_date' => $submittedAt->format('Y-m-d'),
                'date_of_sampling' => $submittedAt->copy()->subDay()->format('Y-m-d'),
                'receipt_notification_payload' => [
                    'sample_receiving_date' => $submittedAt->format('Y-m-d'),
                    'sample_description' => (string) $scenario['description'],
                ],
                'sample_configuration_payload' => [
                    [
                        'sample_type_id' => $sampleType->id,
                        'analysis_type_id' => $analysisType->id,
                        'number_of_samples' => 1,
                        'sample_code_prefix' => $prefix,
                        'instances' => [
                            ['customer_sample_id' => $seedKey, 'sample_marking' => ''],
                        ],
                    ],
                ],
            ],
            lines: $lines,
            createdBy: (string) ($reviewUser?->id ?? ''),
        );

        $acceptanceForm = $acceptanceService->recordCustomerSignature(
            $acceptanceForm,
            (string) ($customer->name ?? 'Seed Customer'),
            'seed-customer-signature',
            $signedAt,
        );

        $acceptanceForm = $acceptanceService->recordManagerSignature(
            $acceptanceForm,
            (string) ($reviewUser?->name ?? 'Seed Manager'),
            'seed-manager-signature',
            $signedAt,
            leadAnalystId: $analystUser?->id ? (string) $analystUser->id : null,
            assignedAnalystIds: array_filter([(string) ($analystUser?->id ?? '')]),
        );

        $header = SampleHeader::query()->findOrFail($acceptanceForm->sample_header_id);
        $this->assertSeededNumberingFormats($header);
        $this->applyWorkflowStatus($header, $scenario['workflow'], $stages, $users);

        return $this->buildSeedChainResult(
            $instance,
            $trfi,
            $enquiry->fresh(),
            $acceptanceForm->fresh(['lines', 'sampleHeader']),
            $header->fresh(),
        );
    }

    /**
     * @return array{
     *     submissionFormInstance: SubmissionFormInstance,
     *     testRequestFormInstance: TestRequestFormInstance,
     *     submissionRequest: SampleSubmissionRequest,
     *     acceptanceForm: ?AnalysisAcceptanceForm,
     *     sampleHeader: ?SampleHeader
     * }
     */
    private function buildSeedChainResult(
        SubmissionFormInstance $instance,
        TestRequestFormInstance $trfi,
        SampleSubmissionRequest $enquiry,
        ?AnalysisAcceptanceForm $acceptanceForm,
        ?SampleHeader $header,
    ): array {
        return [
            'submissionFormInstance' => $instance->fresh(),
            'testRequestFormInstance' => $trfi->fresh(),
            'submissionRequest' => $enquiry->fresh(),
            'acceptanceForm' => $acceptanceForm,
            'sampleHeader' => $header,
        ];
    }

    /**
     * @param  array<string, mixed>  $workflow
     * @param  array<string, SampleAnalysisStage>  $stages
     */
    protected function applyWorkflowStatus(
        SampleHeader $header,
        array $workflow,
        array $stages,
        Collection $users,
    ): void {
        $stage = $stages[$workflow['stage']] ?? $stages['REC'];
        $receivingUser = $users->values()[0 % $users->count()];
        $specialistUser = $users->values()[1 % $users->count()];
        $verifyUser = $users->values()[2 % $users->count()];

        $receiptDate = $header->receipt_date
            ? Carbon::parse($header->receipt_date)
            : now()->subDays(2);

        $header->update([
            'status' => $workflow['status'],
            'sample_tracking_stage' => $stage->id,
            'sample_detail_processed' => (bool) ($workflow['processed'] ?? false),
            'begin_process' => (bool) ($workflow['processed'] ?? false),
            'isactive' => (bool) ($workflow['active'] ?? true),
            'receiving_officer' => $receivingUser?->id,
            'sampling_officer' => $receivingUser?->id,
            'specialist_analyst_id' => $specialistUser?->id,
            'verify_user_id' => in_array($workflow['stage'], ['QCR', 'APP'], true) ? $verifyUser?->id : null,
            'approve_user_id' => $workflow['stage'] === 'APP' ? $verifyUser?->id : null,
            'receipt_date' => $receiptDate->format('Y-m-d'),
            'processing_date' => ($workflow['processed'] ?? false)
                ? $receiptDate->copy()->addDay()->format('Y-m-d')
                : null,
            'approval_date' => $workflow['stage'] === 'APP'
                ? $receiptDate->copy()->addDays(4)->format('Y-m-d')
                : null,
            'date_expected' => ($workflow['active'] ?? true)
                ? now()->addDays(3)->format('Y-m-d H:i:s')
                : $receiptDate->copy()->addDays(5)->format('Y-m-d H:i:s'),
        ]);
    }

    protected function assertSeededNumberingFormats(SampleHeader $header): void
    {
        $numbering = app(JobSampleNumberingService::class);

        if (! $numbering->isJobNumberFormat((string) $header->batch_code)) {
            throw new \RuntimeException("Seeded batch_code {$header->batch_code} is not a valid job number.");
        }

        $detail = SampleDetails::query()->where('sample_header_id', $header->id)->first();
        if ($detail === null) {
            throw new \RuntimeException("No sample detail created for batch {$header->batch_code}.");
        }

        if (! preg_match('/^\d{9}-[MLC]\d{3}$/', (string) $detail->sample_code)) {
            throw new \RuntimeException("Seeded sample_code {$detail->sample_code} has an invalid format.");
        }

        $expectedReport = $numbering->reportNumber((string) $header->batch_code);
        if ((string) $detail->report_number !== $expectedReport) {
            throw new \RuntimeException("Seeded report_number {$detail->report_number} does not match {$expectedReport}.");
        }
    }

    private function resolveCategoryPrefix(string $categoryFlag): string
    {
        return match ($categoryFlag) {
            'microbiology' => JobSampleNumberingService::PREFIX_MICROBIOLOGY,
            'legionella' => JobSampleNumberingService::PREFIX_LEGIONELLA,
            default => JobSampleNumberingService::PREFIX_CHEMISTRY,
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildFallbackAcceptanceLines(SampleType $sampleType, AnalysisType $analysisType): array
    {
        return [
            [
                'line_no' => 1,
                'sample_type_id' => $sampleType->id,
                'analysis_type_id' => $analysisType->id,
                'parameter_label' => $analysisType->name,
                'unit_amount' => 100,
                'number_of_samples' => 1,
                'is_approved' => true,
                'sort_order' => 0,
            ],
        ];
    }

    private function resolveSampleTypeForForm(SubmissionForm $form, string $documentCode): SampleType
    {
        $sampleType = $form->sampleTypes()->first();

        if ($sampleType !== null) {
            return $sampleType;
        }

        $patterns = match ($documentCode) {
            'TRF-WATER-020' => ['water', 'WTR', 'potable'],
            'TRF-FOOD-019' => ['food', 'FOOD'],
            'TRF-WASTE-036' => ['waste', 'WWTR'],
            default => [],
        };

        $query = SampleType::query();
        $query->where(function ($builder) use ($patterns): void {
            foreach ($patterns as $pattern) {
                $builder->orWhere('name', 'ilike', '%'.$pattern.'%')
                    ->orWhere('code', 'ilike', '%'.$pattern.'%');
            }
        });

        $sampleType = $query->first();
        if ($sampleType === null) {
            throw new \RuntimeException("No sample type found for {$documentCode}.");
        }

        return $sampleType;
    }

    private function resolveAnalysisTypeForSampleType(SampleType $sampleType, string $categoryFlag): AnalysisType
    {
        $query = AnalysisType::query()
            ->where('sample_type_id', $sampleType->id)
            ->where('active', 1);

        $analysisType = match ($categoryFlag) {
            'microbiology' => (clone $query)->where(function ($builder): void {
                $builder->where('name', 'ilike', '%micro%')
                    ->orWhere('name', 'ilike', '%bacter%');
            })->first(),
            'legionella' => (clone $query)->where('name', 'ilike', '%legionella%')->first(),
            default => (clone $query)->where(function ($builder): void {
                $builder->where('name', 'ilike', '%chem%')
                    ->orWhere('name', 'not ilike', '%micro%');
            })->orderBy('name')->first(),
        };

        $analysisType ??= $query->orderBy('name')->first();

        if ($analysisType === null) {
            throw new \RuntimeException("No analysis type found for sample type {$sampleType->code}.");
        }

        return $analysisType;
    }

    private function deleteSampleBatchTree(string $headerId): void
    {
        $detailIds = SampleDetails::query()
            ->where('sample_header_id', $headerId)
            ->pluck('id');

        if ($detailIds->isNotEmpty()) {
            DB::table('captured_results')->whereIn('sample_detail_id', $detailIds)->delete();
            DB::table('results')->whereIn('sample_detail_id', $detailIds)->delete();
            SampleDetails::query()->whereIn('id', $detailIds)->delete();
        }

        AnalysisAcceptanceForm::query()
            ->where('sample_header_id', $headerId)
            ->each(function (AnalysisAcceptanceForm $form): void {
                $form->lines()->delete();
                $form->delete();
            });

        SampleSubmissionRequest::query()
            ->where('sample_header_id', $headerId)
            ->update(['sample_header_id' => null]);

        SampleHeader::query()->whereKey($headerId)->delete();
    }
}
