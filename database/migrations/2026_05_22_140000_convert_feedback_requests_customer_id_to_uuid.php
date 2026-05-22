<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedback_requests')) {
            Schema::table('feedback_requests', function (Blueprint $table): void {
                $table->dropColumn('customer_id');
            });

            Schema::table('feedback_requests', function (Blueprint $table): void {
                $table->uuid('customer_id')->nullable()->index('idx_feedback_requests_customer_id_976dfc5d');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('feedback_requests')) {
            Schema::table('feedback_requests', function (Blueprint $table): void {
                $table->dropColumn('customer_id');
            });

            Schema::table('feedback_requests', function (Blueprint $table): void {
                $table->unsignedBigInteger('customer_id')->nullable()->index('idx_feedback_requests_customer_id_976dfc5d');
            });
        }
    }
};
