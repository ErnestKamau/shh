<?php
$modelPath = 'app/SampleDetails.php';
$model = file_get_contents($modelPath);

$castsCode = "protected \$casts = [
        'barcode' => 'encrypted',
        'comments' => 'encrypted',
        'gps' => 'encrypted',
        'section_details' => 'encrypted',
        'main_body' => 'encrypted',
        'header_body' => 'encrypted',
        'notes_body' => 'encrypted',
        'report_number' => 'encrypted',
        'crm_unit_id' => 'string',
        'main_standard' => 'string',
        'secondary_standard' => 'string',
        'third_standard_id' => 'string',
        'store_id' => 'string',
        'store_slot_id' => 'string'
    ];";

if (strpos($model, 'protected $casts') !== false) {
    echo "Already has casts\n";
} else {
    $model = str_replace("public \$incrementing = false;", "public \$incrementing = false;\n\n    " . $castsCode, $model);
    file_put_contents($modelPath, $model);
    echo "Added casts.\n";
}
