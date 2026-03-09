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
        Schema::create('report_format_sections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('report_format_id');
            $table->string('section_name'); // e.g., 'Header', 'Sample Info', 'Methods', 'Results', 'Signatures'
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('custom_title')->nullable();
            $table->timestamps();

            $table->foreign('report_format_id')->references('id')->on('report_formats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_format_sections');
    }
};
