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
        Schema::table('audit_workflow_approvers', function (Blueprint $table) {
            $table->string('module')->default('audit')->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_workflow_approvers', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
