<?php

namespace Tests\Feature\Commercial;

use App\ChainOfCustody;
use App\DTOs\Commercial\JobCoverageOutcome;
use App\Enums\Commercial\SampleHeaderPoStatus;
use App\Events\Commercial\PurchaseOrderLineExhausted;
use App\Events\Commercial\PurchaseOrderLineThresholdReached;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\System\SystemConfiguration;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Commercial\JobPurchaseOrderCoverageService;
use App\Services\Commercial\PurchaseOrderAllocationService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SplitJobReportGroupService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class JobPurchaseOrderCoverageServiceTest extends TestCase
{
    use RefreshDatabase;

    private JobPurchaseOrderCoverageService $coverage;

    private PurchaseOrderAllocationService $allocation;

    private User $user;

    private string $sampleTypeId;

    private string $analysisTypeId;

    private string $secondAnalysisTypeId;

    private int $requestNumber;

    protected function setUp(): void
    {
        parent::setUp();

        config(['purchase_orders.enabled' => true]);
        Event::fake([PurchaseOrderLineExhausted::class, PurchaseOrderLineThresholdReached::class]);

        $this->coverage = app(JobPurchaseOrderCoverageService::class);
        $this->allocation = app(PurchaseOrderAllocationService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->requestNumber = random_int(100000, 800000);

        $this->createCatalogue();
    }

    public function test_job_fully_covered_by_its_po_is_marked_covered_and_not_split(): void
    {
        $customer = $this->customer('credit');
        [$po, $line] = $this->blanketFor($customer, 10);
        $job = $this->jobFor($customer, 4);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 4, $po));

        $this->assertSame(SampleHeaderPoStatus::Covered, $outcome->status);
        $this->assertFalse($outcome->wasSplit());
        $this->assertSame(4, $outcome->coveredSamples);
        $this->assertSame(SampleHeaderPoStatus::Covered->value, $job->fresh()->po_status);
        $this->assertSame((string) $po->id, (string) $job->fresh()->customer_purchase_order_id);
        $this->assertSame(4, $line->fresh()->committed_qty);
        $this->assertSame(0, SampleHeader::query()->where('split_from_sample_header_id', $job->id)->count());
    }

    public function test_partial_cover_splits_uncovered_samples_onto_an_awaiting_po_job(): void
    {
        $customer = $this->customer('credit');
        [$po, $line] = $this->blanketFor($customer, 3);
        $job = $this->jobFor($customer, 5);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 5, $po));

        $this->assertTrue($outcome->wasSplit());
        $this->assertSame(3, $outcome->coveredSamples);
        $this->assertSame(2, $outcome->heldSamples);

        $held = SampleHeader::query()->findOrFail($outcome->heldSampleHeaderId);
        $this->assertNotSame($job->batch_code, $held->batch_code);
        $this->assertSame((string) $job->id, (string) $held->split_from_sample_header_id);
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $held->po_status);
        $this->assertNotNull($held->po_held_at);
        $this->assertSame((string) $po->id, (string) $held->customer_purchase_order_id);
        $this->assertSame('Samples Request Review', $held->status);

        $this->assertSame(
            [$job->batch_code.'-001', $job->batch_code.'-002', $job->batch_code.'-003'],
            $this->sampleCodesOn($job),
        );
        $this->assertSame([$job->batch_code.'-004', $job->batch_code.'-005'], $this->sampleCodesOn($held));
        $this->assertSame(2, SampleAnalysisTypeRelation::query()->where('batch_id', $held->id)->count());
        $this->assertSame(3, SampleAnalysisTypeRelation::query()->where('batch_id', $job->id)->count());

        $this->assertSame(SampleHeaderPoStatus::Covered->value, $job->fresh()->po_status);
        $this->assertSame(3, $line->fresh()->committed_qty);
        $this->assertSame(0, $line->fresh()->remaining_qty);
        $this->assertTrue(ChainOfCustody::query()->where('sample_header_id', $held->id)->exists());
    }

    public function test_sample_is_covered_only_when_every_analysis_has_balance(): void
    {
        $customer = $this->customer('credit');
        [$po, $firstLine] = $this->blanketFor($customer, 10);
        $secondLine = $this->addLine($po, $this->secondAnalysisTypeId, 1);
        $job = $this->jobFor($customer, 3, [$this->analysisTypeId, $this->secondAnalysisTypeId]);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 3, $po));

        $this->assertSame(1, $outcome->coveredSamples);
        $this->assertSame(2, $outcome->heldSamples);
        $this->assertSame(1, $firstLine->fresh()->committed_qty, 'Units drawn for held samples go back to the PO.');
        $this->assertSame(9, $firstLine->fresh()->remaining_qty);
        $this->assertSame(1, $secondLine->fresh()->committed_qty);
        $this->assertSame([$job->batch_code.'-001'], $this->sampleCodesOn($job));
    }

    public function test_whole_job_is_held_when_nothing_is_covered_and_the_draw_is_given_back(): void
    {
        $customer = $this->customer('credit');
        [$po, $line] = $this->blanketFor($customer, 10);
        $job = $this->jobFor($customer, 2, [$this->analysisTypeId, $this->secondAnalysisTypeId]);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 2, $po));

        $this->assertTrue($outcome->isHeldWhole());
        $this->assertFalse($outcome->wasSplit());
        $this->assertSame(2, $outcome->heldSamples);
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $job->fresh()->po_status);
        $this->assertSame((string) $po->id, (string) $job->fresh()->customer_purchase_order_id);
        $this->assertSame(0, $line->fresh()->committed_qty);
        $this->assertSame(10, $line->fresh()->remaining_qty);
    }

    public function test_customer_that_needs_a_po_but_has_none_is_held_whole(): void
    {
        $customer = $this->customer('credit');
        $job = $this->jobFor($customer, 3);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 3));

        $this->assertTrue($outcome->isHeldWhole());
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $job->fresh()->po_status);
        $this->assertNotNull($job->fresh()->po_held_at);
        $this->assertNull($job->fresh()->customer_purchase_order_id);
    }

    public function test_customer_without_a_po_requirement_is_never_split(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 3);
        $job = $this->jobFor($customer, 5);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 5, $po));

        $this->assertSame(SampleHeaderPoStatus::NotRequired, $outcome->status);
        $this->assertFalse($outcome->wasSplit());
        $this->assertSame(5, SampleDetails::query()->where('sample_header_id', $job->id)->count());
        $this->assertSame(3, $line->fresh()->committed_qty);
        $this->assertSame(SampleHeaderPoStatus::NotRequired->value, $job->fresh()->po_status);
    }

    public function test_customer_without_a_po_requirement_and_no_po_is_not_required(): void
    {
        $customer = $this->customer();
        $job = $this->jobFor($customer, 2);

        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 2));

        $this->assertSame(SampleHeaderPoStatus::NotRequired, $outcome->status);
        $this->assertSame(SampleHeaderPoStatus::NotRequired->value, $job->fresh()->po_status);
    }

    public function test_nothing_happens_when_purchase_orders_are_disabled_or_there_is_no_enquiry(): void
    {
        $customer = $this->customer('credit');
        $job = $this->jobFor($customer, 2);

        $this->assertNull($this->coverage->coverNewJob($job, null, (string) $this->user->id));

        config(['purchase_orders.enabled' => false]);
        $this->assertNull($this->coverage->coverNewJob($job, $this->enquiryFor($customer, 2), (string) $this->user->id));
        $this->assertNull($job->fresh()->po_status);
    }

    public function test_applying_a_po_releases_the_held_job_to_the_lab(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 3, heldSamples: 2);
        [$topUp, $topUpLine] = $this->blanketFor($customer, 5);

        $outcome = $this->coverage->applyPurchaseOrder($held, (string) $topUp->id, (string) $this->user->id);

        $this->assertSame(2, $outcome->coveredSamples);
        $this->assertSame(0, $outcome->heldSamples);
        $this->assertNull($outcome->heldSampleHeaderId);

        $held->refresh();
        $this->assertSame('Samples In Lab', $held->status);
        $this->assertSame(SampleHeaderPoStatus::Covered->value, $held->po_status);
        $this->assertSame((string) $topUp->id, (string) $held->customer_purchase_order_id);
        $this->assertNotNull($held->po_released_at);
        $this->assertSame((string) $this->user->id, (string) $held->po_released_by);
        $this->assertSame(2, $topUpLine->fresh()->committed_qty);
    }

    public function test_applying_a_po_that_covers_only_some_samples_splits_the_rest_again(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 4);
        [$topUp, $topUpLine] = $this->blanketFor($customer, 1);

        $outcome = $this->coverage->applyPurchaseOrder($held, (string) $topUp->id, (string) $this->user->id);

        $this->assertSame(1, $outcome->coveredSamples);
        $this->assertSame(3, $outcome->heldSamples);
        $this->assertSame('Samples In Lab', $held->fresh()->status);
        $this->assertSame(1, SampleDetails::query()->where('sample_header_id', $held->id)->count());

        $stillHeld = SampleHeader::query()->findOrFail($outcome->heldSampleHeaderId);
        $this->assertSame((string) $held->split_from_sample_header_id, (string) $stillHeld->split_from_sample_header_id, 'Re-split parts stay in the root job\'s report group.');
        $this->assertSame(JobPurchaseOrderCoverageService::WORKFLOW_STATUS_AWAITING_PO, $stillHeld->status);
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $stillHeld->po_status);
        $this->assertSame((string) $topUp->id, (string) $stillHeld->customer_purchase_order_id);
        $this->assertSame(3, SampleDetails::query()->where('sample_header_id', $stillHeld->id)->count());
        $this->assertSame(1, $topUpLine->fresh()->committed_qty);
    }

    public function test_a_whole_held_job_can_be_released_after_its_draw_was_given_back(): void
    {
        $customer = $this->customer('credit');
        [$po, $line] = $this->blanketFor($customer, 10);
        $job = $this->jobFor($customer, 2, [$this->analysisTypeId, $this->secondAnalysisTypeId]);
        $this->coverNewJob($job, $this->enquiryFor($customer, 2, $po));
        $this->markAccepted($job);

        $secondLine = $this->addLine($po, $this->secondAnalysisTypeId, 2);
        $outcome = $this->coverage->applyPurchaseOrder($job->fresh(), (string) $po->id, (string) $this->user->id);

        $this->assertSame(2, $outcome->coveredSamples);
        $this->assertSame('Samples In Lab', $job->fresh()->status);
        $this->assertSame(2, $line->fresh()->committed_qty);
        $this->assertSame(2, $secondLine->fresh()->committed_qty);
    }

    public function test_applying_a_po_with_no_matching_balance_is_rejected_and_writes_nothing(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 2);
        $otherPo = CustomerPurchaseOrder::factory()->blanket()->create(['customer_id' => (string) $customer->id]);
        $otherLine = $this->addLine($otherPo, $this->secondAnalysisTypeId, 5);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->coverage->applyPurchaseOrder($held, (string) $otherPo->id, (string) $this->user->id));

        $this->assertSame(JobPurchaseOrderCoverageService::WORKFLOW_STATUS_AWAITING_PO, $held->fresh()->status);
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $held->fresh()->po_status);
        $this->assertSame(0, $otherLine->fresh()->committed_qty);
    }

    public function test_cannot_apply_another_customers_po(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 1);
        [$foreignPo] = $this->blanketFor($this->customer('credit'), 10);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->coverage->applyPurchaseOrder($held, (string) $foreignPo->id, (string) $this->user->id));
        $this->assertSame(SampleHeaderPoStatus::AwaitingPo->value, $held->fresh()->po_status);
    }

    public function test_cannot_apply_a_po_to_a_job_that_is_not_awaiting_one(): void
    {
        $customer = $this->customer('credit');
        [$po] = $this->blanketFor($customer, 10);
        $job = $this->jobFor($customer, 2);
        $this->coverNewJob($job, $this->enquiryFor($customer, 2, $po));

        $this->assertValidationError('job', fn () => $this->coverage->applyPurchaseOrder($job->fresh(), (string) $po->id, (string) $this->user->id));
    }

    public function test_held_job_still_in_request_review_cannot_take_a_po_yet(): void
    {
        $customer = $this->customer('credit');
        $job = $this->jobFor($customer, 2);
        $this->coverNewJob($job, $this->enquiryFor($customer, 2));
        [$po] = $this->blanketFor($customer, 10);

        $this->assertValidationError('job', fn () => $this->coverage->applyPurchaseOrder($job->fresh(), (string) $po->id, (string) $this->user->id));
    }

    public function test_preview_forecasts_the_release_without_drawing(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 4);
        [$topUp, $topUpLine] = $this->blanketFor($customer, 2);

        $this->assertSame(['covered' => 2, 'held' => 2], $this->coverage->previewApply($held, $topUp));
        $this->assertSame(0, $topUpLine->fresh()->committed_qty);
    }

    public function test_cancelling_a_held_job_takes_it_out_of_the_workflow_and_report_group(): void
    {
        $customer = $this->customer('credit');
        $held = $this->heldSplitPart($customer, coveredSamples: 2, heldSamples: 1);
        $root = SampleHeader::query()->findOrFail($held->split_from_sample_header_id);

        $this->coverage->cancelHeldJob($held, '  Customer will not issue a PO  ', (string) $this->user->id);

        $held->refresh();
        $this->assertSame(JobPurchaseOrderCoverageService::WORKFLOW_STATUS_CANCELLED, $held->status);
        $this->assertSame(0, (int) $held->isactive);
        $this->assertNull($held->sample_tracking_stage);
        $this->assertSame(SampleHeaderPoStatus::Cancelled->value, $held->po_status);
        $this->assertSame('Customer will not issue a PO', $held->po_cancel_reason);
        $this->assertSame((string) $this->user->id, (string) $held->po_cancelled_by);
        $this->assertNotNull($held->po_cancelled_at);
        $this->assertSame([(string) $root->id], app(SplitJobReportGroupService::class)->reportGroupIds($root));
        $this->assertFalse($this->coverage->heldJobsQuery()->whereKey($held->id)->exists());
    }

    public function test_cannot_cancel_a_job_that_is_not_awaiting_a_po(): void
    {
        $customer = $this->customer('credit');
        [$po] = $this->blanketFor($customer, 10);
        $job = $this->jobFor($customer, 1);
        $this->coverNewJob($job, $this->enquiryFor($customer, 1, $po));

        $this->assertValidationError('job', fn () => $this->coverage->cancelHeldJob($job->fresh(), 'No PO', (string) $this->user->id));
        $this->assertSame(SampleHeaderPoStatus::Covered->value, $job->fresh()->po_status);
    }

    public function test_held_jobs_query_lists_only_accepted_active_awaiting_po_jobs(): void
    {
        $customer = $this->customer('credit');
        $accepted = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 1);

        $inReview = $this->jobFor($customer, 1);
        $this->coverNewJob($inReview, $this->enquiryFor($customer, 1));

        $ids = $this->coverage->heldJobsQuery()->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $this->assertContains((string) $accepted->id, $ids);
        $this->assertNotContains((string) $inReview->id, $ids);
        $this->assertNotContains((string) $accepted->split_from_sample_header_id, $ids);
    }

    public function test_drawable_orders_are_the_customers_live_orders(): void
    {
        $customer = $this->customer('credit');
        [$live] = $this->blanketFor($customer, 10);
        [$expired] = $this->blanketFor($customer, 10);
        $expired->forceFill(['valid_from' => now()->subYear()->toDateString(), 'valid_to' => now()->subDay()->toDateString()])->save();
        [$foreign] = $this->blanketFor($this->customer('credit'), 10);
        $held = $this->heldSplitPart($customer, coveredSamples: 1, heldSamples: 1);

        $ids = $this->coverage->drawableOrdersFor($held)->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $this->assertContains((string) $live->id, $ids);
        $this->assertNotContains((string) $expired->id, $ids);
        $this->assertNotContains((string) $foreign->id, $ids);
    }

    public function test_acceptance_summary_describes_the_split(): void
    {
        $customer = $this->customer('credit');
        [$po] = $this->blanketFor($customer, 3);
        $job = $this->jobFor($customer, 5);
        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 5, $po));
        $held = SampleHeader::query()->findOrFail($outcome->heldSampleHeaderId);

        $this->assertSame(
            sprintf('Job %s: 3 samples on PO %s. Job %s: Awaiting PO, 2 samples held out of the lab.', $job->batch_code, $po->po_number, $held->batch_code),
            $this->coverage->acceptanceSummary($job->fresh()),
        );
    }

    public function test_acceptance_summary_is_silent_for_a_plain_job_without_a_po_requirement(): void
    {
        $customer = $this->customer();
        $job = $this->jobFor($customer, 2);
        $this->coverNewJob($job, $this->enquiryFor($customer, 2));

        $this->assertNull($this->coverage->acceptanceSummary($job->fresh()));
        $this->assertNull($this->coverage->acceptanceSummary($this->jobFor($customer, 1)));
    }

    public function test_signing_acceptance_sends_covered_job_to_the_lab_and_parks_its_held_part(): void
    {
        $customer = $this->customer('credit');
        [$po] = $this->blanketFor($customer, 2);
        $job = $this->jobFor($customer, 3);
        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, 3, $po));

        $this->routeAcceptedBatch($job);

        $held = SampleHeader::query()->findOrFail($outcome->heldSampleHeaderId);
        $this->assertSame('Samples In Lab', $job->fresh()->status);
        $this->assertSame(JobPurchaseOrderCoverageService::WORKFLOW_STATUS_AWAITING_PO, $held->status);
        $this->assertNull($held->sample_tracking_stage);
        $this->assertTrue(
            ChainOfCustody::query()
                ->where('sample_header_id', $held->id)
                ->where('comments', 'like', '%held as Awaiting PO%')
                ->exists()
        );
    }

    public function test_signing_acceptance_parks_a_wholly_held_job(): void
    {
        $customer = $this->customer('credit');
        $job = $this->jobFor($customer, 2);
        $this->coverNewJob($job, $this->enquiryFor($customer, 2));

        $this->routeAcceptedBatch($job);

        $this->assertSame(JobPurchaseOrderCoverageService::WORKFLOW_STATUS_AWAITING_PO, $job->fresh()->status);
        $this->assertTrue($this->coverage->heldJobsQuery()->whereKey($job->id)->exists());
    }

    private function coverNewJob(SampleHeader $job, SampleSubmissionRequest $enquiry): JobCoverageOutcome
    {
        $outcome = $this->coverage->coverNewJob($job, $enquiry, (string) $this->user->id);
        $this->assertNotNull($outcome);

        return $outcome;
    }

    /**
     * Create a credit job split at creation and accept it, returning the held part sitting in Awaiting PO.
     */
    private function heldSplitPart(CRMCustomer $customer, int $coveredSamples, int $heldSamples): SampleHeader
    {
        [$po] = $this->blanketFor($customer, $coveredSamples);
        $total = $coveredSamples + $heldSamples;
        $job = $this->jobFor($customer, $total);
        $outcome = $this->coverNewJob($job, $this->enquiryFor($customer, $total, $po));
        $this->assertNotNull($outcome->heldSampleHeaderId);

        $held = SampleHeader::query()->findOrFail($outcome->heldSampleHeaderId);
        $this->markAccepted($held);

        return $held->fresh();
    }

    private function markAccepted(SampleHeader $job): void
    {
        $job->forceFill(['status' => JobPurchaseOrderCoverageService::WORKFLOW_STATUS_AWAITING_PO])->save();
    }

    private function routeAcceptedBatch(SampleHeader $job): void
    {
        $form = (new AnalysisAcceptanceForm)->forceFill([
            'sample_header_id' => (string) $job->id,
            'mode_of_work' => 'Normal',
            'created_by' => (string) $this->user->id,
        ]);

        $route = new ReflectionMethod(AcceptanceFormService::class, 'routeAcceptedBatch');
        $route->invoke(app(AcceptanceFormService::class), $form);
    }

    /**
     * @return list<string>
     */
    private function sampleCodesOn(SampleHeader $job): array
    {
        return SampleDetails::query()
            ->where('sample_header_id', $job->id)
            ->orderBy('sample_code')
            ->pluck('sample_code')
            ->all();
    }

    /**
     * @param  list<string>|null  $analysisTypeIds
     */
    private function jobFor(CRMCustomer $customer, int $samples, ?array $analysisTypeIds = null): SampleHeader
    {
        $analysisTypeIds ??= [$this->analysisTypeId];
        $batchCode = 'T'.random_int(100000000, 999999999);

        $job = SampleHeader::query()->create([
            'batch_code' => $batchCode,
            'crm_customer_id' => (string) $customer->id,
            'crm_unit_name' => 'Main site',
            'status' => 'Samples Request Review',
            'is_routine' => false,
            'routine_frequency' => 0,
            'sample_type_id' => $this->sampleTypeId,
            'isactive' => 1,
        ]);

        for ($index = 1; $index <= $samples; $index++) {
            $detail = SampleDetails::query()->create([
                'sample_header_id' => (string) $job->id,
                'sample_code' => sprintf('%s-%03d', $batchCode, $index),
                'sample_type_id' => $this->sampleTypeId,
                'analysis_type_id' => $analysisTypeIds[0],
            ]);

            foreach ($analysisTypeIds as $analysisTypeId) {
                SampleAnalysisTypeRelation::query()->forceCreate([
                    'batch_id' => (string) $job->id,
                    'sample_detail_id' => (string) $detail->id,
                    'analysis_type_id' => $analysisTypeId,
                ]);
            }
        }

        return $job->fresh();
    }

    private function customer(string $billingType = 'walk_in'): CRMCustomer
    {
        $accountStatus = null;
        if ($billingType !== 'walk_in') {
            $accountStatus = (string) SystemConfiguration::query()->create([
                'key' => 'account_status_'.$billingType.'_'.Str::random(4),
                'value' => ucfirst($billingType).' account',
                'meta' => ['billing_type' => $billingType],
                'status' => true,
            ])->id;
        }

        return CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer '.Str::random(6),
            'code' => strtoupper(Str::random(6)),
            'account_status' => $accountStatus,
        ]);
    }

    /**
     * @return array{0: CustomerPurchaseOrder, 1: CustomerPurchaseOrderLine}
     */
    private function blanketFor(CRMCustomer $customer, int $orderedQty): array
    {
        $po = CustomerPurchaseOrder::factory()->blanket()->create(['customer_id' => (string) $customer->id]);

        $line = $this->addLine($po, $this->analysisTypeId, $orderedQty);

        return [$po->fresh(), $line];
    }

    private function addLine(CustomerPurchaseOrder $po, string $analysisTypeId, int $orderedQty): CustomerPurchaseOrderLine
    {
        return $this->allocation->addLine($po, [
            'description' => 'Potable water analysis',
            'ordered_qty' => $orderedQty,
            'unit_price_gross' => 157.50,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$analysisTypeId],
            'is_package' => false,
        ]);
    }

    private function enquiryFor(CRMCustomer $customer, int $samples, ?CustomerPurchaseOrder $po = null): SampleSubmissionRequest
    {
        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) $customer->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            'request_number' => $this->requestNumber++,
            'number_of_samples' => $samples,
            'customer_purchase_order_id' => $po !== null ? (string) $po->id : null,
            'sample_lines' => [
                ['sort_order' => 1, 'sample_type_id' => $this->sampleTypeId, 'analysis_type_id' => $this->analysisTypeId, 'analysis_element_id' => null, 'number_of_samples' => $samples],
            ],
        ])->fresh(['customer']);
    }

    /**
     * Sample details and analysis relations reference real catalogue rows through foreign keys.
     */
    private function createCatalogue(): void
    {
        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();
        $this->sampleTypeId = (string) Str::uuid();
        $this->analysisTypeId = (string) Str::uuid();
        $this->secondAnalysisTypeId = (string) Str::uuid();

        DB::table('companies')->insert(['id' => $companyId, 'name' => 'Test Company', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('labs')->insert([
            'id' => $labId,
            'company_id' => $companyId,
            'code' => 'LAB-'.Str::upper(Str::random(3)),
            'name' => 'Test Lab',
            'phone1' => '000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sample_types')->insert([
            'id' => $this->sampleTypeId,
            'company_id' => $companyId,
            'name' => 'Potable water',
            'code' => 'PW-'.Str::upper(Str::random(4)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([$this->analysisTypeId => 'Metals', $this->secondAnalysisTypeId => 'Microbiology'] as $id => $name) {
            DB::table('analysis_types')->insert([
                'id' => $id,
                'company_id' => $companyId,
                'lab_id' => $labId,
                'sample_type_id' => $this->sampleTypeId,
                'name' => $name,
                'code' => strtoupper(substr($name, 0, 3)).'-'.Str::upper(Str::random(4)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function assertValidationError(string $key, callable $callback): void
    {
        try {
            $callback();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($key, $exception->errors());

            return;
        }

        $this->fail("Expected a validation error on [{$key}].");
    }
}
