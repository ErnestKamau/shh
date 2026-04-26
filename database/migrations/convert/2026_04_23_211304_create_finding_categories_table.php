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
        Schema::create('finding_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('severity')->nullable();
            $table->boolean('requires_capa')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_finding_categories_company_id_e00d1c37');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['company_id'], 'fk_finding_categories_company_id_c33bfaf4')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finding_categories');
    }
};
