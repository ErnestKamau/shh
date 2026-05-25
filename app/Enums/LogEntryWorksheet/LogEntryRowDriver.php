<?php

namespace App\Enums\LogEntryWorksheet;

enum LogEntryRowDriver: string
{
    case SampleHeader = 'sample_header';
    case SampleDetail = 'sample_detail';
    case CapturedResult = 'captured_result';
    case Method = 'method';

    public function label(): string
    {
        return match ($this) {
            self::SampleHeader => 'Sample header (one row per batch)',
            self::SampleDetail => 'Sample details (one row per sample)',
            self::CapturedResult => 'Captured results (one row per test)',
            self::Method => 'Methods (one row per distinct method)',
        };
    }

    public function driverTypeClass(): string
    {
        return match ($this) {
            self::SampleHeader => \App\SampleHeader::class,
            self::SampleDetail => \App\SampleDetails::class,
            self::CapturedResult => \App\CapturedResult::class,
            self::Method => \App\AnalysisMethod::class,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
