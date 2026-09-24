<?php

namespace Tests\Unit\Services;

use App\CapturedResult;
use App\SampleDetails;
use App\Services\StandardLimitDisplayService;
use PHPUnit\Framework\TestCase;

class StandardLimitDisplayServiceTest extends TestCase
{
    public function test_falls_back_to_secondary_when_main_has_no_limit(): void
    {
        $service = $this->makeService([
            'main-std' => null,
            'sec-std' => 'Not Detected',
        ], [
            'main-std' => 'GSO 149:2021',
            'sec-std' => 'GSO Halal Spec',
        ]);

        $captured = $this->makeCapturedResult([
            'analyte_id' => 'analyte-1',
            'main_standard_id' => 'main-std',
            'secondary_standard_id' => 'sec-std',
            'main_value' => null,
            'secondary_value' => null,
        ]);

        $this->assertSame('Not Detected', $service->forCapturedResult($captured));
        $this->assertSame('GSO Halal Spec', $service->standardNameForCapturedResult($captured));
    }

    public function test_ignores_placeholder_main_value_and_uses_secondary_limit(): void
    {
        $service = $this->makeService([
            'main-std' => null,
            'sec-std' => 'Absent',
        ], [
            'main-std' => 'GSO 149:2021',
            'sec-std' => 'UAE.S 2055-1',
        ]);

        $captured = $this->makeCapturedResult([
            'analyte_id' => 'analyte-1',
            'main_standard_id' => 'main-std',
            'secondary_standard_id' => 'sec-std',
            'main_value' => '-',
            'secondary_value' => null,
        ]);

        $resolved = $service->resolveCapturedResultSpecification($captured);

        $this->assertSame('sec-std', $resolved['standard_id']);
        $this->assertSame('Absent', $resolved['limit']);
        $this->assertSame('UAE.S 2055-1', $resolved['name']);
    }

    public function test_prefers_main_limit_when_present(): void
    {
        $service = $this->makeService([
            'main-std' => 'max 60',
            'sec-std' => 'Not Detected',
        ], [
            'main-std' => 'GSO 149:2021',
            'sec-std' => 'GSO Halal Spec',
        ]);

        $captured = $this->makeCapturedResult([
            'analyte_id' => 'analyte-1',
            'main_standard_id' => 'main-std',
            'secondary_standard_id' => 'sec-std',
            'main_value' => null,
            'secondary_value' => null,
        ]);

        $this->assertSame('max 60', $service->forCapturedResult($captured));
        $this->assertSame('GSO 149:2021', $service->standardNameForCapturedResult($captured));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeCapturedResult(array $attributes): CapturedResult
    {
        $captured = new CapturedResult;
        $captured->setRawAttributes($attributes, true);
        $captured->setRelation('sample', new SampleDetails);

        return $captured;
    }

    /**
     * @param  array<string, ?string>  $limitsByStandardId
     * @param  array<string, string>  $namesByStandardId
     */
    private function makeService(array $limitsByStandardId, array $namesByStandardId): StandardLimitDisplayService
    {
        return new class($limitsByStandardId, $namesByStandardId) extends StandardLimitDisplayService
        {
            /**
             * @param  array<string, ?string>  $limitsByStandardId
             * @param  array<string, string>  $namesByStandardId
             */
            public function __construct(
                private array $limitsByStandardId,
                private array $namesByStandardId,
            ) {}

            public function format(
                string|int|null $standardId,
                string|int|null $analyteId,
                mixed $capturedValue = null,
                string|int|null $capturedStandardForeignKey = null,
            ): ?string {
                if ($standardId === null || $standardId === '') {
                    return null;
                }

                return $this->limitsByStandardId[(string) $standardId] ?? null;
            }

            protected function resolveStandardDisplayName(string|int $standardId): ?string
            {
                return $this->namesByStandardId[(string) $standardId] ?? null;
            }
        };
    }
}
