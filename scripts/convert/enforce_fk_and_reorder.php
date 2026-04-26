<?php

declare(strict_types=1);

/**
 * Enforce FK/index rules on convert migrations and reorder files parent-first.
 *
 * Inputs:
 * - storage/convert_audit/missing_fk_candidates.tsv
 *
 * Rules:
 * - For mapped *_id columns (likely_parent_table present), ensure index + FK exists.
 * - If parent table PK is uuid('id'), convert integer-like FK column declaration to uuid().
 * - Keep one migration file per table.
 * - Reorder migration filenames so parent tables come before children.
 */

$root = dirname(__DIR__, 2);
$convertDir = $root . '/database/migrations/convert';
$auditFile = $root . '/storage/convert_audit/missing_fk_candidates.tsv';
$reportDir = $root . '/storage/convert_audit';
$reportFile = $reportDir . '/enforce_fk_report.txt';

if (!is_dir($convertDir) || !is_file($auditFile)) {
    fwrite(STDERR, "Required input missing. convertDir or audit file not found.\n");
    exit(1);
}

@mkdir($reportDir, 0777, true);

/** @var array<string, string> $tableToFile */
$tableToFile = [];
/** @var array<string, bool> $tableHasUuidPk */
$tableHasUuidPk = [];

$files = glob($convertDir . '/*_create_*_table.php') ?: [];
foreach ($files as $file) {
    $name = basename($file);
    if (!preg_match('/_create_(.+)_table\.php$/', $name, $m)) {
        continue;
    }

    $table = $m[1];
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $tableToFile[$table] = $file;
    $tableHasUuidPk[$table] = strpos($content, "\$table->uuid('id')") !== false;
}

$overrideParentByColumn = [
    'created_by' => 'users',
    'updated_by' => 'users',
    'deleted_by' => 'users',
    'approved_by' => 'users',
    'assigned_by' => 'users',
    'requested_by' => 'users',
    'closed_by' => 'users',
    'uploaded_by' => 'users',
    'owner_id' => 'users',
    'user_id' => 'users',
    'company_id' => 'companies',
    'country_id' => 'countries',
];

$skipColumns = [
    'id' => true,
];

$skipFkPairs = [
    // Explicit cycle break: keep risk_assessments.risk_id FK, skip backlink on risks.current_assessment_id.
    'risks.current_assessment_id' => true,
    // Explicit cycle break: risk_evaluations references risks; skip backlink current pointer.
    'risks.current_evaluation_id' => true,
    // Explicit cycle break: keep sample_headers.sample_header_staging_id FK, skip backlink on staging table.
    'sample_header_staging.sample_header_id' => true,
    // Explicit cycle break: users and labs reference each other.
    'users.lab_id' => true,
];

$intMethods = [
    'unsignedBigInteger',
    'bigInteger',
    'unsignedInteger',
    'integer',
    'unsignedSmallInteger',
    'smallInteger',
    'unsignedTinyInteger',
    'tinyInteger',
];

$nonUuidFkMethods = [
    'string',
    'char',
    'text',
    'tinyText',
    'mediumText',
    'longText',
];

