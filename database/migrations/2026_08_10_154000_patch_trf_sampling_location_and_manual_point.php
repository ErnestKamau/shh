<?php

use Database\Seeders\SubmissionFormTrfRowsSectionPatchSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Ensure Sampling Location (CRM dropdown) and Sampling Point (manual text)
     * are distinct on all TRF Test & sample information sections.
     */
    public function up(): void
    {
        (new SubmissionFormTrfRowsSectionPatchSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: restoring prior field layouts risks dropping
        // Sampling Point values already captured as free text.
    }
};
