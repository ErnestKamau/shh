<?php

declare(strict_types=1);

/**
 * Extract all inline foreign keys from convert create migrations into a single
 * terminal migration, while keeping create migrations table-only.
 */

$root = dirname(__DIR__, 2);
$convertDir = $root . '/database/migrations/convert';
$finalMigrationName = '9999_12_31_235959_add_convert_foreign_keys.php';
$finalMigrationPath = $convertDir . '/' . $finalMigrationName;

if (!is_dir($convertDir)) {
    fwrite(STDERR, "Convert directory not found: {$convertDir}\n");
    exit(1);
}

$createFiles = glob($convertDir . '/*_create_*_table.php') ?: [];
sort($createFiles);

/** @var array<string, string> $tableToFile */
$tableToFile = [];
/** @var array<string, list<array{line:string, drop:string, parent:string}>> $fksByTable */
$fksByTable = [];
/** @var array<string, list<string>> $dependencyParents */
$dependencyParents = [];

$manualForeignKeys = [
    'system_configurations' => [
        [
            'line' => "            \$table->foreign(['configuration_type_id'], 'fk_system_configurations_configuration_type_id')->references(['id'])->on('system_configuration_types')->onUpdate('no action')->onDelete('set null');",
            'drop' => "            \$table->dropForeign('fk_system_configurations_configuration_type_id');",
            'parent' => 'system_configuration_types',
        ],
    ],
];

