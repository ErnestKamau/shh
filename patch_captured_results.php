<?php
$migFile = 'database/migrations/convert/2026_04_23_211827_create_captured_results_table.php';
$mig = file_get_contents($migFile);

$encryptFields = [
    'analyte_code', 'result', 'remark', 'main_value', 'secondary_value',
    'sec_remark', 'third_remark', 'scienctific_result', 'superscript_number',
    'superscript_negative', 'supercsript_base'
];

foreach ($encryptFields as $field) {
    // match string, boolean, integer, etc
    $mig = preg_replace("/\\\$table->(string|boolean|integer|decimal|text)\('$field'(,\s*\d+(,\s*\d+)?)?\)/", "\$table->text('$field')", $mig);
}

$uuidFieldsRefs = [
    'operator_id' => 'users',
    'method_id' => 'methods',
    'main_standard_id' => 'standards',
    'secondary_standard_id' => 'standards',
    'lab_section_id' => 'sample_analysis_stages',
    'third_standard_id' => 'standards',
    'ltm_method_id' => 'methods',
    'formular_id' => 'formulars',
    'method_sequence_id' => 'method_sequences' // method_sequence_id is already uuid, but let's make sure it handles it fine.
];

foreach ($uuidFieldsRefs as $field => $tableRef) {
    if ($field !== 'method_sequence_id' && strpos($mig, "\$table->uuid('$field')") === false) {
       $mig = preg_replace("/\\\$table->(integer|string|bigInteger|unsignedBigInteger)\('$field'(,\s*\d+)?\)->nullable\(\)(->default\(0\))?;/", "\$table->uuid('$field')->nullable();", $mig);
       $mig = preg_replace("/\\\$table->(integer|string|bigInteger|unsignedBigInteger)\('$field'(,\s*\d+)?\)(->default\(0\))?;/", "\$table->uuid('$field')->nullable();", $mig);
    }
}

$foreignKeys = [
    "            \$table->foreign('operator_id')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('method_id')->references('id')->on('methods')->onDelete('set null');",
    "            \$table->foreign('main_standard_id')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('secondary_standard_id')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('lab_section_id')->references('id')->on('sample_analysis_stages')->onDelete('set null');",
    "            \$table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('ltm_method_id')->references('id')->on('methods')->onDelete('set null');",
    "            \$table->foreign('formular_id')->references('id')->on('formulars')->onDelete('set null');"
];

foreach ($foreignKeys as $fk) {
    $parts = explode('->references', ltrim($fk));
    if (strpos($mig, $parts[0]) === false) {
        $mig = preg_replace("/(\s+\\\$table->primary\(\['id'\]\);)/", "\n$fk$1", $mig);
    }
}

file_put_contents($migFile, $mig);

$modelPath = 'app/CapturedResult.php';
$model = file_get_contents($modelPath);

$castsLines = [];
foreach ($encryptFields as $field) {
    $castsLines[] = "'$field' => 'encrypted'";
}
foreach (array_keys($uuidFieldsRefs) as $field) {
    $castsLines[] = "'$field' => 'string'";
}

$castsCode = "protected \$casts = [\n        " . implode(",\n        ", $castsLines) . "\n    ];\n";

if (strpos($model, 'protected $casts') !== false) {
    $model = preg_replace("/protected\s+\\$casts\s*=\s*\[.*?\];/s", $castsCode, $model);
} else {
    // Insert casts property into model. Under `protected $guarded = ['id'];`
    $model = preg_replace("/(protected\s+\\$guarded\s*=\s*\['id'\];)/", "$1\n\n    $castsCode", $model);
}

// Make sure CapturedResult has HasUuids and correct incrementing and keytype properties
if (strpos($model, 'use HasUuids;') === false) {
    $model = preg_replace("/(class\s+CapturedResult\s+extends\s+Model\s+implements\s+Auditable\s*\{)(.*?)(use\s+\\\OwenIt\\\Auditing\\\Auditable;)/s", "$1$2use \\Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;\n\tprotected \$keyType = 'string';\n\tpublic \$incrementing = false;\n\tuse \\OwenIt\\Auditing\\Auditable;", $model);
}

file_put_contents($modelPath, $model);
echo "Patched\n";

