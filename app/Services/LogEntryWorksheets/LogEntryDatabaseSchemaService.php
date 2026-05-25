<?php

namespace App\Services\LogEntryWorksheets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\TemplateEngine\Services\DatabaseMetadataService;

class LogEntryDatabaseSchemaService
{
    public function __construct(
        protected DatabaseMetadataService $metadata,
    ) {}

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function tableOptions(): array
    {
        return collect($this->metadata->getTables())
            ->map(fn (string $name) => [
                'value' => $name,
                'label' => Str::title(str_replace('_', ' ', $name)),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string, type: ?string}>
     */
    public function columnOptions(string $tableName): array
    {
        if (! Schema::hasTable($tableName)) {
            return [];
        }

        return collect($this->metadata->getColumns($tableName))
            ->map(function (string $column) use ($tableName) {
                $type = null;
                try {
                    $type = Schema::getColumnType($tableName, $column);
                } catch (\Throwable) {
                    // ignore
                }

                return [
                    'value' => $column,
                    'label' => Str::title(str_replace('_', ' ', $column)),
                    'type' => $type,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{column: string, referenced_table: string, referenced_column: string, label: string}>
     */
    public function foreignKeyOptions(string $tableName): array
    {
        return collect($this->metadata->getForeignKeys($tableName))
            ->map(fn (array $fk) => [
                'column' => $fk['column'],
                'referenced_table' => $fk['referenced_table'],
                'referenced_column' => $fk['referenced_column'],
                'label' => $fk['column'].' → '.$fk['referenced_table'],
            ])
            ->values()
            ->all();
    }

    public function isDateColumn(string $tableName, string $column): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        try {
            $type = Schema::getColumnType($tableName, $column);

            return in_array($type, ['date', 'datetime', 'datetime_immutable', 'timestamp'], true);
        } catch (\Throwable) {
            return Str::contains(strtolower($column), ['date', 'time']);
        }
    }

    public function isUsersTable(string $tableName): bool
    {
        return in_array($tableName, ['users', 'user'], true);
    }
}
