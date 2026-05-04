<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_form_instances', 'target_record_type')) {
                $table->string('target_record_type', 100)
                    ->nullable()
                    ->after('crm_customer_id')
                    ->comment('Eloquent/DB table name of the record this instance writes mapped values into (e.g. sample_headers)');
            }

            if (!Schema::hasColumn('submission_form_instances', 'target_record_id')) {
                $table->unsignedBigInteger('target_record_id')
                    ->nullable()
                    ->after('target_record_type')
                    ->comment('Primary key of the target record row');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_instances', 'target_record_id')) {
                $table->dropColumn('target_record_id');
            }

            if (Schema::hasColumn('submission_form_instances', 'target_record_type')) {
                $table->dropColumn('target_record_type');
            }
        });
    }
};