/** @var array<int, array{child:string,column:string,parent:string,indexed:int}> $candidates */
$candidates = [];
$lines = file($auditFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
foreach ($lines as $line) {
    $parts = explode("\t", $line);
    if (count($parts) < 5) {
        continue;
    }

    [$childTable, $column, $columnType, $likelyParent, $isIndexed] = $parts;
    if (isset($skipColumns[$column])) {
        continue;
    }

    $parent = trim($likelyParent) !== '' && strtoupper($likelyParent) !== 'NULL' ? $likelyParent : '';
    if (isset($overrideParentByColumn[$column])) {
        $parent = $overrideParentByColumn[$column];
    }

    if ($parent === '' || !isset($tableToFile[$childTable]) || !isset($tableToFile[$parent])) {
        continue;
    }

    $candidates[] = [
        'child' => $childTable,
        'column' => $column,
        'parent' => $parent,
        'indexed' => (int)$isIndexed,
    ];
}

$modifiedFiles = [];
$fkAdded = 0;
$indexAdded = 0;
$typeConverted = 0;

foreach ($candidates as $candidate) {
    $childTable = $candidate['child'];
    $column = $candidate['column'];
    $parentTable = $candidate['parent'];

    if (isset($skipFkPairs[$childTable . '.' . $column])) {
        continue;
    }

    $childFile = $tableToFile[$childTable];
    $content = file_get_contents($childFile);
    if ($content === false) {
        continue;
    }

    $original = $content;

    // Skip if column does not appear in file.
    if (strpos($content, "'{$column}'") === false) {
        continue;
    }

    // Convert integer-like FK column type to uuid when parent PK is uuid.
    $parentIsUuid = $tableHasUuidPk[$parentTable] ?? false;
    if ($parentIsUuid) {
        foreach (array_merge($intMethods, $nonUuidFkMethods) as $method) {
            $pattern = '/^(\s*)\$table->' . preg_quote($method, '/') . '\(\'' . preg_quote($column, '/') . '\'(?:,[^)]*)?\)([^;]*);\s*$/m';
            $content = preg_replace_callback(
                $pattern,
                static function (array $m) use (&$typeConverted, $column): string {
                    $typeConverted++;
                    return $m[1] . "\$table->uuid('{$column}')" . $m[2] . ';';
                },
                $content
            ) ?? $content;
        }
    }

    // Ensure inline index or separate index exists for the column.
    $hasInlineIndex = (bool)preg_match('/\$table->[^\n;]*\(\'' . preg_quote($column, '/') . '\'[^\n;]*->index\(/', $content);
    $hasSeparateIndex = (bool)preg_match('/\$table->index\(\[[^\]]*\'' . preg_quote($column, '/') . '\'[^\]]*\]\)/', $content)
        || (bool)preg_match('/\$table->index\(\'' . preg_quote($column, '/') . '\'\)/', $content);

    if (!$hasInlineIndex && !$hasSeparateIndex) {
        $linePattern = '/^(\s*\$table->[^\n;]*\(\'' . preg_quote($column, '/') . '\'(?:,[^)]*)?\)([^;]*));\s*$/m';
        $content = preg_replace_callback(
            $linePattern,
            static function (array $m) use (&$indexAdded, $childTable, $column): string {
                if (strpos($m[1], '->index(') !== false) {
                    return $m[0];
                }
                $indexAdded++;
                $indexName = buildShortIndexName($childTable, [$column]);
                return $m[1] . "->index('{$indexName}');";
            },
            $content,
            1
        ) ?? $content;
    }

    // Ensure FK declaration exists.
    $hasForeign = (bool)preg_match('/\$table->foreign\(\[\'' . preg_quote($column, '/') . '\'\](?:,\s*\'[^\']+\')?\)/', $content);
    if (!$hasForeign) {
        $isNullable = (bool)preg_match('/\$table->[^\n;]*\(\'' . preg_quote($column, '/') . '\'[^\n;]*->nullable\(/', $content)
            || (bool)preg_match('/\$table->[^\n;]*\(\'' . preg_quote($column, '/') . '\'[^\n;]*->nullable\(\)/', $content);
        $onDelete = $isNullable ? 'set null' : 'cascade';

        $fkName = buildShortFkName($childTable, $column, $parentTable);
        $fkLine = "            \$table->foreign(['{$column}'], '{$fkName}')->references(['id'])->on('{$parentTable}')->onUpdate('no action')->onDelete('{$onDelete}');\n";

        if (preg_match('/\n\s*\$table->primary\(\[\'id\'\]\);/m', $content, $pm, PREG_OFFSET_CAPTURE)) {
            $offset = $pm[0][1];
            $content = substr($content, 0, $offset) . $fkLine . substr($content, $offset);
            $fkAdded++;
        } elseif (preg_match('/\n\s*\}\);\n\s*\}\n\n\s*\/\*\*/', $content, $cm, PREG_OFFSET_CAPTURE)) {
            $offset = $cm[0][1];
            $content = substr($content, 0, $offset) . "\n" . $fkLine . substr($content, $offset);
            $fkAdded++;
        }
    }

    if ($content !== $original) {
        file_put_contents($childFile, $content);
        $modifiedFiles[$childFile] = true;
    }
}

// Remove explicit exception FK lines when present.
foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;
    foreach (array_keys($skipFkPairs) as $pair) {
        [$skipTable, $skipColumn] = explode('.', $pair, 2);
        if ($skipTable !== $table) {
            continue;
        }

        $content = preg_replace(
            '/^\s*\$table->foreign\(\[\'' . preg_quote($skipColumn, '/') . '\'\](?:,\s*\'[^\']+\')?\)->references\(\[\'id\'\]\)->on\(\'[^\']+\'\)[^;]*;\s*$/m',
            '',
            $content
        ) ?? $content;
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        $modifiedFiles[$file] = true;
    }
}

// Remove duplicate FK lines per (column,parent) pair within each migration.
foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;
    // Normalize accidentally concatenated FK statements to one statement per line.
    $content = preg_replace('/;\s*(\$table->foreign\(\[)/', ";\n            $1", $content) ?? $content;

    $lines = explode("\n", $content);
    $seen = [];
    $out = [];

    foreach ($lines as $line) {
        if (preg_match('/\$table->foreign\(\[\'([^\']+)\'\](?:,\s*\'[^\']+\')?\)->references\(\[\'id\'\]\)->on\(\'([^\']+)\'\)/', $line, $m)) {
            $key = $m[1] . '|' . $m[2];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
        }
        $out[] = $line;
    }

    $content = implode("\n", $out);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $modifiedFiles[$file] = true;
    }
}

