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
        Schema::table('feedback_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback_requests', 'expires_at')) {
                $table->timestamp('expires_at')->after('submitted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback_requests', function (Blueprint $table) {
             if (Schema::hasColumn('feedback_requests', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
