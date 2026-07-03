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
            $table->uuid('id');
            $table->uuid('report_format_id')->index('idx_report_format_sections_report_format_id_d7e41383');
            $table->string('section_name');
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('custom_title')->nullable();
            $table->longText('settings')->nullable();
            $table->timestamps();
            $table->primary(['id']);
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
