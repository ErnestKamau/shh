<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Submission form columns are configured per customer in CRM Configurations tab.
 * No default columns when a new customer is created.
 */
class CustomerSubmissionFormColumnsSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: customers configure columns via Configurations tab
    }
}
