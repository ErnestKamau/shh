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
            $table->uuid('id');
            $table->string('option_type');
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0)->index('idx_risk_configuration_options_order_index_2640819e');
            $table->boolean('is_active')->default(true);
            $table->longText('metadata')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risk_configuration_options_company_id_b1d63d9d');
            $table->uuid('created_by')->nullable()->index('idx_risk_configuration_options_created_by_3857b1b8');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['option_type', 'company_id', 'is_active'], 'idx_risk_configuration_options_option_type_company_id_de86e89d');
            $table->unique(['option_type', 'code', 'company_id'], 'risk_config_option_unique');

            $table->primary(['id']);

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
