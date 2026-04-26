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
            $table->uuid('created_by')->nullable()->index('form_template_variables_created_by_foreign');
            $table->timestamps();

            $table->unique(['form_template_id', 'name']);
            $table->foreign(['created_by'], 'fk_form_template_variables_created_by_6ba5ff42')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['form_template_id'], 'fk_form_template_variables_form_template_id_bea66487')->references(['id'])->on('form_templates')->onUpdate('no action')->onDelete('cascade');
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
