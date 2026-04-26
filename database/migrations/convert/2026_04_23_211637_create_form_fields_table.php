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
            $table->uuid('form_template_id')->index('form_fields_form_template_id_foreign');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->uuid('parent_field_id')->nullable()->index('form_fields_parent_field_id_foreign');
            $table->uuid('parent_id')->nullable()->index('form_fields_parent_id_foreign');
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
            $table->foreign(['form_template_id'], 'fk_form_fields_form_template_id_fb6faee5')->references(['id'])->on('form_templates')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_field_id'], 'fk_form_fields_parent_field_id_739ee42c')->references(['id'])->on('form_fields')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_id'], 'fk_form_fields_parent_id_729e8c68')->references(['id'])->on('form_fields')->onUpdate('no action')->onDelete('cascade');
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
