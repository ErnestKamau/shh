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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('form_template_id')->index('idx_form_fields_form_template_id_65b1ae60');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->uuid('parent_field_id')->nullable()->index('idx_form_fields_parent_field_id_00a6440b');
            $table->uuid('parent_id')->nullable()->index('idx_form_fields_parent_id_949894d7');
            $table->string('label');
            $table->string('name')->comment('Unique identifier for the field in the form');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->longText('validation_rules')->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
