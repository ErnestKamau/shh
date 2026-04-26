<?php

// Patch migration
$migFile = 'database/migrations/convert/2026_04_23_211607_create_users_table.php';
$mig = file_get_contents($migFile);

// Change columns to text for encryption
$mig = preg_replace("/\\\$table->string\('email'\)->unique\(\);/", "\$table->text('email')->nullable();", $mig);
$mig = preg_replace("/\\\$table->string\('phone',\s*100\)->nullable\(\);/", "\$table->text('phone')->nullable();", $mig);

// gender
if (strpos($mig, "'gender'") === false) {
    $mig = preg_replace("/\\\$table->text\('phone'\)->nullable\(\);/", "\$table->text('phone')->nullable();\n            \$table->text('gender')->nullable();", $mig);
}

$mig = preg_replace("/\\\$table->integer\('designation'\)->nullable\(\);/", "\$table->text('designation')->nullable();", $mig);
$mig = preg_replace("/\\\$table->date\('date_of_birth'\)->nullable\(\);/", "\$table->text('date_of_birth')->nullable();", $mig);
$mig = preg_replace("/\\\$table->string\('id_number',\s*100\)->nullable\(\);/", "\$table->text('id_number')->nullable();", $mig);
$mig = preg_replace("/\\\$table->string\('first_name',\s*100\)->nullable\(\);/", "\$table->text('first_name')->nullable();", $mig);
$mig = preg_replace("/\\\$table->string\('middle_name',\s*100\)->nullable\(\);/", "\$table->text('middle_name')->nullable();", $mig);
$mig = preg_replace("/\\\$table->string\('last_name',\s*100\)->nullable\(\);/", "\$table->text('last_name')->nullable();", $mig);

$mig = preg_replace("/\\\$table->string\('verify_code'\)->nullable\(\);/", "\$table->text('verify_code')->nullable();", $mig);
$mig = preg_replace("/\\\$table->dateTime\('verify_code_expires'\)->nullable\(\);/", "\$table->text('verify_code_expires')->nullable();", $mig);
// two_factor_secret and two_factor_recovery_codes are already text.

// Make client_id and crm_contact_id UUIDs and add foreign keys
$mig = preg_replace("/\\\$table->string\('client_id'\)->nullable\(\);/", "\$table->uuid('client_id')->nullable()->index('idx_users_client_id');", $mig);
$mig = preg_replace("/\\\$table->unsignedBigInteger\('crm_contact_id'\)->nullable\(\);/", "\$table->uuid('crm_contact_id')->nullable()->index('idx_users_crm_contact_id');", $mig);
$mig = preg_replace("/\\\$table->unsignedBigInteger\('crmcontact_id'\)->nullable\(\);/", "\$table->uuid('crmcontact_id')->nullable(); // redundant, maybe keeping as uuid?", $mig);

// Add foreign key definitions
$fkCode = <<<CODE
            \$table->foreign('client_id', 'fk_users_client_id')->references('id')->on('crm_customers')->onDelete('set null');
            \$table->foreign('crm_contact_id', 'fk_users_crm_contact_id')->references('id')->on('crm_customer_contacts')->onDelete('set null');
CODE;

if (strpos($mig, "fk_users_client_id") === false) {
    // Add before primary id
    $mig = preg_replace("/(\s+\\\$table->primary\(\['id'\]\);)/", "\n$fkCode\n$1", $mig);
}

file_put_contents($migFile, $mig);

// Patch User.php
$userFile = 'app/User.php';
$user = file_get_contents($userFile);

// Casts
$casts = [
    "'email' => 'encrypted'",
    "'verify_code' => 'encrypted'",
    "'phone' => 'encrypted'",
    "'gender' => 'encrypted'",
    "'designation' => 'encrypted'",
    "'date_of_birth' => 'encrypted'",
    "'id_number' => 'encrypted'",
    "'first_name' => 'encrypted'",
    "'middle_name' => 'encrypted'",
    "'last_name' => 'encrypted'",
    "'verify_code_expires' => 'encrypted'",
    "'two_factor_secret' => 'encrypted'",
    "'two_factor_recovery_codes' => 'encrypted'",
    "'client_id' => 'string'",
    "'crm_contact_id' => 'string'",
    "'crmcontact_id' => 'string'",
];

$castsCode = "[\n        " . implode(",\n        ", $casts) . ",\n        'email_verified_at' => 'datetime',\n    ]";
$user = preg_replace("/protected\s+\\$casts\s*=\s*\[[^\]]+\];/", "protected \$casts = $castsCode;", $user);

file_put_contents($userFile, $user);

echo "Done\n";