// Ensure all FK declarations have explicit short names to avoid identifier overflows.
foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;

    $content = preg_replace_callback(
        '/\$table->foreign\(\[\'([^\']+)\'\]\)->references\(\[\'id\'\]\)->on\(\'([^\']+)\'\)([^;]*);/',
        static function (array $m) use ($table): string {
            $column = $m[1];
            $parent = $m[2];
            $tail = $m[3];
            $fkName = buildShortFkName($table, $column, $parent);
            return "\$table->foreign(['{$column}'], '{$fkName}')->references(['id'])->on('{$parent}'){$tail};";
        },
        $content
    ) ?? $content;

    if ($content !== $original) {
        file_put_contents($file, $content);
        $modifiedFiles[$file] = true;
    }
}

// Ensure all index declarations have explicit short names to avoid identifier overflows.
foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;

    $content = preg_replace_callback(
        '/(\$table->[^\n;]*\(\'([^\']+)\'[^\n;]*\))->index\(\);/',
        static function (array $m) use ($table): string {
            $column = $m[2];
            $indexName = buildShortIndexName($table, [$column]);
            return $m[1] . "->index('{$indexName}');";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/\$table->index\(\[([^\]]+)\]\);/',
        static function (array $m) use ($table): string {
            preg_match_all('/\'([^\']+)\'/', $m[1], $cm);
            $columns = $cm[1] ?? [];
            if ($columns === []) {
                return $m[0];
            }
            $indexName = buildShortIndexName($table, $columns);
            return "\$table->index([" . implode(', ', array_map(static fn($c) => "'{$c}'", $columns)) . "], '{$indexName}');";
        },
        $content
    ) ?? $content;

    $content = preg_replace_callback(
        '/\$table->index\(\'([^\']+)\'\);/',
        static function (array $m) use ($table): string {
            $column = $m[1];
            $indexName = buildShortIndexName($table, [$column]);
            return "\$table->index('{$column}', '{$indexName}');";
        },
        $content
    ) ?? $content;

    if ($content !== $original) {
        file_put_contents($file, $content);
        $modifiedFiles[$file] = true;
    }
}

// Global FK type alignment for already constrained columns.
foreach ($tableToFile as $childTable => $childFile) {
    $content = file_get_contents($childFile);
    if ($content === false) {
        continue;
    }

    $original = $content;

    if (preg_match_all('/\$table->foreign\(\[\'([^\']+)\'\](?:,\s*\'[^\']+\')?\)->references\(\[\'id\'\]\)->on\(\'([^\']+)\'\)/', $content, $fkMatches, PREG_SET_ORDER)) {
        foreach ($fkMatches as $fm) {
            $column = $fm[1];
            $parentTable = $fm[2];

            if (!($tableHasUuidPk[$parentTable] ?? false)) {
                continue;
            }

            foreach (array_merge($intMethods, $nonUuidFkMethods) as $method) {
                $pattern = '/^(\s*)\$table->' . preg_quote($method, '/') . '\(\'' . preg_quote($column, '/') . '\'(?:,[^)]*)?\)([^;]*);\s*$/m';
                $content = preg_replace_callback(
                    $pattern,
                    static function (array $m) use (&$typeConverted, $column): string {
                        $typeConverted++;
                        return $m[1] . "\$table->uuid('{$column}')" . $m[2] . ';';
                    },
                    $content
                ) ?? $content;
            }
        }
    }

    if ($content !== $original) {
        file_put_contents($childFile, $content);
        $modifiedFiles[$childFile] = true;
    }
}

// Ensure uuid id columns are explicitly primary keys for FK compatibility.
foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $original = $content;
    $hasUuidId = strpos($content, "\$table->uuid('id')") !== false;
    $hasPrimaryId = strpos($content, "\$table->primary(['id'])") !== false
        || strpos($content, "\$table->primary(\"id\")") !== false
        || strpos($content, "\$table->primary('id')") !== false;

    if ($hasUuidId && !$hasPrimaryId) {
        if (preg_match('/\n\s*\}\);\n\s*\}\n\n\s*\/\*\*/', $content, $cm, PREG_OFFSET_CAPTURE)) {
            $offset = $cm[0][1];
            $content = substr($content, 0, $offset)
                . "\n            \$table->primary(['id']);"
                . substr($content, $offset);
        }
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        $modifiedFiles[$file] = true;
    }
}

// Build dependency graph from FK lines for parent-first filename ordering.
$nodes = array_keys($tableToFile);
$inDegree = array_fill_keys($nodes, 0);
$adj = [];

