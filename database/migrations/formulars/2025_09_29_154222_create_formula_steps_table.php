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
        Schema::create('formula_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_version_id');
            $table->integer('step_number');
            $table->string('variable_name');
            $table->enum('step_type', ['input', 'derived', 'lookup']);
            $table->text('expression')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('lookup_config')->nullable(); // For lookup table configuration
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['formula_version_id', 'step_number']);
            $table->index(['step_type']);
            $table->unique(['formula_version_id', 'variable_name', 'deleted_at']);
        });

        // Add foreign key constraints
        Schema::table('formula_steps', function (Blueprint $table) {
            $table->foreign('formula_version_id')->references('id')->on('formula_versions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_steps');
    }
};