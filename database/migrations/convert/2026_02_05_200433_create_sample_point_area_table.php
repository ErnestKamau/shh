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
        if (Schema::hasTable('sample_point_area')) {
            return;
        }
        Schema::create('sample_point_area', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->uuid('crm_customer_id')->index('idx_sample_point_area_crm_customer_id_ac361a33');
            $table->uuid('crm_company_sub_unit_id')->nullable()->index('idx_sample_point_area_crm_company_sub_unit_id_4161041c');
            $table->uuid('crm_area_id')->nullable()->index('idx_sample_point_area_crm_area_id_a8e518d6');
            $table->uuid('crm_company_unit_id')->nullable()->index('idx_sample_point_area_crm_company_unit_id_eb9377aa');
            $table->dateTime('deleted_at')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_point_area');
    }
};
