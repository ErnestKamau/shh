<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->json('sample_configuration_payload')->nullable()->after('receipt_notification_payload');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->dropColumn('sample_configuration_payload');
        });
    }
};
