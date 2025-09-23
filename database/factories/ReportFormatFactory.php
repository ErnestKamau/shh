<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ReportFormat;
use Faker\Generator as Faker;

$factory->define(ReportFormat::class, function (Faker $faker) {
    return [
        'report_name' => $faker->words(3, true) . ' Report Format',
        'report_code' => strtoupper($faker->unique()->lexify('RF???')),
        'is_active' => $faker->boolean(80), // 80% chance of being active
        'company_id' => 1, // Default company ID, can be overridden
    ];
});