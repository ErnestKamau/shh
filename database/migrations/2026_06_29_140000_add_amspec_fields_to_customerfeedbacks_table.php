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
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->string('business_frequency')->nullable();
            $table->text('hear_about_us')->nullable(); // Stores multiselect choices as JSON
            $table->string('hear_about_us_other')->nullable();
            $table->text('critical_services')->nullable(); // Stores top 3 ranked services as JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->dropColumn(['business_frequency', 'hear_about_us', 'hear_about_us_other', 'critical_services']);
        });
    }
};
