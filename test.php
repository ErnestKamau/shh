<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo (new App\Models\CRM\CRMCustomer)->getTable() . "\n";
echo (new App\Models\CRM\CustomerContact)->getTable() . "\n";
