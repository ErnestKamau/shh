<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$form = \App\Models\SubmissionForm::where('name', 'Laboratory Service Request Form')->first();
if (!$form) {
    echo "Form not found!";
    exit;
}

$elements = [];
foreach ($form->sections as $section) {
    foreach ($section->elementHolders as $holder) {
        foreach ($holder->elements as $element) {
            $elements[] = $element->name . ' (' . $element->element_type . ')';
        }
    }
}

echo "Elements:\n" . implode("\n", $elements);
