<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_form_instances', 'additional_info_responded_at')) {
                $table->timestamp('additional_info_responded_at')->nullable()->after('review_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_instances', 'additional_info_responded_at')) {
                $table->dropColumn('additional_info_responded_at');
            }
        });
    }
};
