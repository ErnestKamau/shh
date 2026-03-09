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
        Schema::table('procedure_worksheets', function (Blueprint $table) {
            $table->string('document_control_no')->nullable()->after('is_active');
            $table->string('revision')->nullable()->after('document_control_no');
            $table->date('issue_date')->nullable()->after('revision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_worksheets', function (Blueprint $table) {
            $table->dropColumn(['document_control_no', 'revision', 'issue_date']);
        });
    }
};
