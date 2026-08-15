<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure water TRF test_requirements (and legacy test_category) row elements
     * are visible to the sample-row editor and submission persistence.
     */
    public function up(): void
    {
        DB::table('submission_form_elements')
            ->whereIn('name', ['test_requirements', 'test_category'])
            ->where('is_hidden', true)
            ->update(['is_hidden' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible visibility patch.
    }
};
