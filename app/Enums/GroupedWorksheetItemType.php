<?php

namespace App\Enums;

enum GroupedWorksheetItemType: string
{
    case Formula = 'formula';
    case Procedure = 'procedure';
    case StageHeader = 'stage_header';
    case HybridWorksheet = 'hybrid_worksheet';
    case LogEntryWorksheet = 'log_entry_worksheet';
    case ResultsCapture = 'results_capture';

    public function label(): string
    {
        return match ($this) {
            self::Formula => 'Formula worksheet',
            self::Procedure => 'Procedure worksheet',
            self::StageHeader => 'Method sequence (stage header)',
            self::HybridWorksheet => 'Hybrid worksheet',
            self::LogEntryWorksheet => 'Log entry worksheet',
            self::ResultsCapture => 'Results capture',
        };
    }

    public function referenceTable(): string
    {
        return match ($this) {
            self::Formula => 'formulas',
            self::Procedure => 'procedure_worksheets',
            self::StageHeader => 'stage_headers',
            self::HybridWorksheet => 'hybrid_worksheets',
            self::LogEntryWorksheet => 'log_entry_worksheets',
            self::ResultsCapture => '',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            if ($case === self::ResultsCapture) {
                continue;
            }
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
