<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = \App\Country::orderBy('name')->get(['id', 'name', 'status']);
echo "Total Countries: " . $countries->count() . "\n";
echo "Active Countries: " . $countries->where('status', 1)->count() . "\n";
echo "First 15 Active Countries alphabetically:\n";
$i = 0;
foreach ($countries->where('status', 1) as $c) {
    if ($i++ < 15) {
        echo "- " . $c->name . " (ID: " . $c->id . ")\n";
    }
}
