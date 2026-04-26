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
        Schema::create('sample_point_area', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->uuid('crm_customer_id')->index('sample_point_area_crm_customer_id_foreign');
            $table->uuid('crm_company_sub_unit_id')->nullable()->index('idx_sample_point_area_crm_company_sub_unit_id_6094a349');
            $table->uuid('crm_area_id')->nullable()->index('idx_sample_point_area_crm_area_id_ddb0a506');
            $table->uuid('crm_company_unit_id')->nullable()->index('idx_sample_point_area_crm_company_unit_id_27fe6b42');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign(['crm_customer_id'], 'fk_sample_point_area_crm_customer_id_a7c9520c')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_area_id'], 'fk_sample_point_area_crm_area_id_60117a20')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_company_sub_unit_id'], 'fk_sample_point_area_crm_company_sub_unit_id_7479b6de')->references(['id'])->on('crm_company_sub_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_company_unit_id'], 'fk_sample_point_area_crm_company_unit_id_b92fe99c')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('set null');



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
