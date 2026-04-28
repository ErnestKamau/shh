<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$convertDir = $root . '/database/migrations/convert';

if (!is_dir($convertDir)) {
    fwrite(STDERR, "Convert directory not found: {$convertDir}\n");
    exit(1);
}

$files = glob($convertDir . '/*_create_*_table.php') ?: [];
sort($files);

$updatedFiles = 0;

foreach ($files as $file) {
    $name = basename($file);
    if (!preg_match('/_create_(.+)_table\.php$/', $name, $matches)) {
        continue;
    }

    $table = $matches[1];
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;

    $content = preg_replace_callback(
        '/(\$table->[^\n;]*\(\'([^\']+)\'[^\n;]*?\))->index\(\'[^\']+\'\)/',
        static function (array $m) use ($table): string {
            $column = $m[2];
            $indexName = buildIndexName($table, [$column], 'idx');
            return $m[1] . "->index('{$indexName}')";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/(\$table->[^\n;]*\(\'([^\']+)\'[^\n;]*?\))->unique\(\'[^\']+\'\)/',
        static function (array $m) use ($table): string {
            $column = $m[2];
            $indexName = buildIndexName($table, [$column], 'uq');
            return $m[1] . "->unique('{$indexName}')";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/(\$table->[^\n;]*\(\'([^\']+)\'[^\n;]*?\))->fulltext\(\'[^\']+\'\)/i',
        static function (array $m) use ($table): string {
            $column = $m[2];
            $indexName = buildIndexName($table, [$column], 'ftx');
            return $m[1] . "->fulltext('{$indexName}')";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/\$table->index\(\'([^\']+)\'(?:,\s*\'[^\']+\')?\);/',
        static function (array $m) use ($table): string {
            $column = $m[1];
            $indexName = buildIndexName($table, [$column], 'idx');
            return "\$table->index('{$column}', '{$indexName}');";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/\$table->unique\(\'([^\']+)\'(?:,\s*\'[^\']+\')?\);/',
        static function (array $m) use ($table): string {
            $column = $m[1];
            $indexName = buildIndexName($table, [$column], 'uq');
            return "\$table->unique('{$column}', '{$indexName}');";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/\$table->index\(\[([^\]]+)\](?:,\s*\'[^\']+\')?\);/',
        static function (array $m) use ($table): string {
            preg_match_all('/\'([^\']+)\'/', $m[1], $columnMatches);
            $columns = $columnMatches[1] ?? [];
            if ($columns === []) {
                return $m[0];
            }
            $indexName = buildIndexName($table, $columns, 'idx');
            $columnSql = implode(', ', array_map(static fn(string $column): string => "'{$column}'", $columns));
            return "\$table->index([{$columnSql}], '{$indexName}');";
        },
        $content
    ) ?? $content;

    if ($content !== $original) {
        file_put_contents($file, $content);
        $updatedFiles++;
    }
}

fwrite(STDOUT, "Normalized index names in {$updatedFiles} files\n");

/**
 * @param list<string> $columns
 */
function buildIndexName(string $table, array $columns, string $prefix): string
{
    $normalizedColumns = array_map(
        static fn(string $column): string => preg_replace('/[^a-z0-9_]+/i', '_', $column),
        $columns
    );
    $base = $prefix . '_' . preg_replace('/[^a-z0-9_]+/i', '_', $table) . '_' . implode('_', $normalizedColumns);
    $hash = substr(md5($prefix . '|' . $table . '|' . implode('|', $columns)), 0, 8);
    $name = $base . '_' . $hash;

    if (strlen($name) <= 63) {
        return $name;
    }

    $trimmed = substr($base, 0, max(0, 63 - 1 - strlen($hash)));
    return rtrim($trimmed, '_') . '_' . $hash;
}