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
        Schema::create('risk_business_processes', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risk_business_processes_company_id_313e48dd');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['company_id'], 'fk_risk_business_processes_company_id_6a052fac')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_business_processes');
    }
};
