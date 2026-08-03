<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submission_form_instances')) {
            return;
        }

        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (! Schema::hasColumn('submission_form_instances', 'trf_pdf_orientation')) {
                $table->string('trf_pdf_orientation', 16)->nullable()->after('form_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('submission_form_instances')) {
            return;
        }

        Schema::table('submission_form_instances', function (Blueprint $table): void {
            if (Schema::hasColumn('submission_form_instances', 'trf_pdf_orientation')) {
                $table->dropColumn('trf_pdf_orientation');
            }
        });
    }
};
