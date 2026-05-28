<?php

namespace App\Enums\Procedures;

enum ProcedureStepTableColumnType: string
{
    case Input = 'input';
    case Derived = 'derived';
    case Dataset = 'dataset';

    public function label(): string
    {
        return match ($this) {
            self::Input => 'User input',
            self::Derived => 'Derived (expression)',
            self::Dataset => 'Dataset',
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
