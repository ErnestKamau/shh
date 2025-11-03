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
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->unsignedBigInteger('formular_id')->nullable()->after('remedy_header_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropColumn('formular_id');
        });
    }
};
