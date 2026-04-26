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
        Schema::create('capa_priorities', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('priority_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_capa_priorities_company_id_f4e2bf5a');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['company_id'], 'fk_capa_priorities_company_id_757b9b17')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capa_priorities');
    }
};
