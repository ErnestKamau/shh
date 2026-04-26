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
        Schema::create('formula_mandatory_fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('formula_version_id')->index('formula_mandatory_fields_formula_version_id_foreign');
            $table->string('label');
            $table->enum('field_type', ['input', 'datetime', 'date', 'dataset_related']);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('field_value_name');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['formula_version_id'], 'fk_formula_mandatory_fields_formula_version_id_e43a55d8')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_mandatory_fields');
    }
};
