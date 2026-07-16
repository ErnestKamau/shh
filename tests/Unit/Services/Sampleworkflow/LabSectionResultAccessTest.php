<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\CapturedResult;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use App\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LabSectionResultAccessTest extends TestCase
{
    private LabSectionResultAccess $access;

    protected function setUp(): void
    {
        parent::setUp();
        $this->access = new LabSectionResultAccess();
    }

    #[Test]
    public function unassigned_user_can_view_all_but_cannot_edit(): void
    {
        $user = $this->userWithSections([]);
        $row = $this->capturedResultWithSection((string) Str::uuid());

        $this->assertFalse($this->access->hasLabSectionAssignment($user));
        $this->assertTrue($this->access->canViewCapturedResult($user, $row));
        $this->assertFalse($this->access->canEditCapturedResult($user, $row));
    }

    #[Test]
    public function assigned_user_can_view_all_sections_but_edit_matching_section_only(): void
    {
        $micro = (string) Str::uuid();
        $chem = (string) Str::uuid();
        $user = $this->userWithSections([$micro]);

        $microRow = $this->capturedResultWithSection($micro);
        $chemRow = $this->capturedResultWithSection($chem);
        $orphanRow = $this->capturedResultWithSection(null);

        $this->assertTrue($this->access->hasLabSectionAssignment($user));
        $this->assertTrue($this->access->canViewCapturedResult($user, $microRow));
        $this->assertTrue($this->access->canEditCapturedResult($user, $microRow));
        $this->assertTrue($this->access->canViewCapturedResult($user, $chemRow));
        $this->assertFalse($this->access->canEditCapturedResult($user, $chemRow));
        $this->assertTrue($this->access->canViewCapturedResult($user, $orphanRow));
        $this->assertFalse($this->access->canEditCapturedResult($user, $orphanRow));
    }

    #[Test]
    public function scope_visible_does_not_filter_by_lab_section(): void
    {
        $micro = (string) Str::uuid();
        $assigned = $this->userWithSections([$micro]);
        $unassigned = $this->userWithSections([]);

        $assignedQuery = $this->access->scopeVisibleCapturedResults(
            CapturedResult::query(),
            $assigned
        );
        $this->assertStringNotContainsString('lab_section_id', strtolower($assignedQuery->toSql()));

        $openQuery = $this->access->scopeVisibleCapturedResults(
            CapturedResult::query(),
            $unassigned
        );
        $this->assertStringNotContainsString('lab_section_id', strtolower($openQuery->toSql()));
    }

    #[Test]
    public function filter_analytes_holder_keeps_all_sections_for_authenticated_users(): void
    {
        $micro = (string) Str::uuid();
        $chem = (string) Str::uuid();
        $user = $this->userWithSections([$micro]);

        $holder = [
            'S-1' => [
                $micro => ['section' => 'Micro', 'cr' => ['m']],
                $chem => ['section' => 'Chem', 'cr' => ['c']],
            ],
            'S-2' => [
                $chem => ['section' => 'Chem', 'cr' => ['c2']],
            ],
        ];

        $this->assertSame($holder, $this->access->filterAnalytesHolderForUser($holder, $user));
        $this->assertSame(
            $holder,
            $this->access->filterAnalytesHolderForUser($holder, $this->userWithSections([]))
        );
        $this->assertSame([], $this->access->filterAnalytesHolderForUser($holder, null));
    }

    #[Test]
    public function deny_edit_message_distinguishes_unassigned_and_assigned_users(): void
    {
        $unassigned = $this->userWithSections([]);
        $assigned = $this->userWithSections([(string) Str::uuid()]);

        $this->assertSame(
            'Assign a lab section in your profile before capturing results.',
            $this->access->denyEditMessage($unassigned)
        );
        $this->assertSame(
            'You can only capture results for your assigned lab section(s).',
            $this->access->denyEditMessage($assigned)
        );
    }

    #[Test]
    public function can_edit_all_requires_every_row_to_match_user_section(): void
    {
        $micro = (string) Str::uuid();
        $chem = (string) Str::uuid();
        $user = $this->userWithSections([$micro]);

        $this->assertFalse($this->access->canEditAllCapturedResults($user, []));
        $this->assertFalse($this->access->canEditAllCapturedResults($user, [null]));
        $this->assertTrue(
            $this->access->canEditAllCapturedResults($user, [
                $this->capturedResultWithSection($micro),
                $this->capturedResultWithSection($micro),
            ])
        );
        $this->assertFalse(
            $this->access->canEditAllCapturedResults($user, [
                $this->capturedResultWithSection($micro),
                $this->capturedResultWithSection($chem),
            ])
        );
    }

    /**
     * @param  list<string>  $sectionIds
     */
    private function userWithSections(array $sectionIds): User
    {
        return new class($sectionIds) extends User
        {
            /** @param list<string> $sectionIds */
            public function __construct(private array $sectionIds)
            {
                parent::__construct();
            }

            protected function getLabSectionIdsAttribute()
            {
                return $this->sectionIds;
            }
        };
    }

    private function capturedResultWithSection(?string $sectionId): CapturedResult
    {
        $row = new CapturedResult();
        $row->lab_section_id = $sectionId;

        return $row;
    }
}
