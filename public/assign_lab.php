<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$user = \App\User::where('email', 'karokin35@gmail.com')->first() ?? \App\User::where('first_name', 'Nancy')->first();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$lab = \App\Lab::where('name', 'NZ-Microbiology Lab')->first() ?? \App\Lab::first();

if (!$lab) {
    echo json_encode(['error' => 'Lab not found']);
    exit;
}

// Check if already assigned
$exists = \Illuminate\Support\Facades\DB::table('user_lab_relation')
    ->where('user_id', $user->id)
    ->where('lab_id', $lab->id)
    ->exists();

if (!$exists) {
    \Illuminate\Support\Facades\DB::table('user_lab_relation')->insert([
        'user_id' => $user->id,
        'lab_id' => $lab->id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo json_encode([
    'success' => true,
    'message' => 'Assigned lab to user',
    'user' => $user->name,
    'lab' => $lab->name,
    'was_already_assigned' => $exists
]);
