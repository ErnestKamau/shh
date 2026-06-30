<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (! Schema::hasColumn('submission_form_instances', 'selected_sample_type_id')) {
                $table->uuid('selected_sample_type_id')->nullable()->after('submission_form_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (Schema::hasColumn('submission_form_instances', 'selected_sample_type_id')) {
                $table->dropColumn('selected_sample_type_id');
            }
        });
    }
};
