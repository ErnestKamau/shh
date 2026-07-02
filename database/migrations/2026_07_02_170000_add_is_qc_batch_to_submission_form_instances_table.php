<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (! Schema::hasColumn('submission_form_instances', 'is_qc_batch')) {
                $table->boolean('is_qc_batch')->default(false)->after('receiving_lab_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (Schema::hasColumn('submission_form_instances', 'is_qc_batch')) {
                $table->dropColumn('is_qc_batch');
            }
        });
    }
};
