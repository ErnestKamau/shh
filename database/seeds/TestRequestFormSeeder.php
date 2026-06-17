<?php

namespace Database\Seeders;

use App\Models\TestRequestForm;
use Illuminate\Database\Seeder;

class TestRequestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestRequestForm::seedDefaults();
    }
}