foreach ($createFiles as $file) {
    $name = basename($file);
    if (!preg_match('/_create_(.+)_table\.php$/', $name, $matches)) {
        continue;
    }

    $table = $matches[1];
    $tableToFile[$table] = $file;

    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;

    preg_match_all('/^[ \t]*\$table->foreign\((.+?)\);[ \t]*$/m', $content, $fkMatches, PREG_SET_ORDER);
    foreach ($fkMatches as $fkMatch) {
        $fullLine = trim($fkMatch[0]);
        $normalized = normalizeForeignLine($fullLine);
        if ($normalized === null) {
            continue;
        }

        $fksByTable[$table] ??= [];
        if (!hasForeignKey($fksByTable[$table], $normalized['line'])) {
            $fksByTable[$table][] = $normalized;
        }
        $dependencyParents[$table] ??= [];
        if (!in_array($normalized['parent'], $dependencyParents[$table], true)) {
            $dependencyParents[$table][] = $normalized['parent'];
        }
    }

    $content = preg_replace('/^[ \t]*\$table->foreign\((.+?)\);[ \t]*(?:\R|$)/m', '', $content) ?? $content;

    $content = preg_replace_callback(
        '/^([ \t]*)\$table->foreignUuid\(\'([^\']+)\'\)((?:\s*\R?[ \t]*->[^;\r\n]+)*)[ \t]*;$/m',
        static function (array $m): string {
            return $m[1] . "\$table->uuid('{$m[2]}')" . $m[3] . ';';
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/^([ \t]*)\$table->foreignId\(\'([^\']+)\'\)((?:\s*\R?[ \t]*->[^;\r\n]+)*)[ \t]*;$/m',
        static function (array $m): string {
            return $m[1] . "\$table->unsignedBigInteger('{$m[2]}')" . $m[3] . ';';
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/^([ \t]*)\$table->foreignUlid\(\'([^\']+)\'\)((?:\s*\R?[ \t]*->[^;\r\n]+)*)[ \t]*;$/m',
        static function (array $m): string {
            return $m[1] . "\$table->ulid('{$m[2]}')" . $m[3] . ';';
        },
        $content
    ) ?? $content;

    $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

    if ($content !== $original) {
        file_put_contents($file, $content);
    }
}

foreach ($manualForeignKeys as $table => $items) {
    foreach ($items as $item) {
        $fksByTable[$table] ??= [];
        if (!hasForeignKey($fksByTable[$table], $item['line'])) {
            $fksByTable[$table][] = $item;
        }
        $dependencyParents[$table] ??= [];
        if (!in_array($item['parent'], $dependencyParents[$table], true)) {
            $dependencyParents[$table][] = $item['parent'];
        }
    }
}

$orderedTables = topoSortTables(array_keys($tableToFile), $dependencyParents);
reassignCreateMigrationTimestamps($convertDir, $orderedTables, $tableToFile);

ksort($fksByTable);

$upBlocks = [];
$downBlocks = [];

foreach ($fksByTable as $table => $fkItems) {
    if (!isset($tableToFile[$table])) {
        continue;
    }

    usort(
        $fkItems,
        static fn(array $left, array $right): int => strcmp($left['line'], $right['line'])
    );

    $upLines = array_map(static fn(array $item): string => $item['line'], $fkItems);
    $downLines = array_map(static fn(array $item): string => $item['drop'], $fkItems);

    $upBlocks[] = "        Schema::table('{$table}', function (Blueprint \$table) {\n" . implode("\n", $upLines) . "\n        });";
    $downBlocks[] = "        Schema::table('{$table}', function (Blueprint \$table) {\n" . implode("\n", $downLines) . "\n        });";
}

$migration = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    /**\n     * Run the migrations.\n     */\n    public function up(): void\n    {\n" . implode("\n\n", $upBlocks) . "\n    }\n\n    /**\n     * Reverse the migrations.\n     */\n    public function down(): void\n    {\n" . implode("\n\n", array_reverse($downBlocks)) . "\n    }\n};\n";

file_put_contents($finalMigrationPath, $migration);

fwrite(STDOUT, "Extracted foreign keys into {$finalMigrationName}\n");
fwrite(STDOUT, 'Create migrations reordered: ' . count($orderedTables) . "\n");
fwrite(STDOUT, 'Tables with foreign keys: ' . count($fksByTable) . "\n");

function normalizeForeignLine(string $line): ?array
{
    if (preg_match('/\$table->foreign\(\[\'([^\']+)\'\](?:,\s*\'([^\']+)\')?\)->references\(\[\'([^\']+)\'\]\)->on\(\'([^\']+)\'\)(.*);$/', $line, $m)) {
        $column = $m[1];
        $name = $m[2] ?: '';
        $reference = $m[3];
        $parent = $m[4];
        $tail = $m[5];
        $normalizedLine = $name !== ''
            ? "            \$table->foreign(['{$column}'], '{$name}')->references(['{$reference}'])->on('{$parent}'){$tail};"
            : "            \$table->foreign(['{$column}'])->references(['{$reference}'])->on('{$parent}'){$tail};";
        $drop = $name !== ''
            ? "            \$table->dropForeign('{$name}');"
            : "            \$table->dropForeign(['{$column}']);";

        return ['line' => $normalizedLine, 'drop' => $drop, 'parent' => $parent];
    }

    if (preg_match('/\$table->foreign\(\'([^\']+)\'(?:,\s*\'([^\']+)\')?\)->references\(\'([^\']+)\'\)->on\(\'([^\']+)\'\)(.*);$/', $line, $m)) {
        $column = $m[1];
        $name = $m[2] ?: '';
        $reference = $m[3];
        $parent = $m[4];
        $tail = $m[5];
        $normalizedLine = $name !== ''
            ? "            \$table->foreign(['{$column}'], '{$name}')->references(['{$reference}'])->on('{$parent}'){$tail};"
            : "            \$table->foreign(['{$column}'])->references(['{$reference}'])->on('{$parent}'){$tail};";
        $drop = $name !== ''
            ? "            \$table->dropForeign('{$name}');"
            : "            \$table->dropForeign(['{$column}']);";

        return ['line' => $normalizedLine, 'drop' => $drop, 'parent' => $parent];
    }

    return null;
}

function hasForeignKey(array $items, string $line): bool
{
    foreach ($items as $item) {
        if ($item['line'] === $line) {
            return true;
        }
    }

    return false;
}

function topoSortTables(array $tables, array $dependencyParents): array
{
    sort($tables);
    $inDegree = array_fill_keys($tables, 0);
    $adj = [];

    foreach ($dependencyParents as $child => $parents) {
        foreach ($parents as $parent) {
            if (!isset($inDegree[$parent]) || $parent === $child) {
                continue;
            }

            $adj[$parent] ??= [];
            if (!in_array($child, $adj[$parent], true)) {
                $adj[$parent][] = $child;
                $inDegree[$child]++;
            }
        }
    }

    $queue = [];
    foreach ($inDegree as $table => $deg) {
        if ($deg === 0) {
            $queue[] = $table;
        }
    }
    sort($queue);

    $ordered = [];
    while ($queue !== []) {
        $current = array_shift($queue);
        $ordered[] = $current;

        foreach ($adj[$current] ?? [] as $child) {
            $inDegree[$child]--;
            if ($inDegree[$child] === 0) {
                $queue[] = $child;
                sort($queue);
            }
        }
    }

    if (count($ordered) < count($tables)) {
        $remaining = array_values(array_diff($tables, $ordered));
        sort($remaining);
        $ordered = array_merge($ordered, $remaining);
    }

    return $ordered;
}

function reassignCreateMigrationTimestamps(string $convertDir, array $orderedTables, array &$tableToFile): void
{
    $start = new DateTimeImmutable('2026-04-23 21:12:21');
    $tempMap = [];

    foreach ($orderedTables as $index => $table) {
        $oldFile = $tableToFile[$table] ?? null;
        if ($oldFile === null) {
            continue;
        }

        $newTimestamp = $start->modify('+' . $index . ' seconds')->format('Y_m_d_His');
        $newName = $newTimestamp . '_create_' . $table . '_table.php';
        $newPath = $convertDir . '/' . $newName;
        $tempPath = $convertDir . '/__tmp__' . $newName;

        if ($oldFile === $newPath) {
            $tableToFile[$table] = $newPath;
            continue;
        }

        rename($oldFile, $tempPath);
        $tempMap[$tempPath] = $newPath;
        $tableToFile[$table] = $newPath;
    }

    foreach ($tempMap as $tempPath => $newPath) {
        rename($tempPath, $newPath);
    }
}