<?php
$modelPath = 'app/SampleHeader.php';
$model = file_get_contents($modelPath);

$encryptFields = [
    'crm_unit_name', 'reference_number', 'method_deviation_reason', 'document_number', 'description', 
    'importer_address', 'reason_for_submission', 'how_sample_was_obtained', 'sample_appearance_description', 
    'net_quantity_and_unit_of_quantity', 'use_of_goods', 'declared_amount', 'where_sample_was_obtained', 
    'radio_active_levels', 'ammendment_number', 'sampling_officer_name', 'receiving_officer_name', 
    'submit_by', 'batch_report_url', 'batch_instructions', 'condition_quality_sample', 
    'declaration_customer_signature', 'invoice_amount', 'cluster_amount', 'cluster_balance', 
    'cluster_amount_paid', 'case_id', 'batch_report_online_url', 'schedule_customer_email'
];

$uuidFields = [
    'receiving_officer', 'sampling_officer', 'specialist_analyst_id', 'verify_user_id', 'approve_user_id',
    'invoice_id', 'quote_id', 'crm_unit_id', 'qc_scheme_id', 'qc_type_id', 'sampling_method_id', 'crm_contact_id'
];

$castsLines = [];
foreach ($encryptFields as $field) {
    $castsLines[] = "'$field' => 'encrypted'";
}
foreach ($uuidFields as $field) {
    $castsLines[] = "'$field' => 'string'";
}

$castsCode = "protected \$casts = [\n        " . implode(",\n        ", $castsLines) . "\n    ];\n";

if (strpos($model, 'protected $casts') === false) {
    $model = preg_replace("/(public\s+\\$incrementing\s*=\s*false;)/", "$1\n\n    $castsCode", $model);
    file_put_contents($modelPath, $model);
}

// Ensure foreign keys exist in migration
$migFile = 'database/migrations/convert/2026_04_23_211750_create_sample_headers_table.php';
$mig = file_get_contents($migFile);

$foreignKeys = [
    "            \$table->foreign('receiving_officer')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('sampling_officer')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('specialist_analyst_id')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('verify_user_id')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('approve_user_id')->references('id')->on('users')->onDelete('set null');",
    "            \$table->foreign('invoice_id')->references('id')->on('customer_invoice')->onDelete('set null');",
    "            \$table->foreign('quote_id')->references('id')->on('quotation_headers')->onDelete('set null');",
    "            \$table->foreign('crm_unit_id')->references('id')->on('crm_company_units')->onDelete('set null');",
    "            \$table->foreign('qc_scheme_id')->references('id')->on('qc_scheme')->onDelete('set null');",
    "            \$table->foreign('qc_type_id')->references('id')->on('qc_types')->onDelete('set null');",
    "            \$table->foreign('sampling_method_id')->references('id')->on('analysis_methods')->onDelete('set null');",
    "            \$table->foreign('crm_contact_id')->references('id')->on('crm_customer_contacts')->onDelete('set null');"
];

foreach ($foreignKeys as $fk) {
    // Only add if it doesn't already exist based on substring match
    $parts = explode('->references', ltrim($fk));
    if (strpos($mig, $parts[0]) === false) {
        $mig = preg_replace("/(\s+\\\$table->primary\(\['id'\]\);)/", "\n$fk$1", $mig);
    }
}

file_put_contents($migFile, $mig);
