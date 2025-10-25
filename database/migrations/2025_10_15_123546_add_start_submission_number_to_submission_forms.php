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
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->integer('start_submission_number')->nullable()->default(1)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->dropColumn('start_submission_number');
        });
    }
};
