<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\CapturedResult;
use App\Services\Sampleworkflow\BatchVerificationReadinessService;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BatchVerificationReadinessServiceTest extends TestCase
{
    private BatchVerificationReadinessService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BatchVerificationReadinessService();
    }

    #[Test]
    public function blocks_when_no_captured_results_exist(): void
    {
        $reason = $this->service->blockingReasonForResults([]);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('No results have been set up', $reason);
    }

    #[Test]
    public function blocks_when_all_capturable_results_are_empty(): void
    {
        $rows = [
            $this->row(null),
            $this->row(''),
            $this->row('No Attachment'),
        ];

        $reason = $this->service->blockingReasonForResults($rows);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('No results have been entered', $reason);
    }

    #[Test]
    public function blocks_when_some_results_are_still_missing(): void
    {
        $rows = [
            $this->row(null),
            $this->row('7.2'),
            $this->row('No Attachment'),
        ];

        $reason = $this->service->blockingReasonForResults($rows);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('still missing', $reason);
        $this->assertStringContainsString('Partial / interim report', $reason);
    }

    #[Test]
    public function allows_when_all_capturable_results_are_entered(): void
    {
        $rows = [
            $this->row('Absent'),
            $this->row('7.2'),
            $this->row('Detected'),
        ];

        $this->assertNull($this->service->blockingReasonForResults($rows));
    }

    #[Test]
    public function allows_when_only_no_result_capture_rows_exist(): void
    {
        $rows = [
            $this->row(null, hasNoResultCapture: true),
            $this->row('', hasNoResultCapture: true),
        ];

        $this->assertNull($this->service->blockingReasonForResults($rows));
    }

    #[Test]
    public function ignores_no_result_capture_rows_when_checking_entered_values(): void
    {
        $rows = [
            $this->row('Positive', hasNoResultCapture: true),
            $this->row(null, hasNoResultCapture: false),
        ];

        $reason = $this->service->blockingReasonForResults($rows);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('No results have been entered', $reason);
    }

    #[Test]
    public function partial_interim_allows_when_at_least_one_result_is_entered(): void
    {
        $rows = [
            $this->row(null),
            $this->row('7.2'),
            $this->row('No Attachment'),
        ];

        $this->assertNull($this->service->blockingReasonForResults(
            $rows,
            BatchVerificationReadinessService::REPORT_LEVEL_PARTIAL_INTERIM
        ));
    }

    #[Test]
    public function partial_interim_blocks_when_no_results_are_entered(): void
    {
        $rows = [
            $this->row(null),
            $this->row(''),
            $this->row('No Attachment'),
        ];

        $reason = $this->service->blockingReasonForResults(
            $rows,
            BatchVerificationReadinessService::REPORT_LEVEL_PARTIAL_INTERIM
        );

        $this->assertNotNull($reason);
        $this->assertStringContainsString('at least one sample result', $reason);
    }

    #[Test]
    public function preliminary_and_draft_still_require_all_results(): void
    {
        $rows = [
            $this->row(null),
            $this->row('7.2'),
        ];

        $this->assertNotNull($this->service->blockingReasonForResults(
            $rows,
            BatchVerificationReadinessService::REPORT_LEVEL_PRELIMINARY
        ));
        $this->assertNotNull($this->service->blockingReasonForResults(
            $rows,
            BatchVerificationReadinessService::REPORT_LEVEL_DRAFT
        ));
        $this->assertNotNull($this->service->blockingReasonForResults(
            $rows,
            BatchVerificationReadinessService::REPORT_LEVEL_FINAL
        ));
    }

    private function row(?string $result, bool $hasNoResultCapture = false): CapturedResult
    {
        $captured = new CapturedResult();
        $captured->forceFill([
            'id' => (string) Str::uuid(),
            'result' => $result,
            'has_no_result_capture' => $hasNoResultCapture,
        ]);

        return $captured;
    }
}
