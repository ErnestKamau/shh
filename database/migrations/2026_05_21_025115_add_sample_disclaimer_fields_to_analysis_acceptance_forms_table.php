<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->boolean('raises_sample_disclaimer')->default(false)->after('receipt_notification_payload');
            $table->json('sample_disclaimer_payload')->nullable()->after('raises_sample_disclaimer');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
            $table->dropColumn(['raises_sample_disclaimer', 'sample_disclaimer_payload']);
        });
    }
};
