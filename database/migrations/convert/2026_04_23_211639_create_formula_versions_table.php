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
            $table->uuid('id');
            $table->uuid('formula_id');
            $table->integer('version_number')->index('idx_formula_versions_version_number_77cc4317');
            $table->boolean('is_active')->default(false);
            $table->uuid('created_by')->index('formula_versions_created_by_foreign');
            $table->uuid('approved_by')->nullable()->index('formula_versions_approved_by_foreign');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['formula_id', 'is_active'], 'idx_formula_versions_formula_id_is_active_146f383a');
            $table->unique(['formula_id', 'version_number', 'deleted_at']);
            $table->foreign(['approved_by'], 'fk_formula_versions_approved_by_018dfed1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_formula_versions_created_by_d020a4f5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_id'], 'fk_formula_versions_formula_id_7c1ff219')->references(['id'])->on('formulas')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
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
