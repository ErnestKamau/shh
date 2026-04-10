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
        Schema::create('risk_configuration_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_type'); // Type identifier (e.g., 'risk_level', 'evaluation_result')
            $table->string('code'); // Unique code within option_type (e.g., 'critical', 'high')
            $table->string('name'); // Display name (e.g., 'Critical', 'High')
            $table->text('description')->nullable();
            $table->string('color_code')->nullable(); // Hex color for badges/display
            $table->integer('order_index')->default(0); // Display order (ascending)
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional configuration (e.g., min/max values, thresholds)
            $table->integer('company_id')->default(0); // Company-specific or global (0 for global)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique Constraint: (option_type, code, company_id)
            $table->unique(['option_type', 'code', 'company_id'], 'risk_config_option_unique');
            
            // Indexes (shortened names to avoid MySQL identifier length limit)
            $table->index(['option_type', 'company_id', 'is_active'], 'risk_config_opt_type_comp_active_idx');
            $table->index('order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_configuration_options');
    }
};
