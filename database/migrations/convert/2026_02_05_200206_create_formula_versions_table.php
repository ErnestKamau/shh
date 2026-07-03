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
        if (Schema::hasTable('formula_versions')) {
            return;
        }
        Schema::create('formula_versions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('formula_id');
            $table->integer('version_number')->index('idx_formula_versions_version_number_56f14f21');
            $table->boolean('is_active')->default(false);
            $table->uuid('created_by')->index('idx_formula_versions_created_by_dc607e9a');
            $table->uuid('approved_by')->nullable()->index('idx_formula_versions_approved_by_84a81e32');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['formula_id', 'is_active'], 'idx_formula_versions_formula_id_is_active_6148d94b');
            $table->unique(['formula_id', 'version_number', 'deleted_at']);
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
