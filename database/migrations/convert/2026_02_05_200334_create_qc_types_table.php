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
        if (Schema::hasTable('qc_types')) {
            return;
        }
        Schema::create('qc_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('has_standards')->default(false);
            $table->boolean('has_configured_samples')->default(false);
            $table->boolean('is_active')->default(false);
            $table->uuid('created_by')->nullable()->index('idx_qc_types_created_by_87b89563');
            $table->timestamps();
            $table->boolean('use_existing_sample')->nullable()->default(false);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_types');
    }
};
