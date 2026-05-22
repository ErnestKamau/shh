<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->json('manager_assignment_payload')->nullable()->after('manager_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->dropColumn('manager_assignment_payload');
        });
    }
};
