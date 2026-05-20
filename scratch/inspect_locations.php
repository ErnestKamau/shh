<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\InventoryLocation;
use App\Zone;

echo "=== INVENTORY LOCATIONS ===\n";
foreach (InventoryLocation::all() as $loc) {
    echo "ID: {$loc->id} | Name: {$loc->name} | Parent ID: {$loc->inventory_location_id}\n";
}

echo "\n=== ZONES ===\n";
foreach (Zone::all() as $zone) {
    echo "ID: {$zone->id} | Key: {$zone->key} | Value: {$zone->value} | Location ID: {$zone->inventory_location_id}\n";
}
