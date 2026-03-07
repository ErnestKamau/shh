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
        Schema::create('report_format_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('report_format_id');
            $table->string('key_name'); // e.g., 'default_disclaimer', 'decision_rule', 'methodology'
            $table->longText('text_value')->nullable();
            $table->timestamps();

            $table->foreign('report_format_id')->references('id')->on('report_formats')->onDelete('cascade');
            $table->unique(['report_format_id', 'key_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_format_details');
    }
};
