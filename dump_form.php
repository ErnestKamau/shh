<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubmissionForm;

$form = SubmissionForm::with(['sections.elementHolders.elements', 'customers', 'sampleTypes', 'permissions', 'certificateTemplates', 'sampleAnalysisStages'])->where('name', 'like', '%Laboratory Service Request%')->first();

if (!$form) {
    echo "Form not found!\n";
    exit;
}

$data = $form->toArray();

echo "Found form: " . $form->name . "\n";
file_put_contents('form_dump.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped to form_dump.json\n";
