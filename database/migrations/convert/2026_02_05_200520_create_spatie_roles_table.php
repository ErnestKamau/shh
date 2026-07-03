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
        if (Schema::hasTable('spatie_roles')) {
            return;
        }
        Schema::create('spatie_roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('level')->default(1);
            $table->string('guard_name');
            $table->uuid('company_id')->nullable()->index('idx_spatie_roles_company_id_737315ee');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spatie_roles');
    }
};
