<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_template_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('label');
            $table->string('name')->comment('Unique identifier for the field in the form');
            $table->string('type'); // text, number, date, select, radio, checkbox, dynamic, etc.
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->json('validation_rules')->nullable();
            $table->json('meta')->nullable(); // Extra config like min/max, step, etc.
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('form_template_sections')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
