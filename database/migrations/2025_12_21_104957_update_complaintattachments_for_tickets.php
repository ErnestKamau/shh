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
        Schema::table('complaintattachments', function (Blueprint $table) {
            // File type (screenshot, document, other)
            if (!Schema::hasColumn('complaintattachments', 'file_type')) {
                $table->enum('file_type', ['screenshot', 'document', 'other'])
                    ->default('document')
                    ->after('type');
            }
            
            // File size in KB
            if (!Schema::hasColumn('complaintattachments', 'file_size')) {
                $table->unsignedInteger('file_size')->nullable()->after('file_type');
            }
            
            // Soft deletes
            if (!Schema::hasColumn('complaintattachments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintattachments', function (Blueprint $table) {
            if (Schema::hasColumn('complaintattachments', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
            if (Schema::hasColumn('complaintattachments', 'file_size')) {
                $table->dropColumn('file_size');
            }
            if (Schema::hasColumn('complaintattachments', 'file_type')) {
                $table->dropColumn('file_type');
            }
        });
    }
};
