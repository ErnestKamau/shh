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
        if (Schema::hasTable('capa_priorities')) {
            return;
        }
        Schema::create('capa_priorities', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('priority_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_capa_priorities_company_id_2c9fdfae');
            $table->timestamps();
            $table->softDeletes();

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
