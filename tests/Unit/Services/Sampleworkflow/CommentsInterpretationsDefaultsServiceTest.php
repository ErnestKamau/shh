<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\CommentsInterpretationsDefaultsService;
use App\Services\Sampleworkflow\StandardPassFailCommentService;
use PHPUnit\Framework\TestCase;

class CommentsInterpretationsDefaultsServiceTest extends TestCase
{
    private CommentsInterpretationsDefaultsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CommentsInterpretationsDefaultsService(
            new StandardPassFailCommentService
        );
    }

    public function test_single_pass_uses_default_sentence(): void
    {
        $remark = $this->service->formatConformityRemark([
            ['name' => 'GSO 1025:2014', 'passed' => true],
        ]);

        $this->assertSame(
            'The above test results conform to GSO 1025:2014',
            $remark
        );
    }

    public function test_single_fail_uses_default_sentence(): void
    {
        $remark = $this->service->formatConformityRemark([
            ['name' => 'GSO 1025:2014', 'passed' => false],
        ]);

        $this->assertSame(
            'The above test results do not conform to GSO 1025:2014',
            $remark
        );
    }

    public function test_single_outcome_prefers_custom_comment(): void
    {
        $remark = $this->service->formatConformityRemark([
            [
                'name' => 'GSO 149:2021',
                'passed' => true,
                'comment' => 'The above test results conform to GSO 149:2021',
            ],
        ]);

        $this->assertSame('The above test results conform to GSO 149:2021', $remark);
    }

    public function test_mixed_outcomes_put_positive_statements_first(): void
    {
        $remark = $this->service->formatConformityRemark([
            ['name' => 'GSO 1017:2022', 'passed' => false],
            ['name' => 'GSO 1016:2022', 'passed' => true],
        ]);

        $this->assertSame(
            'The above test results conform to GSO 1016:2022 and do not conform to GSO 1017:2022',
            $remark
        );
    }

    public function test_all_failing_specs_are_joined_with_and(): void
    {
        $remark = $this->service->formatConformityRemark([
            ['name' => 'GSO 1017', 'passed' => false],
            ['name' => 'GSO 898', 'passed' => false],
        ]);

        $this->assertSame(
            'The above test results do not conform to GSO 1017 and do not conform to GSO 898',
            $remark
        );
    }

    public function test_sample_two_style_mixed_statement(): void
    {
        $remark = $this->service->formatConformityRemark([
            ['name' => 'GSO 56699', 'passed' => true],
            ['name' => 'GSO 8988', 'passed' => false],
        ]);

        $this->assertSame(
            'The above test results conform to GSO 56699 and do not conform to GSO 8988',
            $remark
        );
    }

    public function test_multi_spec_extracts_labels_from_custom_comments(): void
    {
        $remark = $this->service->formatConformityRemark([
            [
                'name' => 'Std Fail',
                'passed' => false,
                'comment' => 'The above test results do not conform to "GSO 1017:2022"',
            ],
            [
                'name' => 'Std Pass',
                'passed' => true,
                'comment' => 'The above test results conform to "GSO 56699"',
            ],
        ]);

        $this->assertSame(
            'The above test results conform to GSO 56699 and do not conform to GSO 1017:2022',
            $remark
        );
    }
}
