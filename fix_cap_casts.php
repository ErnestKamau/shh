<?php
$modelPath = 'app/CapturedResult.php';
$model = file_get_contents($modelPath);
$castsCode = "protected \$casts = [
        'analyte_code' => 'encrypted',
        'result' => 'encrypted',
        'remark' => 'encrypted',
        'main_value' => 'encrypted',
        'secondary_value' => 'encrypted',
        'sec_remark' => 'encrypted',
        'third_remark' => 'encrypted',
        'scienctific_result' => 'encrypted',
        'superscript_number' => 'encrypted',
        'superscript_negative' => 'encrypted',
        'supercsript_base' => 'encrypted',
        'operator_id' => 'string',
        'method_id' => 'string',
        'main_standard_id' => 'string',
        'secondary_standard_id' => 'string',
        'lab_section_id' => 'string',
        'third_standard_id' => 'string',
        'ltm_method_id' => 'string',
        'formular_id' => 'string',
        'method_sequence_id' => 'string'
    ];";

if (strpos($model, 'protected $casts') === false) {
    $model = str_replace("public \$incrementing = false;", "public \$incrementing = false;\n\n    " . $castsCode, $model);
    file_put_contents($modelPath, $model);
    echo "Fixed casts.\n";
} else {
    echo "Casts exists.\n";
}
