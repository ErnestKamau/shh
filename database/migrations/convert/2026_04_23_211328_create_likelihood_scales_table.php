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
        Schema::create('likelihood_scales', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('score')->default(1);
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_likelihood_scales_company_id_a0b4b2e6');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['company_id'], 'fk_likelihood_scales_company_id_8f5f0346')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likelihood_scales');
    }
};
