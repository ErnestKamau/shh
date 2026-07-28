<?php

namespace Tests\Unit\Services\ShelfLife;

use App\Models\ShelfLife\ShelfLifeStudy;
use App\Models\ShelfLife\ShelfLifeStudyParameterSpec;
use App\Services\ShelfLife\ShelfLifeStudyBootstrapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShelfLifeStudyBootstrapServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_monthly_intervals_include_baseline_and_twelve_month_span(): void
    {
        $intervals = app(ShelfLifeStudyBootstrapService::class)->defaultMonthlyIntervals();

        $this->assertCount(6, $intervals);
        $this->assertTrue($intervals[0]['is_baseline']);
        $this->assertSame(0, $intervals[0]['offset_value']);
        $this->assertSame(12, $intervals[5]['offset_value']);
    }

    public function test_generate_pull_points_schedules_from_start_date(): void
    {
        $study = ShelfLifeStudy::query()->create([
            'code' => 'SLS-0001',
            'title' => 'Test study',
            'study_type' => ShelfLifeStudy::TYPE_REAL_TIME,
            'status' => ShelfLifeStudy::STATUS_DRAFT,
            'start_date' => '2026-01-15',
        ]);

        $points = app(ShelfLifeStudyBootstrapService::class)->generatePullPoints(
            $study,
            [
                ['offset_value' => 0, 'offset_unit' => 'months', 'is_baseline' => true, 'label' => 'T0'],
                ['offset_value' => 3, 'offset_unit' => 'months', 'label' => 'Month 3'],
            ],
            Carbon::parse('2026-01-15')
        );

        $this->assertCount(2, $points);
        $this->assertSame('2026-01-15', $points[0]->scheduled_date->format('Y-m-d'));
        $this->assertSame('2026-04-15', $points[1]->scheduled_date->format('Y-m-d'));
        $this->assertTrue($points[0]->is_baseline);
        $this->assertFalse($points[1]->is_baseline);
    }

    public function test_parameter_spec_evaluate_range_and_max(): void
    {
        $range = new ShelfLifeStudyParameterSpec([
            'spec_type' => ShelfLifeStudyParameterSpec::SPEC_RANGE,
            'spec_low' => 1,
            'spec_high' => 5,
        ]);

        $this->assertSame('PASS', $range->evaluate(3.0));
        $this->assertSame('FAIL', $range->evaluate(6.0));

        $max = new ShelfLifeStudyParameterSpec([
            'spec_type' => ShelfLifeStudyParameterSpec::SPEC_MAX,
            'spec_high' => 10,
        ]);

        $this->assertSame('PASS', $max->evaluate(10.0));
        $this->assertSame('FAIL', $max->evaluate(10.1));
    }
}
