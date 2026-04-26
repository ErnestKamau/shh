<?php
$migFile = 'database/migrations/convert/2026_04_23_211812_create_sample_details_table.php';
$mig = file_get_contents($migFile);

// 1. Encrypt fields (change to text)
$encryptFields = ['barcode', 'comments', 'gps', 'section_details', 'main_body', 'header_body', 'notes_body', 'report_number'];
foreach ($encryptFields as $field) {
    $mig = preg_replace("/\\\$table->(string|integer|decimal|text)\('$field'(,\s*\d+(,\s*\d+)?)?\)/", "\$table->text('$field')", $mig);
}

// 2. Convert FK columns to uuid
$uuidFieldsRefs = [
    'crm_unit_id' => 'crm_company_units',
    'main_standard' => 'standards',
    'secondary_standard' => 'standards',
    'third_standard_id' => 'standards',
    'store_id' => 'inventory_stores',
    'store_slot_id' => 'inventory_store_slots'
];

foreach ($uuidFieldsRefs as $field => $tableRef) {
    if (strpos($mig, "\$table->uuid('$field')") === false) {
       // match string, integer, unsignedBigInteger, and possibly default(0)
       $mig = preg_replace("/\\\$table->(string|integer|unsignedBigInteger)\('$field'(,\s*\d+)?\)->nullable\(\)(->default\(0\))?;/", "\$table->uuid('$field')->nullable();", $mig);
       $mig = preg_replace("/\\\$table->(string|integer|unsignedBigInteger)\('$field'(,\s*\d+)?\);/", "\$table->uuid('$field')->nullable();", $mig);
    }
}

// 3. Add foreign keys (only if not exist)
// "ensure the fks added will not bring conflicts of referencing the tables that their migrations are not run"
// They request FKs, I will add them at the bottom.
$foreignKeys = [
    "            \$table->foreign('crm_unit_id')->references('id')->on('crm_company_units')->onDelete('set null');",
    "            \$table->foreign('main_standard')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('secondary_standard')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');",
    "            \$table->foreign('store_id')->references('id')->on('inventory_stores')->onDelete('set null');",
    "            \$table->foreign('store_slot_id')->references('id')->on('inventory_store_slots')->onDelete('set null');"
];

foreach ($foreignKeys as $fk) {
    $parts = explode('->references', ltrim($fk));
    if (strpos($mig, $parts[0]) === false) {
        $mig = preg_replace("/(\s+\\\$table->primary\(\['id'\]\);)/", "\n$fk$1", $mig);
    }
}

file_put_contents($migFile, $mig);

// Model update
$modelPath = 'app/SampleDetails.php';
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
    $model = preg_replace("/(public\s+\\$incrementing\s*=\s*false;)/", "$1\n\n    $castsCode", $model);
}

file_put_contents($modelPath, $model);
echo "Patched\n";

