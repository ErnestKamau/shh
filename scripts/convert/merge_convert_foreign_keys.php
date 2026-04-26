<?php

declare(strict_types=1);

/**
 * Merge generated add_foreign_keys migrations into create_table migrations
 * so each table is represented by a single migration file.
 */

$convertDir = dirname(__DIR__, 2) . '/database/migrations/convert';

if (!is_dir($convertDir)) {
    fwrite(STDERR, "Convert directory not found: {$convertDir}\n");
    exit(1);
}

$fkFiles = glob($convertDir . '/*_add_foreign_keys_to_*_table.php');
if ($fkFiles === false) {
    fwrite(STDERR, "Failed to read convert directory.\n");
    exit(1);
}

$mergedCount = 0;
$skippedCount = 0;
$removedCount = 0;

foreach ($fkFiles as $fkFile) {
    $fkContent = file_get_contents($fkFile);
    if ($fkContent === false) {
        $skippedCount++;
        continue;
    }

    if (!preg_match("/Schema::table\\('([^']+)'/", $fkContent, $tableMatch)) {
        $skippedCount++;
        continue;
    }

    $tableName = $tableMatch[1];

    if (!preg_match_all('/^\s*\$table->foreign\(\[[^\n]+;\s*$/m', $fkContent, $foreignMatches)) {
        $skippedCount++;
        continue;
    }

    $foreignLines = array_values(array_unique(array_map('trim', $foreignMatches[0])));
    if (count($foreignLines) === 0) {
        $skippedCount++;
        continue;
    }

    $createMatches = glob($convertDir . '/*_create_' . $tableName . '_table.php');
    if ($createMatches === false || count($createMatches) === 0) {
        $skippedCount++;
        continue;
    }

    $createFile = $createMatches[0];
    $createContent = file_get_contents($createFile);
    if ($createContent === false) {
        $skippedCount++;
        continue;
    }

    $newForeignLines = [];
    foreach ($foreignLines as $foreignLine) {
        if (strpos($createContent, $foreignLine) === false) {
            $newForeignLines[] = '            ' . $foreignLine;
        }
    }

    if (count($newForeignLines) === 0) {
        if (unlink($fkFile)) {
            $removedCount++;
        }
        continue;
    }

    $inserted = false;

    // Prefer inserting before explicit primary key declarations.
    if (preg_match('/^\s*\$table->primary\(\[[^\n]*\);\s*$/m', $createContent, $primaryMatch, PREG_OFFSET_CAPTURE)) {
        $offset = $primaryMatch[0][1];
        $insert = implode("\n", $newForeignLines) . "\n\n";
        $createContent = substr($createContent, 0, $offset) . $insert . substr($createContent, $offset);
        $inserted = true;
    }

    // Fallback: insert before closure of Schema::create block in up().
    if (!$inserted && preg_match('/\n\s*\}\);\n\s*\}\n\n\s*\/\*\*/', $createContent, $closureMatch, PREG_OFFSET_CAPTURE)) {
        $offset = $closureMatch[0][1];
        $insert = "\n" . implode("\n", $newForeignLines);
        $createContent = substr($createContent, 0, $offset) . $insert . substr($createContent, $offset);
        $inserted = true;
    }

    if (!$inserted) {
        $skippedCount++;
        continue;
    }

    if (file_put_contents($createFile, $createContent) === false) {
        $skippedCount++;
        continue;
    }

    if (unlink($fkFile)) {
        $removedCount++;
    }

    $mergedCount++;
}

fwrite(STDOUT, "Merge complete.\n");
fwrite(STDOUT, "Merged files: {$mergedCount}\n");
fwrite(STDOUT, "Removed FK files: {$removedCount}\n");
fwrite(STDOUT, "Skipped: {$skippedCount}\n");
