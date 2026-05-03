<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_forms', 'is_customer_portal_form')) {
                $table->boolean('is_customer_portal_form')
                    ->default(false)
                    ->after('is_active');
            }

            if (!Schema::hasColumn('submission_forms', 'lims_destination_pages')) {
                $table->json('lims_destination_pages')
                    ->nullable()
                    ->after('target_pages');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            if (Schema::hasColumn('submission_forms', 'lims_destination_pages')) {
                $table->dropColumn('lims_destination_pages');
            }

            if (Schema::hasColumn('submission_forms', 'is_customer_portal_form')) {
                $table->dropColumn('is_customer_portal_form');
            }
        });
    }
};
