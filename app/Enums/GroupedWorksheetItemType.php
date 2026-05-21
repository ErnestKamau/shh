<?php

namespace App\Enums;

enum GroupedWorksheetItemType: string
{
    case Formula = 'formula';
    case Procedure = 'procedure';
    case StageHeader = 'stage_header';
    case HybridWorksheet = 'hybrid_worksheet';

    public function label(): string
    {
        return match ($this) {
            self::Formula => 'Formula worksheet',
            self::Procedure => 'Procedure worksheet',
            self::StageHeader => 'Method sequence (stage header)',
            self::HybridWorksheet => 'Hybrid worksheet',
        };
    }

    public function referenceTable(): string
    {
        return match ($this) {
            self::Formula => 'formulas',
            self::Procedure => 'procedure_worksheets',
            self::StageHeader => 'stage_headers',
            self::HybridWorksheet => 'hybrid_worksheets',
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