foreach ($tableToFile as $table => $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    if (preg_match_all('/\$table->foreign\(\[\'([^\']+)\'\](?:,\s*\'[^\']+\')?\)->references\(\[\'id\'\]\)->on\(\'([^\']+)\'\)/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $parent = $m[2];
            if (!isset($tableToFile[$parent]) || $parent === $table) {
                continue;
            }
            if (!isset($adj[$parent])) {
                $adj[$parent] = [];
            }
            // parent -> child edge for topological order
            if (!in_array($table, $adj[$parent], true)) {
                $adj[$parent][] = $table;
                $inDegree[$table]++;
            }
        }
    }
}

$queue = [];
foreach ($inDegree as $node => $deg) {
    if ($deg === 0) {
        $queue[] = $node;
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

// Cycle fallback: append remaining nodes alphabetically.
if (count($ordered) < count($nodes)) {
    $remaining = array_diff($nodes, $ordered);
    sort($remaining);
    $ordered = array_merge($ordered, $remaining);
}

// Repair parent-before-child order for non-mutual dependencies.
$position = array_flip($ordered);
$changed = true;
$maxPasses = max(1, count($ordered) * 2);
$pass = 0;

while ($changed && $pass < $maxPasses) {
    $changed = false;
    $pass++;

    foreach ($adj as $parent => $children) {
        foreach ($children as $child) {
            if (!isset($position[$parent], $position[$child])) {
                continue;
            }

            // Skip direct mutual edges because they are cyclic and cannot both be satisfied.
            if (in_array($parent, $adj[$child] ?? [], true)) {
                continue;
            }

            if ($position[$parent] > $position[$child]) {
                $childPos = $position[$child];
                $parentPos = $position[$parent];

                array_splice($ordered, $childPos, 1);
                if ($childPos < $parentPos) {
                    $parentPos--;
                }
                array_splice($ordered, $parentPos + 1, 0, [$child]);

                $position = array_flip($ordered);
                $changed = true;
            }
        }
    }
}

// Reassign timestamps in parent-first order.
$start = new DateTimeImmutable('now');
$renamed = 0;
$tempMap = [];

foreach ($ordered as $idx => $table) {
    $oldFile = $tableToFile[$table];
    $name = basename($oldFile);
    if (!preg_match('/_create_' . preg_quote($table, '/') . '_table\.php$/', $name)) {
        continue;
    }

    $ts = $start->modify('+' . $idx . ' seconds')->format('Y_m_d_His');
    $newName = $ts . '_create_' . $table . '_table.php';
    $tempName = '__tmp__' . $newName;
    $tempPath = $convertDir . '/' . $tempName;

    if ($oldFile !== $tempPath) {
        rename($oldFile, $tempPath);
        $tempMap[$tempPath] = $convertDir . '/' . $newName;
        $renamed++;
    }
}

foreach ($tempMap as $temp => $final) {
    rename($temp, $final);
}

$report = [];
$report[] = 'FK enforcement complete';
$report[] = 'Modified files: ' . count($modifiedFiles);
$report[] = 'FK added: ' . $fkAdded;
$report[] = 'Index added: ' . $indexAdded;
$report[] = 'FK type converted to uuid: ' . $typeConverted;
$report[] = 'Files renamed parent-first: ' . $renamed;
$report[] = 'Total tables ordered: ' . count($ordered);

file_put_contents($reportFile, implode("\n", $report) . "\n");

echo implode("\n", $report) . "\n";

/**
 * Build deterministic FK name under MySQL identifier limit.
 */
function buildShortFkName(string $table, string $column, string $parent): string
{
    $base = 'fk_' . preg_replace('/[^a-z0-9_]+/i', '_', $table) . '_' . preg_replace('/[^a-z0-9_]+/i', '_', $column);
    $hash = substr(md5($table . '|' . $column . '|' . $parent), 0, 8);
    $name = $base . '_' . $hash;

    if (strlen($name) <= 63) {
        return $name;
    }

    $trimmed = substr($base, 0, max(0, 63 - 1 - strlen($hash)));
    return rtrim($trimmed, '_') . '_' . $hash;
}

/**
 * Build deterministic index name under MySQL identifier limit.
 *
 * @param array<int, string> $columns
 */
function buildShortIndexName(string $table, array $columns): string
{
    $normalizedColumns = array_map(
        static fn(string $c): string => preg_replace('/[^a-z0-9_]+/i', '_', $c),
        $columns
    );
    $colPart = implode('_', $normalizedColumns);
    $base = 'idx_' . preg_replace('/[^a-z0-9_]+/i', '_', $table) . '_' . $colPart;
    $hash = substr(md5($table . '|' . implode('|', $columns)), 0, 8);
    $name = $base . '_' . $hash;

    if (strlen($name) <= 63) {
        return $name;
    }

    $trimmed = substr($base, 0, max(0, 63 - 1 - strlen($hash)));
    return rtrim($trimmed, '_') . '_' . $hash;
}
