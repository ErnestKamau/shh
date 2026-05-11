<?php

use App\User;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Update the first_name for the user via Eloquent
$user = User::where('email', 'karokin35@gmail.com')->first();
if ($user) {
    $user->first_name = 'David'; // Set to a valid value
    $user->save();
    echo "User first_name updated successfully.\n";
} else {
    echo "User not found.\n";
}
