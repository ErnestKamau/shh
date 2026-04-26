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
            $table->integer('order_index')->default(0)->index('idx_risk_configuration_options_order_index_8ed9a4cd');
            $table->boolean('is_active')->default(true);
            $table->longText('metadata')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risk_configuration_options_company_id_54ffd541');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['option_type', 'company_id', 'is_active'], 'risk_config_opt_type_comp_active_idx');
            $table->unique(['option_type', 'code', 'company_id'], 'risk_config_option_unique');
            $table->foreign(['company_id'], 'fk_risk_configuration_options_company_id_88281b71')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
