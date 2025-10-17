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
        Schema::create('formula_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_id');
            $table->integer('version_number');
            $table->boolean('is_active')->default(false);
            $table->bigInteger('created_by');
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['formula_id', 'is_active']);
            $table->index(['version_number']);
            $table->unique(['formula_id', 'version_number', 'deleted_at']);
        });

        // Add foreign key constraints
        Schema::table('formula_versions', function (Blueprint $table) {
            $table->foreign('formula_id')->references('id')->on('formulas')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_versions');
    }
};