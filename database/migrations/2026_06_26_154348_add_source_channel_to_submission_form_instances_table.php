<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (! Schema::hasColumn('submission_form_instances', 'source_channel')) {
                $table->string('source_channel', 32)->nullable()->after('receiving_lab_id');
            }

            if (! Schema::hasColumn('submission_form_instances', 'sampling_schedule_id')) {
                $table->uuid('sampling_schedule_id')->nullable()->index()->after('source_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (Schema::hasColumn('submission_form_instances', 'sampling_schedule_id')) {
                $table->dropColumn('sampling_schedule_id');
            }

            if (Schema::hasColumn('submission_form_instances', 'source_channel')) {
                $table->dropColumn('source_channel');
            }
        });
    }
};
