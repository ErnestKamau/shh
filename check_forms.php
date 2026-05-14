<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubmissionForm;

$forms = SubmissionForm::all();
echo "Total forms: " . $forms->count() . "\n";
foreach ($forms as $form) {
    echo "ID: {$form->id}, Name: {$form->name}, Type: {$form->form_type}, Active: {$form->is_active}, Published: {$form->is_published}\n";
}
