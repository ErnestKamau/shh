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
        if (Schema::hasTable('system_configuration_types')) {
            return;
        }
        Schema::create('system_configuration_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('configuration_type');
            $table->text('description');
            $table->boolean('status')->default(false);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configuration_types');
    }
};
