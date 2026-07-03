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
        if (Schema::hasTable('lookup_tables')) {
            return;
        }
        Schema::create('lookup_tables', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('key_columns');
            $table->string('key_label')->nullable();
            $table->string('value_column');
            $table->enum('lookup_type', ['key_value_comparison', 'range_based'])->default('key_value_comparison');
            $table->string('range_variable_name')->nullable();
            $table->string('value_interpretation_column')->nullable();
            $table->string('value_label')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_lookup_tables_is_active_53930634');
            $table->boolean('is_standard')->default(false);
            $table->boolean('show_on_report')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'deleted_at']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookup_tables');
    }
};
