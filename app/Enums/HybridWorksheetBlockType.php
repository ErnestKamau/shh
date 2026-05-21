<?php

namespace App\Enums;

enum HybridWorksheetBlockType: string
{
    case FormulaReference = 'formula_reference';
    case ProcedureReference = 'procedure_reference';
    case StageHeaderReference = 'stage_header_reference';
    case FormulaInline = 'formula_inline';
    case ProcedureInline = 'procedure_inline';
    case SequenceInline = 'sequence_inline';

    public function label(): string
    {
        return match ($this) {
            self::FormulaReference => 'Formula (reference)',
            self::ProcedureReference => 'Procedure (reference)',
            self::StageHeaderReference => 'Method sequence (reference)',
            self::FormulaInline => 'Formula steps (inline)',
            self::ProcedureInline => 'Procedure steps (inline)',
            self::SequenceInline => 'Sequence stages (inline)',
        };
    }

    public function isReference(): bool
    {
        return in_array($this, [
            self::FormulaReference,
            self::ProcedureReference,
            self::StageHeaderReference,
        ], true);
    }

    public function isInline(): bool
    {
        return ! $this->isReference();
    }

    public function referenceTable(): ?string
    {
        return match ($this) {
            self::FormulaReference => 'formulas',
            self::ProcedureReference => 'procedure_worksheets',
            self::StageHeaderReference => 'stage_headers',
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function referenceOptions(): array
    {
        return [
            self::FormulaReference->value => self::FormulaReference->label(),
            self::ProcedureReference->value => self::ProcedureReference->label(),
            self::StageHeaderReference->value => self::StageHeaderReference->label(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function inlineOptions(): array
    {
        return [
            self::FormulaInline->value => self::FormulaInline->label(),
            self::ProcedureInline->value => self::ProcedureInline->label(),
            self::SequenceInline->value => self::SequenceInline->label(),
        ];
    }
}
