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
        if (Schema::hasTable('equipment_disposal_methods')) {
            return;
        }
        Schema::create('equipment_disposal_methods', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('method')->index('idx_equipment_disposal_methods_method_bc8430c4');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->longText('applicable_categories');
            $table->longText('regulatory_requirements')->nullable();
            $table->longText('required_documentation')->nullable();
            $table->longText('approved_vendors')->nullable();
            $table->text('safety_requirements')->nullable();
            $table->text('environmental_compliance')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_equipment_disposal_methods_is_active_ba8ff873');
            $table->timestamps();

            $table->unique(['method']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_methods');
    }
};
