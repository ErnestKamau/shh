<?php

namespace Tests\Unit\Services;

use App\LabSection;
use App\Models\Monitoring\MonitoringCalibrationSnapshot;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringLogEntry;
use App\Models\Monitoring\MonitoringTemplate;
use App\Services\Monitoring\MonitoringLogValueResolver;
use App\Services\Monitoring\MonitoringSectionChartService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class MonitoringSectionChartServiceTest extends TestCase
{
    public function test_reference_mode_is_range_when_section_expects_range(): void
    {
        $section = new LabSection([
            'expected_value_type' => 'range',
            'expected_min' => 20.0,
            'expected_max' => 30.0,
            'optimum_level' => '25',
        ]);

        $service = new MonitoringSectionChartService(new MonitoringLogValueResolver);

        $reflection = new \ReflectionMethod($service, 'referenceMode');
        $reflection->setAccessible(true);

        $this->assertSame('range', $reflection->invoke($service, $section));
    }

    public function test_build_series_from_logs_extracts_final_values_and_range_limits(): void
    {
        $section = new LabSection([
            'id' => 'section-1',
            'expected_value_type' => 'range',
            'expected_min' => 20.0,
            'expected_max' => 30.0,
            'optimum_level' => '25',
            'reading_frequency_schedule' => [
                ['frequency' => 1, 'label' => 'AM'],
                ['frequency' => 2, 'label' => 'PM'],
            ],
        ]);

        $template = new MonitoringTemplate(['id' => 'template-1']);
        $template->setRelation('fields', collect());
        $template->setRelation('formulaRules', collect());

        $log = new MonitoringLog([
            'log_date' => Carbon::parse('2026-05-27'),
            'frequency_slot' => 1,
            'executed_at' => Carbon::parse('2026-05-27 08:00:00'),
            'lab_section_id' => 'section-1',
        ]);
        $log->setRelation('entries', collect([
            new MonitoringLogEntry([
                'field_key' => 'initial',
                'raw_value' => '17.9',
            ]),
            new MonitoringLogEntry([
                'field_key' => 'final',
                'raw_value' => '19.9',
                'computed_value' => '19.9',
            ]),
        ]));
        $log->setRelation('calibrationSnapshots', collect([
            new MonitoringCalibrationSnapshot(['uncertainty_of_measure' => 0.12]),
        ]));

        $service = new MonitoringSectionChartService(new MonitoringLogValueResolver);

        $reference = [
            'min' => 20.0,
            'max' => 30.0,
            'optimum' => 25.0,
            'constant' => null,
        ];

        $series = $service->buildSeriesFromLogs(
            collect([$log]),
            $template,
            $section,
            [1 => 'AM', 2 => 'PM'],
            $reference,
            'range',
            true,
        );

        $this->assertSame(['May 27 AM'], $series['labels']);
        $this->assertSame([19.9], $series['actual']);
        $this->assertSame([20.0], $series['min']);
        $this->assertSame([30.0], $series['max']);
        $this->assertEqualsWithDelta(20.02, $series['actual_um_high'][0], 0.001);
        $this->assertEqualsWithDelta(19.78, $series['actual_um_low'][0], 0.001);
        $this->assertEqualsWithDelta(25.12, $series['optimum_um_high'][0], 0.001);
        $this->assertEqualsWithDelta(24.88, $series['optimum_um_low'][0], 0.001);
        $this->assertEqualsWithDelta(19.88, $series['min_um_low'][0], 0.001);
        $this->assertEqualsWithDelta(30.12, $series['max_um_high'][0], 0.001);
    }

    public function test_build_series_skips_logs_without_numeric_final(): void
    {
        $section = new LabSection([
            'id' => 'section-1',
            'expected_value_type' => 'range',
            'expected_min' => 20.0,
            'expected_max' => 30.0,
        ]);

        $template = new MonitoringTemplate(['id' => 'template-1']);
        $template->setRelation('fields', collect());
        $template->setRelation('formulaRules', collect());

        $log = new MonitoringLog([
            'log_date' => Carbon::parse('2026-05-27'),
            'lab_section_id' => 'section-1',
        ]);
        $log->setRelation('entries', collect([
            new MonitoringLogEntry(['field_key' => 'remark', 'raw_value' => 'PASS']),
        ]));
        $log->setRelation('calibrationSnapshots', collect());

        $service = new MonitoringSectionChartService(new MonitoringLogValueResolver);

        $series = $service->buildSeriesFromLogs(
            collect([$log]),
            $template,
            $section,
            [],
            ['min' => 20.0, 'max' => 30.0, 'optimum' => 25.0, 'constant' => null],
            'range',
            false,
        );

        $this->assertSame([], $series['labels']);
        $this->assertSame([], $series['actual']);
    }
}
