<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->json('receipt_notification_payload')->nullable()->after('processing_error');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->dropColumn('receipt_notification_payload');
        });
    }
};
