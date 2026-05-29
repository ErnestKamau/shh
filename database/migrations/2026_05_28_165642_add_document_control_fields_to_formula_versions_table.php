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
        Schema::table('formula_versions', function (Blueprint $table) {
            $table->string('document_control_revision_no')->nullable()->after('mandatory_fields_placement');
            $table->date('document_control_issue_date')->nullable()->after('document_control_revision_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_versions', function (Blueprint $table) {
            $table->dropColumn([
                'document_control_revision_no',
                'document_control_issue_date',
            ]);
        });
    }
};
