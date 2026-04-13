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
        Schema::create('crm_company_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('crm_customer_id');
            $table->integer('company_id');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->foreign('crm_customer_id')->references('id')->on('crm_customers')->onDelete('cascade');
            $table->index('crm_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_company_sections');
    }
};
