<?php

declare(strict_types=1);

/**
 * Convert generated convert migrations to UUID-oriented key columns.
 *
 * - Converts primary key column `id` definitions to uuid.
 * - Converts FK columns participating in `$table->foreign([...])->references(['id'])`
 *   to uuid column definitions when currently integer-based.
 */

$convertDir = dirname(__DIR__, 2) . '/database/migrations/convert';

if (!is_dir($convertDir)) {
    fwrite(STDERR, "Convert directory not found: {$convertDir}\n");
    exit(1);
}

$files = glob($convertDir . '/*_create_*_table.php');
if ($files === false) {
    fwrite(STDERR, "Failed to read convert migration files.\n");
    exit(1);
}

$updatedFiles = 0;
$pkConversions = 0;
$fkColumnConversions = 0;

$intTypes = '(?:unsignedBigInteger|bigInteger|unsignedInteger|integer|unsignedSmallInteger|smallInteger|unsignedTinyInteger|tinyInteger)';

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;

    // Convert common generated integer id PK definitions, preserving method chains.
    $pkPattern = '/^(\s*)\$table->(?:bigInteger\(\'id\',\s*true\)|bigIncrements\(\'id\'\)|increments\(\'id\'\)|integer\(\'id\',\s*true\))([^;]*);\s*$/m';
    $content = preg_replace_callback(
        $pkPattern,
        static function (array $matches) use (&$pkConversions): string {
            $pkConversions++;
            return $matches[1] . "\$table->uuid('id')" . $matches[2] . ';';
        },
        $content
    ) ?? $content;

    // Detect FK columns tied to parent id PKs.
    $fkColumns = [];
    if (preg_match_all('/\$table->foreign\(\[\'([^\']+)\'\]\)->references\(\[\'id\'\]\)->on\(\'([^\']+)\'\)[^;]*;/', $content, $fkMatches, PREG_SET_ORDER)) {
        foreach ($fkMatches as $match) {
            $fkColumns[$match[1]] = true;
        }
    }

    // Convert integer FK columns to uuid in same migration file.
    foreach (array_keys($fkColumns) as $column) {
        $quotedColumn = preg_quote($column, '/');
        $pattern = '/^(\s*)\$table->' . $intTypes . '\(\'' . $quotedColumn . '\'(?:,[^)]*)?\)([^;]*);\s*$/m';

        $content = preg_replace_callback(
            $pattern,
            static function (array $matches) use ($column, &$fkColumnConversions): string {
                $indent = $matches[1];
                $chain = $matches[2];

                // Avoid double index declarations when conversion keeps original chain.
                $replacement = $indent . '$table->uuid(\'' . $column . '\')' . $chain . ';';
                $fkColumnConversions++;

                return $replacement;
            },
            $content
        ) ?? $content;
    }

    // Ensure id has primary key declaration if missing.
    $hasUuidId = strpos($content, "\$table->uuid('id')") !== false;
    $hasPrimaryId = strpos($content, "\$table->primary(['id'])") !== false;
    if ($hasUuidId && !$hasPrimaryId) {
        if (preg_match('/\n\s*\}\);\n\s*\}\n\n\s*\/\*\*/', $content, $match, PREG_OFFSET_CAPTURE)) {
            $offset = $match[0][1];
            $content = substr($content, 0, $offset)
                . "\n            \$table->primary(['id']);"
                . substr($content, $offset);
        }
    }

    if ($content !== $original) {
        if (file_put_contents($file, $content) !== false) {
            $updatedFiles++;
        }
    }
}

fwrite(STDOUT, "UUID conversion pass complete.\n");
fwrite(STDOUT, "Updated files: {$updatedFiles}\n");
fwrite(STDOUT, "Primary key conversions: {$pkConversions}\n");
fwrite(STDOUT, "Foreign key column conversions: {$fkColumnConversions}\n");
