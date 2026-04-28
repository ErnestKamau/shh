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
        Schema::create('form_template_variables', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('form_template_id');
            $table->string('name');
            $table->string('type');
            $table->string('data_type');
            $table->longText('config')->nullable();
            $table->longText('validation_rules')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_form_template_variables_created_by_c85bc1cd');
            $table->timestamps();

            $table->unique(['form_template_id', 'name']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_template_variables');
    }
};
