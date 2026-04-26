<?php

$migFile = 'database/migrations/convert/2026_04_23_211750_create_sample_headers_table.php';
$mig = file_get_contents($migFile);

// Fields to text
$encryptFields = [
    'crm_unit_name', 'reference_number', 'method_deviation_reason', 'document_number', 'description', 
    'importer_address', 'reason_for_submission', 'how_sample_was_obtained', 'sample_appearance_description', 
    'net_quantity_and_unit_of_quantity', 'use_of_goods', 'declared_amount', 'where_sample_was_obtained', 
    'radio_active_levels', 'ammendment_number', 'sampling_officer_name', 'receiving_officer_name', 
    'submit_by', 'batch_report_url', 'batch_instructions', 'condition_quality_sample', 
    'declaration_customer_signature', 'invoice_amount', 'cluster_amount', 'cluster_balance', 
    'cluster_amount_paid', 'case_id', 'batch_report_online_url', 'schedule_customer_email'
];

foreach ($encryptFields as $field) {
    // Replace string, text, integer, decimal with text
    $mig = preg_replace("/\\\$table->(string|integer|decimal|text)\('$field'(,\s*\d+(,\s*\d+)?)?\)/", "\$table->text('$field')", $mig);
}

// Convert FK columns to uuid
$uuidFields = [
    'receiving_officer', 'sampling_officer', 'specialist_analyst_id', 'verify_user_id', 'approve_user_id',
    'invoice_id', 'quote_id', 'crm_unit_id', 'qc_scheme_id', 'qc_type_id', 'sampling_method_id', 'crm_contact_id'
];

foreach ($uuidFields as $field) {
    if (strpos($mig, "\$table->uuid('$field')") === false) {
       $mig = preg_replace("/\\\$table->(string|integer|unsignedBigInteger)\('$field'(,\s*\d+)?\)/", "\$table->uuid('$field')", $mig);
    }
}

file_put_contents($migFile, $mig);

// Model update
$modelPath = 'app/SampleHeader.php';
$model = file_get_contents($modelPath);

$castsLines = [];
foreach ($encryptFields as $field) {
    $castsLines[] = "'$field' => 'encrypted'";
}
foreach ($uuidFields as $field) {
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

