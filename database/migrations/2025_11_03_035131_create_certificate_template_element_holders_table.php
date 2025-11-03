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
        Schema::create('certificate_template_element_holders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_template_section_id')->constrained('certificate_template_sections')->onDelete('cascade');
            $table->enum('holder_type', ['field', 'text'])->default('field');
            $table->integer('max_elements')->default(10);
            $table->integer('sort_order')->default(0);
            $table->decimal('position_x', 10, 2)->nullable();
            $table->decimal('position_y', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('position_x_percent', 10, 4)->nullable();
            $table->decimal('position_y_percent', 10, 4)->nullable();
            $table->decimal('width_percent', 10, 4)->nullable();
            $table->decimal('height_percent', 10, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_element_holders');
    }
};
