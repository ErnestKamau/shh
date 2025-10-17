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
            $table->id();
            $table->foreignId('formula_version_id')->constrained('formula_versions')->onDelete('cascade');
            $table->string('label');
            $table->enum('field_type', ['input', 'datetime', 'date', 'dataset_related']);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('field_value_name');
            $table->timestamps();
            $table->softDeletes();
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
