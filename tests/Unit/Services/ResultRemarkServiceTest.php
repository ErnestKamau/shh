<?php

namespace Tests\Unit\Services;

use App\Services\ResultRemarkService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResultRemarkServiceTest extends TestCase
{
    private ResultRemarkService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ResultRemarkService;
    }

    #[DataProvider('notDetectableStandardProvider')]
    public function test_not_detectable_standards_calculate_pass_fail(string $standard, string $result, string $expected): void
    {
        $remark = $this->service->calculateRemark(
            null,
            $result,
            null,
            $standard,
        );

        $this->assertSame($expected, $remark);
    }

    #[DataProvider('reportingSymbolStandardProvider')]
    public function test_reporting_symbol_is_applied_to_manual_max_min_limits(
        string $standard,
        string $result,
        ?string $symbol,
        string $expected,
    ): void {
        $remark = $this->service->calculateRemark(
            null,
            $result,
            null,
            $standard,
            $symbol,
        );

        $this->assertSame($expected, $remark);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function notDetectableStandardProvider(): array
    {
        return [
            'not detectable + ND => PASS' => ['Not Detectable', 'ND', 'PASS'],
            'not detectable + not detected => PASS' => ['Not Detectable', 'Not Detected', 'PASS'],
            'not detectable + 0 => PASS' => ['Not Detectable', '0', 'PASS'],
            'not detectable + positive number => FAIL' => ['Not Detectable', '435', 'FAIL'],
            'nd code + ND => PASS' => ['ND', 'ND', 'PASS'],
            'nd code + positive number => FAIL' => ['ND', '12', 'FAIL'],
            'nil + absent => PASS' => ['NIL', 'Absent', 'PASS'],
            'max still works' => ['max 34', '34', 'PASS'],
            'max fail still works' => ['max 34', '36', 'FAIL'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: ?string, 3: string}>
     */
    public static function reportingSymbolStandardProvider(): array
    {
        return [
            'lt + under max => PASS' => ['max 50', '4', '<', 'PASS'],
            'lt + equal max => PASS' => ['max 50', '50', '<', 'PASS'],
            'no symbol under max => PASS' => ['max 50', '4', null, 'PASS'],
            'gt under max still PASS by legacy rule' => ['max 50', '4', '>', 'PASS'],
            'gt at max => FAIL' => ['max 50', '50', '>', 'FAIL'],
            'typed limit via evaluateTypedLimit lt' => ['max 50', '4', '<', 'PASS'],
        ];
    }
}
