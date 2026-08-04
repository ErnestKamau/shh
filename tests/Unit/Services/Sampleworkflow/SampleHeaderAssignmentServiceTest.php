<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\CapturedResult;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use App\Services\Sampleworkflow\SampleHeaderAssignmentService;
use App\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SampleHeaderAssignmentServiceTest extends TestCase
{
    private SampleHeaderAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SampleHeaderAssignmentService(new LabSectionResultAccess());
    }

    #[Test]
    public function scoped_results_incomplete_when_analyst_has_no_assigned_tests(): void
    {
        $analyst = $this->userWithId((string) Str::uuid());
        $other = (string) Str::uuid();

        $rows = [
            $this->capturedResult(assignedUserId: $other, result: '1.2'),
            $this->capturedResult(assignedUserId: $other, result: '3.4'),
        ];

        $this->assertFalse($this->service->analystScopedResultsAreComplete($analyst, $rows));
    }

    #[Test]
    public function scoped_results_incomplete_when_any_assigned_test_missing_result(): void
    {
        $analystId = (string) Str::uuid();
        $analyst = $this->userWithId($analystId);

        $rows = [
            $this->capturedResult(assignedUserId: $analystId, result: '1.2'),
            $this->capturedResult(assignedUserId: $analystId, result: ''),
        ];

        $this->assertFalse($this->service->analystScopedResultsAreComplete($analyst, $rows));
    }

    #[Test]
    public function scoped_results_complete_when_all_assigned_tests_have_results(): void
    {
        $analystId = (string) Str::uuid();
        $otherId = (string) Str::uuid();
        $analyst = $this->userWithId($analystId);

        $rows = [
            $this->capturedResult(assignedUserId: $analystId, result: '1.2'),
            $this->capturedResult(assignedUserId: $analystId, result: '0.5', assignedAnalystIds: [$analystId]),
            $this->capturedResult(assignedUserId: $otherId, result: ''),
        ];

        $this->assertTrue($this->service->analystScopedResultsAreComplete($analyst, $rows));
    }

    #[Test]
    public function scoped_results_ignore_has_no_result_capture_rows(): void
    {
        $analystId = (string) Str::uuid();
        $analyst = $this->userWithId($analystId);

        $rows = [
            $this->capturedResult(assignedUserId: $analystId, result: '1.2'),
            $this->capturedResult(assignedUserId: $analystId, result: '', hasNoResultCapture: true),
        ];

        $this->assertTrue($this->service->analystScopedResultsAreComplete($analyst, $rows));
    }

    #[Test]
    public function additional_assigned_analyst_ids_count_toward_scope(): void
    {
        $primaryId = (string) Str::uuid();
        $secondaryId = (string) Str::uuid();
        $secondary = $this->userWithId($secondaryId);

        $rows = [
            $this->capturedResult(
                assignedUserId: $primaryId,
                result: '2.0',
                assignedAnalystIds: [$primaryId, $secondaryId],
            ),
        ];

        $this->assertTrue($this->service->analystScopedResultsAreComplete($secondary, $rows));
    }

    private function userWithId(string $userId): User
    {
        return new class($userId) extends User
        {
            public function __construct(string $userId)
            {
                parent::__construct();
                $this->id = $userId;
                $this->exists = true;
            }

            protected function getLabSectionIdsAttribute()
            {
                return [];
            }
        };
    }

    /**
     * @param  list<string>|null  $assignedAnalystIds
     */
    private function capturedResult(
        ?string $assignedUserId,
        string $result,
        ?array $assignedAnalystIds = null,
        bool $hasNoResultCapture = false,
    ): CapturedResult {
        $row = new CapturedResult();
        $row->user_id = $assignedUserId;
        $row->result = $result;
        $row->assigned_analyst_ids = $assignedAnalystIds;
        $row->has_no_result_capture = $hasNoResultCapture;

        return $row;
    }
}
