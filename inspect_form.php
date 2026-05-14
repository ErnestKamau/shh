<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubmissionForm;

$form = SubmissionForm::where('name', 'Laboratory Service Request')->first();
if ($form) {
    echo json_encode($form->toArray(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Form not found\n";
}
