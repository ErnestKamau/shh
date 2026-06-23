<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests')
            || ! Schema::hasColumn('sample_submission_requests', 'submitted_by_signature')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->text('submitted_by_signature')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_submission_requests')
            || ! Schema::hasColumn('sample_submission_requests', 'submitted_by_signature')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->string('submitted_by_signature')->nullable()->change();
        });
    }
};
