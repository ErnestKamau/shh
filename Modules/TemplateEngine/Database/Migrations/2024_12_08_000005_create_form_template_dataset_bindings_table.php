<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_template_dataset_bindings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_field_id');
            $table->string('table_name');
            $table->string('column_value'); // The column to store as value (e.g., id)
            $table->string('column_label'); // The column to show as label (e.g., name)
            $table->json('filters')->nullable(); // WHERE conditions
            $table->string('order_by_column')->nullable();
            $table->string('order_direction')->default('asc');
            $table->timestamps();
            
            $table->foreign('form_field_id')->references('id')->on('form_fields')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_template_dataset_bindings');
    }
};
