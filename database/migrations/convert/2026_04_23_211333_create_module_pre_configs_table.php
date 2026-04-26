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
        Schema::create('module_pre_configs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('type');
            $table->string('description', 1024)->nullable();
            $table->integer('level')->nullable();
            $table->string('color', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->string('module')->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_module_pre_configs_inventory_location_id_393825af');
            $table->string('zoho_id')->nullable();
            $table->string('code', 100)->nullable();
            $table->foreign(['inventory_location_id'], 'fk_module_pre_configs_inventory_location_id_805c2e87')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_pre_configs');
    }
};
